<?php

namespace App\Controller;

use App\Entity\Tournoi;
use App\Form\TournoiType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use App\Entity\Reservation;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Psr\Log\LoggerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;



final class TournoiController extends AbstractController
{
    private $security;


    // Optionally, inject the Security service into the constructor
    public function __construct(Security $security, LoggerInterface $logger)
    {
        $this->security = $security;
        $this->logger = $logger;

    }

    #[Route('/admin/tournoi/', name: 'app_tournoi_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
        {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');

            try {
                // Get the selected statut from the request query parameters (optional)
                $statutFilter = $request->query->get('statut'); 

                // Build the query to filter based on statut if it's set
                $queryBuilder = $entityManager->getRepository(Tournoi::class)->createQueryBuilder('t');

                if ($statutFilter) {
                    // Filter by statut if provided
                    $queryBuilder->where('t.statut = :statut')
                                ->setParameter('statut', $statutFilter);
                }

                // Execute the query
                $tournois = $queryBuilder->getQuery()->getResult();

                return $this->render('tournoi/index.html.twig', [
                    'tournois' => $tournois,
                    'statutFilter' => $statutFilter,  // To keep track of the current filter
                ]);
            } catch (\Exception $e) {
                $this->logger->error('Error fetching tournois', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->addFlash('error', 'An error occurred while fetching tournois.');
                return $this->redirectToRoute('app_dashboard');
            }
        }


    #[Route('/admin/tournoi/new', name: 'app_tournoi_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
        $tournoi = new Tournoi();
        $form = $this->createForm(TournoiType::class, $tournoi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tournoi);
            $entityManager->flush();

            return $this->redirectToRoute('app_tournoi_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tournoi/new.html.twig', [
            'tournoi' => $tournoi,
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

    #[Route('/admin/tournoi/{id}', name: 'app_tournoi_show', methods: ['GET'])]
    public function show(Tournoi $tournoi): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
        return $this->render('tournoi/show.html.twig', [
            'tournoi' => $tournoi,
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

    #[Route('/admin/tournoi/{id}/edit', name: 'app_tournoi_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
        $form = $this->createForm(TournoiType::class, $tournoi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_tournoi_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tournoi/edit.html.twig', [
            'tournoi' => $tournoi,
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

    #[Route('/admin/tournoi/{id}', name: 'app_tournoi_delete', methods: ['POST'])]
    public function delete(Request $request, Tournoi $tournoi, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        try {
        if ($this->isCsrfTokenValid('delete'.$tournoi->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tournoi);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tournoi_index', [], Response::HTTP_SEE_OTHER);}catch (\Exception $e) {
            $this->logger->error('Error fetching users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'An error occurred while fetching users.');
            return $this->redirectToRoute('app_dashboard');
        }
    }


    #[Route('/athlete/tournois/avenir', name: 'app_tournoi_avenir', methods: ['GET'])]
    public function upcomingTournaments(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        try {
        // Get the current date and time
        $now = new \DateTime();

        // Fetch upcoming tournaments (where the start date is in the future)
        $tournois = $entityManager->getRepository(Tournoi::class)
            ->createQueryBuilder('t')
            ->where('t.datedebut > :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();

        // Get the current authenticated user
        $user = $this->getUser();

        // Fetch tournaments the current user is participating in
        $participatingTournaments = $entityManager->getRepository(Tournoi::class)
            ->createQueryBuilder('t')
            ->join('t.participants', 'p')
            ->where('p = :user') // Join the participants relation
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        // Extract tournament IDs the user is participating in
        $participatingTournamentIds = array_map(function ($tournoi) {
            return $tournoi->getId();
        }, $participatingTournaments);

        // Render the template and pass both the upcoming tournaments and the participating IDs
        return $this->render('tournoi/upcoming.html.twig', [
            'tournois' => $tournois,
            'participatingTournamentIds' => $participatingTournamentIds,
        ]);}catch (\Exception $e) {
            $this->logger->error('Error fetching users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'An error occurred while fetching users.');
            return $this->redirectToRoute('app_dashboard');
        }
    }

    #[Route('/athlete/{id}/register', name: 'tournoi_register')]
    public function registerTournament(int $id, EntityManagerInterface $entityManager, Security $security): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        try {
        $user = $security->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour vous inscrire à un tournoi.');
            return $this->redirectToRoute('app_login'); // Rediriger vers la page de connexion
        }

        // Récupérer le tournoi
        $tournoi = $entityManager->getRepository(Tournoi::class)->find($id);
        if (!$tournoi || $tournoi->getStatut() !== 'Prévu') {
            $this->addFlash('error', 'Le tournoi n\'est pas disponible pour l\'inscription.');
            return $this->redirectToRoute('upcoming_tournaments'); // Rediriger vers la page des tournois à venir
        }

        // Ajouter l'utilisateur en tant que participant au tournoi
        $tournoi->addParticipant($user);  // Ensure the addParticipant method is defined in your Tournoi entity

        // Créer une réservation pour l'utilisateur et le tournoi
        $reservation = new Reservation();
        $reservation->setTournois($tournoi);
        $reservation->setUtilisateurId($user);
        $reservation->setDateReservation(new \DateTime());
        $reservation->setStatut('Confirmée');
        $reservation->setType(null); // Type null par défaut

        // Enregistrer la réservation et les modifications dans le tournoi
        $entityManager->persist($reservation);
        $entityManager->persist($tournoi); // Ensure the tournament's participants collection is persisted
        $entityManager->flush();

        // Afficher un message de succès et rediriger vers la liste des tournois
        $this->addFlash('success', 'Votre inscription au tournoi a été confirmée !');
        return $this->redirectToRoute('app_tournoi_avenir'); // Rediriger vers la page des tournois à venir
    }catch (\Exception $e) {
        $this->logger->error('Error fetching users', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        $this->addFlash('error', 'An error occurred while fetching users.');
        return $this->redirectToRoute('app_dashboard');
    }}


    #[Route('/athlete/mesreservations', name: 'mes_reservations')]
    public function myReservations(EntityManagerInterface $entityManager, Security $security): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ATHLETE');
        try {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour voir vos réservations.');
            return $this->redirectToRoute('app_login'); // Rediriger vers la page de connexion
        }

        // Récupérer les réservations confirmées pour l'utilisateur
        $reservations = $entityManager->getRepository(Reservation::class)
            ->createQueryBuilder('r')
            ->where('r.utilisateurid = :user')
            ->andWhere('r.statut = :statut')
            ->setParameter('user', $user)
            ->setParameter('statut', 'Confirmée')
            ->getQuery()
            ->getResult();

        // Passer les réservations à la vue
        return $this->render('tournoi/my_reservations.html.twig', [
            'reservations' => $reservations,
        ]);}catch (\Exception $e) {
            $this->logger->error('Error fetching users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addFlash('error', 'An error occurred while fetching users.');
            return $this->redirectToRoute('app_dashboard');
        }
    }


    #[Route('/reservation/{id}/delete', name: 'reservation_delete', methods: ['POST'])]
    public function deleteReservation(int $id, EntityManagerInterface $entityManager, Request $request): RedirectResponse
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer une réservation.');
            return $this->redirectToRoute('app_login'); // Rediriger vers la page de connexion
        }

        // Récupérer la réservation
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);

        // Vérifier si la réservation existe et appartient à l'utilisateur connecté
        if (!$reservation || $reservation->getUtilisateurId() !== $user) {
            $this->addFlash('error', 'Réservation introuvable ou vous n\'êtes pas autorisé à la supprimer.');
            return $this->redirectToRoute('mes_reservations'); // Rediriger vers la page des réservations
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('delete_' . $reservation->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('mes_reservations'); // Rediriger vers la page des réservations
        }

        // Supprimer l'utilisateur de la liste des participants du tournoi
        $tournoi = $reservation->getTournois(); // Récupérer le tournoi associé à la réservation
        $tournoi->removeParticipant($user); // Retirer l'utilisateur de la collection participants du tournoi

        // Supprimer la réservation
        $entityManager->remove($reservation);
        $entityManager->persist($tournoi); // Persister le tournoi pour que la relation soit mise à jour
        $entityManager->flush(); // Commit les changements dans la base de données

        // Afficher un message de succès et rediriger vers la liste des réservations
        $this->addFlash('success', 'Votre réservation a été supprimée avec succès.');
        return $this->redirectToRoute('mes_reservations');
    }

    #[Route('/admin/tournoi/{id}/qr/generate', name: 'tournoi_qr_generate')]
    public function generateQRCode(Tournoi $tournoi): Response
    {
        // Créer une chaîne contenant les informations du tournoi
        $data = sprintf(
            "Le tournoi du nom: %s\n son type de sport: %s\n son lieu: %s\nDate de début: %s\nDate de fin: %s\nStatut: %s\nRécompense: %s",
            $tournoi->getNom(),
            $tournoi->getTypesport(),
            $tournoi->getLieu(),
            $tournoi->getDatedebut()->format('Y-m-d'),
            $tournoi->getDatefin()->format('Y-m-d'),
            $tournoi->getStatut(),
            $tournoi->getRecompense()
        );

        // Créer le QR code avec les données
        $qrCode = new QrCode($data);
        $qrCode->setSize(300); // Taille du QR code
        $qrCode->setMargin(10); // Marge autour du QR code

        // Créer le QR code en PNG
        $writer = new PngWriter();
        $pngData = $writer->write($qrCode)->getString();  // Utiliser getString() ici pour récupérer l'image

        // Définir le chemin du fichier où l'image sera sauvegardée
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/qrcodes/tournoi_' . $tournoi->getId() . '.png';

        // Sauvegarder l'image dans un fichier
        file_put_contents($filePath, $pngData); // Sauvegarder les données dans un fichier

        // Retourner une réponse pour indiquer que le fichier a été généré
        return $this->redirectToRoute('tournoi_qr_download', ['filename' => 'tournoi_' . $tournoi->getId() . '.png']);
    }

    #[Route('/admin/tournoi/qr/{filename}', name: 'tournoi_qr_download')]
    public function downloadQRCode($filename): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/qrcodes/' . $filename;

        // Vérifiez si le fichier existe
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Fichier QR code non trouvé.');
        }

        return new Response(
            file_get_contents($filePath),
            200,
            [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );
    }
}
