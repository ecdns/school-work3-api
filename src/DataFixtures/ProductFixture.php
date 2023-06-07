<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Company;
use Entity\Product;
use Entity\ProductFamily;
use Entity\QuantityUnit;
use Entity\Supplier;
use Entity\Vat;

class ProductFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addProduct1($manager);
        $this->addProduct2($manager);
        $this->addProduct3($manager);
        $this->addProduct4($manager);
        $this->addProduct5($manager);
        $this->addProduct6($manager);
        $this->addProduct7($manager);
        $this->addProduct8($manager);
        $this->addProduct9($manager);
        $manager->flush();
    }


    public function addProduct1(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'LtileMarcel']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Mètre']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Salle de bain']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 10%']);

        $productFamily = new Product('Baignoire', 'Baignoire', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct2(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'LtileMarcel']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Mètre']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Salle de bain']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 20%']);

        $productFamily = new Product('Lavabo', 'Lavabo', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    // add third poduct for company 1
    public function addProduct3(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'LtileMarcel']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Mètre']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Salle de bain']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 5.5%']);

        $productFamily = new Product('Douche', 'Douche', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct4(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'PenMaker']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Grammes']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Cuisine']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 20%']);

        $productFamily = new Product('Casserole', 'Casserole', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct5(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'PenMaker']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Grammes']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Cuisine']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 10%']);

        $productFamily = new Product('Poêle', 'Poêle', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct6(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'PenMaker']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Grammes']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Cuisine']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 5.5%']);

        $productFamily = new Product('Couteau', 'Couteau', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct7(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'Zeubi']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Litre']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Chambre']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 20%']);

        $productFamily = new Product('Pantalon', 'Pantalon', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct8(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'Zeubi']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Litre']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Chambre']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 10%']);

        $productFamily = new Product('Chemise', 'Chemise', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct9(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'Zeubi']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Litre']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Chambre']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 5.5%']);

        $productFamily = new Product('Chaussure', 'Chaussure', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }




    public function getOrder(): int
    {
        return 6;
    }
}