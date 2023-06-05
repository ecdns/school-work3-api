<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Company;
use Entity\Customer;
use Entity\CustomerStatus;
use Entity\Supplier;

class SupplierFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addSupplier1($manager);
        $this->addSupplier2($manager);
        $this->addSupplier3($manager);
        $manager->flush();
    }


    public function addSupplier1(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);


        //generate customer
        $supplier = new Supplier('LtileMarcel', 'Jean-Luc', 'Mélancon', 'jeanluc.mélancon@lagauchecestcool.fr', '1 rue de la paix', 'Paris', 'France', '75000', '0606060606', $company);

        //persist customer
        $manager->persist($supplier);

    }

    //add second supplier
    public function addSupplier2(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);


        //generate customer
        $supplier = new Supplier('PenMaker', 'Marine', 'LePen', 'marin.lepen@penmaker.fr', '1 rue de la paix', 'Paris', 'France', '75000', '0606060606', $company);

        //persist customer
        $manager->persist($supplier);
    }

    //add third supplier

    public function addSupplier3(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);

        //generate customer

        $supplier = new Supplier('Zeubi', 'Assane', 'Fatou', 'assane.fatou@zeubi.de', '1 rue de la paix', 'Paris', 'France', '75000', '0606060606', $company);

        //persist customer
        $manager->persist($supplier);

    }


    public function getOrder(): int
    {
        return 5;
    }
}