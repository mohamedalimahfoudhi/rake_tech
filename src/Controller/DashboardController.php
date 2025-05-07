<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function adminDashboard(): Response
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
} 