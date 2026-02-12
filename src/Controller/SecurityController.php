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
        if (!$photo) return new JsonResponse(['success' => false, 'error' => 'Photo manquante']);

        try {
            $ch = curl_init("https://api.luxand.cloud/v2/search");
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'photo' => new \CURLFile($photo->getPathname()),
                'all' => '1' 
            ]);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'token: ' . $_ENV['LUXAND_API_KEY'],
                'Connection: close',
                'Expect:'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $data = json_decode($response, true);
            curl_close($ch);

            // Si Luxand reconnaît quelqu'un
            if (!empty($data) && isset($data[0])) {
                $match = $data[0];
                $probability = $match['probability'] ?? 0;
                
                // On récupère l'ID de la personne (prioritaire) ou du visage
                $detectedId = $match['person_uuid'] ?? $match['uuid'];
                $detectedName = $match['name'] ?? 'Inconnu';

                if ($probability > 0.4) {
                    $user = $em->getRepository(User::class)->findOneBy(['facialId' => $detectedId]);

                    if ($user) {
                        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
                        $tokenStorage->setToken($token);
                        $request->getSession()->set('_security_main', serialize($token));
                        return new JsonResponse(['success' => true]);
                    }

                    // --- ERREUR DE CORRESPONDANCE BDD ---
                    return new JsonResponse([
                        'success' => false, 
                        'error' => "IA vous a reconnu comme : $detectedName.\nID détecté : $detectedId.\nCet ID n'est pas dans votre MySQL !"
                    ]);
                }
            }

            // --- ERREUR DE RECONNAISSANCE IA ---
            $rawDebug = !empty($data) ? json_encode($data) : "Aucune donnée reçue de Luxand.";
            return new JsonResponse([
                'success' => false, 
                'error' => "L'IA ne reconnaît aucun visage connu. Vérifiez l'éclairage.\nDebug API : " . $rawDebug
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}