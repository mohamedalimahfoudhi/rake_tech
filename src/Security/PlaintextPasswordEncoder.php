<?php

namespace App\Security;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\PlaintextPasswordHasher;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class PlaintextPasswordEncoder implements \Symfony\Component\PasswordHasher\PasswordHasherInterface
{
    private $passwordHasher;

    public function __construct()
    {
        // Create a PlaintextPasswordHasher that doesn't do any encoding
        $factory = new PasswordHasherFactory([
            PasswordAuthenticatedUserInterface::class => new PlaintextPasswordHasher(),
        ]);
        
        $this->passwordHasher = $factory->getPasswordHasher(PasswordAuthenticatedUserInterface::class);
    }

    public function hash(string $plainPassword): string
    {
        // Just return the plaintext password
        return $plainPassword;
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        // Just compare the strings directly
        return $hashedPassword === $plainPassword;
    }

    public function needsRehash(string $hashedPassword): bool
    {
        // No rehashing needed for plaintext
        return false;
    }
} 