<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Company;
use Entity\ProductFamily;
use Entity\Supplier;

class ProductFamilyFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addProductFamily1($manager);
        $this->addProductFamily2($manager);
        $this->addProductFamily3($manager);
        $manager->flush();
    }


    public function addProductFamily1(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);


        //generate customer
        $productFamily = new ProductFamily('Salle de bain', 'Produits de sale de bain', $company);

        //persist customer
        $manager->persist($productFamily);

    }

    //add second customer
    public function addProductFamily2(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);

        //generate customer
        $productFamily = new ProductFamily('Cuisine', 'Produits de cuisine', $company);

        //persist customer
        $manager->persist($productFamily);
    }

    //add third customer
    public function addProductFamily3(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);

        //generate customer
        $productFamily = new ProductFamily('Chambre', 'Produits de chambre', $company);

        //persist customer
        $manager->persist($productFamily);
    }



    public function getOrder(): int
    {
        return 3;
    }
}