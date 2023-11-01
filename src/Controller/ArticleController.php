<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ArticleController extends AbstractController
{

    #[Route('/api/articles', name: 'articles', methods: ['GET'])]
    public function getAllArticles(ArticleRepository $articleRepository, SerializerInterface $serializer): JsonResponse
    {
        $articles = $articleRepository->findAll();
        $jsonArticles = $serializer->serialize($articles, 'json', ["groups" => "getArticle"]);
        return new JsonResponse($jsonArticles, Response::HTTP_OK, [], true);
    }

    #[Route('/api/articles/{id}', name: 'detailArticle', methods: ['GET'])]
    public function getOneArticle(Article $article, SerializerInterface $serializer): JsonResponse
    {
        // $article = $articleRepository->find($articleUrl);
        $jsonArticle = $serializer->serialize($article, 'json', ["groups" => "getArticle"]);
        return new JsonResponse($jsonArticle, Response::HTTP_OK, [], true);
    }

    #[Route('/api/articles/{id}', name: 'deleteArticle', methods: ['DELETE'])]
    public function deleteArticle(Article $article, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($article);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/articles', name: 'createArticle', methods: 'POST')]
    public function createArticle(
        Request $request, 
        SerializerInterface $serializer, 
        EntityManagerInterface $em, 
        UrlGenerator $urlGenerator
        ): JsonResponse
    {
        $article = $serializer->deserialize($request->getContent(), Article::class, 'json');

        $em->persist($article);
        $em->flush();

        $jsonArticle = $serializer->serialize($article, 'json', ["groups" => "getArticle"]);

        // Génération de l'url de la nouvelle ressource créee grâce à UrlGenerator
        $location = $urlGenerator->generate('detailArticle', ['id' => $article->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        // Ajout de l'url de la ressource dans l'en tête de retour de la jsonResponse
        return new JsonResponse($jsonArticle, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/api/articles', name: 'updateArticle', methods: 'PUT')]
    public function updateArticle(
        Request $request, 
        SerializerInterface $serializer, 
        EntityManagerInterface $em, 
        Article $currentArticle
    ): JsonResponse {

        $article = $serializer->deserialize(
            $request->getContent(), 
            Article::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentArticle]
        );
        // le paramêtre [AbstractNormalizer::OBJECT_TO_POPULATE] permet de désérialiser directement à l’intérieur de l’objet $currentArticle , qui correspond à l'article passé dans l’URL.
        $em->persist($article);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
