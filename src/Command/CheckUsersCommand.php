<?php

namespace App\Command;

use App\Repository\UtilisateurRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-users',
    description: 'Check users in the database',
)]
class CheckUsersCommand extends Command
{
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(UtilisateurRepository $utilisateurRepository)
    {
        parent::__construct();
        $this->utilisateurRepository = $utilisateurRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->utilisateurRepository->findAll();
        
        $io->title('User list');
        
        if (empty($users)) {
            $io->warning('No users found in the database.');
            return Command::SUCCESS;
        }

        $userTable = [];
        foreach ($users as $user) {
            $userTable[] = [
                'ID' => $user->getID(),
                'Email' => $user->getEmail(),
                'Password' => $user->getMotdepasse(),
                'Roles' => implode(', ', $user->getRoles()),
            ];
        }

        $io->table(
            ['ID', 'Email', 'Password', 'Roles'],
            $userTable
        );

        return Command::SUCCESS;
    }
} 