<?php

namespace Controller;

// controller for entity ProductFamily
use Doctrine\ORM\EntityManager;
use Entity\ProductFamily;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="ProductFamilyRequest",
 *     required={"name", "description"},
 *     @OA\Property(property="name", type="string", example="Salle de Bain"),
 *     @OA\Property(property="description", type="string", example="Catégorie regroupant tous les produits pour la salle de bain")
 * )
 *
 * @OA\Schema (
 *     schema="ProductFamilyResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Salle de Bain"),
 *     @OA\Property(property="description", type="string", example="Catégorie regroupant tous les produits pour la salle de bain"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-03-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-03-01 00:00:00")
 * )
 */
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

    /**
     * @OA\Post(
     *     path="/product-family",
     *     tags={"ProductFamily"},
     *     summary="Add a new ProductFamily",
     *     description="Add a new ProductFamily",
     *     @OA\RequestBody(
     *         required=true,
     *         description="ProductFamily object that needs to be added",
     *         @OA\JsonContent(ref="#/components/schemas/ProductFamilyRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="ProductFamily created",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="ProductFamily already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function addProductFamily(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this :
        // {
        //     "name": "Salle de Bain",
        //     "description": "Catégorie regroupant tous les produits pour la salle de bain"
        //  }

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

    /**
     * @OA\Get(
     *     path="/product-family/all",
     *     tags={"ProductFamily"},
     *     summary="Get all ProductFamilies",
     *     description="Get all ProductFamilies",
     *     @OA\Response(
     *         response=200,
     *         description="ProductFamilies found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ProductFamilyResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
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


    /**
     * @OA\Get(
     *     path="/product-family/{id}",
     *     tags={"ProductFamily"},
     *     summary="Get a ProductFamily by ID",
     *     description="Get a ProductFamily by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the ProductFamily to get",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ProductFamily found",
     *         @OA\JsonContent(ref="#/components/schemas/ProductFamilyResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="ProductFamily not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/product-family/{id}",
     *     tags={"ProductFamily"},
     *     summary="Update a ProductFamily by ID",
     *     description="Update a ProductFamily by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the ProductFamily to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="ProductFamily object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/ProductFamilyRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ProductFamily updated",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="ProductFamily not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="ProductFamily already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
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


    /**
     * @OA\Delete(
     *     path="/product-family/{id}",
     *     tags={"ProductFamily"},
     *     summary="Delete a ProductFamily by ID",
     *     description="Delete a ProductFamily by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the ProductFamily to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ProductFamily deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="ProductFamily not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
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