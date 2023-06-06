<?php

namespace DataFixtures;

use DateTime;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Estimate;
use Entity\EstimateProduct;
use Entity\EstimateStatus;
use Entity\Product;
use Entity\Project;
use Entity\Role;

class EstimateFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addEstimate1($manager);
        $this->addEstimate2($manager);
        $this->addEstimate3($manager);
        $manager->flush();
        $this->addProductToEstimate1($manager);
        $this->addProductToEstimate2($manager);
        $this->addProductToEstimate3($manager);
        $manager->flush();
    }

    public function addEstimate1(ObjectManager $manager): void
    {
        //get EstimateStatus
        $estimateStatus = $manager->getRepository(EstimateStatus::class)->findOneBy(['name' => 'Brouillon']);

        //get the projectStatus
        $project = $manager->getRepository(Project::class)->findOneBy(['name' =>  'Amandanas - Construction']);

        $estimate = new Estimate('Devis pour Amandanas', 'Le devis conciste à construire le batiment pour Amandanas', $project, new DateTime('2025-01-01'), $estimateStatus);

        $manager->persist($estimate);
    }

    public function addEstimate2(ObjectManager $manager): void
    {
        //get EstimateStatus
        $estimateStatus = $manager->getRepository(EstimateStatus::class)->findOneBy(['name' => 'Envoyé']);

        //get the projectStatus
        $project = $manager->getRepository(Project::class)->findOneBy(['name' =>  'Redook - SDB']);

        $estimate = new Estimate('Devis pour Redook - SDB', 'Le devis conciste à construire la salle de bain pour Redook', $project, new DateTime('2024-01-01'), $estimateStatus);

        $manager->persist($estimate);
    }

    public function addEstimate3(ObjectManager $manager): void
    {
        //get EstimateStatus
        $estimateStatus = $manager->getRepository(EstimateStatus::class)->findOneBy(['name' => 'Validé']);

        //get the projectStatus
        $project = $manager->getRepository(Project::class)->findOneBy(['name' =>  'Redook - Cuisine']);

        $estimate = new Estimate('Devis pour Redook - Cuisine', 'Le devis conciste à construire la cuisine pour Redook', $project, new DateTime('2026-01-01'), $estimateStatus);

        $manager->persist($estimate);
    }

    //add Product to Estimate
 public function addProductToEstimate1(ObjectManager $manager): void
 {
        //get EstimateStatus
        $estimate = $manager->getRepository(Estimate::class)->findOneBy(['name' => 'Devis pour Amandanas']);

        //get the projectStatus
        $product = $manager->getRepository(Product::class)->findOneBy(['name' =>  'Douche']);

        $estimateProduct = new EstimateProduct($estimate, $product, 100);

        $manager->persist($estimateProduct);
 }

    public function addProductToEstimate2(ObjectManager $manager): void
    {
        //get EstimateStatus
        $estimate = $manager->getRepository(Estimate::class)->findOneBy(['name' => 'Devis pour Redook - SDB']);

        //get the projectStatus
        $product = $manager->getRepository(Product::class)->findOneBy(['name' =>  'Baignoire']);

        $estimateProduct = new EstimateProduct($estimate, $product, 68);

        $manager->persist($estimateProduct);
    }

    public function addProductToEstimate3(ObjectManager $manager): void
    {
        //get EstimateStatus
        $estimate = $manager->getRepository(Estimate::class)->findOneBy(['name' => 'Devis pour Redook - Cuisine']);

        //get the projectStatus
        $product = $manager->getRepository(Product::class)->findOneBy(['name' =>  'Lavabo']);

        $estimateProduct = new EstimateProduct($estimate, $product, 45);

        $manager->persist($estimateProduct);
    }



    public function getOrder(): int
    {
        return 8;
    }
}