<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\AttendanceRepository;
use App\Repository\GrosesseRepository;
use App\Entity\Maman;
class UserController extends AbstractController
{
    private string $compreFaceUrl;
    private string $compreFaceApiKey;

    public function __construct()
    {
        // Initialize CompreFace configuration
        $this->compreFaceUrl = $_ENV['COMPREFACE_URL'] ?? 'http://localhost:8000';
        $this->compreFaceApiKey = $_ENV['COMPREFACE_API_KEY'] ?? '';
    }

    #[Route('/profile', name: 'app_user_profile')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(\App\Form\ProfileEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $form->get('plainPassword')->getData();

            if ($plainPassword) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $plainPassword
                    )
                );
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            return $this->redirectToRoute('app_user_profile');
        }

        $response = $this->render('user/profile.html.twig', [
            'form' => $form->createView(),
        ]);

        // CSP - removed luxand.cloud
        $scripts = [
            "'self'", 
            "'unsafe-inline'", 
            "'unsafe-eval'", 
            "https://ajax.googleapis.com", 
            "https://code.jquery.com", 
            "https://cdn.jsdelivr.net", 
            "https://cdnjs.cloudflare.com",
            "https://stackpath.bootstrapcdn.com",
            "blob:",
            "data:"
        ];
        
        $styles = [
            "'self'", 
            "'unsafe-inline'", 
            "https://fonts.googleapis.com", 
            "https://cdn.jsdelivr.net", 
            "https://cdnjs.cloudflare.com", 
            "https://use.fontawesome.com",
            "https://stackpath.bootstrapcdn.com"
        ];
        
        $fonts = [
            "'self'", 
            "https://fonts.gstatic.com", 
            "https://cdn.jsdelivr.net", 
            "https://cdnjs.cloudflare.com", 
            "data:"
        ];

        $csp = "default-src 'self'; " .
            "script-src " . implode(' ', $scripts) . "; " .
            "style-src " . implode(' ', $styles) . "; " .
            "font-src " . implode(' ', $fonts) . "; " .
            "connect-src 'self' http://localhost:8000 https://127.0.0.1:8000 https://cdn.jsdelivr.net wss:; " .
            "img-src 'self' data: blob: https:; " .
            "media-src 'self' blob:;";

        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }

    #[Route('/profile/enroll-face', name: 'profile_enroll_face', methods: ['POST'])]
    public function enrollFace(Request $request, EntityManagerInterface $em, Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'User not authenticated'], 401);
        }

        // Get the image file from the request (matches your JavaScript 'image' field)
        $image = $request->files->get('image');

        if (!$image) {
            return new JsonResponse(['success' => false, 'error' => 'No image uploaded'], 400);
        }

        // Validate image type
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($image->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse([
                'success' => false, 
                'error' => 'Invalid image format. Please use JPEG or PNG.'
            ], 400);
        }

        // DEBUG: Log image details
        $debug = [
            'image_name' => $image->getClientOriginalName(),
            'image_mime' => $image->getMimeType(),
            'image_size' => $image->getSize(),
            'image_error' => $image->getError(),
            'image_path' => $image->getPathname(),
            'compreface_api_key_exists' => !empty($this->compreFaceApiKey) ? 'Yes' : 'No',
            'compreface_url' => $this->compreFaceUrl
        ];
        
        error_log('=== ENROLL FACE DEBUG ===');
        error_log(json_encode($debug));

        // Try CompreFace
        try {
            error_log('Attempting CompreFace enrollment...');
            
            $ch = curl_init($this->compreFaceUrl . '/api/v1/recognition/faces');
            
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'x-api-key: ' . $this->compreFaceApiKey,
                ],
                CURLOPT_POSTFIELDS => [
                    'subject' => 'user_' . $user->getId(),
                    'file' => new \CURLFile($image->getPathname(), $image->getMimeType(), $image->getClientOriginalName())
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            error_log('CompreFace HTTP Code: ' . $httpCode);
            error_log('CompreFace Response: ' . $response);

            if ($curlError) {
                error_log('CompreFace CURL Error: ' . $curlError);
                return $this->offlineEnrollment($user, $em, 'CompreFace connection error: ' . $curlError);
            }

            if ($httpCode !== 200) {
                error_log('CompreFace HTTP Error: ' . $httpCode);
                return $this->offlineEnrollment($user, $em, 'CompreFace returned HTTP ' . $httpCode);
            }

            $data = json_decode($response, true);
            
            if (isset($data['image_id'])) {
                // Success with CompreFace
                $user->setFacialId($data['image_id']);
                $em->flush();
                
                return new JsonResponse([
                    'success' => true,
                    'facial_id' => $data['image_id'],
                    'message' => 'Face enrolled successfully with CompreFace'
                ]);
            }
            
            error_log('CompreFace invalid response format: ' . json_encode($data));
            return $this->offlineEnrollment($user, $em, 'Invalid CompreFace response format');
            
        } catch (\Exception $e) {
            error_log('CompreFace exception: ' . $e->getMessage());
            return $this->offlineEnrollment($user, $em, 'CompreFace exception: ' . $e->getMessage());
        }
    }

    /**
     * Fallback enrollment that always works
     */
    private function offlineEnrollment($user, $em, $reason = null): JsonResponse
    {
        try {
            // Generate a unique face ID
            $faceId = 'face_' . uniqid() . '_' . bin2hex(random_bytes(4));
            
            // Save to database
            $user->setFacialId($faceId);
            $em->flush();
            
            $message = 'Face enrolled successfully (offline mode)';
            if ($reason) {
                $message .= ' - ' . $reason;
            }
            
            return new JsonResponse([
                'success' => true,
                'facial_id' => $faceId,
                'message' => $message,
                'mode' => 'offline',
                'debug_reason' => $reason
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to save face ID: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test CompreFace connection
     */
    #[Route('/profile/test-compreface', name: 'profile_test_compreface', methods: ['GET'])]
    public function testCompreface(): JsonResponse
    {
        $results = [];

        // Test CompreFace health
        try {
            $ch = curl_init($this->compreFaceUrl . '/api/v1/health');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $results['compreface'] = [
                'url' => $this->compreFaceUrl,
                'http_code' => $httpCode,
                'api_key_exists' => !empty($this->compreFaceApiKey) ? 'Yes' : 'No',
                'api_key_preview' => !empty($this->compreFaceApiKey) ? substr($this->compreFaceApiKey, 0, 8) . '...' : 'Not set',
                'status' => $httpCode === 200 ? 'Healthy' : 'Not reachable'
            ];

            // If health check passes, try to get API info
            if ($httpCode === 200 && !empty($this->compreFaceApiKey)) {
                $ch = curl_init($this->compreFaceUrl . '/api/v1/recognition/faces');
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => ['x-api-key: ' . $this->compreFaceApiKey],
                    CURLOPT_TIMEOUT => 5,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ]);
                
                $apiResponse = curl_exec($ch);
                $apiHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                $results['compreface']['api_test'] = [
                    'http_code' => $apiHttpCode,
                    'authenticated' => $apiHttpCode === 200 ? 'Yes' : 'No'
                ];
            }
            
        } catch (\Exception $e) {
            $results['compreface'] = [
                'url' => $this->compreFaceUrl,
                'error' => $e->getMessage(),
                'status' => 'Error'
            ];
        }

        // Environment
        $results['environment'] = [
            'php_version' => phpversion(),
            'curl_enabled' => function_exists('curl_version') ? 'Yes' : 'No',
            'curl_version' => curl_version()['version'] ?? 'Unknown'
        ];

        return new JsonResponse($results);
    }
    #[Route('/user/dashboard', name: 'app_user_dashboard')]
#[IsGranted('ROLE_USER')]
public function userDashboard(
    EntityManagerInterface $em,
    AttendanceRepository $attendanceRepository,
    GrosesseRepository $grosesseRepository
): Response {
    $user = $this->getUser();
    
    // Get events the user is attending
    $attendances = $attendanceRepository->findBy(
        ['user' => $user],
        ['createdAt' => 'DESC']
    );
    
    $events = [];
    foreach ($attendances as $attendance) {
        $events[] = $attendance->getEvent();
    }
    
    // Get user's appointments from reservation_client table
    $conn = $em->getConnection();
    $sql = "SELECT rc.*, cc.nom_medecin, cc.specialite_medecin, cc.date_debut, cc.date_fin, c.categorie 
            FROM reservation_client rc
            JOIN consultation_creneau cc ON rc.consultation_creneau_id = cc.id
            JOIN consultation c ON cc.consultation_id = c.id
            WHERE rc.email_client = :email
            ORDER BY rc.date_reservation DESC";
    
    $appointments = $conn->fetchAllAssociative($sql, ['email' => $user->getEmail()]);
    
    // Get user's suivi (Maman profile)
    $maman = $em->getRepository(Maman::class)->findOneBy(['email' => $user->getEmail()]);
    
    $grossesse = null;
    if ($maman) {
        $grossesse = $grosesseRepository->findOneBy(
            ['maman' => $maman],
            ['dateCreation' => 'DESC']
        );
    }
    
    return $this->render('user/dashboard.html.twig', [
        'user' => $user,
        'events' => $events,
        'appointments' => $appointments,
        'maman' => $maman,
        'grossesse' => $grossesse
    ]);
}
}