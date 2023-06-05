<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Company;
use Entity\License;
use Entity\Role;

class RoleFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addRole1($manager);
        $this->addRole2($manager);
        $this->addRole3($manager);
        $manager->flush();
    }

    public function addRole1(ObjectManager $manager): void
    {

        $company = new Role('Administrateur', 'Role administrateur');

        $manager->persist($company);
    }

    //add second role
    public function addRole2(ObjectManager $manager): void
    {

        $company = new Role('Utilisateur', 'Role utilisateur');

        $manager->persist($company);
    }

    //add third role
    public function addRole3(ObjectManager $manager): void
    {

        $company = new Role('Visiteur', 'Role visiteur');

        $manager->persist($company);
    }


    public function getOrder(): int
    {
        return 3;
    }
}