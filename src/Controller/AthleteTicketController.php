<?php

namespace App\Controller;

use App\Entity\Billet;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AthleteTicketController extends AbstractController
{
    private BuilderInterface $qrBuilder;
    private $exchangeRates = [
        'EUR' => 1.0,
        'USD' => 1.08,
        'GBP' => 0.85,
        'CHF' => 0.96,
        'CAD' => 1.47,
        'JPY' => 161.85
    ];
    
    public function __construct(BuilderInterface $qrBuilder)
    {
        $this->qrBuilder = $qrBuilder;
    }
    
    #[Route('/athlete/tickets', name: 'app_athlete_tickets')]
    public function index(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        
        // Get the current user
        $user = $this->getUser();
        
        // Build query to get all tickets (since there's no user relation in Billet entity)
        $queryBuilder = $entityManager->getRepository(Billet::class)->createQueryBuilder('b')
            ->orderBy('b.id', 'DESC');
            
        $query = $queryBuilder->getQuery();

        // Default to 5 items per page
        $itemsPerPage = $request->query->getInt('limit', 5);
        
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );
        
        // Calculate total number of pages
        $totalItems = $pagination->getTotalItemCount();
        $totalPages = ceil($totalItems / $itemsPerPage);

        return $this->render('billet/athlete_tickets.html.twig', [
            'pagination' => $pagination,
            'totalPages' => $totalPages,
            'itemsPerPage' => $itemsPerPage,
        ]);
    }
    
    #[Route('/athlete/ticket/{id}/view', name: 'app_athlete_ticket_view')]
    public function view(Billet $billet): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        
        // Generate QR code for ticket verification
        $ticketUrl = $this->generateUrl('app_athlete_ticket_verify', ['id' => $billet->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $qrCode = $this->qrBuilder
            ->data($ticketUrl)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(10)
            ->writer(new SvgWriter())
            ->build();
        
        $qrDataUri = $qrCode->getDataUri();
        
        return $this->render('billet/athlete_ticket_view.html.twig', [
            'billet' => $billet,
            'qrCode' => $qrDataUri,
            'exchangeRates' => $this->exchangeRates
        ]);
    }
    
    #[Route('/athlete/ticket/{id}/verify', name: 'app_athlete_ticket_verify')]
    public function verify(Billet $billet): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        
        return $this->render('billet/athlete_ticket_verify.html.twig', [
            'billet' => $billet,
            'isValid' => $billet->getStatut() === 'Valide'
        ]);
    }
} 