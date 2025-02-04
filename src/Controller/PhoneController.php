<?php

namespace App\Controller;

use App\Entity\Phone;
use OpenApi\Attributes as OA;
use App\Repository\PhoneRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PhoneController extends AbstractController
{
    // Liste des téléphones
    #[Route('/api/phones', name: 'phones', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste paginée des téléphones',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Phone::class/* , groups: ['getUsers'] */))
        )
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        description: 'La page que l\'on veut récupérer',
        schema: new OA\Schema(type: 'integer', default: 1)
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Le nombre d\'éléments par page',
        schema: new OA\Schema(type: 'integer', default: 3)
    )]
    #[OA\Tag(name: 'Phones')]
    #[Security(name: 'Bearer')]
    public function getPhoneList(PhoneRepository $phoneRepository, SerializerInterface $serializer): JsonResponse
    {
        $phoneList = $phoneRepository->findAll();
        $jsonPhoneList = $serializer->serialize($phoneList, 'json');
        return new JsonResponse(
            $jsonPhoneList, Response::HTTP_OK, [], true);
    }

    // Détail d'un téléphone
    #[Route('/api/phones/{id}', name: 'detailPhone', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne les détails d\'un téléphone spécifique',
        content: new OA\JsonContent(
            ref: new Model(type: Phone::class/* , groups: ['getUser'] */)
        )
    )]
    /* #[OA\Response(response: 401, description: 'Non autorisé - Jeton manquant ou invalide')] */
    #[OA\Response(response: 404, description: 'Produit non trouvé')]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Identifiant de l\'utilisateur',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'Phones')]
    #[Security(name: 'Bearer')]
    public function getDetailPhone(Phone $phone, SerializerInterface $serializer)
    {
        $jsonPhone = $serializer->serialize($phone, 'json');
        return new JsonResponse($jsonPhone, Response::HTTP_OK, [], true);
    }
}

