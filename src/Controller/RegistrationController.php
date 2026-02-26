<?php
// src/Controller/RegistrationController.php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        // If already logged in, redirect to profile
        if ($this->getUser()) {
            return $this->redirectToRoute('app_user_profile');
        }

        $user = new User();
        $user->setType('MAMAN');
        $form = $this->createForm(RegistrationFormType::class, $user);
        
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
            'site_key' => $_ENV['RECAPTCHA_SITE_KEY'] ?? '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'
        ]);
    }
}