<?php

namespace App\DataFixtures;

use App\Entity\Utilisateur;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements FixtureGroupInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Find Existing Roles (Assuming they exist since we use --append)
        $adminRole = $manager->getRepository(Role::class)->findOneBy(['roleNom' => 'ADMIN']);
        $userRole = $manager->getRepository(Role::class)->findOneBy(['roleNom' => 'USER']);

        // Error handling if roles are somehow missing
        if (!$adminRole) {
            throw new \RuntimeException('ADMIN role not found in database. Cannot append users.');
        }
        if (!$userRole) {
             throw new \RuntimeException('USER role not found in database. Cannot append users.');
        }

        // 2. Create Admin User (if not already exists)
        $existingAdmin = $manager->getRepository(Utilisateur::class)->findOneBy(['email' => 'jawher@jawher.com']);
        if (!$existingAdmin) {
            $adminUser = new Utilisateur();
            $adminUser->setEmail('jawher@jawher.com');
            $adminUser->setRole($adminRole); 
            $adminUser->setPrenom('Jawher'); 
            $adminUser->setNom('Admin');
            $adminUser->setMotdepasse(
                $this->passwordHasher->hashPassword($adminUser, '123456789')
            );
            $manager->persist($adminUser);
            echo "Admin user created.\n"; // Optional feedback
        } else {
            echo "Admin user already exists.\n"; // Optional feedback
        }

        // 3. Create Normal User (if not already exists)
        $existingUser = $manager->getRepository(Utilisateur::class)->findOneBy(['email' => 'jawher@test.com']);
        if (!$existingUser) {
            $normalUser = new Utilisateur();
            $normalUser->setEmail('jawher@test.com');
            $normalUser->setRole($userRole); 
            $normalUser->setPrenom('Jawher');
            $normalUser->setNom('Test');
            $normalUser->setMotdepasse(
                $this->passwordHasher->hashPassword($normalUser, '123456789')
            );
            $manager->persist($normalUser);
            echo "Normal user created.\n"; // Optional feedback
        } else {
             echo "Normal user already exists.\n"; // Optional feedback
        }

        $manager->flush();
    }

    // Define the group for this fixture
    public static function getGroups(): array
    {
        return ['users']; // Name this group 'users'
    }
} 