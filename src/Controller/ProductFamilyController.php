<?php

namespace Controller;

// controller for entity ProductFamily
use Doctrine\ORM\EntityManager;
use Entity\ProductFamily;
use Exception;
use Service\DAO;
use Service\Request;

class ProductFamilyController extends AbstractController
{

    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name', 'description'];
    
    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

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
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the ProductFamily data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];

        // create a new productFamily
        $productFamily = new ProductFamily($name, $description);

        // persist the productFamily
        try {
            $this->dao->addEntity($productFamily);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('ProductFamily already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'ProductFamily created');

    }

    //function for getting all ProductFamily
    public function getProductFamilies(): void
    {
        // get all the ProductFamily from the database
        try {
            $productFamilies = $this->dao->getAllEntities(ProductFamily::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($productFamilies as $productFamily) {
            $response[] = $productFamily->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'ProductFamily found', $response);
    }

    public function getProductFamilyById(int $id): void
    {
        // get the license from the database by its id
        try {
            $productFamily = $this->dao->getOneEntityBy(ProductFamily::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$productFamily) {
            $this->request->handleErrorAndQuit(404, new Exception('ProductFamily not found'));
        }

        // set the response
        $response = $productFamily->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'ProductFamily found', $response);
    }

    //function for updating a productFamily
    public function updateProductFamily(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this :
        // {
        //     "name": "Salle de Bain",
        //     "description" : "Catégorie regroupant tous les produits pour la salle de bain"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the ProductFamily from the database by its id
        try {
            $productFamily = $this->dao->getOneEntityBy(ProductFamily::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the productFamily is not found
        if (!$productFamily) {
            $this->request->handleErrorAndQuit(404, new Exception('ProductFamily not found'));
        }

        // get the ProductFamily data from the request body
        $name = $requestBody['name'] ?? $productFamily->getName();
        $description = $requestBody['description'] ?? $productFamily->getDescription();

        // update the productFamily
        $productFamily->setName($name);
        $productFamily->setDescription($description);

        // persist the productFamily
        try {
            $this->dao->updateEntity($productFamily);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('ProductFamily already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'ProductFamily updated');

    }

    //function for deleting a ProductFamily
    public function deleteProductFamily(int $id): void
    {
        // get the ProductFamily from the database by its id
        try {
            $productFamily = $this->dao->getOneEntityBy(ProductFamily::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the ProductFamily is not found
        if (!$productFamily) {
            $this->request->handleErrorAndQuit(404, new Exception('ProductFamily not found'));
        }

        // remove the ProductFamily
        try {
            $this->dao->deleteEntity($productFamily);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'ProductFamily deleted');
    }

}