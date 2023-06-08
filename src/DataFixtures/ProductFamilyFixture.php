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
        $this->addProductFamily4($manager);
        $this->addProductFamily5($manager);
        $this->addProductFamily6($manager);
        $this->addProductFamily7($manager);
        $manager->flush();
    }

    public function addProductFamily1(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);

        $productFamily = new ProductFamily('Salle de bain', 'Produits de sale de bain', $company);

        $manager->persist($productFamily);
    }

    public function addProductFamily2(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);

        $productFamily = new ProductFamily('Cuisine', 'Produits de cuisine', $company);

        $manager->persist($productFamily);
    }

    public function addProductFamily3(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);

        $productFamily = new ProductFamily('Chambre', 'Produits de chambre', $company);

        $manager->persist($productFamily);
    }

    public function addProductFamily4(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);

        $productFamily = new ProductFamily('Chambre', 'Produits de chambre', $company);

        $manager->persist($productFamily);
    }

    public function addProductFamily5(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);

        $productFamily = new ProductFamily('Cuisine', 'Produits de cuisine', $company);

        $manager->persist($productFamily);
    }

    public function addProductFamily6(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);

        $productFamily = new ProductFamily('Salon', 'Produits de salon', $company);

        $manager->persist($productFamily);
    }

    public function addProductFamily7(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);

        $productFamily = new ProductFamily('Expertise Marketing', 'Produits de expertise marketing', $company);

        $manager->persist($productFamily);
    }

    public function getOrder(): int
    {
        return 3;
    }
}