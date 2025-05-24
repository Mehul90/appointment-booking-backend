<?php

namespace App\Service;

use App\Entity\Appointment;
use App\Entity\Participant;

class Service
{
    public function hasOverlappingAppointments(Appointment $appointment): bool
    {
        foreach ($appointment->getAppointmentParticipants() as $appointmentParticipant) {
            $participant = $appointmentParticipant->getParticipant();
            if (!$participant) {
                continue;
            }
            
            foreach ($participant->getAppointmentParticipants() as $existingAppointmentParticipant) {
                $existingAppointment = $existingAppointmentParticipant->getAppointment();
                if (!$existingAppointment) {
                    continue;
                }

                if ($existingAppointment->getId() === $appointment->getId()) {
                    continue; // Skip the current appointment when checking for overlaps
                }

                if ($appointment->getDate() == $existingAppointment->getDate()) {
                    $date = $appointment->getDate();
                    $startA = new \DateTimeImmutable($date . ' ' . $appointment->getStartTime());
                    $endA = new \DateTimeImmutable($date . ' ' . $appointment->getEndTime());
                    $startB = new \DateTimeImmutable($date . ' ' . $existingAppointment->getStartTime());
                    $endB = new \DateTimeImmutable($date . ' ' . $existingAppointment->getEndTime());

                    if ($startA < $endB && $endA > $startB) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function persistAppointment(Appointment $appointment, $data): Appointment
    {
        $appointment->setTitle($data['title']);
        $appointment->setDescription($data['description']);
        $appointment->setLocation($data['location']);
        $appointment->setColor(!empty($data['color']) ? $data['color'] : '#6366F1');
        $appointment->setDate(new \DateTimeImmutable($data['date']));
        $appointment->setStartTime(new \DateTimeImmutable($data['start_time']));
        $appointment->setEndTime(new \DateTimeImmutable($data['end_time']));

        return $appointment;
    }

    public function persistParticipant(Participant $participant, $data): Participant
    {
        $participant->setName($data['name']);
        $participant->setEmail($data['email']);
        $participant->setPhone($data['phone']);
        $participant->setColor(!empty($data['color']) ? $data['color'] : '#ec4899');

        return $participant;
    }

}
