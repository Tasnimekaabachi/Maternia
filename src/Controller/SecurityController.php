<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private string $compreFaceUrl;
    private string $compreFaceApiKey;

    public function __construct()
    {
        // Initialize CompreFace configuration
        $this->compreFaceUrl = $_ENV['COMPREFACE_URL'] ?? 'http://localhost:8000';
        $this->compreFaceApiKey = $_ENV['COMPREFACE_API_KEY'] ?? '';
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Interception par le firewall.');
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) return $this->redirectToRoute('app_user_profile');
        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(), 
            'error' => $authenticationUtils->getLastAuthenticationError()
        ]);
    }

    #[Route('/login/check-face', name: 'app_check_face', methods: ['POST'])]
    public function checkFace(
        Request $request, 
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        $photo = $request->files->get('photo');
        
        if (!$photo) {
            return new JsonResponse(['success' => false, 'error' => 'Photo manquante']);
        }

        // Validate image type
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($photo->getMimeType(), $allowedMimeTypes)) {
            return new JsonResponse([
                'success' => false, 
                'error' => 'Format d\'image invalide. Utilisez JPEG ou PNG.'
            ], 400);
        }

        try {
            // Call CompreFace recognition API
            $ch = curl_init($this->compreFaceUrl . '/api/v1/recognition/recognize');
            
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'x-api-key: ' . $this->compreFaceApiKey,
                ],
                CURLOPT_POSTFIELDS => [
                    'file' => new \CURLFile($photo->getPathname(), $photo->getMimeType(), $photo->getClientOriginalName()),
                    'limit' => 1 // Only need the top match
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Check for CURL errors
            if ($curlError) {
                return new JsonResponse([
                    'success' => false, 
                    'error' => 'Erreur de connexion: ' . $curlError
                ], 500);
            }

            // Check HTTP response
            if ($httpCode !== 200) {
                return new JsonResponse([
                    'success' => false, 
                    'error' => 'CompreFace a retourné une erreur (HTTP ' . $httpCode . ')'
                ], 500);
            }

            $data = json_decode($response, true);

            // Check if any face was recognized
            if (empty($data['result']) || empty($data['result'][0]['subjects'])) {
                return new JsonResponse([
                    'success' => false, 
                    'error' => 'Aucun visage reconnu. Vérifiez l\'éclairage et réessayez.'
                ]);
            }

            // Get the top match
            $match = $data['result'][0]['subjects'][0];
            $subject = $match['subject']; // Format: "user_X" where X is the user ID
            $similarity = $match['similarity'];
            
            // Extract user ID from subject (format: "user_123")
            $userId = str_replace('user_', '', $subject);
            
            // Log for debugging
            error_log("Face recognition - Subject: $subject, Similarity: $similarity, User ID: $userId");

            // Check similarity threshold (70% is a good starting point)
            if ($similarity < 0.7) {
                return new JsonResponse([
                    'success' => false, 
                    'error' => 'Confiance trop faible pour une connexion sécurisée. Réessayez avec un meilleur éclairage.'
                ]);
            }

            // Find the user in database by ID
            $user = $em->getRepository(User::class)->find($userId);

            if (!$user) {
                return new JsonResponse([
                    'success' => false, 
                    'error' => "Utilisateur non trouvé dans la base de données. ID: $userId"
                ]);
            }

            // Log the user in
            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
            $tokenStorage->setToken($token);
            $request->getSession()->set('_security_main', serialize($token));
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Connexion réussie!'
            ]);

        } catch (\Exception $e) {
            error_log('Face login exception: ' . $e->getMessage());
            return new JsonResponse([
                'success' => false, 
                'error' => 'Erreur technique: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optional: Test endpoint to verify CompreFace is working
     */
    #[Route('/test-face-recognition', name: 'test_face_recognition', methods: ['POST'])]
    public function testFaceRecognition(Request $request): JsonResponse
    {
        $photo = $request->files->get('photo');
        
        if (!$photo) {
            return new JsonResponse(['error' => 'No photo provided'], 400);
        }

        try {
            $ch = curl_init($this->compreFaceUrl . '/api/v1/recognition/recognize');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['x-api-key: ' . $this->compreFaceApiKey],
                CURLOPT_POSTFIELDS => ['file' => new \CURLFile($photo->getPathname())],
                CURLOPT_TIMEOUT => 30
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode($response, true);
            
            return new JsonResponse([
                'http_code' => $httpCode,
                'response' => $data
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}