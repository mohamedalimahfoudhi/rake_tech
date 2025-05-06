<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils, UtilisateurRepository $utilisateurRepository): Response
    {
        // If user is already logged in, redirect to the appropriate dashboard
        if ($this->getUser()) {
            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                return $this->redirectToRoute('admin_dashboard');
            }
            return $this->redirectToRoute('user_dashboard');
        }
        
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        
        // Custom error handling for debugging
        $errorMessage = null;
        if ($error) {
            $errorMessage = $error->getMessage();
            
            // If there was an attempt, check if it's a credentials issue
            if ($lastUsername && $request->isMethod('POST')) {
                $password = $request->request->get('_password', '');
                if ($password && !$utilisateurRepository->validateCredentials($lastUsername, $password)) {
                    $errorMessage = 'Invalid credentials provided. Please check your email and password.';
                }
            }
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'error_message' => $errorMessage,
            'page_title' => 'Login',
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method can be empty - it will be intercepted by the logout key on your firewall
        throw new \LogicException('This method should not be called directly.');
    }
} 