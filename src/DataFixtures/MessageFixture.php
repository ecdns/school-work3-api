<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Message;

class MessageFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addMessage1($manager);
        $this->addMessage2($manager);
        $this->addMessage3($manager);
        $manager->flush();
    }

    public function addMessage1(ObjectManager $manager): void
    {
        $user = $manager->getRepository('Entity\User')->findOneBy(['email' => 'dorian.breuillard@gmail.com']);

        $project = $manager->getRepository('Entity\Project')->findOneBy(['name' => 'Amandanas - Construction']);

        $message = new Message($user, $project, 'Coucou les loulous');

        $manager->persist($message);
    }

    public function addMessage2(ObjectManager $manager): void
    {
        $user = $manager->getRepository('Entity\User')->findOneBy(['email' => 'tallmacky001@gmail.com']);

        $project = $manager->getRepository('Entity\Project')->findOneBy(['name' => 'Amandanas - Construction']);

        $message = new Message($user, $project, 'Coucou les potes');

        $manager->persist($message);

    }

    public function addMessage3(ObjectManager $manager): void
    {
        $user = $manager->getRepository('Entity\User')->findOneBy(['email' => 'clement@getinov.com']);

        $project = $manager->getRepository('Entity\Project')->findOneBy(['name' => 'Amandanas - Construction']);

        $message = new Message($user, $project, 'Coucou les zozos');

        $manager->persist($message);

    }

    public function getOrder(): int
    {
        return 7;
    }
}