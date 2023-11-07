<?php

namespace App\Controller;

use App\Entity\Service;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ServiceController extends AbstractController
{
    #[Route('/api/services', name: 'services', methods: 'GET')]
    public function getAllService(ServiceRepository $serviceRepository, SerializerInterface $serializer): JsonResponse
    {
        $services = $serviceRepository->findAll();
        $jsonServices = $serializer->serialize($services, 'json', ['groups' => 'getService']);
        return new JsonResponse($jsonServices, Response::HTTP_OK, [], true);
    }

    #[Route('/api/services/{id}', name: 'detailService', methods: 'GET')]
    public function getOneService(Service $service, ServiceRepository $serviceRepository, SerializerInterface $serializer): JsonResponse
    {
        $jsonService = $serializer->serialize($serviceRepository->find($service), 'json', ['groups' => 'getService']);
        return new JsonResponse($jsonService, Response::HTTP_OK, [], true);
    }

    #[Route('/api/services/{id}', name: 'deleteService', methods: 'DELETE')]
    public function deleteService(Service $service, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($service);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/services', name: 'createService', methods: 'POST')]
    public function createService(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $service = $serializer->deserialize($request->getContent(), Service::class, 'json');

        $errors = $validator->validate($service);
        if ($errors->count() > 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "La requête n'est pas valide");
        }

        $em->persist($service);
        $em->flush();

        $jsonService = $serializer->serialize($service, 'json', ['groups' => 'getService']);
        $location = $urlGenerator->generate('detailService', ['id' => $service->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonService, Response::HTTP_CREATED, [$location], true);
    }

    #[Route('/api/services/{id}', name: 'updateService', methods: 'PUT')]
    public function updateService(Service $currentService, Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $service = $serializer->deserialize($request->getContent(), Service::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentService]);

        $errors = $validator->validate($service);
        if ($errors->count() > 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "La requête n'est pas valide");
        }

        $em->persist($service);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
