<?php
// src/Controller/EmailVerificationController.php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class EmailVerificationController extends AbstractController
{
    #[Route('/register/send-code', name: 'app_register_send_code', methods: ['POST'])]
    public function sendVerificationCode(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $email = $data['email'] ?? '';
        $nom = $data['nom'] ?? '';
        $prenom = $data['prenom'] ?? '';
        
        // Basic validation
        if (!$email || !$nom || !$prenom) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Tous les champs sont requis.'
            ], 400);
        }

        // Generate 6-digit verification code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store code in session with email
        $session = $request->getSession();
        $session->set('verification_email', $email);
        $session->set('verification_code', $code);
        $session->set('verification_nom', $nom);
        $session->set('verification_prenom', $prenom);
        $session->set('verification_password', $data['password'] ?? '');
        $session->set('verification_expires', time() + 600); // 10 minutes

        // Send email with code
        if ($this->sendVerificationEmail($email, $code, $nom, $prenom)) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Code de vérification envoyé à votre email.'
            ]);
        } else {
            return new JsonResponse([
                'success' => false,
                'error' => "L'envoi de l'email a échoué. Veuillez réessayer."
            ], 500);
        }
    }

    #[Route('/register/verify-code', name: 'app_register_verify_code', methods: ['POST'])]
    public function verifyCode(
        Request $request, 
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $submittedCode = $data['code'] ?? '';
        
        $session = $request->getSession();
        $storedCode = $session->get('verification_code');
        $email = $session->get('verification_email');
        $expires = $session->get('verification_expires', 0);

        // Check if code exists and matches
        if (!$storedCode || $storedCode !== $submittedCode) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Code de vérification invalide.'
            ], 400);
        }

        // Check if code expired
        if (time() > $expires) {
            // Clear session
            $session->remove('verification_code');
            $session->remove('verification_email');
            $session->remove('verification_expires');
            
            return new JsonResponse([
                'success' => false,
                'error' => 'Le code a expiré. Veuillez demander un nouveau code.'
            ], 400);
        }

        // Check if email already exists
        $existingUser = $userRepository->findOneBy(['email' => $email]);
        if ($existingUser) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Cet email est déjà utilisé.'
            ], 400);
        }

        // Create new user
        $user = new User();
        $user->setEmail($email);
        $user->setNom($session->get('verification_nom'));
        $user->setPrenom($session->get('verification_prenom'));
        $user->setPassword(
            $passwordHasher->hashPassword(
                $user, 
                $session->get('verification_password')
            )
        );
        $user->setRoles(['ROLE_MAMAN']);
        $user->setType('MAMAN');
        
        $em->persist($user);
        $em->flush();

        // Clear verification data from session
        $session->remove('verification_code');
        $session->remove('verification_email');
        $session->remove('verification_nom');
        $session->remove('verification_prenom');
        $session->remove('verification_password');
        $session->remove('verification_expires');

        // Auto-login the user
        return new JsonResponse([
            'success' => true,
            'message' => 'Compte créé avec succès!'
        ]);
    }

    #[Route('/register/resend-code', name: 'app_register_resend_code', methods: ['POST'])]
    public function resendCode(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        
        $session = $request->getSession();
        $storedEmail = $session->get('verification_email');

        if (!$storedEmail || $storedEmail !== $email) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Demande de vérification non trouvée.'
            ], 400);
        }

        // Generate new code
        $newCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Update session
        $session->set('verification_code', $newCode);
        $session->set('verification_expires', time() + 600);

        // Resend email
        if ($this->sendVerificationEmail(
            $email, 
            $newCode,
            $session->get('verification_nom'),
            $session->get('verification_prenom')
        )) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Nouveau code envoyé.'
            ]);
        } else {
            return new JsonResponse([
                'success' => false,
                'error' => "L'envoi de l'email a échoué."
            ], 500);
        }
    }

    private function sendVerificationEmail(string $toEmail, string $code, string $nom, string $prenom): bool
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'malekbensassi321@gmail.com';
            $mail->Password   = 'rbmv rfrn xeei bcmk';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('no-reply@maternia.tn', 'Maternia');
            $mail->addAddress($toEmail);

            $mail->isHTML(true);
            $mail->Subject = 'Code de vérification - Maternia';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #d53f8c;'>Bonjour $prenom $nom,</h2>
                    <p>Bienvenue sur Maternia! Voici votre code de vérification :</p>
                    <div style='background-color: #f3f4f6; padding: 20px; text-align: center; border-radius: 10px;'>
                        <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #d53f8c;'>$code</span>
                    </div>
                    <p style='color: #6b7280; font-size: 14px;'>Ce code est valable 10 minutes.</p>
                </div>";

            return $mail->send();
        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }
}