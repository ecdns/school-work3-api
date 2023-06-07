<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Company;
use Entity\Customer;
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
        $this->addUserToProject1($manager);
        $this->addUserToProject2($manager);
    }


    public function addProject1(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);
        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'dorian.breuillard@gmail.com']);
        $projectStatus = $manager->getRepository(ProjectStatus::class)->findOneBy(['name' => 'Terminé']);
        $customer = $manager->getRepository(Customer::class)->findOneBy(['firstName' => 'Jean']);

        $project = new Project('Amandanas - Construction', 'Le projet conciste à construire le batiment pour Amandanas', $company, $user, $customer, $projectStatus);

        $manager->persist($project);

    }

    public function addProject2(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);
        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'dorian.breuillard@gmail.com']);
        $projectStatus = $manager->getRepository(ProjectStatus::class)->findOneBy(['name' => 'En cours']);
        $customer = $manager->getRepository(Customer::class)->findOneBy(['firstName' => 'Jean']);

        $project = new Project('Redook - SDB', 'Le projet conciste à construire la salle de bain pour Redook', $company, $user, $customer, $projectStatus);

        $manager->persist($project);

    }

    public function addProject3(ObjectManager $manager): void
    {
        $company = $manager->getRepository(Company::class)->findOneBy(['name' => 'Aubade']);
        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'dorian.breuillard@gmail.com']);
        $projectStatus = $manager->getRepository(ProjectStatus::class)->findOneBy(['name' => 'En cours']);
        $customer = $manager->getRepository(Customer::class)->findOneBy(['firstName' => 'Jean']);

        $project = new Project('Redook - Cuisine', 'Le projet conciste à construire la cuisine pour Redook', $company, $user, $customer, $projectStatus);

        $manager->persist($project);
    }

    public function addUserToProject1(ObjectManager $manager): void
    {
        $project = $manager->getRepository(Project::class)->findOneBy(['name' => 'Amandanas - Construction']);
        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'janedoe@gmail.com']);

        $project->addUser($user);

        $manager->flush($project);
    }

    public function addUserToProject2(ObjectManager $manager): void
    {
        $project = $manager->getRepository(Project::class)->findOneBy(['name' => 'Amandanas - Construction']);
        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'jhondoe@gmail.com']);

        $project->addUser($user);

        $manager->flush($project);
    }

    public function getOrder(): int
    {
        return 5;
    }
}