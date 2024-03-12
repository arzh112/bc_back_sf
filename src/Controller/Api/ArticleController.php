<?php

namespace App\Controller\Api;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\ServiceRepository;
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

class ArticleController extends AbstractController
{

    #[Route('/api/articles', name: 'articles', methods: ['GET'])]
    public function getAllArticles(ArticleRepository $articleRepository, SerializerInterface $serializer): JsonResponse
    {
        // récupération d'un tableau d'objet contenant tous les articles
        $articles = $articleRepository->findAll();
        // sérialisation des objets articles au format JSON
        $jsonArticles = $serializer->serialize($articles, 'json', ["groups" => "getArticle"]);
        // le return de la méthode retourne une réponse au format JSON avec le code de réponse HTTP
        return new JsonResponse($jsonArticles, Response::HTTP_OK, [], true);
    }

    #[Route('/api/articles/{id}', name: 'detailArticle', methods: ['GET'])]
    public function getOneArticle(Article $article, SerializerInterface $serializer): JsonResponse
    {
        $jsonArticle = $serializer->serialize($article, 'json', ["groups" => "getArticle"]);
        return new JsonResponse($jsonArticle, Response::HTTP_OK, [], true);
    }

    #[Route('/api/articles/{id}', name: 'deleteArticle', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un article')]
    public function deleteArticle(Article $article, EntityManagerInterface $em): JsonResponse
    {
        // utilisation de la méthode remove de l'entityManagerInterface
        $em->remove($article);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/articles', name: 'createArticle', methods: 'POST')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un article')]
    public function createArticle(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
        ServiceRepository $serviceRepository,
        CategoryRepository $categoryRepository
    ): JsonResponse {

        // On dé-sérialise l'objet JSON en objet PHP article
        $article = $serializer->deserialize($request->getContent(), Article::class, 'json');

        // On vérifie les erreurs
        $errors = $validator->validate($article);
        if ($errors->count() > 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "La requête n'est pas valide");
        }

        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        $article->setName(htmlspecialchars($article->getName()));
        $article->setDescription(htmlspecialchars($article->getDescription()));

        // Récupération de l'idService. S'il n'est pas défini
        $arrayIdService = $content['idService'] ?? null;
        $arrayIdCategory = $content['idCategory'] ?? null;

        // Pour chaque id dans les tableaux, on cherche l'entité correspondante et on l'ajoute à l'article
        // Si "find" ne trouve pas l'article, alors null sera retourné.
        if($arrayIdService !== null) {
            foreach ($arrayIdService as $service) {
                $article->addService($serviceRepository->find($service));
            }
        }
        if($arrayIdCategory !== null) {
            foreach ($arrayIdCategory as $category) {
                $article->addCategory($categoryRepository->find($category));
            }
        }

        $em->persist($article);
        $em->flush();

        $jsonArticle = $serializer->serialize($article, 'json', ["groups" => "getArticle"]);

        // Génération de l'url de la nouvelle ressource créee grâce à UrlGenerator
        $location = $urlGenerator->generate('detailArticle', ['id' => $article->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        // Ajout de l'url de la ressource dans l'en tête de retour de la jsonResponse
        return new JsonResponse($jsonArticle, Response::HTTP_CREATED, ['location' => $location], true);
    }

    #[Route('/api/articles/{id}', name: 'updateArticle', methods: 'PUT')]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un article')]
    public function updateArticle(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        Article $currentArticle,
        ServiceRepository $serviceRepository,
        CategoryRepository $categoryRepository,
        ValidatorInterface $validator
    ): JsonResponse {

        
        $article = $serializer->deserialize(
            $request->getContent(),
            Article::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentArticle]
        );
        // le paramêtre [AbstractNormalizer::OBJECT_TO_POPULATE] permet de désérialiser directement à l’intérieur de l’objet $currentArticle , qui correspond à l'article passé dans l’URL.

        $errors = $validator->validate($article);
        if ($errors->count() > 0) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, "La requête n'est pas valide");
        }
        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idService. S'il n'est pas défini, alors on met -1 par défaut.
        $arrayIdService = $content['idService'] ?? null;
        $arrayIdCategory = $content['idCategory'] ?? null;

        // Si "find" ne trouve pas l'article, alors null sera retourné.
        if($arrayIdService !== null) {
            foreach ($arrayIdService as $service) {
                $article->addService($serviceRepository->find($service));
            }
        }
        if($arrayIdCategory !== null) {
            foreach ($arrayIdCategory as $category) {
                $article->addCategory($categoryRepository->find($category));
            }
        }

        $em->persist($article);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
