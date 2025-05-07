<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EmpruntRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Emprunt;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Get the user's role
        $roles = $user->getRoles();
        $role = str_replace('ROLE_', '', $roles[0]);

        // Redirect based on role
        switch ($role) {
            case 'ADMIN':
                return $this->redirectToRoute('app_admin_dashboard');
            case 'ATHLETE':
                return $this->redirectToRoute('app_athlete_dashboard');
            case 'ORGANIZER':
                return $this->redirectToRoute('app_organizer_dashboard');
            default:
                return $this->redirectToRoute('app_user_dashboard');
        }
    }

    #[Route('/admin/dashboard', name: 'app_admin_dashboard')]
    public function adminDashboard(EmpruntRepository $empruntRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('dashboard/admin.html.twig');
    }
    
    #[Route('/user/dashboard', name: 'app_user_dashboard')]
    public function userDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        return $this->render('dashboard/user.html.twig');
    }

    #[Route('/athlete/dashboard', name: 'app_athlete_dashboard')]
    public function athleteDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        return $this->render('dashboard/athlete.html.twig');
    }

    #[Route('/organizer/dashboard', name: 'app_organizer_dashboard')]
    public function organizerDashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ORGANIZER');
        return $this->render('dashboard/organizer.html.twig');
    }


    #[Route('/api/emprunts', name: 'api_emprunts', methods: ['GET'])]
    public function getEmprunts(EmpruntRepository $empruntRepository): JsonResponse
    {
        // Fetch emprunts from the database
        $emprunts = $empruntRepository->findAll();
    
        // Prepare the events for FullCalendar
        $events = [];
        foreach ($emprunts as $emprunt) {
            $events[] = [
                'title' => 'Emprunt: ' . $emprunt->getMaterielId()->getType(),
                'start' => $emprunt->getDateEmprunt()->format('Y-m-d'),
                'end' => $emprunt->getDateRetour()->format('Y-m-d'),
            ];
        }
    
        return new JsonResponse($events);
    }
    
    #[Route('/admin/Empruntcalendar', name: 'app_admin_calendar')]
    public function CalendarDashboard(EmpruntRepository $empruntRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Fetch all emprunts from the repository
        $emprunts = $empruntRepository->findAll();

        // Prepare the events for FullCalendar
        $events = [];
        foreach ($emprunts as $emprunt) {
            $events[] = [
                'title' => 'Emprunt: ' . $emprunt->getMaterielId()->getType(),
                'start' => $emprunt->getDateEmprunt()->format('Y-m-d'),
                'end' => $emprunt->getDateRetour()->format('Y-m-d'),
            ];
        }

        // Pass events to the Twig template
        return $this->render('emprunt/calenderEmprunt.html.twig', [
            'events' => $events,  // Pass 'events' correctly to the template
        ]);
    }


} 