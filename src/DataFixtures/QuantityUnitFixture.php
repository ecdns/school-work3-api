<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Company;
use Entity\ProductFamily;
use Entity\QuantityUnit;

class QuantityUnitFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addQuantityUnit1($manager);
        $this->addQuantityUnit2($manager);
        $this->addQuantityUnit3($manager);
        $this->addQuantityUnit4($manager);
        $this->addQuantityUnit5($manager);
        $this->addQuantityUnit6($manager);
        $this->addQuantityUnit7($manager);
        $this->addQuantityUnit8($manager);

        $manager->flush();
    }


    public function addQuantityUnit1(ObjectManager $manager): void
    {
        //generate quantityUnit
        $quantityUnit = new QuantityUnit('Grammes', 'kg', 'Unitée en grammes');

        //persist customer
        $manager->persist($quantityUnit);

    }

    //add second quantityUnit
public function addQuantityUnit2(ObjectManager $manager): void
    {
        //generate quantityUnit
        $quantityUnit = new QuantityUnit('Kilogrammes', 'kg', 'Unitée en kilogrammes');

        //persist customer
        $manager->persist($quantityUnit);
    }

    //add third quantityUnit
public function addQuantityUnit3(ObjectManager $manager): void
    {
        //generate quantityUnit
        $quantityUnit = new QuantityUnit('Litre', 'L', 'Unitée en litre');

        //persist customer
        $manager->persist($quantityUnit);
    }

    //add fourth quantityUnit
public function addQuantityUnit4(ObjectManager $manager): void
    {
        //generate quantityUnit
        $quantityUnit = new QuantityUnit('Millilitre', 'mL', 'Unitée en millilitre');

        //persist customer
        $manager->persist($quantityUnit);
    }

    //add fifth quantityUnit
public function addQuantityUnit5(ObjectManager $manager): void
    {
        //generate quantityUnit
        $quantityUnit = new QuantityUnit('Unitée', 'U', 'Unitée');

        //persist customer
        $manager->persist($quantityUnit);
    }

    //add sixth quantityUnit
public function addQuantityUnit6(ObjectManager $manager): void
    {
        //generate quantityUnit
        $quantityUnit = new QuantityUnit('Mètre', 'm', 'Unitée en mètre');

        //persist customer
        $manager->persist($quantityUnit);
    }

    //add seventh quantityUnit
public function addQuantityUnit7(ObjectManager $manager): void
    {
        //generate quantityUnit
        $quantityUnit = new QuantityUnit('Centimètre', 'cm', 'Unitée en centimètre');

        //persist customer
        $manager->persist($quantityUnit);
    }

    //add eighth quantityUnit
public function addQuantityUnit8(ObjectManager $manager): void
    {
        //generate quantityUnit
        $quantityUnit = new QuantityUnit('Millimètre', 'mm', 'Unitée en millimètre');

        //persist customer
        $manager->persist($quantityUnit);
    }




    public function getOrder(): int
    {
        return 1;
    }
}