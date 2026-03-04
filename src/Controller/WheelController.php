<?php

namespace App\Controller;

use App\Entity\PromoCode;
use App\Repository\PromoCodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/wheel')]
final class WheelController extends AbstractController
{
    #[Route('', name: 'app_wheel', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/wheel.html.twig');
    }

    #[Route('/spin', name: 'app_wheel_spin', methods: ['POST'])]
    public function spin(
        Request $request,
        EntityManagerInterface $entityManager,
        PromoCodeRepository $promoCodeRepository
    ): JsonResponse {
        $email = trim((string) $request->request->get('email'));

        if ($email === '') {
            return $this->json([
                'result' => 'email_required',
                'message' => 'Merci de saisir votre email avant de tourner la roue.',
            ]);
        }

        $now = new \DateTimeImmutable();
        $countThisMonth = $promoCodeRepository->countForEmailInMonth($email, $now);
        if ($countThisMonth >= 3) {
            return $this->json([
                'result' => 'limit',
                'message' => 'Vous avez déjà obtenu 3 codes ce mois-ci.',
            ]);
        }

        // Tirage aléatoire : 0 = rien, 1 = -5%, 2 = -10%
        $roll = random_int(0, 2);

        if ($roll === 0) {
            return $this->json([
                'result' => 'none',
            ]);
        }

        $discount = $roll === 2 ? 10 : 5;

        // Génération d'un code unique simple
        do {
            $code = sprintf('MATER-%d-%s', $discount, strtoupper(bin2hex(random_bytes(3))));
        } while ($promoCodeRepository->findOneBy(['code' => $code]) !== null);

        $promo = new PromoCode($code, $discount, $email !== '' ? $email : null);
        $entityManager->persist($promo);
        $entityManager->flush();

        return $this->json([
            'result' => 'win',
            'percent' => $discount,
            'code' => $code,
        ]);
    }
}

