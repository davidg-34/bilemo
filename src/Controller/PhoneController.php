<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PhoneController extends AbstractController
{
    #[Route('/api/phones', name: 'phone_list', methods: ['GET'])]
    public function getPhoneList(PhoneRepository $phoneRepository, SerializerInterface $serializer): JsonResponse
    {
        $phoneList = $phoneRepository->findAll();
        $jsonPhoneList = $serializer->serialize($phoneList, 'json');
        return new JsonResponse(
            $jsonPhoneList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/phones/{id}', name: 'detailBook', methods: ['GET'])]
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer)
    {
        $jsonPhone = $serializer->serialize($phone, 'json');
        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }
}

