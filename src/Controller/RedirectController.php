<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RedirectController extends AbstractController
{
    #[Route('/admin/athlete/tickets', name: 'redirect_athlete_tickets')]
    public function redirectAthleteTickets(): Response
    {
        return $this->redirectToRoute('app_athlete_tickets');
    }
} 