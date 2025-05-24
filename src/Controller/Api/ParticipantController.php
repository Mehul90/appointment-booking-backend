<?php

namespace App\Controller\Api;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use App\Service\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/participants')]
class ParticipantController extends BaseApiController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        SerializerInterface $serializer,
        private Service $service
    ) {
        parent::__construct($serializer);
    }

    #[Route('/list', name: 'api_participants_list', methods: ['GET'])]
    public function list(ParticipantRepository $repository): JsonResponse
    {
        try {
            $participants = $repository->getParticipants();
            return $this->jsonResponse([
                'message' => 'Participants retrieved successfully',
                'data' => $participants
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred while retrieving participants',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/create', name: 'api_participants_create', methods: ['POST'])]
    public function create(Request $request, ParticipantRepository $repository): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
            }

            if (!empty($data)) {
                $participant = new Participant();
                $participant = $this->service->persistParticipant($participant, $data);

                $errors = $this->validator->validate($participant);
                if (count($errors) > 0) {
                    $errorMessages = $this->formatValidationErrors($errors);

                    return new JsonResponse([
                        'message' => 'Validation failed',
                        'errors' => $errorMessages
                    ], Response::HTTP_BAD_REQUEST);
                }

                $repository->save($participant, true);

                return $this->jsonResponse([
                    'message' => 'Participant created successfully',
                    'data' => $participant
                ], Response::HTTP_CREATED);
            } else {
                return new JsonResponse([
                    'message' => 'An error occurred while creating participant',
                    'error' => 'Please enter the field data'
                ], Response::HTTP_OK);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred while creating participant',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/view/{id}', name: 'api_participants_get', methods: ['GET'])]
    public function get(Participant $participant): JsonResponse
    {
        try {
            return $this->jsonResponse([
                'message' => 'Participant retrieved successfully',
                'data' => $participant
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred while retrieving participant',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/update/{id}', name: 'api_participants_update', methods: ['PUT'])]
    public function update(Request $request, Participant $participant, ParticipantRepository $repository): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
            }

            $participant = $this->service->persistParticipant($participant, $data);
            
            $errors = $this->validator->validate($participant);
            if (count($errors) > 0) {
                $errorMessages = $this->formatValidationErrors($errors);

                return new JsonResponse([
                    'message' => 'Validation failed',
                    'errors' => $errorMessages
                ], Response::HTTP_BAD_REQUEST);
            }

            $repository->save($participant, true);

            return $this->jsonResponse([
                'message' => 'Participant updated successfully',
                'data' => $participant
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred while updating participant',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/delete/{id}', name: 'api_participants_delete', methods: ['DELETE'])]
    public function delete(Participant $participant): JsonResponse
    {
        try {
            $this->entityManager->remove($participant);
            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'Participant deleted successfully'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'An error occurred while deleting participant',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 
