<?php

namespace App\Controller;

use App\Entity\Billet;
use App\Entity\Reservation;
use App\Entity\Tournoi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/front')]
class FrontOfficeController extends AbstractController
{
    #[Route('/', name: 'app_front', methods: ['GET'])]
    public function front(EntityManagerInterface $entityManager): Response
    {
        $billets = $entityManager
                ->getRepository(Billet::class)
            ->findBy(['statut' => 'Valide'], ['typebillet' => 'ASC']);
        $reservations = $entityManager
            ->getRepository(Reservation::class)
            ->findBy(['statut' => 'En attente']);
        $tournois = $entityManager
            ->getRepository(Tournoi::class)
            ->findAll();

        return $this->render('frontOffice/index.html.twig', [
            'billets' => $billets,
            'reservations' => $reservations,
            'tournois' => $tournois,
        ]);
    }
} 