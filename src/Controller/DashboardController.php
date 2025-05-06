<?php

namespace App\Controller;

use App\Repository\EvenementRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminDashboard(
        UtilisateurRepository $utilisateurRepository,
        EvenementRepository $evenementRepository,
        \App\Repository\TournoiRepository $tournoiRepository,
        \App\Repository\BilletRepository $billetRepository
    ): Response {
        // Get basic stats
        $userCount = $utilisateurRepository->count([]);
        $eventCount = $evenementRepository->count([]);
        $tournamentCount = $tournoiRepository->count([]);
        $ticketCount = $billetRepository->count([]);
        
        // Get recent users
        $recentUsers = $utilisateurRepository->findBy([], ['ID' => 'DESC'], 5);
        
        return $this->render('admin/dashboard.html.twig', [
            'user_count' => $userCount,
            'event_count' => $eventCount,
            'tournament_count' => $tournamentCount,
            'ticket_count' => $ticketCount,
            'recent_users' => $recentUsers,
            'recent_events' => $evenementRepository->findBy([], ['ID' => 'DESC'], 5),
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function userDashboard(EvenementRepository $evenementRepository): Response
    {
        // Get upcoming events
        $upcomingEvents = $evenementRepository->findBy([], ['ID' => 'DESC'], 5);
        
        return $this->render('home/index.html.twig', [
            'upcoming_events' => $upcomingEvents,
            'user' => $this->getUser(),
        ]);
    }
} 