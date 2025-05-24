<?php

namespace App\Controller\Api;

use App\Entity\Appointment;
use App\Entity\AppointmentParticipant;
use App\Entity\Participant;
use App\Repository\AppointmentRepository;
use App\Repository\ParticipantRepository;
use App\Service\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/appointments')]
class AppointmentController extends BaseApiController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private ParticipantRepository $participantRepository,
        SerializerInterface $serializer,
        private Service $service
    ) {
        parent::__construct($serializer);
    }

    #[Route('/list', name: 'api_appointments_list', methods: ['GET'])]
    public function list(AppointmentRepository $repository): JsonResponse
    {
        try {
            $appointments = $repository->getAppointments();
            return $this->jsonResponse([
                'message' => 'Appointments retrieved successfully',
                'data' => $appointments
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred while retrieving appointments',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/create', name: 'api_appointments_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
            }

            $appointment = new Appointment();
            $appointment = $this->service->persistAppointment($appointment, $data);

            // Add participants
            $participants = $this->participantRepository->findBy(['id' => $data['participants']]);
            foreach ($participants as $participant) {
                if ($participant) {
                    $appointmentParticipant = new AppointmentParticipant();
                    $appointmentParticipant->setParticipant($participant);
                    $appointment->addAppointmentParticipant($appointmentParticipant);
                }
            }

            // Check for overlapping appointments
            if ($this->service->hasOverlappingAppointments($appointment)) {
                return new JsonResponse([
                    'message' => 'Validation failed',
                    'errors' => 'One or more participants have overlapping appointments'
                ], Response::HTTP_BAD_REQUEST);
            }

            $errors = $this->validator->validate($appointment);
            if (count($errors) > 0) {
                $errorMessages = $this->formatValidationErrors($errors);

                return new JsonResponse([
                    'message' => 'Validation failed',
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($appointment);
            $this->entityManager->flush();

            return $this->jsonResponse([
                'message' => 'Appointment created successfully',
                'data' => $appointment
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred while creating appointment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/view/{id}', name: 'api_appointments_get', methods: ['GET'])]
    public function get(Appointment $appointment): JsonResponse
    {
        try {
            return $this->jsonResponse([
                'message' => 'Appointment retrieved successfully',
                'data' => $appointment
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred while retrieving appointment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/update/{id}', name: 'api_appointments_update', methods: ['PUT'])]
    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
            }
            $appointment = $this->service->persistAppointment($appointment, $data);

            // Clear existing participants
            $existingParticipants = $appointment->getAppointmentParticipants();
            foreach ($existingParticipants as $appointmentParticipant) {
                $this->entityManager->remove($appointmentParticipant);
            }

            // Add new participants
            $participants = $this->participantRepository->findBy(['id' => $data['participants']]);
            foreach ($participants as $participant) {
                if ($participant) {
                    $appointmentParticipant = new AppointmentParticipant();
                    $appointmentParticipant->setParticipant($participant);
                    $appointment->addAppointmentParticipant($appointmentParticipant);
                }
            }

            // Check for overlapping appointments
            if ($this->service->hasOverlappingAppointments($appointment)) {
                return new JsonResponse([
                    'message' => 'One or more participants have overlapping appointments'
                ], Response::HTTP_BAD_REQUEST);
            }

            $errors = $this->validator->validate($appointment);
            if (count($errors) > 0) {
                $errorMessages = $this->formatValidationErrors($errors);

                return new JsonResponse([
                    'message' => 'Validation failed',
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->flush();

            return $this->jsonResponse([
                'message' => 'Appointment updated successfully',
                'data' => $appointment
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred while updating appointment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/delete/{id}', name: 'api_appointments_delete', methods: ['DELETE'])]
    public function delete(Appointment $appointment): JsonResponse
    {
        try {
            $this->entityManager->remove($appointment);
            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'Appointment deleted successfully'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred while deleting appointment',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 
