<?php

namespace DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Entity\Invoice;
use Entity\InvoiceProduct;
use Entity\Product;
use Entity\Project;

class InvoiceFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $this->addInvoice1($manager);
        $this->addInvoice2($manager);
        $this->addInvoice3($manager);
        $manager->flush();
        $this->addProductToInvoice1($manager);
        $this->addProductToInvoice2($manager);
        $this->addProductToInvoice3($manager);
        $manager->flush();
    }

    public function addInvoice1(ObjectManager $manager): void
    {
        //get the projectStatus
        $project = $manager->getRepository(Project::class)->findOneBy(['name' =>  'Amandanas - Construction']);

        $estimate = new Invoice('Facture pour Amandanas', 'La facture conciste à construire le batiment pour Amandanas', $project);

        $manager->persist($estimate);
    }

    public function addInvoice2(ObjectManager $manager): void
    {
        //get the projectStatus
        $project = $manager->getRepository(Project::class)->findOneBy(['name' =>  'Redook - SDB']);

        $estimate = new Invoice('Facture pour Redook - SDB', 'La facture conciste à construire la salle de bain pour Redook', $project);

        $manager->persist($estimate);
    }

    public function addInvoice3(ObjectManager $manager): void
    {
        //get the projectStatus
        $project = $manager->getRepository(Project::class)->findOneBy(['name' =>  'Redook - Cuisine']);

        $estimate = new Invoice('Facture pour Redook - Cuisine', 'La facture conciste à construire la cuisine pour Redook', $project);

        $manager->persist($estimate);
    }

    //add Product to Invoice
 public function addProductToInvoice1(ObjectManager $manager): void
 {
        //get InvoiceStatus
        $estimate = $manager->getRepository(Invoice::class)->findOneBy(['name' => 'Facture pour Amandanas']);

        //get the projectStatus
        $product = $manager->getRepository(Product::class)->findOneBy(['name' =>  'Douche']);

        $estimateProduct = new InvoiceProduct($estimate, $product, 100);

        $manager->persist($estimateProduct);
 }

    public function addProductToInvoice2(ObjectManager $manager): void
    {
        //get InvoiceStatus
        $estimate = $manager->getRepository(Invoice::class)->findOneBy(['name' => 'Facture pour Redook - SDB']);

        //get the projectStatus
        $product = $manager->getRepository(Product::class)->findOneBy(['name' =>  'Baignoire']);

        $estimateProduct = new InvoiceProduct($estimate, $product, 100);

        $manager->persist($estimateProduct);
    }

    public function addProductToInvoice3(ObjectManager $manager): void
    {
        //get InvoiceStatus
        $estimate = $manager->getRepository(Invoice::class)->findOneBy(['name' => 'Facture pour Redook - Cuisine']);

        //get the projectStatus
        $product = $manager->getRepository(Product::class)->findOneBy(['name' =>  'Lavabo']);

        $estimateProduct = new InvoiceProduct($estimate, $product, 100);

        $manager->persist($estimateProduct);
    }



    public function getOrder(): int
    {
        return 8;
    }
}