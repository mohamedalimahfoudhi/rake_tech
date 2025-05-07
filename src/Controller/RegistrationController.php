<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\RoleRepository;
use App\Service\SmsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    private $session;
    private $smsService;

    public function __construct(SessionInterface $session, SmsService $smsService)
    {
        $this->session = $session;
        $this->smsService = $smsService;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, RoleRepository $roleRepository): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Generate verification code
            $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store verification data in session
            $this->session->set('verification_code', $verificationCode);
            $this->session->set('verification_phone', $user->getNumeroTelephone());
            
            // Store the role ID in the session, not the Role object itself
            $roleId = null;
            if ($user->getRole()) {
                $roleId = $user->getRole()->getRoleID();
            }
            
            $this->session->set('pending_user', [
                'email' => $user->getEmail(),
                'password' => $form->get('password')->getData(),
                'prenom' => $user->getPrenom(),
                'nom' => $user->getNom(),
                'genre' => $user->getGenre(),
                'adresse' => $user->getAdresse(),
                'nomOrganisation' => $user->getNomOrganisation(),
                'role_id' => $roleId
            ]);

            // Send SMS verification code
            if ($this->smsService->sendVerificationCode($user->getNumeroTelephone(), $verificationCode)) {
                return $this->redirectToRoute('app_verify_phone');
            } else {
                $this->addFlash('error', 'Failed to send verification code. Please try again.');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'recaptcha_site_key' => $this->getParameter('recaptcha_site_key'),
        ]);
    }

    #[Route('/verify-phone', name: 'app_verify_phone')]
    public function verifyPhone(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, RoleRepository $roleRepository): Response
    {
        if (!$this->session->has('verification_code')) {
            return $this->redirectToRoute('app_register');
        }

        if ($request->isMethod('POST')) {
            $submittedCode = $request->request->get('verification_code');
            $storedCode = $this->session->get('verification_code');

            if ($submittedCode === $storedCode) {
                $pendingUser = $this->session->get('pending_user');
                
                // Create and persist the user
                $user = new User();
                $user->setEmail($pendingUser['email']);
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $pendingUser['password']
                    )
                );
                $user->setPrenom($pendingUser['prenom']);
                $user->setNom($pendingUser['nom']);
                $user->setGenre($pendingUser['genre']);
                $user->setNumeroTelephone($this->session->get('verification_phone'));
                $user->setAdresse($pendingUser['adresse']);
                $user->setNomOrganisation($pendingUser['nomOrganisation']);
                
                // Set role using the roleRepository to find the Role entity
                if (isset($pendingUser['role_id']) && $pendingUser['role_id']) {
                    $role = $roleRepository->find($pendingUser['role_id']);
                    if ($role) {
                        $user->setRole($role);
                    }
                }

                $entityManager->persist($user);
                $entityManager->flush();

                // Clear session data
                $this->session->remove('verification_code');
                $this->session->remove('verification_phone');
                $this->session->remove('pending_user');

                $this->addFlash('success', 'Registration successful! Please login with your credentials.');
                return $this->redirectToRoute('app_login', ['email' => $user->getEmail()]);
            } else {
                $this->addFlash('error', 'Invalid verification code. Please try again.');
            }
        }

        return $this->render('registration/verify_phone.html.twig');
    }
} 