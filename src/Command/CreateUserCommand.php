<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates a new user',
)]
class CreateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the user')
            ->addArgument('role', InputArgument::OPTIONAL, 'The role (1=ADMIN, 2=USER)', '2')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $roleId = $input->getArgument('role');

        // Check if user already exists
        $existingUser = $this->entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('User with this email already exists!');
            return Command::FAILURE;
        }

        // Get role
        $role = $this->entityManager->getRepository(Role::class)->find($roleId);
        if (!$role) {
            $io->error('Role not found. Please check if roles are set up correctly.');
            return Command::FAILURE;
        }

        // Create new user
        $user = new Utilisateur();
        $user->setEmail($email);
        $user->setMotdepasse($password); // Plain text for now 
        $user->setRole($role);
        $user->setPrenom('Admin');
        $user->setNom('User');
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('User created successfully. Email: ' . $email . ', Role: ' . $role->getRoleNom());

        return Command::SUCCESS;
    }
} 