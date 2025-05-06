<?php

namespace App\Controller;

use App\Repository\UtilisateurRepository;
use App\Repository\EvenementRepository;
use App\Repository\TournoiRepository;
use App\Repository\BilletRepository;
use App\Service\QrCodeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Billet;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    private $qrCodeService;
    private LoggerInterface $logger;
    
    public function __construct(QrCodeService $qrCodeService, LoggerInterface $logger)
    {
        $this->qrCodeService = $qrCodeService;
        $this->logger = $logger;
    }
    
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(
        UtilisateurRepository $utilisateurRepository,
        EvenementRepository $evenementRepository,
        TournoiRepository $tournoiRepository,
        BilletRepository $billetRepository
    ): Response
    {
        // Get counts for dashboard
        $userCount = $utilisateurRepository->count([]);
        $eventCount = $evenementRepository->count([]);
        $tournamentCount = $tournoiRepository->count([]);
        $ticketCount = $billetRepository->count([]);
        
        // Get some recent data
        $recentUsers = $utilisateurRepository->findBy([], ['ID' => 'DESC'], 5);
        $recentEvents = $evenementRepository->findBy([], ['ID' => 'DESC'], 5);
        
        return $this->render('admin/dashboard.html.twig', [
            'user_count' => $userCount,
            'event_count' => $eventCount,
            'tournament_count' => $tournamentCount,
            'ticket_count' => $ticketCount,
            'recent_users' => $recentUsers,
            'recent_events' => $recentEvents,
        ]);
    }
    
    #[Route('/users', name: 'admin_users')]
    public function users(UtilisateurRepository $utilisateurRepository): Response
    {
        $users = $utilisateurRepository->findAll();
        
        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }
    
    #[Route('/events', name: 'admin_events')]
    public function events(EvenementRepository $evenementRepository): Response
    {
        $events = $evenementRepository->findAll();
        
        return $this->render('admin/events.html.twig', [
            'events' => $events,
        ]);
    }
    
    #[Route('/tournaments', name: 'admin_tournaments')]
    public function tournaments(TournoiRepository $tournoiRepository): Response
    {
        $tournaments = $tournoiRepository->findAll();
        
        return $this->render('admin/tournaments.html.twig', [
            'tournaments' => $tournaments,
        ]);
    }
    
    #[Route('/tickets', name: 'admin_tickets')]
    public function tickets(
        Request $request, 
        BilletRepository $billetRepository, 
        EvenementRepository $evenementRepository, 
        PaginatorInterface $paginator
    ): Response
    {
        $searchType = $request->query->get('search', '');
        
        // Create a query builder for tickets
        $queryBuilder = $billetRepository->createQueryBuilder('b');

        // Apply search filter if provided
        if (!empty($searchType)) {
            $queryBuilder->where('b.typeBillet = :searchType')
                         ->setParameter('searchType', $searchType);
        }
        
        // Get the query object from the query builder
        $query = $queryBuilder->getQuery();

        // Paginate the results
        $pagination = $paginator->paginate(
            $query, // Doctrine Query, not results
            $request->query->getInt('page', 1), // Get page number from request, default to 1
            5 // Items per page
        );

        // We might still need all events for dropdowns or other purposes
        $events = $evenementRepository->findAll(); 
        
        return $this->render('admin/tickets.html.twig', [
            'pagination' => $pagination, // Pass the pagination object to Twig
            'events' => $events,       // Keep events if needed for forms/dropdowns
            'searchType' => $searchType
        ]);
    }
    
    #[Route('/ticket/edit/{id}', name: 'admin_ticket_edit')]
    public function editTicket(Billet $ticket, Request $request, EntityManagerInterface $entityManager, EvenementRepository $evenementRepository): Response
    {
        if ($request->isMethod('POST')) {
            // Get form data
            $typeBillet = $request->request->get('typeBillet');
            $prix = $request->request->get('prix');
            $quantite = $request->request->get('quantite');
            $statut = $request->request->get('statut');
            $eventID = $request->request->get('eventID');
            
            // Initialize errors array
            $errors = [];
            
            // Validate ticket type
            if (empty($typeBillet)) {
                $errors['typeBillet'] = 'Ticket type is required';
            } elseif (!in_array($typeBillet, ['Gradin', 'Virage', 'VIP'])) {
                $errors['typeBillet'] = 'Invalid ticket type';
            }
            
            // Validate price
            if (empty($prix)) {
                $errors['prix'] = 'Price is required';
            } elseif (!is_numeric($prix) || floatval($prix) < 0) {
                $errors['prix'] = 'Price must be a positive number';
            }
            
            // Validate quantity
            if (empty($quantite)) {
                $errors['quantite'] = 'Quantity is required';
            } elseif (!ctype_digit((string) $quantite) || intval($quantite) <= 0) {
                $errors['quantite'] = 'Quantity must be a positive integer';
            }
            
            // Validate status
            if (empty($statut)) {
                $errors['statut'] = 'Status is required';
            } elseif (!in_array($statut, ['Valide', 'Annulé', 'Non valide'])) {
                $errors['statut'] = 'Invalid status';
            }
            
            // Validate event
            if (empty($eventID)) {
                $errors['eventID'] = 'Event is required';
            } else {
                $event = $evenementRepository->find($eventID);
                if (!$event) {
                    $errors['eventID'] = 'Invalid event';
                }
            }
            
            // If no errors, update the ticket
            if (empty($errors)) {
                $ticket->setTypeBillet($typeBillet);
                $ticket->setPrix(floatval($prix));
                $ticket->setQuantite(intval($quantite));
                $ticket->setStatut($statut);
                
                if (isset($event)) {
                    $ticket->setEvenement($event);
                    $ticket->setEventID($event->getId());
                }
                
                $entityManager->flush();
                
                $this->addFlash('success', 'Ticket updated successfully');
                return $this->redirectToRoute('admin_tickets');
            } else {
                // Add flash messages for each error
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
            }
        }
        
        return $this->redirectToRoute('admin_tickets');
    }
    
    #[Route('/ticket/delete/{id}', name: 'admin_ticket_delete')]
    public function deleteTicket(Billet $ticket, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($ticket);
        $entityManager->flush();
        
        $this->addFlash('success', 'Ticket successfully deleted');
        return $this->redirectToRoute('admin_tickets');
    }
    
    #[Route('/ticket/add', name: 'admin_ticket_add')]
    public function addTicket(
        Request $request, 
        EntityManagerInterface $entityManager, 
        EvenementRepository $evenementRepository, 
        MailerInterface $mailer
    ): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser(); // Get the logged-in user
        $senderAddress = $this->getParameter('mailer_sender_address'); // Get sender from env/parameters

        if ($request->isMethod('POST')) {
            // Get form data
            $typeBillet = $request->request->get('typeBillet');
            $prix = $request->request->get('prix');
            $quantite = $request->request->get('quantite');
            $statut = $request->request->get('statut');
            $eventID = $request->request->get('eventID');
            $errors = [];
            
            // Validate ticket type
            if (empty($typeBillet)) {
                $errors['typeBillet'] = 'Ticket type is required';
            } elseif (!in_array($typeBillet, ['Gradin', 'Virage', 'VIP'])) {
                $errors['typeBillet'] = 'Invalid ticket type';
            }
            
            // Validate price
            if (empty($prix)) {
                $errors['prix'] = 'Price is required';
            } elseif (!is_numeric($prix) || floatval($prix) <= 0) {
                $errors['prix'] = 'Price must be a positive number';
            }
            
            // Validate quantity
            if (empty($quantite)) {
                $errors['quantite'] = 'Quantity is required';
            } elseif (!ctype_digit((string) $quantite) || intval($quantite) <= 0) {
                $errors['quantite'] = 'Quantity must be a positive integer';
            }
            
            // Validate status
            if (empty($statut)) {
                $errors['statut'] = 'Status is required';
            } elseif (!in_array($statut, ['Valide', 'Annulé', 'Non valide'])) {
                $errors['statut'] = 'Invalid status';
            }
            
            // Validate event
            if (empty($eventID)) {
                $errors['eventID'] = 'Event is required';
            } else {
                $event = $evenementRepository->find($eventID);
                if (!$event) {
                    $errors['eventID'] = 'Invalid event';
                }
            }
            
            // If no errors, create and persist the ticket
            if (empty($errors)) {
                $ticket = new Billet();
                $ticket->setTypeBillet($typeBillet);
                $ticket->setPrix(floatval($prix));
                $ticket->setQuantite(intval($quantite));
                $ticket->setStatut($statut);
                $ticket->setDateAchat(new \DateTime());
                
                if (isset($event)) {
                    $ticket->setEvenement($event);
                    $ticket->setEventID($event->getId());
                }
                
                $entityManager->persist($ticket);
                $entityManager->flush();
                
                // --- Send Confirmation Email --- 
                if ($user && $user->getEmail() && $senderAddress) { 
                    try {
                        $email = (new Email())
                            ->from($senderAddress) // Use configured sender
                            ->to($user->getEmail()) // Send to the logged-in user
                            ->subject('New Ticket Added Confirmation (Admin)')
                            ->html($this->renderView('emails/admin_ticket_added.html.twig', [
                                'ticket' => $ticket,
                                'event' => $event,
                                'user' => $user // Pass user to template if needed
                            ]));
                        
                        $mailer->send($email);
                        $this->addFlash('success', 'Ticket added successfully (Email sent to ' . $user->getEmail() . '). Code: ' . $ticket->getCodeUnique());

                    } catch (\Exception $e) {
                        $this->logger->error('Failed to send ticket addition email: ' . $e->getMessage(), ['exception' => $e]);
                        $this->addFlash('warning', 'Ticket added successfully, but failed to send confirmation email. Error: ' . $e->getMessage() . ' Code: ' . $ticket->getCodeUnique());
                    }
                } else {
                    $this->addFlash('success', 'Ticket added successfully (Could not send email - user/sender missing). Code: ' . $ticket->getCodeUnique());
                }
                // --- End Send Email --- 

            } else {
                // Add flash messages for each error
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
            }
        }
        
        return $this->redirectToRoute('admin_tickets');
    }
    
    #[Route('/event/update-status/{id}', name: 'admin_event_update_status')]
    public function updateEventStatus(int $id, Request $request, EntityManagerInterface $entityManager, EvenementRepository $evenementRepository): Response
    {
        $event = $evenementRepository->find($id);
        
        if (!$event) {
            $this->addFlash('error', 'Event not found.');
            return $this->redirectToRoute('admin_events');
        }
        
        $newStatus = $request->query->get('status', 'En cours');
        $event->setStatut($newStatus);
        
        $entityManager->flush();
        
        $this->addFlash('success', 'Event status has been updated to "' . $newStatus . '".');
        
        return $this->redirectToRoute('admin_events');
    }
    
    #[Route('/event/add-tickets/{id}', name: 'admin_event_add_tickets')]
    public function addEventTickets(
        int $id, 
        Request $request, 
        EntityManagerInterface $entityManager, 
        EvenementRepository $evenementRepository,
        MailerInterface $mailer
    ): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser(); // Get the logged-in user
        $senderAddress = $this->getParameter('mailer_sender_address'); // Get sender from env/parameters

        $event = $evenementRepository->find($id);
        
        if (!$event) {
            $this->addFlash('error', 'Event not found.');
            // Redirect to tickets or events? Let's assume tickets for consistency 
            return $this->redirectToRoute('admin_tickets'); 
        }
        
        if ($request->isMethod('POST')) {
            // Get form data
            $ticketType = $request->request->get('ticketType');
            $price = $request->request->get('price');
            $quantity = $request->request->get('quantity');
            $status = $request->request->get('status', 'Valide');
            $errors = [];
            
            // Validate ticket type
            if (empty($ticketType)) {
                $errors['ticketType'] = 'Ticket type is required';
            } elseif (!in_array($ticketType, ['Gradin', 'Virage', 'VIP'])) {
                $errors['ticketType'] = 'Invalid ticket type';
            }
            
            // Validate price
            if (empty($price)) {
                $errors['price'] = 'Price is required';
            } elseif (!is_numeric($price) || floatval($price) <= 0) {
                $errors['price'] = 'Price must be a positive number';
            }
            
            // Validate quantity
            if (empty($quantity)) {
                $errors['quantity'] = 'Quantity is required';
            } elseif (!ctype_digit((string) $quantity) || intval($quantity) <= 0) {
                $errors['quantity'] = 'Quantity must be a positive integer';
            }
            
            // Validate status
            if (empty($status)) {
                $errors['status'] = 'Status is required';
            } elseif (!in_array($status, ['Valide', 'Annulé', 'Non valide'])) {
                $errors['status'] = 'Invalid status';
            }
            
            // If no errors, create and persist the ticket
            if (empty($errors)) {
                $ticket = new Billet();
                $ticket->setEventID($event->getId());
                $ticket->setEvenement($event);
                $ticket->setTypeBillet($ticketType);
                $ticket->setPrix(floatval($price));
                $ticket->setQuantite(intval($quantity));
                $ticket->setStatut($status);
                $ticket->setDateAchat(new \DateTime());
                
                $entityManager->persist($ticket);
                $entityManager->flush();

                // --- Send Confirmation Email --- 
                if ($user && $user->getEmail() && $senderAddress) { 
                    try {
                        $email = (new Email())
                            ->from($senderAddress) // Use configured sender
                            ->to($user->getEmail()) // Send to the logged-in user
                            ->subject('New Tickets Added for Event!')
                            // We can reuse the same template
                            ->html($this->renderView('emails/admin_ticket_added.html.twig', [ 
                                'ticket' => $ticket, // Pass the newly created ticket
                                'event' => $event,
                                'user' => $user 
                            ]));
                        
                        $mailer->send($email);
                        $this->addFlash('success', 'Added ' . $quantity . ' new ' . $ticketType . ' tickets. (Email sent to ' . $user->getEmail() . '). Code: ' . $ticket->getCodeUnique());

                    } catch (\Exception $e) {
                        $this->logger->error('Failed to send event tickets addition email: ' . $e->getMessage(), ['exception' => $e]);
                        $this->addFlash('warning', 'Added ' . $quantity . ' new ' . $ticketType . ' tickets, but failed to send confirmation email. Error: ' . $e->getMessage() . ' Code: ' . $ticket->getCodeUnique());
                    }
                } else {
                    $this->addFlash('success', 'Added ' . $quantity . ' new ' . $ticketType . ' tickets (Could not send email - user/sender missing). Code: ' . $ticket->getCodeUnique());
                }
                // --- End Send Email --- 
                
                return $this->redirectToRoute('admin_tickets');
            } else {
                // Add flash messages for each error
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('admin/add_tickets.html.twig', [
                    'event' => $event,
                    'errors' => $errors // Make sure errors are passed if needed by this template
                ]);
            }
        }
        
        // Render the form if not POST or if errors occurred before trying to save
        return $this->render('admin/add_tickets.html.twig', [
            'event' => $event,
            'errors' => $errors ?? [] // Pass empty errors if GET request
        ]);
    }
    
    #[Route('/ticket/qrcode/{id}', name: 'admin_ticket_qrcode')]
    public function viewTicketQrCode(Billet $ticket): Response
    {
        // Génère l'URL du QR code pour ce billet
        $qrCodeUrl = $this->qrCodeService->generateQrCodeUrl($ticket->getCodeUnique());
        
        return $this->render('admin/ticket_qrcode.html.twig', [
            'ticket' => $ticket,
            'qr_code_url' => $qrCodeUrl
        ]);
    }
} 