<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private const ADMIN_EMAIL = "admin@admin.com";
    private const ADMIN_PASSWORD = "admin";
    private const EMPLOYEES_NB = 6;
    private const CLIENTS_NB = 30;
    private const CATEGORY = [
        "laine",
        "délicat",
        "linge de maison",
        "cuir",
        "cachemire",
        "jean",
        "cotton",
        "synthétique",
        "soie",
        "plume",
        "costume"
    ];
    private const ARTICLES = [
        "chemise",
        "t-shirt",
        "robe",
        "jupe",
        "pull",
        "veste",
        "pantalon",
        "short",
        "manteau",
        "drap",
        "couette",
        "rideaux",
        "coussin",
        "tapis",
        "housse",
        "chaussures",
        "sac"
    ];
    private const SERVICES = [
        "lavage",
        "lavage à sec",
        "repassage",
        "réparation",
        "traitement anti-tâches",
        "imperméabilisation",
        "blanchiment",
        "nettoyage"
    ];

    public function __construct(
        private UserPasswordHasherInterface $hasher,
        )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");
        
        // Fixtures pour les utilisateurs

        $admin = new User();
        $admin->setEmail(self::ADMIN_EMAIL)
            ->setPassword($this->hasher->hashPassword($admin, self::ADMIN_PASSWORD))
            ->setRoles(["ROLE_ADMIN"]);
        $manager->persist($admin);

        for($i = 0; $i < self::EMPLOYEES_NB; $i++) {
            $employee = new User;
            $employee->setEmail($faker->email())
                ->setPassword($this->hasher->hashPassword($employee, "test"))
                ->setRoles(["ROLE_EMPLOYEE"])
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName());
            $manager->persist($employee);
        }

        for($i = 0; $i < self::CLIENTS_NB; $i++) {
            $client = new User;
            $client->setEmail($faker->email())
                ->setPassword($this->hasher->hashPassword($client, "test"))
                ->setRoles(["ROLE_CLIENT"])
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName());
            $manager->persist($client);
        }

        // Fixtures pour les catégories
        $categories = [];
        foreach(self::CATEGORY as $element) {
            $category = new Category;
            $category->setName($element);
            $manager->persist($category);
        }

        // Fixtures les articles
        foreach(self::ARTICLES as $element) {
            $article = new Article;
            $article->setName($element);
            $article->setPrice(200);
            $manager->persist($article);
        }

        // Fixtures les services
        foreach(self::SERVICES as $element) {
            $service = new Service;
            $service->setName($element);
            $service->setPrice(200);
            $manager->persist($service);
        }

        $manager->flush();
    }
}
