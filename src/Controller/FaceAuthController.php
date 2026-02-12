<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FaceAuthController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/face-login-verify', name: 'app_face_login_verify', methods: ['POST'])]
    public function verify(
        Request $request, 
        UserRepository $userRepo, 
        UserAuthenticatorInterface $authenticator, 
        FormLoginAuthenticator $formAuthenticator
    ) {
        $photo = $request->files->get('photo');

        if (!$photo) {
            return new JsonResponse(['error' => 'Photo non reçue'], 400);
        }

        // 1. Demander à Luxand qui est sur la photo
        $response = $this->httpClient->request('POST', 'https://api.luxand.cloud/v2/search', [
            'headers' => ['token' => $_ENV['LUXAND_API_KEY']],
            'multipart' => [
                ['name' => 'photo', 'contents' => fopen($photo->getPathname(), 'r')],
                ['name' => 'all', 'contents' => '1'] // Pour chercher dans toute la collection
            ]
        ]);

        $results = $response->toArray();

        // 2. Si un visage est reconnu
        if (!empty($results)) {
            $facialId = $results[0]['uuid'];
            $user = $userRepo->findOneBy(['facialId' => $facialId]);

            if ($user) {
                // 3. Connecter l'utilisateur à Symfony
                return $authenticator->authenticateUser($user, $formAuthenticator, $request);
            }
        }

        return new JsonResponse(['error' => 'Visage non reconnu'], 401);
    }
}