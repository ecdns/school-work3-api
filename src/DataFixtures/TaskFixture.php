<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Task;

class TaskFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addTask1($manager);
        $this->addTask2($manager);
        $this->addTask3($manager);
        $manager->flush();
    }

    public function addTask1(ObjectManager $manager): void
    {
        //get user
        $user = $manager->getRepository('Entity\User')->findOneBy(['email' => 'dorian.breuillard@gmail.com']);

        //get Project
        $project = $manager->getRepository('Entity\Project')->findOneBy(['name' => 'Amandanas - Construction']);

//get TaskStatus
        $taskStatus = $manager->getRepository('Entity\TaskStatus')->findOneBy(['name' => 'En cours']);

        //get TaskType
        $taskType = $manager->getRepository('Entity\TaskType')->findOneBy(['name' => 'Autre']);


        //generate user
        $task = new Task('Faire un cadeau a greg', 'Appeler coline pour savoir ce que greg veut pour son anniversaire', 'Bureau', '2021-05-20', $project, $user, $taskStatus, $taskType);

        $manager->persist($task);
    }

    public function addTask2(ObjectManager $manager): void
    {
        //get user
        $user = $manager->getRepository('Entity\User')->findOneBy(['email' => 'clement@getinov.com']);

        //get Project
        $project = $manager->getRepository('Entity\Project')->findOneBy(['name' => 'Amandanas - Construction']);

//get TaskStatus
        $taskStatus = $manager->getRepository('Entity\TaskStatus')->findOneBy(['name' => 'Annulé']);

        //get TaskType
        $taskType = $manager->getRepository('Entity\TaskType')->findOneBy(['name' => 'Appel']);

        //generate user
        $task = new Task('Ranger les courses', 'Jaaj', 'Bureau', '2021-05-20', $project, $user, $taskStatus, $taskType);

        $manager->persist($task);
    }

    public function addTask3(ObjectManager $manager): void
    {
        //get user
        $user = $manager->getRepository('Entity\User')->findOneBy(['email' => 'tallmacky001@gmail.com']);
        
        //get Project
        $project = $manager->getRepository('Entity\Project')->findOneBy(['name' => 'Amandanas - Construction']);

        //get TaskStatus
        $taskStatus = $manager->getRepository('Entity\TaskStatus')->findOneBy(['name' => 'A faire']);

        //get TaskType
        $taskType = $manager->getRepository('Entity\TaskType')->findOneBy(['name' => 'Email']);

        //generate user
        $task = new Task('Faire un mail a marjo', 'Envoyer un mail  marjo', 'Bureau', '2021-05-20', $project, $user, $taskStatus, $taskType);

        $manager->persist($task);

    }

    public function getOrder(): int
    {
        return 8;
    }
}