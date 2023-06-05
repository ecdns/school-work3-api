<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Company;
use Entity\Customer;
use Entity\CustomerStatus;
use Entity\Vat;

class VatFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addVat1($manager);
        $this->addVat2($manager);
        $this->addVat3($manager);
        $manager->flush();
    }


    public function addVat1(ObjectManager $manager): void
    {

        //generate vat
        $vat = new Vat('TVA 20%', 20, 'Tva à 20%');

        //persist customer
        $manager->persist($vat);

    }

    public function addVat2(ObjectManager $manager): void
    {

        //generate vat
        $vat = new Vat('TVA 10%', 10, 'Tva à 10%');

        //persist customer
        $manager->persist($vat);

    }

    public function addVat3(ObjectManager $manager): void
    {

        //generate vat
        $vat = new Vat('TVA 5.5%', 5.5, 'Tva à 5.5%');

        //persist customer
        $manager->persist($vat);

    }


    public function getOrder(): int
    {
        return 5;
    }
}