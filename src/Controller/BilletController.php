<?php

namespace App\Controller;

use App\Entity\Billet;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/admin')]
class BilletController extends AbstractController
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
    
    private $ticketTypes = [
        'Tous' => '',
        'Standard' => 'Standard',
        'VIP' => 'VIP',
        'Early Bird' => 'Early Bird',
        'Groupe' => 'Groupe',
    ];

    public function __construct(BuilderInterface $qrBuilder)
    {
        $this->qrBuilder = $qrBuilder;
    }

    #[Route('/tickets', name: 'app_ticket_index')]
    public function index(Request $request, EntityManagerInterface $entityManager, PaginatorInterface $paginator): Response
    {
        // Get filter parameters
        $filterType = $request->query->get('type', '');
        
        // Build query with filters
        $queryBuilder = $entityManager->getRepository(Billet::class)->createQueryBuilder('b')
            ->orderBy('b.id', 'DESC');
            
        // Apply type filter if selected
        if (!empty($filterType)) {
            $queryBuilder->andWhere('b.typebillet = :type')
                ->setParameter('type', $filterType);
        }
        
        $query = $queryBuilder->getQuery();

        // Default to 3 items per page
        $itemsPerPage = $request->query->getInt('limit', 3);
        
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $itemsPerPage
        );
        
        // Calculate total number of pages
        $totalItems = $pagination->getTotalItemCount();
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Calculate total value of tickets
        $totalValue = 0;
        foreach ($pagination->getItems() as $billet) {
            if ($billet->getStatut() === 'Valide') {
                $totalValue += $billet->getPrix() * $billet->getQuantite();
            }
        }

        // Default currency
        $currency = $request->query->get('currency', 'EUR');
        $rate = $this->exchangeRates[$currency] ?? 1.0;
        
        return $this->render('billet/index.html.twig', [
            'pagination' => $pagination,
            'totalValue' => $totalValue,
            'exchangeRates' => $this->exchangeRates,
            'selectedCurrency' => $currency,
            'selectedRate' => $rate,
            'totalPages' => $totalPages,
            'itemsPerPage' => $itemsPerPage,
            'ticketTypes' => $this->ticketTypes,
            'filterType' => $filterType,
        ]);
    }

    #[Route('/ticket/{id}/view', name: 'app_ticket_view')]
    public function view(Billet $billet): Response
    {
        // Generate QR code for ticket verification
        $ticketUrl = $this->generateUrl('app_ticket_verify', ['id' => $billet->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $qrCode = $this->qrBuilder
            ->data($ticketUrl)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(10)
            ->writer(new SvgWriter())
            ->build();
        
        $qrDataUri = $qrCode->getDataUri();
        
        return $this->render('billet/view.html.twig', [
            'billet' => $billet,
            'qrCode' => $qrDataUri,
            'exchangeRates' => $this->exchangeRates
        ]);
    }
    
    #[Route('/ticket/{id}/verify', name: 'app_ticket_verify')]
    public function verify(Billet $billet): Response
    {
        return $this->render('billet/verify.html.twig', [
            'billet' => $billet,
            'isValid' => $billet->getStatut() === 'Valide'
        ]);
    }
    
    #[Route('/ticket/{id}/mark-used', name: 'app_ticket_mark_used', methods: ['POST'])]
    public function markAsUsed(Request $request, Billet $billet, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('mark_used'.$billet->getId(), $request->request->get('_token'))) {
            // Only mark as used if the ticket is currently valid
            if ($billet->getStatut() === 'Valide') {
                $billet->setStatut('Utilisé');
                $entityManager->flush();
                
                $this->addFlash('success', 'Le billet a été marqué comme utilisé.');
            } else {
                $this->addFlash('warning', 'Le billet n\'est pas valide et ne peut pas être marqué comme utilisé.');
            }
        }
        
        return $this->redirectToRoute('app_ticket_verify', ['id' => $billet->getId()]);
    }

    #[Route('/ticket/new', name: 'app_ticket_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $billet = new Billet();
        
        $form = $this->createFormBuilder($billet)
            ->add('typebillet', ChoiceType::class, [
                'label' => 'Type de billet',
                'choices' => [
                    'Standard' => 'Standard',
                    'VIP' => 'VIP',
                    'Early Bird' => 'Early Bird',
                    'Groupe' => 'Groupe',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'attr' => ['class' => 'form-control']
            ])
            ->add('quantite', NumberType::class, [
                'label' => 'Quantité',
                'attr' => ['class' => 'form-control']
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Valide' => 'Valide',
                    'Non valide' => 'Non valide',
                    'Annulé' => 'Annulé',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('eventid', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',
                'label' => 'Événement',
                'attr' => ['class' => 'form-control']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Créer',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ])
            ->getForm();
            
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($billet);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le billet a été créé avec succès.');
            
            // Count total tickets to determine the latest page
            $totalTickets = $entityManager->getRepository(Billet::class)->count([]);
            $itemsPerPage = $request->query->getInt('limit', 3);
            $latestPage = ceil($totalTickets / $itemsPerPage);
            
            // Redirect to the latest page
            return $this->redirectToRoute('app_ticket_index', [
                'page' => $latestPage,
                'limit' => $itemsPerPage
            ]);
        }
        
        return $this->render('billet/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ticket/{id}/edit', name: 'app_ticket_edit')]
    public function edit(Request $request, Billet $billet, EntityManagerInterface $entityManager): Response
    {
        // Store current pagination parameters
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 3);
        $currency = $request->query->get('currency', 'EUR');
        $type = $request->query->get('type', '');
        
        $form = $this->createFormBuilder($billet)
            ->add('typebillet', ChoiceType::class, [
                'label' => 'Type de billet',
                'choices' => [
                    'Standard' => 'Standard',
                    'VIP' => 'VIP',
                    'Early Bird' => 'Early Bird',
                    'Groupe' => 'Groupe',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'attr' => ['class' => 'form-control']
            ])
            ->add('quantite', NumberType::class, [
                'label' => 'Quantité',
                'attr' => ['class' => 'form-control']
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Valide' => 'Valide',
                    'Non valide' => 'Non valide',
                    'Annulé' => 'Annulé',
                    'Utilisé' => 'Utilisé',
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('eventid', EntityType::class, [
                'class' => Event::class,
                'choice_label' => 'name',
                'label' => 'Événement',
                'attr' => ['class' => 'form-control']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Mettre à jour',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ])
            ->getForm();
            
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Le billet a été mis à jour avec succès.');
            
            // Redirect back to the same page with pagination parameters
            return $this->redirectToRoute('app_ticket_index', [
                'page' => $page,
                'limit' => $limit,
                'currency' => $currency,
                'type' => $type
            ]);
        }
        
        return $this->render('billet/edit.html.twig', [
            'billet' => $billet,
            'form' => $form->createView(),
            'page' => $page,
            'limit' => $limit,
            'currency' => $currency,
            'type' => $type
        ]);
    }

    #[Route('/tickets/export', name: 'app_ticket_export_pdf', methods: ['GET'])]
    public function exportPdf(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Get filter parameters
        $filterType = $request->query->get('type', '');
        
        // Build query with filters
        $queryBuilder = $entityManager->getRepository(Billet::class)->createQueryBuilder('b')
            ->orderBy('b.id', 'DESC');
            
        // Apply type filter if selected
        if (!empty($filterType)) {
            $queryBuilder->andWhere('b.typebillet = :type')
                ->setParameter('type', $filterType);
        }
        
        $billets = $queryBuilder->getQuery()->getResult();
        
        // Default currency
        $currency = $request->query->get('currency', 'EUR');
        $rate = $this->exchangeRates[$currency] ?? 1.0;
        
        // Calculate total value of tickets
        $totalValue = 0;
        foreach ($billets as $billet) {
            if ($billet->getStatut() === 'Valide') {
                $totalValue += $billet->getPrix() * $billet->getQuantite();
            }
        }
        
        // Generate HTML for PDF
        $html = $this->renderView('billet/pdf/tickets_pdf.html.twig', [
            'billets' => $billets,
            'totalValue' => $totalValue,
            'selectedCurrency' => $currency,
            'selectedRate' => $rate,
            'filterType' => $filterType,
        ]);
        
        // Configure Dompdf
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        // Instantiate Dompdf
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Generate file name
        $fileName = 'tickets_export_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Stream the file
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ]
        );
    }

    #[Route('/ticket/{id}/delete', name: 'app_ticket_delete', methods: ['POST'])]
    public function delete(Request $request, Billet $billet, EntityManagerInterface $entityManager): Response
    {
        // Store current pagination parameters
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 3);
        $currency = $request->query->get('currency', 'EUR');
        $type = $request->query->get('type', '');
        
        if ($this->isCsrfTokenValid('delete'.$billet->getId(), $request->request->get('_token'))) {
            $entityManager->remove($billet);
            $entityManager->flush();
            
            $this->addFlash('success', 'Le billet a été supprimé avec succès.');
            
            // Check if we need to adjust the page number after deletion
            $queryBuilder = $entityManager->getRepository(Billet::class)->createQueryBuilder('b')
                ->select('COUNT(b.id)');
            
            // Apply type filter if provided
            if (!empty($type)) {
                $queryBuilder->andWhere('b.typebillet = :type')
                    ->setParameter('type', $type);
            }
            
            $totalTickets = $queryBuilder->getQuery()->getSingleScalarResult();
            $maxPage = max(1, ceil($totalTickets / $limit));
            
            if ($page > $maxPage) {
                $page = $maxPage;
            }
        }

        return $this->redirectToRoute('app_ticket_index', [
            'page' => $page,
            'limit' => $limit,
            'currency' => $currency,
            'type' => $type
        ]);
    }
} 