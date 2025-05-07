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
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use App\Pagination\CustomDoctrineORMAdapter;
use App\Entity\Maintenance;
use Dompdf\Dompdf;
use Dompdf\Options;

class MaterielController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        
        // Create a Doctrine ORM Adapter
        $adapter = new CustomDoctrineORMAdapter($queryBuilder);
        
        // Create Pagerfanta instance
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(3);
        $pagerfanta->setCurrentPage($page);
        
        return $this->render('materiel/index.html.twig', [
            'pager' => $pagerfanta,
            'statusFilter' => $statusFilter,
            'disponibleCount' => $disponibleCount,
            'reserveCount' => $reserveCount,
            'maintenanceCount' => $maintenanceCount,
            'totalCount' => $totalCount,
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
            $this->addFlash('error', 'An error occurred while generating the PDF export.');
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

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($materiel);
            $entityManager->flush();

            $this->addFlash('success', 'Le matériel a été créé avec succès.');
            return $this->redirectToRoute('app_materiel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('materiel/new.html.twig', [
            'materiel' => $materiel,
            'form' => $form,
        ]);
        }catch (\Exception $e) {
            $this->logger->error('Error fetching users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'An error occurred while fetching users.');
            return $this->redirectToRoute('app_dashboard');
        }
    }

    #[Route('/admin/materiel/{id}', name: 'app_materiel_show', methods: ['GET'])]
    public function show(Materiel $materiel): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
        return $this->render('materiel/show.html.twig', [
            'materiel' => $materiel,
        ]);}catch (\Exception $e) {
            $this->logger->error('Error fetching users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'An error occurred while fetching users.');
            return $this->redirectToRoute('app_dashboard');
        }
    }

    #[Route('/admin/materiel/{id}/edit', name: 'app_materiel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Materiel $materiel, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
        $form = $this->createForm(MaterielType::class, $materiel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le matériel a été modifié avec succès.');
            return $this->redirectToRoute('app_materiel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('materiel/edit.html.twig', [
            'materiel' => $materiel,
            'form' => $form,
        ]);}catch (\Exception $e) {
            $this->logger->error('Error fetching users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'An error occurred while fetching users.');
            return $this->redirectToRoute('app_dashboard');
        }
    }

    #[Route('/admin/materiel/{id}/delete', name: 'app_materiel_delete', methods: ['POST'])]
    public function delete(Request $request, Materiel $materiel, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
        if ($this->isCsrfTokenValid('delete'.$materiel->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($materiel);
                $entityManager->flush();
                $this->addFlash('success', 'Le matériel a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Impossible de supprimer ce matériel car il est lié à des réservations ou des emprunts.');
            }
        }

        return $this->redirectToRoute('app_materiel_index', [], Response::HTTP_SEE_OTHER);
    }catch (\Exception $e) {
        $this->logger->error('Error fetching users', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $this->addFlash('error', 'An error occurred while fetching users.');
        return $this->redirectToRoute('app_dashboard');
    }
}

    #[Route('/athlete/materiels', name: 'app_athlethe_materiels', methods: ['GET'] )]
    public function afficherMaterielsDisponibles(EntityManagerInterface $em)
    {
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        try {
        // Récupérer tous les matériels avec le statut 'Disponible'
        $materiels = $em->getRepository(Materiel::class)->findBy(['statut' => 'Disponible']);

        // Passer les matériels à la vue
        return $this->render('materiel/indexathlete.html.twig', [
            'materiels' => $materiels
        ]);}catch (\Exception $e) {
            $this->logger->error('Error fetching users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'An error occurred while fetching users.');
            return $this->redirectToRoute('app_dashboard');
        }
    }

    #[Route('/athlete/louer/{id}', name: 'louer_materiel')]
    public function louerMateriel(int $id, EntityManagerInterface $em)
    {
        
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        try {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            // Rediriger si l'utilisateur n'est pas connecté
            return $this->redirectToRoute('app_login');
        }

        // Récupérer le matériel par son ID
        $materiel = $em->getRepository(Materiel::class)->find($id);
        if (!$materiel || $materiel->getStatut() !== 'Disponible') {
            // Vérifier si le matériel est disponible
            $this->addFlash('error', 'Le matériel n\'est pas disponible');
            return $this->redirectToRoute('materiels');
        }

        // Créer un nouvel emprunt
        $emprunt = new Emprunt();
        $emprunt->setUserId($user);
        $emprunt->setMaterielId($materiel);
        $emprunt->setDateEmprunt(new \DateTime());
        $emprunt->setDateRetour((new \DateTime())->modify('+7 days'));
        $emprunt->setStatutEmprunt(null);

        // Enregistrer l'emprunt dans la base de données
        $em->persist($emprunt);

        // Mettre à jour le statut du matériel
        $materiel->setStatut('Réservé');
        $em->persist($materiel);

        // Valider les modifications
        $em->flush();

        // Message de confirmation
        $this->addFlash('success', 'Le matériel a été réservé avec succès.');

        return $this->redirectToRoute('materiels');
        }catch (\Exception $e) {
            $this->logger->error('Error fetching users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'An error occurred while fetching users.');
            return $this->redirectToRoute('app_dashboard');
        }
    }


    #[Route('/athlete/mesmateriels', name: 'mes_materiels_athlete')]
    public function afficherMaterielsLoues(EntityManagerInterface $em, Security $security)
    {
        
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        try {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
        if (!$user) {
            // Rediriger si l'utilisateur n'est pas connecté
            return $this->redirectToRoute('app_login');
        }

        // Récupérer tous les emprunts associés à l'utilisateur
        $emprunts = $em->getRepository(Emprunt::class)->findBy(['userid' => $user]);

        // Passer les emprunts à la vue pour affichage
        return $this->render('materiel/mes_materiels.html.twig', [
            'emprunts' => $emprunts
        ]);}catch (\Exception $e) {
            $this->logger->error('Error fetching users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'An error occurred while fetching users.');
            return $this->redirectToRoute('app_dashboard');
        }
    }

    #[Route('/athlete/signalerpanne/{materielId}', name: 'signaler_panne')]
    public function signalerPanne($materielId, EntityManagerInterface $em, Security $security)
    {
    $this->denyAccessUnlessGranted('ROLE_ATHLETE');

    try {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer le matériel correspondant à l'ID
        $materiel = $em->getRepository(Materiel::class)->find($materielId);
        if (!$materiel) {
            $this->addFlash('error', 'Matériel introuvable.');
            return $this->redirectToRoute('mes_materiels_athlete');
        }

        // Mettre à jour le statut du matériel
        $materiel->setStatut('Sous maintenance');
        $materiel->setSignaler(1);
        $em->persist($materiel);

        // Mettre à jour l'emprunt associé
        $emprunt = $em->getRepository(Emprunt::class)->findOneBy(['materielid' => $materiel, 'userid' => $user]);
        if ($emprunt) {
            $emprunt->setStatutEmprunt('Emprunté');
            $em->persist($emprunt);
        }

        // Créer une nouvelle maintenance
        $maintenance = new Maintenance();
        $maintenance->setMaterielid($materiel);
        $maintenance->setDateMaintenance(new \DateTime());
        $maintenance->setStatutMaintenance('Planifié');
        $maintenance->setDescription('Maintenance automatique');
        $em->persist($maintenance);

        // Enregistrer toutes les modifications
        $em->flush();

        $this->addFlash('success', 'Le matériel a été signalé comme étant en panne.');

        return $this->redirectToRoute('mes_materiels_athlete');

        } catch (\Exception $e) {
        $this->logger->error('Error reporting panne', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $this->addFlash('error', 'An error occurred while reporting the panne.');
        return $this->redirectToRoute('mes_materiels_athlete');
        }
        }

} 