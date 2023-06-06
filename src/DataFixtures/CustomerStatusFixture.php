<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\CustomerStatus;

class CustomerStatusFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addCustomerStatus1($manager);
        $this->addCustomerStatus2($manager);
        $this->addCustomerStatus3($manager);
        $manager->flush();
    }

    public function addCustomerStatus1(ObjectManager $manager): void
    {
        $company = new CustomerStatus('Prospect', 'Client potetiel');

        $manager->persist($company);
    }

    public function addCustomerStatus2(ObjectManager $manager): void
    {
        $company = new CustomerStatus('Client', 'Client');

        $manager->persist($company);
    }

    public function addCustomerStatus3(ObjectManager $manager): void
    {
        $company = new CustomerStatus('Ancien client', 'Ancien client');

        $manager->persist($company);
    }

    public function getOrder(): int
    {
        return 1;
    }
}