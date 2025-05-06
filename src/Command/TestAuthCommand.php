<?php

namespace App\Command;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

#[AsCommand(
    name: 'app:test-auth',
    description: 'Test authentication for a user',
)]
class TestAuthCommand extends Command
{
    private $utilisateurRepository;
    private $passwordHasher;

    public function __construct(UtilisateurRepository $utilisateurRepository, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->utilisateurRepository = $utilisateurRepository;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email to test')
            ->addArgument('password', InputArgument::REQUIRED, 'Password to test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $io->title('Auth Test');
        $io->text("Email: $email");
        $io->text("Password: $password");

        // Find the user
        $user = $this->utilisateurRepository->findOneBy(['email' => $email]);
        if (!$user) {
            $io->error("User not found with email: $email");
            return Command::FAILURE;
        }

        $io->section('User Details');
        $io->text('User ID: ' . $user->getID());

        // Check the password directly
        $storedPassword = $user->getMotdepasse();
        $io->text("Stored password: $storedPassword");

        if ($storedPassword === $password) {
            $io->success("Password direct match: YES");
        } else {
            $io->error("Password direct match: NO");
        }

        // Check using repository method
        if ($this->utilisateurRepository->validateCredentials($email, $password)) {
            $io->success("Repository validateCredentials: YES");
        } else {
            $io->error("Repository validateCredentials: NO");
        }

        // Check using password hasher
        try {
            if ($this->passwordHasher->isPasswordValid($user, $password)) {
                $io->success("PasswordHasher isPasswordValid: YES");
            } else {
                $io->error("PasswordHasher isPasswordValid: NO");
            }
        } catch (\Exception $e) {
            $io->error("PasswordHasher error: " . $e->getMessage());
        }

        return Command::SUCCESS;
    }
} 