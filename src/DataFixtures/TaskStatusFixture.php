<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\TaskStatus;

class TaskStatusFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addTaskStatus1($manager);
        $this->addTaskStatus2($manager);
        $this->addTaskStatus3($manager);
        $this->addTaskStatus4($manager);
        $this->addTaskStatus5($manager);
        $manager->flush();
    }

    public function addTaskStatus1(ObjectManager $manager): void
    {
        //generate user
        $user = new TaskStatus('Terminé', 'La tâche est terminée');

        $manager->persist($user);
    }

    public function addTaskStatus2(ObjectManager $manager): void
    {
        //generate user
        $user = new TaskStatus('En cours', 'La tâche est en cours');

        $manager->persist($user);
    }

    public function addTaskStatus3(ObjectManager $manager): void
    {
        //generate user
        $user = new TaskStatus('A faire', 'La tâche est à faire');

        $manager->persist($user);
    }

    public function addTaskStatus4(ObjectManager $manager): void
    {
        //generate user
        $user = new TaskStatus('En attente', 'La tâche est en attente');

        $manager->persist($user);
    }

    public function addTaskStatus5(ObjectManager $manager): void
    {
        //generate user
        $user = new TaskStatus('Annulé', 'La tâche est annulée');

        $manager->persist($user);
    }

    public function getOrder(): int
    {
        return 7;
    }
}