<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:role',
    description: 'Grant or revoke admin role for a user',
)]
class UserRoleCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email address')
            ->addOption('revoke', null, InputOption::VALUE_NONE, 'Revoke admin role instead of granting it');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $revoke = $input->getOption('revoke');

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error(sprintf('No user found with email "%s".', $email));
            return Command::FAILURE;
        }

        $roles = array_filter($user->getRoles(), fn(string $r) => $r !== 'ROLE_USER');

        if ($revoke) {
            $roles = array_values(array_filter($roles, fn(string $r) => $r !== 'ROLE_ADMIN'));
            $user->setRoles($roles);
            $this->em->flush();
            $io->success(sprintf('Admin role revoked for "%s". Roles: %s', $email, implode(', ', $user->getRoles())));
        } else {
            if (!in_array('ROLE_ADMIN', $roles, true)) {
                $roles[] = 'ROLE_ADMIN';
            }
            $user->setRoles(array_values($roles));
            $this->em->flush();
            $io->success(sprintf('Admin role granted to "%s". Roles: %s', $email, implode(', ', $user->getRoles())));
        }

        return Command::SUCCESS;
    }
}
