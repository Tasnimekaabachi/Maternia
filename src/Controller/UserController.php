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

class UserController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/profile', name: 'app_user_profile')]
    public function index(Request $request, EntityManagerInterface $entityManager, \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(\App\Form\ProfileEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the plain password if handled
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
        
        // On autorise TOUT ce qui est bloqué dans tes captures (Fonts, CDNs, scripts)
        $scripts = ["'self'", "'unsafe-inline'", "'unsafe-eval'", "https://ajax.googleapis.com", "https://code.jquery.com", "https://cdn.jsdelivr.net", "https://cdnjs.cloudflare.com"];
        $styles = ["'self'", "'unsafe-inline'", "https://fonts.googleapis.com", "https://cdn.jsdelivr.net", "https://cdnjs.cloudflare.com", "https://use.fontawesome.com"];
        $fonts = ["'self'", "https://fonts.gstatic.com", "https://cdn.jsdelivr.net", "https://cdnjs.cloudflare.com", "data:"];
        
        $csp = "default-src 'self'; " .
               "script-src " . implode(' ', $scripts) . "; " .
               "style-src " . implode(' ', $styles) . "; " .
               "font-src " . implode(' ', $fonts) . "; " .
               "connect-src 'self' https://api.luxand.cloud https://127.0.0.1:8000; " .
               "img-src 'self' data:;";

        $response->headers->set('Content-Security-Policy', $csp);
        return $response;
    }

    #[Route('/profile/enroll-face', name: 'app_enroll_face', methods: ['POST'])]
    public function enroll(Request $request, EntityManagerInterface $em): JsonResponse 
    {
        $photo = $request->files->get('photo');
        $user = $this->getUser();

        if (!$photo || !$user) {
            return new JsonResponse(['success' => false, 'error' => 'Image non reçue'], 400);
        }

        try {
            // MÉTHODE ALTERNATIVE PLUS SIMPLE (évite MultipartFormDataPart si besoin)
            $response = $this->httpClient->request('POST', 'https://api.luxand.cloud/v2/person', [
                'headers' => [
                    'token' => $_ENV['LUXAND_API_KEY']
                ],
                'body' => [
                    'name' => $user->getUserIdentifier(),
                    'photos' => fopen($photo->getPathname(), 'r'),
                ],
            ]);

            $data = $response->toArray(false);

            if (isset($data['uuid'])) {
                if (method_exists($user, 'setFacialId')) {
                    $user->setFacialId($data['uuid']); 
                    $em->flush();
                    return new JsonResponse(['success' => true]);
                }
            }
            return new JsonResponse(['success' => false, 'error' => 'Luxand error: ' . json_encode($data)], 400);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
