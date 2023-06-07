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
        $this->addProduct10($manager);
        $this->addProduct11($manager);
        $manager->flush();
    }


    public function addProduct1(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'LtileMarcel']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Unitée']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Salle de bain']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 10%']);

        $productFamily = new Product('Baignoire', 'Baignoire', 300, 600, 100, 10, false, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct2(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'LtileMarcel']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Unitée']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Salle de bain']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 20%']);

        $productFamily = new Product('Lavabo', 'Lavabo', 45, 60, 130, 40, false, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    // add third product for company 1
    public function addProduct3(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'LtileMarcel']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Mètre']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Salle de bain']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 5.5%']);

        $productFamily = new Product('Rideau de douche', 'Rideau de douche', 15, 30, 300, 20, true, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct4(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'PenMaker']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Unitée']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Cuisine']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 20%']);

        $productFamily = new Product('Casserole', 'Casserole', 10, 30, 100, 20, false, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct5(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'PenMaker']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Unitée']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Cuisine']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 10%']);

        $productFamily = new Product('Poêle', 'Poêle', 120, 150, 130, 30, true, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct6(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'PenMaker']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Unitée']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Cuisine']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 5.5%']);

        $productFamily = new Product('Couteau', 'Couteau', 130, 180, 360, 20, true, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct7(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'Zeubi']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Unitée']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Chambre']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 20%']);

        $productFamily = new Product('Armoire', 'Armoire', 80, 120, 1000, 20, false, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct8(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'Zeubi']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Unitée']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Chambre']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 10%']);

        $productFamily = new Product('Table de nuit', 'Table de nuit', 110, 120, 250, 20, false, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct9(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'Zeubi']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Unitée']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Chambre']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 5.5%']);

        $productFamily = new Product('Lit', 'Lit', 100, 150, 80, 20, true, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct10(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'LtileMarcel']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Unitée']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Salon']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 20%']);

        $productFamily = new Product('Canapé', 'Canapé', 100, 140, 100, 20, true, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }

    public function addProduct11(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'LtileMarcel']);
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Unitée']);
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Cuisine']);
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 20%']);

        $productFamily = new Product('Etagere', 'Etagère', 100, 180, 70, 20, false, $productFamily, $vat, $company, $quantityUnit, $supplier);

        $manager->persist($productFamily);
    }



    public function getOrder(): int
    {
        return 6;
    }
}