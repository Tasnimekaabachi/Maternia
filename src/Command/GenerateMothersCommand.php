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
    name: 'app:generate-mothers',
    description: 'Génère 10 comptes de test pour des mamans',
)]
class GenerateMothersCommand extends Command
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
        $noms = ['Ben Ali', 'Trabelsi', 'Mezri', 'Mahmoudi', 'Gharbi'];
        $prenoms = ['Sonia', 'Amira', 'Ines', 'Meryem', 'Leila'];

        for ($i = 1; $i <= 10; $i++) {
            $user = new User();
            
            $nom = $noms[array_rand($noms)];
            $prenom = $prenoms[array_rand($prenoms)];
            $email = strtolower($prenom) . $i . "@gmail.com";

            $user->setEmail($email);
            $user->setNom($nom);
            $user->setPrenom($prenom);
            $user->setType('MAMAN');
            $user->setRoles(['ROLE_USER']);
            
            // On met un mot de passe simple pour les tests
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

        $output->writeln('<info>Succès : 10 comptes "Maman" ont été ajoutés à la base !</info>');

        return Command::SUCCESS;
    }
}