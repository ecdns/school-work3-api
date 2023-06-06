<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Estimate;
use Entity\EstimateStatus;

class EstimateStatusFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addEstimateStatus1($manager);
        $this->addEstimateStatus2($manager);
        $this->addEstimateStatus3($manager);
        $this->addEstimateStatus4($manager);
        $this->addEstimateStatus5($manager);
        $manager->flush();
    }

    public function addEstimateStatus1(ObjectManager $manager): void
    {

        $company = new EstimateStatus('Brouillon', 'Le devis est en cours de rédaction');

        $manager->persist($company);
    }

    public function addEstimateStatus2(ObjectManager $manager): void
    {

        $company = new EstimateStatus('Envoyé', 'Le devis a été envoyé au client');

        $manager->persist($company);
    }

    public function addEstimateStatus3(ObjectManager $manager): void
    {

        $company = new EstimateStatus('Validé', 'Le devis a été validé par le client');

        $manager->persist($company);
    }

    public function addEstimateStatus4(ObjectManager $manager): void
    {

        $company = new EstimateStatus('Refusé', 'Le devis a été refusé par le client');

        $manager->persist($company);
    }

    public function addEstimateStatus5(ObjectManager $manager): void
    {

        $company = new EstimateStatus('Annulé', 'Le devis a été annulé');

        $manager->persist($company);
    }









    public function getOrder(): int
    {
        return 3;
    }
}