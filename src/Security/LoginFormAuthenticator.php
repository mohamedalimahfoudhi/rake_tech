<?php

namespace App\Security;

use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private UrlGeneratorInterface $urlGenerator;
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(UrlGeneratorInterface $urlGenerator, UtilisateurRepository $utilisateurRepository)
    {
        $this->urlGenerator = $urlGenerator;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    public function supports(Request $request): bool
    {
        return $request->isMethod('POST') && $request->getPathInfo() === $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('_username', '');
        $password = $request->request->get('_password', '');
        
        $request->getSession()->set(Security::LAST_USERNAME, $email);
        
        // Debug information
        error_log("Login attempt - Email: $email, Password length: " . strlen($password));
        
        // Verify the credentials directly using the repository
        $user = $this->utilisateurRepository->findByEmail($email);
        if (!$user) {
            error_log("User not found with email: $email");
            throw new CustomUserMessageAuthenticationException('Invalid credentials');
        }
        
        // Check if the password is correct
        if (!$this->utilisateurRepository->validateCredentials($email, $password)) {
            error_log("Password validation failed for user: $email");
            throw new CustomUserMessageAuthenticationException('The presented password is invalid.');
        }
        
        error_log("Authentication successful for: $email");
        
        // Create passport with custom credentials validator that always returns true
        // since we already validated the password manually
        return new Passport(
            new UserBadge($email),
            new CustomCredentials(
                function($credentials, $user) {
                    // Always return true since we already validated
                    return true;
                },
                $password
            ),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        $roles = $user->getRoles();
        
        error_log("Authentication success. User roles: " . implode(', ', $roles));
        
        // Redirect to admin dashboard for admin users
        if (in_array('ROLE_ADMIN', $roles)) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }
        
        // For regular users, redirect to the user dashboard
        return new RedirectResponse($this->urlGenerator->generate('user_dashboard'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
} 