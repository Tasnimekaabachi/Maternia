<?php

namespace App\Controller\Admin;

use App\Repository\OffreBabySitterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
final class MessagerieController extends AbstractController
{
    #[Route('/messagerie', name: 'messagerie', methods: ['GET'])]
    public function index(OffreBabySitterRepository $offreRepo): Response
    {
        return $this->render('admin/messagerie.html.twig', [
            'offres_babysitter' => $offreRepo->findBy([], ['nomBabysitter' => 'ASC']),
        ]);
    }
}
