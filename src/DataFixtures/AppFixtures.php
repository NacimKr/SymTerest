<?php

namespace App\DataFixtures;

use App\Entity\Pin;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $users = [];
        for ($i=0; $i < 5; $i++) { 
            $user = new User();
            $user->setFirstName("testPrenom".$i)
                ->setLastName("testNom".$i)
                ->setPassword('$2y$10$SvZ6eQm8Kr0WUwxUFPaKUeEtHQVV8tB4cEj4X23zpd3lP8vN.IJ9y')
                ->setEmail('test'.$i.'@mail.com');

            $users[] = $user;
            $manager->persist($user);
        }
        
        
        $pins = [];
        for ($i=0; $i < 50; $i++) { 
            $pin = new Pin();
            $pin->setTitle("testPrenom".$i)
                ->setDescription("testNom".$i)
                ->setImageName('')
                ->setUser($users[rand(0, count($users) -1)]);

            $pins[] = $pin;        
            
            for ($j=0; $j<rand(5,10); $j++) { 
                $users[rand(0, count($users) -1)]->addPin($pins[rand(0, count($pins) -1)]);
            }
            
            $manager->persist($pin);
        }
        
        
        $manager->flush();
    }
}