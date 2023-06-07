<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\OrderForm;
use Entity\OrderFormProduct;
use Entity\Product;
use Entity\Project;

class OrderFormFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addOrderForm1($manager);
        $this->addOrderForm2($manager);
        $this->addOrderForm3($manager);
        $manager->flush();
        $this->addProductToOrderForm1($manager);
        $this->addProductToOrderForm2($manager);
        $this->addProductToOrderForm3($manager);
        $manager->flush();
    }

    public function addOrderForm1(ObjectManager $manager): void
    {
        $project = $manager->getRepository(Project::class)->findOneBy(['name' =>  'Amandanas - Construction']);

        $orderFrom = new OrderForm('Bon de Commande pour Amandanas', 'Le bon de commande conciste à construire le batiment pour Amandanas', $project);

        $manager->persist($orderFrom);
    }

    public function addOrderForm2(ObjectManager $manager): void
    {
        $project = $manager->getRepository(Project::class)->findOneBy(['name' =>  'Redook - SDB']);

        $orderFrom = new OrderForm('Bon de Commande pour Redook - SDB', 'Le bon de commande conciste à construire la salle de bain pour Redook', $project);

        $manager->persist($orderFrom);
    }

    public function addOrderForm3(ObjectManager $manager): void
    {
        $project = $manager->getRepository(Project::class)->findOneBy(['name' => 'Redook - Cuisine']);

        $orderFrom = new OrderForm('Bon de Commande pour Redook - Cuisine', 'Le bon de commande conciste à construire la cuisine pour Redook', $project);

        $manager->persist($orderFrom);
    }

    public function addProductToOrderForm1(ObjectManager $manager): void
    {
        $estimate = $manager->getRepository(OrderForm::class)->findOneBy(['name' => 'Bon de Commande pour Amandanas']);

        $product = $manager->getRepository(Product::class)->findOneBy(['name' => 'Rideau de douche']);

        $estimateProduct = new OrderFormProduct($estimate, $product, 100);

        $manager->persist($estimateProduct);
    }

    public function addProductToOrderForm2(ObjectManager $manager): void
    {
       $estimate = $manager->getRepository(OrderForm::class)->findOneBy(['name' => 'Bon de Commande pour Redook - SDB']);

       $product = $manager->getRepository(Product::class)->findOneBy(['name' =>  'Baignoire']);

       $estimateProduct = new OrderFormProduct($estimate, $product, 100);

       $manager->persist($estimateProduct);
    }

    public function addProductToOrderForm3(ObjectManager $manager): void
    {
       $estimate = $manager->getRepository(OrderForm::class)->findOneBy(['name' => 'Bon de Commande pour Redook - Cuisine']);

       $product = $manager->getRepository(Product::class)->findOneBy(['name' =>  'Lavabo']);

       $estimateProduct = new OrderFormProduct($estimate, $product, 100);

       $manager->persist($estimateProduct);
    }


    public function getOrder(): int
    {
        return 8;
    }
}