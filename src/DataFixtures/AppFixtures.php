<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private const ADMIN_EMAIL = "admin@admin.com";
    private const ADMIN_PASSWORD = "admin";
    private const EMPLOYEES_NB = 6;
    private const CLIENTS_NB = 30;
    private const ORDERS_NB = 20;
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
    private const STATUS = [
        "waiting",
        "preparing",
        "ready",
        "collected"
    ];

    public function __construct()
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("fr_FR");
        
        // Fixtures pour les utilisateurs

        $admin = new User();
        $admin->setEmail(self::ADMIN_EMAIL)
            ->setPassword(self::ADMIN_PASSWORD)
            ->setRoles(["ROLE_ADMIN"]);
        $manager->persist($admin);

        $employees = [null];
        for($i = 0; $i < self::EMPLOYEES_NB; $i++) {
            $employee = new User;
            $employee->setEmail($faker->email())
                ->setPassword("test")
                ->setRoles(["ROLE_EMPLOYEE"])
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName());
            $employees[] = $employee;
            $manager->persist($employee);
        }

        $clients = [null];
        for($i = 0; $i < self::CLIENTS_NB; $i++) {
            $client = new User;
            $client->setEmail($faker->email())
                ->setPassword("test")
                ->setFirstname($faker->firstName())
                ->setLastname($faker->lastName());
            $clients[] = $client;
            $manager->persist($client);
        }

        // Fixtures pour les catégories
        foreach(self::CATEGORY as $element) {
            $category = new Category;
            $category->setName($element);
            $manager->persist($category);
        }

        // Fixtures les articles
        foreach(self::ARTICLES as $element) {
            $article = new Article;
            $article->setName($element);
            $article->setPrice($faker->numberBetween(200, 500));
            $manager->persist($article);
        }

        // Fixtures les services
        foreach(self::SERVICES as $element) {
            $service = new Service;
            $service->setName($element);
            $service->setPrice($faker->numberBetween(500, 2000));
            $manager->persist($service);
        }

        for($i = 0; $i < self::ORDERS_NB; $i++) {
            $order = new Order();
            $content = ["article" => "veste", "service" => "lavage à sec", "price" => 50, "number" => 1];
            $order->setContent($content)
                ->setStatus($faker->randomElement(self::STATUS))
                ->setClient($faker->randomElement($clients))
                ->setEmployee($faker->randomElement($employees))
                ->setDeposit($faker->dateTimeBetween('-3 days', 'now'))
                ->setPickUp($faker->dateTimeBetween('-1 days', '+3 days'))
                ->setMessage($faker->realText(20))
                ->setTotalPrice($faker->numberBetween(1000, 5000))
                ->setPayment($faker->dateTimeBetween('-3 days', '-2 days'));
            $manager->persist($order);
        }

        $manager->flush();
    }
}
