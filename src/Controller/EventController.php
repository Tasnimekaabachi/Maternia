<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Attendance;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\AttendanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Finder\Finder;
use Dompdf\Dompdf;
use Dompdf\Options;

class EventController extends AbstractController
{
    #[Route('/admin/event/pdf', name: 'app_event_pdf', methods: ['GET'])]
    public function pdf(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        $html = $this->renderView('admin/event/pdf.html.twig', [
            'events' => $events,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="events-maternia.pdf"',
            ]
        );
    }

    #[Route('/admin/event/csv', name: 'app_event_csv', methods: ['GET'])]
    public function csv(EventRepository $eventRepository): StreamedResponse
    {
        $events = $eventRepository->findAll();

        $response = new StreamedResponse(function () use ($events) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Titre',
                'Description',
                'Type',
                'Date de début (Ponctuel)',
                'Heure de début (Ponctuel)',
                'Date de fin (Ponctuel)',
                'Heure de fin (Ponctuel)',
                'Jour (Hebdomadaire)',
                'Heure de début (Hebdomadaire)',
                'Heure de fin (Hebdomadaire)',
                'Lieu',
                'Catégorie',
                'Capacité',
                'Participants',
                'Statut'
            ]);

            $daysMap = [
                'Monday' => 'Lundi',
                'Tuesday' => 'Mardi',
                'Wednesday' => 'Mercredi',
                'Thursday' => 'Jeudi',
                'Friday' => 'Vendredi',
                'Saturday' => 'Samedi',
                'Sunday' => 'Dimanche'
            ];

            foreach ($events as $event) {
                $status = 'À venir';
                if (!$event->isWeekly() && $event->getEndAt() < new \DateTime()) {
                    $status = 'Terminé';
                } elseif ($event->isWeekly()) {
                    $status = 'Hebdomadaire';
                }

                $type = $event->isWeekly() ? 'Hebdomadaire' : 'Ponctuel';

                $startDate = $event->getStartAt()?->format('Y-m-d') ?? '';
                $startTimeOneTime = $event->getStartAt()?->format('H:i') ?? '';
                $endDate = $event->getEndAt()?->format('Y-m-d') ?? '';
                $endTimeOneTime = $event->getEndAt()?->format('H:i') ?? '';

                $dayOfWeek = $event->getDayOfWeek() ? ($daysMap[$event->getDayOfWeek()] ?? $event->getDayOfWeek()) : '';
                $startTime = $event->getStartTime()?->format('H:i') ?? '';
                $endTime = $event->getEndTime()?->format('H:i') ?? '';

                fputcsv($handle, [
                    $event->getId(),
                    $event->getTitle(),
                    $event->getDescription(),
                    $type,
                    $startDate,
                    $startTimeOneTime,
                    $endDate,
                    $endTimeOneTime,
                    $dayOfWeek,
                    $startTime,
                    $endTime,
                    $event->getLocation(),
                    $event->getEventCat()?->getName(),
                    $event->getCapacity(),
                    count($event->getAttendances()),
                    $status
                ]);
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="events-maternia.csv"');

        return $response;
    }

    #[Route('/admin/event/', name: 'app_event_index', methods: ['GET'])]
    public function index(Request $request, EventRepository $eventRepository, \App\Repository\EventCatRepository $eventCatRepository): Response
    {
        $searchTerm = (string) $request->query->get('search', '');
        $sortBy = (string) $request->query->get('sort_by', 'startAt');
        $sortOrder = (string) $request->query->get('sort_order', 'DESC');

        $categoryId = $request->query->get('category_id');
        $status = (string) $request->query->get('status', '');
        $organizer = (string) $request->query->get('organizer', '');

        $events = $eventRepository->findWithSearchAndSort(
            $searchTerm,
            $sortBy,
            $sortOrder,
            $categoryId ? (int) $categoryId : null,
            $status,
            $organizer
        );

        $categories = $eventCatRepository->findAll();

        return $this->render('admin/event/index.html.twig', [
            'events' => $events,
            'categories' => $categories,
            'searchTerm' => $searchTerm,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'currentCategoryId' => $categoryId,
            'currentStatus' => $status,
            'currentOrganizer' => $organizer,
        ]);
    }

    #[Route('/admin/event/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $existingImages = $this->getExistingImages();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($event->isWeekly()) {
                $event->setStartAt(null);
                $event->setEndAt(null);
            } else {
                $event->setDayOfWeek(null);
                $event->setStartTime(null);
                $event->setEndTime(null);
            }

            if ($form->isValid()) {
                $selectedImage = $form->get('selectedImage')->getData();
                if ($selectedImage) {
                    $event->setImage($selectedImage);
                }

                $user = $this->getUser();
                if ($user instanceof \App\Entity\User) {
                    $event->setCreator($user);
                }

                $entityManager->persist($event);
                $entityManager->flush();

                $this->addFlash('success', 'L\'événement a été créé avec succès.');
                return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('admin/event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'existingImages' => $existingImages,
        ]);
    }

    #[Route('/event/new', name: 'app_event_user_new', methods: ['GET', 'POST'])]
    public function new1(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);

        $existingImages = $this->getExistingImages();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Conditional nulling based on isWeekly (before validation)
            if ($event->isWeekly()) {
                $event->setStartAt(null);
                $event->setEndAt(null);
            } else {
                $event->setDayOfWeek(null);
                $event->setStartTime(null);
                $event->setEndTime(null);
            }

            if ($form->isValid()) {
                $selectedImage = $form->get('selectedImage')->getData();

                if ($selectedImage) {
                    $event->setImage($selectedImage);
                }

                $user = $this->getUser();
                if ($user instanceof \App\Entity\User) {
                    $event->setCreator($user);
                }

                $entityManager->persist($event);
                $entityManager->flush();

                $email = (new Email())
                    ->from('contact@maternia.fr')
                    ->to($this->getUser()->getUserIdentifier())
                    ->subject('Votre événement est en ligne : ' . $event->getTitle())
                    ->html($this->renderView('emails/event_created_confirmation.html.twig', [
                        'event' => $event,
                        'user' => $this->getUser(),
                    ]));
                $mailer->send($email);

                $this->addFlash('success', 'Votre événement a été créé avec succès.');
                return $this->redirectToRoute('app_event_user_show', ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('event/usernew.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'existingImages' => $existingImages,
        ]);
    }

    #[Route('/event/{id}', name: 'app_event_user_show', methods: ['GET'])]
    public function show1(Event $event): Response
    {
        return $this->render('event/usershow.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/admin/event/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('admin/event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/admin/event/{id}/edit', name: 'admin_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $currentImage = $event->getImage();
        $form = $this->createForm(EventType::class, $event);
        $existingImages = $this->getExistingImages();

        if ($currentImage) {
            $form->get('selectedImage')->setData($currentImage);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Conditional nulling based on isWeekly (before validation)
            if ($event->isWeekly()) {
                $event->setStartAt(null);
                $event->setEndAt(null);
            } else {
                $event->setDayOfWeek(null);
                $event->setStartTime(null);
                $event->setEndTime(null);
            }

            if ($form->isValid()) {
                $selectedImage = $form->get('selectedImage')->getData();

                if ($selectedImage && $selectedImage !== $currentImage) {
                    $event->setImage($selectedImage);
                }

                $entityManager->flush();

                $this->addFlash('success', 'L\'événement a été modifié avec succès.');
                return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('admin/event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'existingImages' => $existingImages,
            'currentImage' => $currentImage,
        ]);
    }

    #[Route('/event/{id}/edit', name: 'app_event_user_edit', methods: ['GET', 'POST'])]
    public function userEdit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($event->getCreator() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cet événement.');
            return $this->redirectToRoute('app_events');
        }

        $currentImage = $event->getImage();
        $form = $this->createForm(EventType::class, $event);
        $existingImages = $this->getExistingImages();

        if ($currentImage) {
            $form->get('selectedImage')->setData($currentImage);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedImage = $form->get('selectedImage')->getData();

            if ($selectedImage && $selectedImage !== $currentImage) {
                $event->setImage($selectedImage);
            }

            if ($event->isWeekly()) {
                $event->setStartAt(null);
                $event->setEndAt(null);
            } else {
                $event->setDayOfWeek(null);
                $event->setStartTime(null);
                $event->setEndTime(null);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre événement a été modifié avec succès.');
            return $this->redirectToRoute('app_event_user_show', ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/useredit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'existingImages' => $existingImages,
            'currentImage' => $currentImage,
        ]);
    }

    #[Route('/admin/event/{id}', name: 'admin_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $event->getId(), is_string($token) ? $token : null)) {
            $entityManager->remove($event);
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été supprimé.');
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/event/{id}/delete', name: 'app_event_user_delete', methods: ['POST'])]
    public function userDelete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($event->getCreator() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cet événement.');
            return $this->redirectToRoute('app_events');
        }

        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $event->getId(), is_string($token) ? $token : null)) {
            $entityManager->remove($event);
            $entityManager->flush();
            $this->addFlash('success', 'Votre événement a été supprimé.');
        }

        return $this->redirectToRoute('app_events', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/event/{id}/attend', name: 'app_event_attend', methods: ['POST'])]
    public function attend(Event $event, EntityManagerInterface $entityManager, AttendanceRepository $attendanceRepository, MailerInterface $mailer): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour participer à un événement.');
            return $this->redirectToRoute('app_login');
        }

        $existing = $attendanceRepository->findOneBy(['user' => $user, 'event' => $event]);
        if ($existing) {
            $this->addFlash('warning', 'Vous participez déjà à cet événement.');
            return $this->redirectToRoute('app_event_user_show', ['id' => $event->getId()]);
        }

        if (!$event->isWeekly() && $event->getEndAt() < new \DateTime()) {
            $this->addFlash('error', 'Cet événement est déjà terminé.');
            return $this->redirectToRoute('app_event_user_show', ['id' => $event->getId()]);
        }

        if ($event->getCapacity() !== null && $event->getAttendances()->count() >= $event->getCapacity()) {
            $this->addFlash('error', 'Cet événement est complet.');
            return $this->redirectToRoute('app_event_user_show', ['id' => $event->getId()]);
        }

        $attendance = new Attendance();
        if ($user instanceof \App\Entity\User) {
            $attendance->setUser($user);
        }
        $attendance->setEvent($event);

        $entityManager->persist($attendance);
        $entityManager->flush();

        $emailParticipant = (new Email())
            ->from('contact@maternia.fr')
            ->to($user->getUserIdentifier())
            ->subject('Confirmation de participation : ' . $event->getTitle())
            ->html($this->renderView('emails/attendance_confirmation.html.twig', [
                'event' => $event,
                'user' => $user,
                'attendance' => $attendance,
            ]));
        $mailer->send($emailParticipant);

        $this->addFlash('success', 'Votre participation a été enregistrée et un email de confirmation vous a été envoyé.');
        return $this->redirectToRoute('app_event_user_show', ['id' => $event->getId()]);
    }

    #[Route('/event/{id}/unattend', name: 'app_event_unattend', methods: ['POST'])]
    public function unattend(Event $event, EntityManagerInterface $entityManager, AttendanceRepository $attendanceRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $attendance = $attendanceRepository->findOneBy(['user' => $user, 'event' => $event]);
        if ($attendance) {
            $entityManager->remove($attendance);
            $entityManager->flush();
            $this->addFlash('success', 'Votre participation a été annulée.');
        }

        return $this->redirectToRoute('app_event_user_show', ['id' => $event->getId()]);
    }

    #[Route('/event/{id}/ics', name: 'app_event_ics', methods: ['GET'])]
    public function icsExport(Event $event): Response
    {
        $dtStart = $event->isWeekly() ? $event->getStartTime() : $event->getStartAt();
        $dtEnd = $event->isWeekly() ? $event->getEndTime() : $event->getEndAt();

        if (!$dtStart) {
            $this->addFlash('error', 'Impossible de générer le calendrier (dates manquantes).');
            return $this->redirectToRoute('app_event_user_show', ['id' => $event->getId()]);
        }

        $ics = [
            "BEGIN:VCALENDAR",
            "VERSION:2.0",
            "PRODID:-//Maternia//Event Calendar//FR",
            "BEGIN:VEVENT",
            "UID:" . uniqid() . "@maternia.com",
            "DTSTAMP:" . date('Ymd\THis\Z'),
            "DTSTART:" . $dtStart->format('Ymd\THis\Z'),
            "DTEND:" . ($dtEnd ? $dtEnd->format('Ymd\THis\Z') : $dtStart->format('Ymd\THis\Z')),
            "SUMMARY:" . $event->getTitle(),
            "DESCRIPTION:" . str_replace(["\r", "\n"], ["", "\\n"], $event->getDescription()),
            "LOCATION:" . $event->getLocation(),
            "END:VEVENT",
            "END:VCALENDAR"
        ];

        $response = new Response(implode("\r\n", $ics));
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="event-' . $event->getId() . '.ics"');

        return $response;
    }

    #[Route('/attendance/{id}/pass', name: 'app_attendance_pass', methods: ['GET'])]
    public function attendancePass(Attendance $attendance): Response
    {
        return $this->render('event/attendance_pass.html.twig', [
            'attendance' => $attendance,
            'event' => $attendance->getEvent(),
            'user' => $attendance->getUser(),
        ]);
    }

    #[Route('/admin/attendance/{id}/remove', name: 'admin_attendance_remove', methods: ['POST'])]
    public function removeParticipant(Request $request, Attendance $attendance, EntityManagerInterface $entityManager): Response
    {

        $eventId = $attendance->getEvent()->getId();

        if ($this->isCsrfTokenValid('remove' . $attendance->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($attendance);
            $entityManager->flush();

            $this->addFlash('success', 'Le participant a été retiré de l\'événement.');
        }

        return $this->redirectToRoute('app_event_show', ['id' => $eventId]);
    }

    public function publicEvents(EventRepository $eventRepository, ?int $limit = null): Response
    {
        $events = $eventRepository->findUpcomingEvents($limit);

        return $this->render('event/_public_list.html.twig', [
            'events' => $events,
            'limit' => $limit
        ]);
    }

    /**
     * @return string[]
     */
    private function getExistingImages(): array
    {
        $images = [];
        $projectDir = $this->getParameter('kernel.project_dir');
        if (!is_string($projectDir)) {
            throw new \RuntimeException('Project directory must be a string');
        }
        $imgDir = $projectDir . '/public/img/events/';

        if (file_exists($imgDir)) {
            $finder = new Finder();
            $finder->files()->in($imgDir)->name(['*.jpg', '*.jpeg', '*.png', '*.gif', '*.webp']);

            foreach ($finder as $file) {
                $images[] = $file->getFilename();
            }

            sort($images);
        }

        return $images;
    }
}