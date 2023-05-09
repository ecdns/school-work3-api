<?php

namespace Controller;

// controller for entity ProductFamily
use Doctrine\ORM\EntityManager;
use Entity\ProductFamily;
use Exception;
use Service\Request;

class ProductFamilyController extends AbstractController
{

    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    //function for adding a new productFamily
    const REQUIRED_FIELDS = ['name', 'description'];

    public function addProductFamily(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "Salle de Bain",
        //     "description": "Catégorie regroupant tous les produits pour la salle de bain"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the ProductFamily data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];

        // create a new productFamily
        $productFamily = new ProductFamily($name, $description);

        // persist the productFamily
        try {
            $this->entityManager->persist($productFamily);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(409, new Exception('ProductFamily already exists'));
            }
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(201, 'ProductFamily created');

    }

    //function for getting all ProductFamily
    public function getProductFamilies(): void
    {
        // get all the ProductFamily from the database
        try {
            $productFamilies = $this->entityManager->getRepository(ProductFamily::class)->findAll();
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($productFamilies as $productFamily) {
            $response[] = $productFamily->toArray();
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'ProductFamily found', $response);
    }

    public function getProductFamilyById(int $id): void
    {
        // get the license from the database by its id
        try {
            $productFamily = $this->entityManager->getRepository(ProductFamily::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$productFamily) {
            Request::handleErrorAndQuit(404, new Exception('ProductFamily not found'));
        }

        // set the response
        $response = $productFamily->toArray();

        // handle the response
        Request::handleSuccessAndQuit(200, 'ProductFamily found', $response);
    }

    //function for updating a productFamily
    public function updateProductFamily(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "Salle de Bain",
        //     "description": "Catégorie regroupant tous les produits pour la salle de bain"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the ProductFamily data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];

        // get the ProductFamily from the database by its id
        try {
            $productFamily = $this->entityManager->getRepository(ProductFamily::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the productFamily is not found
        if (!$productFamily) {
            Request::handleErrorAndQuit(404, new Exception('ProductFamily not found'));
        }

        // update the productFamily
        $productFamily->setName($name);
        $productFamily->setDescription($description);

        // persist the productFamily
        try {
            $this->entityManager->persist($productFamily);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(409, new Exception('ProductFamily already exists'));
            }
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'ProductFamily updated');

    }

    //function for deleting a ProductFamily
    public function deleteProductFamily(int $id): void
    {
        // get the ProductFamily from the database by its id
        try {
            $productFamily = $this->entityManager->getRepository(ProductFamily::class)->find($id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the ProductFamily is not found
        if (!$productFamily) {
            Request::handleErrorAndQuit(404, new Exception('ProductFamily not found'));
        }

        // remove the ProductFamily
        try {
            $this->entityManager->remove($productFamily);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'ProductFamily deleted');
    }


}