<?php

namespace App\Service;

use App\Repository\TournoiRepository;
use App\Repository\UserRepository;

class TournoiChatbotService
{
    private $tournoiRepository;
    private $userRepository;

    public function __construct(TournoiRepository $tournoiRepository, UserRepository $userRepository)
    {
        $this->tournoiRepository = $tournoiRepository;
        $this->userRepository = $userRepository;
    }

    // Répondre au statut d'un tournoi
    public function getTournoiStatus(string $tournoiName): string {
        // Récupérer les informations du tournoi depuis la base de données
        $tournoi = $this->tournoiRepository->findOneBy(['nom' => $tournoiName]);
        if (!$tournoi) {
            return "Désolé, je n'ai pas trouvé ce tournoi.";
        }
        return "Le tournoi {$tournoi->getNom()} est actuellement {$tournoi->getStatut()}.";
    }

    // Répondre à la date d'un tournoi
    public function getTournoiDate(string $tournoiName): string {
        // Nettoyer le nom du tournoi pour éliminer les espaces ou les différences de casse
        $tournoiName = strtolower(trim($tournoiName));
    
    
        // Rechercher le tournoi en fonction du nom nettoyé
        $tournoi = $this->tournoiRepository->createQueryBuilder('t')
            ->where('LOWER(t.nom) LIKE :tournoiName')
            ->setParameter('tournoiName', '%' . $tournoiName . '%')  // Recherche partielle
            ->getQuery()
            ->getOneOrNullResult();
    
        if (!$tournoi) {
            return "Désolé, je n'ai pas trouvé ce tournoi.";
        }
    
        return "Le tournoi {$tournoi->getNom()} se déroule du " . $tournoi->getDatedebut()->format('d/m/Y') . " au " . $tournoi->getDatefin()->format('d/m/Y') . ".";
    }
    

    // Répondre à la récompense d'un tournoi
    public function getTournoiRecompense(string $tournoiName): string {
        $tournoi = $this->tournoiRepository->findOneBy(['nom' => $tournoiName]);
        if (!$tournoi) {
            return "Désolé, je n'ai pas trouvé ce tournoi.";
        }
        return "Le tournoi {$tournoi->getNom()} offre une récompense de : {$tournoi->getRecompense()}.";
    }

    // Répondre aux tournois en cours
    public function getTournoisEnCours(): string {
        $tournois = $this->tournoiRepository->findBy(['statut' => 'En_cours']);
        if (empty($tournois)) {
            return "Il n'y a pas de tournois en cours actuellement.";
        }
        $tournoiNames = [];
        foreach ($tournois as $tournoi) {
            $tournoiNames[] = $tournoi->getNom();
        }
        return "Les tournois en cours sont : " . implode(', ', $tournoiNames) . ".";
    }
}
