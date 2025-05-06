<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\ReservationBillet;
use App\Repository\BilletRepository;
use App\Repository\ReservationRepository;
use App\Repository\ReservationBilletRepository;
use App\Repository\EvenementRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class TicketPdfController extends AbstractController
{
    /**
     * Generate and download a PDF ticket
     */
    #[Route('/tickets/pdf/{reservationId}', name: 'ticket_pdf_download')]
    public function downloadTicketPdf(
        int $reservationId,
        ReservationRepository $reservationRepository,
        ReservationBilletRepository $reservationBilletRepository,
        BilletRepository $billetRepository,
        EvenementRepository $evenementRepository
    ): Response {
        // Get the current user
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to download tickets');
        }

        // Get the reservation
        $reservation = $reservationRepository->find($reservationId);
        if (!$reservation || $reservation->getUtilisateurID() !== $user->getId()) {
            throw $this->createNotFoundException('Ticket not found or not authorized');
        }

        // Get the reservation billet
        $reservationBillets = $reservationBilletRepository->findBy(['reservationID' => $reservationId]);
        if (empty($reservationBillets)) {
            throw $this->createNotFoundException('Ticket information not found');
        }

        $reservationBillet = $reservationBillets[0]; // Get the first one if multiple
        $billet = $billetRepository->find($reservationBillet->getBilletID());
        if (!$billet) {
            throw $this->createNotFoundException('Ticket details not found');
        }

        $evenement = $billet->getEvenement();
        if (!$evenement) {
            throw $this->createNotFoundException('Event information not found');
        }

        // Generate a unique code for the ticket (typically it would be a UUID or similar)
        $uniqueCode = sprintf('%06d', $reservationId) . sprintf('%06d', $billet->getId());

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // Generate QR code URL using the external service
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Reservation" . $reservation->getId();

        // Render the ticket template with data
        $html = $this->renderView('pdf/ticket.html.twig', [
            'reservation' => $reservation,
            'billet' => $billet,
            'evenement' => $evenement,
            'user' => $user,
            'quantite' => $reservationBillet->getNombreBillet(),
            'qrCodeUrl' => $qrCodeUrl,
            'uniqueCode' => $uniqueCode
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Generate a filename
        $filename = 'ticket_' . $evenement->getNom() . '_' . $uniqueCode . '.pdf';
        $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename); // Sanitize filename
        
        // Return as a downloadable PDF
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );
    }
} 