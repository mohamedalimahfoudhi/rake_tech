<?php

namespace App\DataFixtures;

use App\Entity\Billet;
use App\Entity\Evenement;
use App\Entity\Utilisateur;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory; // We'll use Faker for generating random-like data
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; // Import password hasher

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    // Inject the password hasher via constructor
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create a Faker instance
        $faker = Factory::create('fr_FR'); // Use French locale for potentially relevant data

        // --- Create Roles if they don't exist --- 
        $roleRepository = $manager->getRepository(Role::class);
        
        $adminRole = $roleRepository->findOneBy(['roleNom' => 'ADMIN']);
        if (!$adminRole) {
            $adminRole = new Role();
            $adminRole->setRoleNom('ADMIN');
            $manager->persist($adminRole);
        }

        $userRole = $roleRepository->findOneBy(['roleNom' => 'USER']);
        // Handle potential duplicate USER roles in chleghem.sql if needed, 
        // but findOneBy usually gets the first one.
        if (!$userRole) {
            $userRole = new Role();
            $userRole->setRoleNom('USER');
            $manager->persist($userRole);
        }
        // Flush here to ensure roles get IDs before potentially being used elsewhere if needed
        // Although in this specific case it might not be strictly necessary before user creation
        // $manager->flush(); 

        // --- Existing Event and Billet Fixtures --- 
        // 1. Create a sample Event
        $evenement = new Evenement();
        $evenement->setNom($faker->sentence(3)); // Example: "Concert Rock Incroyable"
        $evenement->setDetails($faker->paragraph(2));
        $startDate = $faker->dateTimeBetween('+1 week', '+1 month');
        $evenement->setDateDebut($startDate);
        $evenement->setDateFin((clone $startDate)->modify('+2 days')); // Event duration 2 days
        $evenement->setType($faker->randomElement(['TERRAIN', 'PADDEL', null])); // Assuming these are possible types from your entity/DB
        $evenement->setRecompense($faker->optional()->word); // Optional reward
        $evenement->setStatut('Prévu'); // Assuming 'Prévu' or similar exists
        $evenement->setParticipantsMax($faker->numberBetween(50, 500));
        $manager->persist($evenement);

        // 2. Create 20 Billets for this Event
        $ticketTypes = ['Gradin', 'Virage', 'VIP']; // Assuming these match your ENUM or allowed values
        $statuses = ['Valide', 'Annulé', 'Non valide']; // Assuming these match your ENUM or allowed values

        for ($i = 0; $i < 20; $i++) {
            $billet = new Billet();
            $billet->setEvenement($evenement); // Associate with the event created above
            // Note: eventID is managed via the relationship, no need to set it manually
            $billet->setDateAchat($faker->dateTimeBetween('-1 month', 'now'));
            $billet->setPrix($faker->randomFloat(2, 10, 150)); // Price between 10.00 and 150.00
            $billet->setTypeBillet($faker->randomElement($ticketTypes));
            $billet->setStatut($faker->randomElement($statuses));
            $billet->setQuantite($faker->numberBetween(1, 5)); // Add quantity variation

            // codeUnique is derived from ID, no need to set here

            $manager->persist($billet);
        }

        // --- End of Existing Fixtures --- 

        // 3. Find Roles (Now they should exist or have been created)
        // We re-fetch them just in case or rely on the variables $adminRole / $userRole from above
        // $adminRole = $manager->getRepository(Role::class)->findOneBy(['roleNom' => 'ADMIN']);
        // $userRole = $manager->getRepository(Role::class)->findOneBy(['roleNom' => 'USER']);
        
        // Remove the exception throwing as we now create roles if missing
        /* REMOVED redundant checks/throws
        if (!$adminRole) {
            throw new \Exception('ADMIN role not found in database. Please ensure it exists.');
        }
         if (!$userRole) {
            throw new \Exception('USER role not found in database. Please ensure it exists.');
        }
        */

        // 4. Create Admin User
        $adminUser = new Utilisateur();
        $adminUser->setEmail('jawher@jawher.com');
        $adminUser->setRole($adminRole); // Assign ADMIN role
        $adminUser->setPrenom('Jawher'); // Add some sample data
        $adminUser->setNom('Admin');
        $adminUser->setMotdepasse(
            $this->passwordHasher->hashPassword(
                $adminUser,
                '123456789' // The plain password
            )
        );
        $manager->persist($adminUser);

        // 5. Create Normal User
        $normalUser = new Utilisateur();
        $normalUser->setEmail('jawher@test.com');
        $normalUser->setRole($userRole); // Assign USER role
        $normalUser->setPrenom('Jawher'); // Add some sample data
        $normalUser->setNom('Test');
        $normalUser->setMotdepasse(
            $this->passwordHasher->hashPassword(
                $normalUser,
                '123456789' // The plain password
            )
        );
        $manager->persist($normalUser);

        $manager->flush();
    }
} 