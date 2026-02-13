<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function index(Request $request, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // 1. Générer un mot de passe temporaire lisible
                $tempPassword = bin2hex(random_bytes(4)); 
                
                // 2. Mettre à jour l'utilisateur en base
                $user->setPassword($hasher->hashPassword($user, $tempPassword));
                $em->flush();

                // 3. Envoyer l'email
                if ($this->sendEmail($user->getEmail(), $tempPassword)) {
                    $this->addFlash('success', 'Un nouveau mot de passe a été envoyé à votre adresse.');
                } else {
                    $this->addFlash('error', "L'envoi de l'email a échoué.");
                }
            } else {
                $this->addFlash('error', 'Aucun compte associé à cet email.');
            }
        }

        return $this->render('security/forgot_password.html.twig');
    }

    private function sendEmail(string $toEmail, string $password): bool
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

            $mail->setFrom('no-reply@maternia.tn', 'Maternia Support');
            $mail->addAddress($toEmail);

            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de votre mot de passe - Maternia';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif;'>
                    <h2>Bonjour,</h2>
                    <p>Vous avez demandé une réinitialisation de mot de passe.</p>
                    <p>Votre mot de passe temporaire est : <strong style='color: #d53f8c; font-size: 1.2em;'>$password</strong></p>
                    <p>Veuillez vous connecter et le modifier dans votre profil dès que possible.</p>
                </div>";

            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }
}