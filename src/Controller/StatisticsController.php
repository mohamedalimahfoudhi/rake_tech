<?php


namespace App\Controller;

use App\Repository\EmpruntRepository;
use App\Repository\MaintenanceRepository;
use App\Repository\MaterielRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class StatisticsController extends AbstractController
{
    #[Route('/admin/statistics', name: 'admin_statistics')]
    public function index(EmpruntRepository $empruntRepository, MaintenanceRepository $maintenanceRepository, MaterielRepository $materielRepository): Response
    {
        // Récupération des données des emprunts
        $emprunts = $empruntRepository->findAll();
        $maintenances = $maintenanceRepository->findAll();
        $materiels = $materielRepository->findAll();
    
        // Traitement des données pour le graphique des emprunts
        $empruntData = [];
        foreach ($emprunts as $emprunt) {
            $date = $emprunt->getDateEmprunt()->format('Y-m-d');
            if (!isset($empruntData[$date])) {
                $empruntData[$date] = 0;
            }
            $empruntData[$date]++;
        }
    
        // Agrégation des maintenances par date
        $maintenanceData = [];
        foreach ($maintenances as $maintenance) {
            $date = $maintenance->getDatemaintenance()->format('Y-m-d');
            if (!isset($maintenanceData[$date])) {
                $maintenanceData[$date] = 0;
            }
            $maintenanceData[$date]++;
        }
    
        // Données des matériels
        $materielsData = [];
        foreach ($materiels as $materiel) {
            $materielsData[$materiel->getStatut()] = (isset($materielsData[$materiel->getStatut()])) ? $materielsData[$materiel->getStatut()] + 1 : 1;
        }
    
        // Passer les données à la vue
        return $this->render('statistics/index.html.twig', [
            'empruntData' => $empruntData,
            'maintenanceData' => $maintenanceData,
            'materielsData' => $materielsData,
        ]);
    }
    
}    