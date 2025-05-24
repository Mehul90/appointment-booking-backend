<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ParticipantFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $participants = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567890',
                'color' => '#FF5733'
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+1987654321',
                'color' => '#33FF57'
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob.johnson@example.com',
                'phone' => '+1122334455',
                'color' => '#3357FF'
            ]
        ];

        foreach ($participants as $participantData) {
            $participant = new Participant();
            $participant->setName($participantData['name']);
            $participant->setEmail($participantData['email']);
            $participant->setPhone($participantData['phone']);
            $participant->setColor($participantData['color']);
            
            $manager->persist($participant);
        }

        $manager->flush();
    }
} 