<?php

namespace App\Controller;

use App\Repository\EmpruntRepository;
use App\Repository\MaintenanceRepository;
use App\Repository\MaterielRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class StatisticsController extends AbstractController
{
    #[Route('/admin/statistics', name: 'admin_statistics')]
    public function index(
        EmpruntRepository $empruntRepository, 
        MaintenanceRepository $maintenanceRepository, 
        MaterielRepository $materielRepository,
        UserRepository $userRepository
    ): Response
    {
        // Récupération des données
        $emprunts = $empruntRepository->findAll();
        $maintenances = $maintenanceRepository->findAll();
        $materiels = $materielRepository->findAll();
    
        // Statistiques des emprunts
        $empruntData = [];
        $empruntDurations = [];
        $empruntByUser = [];
        $empruntByMaterial = [];
        
        foreach ($emprunts as $emprunt) {
            // Agrégation par date
            $date = $emprunt->getDateEmprunt()->format('Y-m-d');
            if (!isset($empruntData[$date])) {
                $empruntData[$date] = 0;
            }
            $empruntData[$date]++;
            
            // Calcul des durées d'emprunt
            if ($emprunt->getDateRetour() && $emprunt->getDateEmprunt()) {
                $duration = $emprunt->getDateRetour()->diff($emprunt->getDateEmprunt())->days;
                $empruntDurations[] = $duration;
            }
            
            // Agrégation par utilisateur
            $userId = $emprunt->getUser() ? $emprunt->getUser()->getId() : 'Unknown';
            $userName = $emprunt->getUser() ? $emprunt->getUser()->getPrenom() . ' ' . $emprunt->getUser()->getNom() : 'Unknown';
            if (!isset($empruntByUser[$userId])) {
                $empruntByUser[$userId] = [
                    'name' => $userName,
                    'count' => 0
                ];
            }
            $empruntByUser[$userId]['count']++;
            
            // Agrégation par matériel
            if ($emprunt->getMateriel()) {
                $materielId = $emprunt->getMateriel()->getId();
                $materielName = $emprunt->getMateriel()->getNom();
                if (!isset($empruntByMaterial[$materielId])) {
                    $empruntByMaterial[$materielId] = [
                        'name' => $materielName,
                        'count' => 0
                    ];
                }
                $empruntByMaterial[$materielId]['count']++;
            }
        }
        
        // Calcul de la durée moyenne d'emprunt
        $avgEmpruntDuration = count($empruntDurations) > 0 ? array_sum($empruntDurations) / count($empruntDurations) : 0;
        
        // Tri des emprunts par utilisateur et par matériel (top 5)
        uasort($empruntByUser, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        $topUsers = array_slice($empruntByUser, 0, 5, true);
        
        uasort($empruntByMaterial, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        $topMaterials = array_slice($empruntByMaterial, 0, 5, true);
    
        // Statistiques des maintenances
        $maintenanceData = [];
        $maintenanceCosts = [];
        $maintenanceDurations = [];
        $maintenanceByType = [];
        
        foreach ($maintenances as $maintenance) {
            // Agrégation par date
            $date = $maintenance->getDatemaintenance()->format('Y-m-d');
            if (!isset($maintenanceData[$date])) {
                $maintenanceData[$date] = 0;
            }
            $maintenanceData[$date]++;
            
            // Agrégation des coûts
            if ($maintenance->getCout()) {
                $maintenanceCosts[] = $maintenance->getCout();
            }
            
            // Calcul des durées de maintenance
            if ($maintenance->getDatefin() && $maintenance->getDatemaintenance()) {
                $duration = $maintenance->getDatefin()->diff($maintenance->getDatemaintenance())->days;
                $maintenanceDurations[] = $duration;
            }
            
            // Agrégation par type
            $type = $maintenance->getType() ?: 'Non spécifié';
            if (!isset($maintenanceByType[$type])) {
                $maintenanceByType[$type] = 0;
            }
            $maintenanceByType[$type]++;
        }
        
        // Calcul du coût moyen et de la durée moyenne des maintenances
        $avgMaintenanceCost = count($maintenanceCosts) > 0 ? array_sum($maintenanceCosts) / count($maintenanceCosts) : 0;
        $avgMaintenanceDuration = count($maintenanceDurations) > 0 ? array_sum($maintenanceDurations) / count($maintenanceDurations) : 0;
    
        // Statistiques des matériels
        $materielsData = [];
        $materielsByCategory = [];
        $materielsAvailability = [
            'Disponible' => 0,
            'En emprunt' => 0,
            'En maintenance' => 0,
            'Hors service' => 0
        ];
        
        foreach ($materiels as $materiel) {
            // Agrégation par statut
            $statut = $materiel->getStatut() ?: 'Non spécifié';
            if (!isset($materielsData[$statut])) {
                $materielsData[$statut] = 0;
            }
            $materielsData[$statut]++;
            
            // Mise à jour des statistiques de disponibilité
            if ($statut === 'Disponible') {
                $materielsAvailability['Disponible']++;
            } elseif ($statut === 'En emprunt') {
                $materielsAvailability['En emprunt']++;
            } elseif ($statut === 'Maintenance') {
                $materielsAvailability['En maintenance']++;
            } else {
                $materielsAvailability['Hors service']++;
            }
            
            // Agrégation par catégorie
            $category = $materiel->getType() ?: 'Non spécifié';
            if (!isset($materielsByCategory[$category])) {
                $materielsByCategory[$category] = 0;
            }
            $materielsByCategory[$category]++;
        }
        
        // Calcul du taux de disponibilité
        $totalMateriels = count($materiels);
        $availabilityRate = $totalMateriels > 0 ? ($materielsAvailability['Disponible'] / $totalMateriels) * 100 : 0;
    
        // Passer les données à la vue
        return $this->render('statistics/index.html.twig', [
            'empruntData' => $empruntData,
            'maintenanceData' => $maintenanceData,
            'materielsData' => $materielsData,
            
            // Statistiques avancées des emprunts
            'totalEmprunts' => count($emprunts),
            'avgEmpruntDuration' => round($avgEmpruntDuration, 1),
            'topUsers' => $topUsers,
            'topMaterials' => $topMaterials,
            
            // Statistiques avancées des maintenances
            'totalMaintenances' => count($maintenances),
            'avgMaintenanceCost' => round($avgMaintenanceCost, 2),
            'avgMaintenanceDuration' => round($avgMaintenanceDuration, 1),
            'maintenanceByType' => $maintenanceByType,
            
            // Statistiques avancées des matériels
            'totalMateriels' => $totalMateriels,
            'availabilityRate' => round($availabilityRate, 1),
            'materielsAvailability' => $materielsAvailability,
            'materielsByCategory' => $materielsByCategory
        ]);
    }
}    