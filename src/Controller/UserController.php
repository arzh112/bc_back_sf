<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    #[Route('/api/users', name: 'users', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $users = $userRepository->findAll();
        $jsonUsers = $serializer->serialize($users, 'json', ["groups" => "getUser"]);
        return new JsonResponse($jsonUsers, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'detailUser', methods: ['GET'])]
    public function getOneUser(User $user, SerializerInterface $serializer): JsonResponse
    {
        $jsonUser = $serializer->serialize($user, 'json', ["groups" => "getUser"]);
        return new JsonResponse($jsonUser, Response::HTTP_OK, [], true);
    }

    #[Route('/api/users/{id}', name: 'deleteUser', methods: ['DELETE'])]
    public function deleteUser(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/users/new', name: 'createUser', methods: 'POST')]
    public function createUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator)
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "La requête n'est pas valide");
        }

        $content = $request->toArray();
        $plainPassword = $content['password'];
        $hashPassword = $hasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashPassword);

        $em->persist($user);
        $em->flush();

        $jsonUser = $serializer->serialize($user, 'json', ["groups" => "getUser"]);
        $location = $urlGenerator->generate('detailUser', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, ['location' => $location], true);

    }

    #[Route('/api/users/{id}', name: 'updateUser', methods: 'PUT')]
    public function updateUser(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, User $currentUser, UserPasswordHasherInterface $hasher, ValidatorInterface $validator, UserRepository $userRepository)
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]);
        $errors = $validator->validate($user);
        if ($errors->count() > 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "La requête n'est pas valide");
        }

        $content = $request->toArray();
        $plainPassword = $content['plainPassword'] ?? null;

        if($plainPassword !== null) {
            $hashPassword = $hasher->hashPassword($user, $plainPassword);
            $userRepository->upgradePassword($user, $hashPassword);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
