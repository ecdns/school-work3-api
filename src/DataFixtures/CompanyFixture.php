<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Company;
use Entity\License;

class CompanyFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addCompany1($manager);
        $this->addCompany2($manager);
        $this->addCompany3($manager);
        $manager->flush();
    }

    public function addCompany1(ObjectManager $manager): void
    {
        $license = $manager->getRepository(License::class)->findOneBy(['name' => 'premium']);

        $company = new Company('Aubade', 'rue de la paix', 'Paris', 'France', '75000', '0145879652', 'La vie est belle', 'logo.png', $license , new \DateTime('2021-12-31'), 'fr', true);

        $manager->persist($company);
    }

    //add second company
    public function addCompany2(ObjectManager $manager): void
    {
        $license = $manager->getRepository(License::class)->findOneBy(['name' => 'standard']);

        $company = new Company('Bubulle', 'rue de la paix', 'Paris', 'France', '75000', '0145879652', 'La vie est belle', 'logo.png', $license , new \DateTime('2021-12-31'), 'fr', true);

        $manager->persist($company);
    }

//add third company
    public function addCompany3(ObjectManager $manager): void
    {
        $license = $manager->getRepository(License::class)->findOneBy(['name' => 'basic']);

        $company = new Company('Cocorico', 'rue de la paix', 'Paris', 'France', '75000', '0145879652', 'La vie est belle', 'logo.png', $license , new \DateTime('2021-12-31'), 'fr', true);

        $manager->persist($company);
    }

    public function getOrder(): int
    {
        return 2;
    }
}