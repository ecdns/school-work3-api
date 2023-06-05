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
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);

        //get CustomerStatus
        $customerStatus = $manager->getRepository(CustomerStatus::class)->findOneBy(['name' => 'Prospect']);

        //generate customer
        $customer = new Customer('Jean', 'Dupont', 'jean.dupont@wanadoo.fr', '1 rue de la paix', 'Paris', 'France', '75000', '0606060606', $company, $customerStatus);

        //persist customer
        $manager->persist($customer);

    }

    //add second customer
    public function addCustomer2(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Bubulle']);

        //get CustomerStatus
        $customerStatus = $manager->getRepository(CustomerStatus::class)->findOneBy(['name' => 'Client']);

        //generate customer
        $customer = new Customer('Paul', 'Martin', 'paul.martin@martinville.fr', '1 rue de la paix', 'Paris', 'France', '75000', '0606060606', $company, $customerStatus);

        //persist customer
        $manager->persist($customer);
    }

    //add third customer
    public function addCustomer3(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Cocorico']);

        //get CustomerStatus
        $customerStatus = $manager->getRepository(CustomerStatus::class)->findOneBy(['name' => 'Ancien client']);

        //generate customer
        $customer = new Customer('Jacques', 'Durand', 'jacques.durand@gmail.com', '1 rue de la paix', 'Paris', 'France', '75000', '0606060606', $company, $customerStatus);

        //persist customer
        $manager->persist($customer);
    }

    public function getOrder(): int
    {
        return 5;
    }
}