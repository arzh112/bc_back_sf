<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Repository\CategoryRepository;
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

class CategoryController extends AbstractController
{
    #[Route('/api/categories', name: 'categories', methods: 'GET')]
    public function getAllCategories(CategoryRepository $categoryRepository, SerializerInterface $serializer): JsonResponse
    {
        $categories = $categoryRepository->findAll();
        $jsonCategories = $serializer->serialize($categories, 'json', ["groups" => "getCategory"]);
        return new JsonResponse($jsonCategories, Response::HTTP_OK, [], true);
    }

    #[Route('/api/categories/{id}', name: 'detailCategory', methods: 'GET')]
    public function getOneCategory(Category $category, SerializerInterface $serializer): JsonResponse
    {
        $jsonCategory = $serializer->serialize($category, 'json', ["groups" => "getCategory"]);
        return new JsonResponse($jsonCategory, Response::HTTP_OK, [], true);
    }

    #[Route('/api/categories/{id}', name: 'deleteCategory', methods: 'DELETE')]
    public function deleteCategory(Category $category, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($category);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/categories', name: 'createCategory', methods: 'POST')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer une catégorie')]
    public function createCategory(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json');

        $errors = $validator->validate($category);
        if ($errors->count() > 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "La requête n'est pas valide");
        }

        $em->persist($category);
        $em->flush();

        $jsonCategory = $serializer->serialize($category, 'json');
        $location = $urlGenerator->generate('detailCategory', ['id' => $category->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        
        return new JsonResponse($jsonCategory, Response::HTTP_CREATED, [$location], true);
    }

    #[Route('/api/categories/{id}', name: 'updateCategory', methods: 'PUT')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier une catégorie')]
    public function updateCategory(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, Category $currentCategory, ValidatorInterface $validator): JsonResponse
    {
        $category = $serializer->deserialize($request->getContent(), Category::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCategory]);

        $errors = $validator->validate($category);
        if ($errors->count() > 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "La requête n'est pas valide");
        }

        $em->persist($category);
        $em->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
