<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
class AdminFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;
    
    public function __construct(UserPasswordHasherInterface $hasher){
        $this->hasher = $hasher;
    }
    
    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setUsername("admin");
        $admin->setFirstName("ClintAdmin");
        $admin->setLastName("ClintAdmin");
        $admin->setRoles(["ROLE_ADMIN"]);
        $hashedPassword = $this->hasher->hashPassword($admin,"admin");
        $admin->setPassword($hashedPassword);
        $admin->setEmail("admin@gmail.com");
        
        $manager->persist($admin);

        $manager->flush();
    }
}
