<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $user = new User();
        $user->setEmail('admin@gmail.com');
        $user->setNom('Admin');
        $user->setPrenom('Maternia');
        $user->setType('ADMIN');
        $user->setRoles(['ROLE_ADMIN']);
        
        // Hash the password "admin"
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'admin');
        $user->setPassword($hashedPassword);

        $manager->persist($user);
        
        // You can add more fixtures here for marketplace products, etc.
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}