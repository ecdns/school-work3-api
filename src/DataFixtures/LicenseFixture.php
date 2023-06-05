<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\License;

class LicenseFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addLicense1($manager);
        $this->addLicense2($manager);
        $this->addLicense3($manager);
        $manager->flush();
    }

    public function addLicense1(ObjectManager $manager): void
    {
        $user = new License('premium', 'Une licence premium', 100, 10, 365);
        $manager->persist($user);
    }

    public function addLicense2(ObjectManager $manager): void
    {
        $user = new License('standard', 'Une licence standard', 50, 5, 365);
        $manager->persist($user);
    }

    public function addLicense3(ObjectManager $manager): void
    {
        $user = new License('basic', 'Une licence basic', 25, 2, 365);
        $manager->persist($user);
    }

    public function getOrder(): int
    {
        return 1;
    }
}