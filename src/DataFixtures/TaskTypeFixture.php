<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\TaskStatus;
use Entity\TaskType;

class TaskTypeFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addTaskType1($manager);
        $this->addTaskType2($manager);
        $this->addTaskType3($manager);
        $this->addTaskType4($manager);
        $this->addTaskType5($manager);
        $manager->flush();
    }

    public function addTaskType1(ObjectManager $manager): void
    {
        //generate user
        $taskType = new TaskType('Appel', 'Un appel téléphonique');

        $manager->persist($taskType);
    }

    public function addTaskType2(ObjectManager $manager): void
    {
        //generate user
        $taskType = new TaskType('Email', 'Un email');

        $manager->persist($taskType);
    }

    public function addTaskType3(ObjectManager $manager): void
    {
        //generate user
        $taskType = new TaskType('Rendez-vous', 'Un rendez-vous');

        $manager->persist($taskType);
    }

    public function addTaskType4(ObjectManager $manager): void
    {
        //generate user
        $taskType = new TaskType('Déplacement', 'Un déplacement');

        $manager->persist($taskType);
    }

    public function addTaskType5(ObjectManager $manager): void
    {
        //generate user
        $taskType = new TaskType('Autre', 'Autre');

        $manager->persist($taskType);
    }


    public function getOrder(): int
    {
        return 7;
    }
}