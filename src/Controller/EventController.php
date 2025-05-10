<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route('/event')]
final class EventController extends AbstractController
{
  //composer require dompdf/dompdf

    #[Route(name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
    #[Route('/front',name: 'app_event_indexf', methods: ['GET'])]
    public function indexf(EventRepository $eventRepository): Response
    {
        return $this->render('event/indexf.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
    #[Route('/front/{id}/review', name: 'app_event_reviewid', methods: ['GET'])]
    public function calculateAverageRatingForEventByID($id, ReviewRepository $reviewRepository): JsonResponse
    {
        $reviews = $reviewRepository->findBy(['event' => $id]);
        $averageRating = $this->calculateAverageRating($reviews);
    
        return new JsonResponse(['average' => round($averageRating, 1)]);
    }
    
    private function calculateAverageRating($reviews): float
    {
        $totalRating = 0;
        $reviewCount = count($reviews);
    
        foreach ($reviews as $review) {
            $totalRating += $review->getRating();
        }
    
        return $reviewCount > 0 ? $totalRating / $reviewCount : 0;
    }
    #[Route('/cal',name: 'cal', methods: ['GET'])]
    public function calendrier(EventRepository $eventRepository): Response
    {
        return $this->render('event/calendar.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
    #[Route('/calb',name: 'calb', methods: ['GET'])]
    public function calendrierb(EventRepository $eventRepository): Response
    {
        return $this->render('event/calendarb.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }
    #[Route('/api/events', name: 'api_events')]
    public function getEvents(EventRepository $eventRepository): JsonResponse
    {
        $events = $eventRepository->findAll();

        $data = [];

        foreach ($events as $event) {
            $data[] = [
                'id' => $event->getId(),
                'title' => $event->getName(),
                'start' => $event->getStartDate()->format('Y-m-d H:i:s'),
                'end' => $event->getEndDate()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data);
    }
    #[Route('/event/pdf', name: 'event_pdf')]
    public function generatePdf(Environment $twig,EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        // Configuration Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        // Rendu HTML via Twig
        $html = $twig->render('event/pdf.html.twig', [
            'events' => $events
        ]);

        // Générer le PDF
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Retourner la réponse PDF
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="events.pdf"',
        ]);
    }
    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
              
                if ($error->getMessage() === 'Expected argument of type "DateTimeInterface", "null" given') {
                    $form->get('startDate')->addError(new FormError('Please provide a valid start date.'));
                }
            }
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }
    #[Route('/front/{id}', name: 'app_event_showf', methods: ['GET'])]
    public function showf(Event $event): Response
    {
        return $this->render('event/showf.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = $form->getErrors(true);
            foreach ($errors as $error) {
              
                if ($error->getMessage() === 'Expected argument of type "DateTimeInterface", "null" given') {
                    $form->get('startDate')->addError(new FormError('Please provide a valid start date.'));
                }
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
