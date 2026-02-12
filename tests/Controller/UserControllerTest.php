<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\MultipartFormDataPart;

class UserController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/profile', name: 'app_user_profile')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $response = $this->render('user/profile.html.twig');
        
        // Autorisation CSP pour Brave et les scripts externes
        $allowed = ["'self'", "'unsafe-inline'", "'unsafe-eval'", "https://ajax.googleapis.com", "https://code.jquery.com", "https://cdn.jsdelivr.net", "https://cdnjs.cloudflare.com"];
        $csp = "default-src 'self'; script-src " . implode(' ', $allowed) . "; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data:; connect-src 'self' https://api.luxand.cloud;";
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }

    #[Route('/profile/enroll-face', name: 'app_enroll_face', methods: ['POST'])]
    public function enroll(Request $request, EntityManagerInterface $em): JsonResponse 
    {
        $photo = $request->files->get('photo');
        $user = $this->getUser();

        if (!$photo || !$user) {
            return new JsonResponse(['success' => false, 'error' => 'Données manquantes'], 400);
        }

        try {
            // On prépare l'envoi manuellement pour éviter l'erreur "Unsupported option multipart"
            $fields = [
                'name' => $user->getUserIdentifier(),
                'photos' => DataPart::fromPath($photo->getPathname(), 'face.jpg', 'image/jpeg'),
            ];
            $formData = new MultipartFormDataPart($fields);

            $response = $this->httpClient->request('POST', 'https://api.luxand.cloud/v2/person', [
                'headers' => array_merge(
                    $formData->getPreparedHeaders()->toArray(),
                    ['token' => $_ENV['LUXAND_API_KEY']]
                ),
                'body' => $formData->bodyToIterable(),
            ]);

            $data = $response->toArray(false);

            if (isset($data['uuid'])) {
                // Assure-toi que ton entité User a bien une propriété facialId
                if (method_exists($user, 'setFacialId')) {
                    $user->setFacialId($data['uuid']); 
                    $em->flush();
                    return new JsonResponse(['success' => true]);
                }
                return new JsonResponse(['success' => false, 'error' => 'Propriété facialId manquante dans l\'entité User.']);
            }
            
            return new JsonResponse(['success' => false, 'error' => 'Luxand n\'a pas pu identifier le visage.'], 400);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }
}