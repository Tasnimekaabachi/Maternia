<?php

namespace App\Controller\Admin;

use App\Entity\ConsultationCreneau;
use App\Entity\Consultation;
use App\Form\ConsultationCreneauType;
use App\Repository\ConsultationCreneauRepository;
use App\Repository\ConsultationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/consultation-creneaux')]
class ConsultationCreneauCrudController extends AbstractController
{
    #[Route('/', name: 'app_admin_consultation_creneau_index', methods: ['GET'])]
    public function index(Request $request, ConsultationCreneauRepository $creneauRepository, ConsultationRepository $consultationRepository): Response
    {
        $searchTerm = $request->query->get('q');
        $creneaux = $creneauRepository->searchAllOrdered($searchTerm);
        
        // --- STATISTIQUES "WAW" ---
        $stats = [
            'total' => count($creneaux),
            'disponibles' => 0,
            'reserves' => 0,
            'parJour' => [], // Pour le graphique d'activité hebdomadaire
            'topMedecins' => [], // Pour le classement
        ];

        $days = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        foreach ($days as $day) { $stats['parJour'][$day] = 0; }

        foreach ($creneaux as $c) {
            // Disponibilité
            if ($c->getStatutReservation() === 'DISPONIBLE') {
                $stats['disponibles']++;
            } else {
                $stats['reserves']++;
            }

            // Répartition par jour (si renseigné)
            if ($c->getJour()) {
                $dayNameFr = $this->getFrenchDayName($c->getJour());
                if (isset($stats['parJour'][$dayNameFr])) {
                    $stats['parJour'][$dayNameFr]++;
                }
            }

            // Top Médecins
            $medName = $c->getNomMedecin() ?: 'Inconnu';
            $stats['topMedecins'][$medName] = ($stats['topMedecins'][$medName] ?? 0) + 1;
        }

        arsort($stats['topMedecins']);
        $stats['topMedecins'] = array_slice($stats['topMedecins'], 0, 5);

        return $this->render('admin/consultation_creneau/index.html.twig', [
            'creneaux' => $creneaux,
            'stats' => $stats,
            'parJourLabels' => array_keys($stats['parJour']),
            'parJourValues' => array_values($stats['parJour']),
            'searchTerm' => $searchTerm ? trim($searchTerm) : null,
        ]);
    }

    private function getFrenchDayName(\DateTimeInterface $date): string
    {
        $days = [
            'Sunday' => 'Dimanche', 'Monday' => 'Lundi', 'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi', 'Thursday' => 'Jeudi', 'Friday' => 'Vendredi',
            'Saturday' => 'Samedi'
        ];
        return $days[$date->format('l')] ?? $date->format('l');
    }

    private function processPhotoUpload(?UploadedFile $file): ?string
    {
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return null;
        }
        $projectDir = $this->getParameter('kernel.project_dir');
        $uploadDir = $projectDir . '/public/uploads/medecins';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'jpg';
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'photo';
        $fileName = $safeName . '_' . uniqid('', true) . '.' . strtolower($ext);
        try {
            $file->move($uploadDir, $fileName);
            return $fileName;
        } catch (FileException $e) {
            return null;
        }
    }

    #[Route('/new', name: 'app_admin_consultation_creneau_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager,
        ConsultationRepository $consultationRepository
    ): Response {
        $consultationCreneau = new ConsultationCreneau();
        $consultationCreneau->setCreatedAt(new \DateTime());
        $consultationCreneau->setUpdatedAt(new \DateTime());
        $consultationCreneau->setStatutReservation('DISPONIBLE');
        
        $form = $this->createForm(ConsultationCreneauType::class, $consultationCreneau, [
            'consultations' => $consultationRepository->findAllOrdered()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Upload photo médecin : enregistrement physique + nom en BDD
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $photoFileName = $this->processPhotoUpload($photoFile);
                if ($photoFileName) {
                    $consultationCreneau->setPhotoMedecin($photoFileName);
                }
            }

            // Récupérer la collection de créneaux horaires
            $creneauxHoraires = $form->get('creneauxHoraires')->getData();
            $creneauxCrees = 0;

            if (!empty($creneauxHoraires)) {
                 foreach ($creneauxHoraires as $idx => $creneauData) {
                    // Ignorer les créneaux vides/incomplets
                    if (empty($creneauData['heureDebut']) || empty($creneauData['heureFin'])) {
                        continue;
                    }
                    
                    $creneau = new ConsultationCreneau();
                    
                    // Copier toutes les informations du formulaire principal
                    $creneau->setConsultation($consultationCreneau->getConsultation());
                    $creneau->setNomMedecin((string)$consultationCreneau->getNomMedecin());
                    $creneau->setPhotoMedecin($consultationCreneau->getPhotoMedecin());
                    $creneau->setDescriptionMedecin($consultationCreneau->getDescriptionMedecin());
                    $creneau->setSpecialiteMedecin($consultationCreneau->getSpecialiteMedecin());
                    $creneau->setStatutReservation('DISPONIBLE'); 
                    
                    // Nouveaux attributs
                    $creneau->setDureeMinutes($consultationCreneau->getDureeMinutes() ?: 30);
                    $creneau->setNombrePlaces($consultationCreneau->getNombrePlaces() ?: 1);

                    $creneau->setCreatedAt(new \DateTime());
                    $creneau->setUpdatedAt(new \DateTime());
                    
                    // Dates
                    $creneau->setJour($creneauData['jour']);
                    $creneau->setHeureDebut($creneauData['heureDebut']);
                    $creneau->setHeureFin($creneauData['heureFin']);

                    // Sécuriser le calcul des dates complètes pour l'affichage front
                    if ($creneauData['jour'] instanceof \DateTimeInterface && $creneauData['heureDebut'] instanceof \DateTimeInterface) {
                        $dateDebut = (clone $creneauData['jour'])->setTime(
                            (int) $creneauData['heureDebut']->format('H'),
                            (int) $creneauData['heureDebut']->format('i'),
                            (int) $creneauData['heureDebut']->format('s')
                        );
                        $creneau->setDateDebut($dateDebut);
                    }

                    if ($creneauData['jour'] instanceof \DateTimeInterface && $creneauData['heureFin'] instanceof \DateTimeInterface) {
                        $dateFin = (clone $creneauData['jour'])->setTime(
                            (int) $creneauData['heureFin']->format('H'),
                            (int) $creneauData['heureFin']->format('i'),
                            (int) $creneauData['heureFin']->format('s')
                        );
                        $creneau->setDateFin($dateFin);
                    }
                    
                    $entityManager->persist($creneau);
                    $creneauxCrees++;
                }
            }
            
            // Si aucun créneau via la collection, on tente un fallback avec les champs simples
            if ($creneauxCrees === 0) {
                if (
                    $consultationCreneau->getJour() instanceof \DateTimeInterface &&
                    $consultationCreneau->getHeureDebut() instanceof \DateTimeInterface &&
                    $consultationCreneau->getHeureFin() instanceof \DateTimeInterface
                ) {
                    $creneau = new ConsultationCreneau();

                    // Copier les informations principales
                    $creneau->setConsultation($consultationCreneau->getConsultation());
                    $creneau->setNomMedecin((string) $consultationCreneau->getNomMedecin());
                    $creneau->setPhotoMedecin($consultationCreneau->getPhotoMedecin());
                    $creneau->setDescriptionMedecin($consultationCreneau->getDescriptionMedecin());
                    $creneau->setSpecialiteMedecin($consultationCreneau->getSpecialiteMedecin());
                    $creneau->setStatutReservation('DISPONIBLE');

                    $creneau->setDureeMinutes($consultationCreneau->getDureeMinutes() ?: 30);
                    $creneau->setNombrePlaces($consultationCreneau->getNombrePlaces() ?: 1);

                    $creneau->setCreatedAt(new \DateTime());
                    $creneau->setUpdatedAt(new \DateTime());

                    // Dates complètes
                    $creneau->setJour($consultationCreneau->getJour());
                    $creneau->setHeureDebut($consultationCreneau->getHeureDebut());
                    $creneau->setHeureFin($consultationCreneau->getHeureFin());

                    $entityManager->persist($creneau);
                    $creneauxCrees = 1;
                } else {
                    $this->addFlash('warning', 'Veuillez ajouter au moins un créneau horaire (via le bouton "Ajouter un créneau" ou les champs de date/heure).');
                    return $this->render('admin/consultation_creneau/new.html.twig', [
                        'creneau' => $consultationCreneau,
                        'form' => $form,
                    ]);
                }
            }

            $entityManager->flush();
            $this->addFlash('success', '✨ ' . $creneauxCrees . ' nouveau(x) créneau(x) ajouté(s) avec succès ! Ils sont maintenant disponibles.');
            return $this->redirectToRoute('app_admin_consultation_creneau_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/consultation_creneau/new.html.twig', [
            'creneau' => $consultationCreneau,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_consultation_creneau_show', methods: ['GET'])]
    public function show(ConsultationCreneau $consultationCreneau): Response
    {
        return $this->render('admin/consultation_creneau/show.html.twig', [
            'creneau' => $consultationCreneau,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_consultation_creneau_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        ConsultationCreneau $consultationCreneau, 
        EntityManagerInterface $entityManager,
        ConsultationRepository $consultationRepository
    ): Response {
        // Mise à jour de la date de modification
        $consultationCreneau->setUpdatedAt(new \DateTime());
        
        $form = $this->createForm(ConsultationCreneauType::class, $consultationCreneau, [
            'consultations' => $consultationRepository->findAllOrdered()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Upload photo médecin si un nouveau fichier est envoyé
            $photoFile = $form->get('photoFile')->getData();
            if ($photoFile) {
                $photoFileName = $this->processPhotoUpload($photoFile);
                if ($photoFileName) {
                    $consultationCreneau->setPhotoMedecin($photoFileName);
                }
            }

            // Processing existing slot
            $consultationCreneau->setUpdatedAt(new \DateTime());
            
            // Processing new additional slots if any
            $creneauxHoraires = $form->get('creneauxHoraires')->getData();
            $creneauxCrees = 0;

            if (!empty($creneauxHoraires)) {
                foreach ($creneauxHoraires as $creneauData) {
                    if (empty($creneauData['heureDebut']) || empty($creneauData['heureFin'])) {
                        continue;
                    }
                    
                    $creneau = new ConsultationCreneau();
                    
                    // Copy info from the current edited slot (doctor, consultation, etc.)
                    $creneau->setConsultation($consultationCreneau->getConsultation());
                    $creneau->setNomMedecin((string)$consultationCreneau->getNomMedecin());
                    $creneau->setPhotoMedecin($consultationCreneau->getPhotoMedecin());
                    $creneau->setDescriptionMedecin($consultationCreneau->getDescriptionMedecin());
                    $creneau->setSpecialiteMedecin($consultationCreneau->getSpecialiteMedecin());
                    $creneau->setStatutReservation('DISPONIBLE'); 
                    
                    $creneau->setDureeMinutes($consultationCreneau->getDureeMinutes() ?: 30);
                    $creneau->setNombrePlaces($consultationCreneau->getNombrePlaces() ?: 1);

                    $creneau->setCreatedAt(new \DateTime());
                    $creneau->setUpdatedAt(new \DateTime());
                    
                    $creneau->setJour($creneauData['jour']);
                    $creneau->setHeureDebut($creneauData['heureDebut']);
                    $creneau->setHeureFin($creneauData['heureFin']);

                    // Sécuriser le calcul des dates complètes pour l'affichage front
                    if ($creneauData['jour'] instanceof \DateTimeInterface && $creneauData['heureDebut'] instanceof \DateTimeInterface) {
                        $dateDebut = (clone $creneauData['jour'])->setTime(
                            (int) $creneauData['heureDebut']->format('H'),
                            (int) $creneauData['heureDebut']->format('i'),
                            (int) $creneauData['heureDebut']->format('s')
                        );
                        $creneau->setDateDebut($dateDebut);
                    }

                    if ($creneauData['jour'] instanceof \DateTimeInterface && $creneauData['heureFin'] instanceof \DateTimeInterface) {
                        $dateFin = (clone $creneauData['jour'])->setTime(
                            (int) $creneauData['heureFin']->format('H'),
                            (int) $creneauData['heureFin']->format('i'),
                            (int) $creneauData['heureFin']->format('s')
                        );
                        $creneau->setDateFin($dateFin);
                    }
                    
                    $entityManager->persist($creneau);
                    $creneauxCrees++;
                }
            }

            $entityManager->flush();

            if ($creneauxCrees > 0) {
                $this->addFlash('success', "💖 Créneau mis à jour et $creneauxCrees nouveau(x) créneau(x) ajouté(s) !");
            } else {
                $this->addFlash('success', '🌸 Modification enregistrée avec succès.');
            }
            
            return $this->redirectToRoute('app_admin_consultation_creneau_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/consultation_creneau/edit.html.twig', [
            'creneau' => $consultationCreneau,
            'form' => $form,
        ]);
    }

  #[Route('/{id}', name: 'app_admin_consultation_creneau_delete', methods: ['POST'])]
public function delete(Request $request, ConsultationCreneau $consultationCreneau, EntityManagerInterface $entityManager): Response
{
    if (!$this->isCsrfTokenValid('delete'.$consultationCreneau->getId(), $request->request->get('_token'))) {
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => false,
                'message' => 'Token CSRF invalide.'
            ], 400);
        }
        
        $this->addFlash('error', 'Token CSRF invalide.');
        return $this->redirectToRoute('app_admin_consultation_creneau_index');
    }

    try {
        $entityManager->remove($consultationCreneau);
        $entityManager->flush();
        
        // Si c'est une requête AJAX
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => 'Créneau supprimé avec succès.',
                'id' => $consultationCreneau->getId()
            ]);
        }
        
        // Sinon, redirection normale
        $this->addFlash('success', 'Créneau supprimé avec succès.');
        
    } catch (\Exception $e) {
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
        
        $this->addFlash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
    }

    return $this->redirectToRoute('app_admin_consultation_creneau_index', [], Response::HTTP_SEE_OTHER);
}

    #[Route('/{id}/reserve', name: 'app_admin_consultation_creneau_reserve', methods: ['POST'])]
    public function reserve(Request $request, ConsultationCreneau $creneau, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('reserve'.$creneau->getId(), $request->request->get('_token'))) {
            $creneau->setStatutReservation('RESERVE');
            $creneau->setUpdatedAt(new \DateTime());
            
            $entityManager->flush();
            $this->addFlash('success', 'Créneau marqué comme réservé.');
        }

        return $this->redirectToRoute('app_admin_consultation_creneau_show', ['id' => $creneau->getId()]);
    }

    #[Route('/{id}/liberer', name: 'app_admin_consultation_creneau_liberer', methods: ['POST'])]
    public function liberer(Request $request, ConsultationCreneau $creneau, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('liberer'.$creneau->getId(), $request->request->get('_token'))) {
            $creneau->setStatutReservation('DISPONIBLE');
            
            // Supprimer la réservation associée si elle existe
            if ($creneau->getReservation()) {
                $entityManager->remove($creneau->getReservation());
                $creneau->setReservation(null);
            }
            
            $creneau->setUpdatedAt(new \DateTime());
            
            $entityManager->flush();
            $this->addFlash('success', 'Créneau libéré avec succès.');
        }

        return $this->redirectToRoute('app_admin_consultation_creneau_show', ['id' => $creneau->getId()]);
    }
}