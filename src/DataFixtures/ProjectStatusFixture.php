<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Company;
use Entity\Project;
use Entity\ProjectStatus;

class ProjectStatusFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addProject1($manager);
        $this->addProject2($manager);
        $this->addProject3($manager);
        $this->addProject4($manager);
        $this->addProject5($manager);
        $manager->flush();
    }


    public function addProject1(ObjectManager $manager): void
    {
        $customer = new ProjectStatus('Terminé', 'Le projet est terminé');

        $manager->persist($customer);
    }

    public function addProject2(ObjectManager $manager): void
    {
        $customer = new ProjectStatus('En cours', 'Le projet est en cours');

        $manager->persist($customer);
    }

    public function addProject3(ObjectManager $manager): void
    {
        $customer = new ProjectStatus('En attente', 'Le projet est en attente');

        $manager->persist($customer);
    }

    public function addProject4(ObjectManager $manager): void
    {
        $customer = new ProjectStatus('Annulé', 'Le projet est annulé');

        $manager->persist($customer);
    }

    public function addProject5(ObjectManager $manager): void
    {
        $customer = new ProjectStatus('En pause', 'Le projet est en pause');

        $manager->persist($customer);
    }

    public function getOrder(): int
    {
        return 1;
    }
}