<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class BaseApiController extends AbstractController
{
    protected SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    protected function jsonResponse($data, int $status = 200): JsonResponse
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [
            new ObjectNormalizer(
                null,
                null,
                null,
                null,
                null,
                null,
                ['appointment:read', 'appointment:participants', 'appointment:user']
            )
        ];
        $serializer = new Serializer($normalizers, $encoders);

        $json = $serializer->serialize($data, 'json', [
            'ignored_attributes' => ['__initializer__', '__cloner__', '__isInitialized__'],
            'circular_reference_handler' => function ($object) {
                if (method_exists($object, 'getId')) {
                    return $object->getId();
                }
                if ($object instanceof AppointmentParticipant) {
                    return [
                        'appointment' => $object->getAppointment()?->getId(),
                        'participant' => $object->getParticipant()?->getId()
                    ];
                }
                return null;
            }
        ]);

        return new JsonResponse($json, $status, [], true);
    }

    protected function formatValidationErrors($errors): array {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = [
                'field' => $error->getPropertyPath(),
                'message' => $error->getMessage()
            ];
        }

        return $errorMessages;
    }
}
