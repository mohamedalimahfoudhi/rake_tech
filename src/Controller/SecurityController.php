<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Service\SmsService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Get the email from the request parameters
        $lastUsername = $authenticationUtils->getLastUsername();
        
        // If there's an error, add a flash message
        if ($error) {
            $this->addFlash('error', 'Invalid credentials. Please try again.');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'recaptcha_site_key' => $this->getParameter('recaptcha_site_key'),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method can be empty - it will be intercepted by the logout key on your firewall
        // The logout is handled by Symfony's security system
        throw new \LogicException('This method should not be reached!');
    }

    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request, UserRepository $userRepository, SmsService $smsService): Response
    {
        if ($request->isMethod('POST')) {
            $phone = $request->request->get('phone');
            $email = $request->request->get('email');
            
            // First, check if email exists in database
            $user = $userRepository->findOneBy(['email' => $email]);
            
            if (!$user) {
                $this->addFlash('error', 'No account found with this email address.');
                return $this->redirectToRoute('app_forgot_password');
            }
            
            // Then check if the phone number matches the user found by email
            if ($user->getNumeroTelephone() !== $phone) {
                $this->addFlash('error', 'The phone number does not match the email provided.');
                return $this->redirectToRoute('app_forgot_password');
            }
            
            // Generate verification code
            $verificationCode = sprintf('%06d', mt_rand(0, 999999));
            
            // Store verification code in session
            $request->getSession()->set('reset_verification_code', $verificationCode);
            $request->getSession()->set('reset_user_id', $user->getId());
            
            // Send verification code via SMS
            if ($smsService->sendVerificationCode($phone, $verificationCode)) {
                $this->addFlash('success', 'Verification code has been sent to your phone.');
                return $this->render('security/forgot_password.html.twig', [
                    'verification_sent' => true
                ]);
            } else {
                $this->addFlash('error', 'Failed to send verification code. Please try again.');
            }
        }
        
        return $this->render('security/forgot_password.html.twig', [
            'verification_sent' => false
        ]);
    }
    
    #[Route('/verify-reset-code', name: 'app_verify_reset_code', methods: ['POST'])]
    public function verifyResetCode(
        Request $request, 
        UserRepository $userRepository, 
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $session = $request->getSession();
        $storedCode = $session->get('reset_verification_code');
        $userId = $session->get('reset_user_id');
        $submittedCode = $request->request->get('verification_code');
        $newPassword = $request->request->get('new_password');
        $confirmPassword = $request->request->get('confirm_password');
        
        if (!$storedCode || !$userId) {
            $this->addFlash('error', 'Invalid or expired verification session.');
            return $this->redirectToRoute('app_forgot_password');
        }
        
        if ($submittedCode !== $storedCode) {
            $this->addFlash('error', 'Invalid verification code.');
            return $this->render('security/forgot_password.html.twig', [
                'verification_sent' => true
            ]);
        }
        
        if ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'Passwords do not match.');
            return $this->render('security/forgot_password.html.twig', [
                'verification_sent' => true
            ]);
        }
        
        // Validate password according to registration requirements
        if (strlen($newPassword) < 8) {
            $this->addFlash('error', 'Your password should be at least 8 characters.');
            return $this->render('security/forgot_password.html.twig', [
                'verification_sent' => true
            ]);
        }
        
        if (!preg_match('/^[a-zA-Z0-9]+$/', $newPassword)) {
            $this->addFlash('error', 'Password must contain only letters and numbers.');
            return $this->render('security/forgot_password.html.twig', [
                'verification_sent' => true
            ]);
        }
        
        // Update user's password
        $user = $userRepository->find($userId);
        if ($user) {
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            
            $entityManager->persist($user);
            $entityManager->flush();
            
            // Clear session data
            $session->remove('reset_verification_code');
            $session->remove('reset_user_id');
            
            $this->addFlash('success', 'Your password has been reset successfully.');
            return $this->redirectToRoute('app_login');
        }
        
        $this->addFlash('error', 'An error occurred while resetting your password.');
        return $this->redirectToRoute('app_forgot_password');
    }
} 