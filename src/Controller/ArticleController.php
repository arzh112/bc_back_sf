<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ArticleController extends AbstractController
{

    #[Route('/api/articles', name: 'article', methods: ['GET'])]
    public function getAllArticles(ArticleRepository $articleRepository, SerializerInterface $serializer): JsonResponse
    {
        $articles = $articleRepository->findAll();
        $jsonArticles = $serializer->serialize($articles, 'json');
        return new JsonResponse($jsonArticles, Response::HTTP_OK, [], true);
    }
}
