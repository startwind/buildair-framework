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
    name: 'app:user:paying',
    description: 'Grant or revoke paying status for a user',
)]
class UserPayingCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email address')
            ->addOption('revoke', null, InputOption::VALUE_NONE, 'Revoke paying status instead of granting it');
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

        if ($revoke) {
            $user->setIsPaying(false);
            $this->em->flush();
            $io->success(sprintf('Paying status revoked for "%s".', $email));
        } else {
            $user->setIsPaying(true);
            $this->em->flush();
            $io->success(sprintf('Paying status granted to "%s".', $email));
        }

        return Command::SUCCESS;
    }
}
