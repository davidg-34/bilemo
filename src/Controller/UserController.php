<?php

namespace App\Controller;

use App\Entity\User;
use OpenApi\Attributes as OA;
use App\Repository\UserRepository;
use App\Repository\CustomerRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Contracts\Cache\ItemInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url as ReferenceUrl;

class UserController extends AbstractController
{
    // Liste des users
    #[Route('api/users', name: 'users', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste paginée des utilisateurs',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: User::class, groups: ['getUsers']))
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
    #[OA\Tag(name: 'Users')]
    #[Security(name: 'Bearer')]
    public function getUserList(UserRepository $userRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);

        $idCache = "getUserList-" . $page . "-" . $limit;

        $jsonUserList = $cache->get($idCache, function (ItemInterface $item) use ($userRepository, $page, $limit, $serializer) {
        echo ("L'élément n'est pas en cache ! \n");
        $item->tag("usersCache");
        $userList = $userRepository->findAllWithPagination($page, $limit);

        $context = SerializationContext::create()->setGroups(['getUsers', 'getCustomers']);
        return $serializer->serialize($userList, 'json', $context);
        });

        return new JsonResponse($jsonUserList, Response::HTTP_OK, [], true);
    }

    // Détail d'un user
    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne les détails d\'un utilisateur spécifique',
        content: new OA\JsonContent(
            ref: new Model(type: User::class, groups: ['getUser'])
        )
    )]
    #[OA\Response(response: 401, description: 'Non autorisé - Jeton manquant ou invalide')]
    #[OA\Response(response: 404, description: 'Utilisateur non trouvé')]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        description: 'Identifiant de l\'utilisateur',
        required: true,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Tag(name: 'Users')]
    #[Security(name: 'Bearer')]
    public function getDetailUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getUsers', 'getCustomers']);
        $jsonUser = $serializer->serialize($user, 'json', $context);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    // Création d'un user
   #[Route('/api/users', name:"createUser", methods: ['POST'])]
   #[OA\Post(
    description: "Crée un nouvel utilisateur",
    requestBody: new OA\RequestBody(
        description: "Données pour créer un utilisateur",
        required: true,
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "firstname", type: "string"),
                new OA\Property(property: "lastname", type: "string"),
                new OA\Property(property: "email", type: "string", example: "user@example.com"),
                new OA\Property(property: "idCustomer", type: "integer", example: 1)
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: "Utilisateur créé avec succès",
            content: new OA\JsonContent(
                ref: new Model(type: User::class, groups: ['getUsers'])
            )
        ),
        new OA\Response(
            response: 400,
            description: "Données invalides",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "errors", type: "array", items: new OA\Items(type: "string"))
                ]
            )
        )
    ]
)]
#[OA\Tag(name: 'Users')]
   #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un utilisateur')]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, CustomerRepository $customerRepository, ValidatorInterface $validator): JsonResponse 
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

         // On vérifie les erreurs
         $errors = $validator->validate($user);

         if ($errors->count() > 0) {
             return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
         }

        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idCustomer. S'il n'est pas défini, alors on met -1 par défaut.
        $idCustomer = $content['idCustomer'] ?? -1;

        // On cherche l'auteur qui correspond et on l'assigne au livre.
        // Si "find" ne trouve pas l'auteur, alors null sera retourné.
        $user->setCustomer($customerRepository->find($idCustomer));
     
        $em->persist($user);
        $em->flush();

        $context = SerializationContext::create()->setGroups(['getUsers']);
        $jsonUser = $serializer->serialize($user, 'json', $context);

        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ["Location" => $location], true);
    }
   
    // Suppression d'un user
    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    #[OA\Delete(
        description: "Supprime un utilisateur spécifique",
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Identifiant de l\'utilisateur à supprimer',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Utilisateur supprimé avec succès"
            ),
            new OA\Response(
                response: 401,
                description: "Non autorisé - Jeton manquant ou invalide"
            ),
            new OA\Response(
                response: 403,
                description: "Accès refusé - Droits insuffisants"
            ),
            new OA\Response(
                response: 404,
                description: "Utilisateur non trouvé"
            )
        ]
    )] 
    #[OA\Tag(name: 'Users')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un utilisateur')]
    public function DeleteUser(User $user, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $cachePool->invalidateTags(["usersCache"]);
        $em->remove($user);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

}
