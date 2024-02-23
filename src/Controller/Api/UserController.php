<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{

    #[Route('/api/users', name: 'users', methods: ['GET'])]
    // #[IsGranted('ROLE_ADMIN', message: "Vous n'avez pas les droits suffisants")]
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        // Si l'utilisateur à le rôle admin renvoyer tous les utilisateurs, sinon renvoyer uniquement l'utilisateur authentifié.
        if(in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            $users = $userRepository->findAll();
        } else {
            $users = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        }
        $jsonUsers = $serializer->serialize($users, 'json', ["groups" => "getUser"]);
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    public function getOneUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        // Si l'utilisateur demandé n'est pas l'utilisateur authentifié et si l'utilisateur authentifié n'as pas le rôle admin.
        if($this->getUser()->getUserIdentifier() !== $user->getEmail() && !in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "Accès non autorisé");
        }
        $jsonUser = $serializer->serialize($user, 'json', ["groups" => "getUser"]);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        // Si l'utilisateur demandé n'est pas l'utilisateur authentifié et si l'utilisateur authentifié n'as pas le rôle admin.
        if($this->getUser()->getUserIdentifier() !== $user->getEmail() && !in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "Accès non autorisé");
        }
        $em->remove($user);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/users', name: 'createUser', methods: 'POST')]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator)
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "La requête n'est pas valide");
        }

        $content = $request->toArray();
        // Cette ligne était utilisé avant la mise en place du userlistener permettant de hasher les mots de passes
        // $hashPassword = $hasher->hashPassword($user, $plainPassword);
        $password = $content['password'];
        $user->setPassword($password);

        $em->persist($user);
        $em->flush();

        $jsonUser = $serializer->serialize($user, 'json', ["groups" => "getUser"]);
        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/api/users/{id}', name: 'updateUser', methods: 'PUT')]
    public function updateUser(Request $request, User $user, SerializerInterface $serializer, EntityManagerInterface $em, User $currentUser, ValidatorInterface $validator, UserRepository $userRepository)
    {
        // Si l'utilisateur demandé n'est pas l'utilisateur authentifié et si l'utilisateur authentifié n'as pas le rôle admin.
        if($this->getUser()->getUserIdentifier() !== $user->getEmail() && !in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "Accès non autorisé");
        }
        $user = $serializer->deserialize($request->getContent(), User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]);
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "La requête n'est pas valide");
        }

        $content = $request->toArray();
        $password = $content['password'] ?? null;

        if ($password !== null) {
            // Cette ligne était utilisé avant la mise en place du userlistener permettant de hasher les mots de passes
            // $hashPassword = $hasher->hashPassword($user, $password);
            $userRepository->upgradePassword($user, $password);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    // #[Route('/api/test', name: 'test', methods: ['GET'])]
    // public function test(SerializerInterface $serializer): JsonResponse
    // {
    //     $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
    //     // $jsonUser = $serializer->serialize($user, 'json', ["groups" => "getUser"]);
    //     $jsonJwtToken = $serializer->serialize($decodedJwtToken, 'json');
    //     return new JsonResponse($jsonJwtToken, Response::HTTP_OK, [], true);
    // }
}
