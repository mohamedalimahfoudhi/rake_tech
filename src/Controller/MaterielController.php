<?php

namespace App\Controller;

use App\Entity\Materiel;
use App\Form\MaterielType;
use App\Repository\MaterielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use App\Entity\Emprunt;
use Symfony\Component\Security\Core\Security;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Maintenance;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Service\ValidationService;

class MaterielController extends AbstractController
{
    private $logger;
    private $paginator;
    private $validationService;

    public function __construct(
        LoggerInterface $logger, 
        PaginatorInterface $paginator,
        ValidationService $validationService
    )
    {
        $this->logger = $logger;
        $this->paginator = $paginator;
        $this->validationService = $validationService;
    }

    #[Route('/admin/materiel/', name: 'app_materiel_index', methods: ['GET'])]
    public function index(MaterielRepository $materielRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Get the current page (default to page 1)
        $page = $request->query->getInt('page', 1);
        
        // Get the status filter (if any)
        $statusFilter = $request->query->get('status');
        
        // Get counts for each status
        $disponibleCount = $materielRepository->count(['statut' => 'Disponible']);
        $reserveCount = $materielRepository->count(['statut' => 'Réservé']);
        $maintenanceCount = $materielRepository->count(['statut' => 'Sous maintenance']);
        $totalCount = $materielRepository->count([]);
        
        // Create the query builder for fetching materials
        $queryBuilder = $materielRepository->createQueryBuilder('m')
            ->orderBy('m.type', 'ASC');
        
        // Apply status filter if provided
        if ($statusFilter && in_array($statusFilter, ['Disponible', 'Réservé', 'Sous maintenance'])) {
            $queryBuilder->andWhere('m.statut = :status')
                ->setParameter('status', $statusFilter);
        }
        
        // Default to 3 items per page
        $itemsPerPage = $request->query->getInt('limit', 3);
        
        // Create pagination with KnpPaginatorBundle
        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $page,
            $itemsPerPage
        );
        
        // Calculate total number of pages
        $totalItems = $pagination->getTotalItemCount();
        $totalPages = ceil($totalItems / $itemsPerPage);
        
        return $this->render('materiel/index.html.twig', [
            'pagination' => $pagination,
            'statusFilter' => $statusFilter,
            'disponibleCount' => $disponibleCount,
            'reserveCount' => $reserveCount,
            'maintenanceCount' => $maintenanceCount,
            'totalCount' => $totalCount,
            'totalPages' => $totalPages,
            'itemsPerPage' => $itemsPerPage,
        ]);
    }
    
    #[Route('/admin/materiel/export-pdf', name: 'app_materiel_export_pdf', methods: ['GET'])]
    public function exportToPdf(MaterielRepository $materielRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        try {
            // Get all materials
            $materiels = $materielRepository->findAll();
            
            // Configure Dompdf
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            
            // Instantiate Dompdf
            $dompdf = new Dompdf($options);
            
            // Generate HTML for PDF
            $html = $this->renderView('materiel/pdf_export.html.twig', [
                'materiels' => $materiels,
                'date' => new \DateTime(),
            ]);
            
            // Load HTML to Dompdf
            $dompdf->loadHtml($html);
            
            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');
            
            // Render the PDF
            $dompdf->render();
            
            // Generate a filename
            $filename = 'export_materiels_' . date('Y-m-d_H-i-s') . '.pdf';
            
            // Output the generated PDF (inline)
            return new Response(
                $dompdf->output(),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]
            );
            
        } catch (\Exception $e) {
            $this->logger->error('Error exporting PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'Une erreur est survenue lors de la génération du PDF.');
            return $this->redirectToRoute('app_materiel_index');
        }
    }

    #[Route('/admin/materiel/new', name: 'app_materiel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
            $materiel = new Materiel();
            $form = $this->createForm(MaterielType::class, $materiel);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                // Validate form and custom validations
                $formValid = $form->isValid();
                $customValid = $this->validationService->validateMateriel($materiel);
                
                if ($formValid && $customValid) {
                    $entityManager->persist($materiel);
                    $entityManager->flush();

                    $this->addFlash('success', 'Le matériel a été créé avec succès.');
                    
                    // Count total materials to determine the latest page
                    $totalMaterials = $entityManager->getRepository(Materiel::class)->count([]);
                    $itemsPerPage = $request->query->getInt('limit', 3);
                    $latestPage = ceil($totalMaterials / $itemsPerPage);
                    
                    // Redirect to the latest page
                    return $this->redirectToRoute('app_materiel_index', [
                        'page' => $latestPage,
                        'limit' => $itemsPerPage,
                        'status' => $request->query->get('status')
                    ]);
                } else {
                    // Form validation errors will be displayed by the form renderer
                    // Custom validation errors have been added to flash messages by the validation service
                    if (!$formValid) {
                        $this->validationService->validateForm($form);
                    }
                }
            }

            return $this->render('materiel/new.html.twig', [
                'materiel' => $materiel,
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error creating material', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'Une erreur est survenue lors de la création du matériel.');
            return $this->redirectToRoute('app_materiel_index');
        }
    }

    #[Route('/admin/materiel/{id}', name: 'app_materiel_show', methods: ['GET'])]
    public function show(Request $request, Materiel $materiel = null): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
            // Check if material exists
            if (!$materiel) {
                $this->addFlash('error', 'Ce matériel n\'existe pas ou a été supprimé.');
                return $this->redirectToRoute('app_materiel_index');
            }
            
            // Store current pagination parameters
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 3);
            $status = $request->query->get('status', '');
            
            return $this->render('materiel/show.html.twig', [
                'materiel' => $materiel,
                'page' => $page,
                'limit' => $limit,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error showing material', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'Une erreur est survenue lors de l\'affichage du matériel.');
            return $this->redirectToRoute('app_materiel_index');
        }
    }

    #[Route('/admin/materiel/{id}/edit', name: 'app_materiel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Materiel $materiel = null, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
            // Check if material exists
            if (!$materiel) {
                $this->addFlash('error', 'Ce matériel n\'existe pas ou a été supprimé.');
                return $this->redirectToRoute('app_materiel_index');
            }
            
            // Store current pagination parameters
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 3);
            $status = $request->query->get('status', '');
            
            $form = $this->createForm(MaterielType::class, $materiel);
            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                // Validate form and custom validations
                $formValid = $form->isValid();
                $customValid = $this->validationService->validateMateriel($materiel);
                
                if ($formValid && $customValid) {
                    $entityManager->flush();

                    $this->addFlash('success', 'Le matériel a été mis à jour avec succès.');
                    
                    return $this->redirectToRoute('app_materiel_index', [
                        'page' => $page,
                        'limit' => $limit,
                        'status' => $status
                    ]);
                } else {
                    // Form validation errors will be displayed by the form renderer
                    // Custom validation errors have been added to flash messages by the validation service
                    if (!$formValid) {
                        $this->validationService->validateForm($form);
                    }
                }
            }

            return $this->render('materiel/edit.html.twig', [
                'materiel' => $materiel,
                'form' => $form,
                'page' => $page,
                'limit' => $limit,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error editing material', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'Une erreur est survenue lors de la modification du matériel.');
            return $this->redirectToRoute('app_materiel_index');
        }
    }

    #[Route('/admin/materiel/{id}/delete', name: 'app_materiel_delete', methods: ['POST'])]
    public function delete(Request $request, Materiel $materiel = null, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
            // Check if material exists
            if (!$materiel) {
                $this->addFlash('error', 'Ce matériel n\'existe pas ou a été supprimé.');
                return $this->redirectToRoute('app_materiel_index');
            }
            
            // Store current pagination parameters
            $page = $request->query->getInt('page', 1);
            $limit = $request->query->getInt('limit', 3);
            $status = $request->query->get('status', '');
            
            // Vérifier si le token CSRF est valide
            if (!$this->isCsrfTokenValid('delete'.$materiel->getId(), $request->request->get('_token'))) {
                $this->addFlash('error', 'Token CSRF invalide. Veuillez réessayer.');
                return $this->redirectToRoute('app_materiel_index', [
                    'page' => $page,
                    'limit' => $limit,
                    'status' => $status
                ]);
            }
            
            // Vérifier les emprunts associés avec DQL
            $empruntCount = $entityManager->createQuery(
                'SELECT COUNT(e) FROM App\Entity\Emprunt e WHERE e.materielid = :materiel'
            )
            ->setParameter('materiel', $materiel)
            ->getSingleScalarResult();
            
            if ($empruntCount > 0) {
                $this->addFlash('error', 'Ce matériel ne peut pas être supprimé car il est associé à ' . $empruntCount . ' emprunt(s).');
                return $this->redirectToRoute('app_materiel_index', [
                    'page' => $page,
                    'limit' => $limit,
                    'status' => $status
                ]);
            }
            
            // Vérifier les maintenances associées avec DQL
            $maintenanceCount = $entityManager->createQuery(
                'SELECT COUNT(m) FROM App\Entity\Maintenance m WHERE m.materielid = :materiel'
            )
            ->setParameter('materiel', $materiel)
            ->getSingleScalarResult();
            
            if ($maintenanceCount > 0) {
                $this->addFlash('error', 'Ce matériel ne peut pas être supprimé car il est associé à ' . $maintenanceCount . ' maintenance(s).');
                return $this->redirectToRoute('app_materiel_index', [
                    'page' => $page,
                    'limit' => $limit,
                    'status' => $status
                ]);
            }
            
            // Supprimer le matériel
            try {
                $entityManager->remove($materiel);
                $entityManager->flush();
                
                $this->addFlash('success', 'Le matériel a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->logger->error('Error during material deletion', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->addFlash('error', 'Erreur lors de la suppression : ' . $e->getMessage());
                return $this->redirectToRoute('app_materiel_index', [
                    'page' => $page,
                    'limit' => $limit,
                    'status' => $status
                ]);
            }
            
            // Check if we need to adjust the page number after deletion
            $materielsRepository = $entityManager->getRepository(Materiel::class);
            $queryBuilder = $materielsRepository->createQueryBuilder('m')
                ->select('COUNT(m.id)');
                
            // Apply status filter if previously applied
            if (!empty($status)) {
                $queryBuilder->andWhere('m.statut = :status')
                    ->setParameter('status', $status);
            }
            
            $totalMaterials = $queryBuilder->getQuery()->getSingleScalarResult();
            $maxPage = max(1, ceil($totalMaterials / $limit));
            
            if ($page > $maxPage) {
                $page = $maxPage;
            }
            
            return $this->redirectToRoute('app_materiel_index', [
                'page' => $page,
                'limit' => $limit,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error deleting material', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du matériel: ' . $e->getMessage());
            return $this->redirectToRoute('app_materiel_index');
        }
    }

    #[Route('/athlete/materiels', name: 'app_athlethe_materiels', methods: ['GET'] )]
    public function afficherMaterielsDisponibles(EntityManagerInterface $em, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        try {
            // Query to get available materials
            $queryBuilder = $em->getRepository(Materiel::class)
                ->createQueryBuilder('m')
                ->where('m.statut = :statut')
                ->setParameter('statut', 'Disponible')
                ->orderBy('m.type', 'ASC');
            
            // Create pagination
            $pagination = $this->paginator->paginate(
                $queryBuilder,
                $request->query->getInt('page', 1),
                $request->query->getInt('limit', 6) // Show more items for athletes
            );
            
            return $this->render('materiel/athlete_materiels.html.twig', [
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error displaying available materials', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'Une erreur est survenue lors de l\'affichage des matériels disponibles.');
            return $this->redirectToRoute('app_athlete_dashboard');
        }
    }

    #[Route('/athlete/louer/{id}', name: 'louer_materiel')]
    public function louerMateriel(int $id, EntityManagerInterface $em, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        try {
            // Get the material
            $materiel = $em->getRepository(Materiel::class)->find($id);
            
            // Check if material exists
            if (!$materiel) {
                $this->addFlash('error', 'Ce matériel n\'existe pas.');
                return $this->redirectToRoute('app_athlethe_materiels');
            }
            
            // Check if material is available
            if ($materiel->getStatut() !== 'Disponible') {
                $this->addFlash('error', 'Ce matériel n\'est pas disponible pour la location.');
                return $this->redirectToRoute('app_athlethe_materiels');
            }
            
            // Create new loan
            $emprunt = new Emprunt();
            $emprunt->setMateriel($materiel);
            $emprunt->setUser($this->getUser());
            $emprunt->setDateEmprunt(new \DateTime());
            
            // Calculate return date (7 days from now)
            $returnDate = new \DateTime();
            $returnDate->modify('+7 days');
            $emprunt->setDateRetourPrevu($returnDate);
            
            // Validate the loan
            if (!$this->validationService->validateEmprunt($emprunt)) {
                return $this->redirectToRoute('app_athlethe_materiels');
            }
            
            // Update material status
            $materiel->setStatut('En emprunt');
            
            // Save to database
            $em->persist($emprunt);
            $em->persist($materiel);
            $em->flush();
            
            $this->addFlash('success', 'Vous avez emprunté le matériel avec succès. Veuillez le retourner avant le ' . $returnDate->format('d/m/Y') . '.');
            
            return $this->redirectToRoute('mes_materiels_athlete');
        } catch (\Exception $e) {
            $this->logger->error('Error renting material', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'Une erreur est survenue lors de la location du matériel.');
            return $this->redirectToRoute('app_athlethe_materiels');
        }
    }

    #[Route('/athlete/mesmateriels', name: 'mes_materiels_athlete')]
    public function afficherMaterielsLoues(EntityManagerInterface $em, Security $security, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        try {
            $user = $security->getUser();
            
            // Query to get user's rented materials
            $queryBuilder = $em->getRepository(Emprunt::class)
                ->createQueryBuilder('e')
                ->join('e.materiel', 'm')
                ->where('e.user = :user')
                ->andWhere('e.dateRetour IS NULL')
                ->setParameter('user', $user)
                ->orderBy('e.dateEmprunt', 'DESC');
            
            // Create pagination
            $pagination = $this->paginator->paginate(
                $queryBuilder,
                $request->query->getInt('page', 1),
                $request->query->getInt('limit', 5)
            );
            
            return $this->render('materiel/athlete_mes_materiels.html.twig', [
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error displaying rented materials', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'Une erreur est survenue lors de l\'affichage de vos matériels empruntés.');
            return $this->redirectToRoute('app_athlete_dashboard');
        }
    }

    #[Route('/athlete/signalerpanne/{materielId}', name: 'signaler_panne')]
    public function signalerPanne($materielId, EntityManagerInterface $em, Security $security, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        try {
            // Get the user and material
            $user = $security->getUser();
            $materiel = $em->getRepository(Materiel::class)->find($materielId);
            
            // Verify material exists
            if (!$materiel) {
                $this->addFlash('error', 'Ce matériel n\'existe pas.');
                return $this->redirectToRoute('mes_materiels_athlete');
            }
            
            // Check if the user has borrowed this material
            $emprunt = $em->getRepository(Emprunt::class)
                ->findOneBy([
                    'user' => $user,
                    'materiel' => $materiel,
                    'dateRetour' => null
                ]);
            
            if (!$emprunt) {
                $this->addFlash('error', 'Vous n\'avez pas emprunté ce matériel ou il a déjà été retourné.');
                return $this->redirectToRoute('mes_materiels_athlete');
            }
            
            // Create new maintenance
            $maintenance = new Maintenance();
            $maintenance->setMateriel($materiel);
            $maintenance->setType('Panne signalée par utilisateur');
            $maintenance->setDatemaintenance(new \DateTime());
            $maintenance->setDescription($request->request->get('description', 'Aucune description fournie'));
            
            // Validate the maintenance
            if (!$this->validationService->validateMaintenance($maintenance)) {
                return $this->redirectToRoute('mes_materiels_athlete');
            }
            
            // Update material status
            $materiel->setStatut('Maintenance');
            
            // Return the material (end the loan)
            $emprunt->setDateRetour(new \DateTime());
            $emprunt->setCommentaire('Retourné pour maintenance: ' . $maintenance->getDescription());
            
            // Save to database
            $em->persist($maintenance);
            $em->persist($materiel);
            $em->persist($emprunt);
            $em->flush();
            
            $this->addFlash('success', 'La panne a été signalée avec succès. Le matériel a été mis en maintenance.');
            
            return $this->redirectToRoute('mes_materiels_athlete');
        } catch (\Exception $e) {
            $this->logger->error('Error reporting issue', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'Une erreur est survenue lors du signalement de la panne.');
            return $this->redirectToRoute('mes_materiels_athlete');
        }
    }

    #[Route('/admin/materiel/statistiques', name: 'app_materiel_statistiques', methods: ['GET'])]
    public function statistiques(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        try {
            // Get repository references
            $materielRepository = $entityManager->getRepository(Materiel::class);
            $empruntRepository = $entityManager->getRepository(Emprunt::class);
            $maintenanceRepository = $entityManager->getRepository(Maintenance::class);
            
            // Material statistics
            $totalMaterials = $materielRepository->count([]);
            $availableMaterials = $materielRepository->count(['statut' => 'Disponible']);
            $rentedMaterials = $materielRepository->count(['statut' => 'En emprunt']);
            $maintenanceMaterials = $materielRepository->count(['statut' => 'Maintenance']);
            
            // Get material types distribution
            $typesDistribution = $entityManager->createQuery(
                'SELECT m.type, COUNT(m.id) as count 
                FROM App\Entity\Materiel m 
                GROUP BY m.type 
                ORDER BY count DESC'
            )->getResult();
            
            // Loan statistics
            $totalLoans = $empruntRepository->count([]);
            $activeLoans = $empruntRepository->count(['dateRetour' => null]);
            $completedLoans = $empruntRepository->createQueryBuilder('e')
                ->select('COUNT(e.id)')
                ->where('e.dateRetour IS NOT NULL')
                ->getQuery()
                ->getSingleScalarResult();
            
            // Get most borrowed materials (top 5)
            $mostBorrowedMaterials = $entityManager->createQuery(
                'SELECT m.nom, m.type, COUNT(e.id) as emprunts 
                FROM App\Entity\Emprunt e 
                JOIN e.materiel m 
                GROUP BY m.id 
                ORDER BY emprunts DESC'
            )->setMaxResults(5)->getResult();
            
            // Maintenance statistics
            $totalMaintenances = $maintenanceRepository->count([]);
            $activeMaintenances = $maintenanceRepository->count(['datefin' => null]);
            $completedMaintenances = $maintenanceRepository->createQueryBuilder('m')
                ->select('COUNT(m.id)')
                ->where('m.datefin IS NOT NULL')
                ->getQuery()
                ->getSingleScalarResult();
            
            // Calculate total maintenance cost
            $maintenanceCost = $entityManager->createQuery(
                'SELECT SUM(m.cout) 
                FROM App\Entity\Maintenance m'
            )->getSingleScalarResult() ?: 0;
            
            // Calculate maintenance cost for current year
            $currentYear = (new \DateTime())->format('Y');
            $maintenanceCostCurrentYear = $entityManager->createQuery(
                'SELECT SUM(m.cout) 
                FROM App\Entity\Maintenance m 
                WHERE YEAR(m.datemaintenance) = :year'
            )->setParameter('year', $currentYear)
            ->getSingleScalarResult() ?: 0;
            
            // Get average maintenance duration (in days)
            $averageMaintenanceDuration = $entityManager->createQuery(
                'SELECT AVG(DATEDIFF(m.datefin, m.datemaintenance)) 
                FROM App\Entity\Maintenance m 
                WHERE m.datefin IS NOT NULL'
            )->getSingleScalarResult() ?: 0;
            
            return $this->render('materiel/statistiques.html.twig', [
                // Materials stats
                'totalMaterials' => $totalMaterials,
                'availableMaterials' => $availableMaterials,
                'rentedMaterials' => $rentedMaterials,
                'maintenanceMaterials' => $maintenanceMaterials,
                'typesDistribution' => $typesDistribution,
                
                // Loans stats
                'totalLoans' => $totalLoans,
                'activeLoans' => $activeLoans,
                'completedLoans' => $completedLoans,
                'mostBorrowedMaterials' => $mostBorrowedMaterials,
                
                // Maintenance stats
                'totalMaintenances' => $totalMaintenances,
                'activeMaintenances' => $activeMaintenances,
                'completedMaintenances' => $completedMaintenances,
                'maintenanceCost' => $maintenanceCost,
                'maintenanceCostCurrentYear' => $maintenanceCostCurrentYear,
                'averageMaintenanceDuration' => round($averageMaintenanceDuration, 1),
                'currentYear' => $currentYear,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error generating statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'Une erreur est survenue lors de la génération des statistiques.');
            return $this->redirectToRoute('app_materiel_index');
        }
    }
} 