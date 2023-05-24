<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\Product;
use Entity\ProductFamily;
use Entity\QuantityUnit;
use Entity\Role;
use Entity\Supplier;
use Entity\Vat;
use Exception;
use Service\DAO;
use Service\Request;

class ProductController extends AbstractController
{
    
    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name', 'description', 'buyPrice', 'sellPrice', 'quantity', 'discount', 'isDiscount', 'productFamily', 'vat', 'company', 'quantityUnit', 'supplier'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *     path="/product",
     *     tags={"Product"},
     *     summary="Add a new product",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Product created",
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request data",
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="Product already exists",
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error",
     *     )
     * )
     */
    public function addProduct(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//         {
//             "name": "Jambon",
//             "description": "Jambon de Paris",
//             "buyPrice": 10,
//             "sellPrice": 15,
//             "quantity": 10,
//             "discount": 0,
//             "isDiscount": false,
//             "productFamily": 1,
//             "vat": 1,
//             "company": 1,
//             "quantityUnit": 1,
//             "supplier": 1
//         }


        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }


        // get the product data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];
        $buyPrice = $requestBody['buyPrice'];
        $sellPrice = $requestBody['sellPrice'];
        $quantity = $requestBody['quantity'];
        $discount = $requestBody['discount'];
        $isDiscount = $requestBody['isDiscount'];
        $productFamily = $requestBody['productFamily'];
        $vat = $requestBody['vat'];
        $company = $requestBody['company'];
        $quantityUnit = $requestBody['quantityUnit'];
        $supplier = $requestBody['supplier'];


        // get the product family from the database by its id
        try {
            $productFamilyObject = $this->dao->getOneEntityBy(ProductFamily::class, ['id' => $productFamily]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // get the vat from the database by its id
        try {
            $vatObject = $this->dao->getOneEntityBy(Vat::class, ['id' => $vat]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // get the company from the database by its id
        try {
            $companyObject = $this->dao->getOneEntityBy(Company::class, ['id' => $company]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // get the quantity unit from the database by its id
        try {
            $quantityUnitObject = $this->dao->getOneEntityBy(QuantityUnit::class, ['id' => $quantityUnit]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // get the supplier from the database by its id
        try {
            $supplierObject = $this->dao->getOneEntityBy(Supplier::class, ['id' => $supplier]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // create a new product
        $product = new Product($name, $description, $buyPrice, $sellPrice, $quantity, $discount, $isDiscount, $productFamilyObject, $vatObject, $companyObject, $quantityUnitObject, $supplierObject);

        // add the product to the database
        try {
            $this->dao->addEntity($product);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Product already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'Product created');
    }


    /**
     * @OA\Get(
     *     path="/product/all",
     *     tags={"Products"},
     *     summary="Get all products",
     *     description="Returns all products",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function getProducts(): void
    {
        // get all roles
        try {
            //get all products by company
            $products = $this->dao->getAllEntities(Product::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($products as $product) {
            $response[] = $product->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Products found', $response);
    }



    /**
     * @OA\Get(
     *     path="/product/{id}",
     *     tags={"Products"},
     *     summary="Get a product by id",
     *     description="Returns a product by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the product to get",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function getProductsByCompany(int $id): void
    {
        // get all roles
        try {
            //get all products by company
            $products = $this->dao->getEntitiesBy(Product::class, ['company' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($products as $product) {
            $response[] = $product->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Products found', $response);
    }


    /**
     * @OA\Get(
     *     path="/product/{id}",
     *     tags={"Products"},
     *     summary="Get a product by id",
     *     description="Returns a product by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the product to get",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function getProductById(int $id): void
    {
        // get the role by id
        try {
            $product = $this->dao->getOneEntityBy(Product::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$product) {
            $this->request->handleErrorAndQuit(404, new Exception('Product not found'));
        }

        // set the response
        $response = $product->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Product found', $response);
    }

    /**
     * @OA\Put(
     *     path="/product/{id}",
     *     tags={"Products"},
     *     summary="Update a product by id",
     *     description="Updates a product by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the product to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Product object that needs to be updated",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product family, vat, company, quantity unit or supplier not found",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function updateProduct(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the product by id
        try {
            $product = $this->dao->getOneEntityBy(Product::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the product is not found
        if (!$product) {
            $this->request->handleErrorAndQuit(404, new Exception('Product not found'));
        }

        // it will look like this:
        // {
        //     "name": "Jambon",
        //     "description": "Jambon de Paris",
        //     "buyPrice": 10,
        //     "sellPrice": 15,
        //     "quantity": 10,
        //     "discount": 0,
        //     "isDiscount": false,
        //     "productFamily": 1,
        //     "vat": 1,
        //     "company": 1,
        //     "quantityUnit": 1,
        //     "supplier": 1
        // }


        // get the product data from the request body
        $name = $requestBody['name'] ?? $product->getName();
        $description = $requestBody['description'] ?? $product->getDescription();
        $buyPrice = $requestBody['buyPrice'] ?? $product->getBuyPrice();
        $sellPrice = $requestBody['sellPrice'] ?? $product->getSellPrice();
        $quantity = $requestBody['quantity'] ?? $product->getQuantity();
        $discount = $requestBody['discount'] ?? $product->getDiscount();
        $isDiscount = $requestBody['isDiscount'] ?? $product->getIsDiscount();
        $productFamily = $requestBody['productFamily'] ?? $product->getProductFamily()->getId();
        $vat = $requestBody['vat'] ?? $product->getVat()->getId();
        $company = $requestBody['company'] ?? $product->getCompany()->getId();
        $quantityUnit = $requestBody['quantityUnit'] ?? $product->getQuantityUnit()->getId();
        $supplier = $requestBody['supplier'] ?? $product->getSupplier()->getId();

        try {

            $productFamily = $this->dao->getOneEntityBy(ProductFamily::class, ['id' => $productFamily]);
            $vat = $this->dao->getOneEntityBy(Vat::class, ['id' => $vat]);
            $company = $this->dao->getOneEntityBy(Company::class, ['id' => $company]);
            $quantityUnit = $this->dao->getOneEntityBy(QuantityUnit::class, ['id' => $quantityUnit]);
            $supplier = $this->dao->getOneEntityBy(Supplier::class, ['id' => $supplier]);

            if (!$productFamily || !$vat || !$company || !$quantityUnit || !$supplier) {
                $this->request->handleErrorAndQuit(404, new Exception('Product family, vat, company, quantity unit or supplier not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // update the product
        $product->setName($name);
        $product->setDescription($description);
        $product->setBuyPrice($buyPrice);
        $product->setSellPrice($sellPrice);
        $product->setQuantity($quantity);
        $product->setDiscount($discount);
        $product->setIsDiscount($isDiscount);
        $product->setProductFamily($productFamily);
        $product->setVat($vat);
        $product->setCompany($company);
        $product->setQuantityUnit($quantityUnit);
        $product->setSupplier($supplier);

        // update the product in the database
        try {
            $this->dao->updateEntity($product);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Product already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Product updated');

    }

    /**
     * @OA\Delete(
     *     path="/product/{id}",
     *     tags={"Products"},
     *     summary="Delete a product by id",
     *     description="Deletes a product by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the product to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *     )
     * )
     */
    public function deleteProduct(int $id): void
    {
        // get the product by id
        try {
            $product = $this->dao->getOneEntityBy(Product::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the product is not found
        if (!$product) {
            $this->request->handleErrorAndQuit(404, new Exception('Product not found'));
        }

        // remove the product
        try {
            $this->dao->deleteEntity($product);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Product deleted');
    }
}