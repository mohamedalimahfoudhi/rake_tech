<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Repository\EvenementRepository;

class HomeController extends AbstractController
{
    private $security;
    private $evenementRepository;

    public function __construct(Security $security, EvenementRepository $evenementRepository)
    {
        $this->security = $security;
        $this->evenementRepository = $evenementRepository;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        if ($this->getUser()) {
            if ($this->security->isGranted('ROLE_ADMIN')) {
                return $this->redirectToRoute('admin_dashboard');
            }
            
            // Redirect regular users to user dashboard
            return $this->redirectToRoute('user_dashboard');
        }
        
        return $this->redirectToRoute('app_login');
    }
    
    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
} 