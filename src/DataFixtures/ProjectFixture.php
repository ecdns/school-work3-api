<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Company;
use Entity\Customer;
use Entity\CustomerStatus;
use Entity\Project;
use Entity\ProjectStatus;
use Entity\User;

class ProjectFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addProject1($manager);
        $this->addProject2($manager);
        $this->addProject3($manager);
        $manager->flush();
    }


    public function addProject1(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);

        //get User
        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'dorian.breuillard@gmail.com']);

        //get Project Status
        $projectStatus = $manager->getRepository(ProjectStatus::class)->findOneBy(['name' => 'Terminé']);

        //get Customer
        $customer = $manager->getRepository(Customer::class)->findOneBy(['firstName' => 'Jean']);

        //generate customer
        $customer = new Project('Jean', 'Dupont', $company, $user, $customer, $projectStatus);

        //persist customer
        $manager->persist($customer);

    }

    public function addProject2(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);

        //get User
        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'dorian.breuillard@gmail.com']);

        //get Project Status
        $projectStatus = $manager->getRepository(ProjectStatus::class)->findOneBy(['name' => 'En cours']);

        //get Customer
        $customer = $manager->getRepository(Customer::class)->findOneBy(['firstName' => 'Jean']);

        //generate customer
        $customer = new Project('Jean', 'Dupont', $company, $user, $customer, $projectStatus);

        //persist customer
        $manager->persist($customer);

    }

    public function addProject3(ObjectManager $manager): void
    {
        //get company
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);

        //get User
        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'dorian.breuillard@gmail.com']);

        //get Project Status
        $projectStatus = $manager->getRepository(ProjectStatus::class)->findOneBy(['name' => 'En attente']);

        //get Customer
        $customer = $manager->getRepository(Customer::class)->findOneBy(['firstName' => 'Jean']);

        //generate customer
        $customer = new Project('Jean', 'Dupont', $company, $user, $customer, $projectStatus);

        //persist customer
        $manager->persist($customer);

    }

    public function getOrder(): int
    {
        return 5;
    }
}