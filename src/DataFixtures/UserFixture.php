<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\User;
use Service\Auth;

class UserFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addUser1($manager);
        $this->addUser2($manager);
        $this->addUser3($manager);
        $this->addUser4($manager);
        $this->addUser5($manager);
        $manager->flush();
    }

    public function addUser1(ObjectManager $manager): void
    {
        //get role
        $role = $manager->getRepository('Entity\Role')->findOneBy(['name' => 'Administrateur']);
        //get Company
        $company = $manager->getRepository('Entity\Company')->findOneBy(['name' => 'Aubade']);
        $password = Auth::hashPassword('123456');

        //generate user
        $user = new User('Dorian', 'Breuillard', 'dorian.breuillard@gmail.com', $password, $role, 'Développeur', '0606060606', $company, true);

        $manager->persist($user);
    }

    //add second user
    public function addUser2(ObjectManager $manager): void
    {
        //get role
        $role = $manager->getRepository('Entity\Role')->findOneBy(['name' => 'Administrateur']);
        //get Company
        $company = $manager->getRepository('Entity\Company')->findOneBy(['name' => 'Cocorico']);
        $password = Auth::hashPassword('123456');

        //generate user
        $user = new User('John', 'Doe', 'jhondoe@gmail.com', $password, $role, 'Développeur', '0606060606', $company, true);

        $manager->persist($user);
    }

    //add third user
    public function addUser3(ObjectManager $manager): void
    {
        //get role
        $role = $manager->getRepository('Entity\Role')->findOneBy(['name' => 'Utilisateur']);
        //get Company
        $company = $manager->getRepository('Entity\Company')->findOneBy(['name' => 'Bubulle']);
        $password = Auth::hashPassword('123456');

        //generate user
        $user = new User('Jane', 'Doe', 'janedoe@gmail.com', $password, $role, 'Développeur', '0606060606', $company, true);

        $manager->persist($user);

    }


    public function addUser4(ObjectManager $manager): void
    {
        //get role
        $role = $manager->getRepository('Entity\Role')->findOneBy(['name' => 'Administrateur']);
        //get Company
        $company = $manager->getRepository('Entity\Company')->findOneBy(['name' => 'Aubade']);
        $password = Auth::hashPassword('123456');

        //generate user
        $user = new User('Macky', 'Tall', 'tallmacky001@gmail.com', $password, $role, 'Développeur', '0606060606', $company, true);

        $manager->persist($user);
    }

    public function addUser5(ObjectManager $manager): void
    {
        //get role
        $role = $manager->getRepository('Entity\Role')->findOneBy(['name' => 'Administrateur']);
        //get Company
        $company = $manager->getRepository('Entity\Company')->findOneBy(['name' => 'Aubade']);
        $password = Auth::hashPassword('123456');

        //generate user
        $user = new User('Clément', 'Pavot', 'clement@getinov.com', $password, $role, 'Développeur', '0606060606', $company, true);

        $manager->persist($user);
    }

    public function getOrder(): int
    {
        return 4;
    }
}