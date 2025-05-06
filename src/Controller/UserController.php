<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Entity\Reservation;
use App\Entity\ReservationBillet;
use App\Repository\BilletRepository;
use App\Repository\EvenementRepository;
use App\Repository\ReservationRepository;
use App\Repository\ReservationBilletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
#[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    #[Route('/dashboard', name: 'user_dashboard')]
    public function dashboard(
        EvenementRepository $evenementRepository, 
        BilletRepository $billetRepository, 
        ReservationRepository $reservationRepository, 
        ReservationBilletRepository $reservationBilletRepository
    ): Response
    {
        // Get upcoming events (events with dates after today)
        $upcomingEvents = $evenementRepository->findUpcomingEvents(5);
        
        // Get user's tickets
        $user = $this->getUser();
        
        // Get ticket reservations for the user
        $ticketReservations = $reservationRepository->findTicketReservationsByUser($user->getId());
        
        // Create a new array with all the necessary data for display
        $userTickets = [];
        
        foreach ($ticketReservations as $reservation) {
            // For each reservation, find the associated tickets
            $reservationBillets = $reservationBilletRepository->findBy(['reservationID' => $reservation->getId()]);
            
            foreach ($reservationBillets as $reservationBillet) {
                // Get the ticket details
                $billet = $billetRepository->find($reservationBillet->getBilletID());
                
                if ($billet) {
                    // Get the event details
                    $event = $evenementRepository->find($billet->getEventID());
                    
                    if ($event) {
                        // Only include upcoming events
                        $eventDate = $event->getDateDebut();
                        $today = new \DateTime();
                        
                        if ($eventDate > $today) {
                            // Create a data structure for the template
                            $userTickets[] = [
                                'reservation' => $reservation,
                                'billet' => $billet,
                                'evenement' => $event,
                                'quantite' => $reservationBillet->getNombreBillet()
                            ];
                        }
                    }
                }
            }
        }
        
        // Sort tickets by event date (ascending)
        usort($userTickets, function($a, $b) {
            return $a['evenement']->getDateDebut() <=> $b['evenement']->getDateDebut();
        });
        
        // Limit to 5 upcoming tickets
        $userTickets = array_slice($userTickets, 0, 5);
        
        return $this->render('user/dashboard.html.twig', [
            'upcoming_events' => $upcomingEvents,
            'user_tickets' => $userTickets,
        ]);
    }
    
    #[Route('/events', name: 'user_events')]
    public function events(EvenementRepository $evenementRepository, Request $request): Response
    {
        $filter = $request->query->get('filter', 'all');
        $type = $request->query->get('type', null);
        $date = $request->query->get('date', null);
        $price = $request->query->get('price', null);
        
        // Base query builder
        $qb = $evenementRepository->createQueryBuilder('e')
            ->where('e.statut NOT IN (:excluded_statuses)')
            ->setParameter('excluded_statuses', ['Annulé', 'Terminé', 'cancelled'])
            ->orderBy('e.dateDebut', 'ASC');
        
        // Apply type filter if specified
        if ($type) {
            $qb->andWhere('e.type = :type')
               ->setParameter('type', $type);
        }
        
        // Apply date filter
        if ($date) {
            $today = new \DateTime('today');
            
            switch ($date) {
                case 'today':
                    $tomorrow = new \DateTime('tomorrow');
                    $qb->andWhere('e.dateDebut >= :today')
                       ->andWhere('e.dateDebut < :tomorrow')
                       ->setParameter('today', $today)
                       ->setParameter('tomorrow', $tomorrow);
                    break;
                case 'tomorrow':
                    $tomorrow = new \DateTime('tomorrow');
                    $dayAfterTomorrow = new \DateTime('tomorrow +1 day');
                    $qb->andWhere('e.dateDebut >= :tomorrow')
                       ->andWhere('e.dateDebut < :dayAfterTomorrow')
                       ->setParameter('tomorrow', $tomorrow)
                       ->setParameter('dayAfterTomorrow', $dayAfterTomorrow);
                    break;
                case 'week':
                    $endOfWeek = new \DateTime('sunday this week');
                    if ($today->format('w') == 0) { // If today is Sunday
                        $endOfWeek = new \DateTime('today +7 days');
                    }
                    $qb->andWhere('e.dateDebut >= :today')
                       ->andWhere('e.dateDebut <= :endOfWeek')
                       ->setParameter('today', $today)
                       ->setParameter('endOfWeek', $endOfWeek);
                    break;
                case 'month':
                    $endOfMonth = new \DateTime('last day of this month');
                    $qb->andWhere('e.dateDebut >= :today')
                       ->andWhere('e.dateDebut <= :endOfMonth')
                       ->setParameter('today', $today)
                       ->setParameter('endOfMonth', $endOfMonth);
                    break;
            }
        }
        // If no date filter but we have a time-based filter
        else {
            // Fetch events based on time filter
            if ($filter === 'upcoming') {
                $today = new \DateTime();
                $qb->andWhere('e.dateDebut >= :today')
                   ->setParameter('today', $today);
            } elseif ($filter === 'today') {
                $today = new \DateTime('today');
                $tomorrow = new \DateTime('tomorrow');
                $qb->andWhere('e.dateDebut >= :today')
                   ->andWhere('e.dateDebut < :tomorrow')
                   ->setParameter('today', $today)
                   ->setParameter('tomorrow', $tomorrow);
            } elseif ($filter === 'weekend') {
                $friday = new \DateTime('next friday');
                if ((new \DateTime())->format('w') >= 5) { // If today is Friday, Saturday or Sunday
                    $friday = new \DateTime('this friday');
                }
                $monday = clone $friday;
                $monday->modify('+3 days'); // Monday after weekend
                
                $qb->andWhere('e.dateDebut >= :friday')
                   ->andWhere('e.dateDebut < :monday')
                   ->setParameter('friday', $friday)
                   ->setParameter('monday', $monday);
            }
        }
        
        $events = $qb->getQuery()->getResult();
        
        return $this->render('user/events.html.twig', [
            'events' => $events,
            'filter' => $filter,
            'typeFilter' => $type,
            'dateFilter' => $date,
            'priceFilter' => $price
        ]);
    }
    
    #[Route('/event/{id}', name: 'event_details')]
    public function eventDetails(
        int $id, 
        EvenementRepository $evenementRepository,
        BilletRepository $billetRepository
    ): Response {
        $event = $evenementRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }
        
        // Fetch all tickets for this event
        $tickets = $billetRepository->findAvailableTicketsByEvent($id);
        error_log("Found " . count($tickets) . " tickets for event " . $id);
        
        return $this->render('user/event_details.html.twig', [
            'event' => $event,
            'tickets' => $tickets
        ]);
    }
    
    #[Route('/my-events', name: 'user_my_events')]
    public function myEvents(EvenementRepository $evenementRepository): Response
    {
        $user = $this->getUser();
        
        // Find events through the jointable where user is a participant
        $myEvents = $evenementRepository->createQueryBuilder('e')
            ->innerJoin('App\Entity\JoinTable', 'j', 'WITH', 'j.eventID = e.ID')
            ->where('j.userID = :userId')
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getResult();
        
        return $this->render('user/my_events.html.twig', [
            'my_events' => $myEvents,
        ]);
    }
    
    #[Route('/my-events/new', name: 'user_create_event')]
    public function createEvent(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Handle event creation form
        
        return $this->render('user/create_event.html.twig');
    }
    
    #[Route('/my-events/edit/{id}', name: 'user_edit_event')]
    public function editEvent(Evenement $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Check if user is the organizer
        if ($event->getOrganisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only edit your own events');
        }
        
        // Handle event edit form
        
        return $this->render('user/edit_event.html.twig', [
            'event' => $event,
        ]);
    }
    
    #[Route('/tickets', name: 'user_tickets')]
    public function tickets(ReservationRepository $reservationRepository, ReservationBilletRepository $reservationBilletRepository, BilletRepository $billetRepository, EvenementRepository $evenementRepository): Response
    {
        $user = $this->getUser();
        
        // Get ticket reservations for the user
        $ticketReservations = $reservationRepository->findTicketReservationsByUser($user->getId());
        
        // Create a new array with all the necessary data for display
        $userTickets = [];
        
        foreach ($ticketReservations as $reservation) {
            // For each reservation, find the associated tickets
            $reservationBillets = $reservationBilletRepository->findBy(['reservationID' => $reservation->getId()]);
            
            foreach ($reservationBillets as $reservationBillet) {
                // Get the ticket details
                $billet = $billetRepository->find($reservationBillet->getBilletID());
                
                if ($billet) {
                    // Get the event details
                    $event = $evenementRepository->find($billet->getEventID());
                    
                    if ($event) {
                        // Create a data structure for the template
                        $userTickets[] = [
                            'reservation' => $reservation,
                            'billet' => $billet,
                            'evenement' => $event,
                            'quantite' => $reservationBillet->getNombreBillet()
                        ];
                    }
                }
            }
        }
        
        return $this->render('user/tickets.html.twig', [
            'reservations' => $userTickets,
        ]);
    }
    
    #[Route('/refund-ticket/{id}', name: 'user_refund_ticket', methods: ['POST'])]
    public function refundTicket(
        int $id,
        ReservationRepository $reservationRepository,
        ReservationBilletRepository $reservationBilletRepository,
        BilletRepository $billetRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        Request $request
    ): Response {
        $user = $this->getUser();
        $senderAddress = $this->getParameter('mailer_sender_address');
        $deleteTicket = $request->request->get('delete_ticket', false);
        
        // Find the reservation
        $reservation = $reservationRepository->find($id);
        
        // Check if reservation exists and belongs to the current user
        if (!$reservation || $reservation->getUtilisateurID() !== $user->getId()) {
            $this->addFlash('danger', 'Ticket reservation not found or not authorized to refund.');
            return $this->redirectToRoute('user_tickets');
        }
        
        // Check if already refunded
        if ($reservation->getStatut() === 'Remboursé') {
            $this->addFlash('warning', 'This ticket has already been refunded.');
            return $this->redirectToRoute('user_tickets');
        }
        
        // Check if the event hasn't happened yet (can only refund future events)
        $reservationBillets = $reservationBilletRepository->findBy(['reservationID' => $reservation->getId()]);
        
        if (empty($reservationBillets)) {
            $this->addFlash('danger', 'No tickets found for this reservation.');
            return $this->redirectToRoute('user_tickets');
        }
        
        // Get the first reservation billet to access the event
        $firstReservationBillet = $reservationBillets[0];
        $billet = $billetRepository->find($firstReservationBillet->getBilletID());
        $event = $billet->getEvenement();
        
        $eventDate = $event->getDateDebut();
        $today = new \DateTime();
        
        // Prevent refunding if event has already happened or is happening today
        if ($eventDate <= $today) {
            $this->addFlash('danger', 'Unable to refund tickets for past or ongoing events.');
            return $this->redirectToRoute('user_tickets');
        }
        
        // For each reservation billet, restore the ticket inventory
        foreach ($reservationBillets as $reservationBillet) {
            $ticketId = $reservationBillet->getBilletID();
            $ticket = $billetRepository->find($ticketId);
            
            if ($ticket) {
                // Restore ticket quantity
                $quantityToRestore = $reservationBillet->getNombreBillet();
                $ticket->setQuantite($ticket->getQuantite() + $quantityToRestore);
                $entityManager->persist($ticket);
            }
            
            // If delete option is enabled, remove the reservation billet
            if ($deleteTicket) {
                $entityManager->remove($reservationBillet);
            }
        }
        
        // Update reservation status or delete if requested
        if ($deleteTicket) {
            $entityManager->remove($reservation);
            $this->addFlash('success', 'Your ticket has been refunded and deleted successfully.');
        } else {
            // Just mark as refunded
            $reservation->setStatut('Remboursé');
            $entityManager->persist($reservation);
            $this->addFlash('success', 'Your ticket has been successfully refunded.');
        }
        
        // Flush changes to database
        $entityManager->flush();
        
        // Send refund confirmation email
        if ($user->getEmail() && $senderAddress) {
            try {
                $email = (new Email())
                    ->from($senderAddress)
                    ->to($user->getEmail())
                    ->subject('Your Ticket Refund Confirmation')
                    ->html($this->renderView('emails/user_ticket_refunded.html.twig', [
                        'ticket' => $billet,
                        'event' => $event,
                        'user' => $user,
                        'reservation' => $reservation,
                        'deleted' => $deleteTicket
                    ]));
                
                $mailer->send($email);
            } catch (\Exception $e) {
                // Log the error but don't stop the refund process
                error_log('Failed to send refund confirmation email: ' . $e->getMessage());
            }
        }
        
        return $this->redirectToRoute('user_tickets');
    }
    
    #[Route('/reserve-ticket/{id}', name: 'user_reserve_ticket', methods: ['POST'])]
    public function reserveTicket(
        int $id, 
        Request $request, 
        BilletRepository $billetRepository, 
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        $senderAddress = $this->getParameter('mailer_sender_address');

        // Get the ticket from the database
        $ticket = $billetRepository->find($id);
        
        if (!$ticket) {
            $this->addFlash('error', 'Ticket not found.');
            $eventId = $ticket?->getEvenement()?->getId();
            return $eventId ? $this->redirectToRoute('event_details', ['id' => $eventId]) : $this->redirectToRoute('user_events');
        }
        
        $event = $ticket->getEvenement();

        // Get the quantity from the form
        $quantity = (int) $request->request->get('quantity', 1);
        
        // Validate quantity
        if ($quantity <= 0 || $quantity > 10) {
            $this->addFlash('error', 'Invalid ticket quantity. Please select between 1 and 10 tickets.');
            return $this->redirectToRoute('event_details', ['id' => $ticket->getEventID()]);
        }
        
        // Create a new reservation
        $reservation = new Reservation();
        if (method_exists($reservation, 'setUtilisateur')) {
             $reservation->setUtilisateur($user); 
        } else {
             $reservation->setUtilisateurID($user->getId());
        }
        $reservation->setDateReservation(new \DateTime());
        $reservation->setStatut('Confirmée');
        $reservation->setType('BILLET');
        
        // Persist the reservation to get an ID
        $entityManager->persist($reservation);
        
        // Create reservation_billet link
        $reservationBillet = new ReservationBillet();
        if (method_exists($reservationBillet, 'setReservation')) {
            $reservationBillet->setReservation($reservation); 
        } else {
             $reservationBillet->setReservationID($reservation->getId());
        }
        if (method_exists($reservationBillet, 'setBillet')) {
             $reservationBillet->setBillet($ticket);
        } else {
             $reservationBillet->setBilletID($ticket->getId());
        }
        $reservationBillet->setNombreBillet($quantity);
        
        // Persist the reservation_billet link
        $entityManager->persist($reservationBillet);

        $entityManager->flush();
        
        // Send Confirmation Email
        if ($user && $user->getEmail() && $senderAddress) { 
            try {
                $email = (new Email())
                    ->from($senderAddress) 
                    ->to($user->getEmail()) 
                    ->subject('Your Ticket Reservation Confirmation')
                    ->html($this->renderView('emails/user_ticket_reserved.html.twig', [
                        'ticket' => $ticket,
                        'event' => $event,
                        'user' => $user,
                        'reservation' => $reservation,
                        'quantity' => $quantity
                    ]));
                
                $mailer->send($email);
                $this->addFlash('success', 'Tickets reserved successfully! Confirmation email sent to ' . $user->getEmail());

            } catch (\Exception $e) {
                $this->addFlash('warning', 'Tickets reserved successfully, but failed to send confirmation email. Error: ' . $e->getMessage());
            }
        } else {
             $this->addFlash('success', 'Tickets reserved successfully! (Could not send email - user/sender missing)');
        }
        
        return $this->redirectToRoute('user_tickets');
    }
    
    #[Route('/profile', name: 'user_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        
        // Handle form submission
        if ($request->isMethod('POST')) {
            // Get form data
            $userData = $request->request->all();
            
            // Update user information
            if (isset($userData['firstName'])) {
                $user->setPrenom($userData['firstName']);
            }
            
            if (isset($userData['lastName'])) {
                $user->setNom($userData['lastName']);
            }
            
            if (isset($userData['email'])) {
                $user->setEmail($userData['email']);
            }
            
            if (isset($userData['phone'])) {
                $user->setNumeroTelephone($userData['phone']);
            }
            
            if (isset($userData['address'])) {
                $user->setAdresse($userData['address']);
            }
            
            if (isset($userData['bio'])) {
                // Check if the bio field exists in the entity before setting it
                if (method_exists($user, 'setBio')) {
                    $user->setBio($userData['bio']);
                }
            }
            
            // Save changes to database
            $entityManager->persist($user);
            $entityManager->flush();
            
            // Add success message
            $this->addFlash('success', 'Your profile has been updated successfully!');
            
            // Redirect to profile page to prevent form resubmission
            return $this->redirectToRoute('user_profile');
        }
        
        return $this->render('user/profile.html.twig');
    }
    
    #[Route('/profile/change-password', name: 'user_change_password', methods: ['POST'])]
    public function changePassword(
        Request $request, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        
        $currentPassword = $request->request->get('currentPassword');
        $newPassword = $request->request->get('newPassword');
        $confirmPassword = $request->request->get('confirmPassword');
        
        // Validate current password
        if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
            $this->addFlash('error', 'The current password is incorrect.');
            return $this->redirectToRoute('user_profile');
        }
        
        // Validate new password
        if ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'The new passwords do not match.');
            return $this->redirectToRoute('user_profile');
        }
        
        // Update password
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setMotdepasse($hashedPassword);
        
        // Save to database
        $entityManager->persist($user);
        $entityManager->flush();
        
        $this->addFlash('success', 'Your password has been changed successfully!');
        return $this->redirectToRoute('user_profile');
    }
    
    #[Route('/profile/upload-photo', name: 'user_upload_photo', methods: ['POST'])]
    public function uploadProfilePhoto(
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        
        // Handle file upload
        $uploadedFile = $request->files->get('profilePicture');
        
        if ($uploadedFile) {
            $newFilename = 'profile-'.$user->getID().'-'.uniqid().'.'.$uploadedFile->guessExtension();
            
            try {
                // Move the file to the directory where profile pictures are stored
                $uploadedFile->move(
                    $this->getParameter('profile_pictures_directory'),
                    $newFilename
                );
                
                // Update user entity with new photo path
                $user->setPhotoProfil($newFilename);
                
                // Save changes to database
                $entityManager->persist($user);
                $entityManager->flush();
                
                $this->addFlash('success', 'Your profile picture has been updated successfully!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while uploading your profile picture: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'No file uploaded or the file is invalid.');
        }
        
        return $this->redirectToRoute('user_profile');
    }
    
    #[Route('/profile/delete-account', name: 'user_delete_account', methods: ['POST'])]
    public function deleteAccount(
        Request $request, 
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var \App\Entity\Utilisateur $user */
        $user = $this->getUser();
        
        // Check if confirmation is correct
        $confirmation = $request->request->get('deleteConfirm');
        if ($confirmation !== 'DELETE') {
            $this->addFlash('error', 'Account deletion confirmation failed. Please type DELETE to confirm.');
            return $this->redirectToRoute('user_profile');
        }
        
        try {
            // Handle user's reservations and events relationships before deletion
            // This depends on your application logic, but typically you would:
            // 1. Cancel user's reservations
            foreach ($user->getReservations() as $reservation) {
                $reservation->setStatut('Annulée');
                $entityManager->persist($reservation);
            }
            
            // 2. Remove user from events they've joined
            foreach ($user->getEvenements() as $event) {
                $user->removeEvenement($event);
            }
            
            // 3. Anonymize user data rather than full deletion (GDPR compliant)
            $user->setEmail('deleted_'.$user->getID().'@deleted.com');
            $user->setPrenom('Deleted');
            $user->setNom('User');
            $user->setNumeroTelephone(null);
            $user->setAdresse(null);
            $user->setPhotoProfil(null);
            
            // Set a random password so the account cannot be accessed
            $randomPassword = bin2hex(random_bytes(20));
            $hashedPassword = $passwordHasher->hashPassword($user, $randomPassword);
            $user->setMotdepasse($hashedPassword);
            
            // 4. Save changes
            $entityManager->persist($user);
            $entityManager->flush();
            
            // 5. Log the user out
            $request->getSession()->invalidate();
            $this->container->get('security.token_storage')->setToken(null);
            
            $this->addFlash('success', 'Your account has been deleted successfully.');
            return $this->redirectToRoute('app_login');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while deleting your account: ' . $e->getMessage());
            return $this->redirectToRoute('user_profile');
        }
    }
    
    #[Route('/book-tickets/{id}', name: 'book_tickets', methods: ['POST'])]
    public function bookTickets(
        Request $request,
        int $id, 
        EvenementRepository $evenementRepository,
        BilletRepository $billetRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $event = $evenementRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }
        
        if ($event->getStatut() !== 'En cours' && !empty($event->getStatut())) {
            $this->addFlash('danger', 'This event is not currently accepting bookings');
            return $this->redirectToRoute('event_details', ['id' => $id]);
        }
        
        // Get the submitted data from POST parameters
        $ticketsData = $request->request->all();
        $ticketsBooked = false;
        $totalAmount = 0;
        $totalTickets = 0;
        $bookedTicketDetails = [];
        
        // Process each selected ticket
        if (isset($ticketsData['tickets']) && is_array($ticketsData['tickets'])) {
            foreach ($ticketsData['tickets'] as $ticketId => $ticketData) {
                // Check if ticket was selected
                if (!isset($ticketData['selected'])) {
                    continue;
                }
                
                $quantity = (int) ($ticketData['quantity'] ?? 0);
                if ($quantity <= 0) {
                    continue;
                }
                
                // Get the ticket from database
                $ticket = $billetRepository->find($ticketId);
                if (!$ticket) {
                    continue;
                }
                
                // Check if enough tickets are available
                if ($ticket->getQuantite() < $quantity) {
                    $this->addFlash('warning', 'Not enough tickets available for ' . $ticket->getTypeBillet());
                    continue;
                }
                
                // Create a reservation
                $reservation = new Reservation();
                $reservation->setUtilisateur($this->getUser());
                $reservation->setDateReservation(new \DateTime());
                $reservation->setStatut('Confirmée');
                $reservation->setType('BILLET');
                
                // Persist the reservation to get an ID
                $entityManager->persist($reservation);
                $entityManager->flush();
                
                // Create reservation ticket
                $reservationBillet = new ReservationBillet();
                
                // IMPORTANT: Set both primary key fields manually
                // Always set the IDs directly to ensure they are assigned properly
                $reservationBillet->setReservationID($reservation->getId());
                $reservationBillet->setBilletID($ticket->getId());
                
                // Now we can also set the relation objects if the methods exist
                if (method_exists($reservationBillet, 'setReservation')) {
                    $reservationBillet->setReservation($reservation);
                }
                
                if (method_exists($reservationBillet, 'setBillet')) {
                    $reservationBillet->setBillet($ticket);
                }
                
                // Set quantity
                if (method_exists($reservationBillet, 'setNombreBillet')) {
                    $reservationBillet->setNombreBillet($quantity);
                } else if (method_exists($reservationBillet, 'setQuantite')) {
                    $reservationBillet->setQuantite($quantity);
                }
                
                // Update available ticket count
                $ticket->setQuantite($ticket->getQuantite() - $quantity);
                
                // Calculate total
                $ticketTotal = $ticket->getPrix() * $quantity;
                $totalAmount += $ticketTotal;
                $totalTickets += $quantity;
                
                // Store ticket details for the success message
                $bookedTicketDetails[] = [
                    'type' => $ticket->getTypeBillet(),
                    'quantity' => $quantity,
                    'price' => $ticket->getPrix(),
                    'total' => $ticketTotal
                ];
                
                // Persist to database
                $entityManager->persist($reservationBillet);
                $entityManager->persist($ticket);
                
                $ticketsBooked = true;
            }
        }
        
        if ($ticketsBooked) {
            $entityManager->flush();
            
            // Build a more detailed success message
            $message = '<strong>Booking Successful!</strong><br>';
            $message .= 'You have booked ' . $totalTickets . ' ticket' . ($totalTickets > 1 ? 's' : '') . ' for ' . $event->getNom() . '.<br>';
            $message .= '<ul class="mb-0 mt-2">';
            
            foreach ($bookedTicketDetails as $detail) {
                $message .= '<li>' . $detail['quantity'] . 'x ' . $detail['type'] . ': $' . number_format($detail['total'], 2) . '</li>';
            }
            
            $message .= '</ul>';
            $message .= '<div class="mt-2"><strong>Total:</strong> $' . number_format($totalAmount, 2) . '</div>';
            
            $this->addFlash('success', $message);
            return $this->redirectToRoute('user_tickets');
        } else {
            $this->addFlash('warning', 'No tickets were selected for booking');
            return $this->redirectToRoute('event_details', ['id' => $id]);
        }
    }
} 