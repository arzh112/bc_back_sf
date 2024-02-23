<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Repository\OrderRepository;
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

class OrderController extends AbstractController
{
    #[Route('/api/orders', name: 'orders', methods: 'GET')]
    public function getAllOrders(OrderRepository $orderRepository, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        // Si l'utilisateur n'a pas le rôle admin la méthode getAllOrders ne retournera que les commandes de l'utilisateurs authetifié.
        if(!in_array("ROLE_ADMIN", $this->getUser()->getRoles())) {
            // Récupération de l'email de l'utilisateur authentifié.
            $user = $userRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]); 
            // Récupération des commandes de l'utilisateur authentifié.
            $orders = $orderRepository->findBy(['client' => $user]);
        } else {
            $orders = $orderRepository->findAll();
        }
        $jsonOrders = $serializer->serialize($orders, 'json', ['groups' => 'getOrder']);
        return new JsonResponse($jsonOrders, Response::HTTP_OK, [], true);
    }

    #[Route('/api/orders/{id}', name: 'detailOrder', methods: 'GET')]
    public function getOneOrder(Order $order, OrderRepository $orderRepository, SerializerInterface $serializer): JsonResponse
    {
        $jsonOrder = $serializer->serialize($orderRepository->find($order), 'json', ['groups' => 'getOrder']);
        return new JsonResponse($jsonOrder, Response::HTTP_OK, [], true);
    }

    #[Route('/api/orders/{id}', name: 'deleteOrder', methods: 'DELETE')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer une commande')]
    public function deleteOrder(Order $order, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($order);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/orders', name: 'createOrder', methods: 'POST')]
    public function createOrder(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $order = $serializer->deserialize($request->getContent(), Order::class, 'json');

        $errors = $validator->validate($order);
        if ($errors->count() > 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "La requête n'est pas valide");
        }

        $em->persist($order);
        $em->flush();

        $jsonOrder = $serializer->serialize($order, 'json');
        $location = $urlGenerator->generate('detailOrder', ['id' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonOrder, Response::HTTP_CREATED, [$location], true);
    }

    #[Route('/api/orders/{id}', name: 'updateOrder', methods: 'PUT')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier une commande')]
    #[IsGranted('ROLE_EMPLOYEE', message: 'Vous n\'avez pas les droits suffisants pour modifier une commande')]
    public function updateOrder(Order $currentOrder, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $order = $serializer->deserialize($request->getContent(), Order::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentOrder]);

        $errors = $validator->validate($order);
        if ($errors->count() > 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "La requête n'est pas valide");
        }

        $em->persist($order);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
