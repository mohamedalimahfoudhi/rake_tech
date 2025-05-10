<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Form\EmpruntType;
use App\Repository\EmpruntRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class EmpruntController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    #[Route('/admin/emprunt', name: 'app_emprunt_index', methods: ['GET'])]
    public function index(EmpruntRepository $empruntRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
            return $this->render('emprunt/index.html.twig', [
                'emprunts' => $empruntRepository->findAll(),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error fetching users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'An error occurred while fetching users.');
            return $this->redirectToRoute('app_dashboard');
        }
    }

    #[Route('/admin/emprunt/export-pdf', name: 'app_emprunt_export_pdf', methods: ['GET'])]
    public function exportToPdf(EmpruntRepository $empruntRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        try {
            // Get all emprunts
            $emprunts = $empruntRepository->findAll();
            
            // Configure Dompdf
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            
            // Instantiate Dompdf
            $dompdf = new Dompdf($options);
            
            // Generate HTML for PDF
            $html = $this->renderView('emprunt/pdf_export.html.twig', [
                'emprunts' => $emprunts,
                'date' => new \DateTime(),
            ]);
            
            // Load HTML to Dompdf
            $dompdf->loadHtml($html);
            
            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');
            
            // Render the PDF
            $dompdf->render();
            
            // Generate a filename
            $filename = 'export_emprunts_' . date('Y-m-d_H-i-s') . '.pdf';
            
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
            return $this->redirectToRoute('app_emprunt_index');
        }
    }

    #[Route('/admin/emprunt/new', name: 'app_emprunt_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
        $emprunt = new Emprunt();
        $form = $this->createForm(EmpruntType::class, $emprunt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($emprunt);
            $entityManager->flush();

            $this->addFlash('success', 'L\'emprunt a été créé avec succès.');
            return $this->redirectToRoute('app_emprunt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emprunt/new.html.twig', [
            'emprunt' => $emprunt,
            'form' => $form,
        ]);
    } catch (\Exception $e) {
        $this->logger->error('Error fetching users', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $this->addFlash('error', 'An error occurred while fetching users.');
        return $this->redirectToRoute('app_dashboard');
    }
    }

    #[Route('/admin/emprunt/{id}', name: 'app_emprunt_show', methods: ['GET'])]
    public function show(Emprunt $emprunt): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
        return $this->render('emprunt/show.html.twig', [
            'emprunt' => $emprunt,
        ]);
    } catch (\Exception $e) {
        $this->logger->error('Error fetching users', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $this->addFlash('error', 'An error occurred while fetching users.');
        return $this->redirectToRoute('app_dashboard');
    }
    }

    #[Route('/admin/emprunt/{id}/edit', name: 'app_emprunt_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Emprunt $emprunt, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
        $form = $this->createForm(EmpruntType::class, $emprunt);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_emprunt_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('emprunt/edit.html.twig', [
            'emprunt' => $emprunt,
            'form' => $form,
        ]);
    } catch (\Exception $e) {
        $this->logger->error('Error fetching users', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $this->addFlash('error', 'An error occurred while fetching users.');
        return $this->redirectToRoute('app_dashboard');
    }
    }

    #[Route('/admin/emprunt/{id}', name: 'app_emprunt_delete', methods: ['POST'])]
    public function delete(Request $request, Emprunt $emprunt, EmpruntRepository $empruntRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
        if ($this->isCsrfTokenValid('delete'.$emprunt->getEmpruntId(), $request->request->get('_token'))) {
            $empruntRepository->remove($emprunt, true);
            
        }
        return $this->redirectToRoute('app_emprunt_index', [], Response::HTTP_SEE_OTHER);
    } catch (\Exception $e) {
        $this->logger->error('Error fetching users', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $this->addFlash('error', 'An error occurred while fetching users.');
        return $this->redirectToRoute('app_dashboard');
    }
    }
}
