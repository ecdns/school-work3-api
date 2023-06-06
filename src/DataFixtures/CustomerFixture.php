<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Company;
use Entity\Customer;
use Entity\CustomerStatus;
use Entity\License;

class CustomerFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addCustomer1($manager);
        $this->addCustomer2($manager);
        $this->addCustomer3($manager);
        $manager->flush();
    }


    public function addCustomer1(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);
        $customerStatus = $manager->getRepository(CustomerStatus::class)->findOneBy(['name' => 'Prospect']);

        $customer = new Customer('La company Canadienne de Jean','Jean', 'Dupont', 'jean.dupont@wanadoo.fr', 'PDG','1 rue de la paix', 'Paris', 'France', '75000', '0606060606', $company, $customerStatus);

        $manager->persist($customer);
    }

    public function addCustomer2(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);
        $customerStatus = $manager->getRepository(CustomerStatus::class)->findOneBy(['name' => 'Client']);

        $customer = new Customer('DodoCompany','Paul', 'Martin', 'paul.martin@martinville.fr', 'Commercial','1 rue de la paix', 'Paris', 'France', '75000', '0606060606', $company, $customerStatus);

        $manager->persist($customer);
    }

    public function addCustomer3(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);
        $customerStatus = $manager->getRepository(CustomerStatus::class)->findOneBy(['name' => 'Ancien client']);

        $customer = new Customer('Le logobi de toulouse','Jacques', 'Durand', 'jacques.durand@gmail.com', 'Danseur','1 rue de la paix', 'Paris', 'France', '75000', '0606060606', $company, $customerStatus);

        $manager->persist($customer);
    }

    public function getOrder(): int
    {
        return 5;
    }
}