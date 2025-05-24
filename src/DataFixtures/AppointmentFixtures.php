<?php

namespace App\DataFixtures;

use App\Entity\Appointment;
use App\Entity\AppointmentParticipant;
use App\Entity\Participant;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AppointmentFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Get the user
        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'user@gmail.com']);
        // Get all participants
        $participants = $manager->getRepository(Participant::class)->findAll();

        // Create some sample appointments
        $appointments = [
            [
                'title' => 'Team Meeting',
                'location' => 'Conference Room A',
                'description' => 'Weekly team sync meeting',
                'color' => '#FF5733',
                'date' => new \DateTime('+1 day'),
                'startTime' => new \DateTimeImmutable('09:00'),
                'endTime' => new \DateTimeImmutable('10:00')
            ],
            [
                'title' => 'Project Review',
                'location' => 'Meeting Room B',
                'description' => 'Quarterly project review',
                'color' => '#33FF57',
                'date' => new \DateTime('+2 days'),
                'startTime' => new \DateTimeImmutable('14:00'),
                'endTime' => new \DateTimeImmutable('15:30')
            ],
            [
                'title' => 'Client Presentation',
                'location' => 'Virtual Meeting',
                'description' => 'Product demo for client',
                'color' => '#3357FF',
                'date' => new \DateTime('+3 days'),
                'startTime' => new \DateTimeImmutable('11:00'),
                'endTime' => new \DateTimeImmutable('12:00')
            ]
        ];

        foreach ($appointments as $appointmentData) {
            $appointment = new Appointment();
            $appointment->setTitle($appointmentData['title']);
            $appointment->setLocation($appointmentData['location']);
            $appointment->setDescription($appointmentData['description']);
            $appointment->setColor($appointmentData['color']);
            $appointment->setDate($appointmentData['date']);
            $appointment->setStartTime($appointmentData['startTime']);
            $appointment->setEndTime($appointmentData['endTime']);
            $appointment->setCreatedBy($user);

            // Add participants to the appointment
            foreach ($participants as $participant) {
                $appointmentParticipant = new AppointmentParticipant();
                $appointmentParticipant->setParticipant($participant);
                $appointmentParticipant->setAppointment($appointment);
                $appointmentParticipant->setCreatedBy($user);
                $manager->persist($appointmentParticipant);
            }

            $manager->persist($appointment);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ParticipantFixtures::class,
        ];
    }
} 
