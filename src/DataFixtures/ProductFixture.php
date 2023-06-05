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
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);

        //get supplier
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'LtileMarcel']);

        //get QuantityUnit
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Mètre']);

        //get productFamily
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Salle de bain']);

        //get Vat
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 10%']);


        //generate customer
        $productFamily = new Product('Baignoire', 'Baignoire', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        //persist customer
        $manager->persist($productFamily);

    }

    // add second poduct for company 1
    public function addProduct2(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);

        //get supplier
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'LtileMarcel']);

        //get QuantityUnit
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Mètre']);

        //get productFamily
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Salle de bain']);

        //get Vat
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 20%']);

        //generate customer
        $productFamily = new Product('Lavabo', 'Lavabo', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        //persist customer
        $manager->persist($productFamily);
    }

    // add third poduct for company 1
    public function addProduct3(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);

        //get supplier
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'LtileMarcel']);

        //get QuantityUnit
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Mètre']);

        //get productFamily
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Salle de bain']);

        //get Vat
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 5.5%']);

        //generate customer
        $productFamily = new Product('Douche', 'Douche', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        //persist customer
        $manager->persist($productFamily);
    }

    // add first poduct for company 2
    public function addProduct4(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);

        //get supplier
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'PenMaker']);

        //get QuantityUnit
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Grammes']);

        //get productFamily
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Cuisine']);

        //get Vat
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 20%']);

        //generate customer
        $productFamily = new Product('Casserole', 'Casserole', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        //persist customer
        $manager->persist($productFamily);
    }

    // add second poduct for company 2
    public function addProduct5(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);

        //get supplier
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'PenMaker']);

        //get QuantityUnit
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Grammes']);

        //get productFamily
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Cuisine']);

        //get Vat
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 10%']);

        //generate customer
        $productFamily = new Product('Poêle', 'Poêle', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        //persist customer
        $manager->persist($productFamily);
    }

    // add third poduct for company 2
    public function addProduct6(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);

        //get supplier
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'PenMaker']);

        //get QuantityUnit
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Grammes']);

        //get productFamily
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Cuisine']);

        //get Vat
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 5.5%']);

        //generate customer
        $productFamily = new Product('Couteau', 'Couteau', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        //persist customer
        $manager->persist($productFamily);
    }

    // add first poduct for company 3
    public function addProduct7(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);

        //get supplier
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'Zeubi']);

        //get QuantityUnit
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Litre']);

        //get productFamily
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Chambre']);

        //get Vat
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 20%']);

        //generate customer
        $productFamily = new Product('Pantalon', 'Pantalon', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        //persist customer
        $manager->persist($productFamily);
    }

    // add second poduct for company 3
    public function addProduct8(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);

        //get supplier
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'Zeubi']);

        //get QuantityUnit
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Litre']);

        //get productFamily
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Chambre']);

        //get Vat
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 10%']);

        //generate customer
        $productFamily = new Product('Chemise', 'Chemise', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        //persist customer
        $manager->persist($productFamily);
    }

    // add third poduct for company 3
    public function addProduct9(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);

        //get supplier
        $supplier = $manager->getRepository(Supplier::class)->findOneBy(['name' => 'Zeubi']);

        //get QuantityUnit
        $quantityUnit = $manager->getRepository(QuantityUnit::class)->findOneBy(['name' => 'Litre']);

        //get productFamily
        $productFamily = $manager->getRepository(ProductFamily::class)->findOneBy(['name' => 'Chambre']);

        //get Vat
        $vat = $manager->getRepository(Vat::class)->findOneBy(['name' => 'TVA 5.5%']);

        //generate customer
        $productFamily = new Product('Chaussure', 'Chaussure', 100, 100, 100, 100, 100, $productFamily, $vat, $company, $quantityUnit, $supplier);

        //persist customer
        $manager->persist($productFamily);
    }




    public function getOrder(): int
    {
        return 6;
    }
}