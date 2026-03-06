<?php

namespace App\Controller\Api;

use App\Entity\Reservation;
use App\Repository\OffreBabySitterRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/reservations', name: 'api_reservations_')]
final class ReservationApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, ReservationRepository $reservationRepo, OffreBabySitterRepository $offreRepo): JsonResponse
    {
        $email = $request->query->get('email');
        $offreId = $request->query->get('offre_id');
        $debut = $request->query->get('debut');
        $fin = $request->query->get('fin');
        if ($debut !== null && $fin !== null) {
            try {
                $dateDebut = new \DateTimeImmutable($debut);
                $dateFin = new \DateTimeImmutable($fin);
            } catch (\Exception) {
                return $this->json(['error' => 'Dates debut/fin invalides.'], 400);
            }
            $offreIdInt = $offreId !== null && $offreId !== '' ? (int) $offreId : null;
            $reservations = $reservationRepo->findByDateRange($dateDebut, $dateFin, $offreIdInt);
        } elseif ($email !== null && $email !== '') {
            $reservations = $reservationRepo->findByParentEmail($email);
        } elseif ($offreId !== null && $offreId !== '') {
            $offre = $offreRepo->find((int) $offreId);
            if (!$offre) {
                return $this->json(['error' => 'Offre introuvable.'], 404);
            }
            $reservations = $reservationRepo->findByOffre($offre);
        } else {
            return $this->json(['error' => 'Préciser email, offre_id ou debut+fin.'], 400);
        }
        $data = array_map(static function (Reservation $r) {
            return [
                'id' => $r->getId(),
                'offre_id' => $r->getOffre()?->getId(),
                'offre_nom' => $r->getOffre()?->getNomBabysitter(),
                'parent_email' => $r->getParentEmail(),
                'parent_name' => $r->getParentName(),
                'date_debut' => $r->getDateDebut()?->format('Y-m-d\TH:i:sP'),
                'date_fin' => $r->getDateFin()?->format('Y-m-d\TH:i:sP'),
                'statut' => $r->getStatut(),
                'message' => $r->getMessage(),
                'created_at' => $r->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }, $reservations);
        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, OffreBabySitterRepository $offreRepo): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];
        $offreId = (int) ($body['offre_id'] ?? 0);
        $parentEmail = (string) ($body['parent_email'] ?? '');
        $parentName = (string) ($body['parent_name'] ?? '');
        $dateDebutStr = (string) ($body['date_debut'] ?? '');
        $dateFinStr = (string) ($body['date_fin'] ?? '');
        $message = (string) ($body['message'] ?? '');
        if ($offreId <= 0 || $parentEmail === '' || $parentName === '' || $dateDebutStr === '' || $dateFinStr === '') {
            return $this->json(['error' => 'offre_id, parent_email, parent_name, date_debut, date_fin requis.'], 400);
        }
        $offre = $offreRepo->find($offreId);
        if (!$offre) {
            return $this->json(['error' => 'Offre introuvable.'], 404);
        }
        try {
            $dateDebut = new \DateTime($dateDebutStr);
            $dateFin = new \DateTime($dateFinStr);
        } catch (\Exception) {
            return $this->json(['error' => 'Format date_debut/date_fin invalide (ISO 8601).'], 400);
        }
        if ($dateFin <= $dateDebut) {
            return $this->json(['error' => 'date_fin doit être après date_debut.'], 400);
        }
        $reservation = new Reservation();
        $reservation->setOffre($offre);
        $reservation->setParentEmail($parentEmail);
        $reservation->setParentName($parentName);
        $reservation->setDateDebut($dateDebut);
        $reservation->setDateFin($dateFin);
        $reservation->setMessage($message !== '' ? $message : null);
        $em->persist($reservation);
        $em->flush();
        return $this->json([
            'id' => $reservation->getId(),
            'offre_id' => $reservation->getOffre()?->getId(),
            'parent_email' => $reservation->getParentEmail(),
            'parent_name' => $reservation->getParentName(),
            'date_debut' => $reservation->getDateDebut()?->format('Y-m-d\TH:i:sP'),
            'date_fin' => $reservation->getDateFin()?->format('Y-m-d\TH:i:sP'),
            'statut' => $reservation->getStatut(),
            'created_at' => $reservation->getCreatedAt()?->format(\DateTimeInterface::ATOM),
        ], 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH', 'PUT'])]
    public function update(int $id, Request $request, ReservationRepository $reservationRepo, EntityManagerInterface $em): JsonResponse
    {
        $reservation = $reservationRepo->find($id);
        if (!$reservation) {
            return $this->json(['error' => 'Réservation introuvable.'], 404);
        }
        $body = json_decode($request->getContent(), true) ?? [];
        $statut = (string) ($body['statut'] ?? '');
        $statuts = [Reservation::STATUT_DEMANDE, Reservation::STATUT_ACCEPTEE, Reservation::STATUT_REFUSEE, Reservation::STATUT_ANNULEE];
        if ($statut !== '' && \in_array($statut, $statuts, true)) {
            $reservation->setStatut($statut);
        }
        $em->flush();
        return $this->json([
            'id' => $reservation->getId(),
            'statut' => $reservation->getStatut(),
            'date_debut' => $reservation->getDateDebut()?->format('Y-m-d\TH:i:sP'),
            'date_fin' => $reservation->getDateFin()?->format('Y-m-d\TH:i:sP'),
        ]);
    }
}
