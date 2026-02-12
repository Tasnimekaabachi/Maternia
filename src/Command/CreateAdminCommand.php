<?php

namespace App\Command;

use App\Entity\User; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates the initial admin account for Maternia',
)]
class CreateAdminCommand extends Command
{
    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Check if user already exists to avoid duplicates
        $repo = $this->entityManager->getRepository(User::class);
        if ($repo->findOneBy(['email' => 'admin@gmail.com'])) {
            $output->writeln('<error>Admin account already exists!</error>');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail('admin@gmail.com');
        $user->setNom('Admin');
        $user->setPrenom('Maternia');
        $user->setType('ADMIN');
        $user->setRoles(['ROLE_ADMIN']);
        
        // Hash the password "admin"
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'admin');
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('<info>Success! Admin account (admin@gmail.com / admin) created.</info>');

        return Command::SUCCESS;
    }
}