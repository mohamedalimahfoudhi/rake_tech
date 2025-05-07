<?php
namespace App\Controller;

use App\Entity\Event;
use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\EventRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends AbstractController
{
    #[Route('/event/{id}/review', name: 'event_review')]
    public function review(Event $event, Request $request, EntityManagerInterface $em, ReviewRepository $reviewRepo)
    {
        $review = new Review();
        $review->setEvent($event);
        $review->setUser($this->getUser());

        $form = $this->createForm(ReviewType::class, $review);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($review);
            $em->flush();
            $this->addFlash('success', 'Review added!');
            return $this->redirectToRoute('event_review', ['id' => $event->getId()]);
        }

        $reviews = $reviewRepo->findBy(['event' => $event]);

        return $this->render('review/index.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
            'reviews' => $reviews,
        ]);
    }
}
