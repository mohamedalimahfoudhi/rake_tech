<?php

namespace App\Security;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        /** @var User $user */
        $user = $token->getUser();
        
        if (!$user instanceof User) {
            return new RedirectResponse($this->urlGenerator->generate('app_user_dashboard'));
        }
        
        $role = $user->getRole();
        
        if ($role) {
            $roleName = 'ROLE_' . strtoupper($role->getRoleNom());
            
            switch ($roleName) {
                case 'ROLE_ADMIN':
                    return new RedirectResponse($this->urlGenerator->generate('app_admin_dashboard'));
                case 'ROLE_ATHLETE':
                    return new RedirectResponse($this->urlGenerator->generate('app_athlete_dashboard'));
                case 'ROLE_ORGANIZER':
                    return new RedirectResponse($this->urlGenerator->generate('app_organizer_dashboard'));
                default:
                    return new RedirectResponse($this->urlGenerator->generate('app_user_dashboard'));
            }
        }

        // Default redirect if no role is found
        return new RedirectResponse($this->urlGenerator->generate('app_user_dashboard'));
    }
} 