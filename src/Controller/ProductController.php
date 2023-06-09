<?php

declare(strict_types=1);

namespace Controller;

use Entity\Company;
use Entity\Product;
use Entity\ProductFamily;
use Entity\QuantityUnit;
use Entity\Supplier;
use Entity\Vat;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="ProductRequest",
 *     required={"name", "description", "buyPrice", "sellPrice", "quantity", "discount", "isDiscount", "productFamily", "vat", "company", "quantityUnit", "supplier"},
 *     @OA\Property(property="name", type="string", example="Jambon"),
 *     @OA\Property(property="description", type="string", example="Jambon de Paris"),
 *     @OA\Property(property="buyPrice", type="integer", example=10),
 *     @OA\Property(property="sellPrice", type="integer", example=15),
 *     @OA\Property(property="quantity", type="integer", example=10),
 *     @OA\Property(property="discount", type="integer", example=0),
 *     @OA\Property(property="isDiscount", type="boolean", example=false),
 *     @OA\Property(property="productFamily", type="integer", example=1),
 *     @OA\Property(property="vat", type="integer", example=1),
 *     @OA\Property(property="company", type="integer", example=1),
 *     @OA\Property(property="quantityUnit", type="integer", example=1),
 *     @OA\Property(property="supplier", type="integer", example=1)
 * )
 *
 * @OA\Schema (
 *     schema="ProductResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Jambon"),
 *     @OA\Property(property="description", type="string", example="Jambon de Paris"),
 *     @OA\Property(property="buyPrice", type="integer", example=10),
 *     @OA\Property(property="sellPrice", type="integer", example=15),
 *     @OA\Property(property="quantity", type="integer", example=10),
 *     @OA\Property(property="discount", type="integer", example=0),
 *     @OA\Property(property="isDiscount", type="boolean", example=false),
 *     @OA\Property(property="productFamily", type="object", ref="#/components/schemas/ProductFamilyResponse"),
 *     @OA\Property(property="vat", type="object", ref="#/components/schemas/VatResponse"),
 *     @OA\Property(property="company", type="object", ref="#/components/schemas/CompanyResponse"),
 *     @OA\Property(property="quantityUnit", type="object", ref="#/components/schemas/QuantityUnitResponse"),
 *     @OA\Property(property="supplier", type="integer", example=1),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-03-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-03-01 00:00:00")
 * )
 *
 */
class ProductController extends AbstractController
{

    private const REQUIRED_FIELDS = ['name', 'description', 'buyPrice', 'sellPrice', 'quantity', 'discount', 'isDiscount', 'productFamily', 'vat', 'company', 'quantityUnit', 'supplier'];
    private DAO $dao;
    private Request $request;

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
     *         @OA\JsonContent(ref="#/components/schemas/ProductRequest")
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
            $productFamilyObject = $this->dao->getOneBy(ProductFamily::class, ['id' => $productFamily]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // get the vat from the database by its id
        try {
            $vatObject = $this->dao->getOneBy(Vat::class, ['id' => $vat]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // get the company from the database by its id
        try {
            $companyObject = $this->dao->getOneBy(Company::class, ['id' => $company]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // get the quantity unit from the database by its id
        try {
            $quantityUnitObject = $this->dao->getOneBy(QuantityUnit::class, ['id' => $quantityUnit]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // get the supplier from the database by its id
        try {
            $supplierObject = $this->dao->getOneBy(Supplier::class, ['id' => $supplier]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // create a new product
        $product = new Product($name, $description, $buyPrice, $sellPrice, $quantity, $discount, $isDiscount, $productFamilyObject, $vatObject, $companyObject, $quantityUnitObject, $supplierObject);

        // add the product to the database
        try {
            $this->dao->add($product);
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
     *     tags={"Product"},
     *     summary="Get all products",
     *     description="Returns all products",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ProductResponse")
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
            $products = $this->dao->getAll(Product::class);
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
     *     path="/product/company/{companyId}",
     *     tags={"Product"},
     *     summary="Get a product by company",
     *     description="Returns a product by company",
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
     *         @OA\JsonContent(ref="#/components/schemas/ProductResponse")
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
    public function getProductsByCompany(int $companyId): void
    {
        // get all roles
        try {
            //get all products by company
            $products = $this->dao->getBy(Product::class, ['company' => $companyId]);
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
     *     tags={"Product"},
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
     *         @OA\JsonContent(ref="#/components/schemas/ProductResponse")
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
            $product = $this->dao->getOneBy(Product::class, ['id' => $id]);
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

    //getProductsByProductFamily
    /**
     * @OA\Get(
     *     path="/product/productFamily/{productFamilyId}",
     *     tags={"Product"},
     *     summary="Get a product by product family id",
     *     description="Returns a product by product family id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the product family to get products",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ProductResponse")
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
    public function getProductsByProductFamily(int $productFamilyId): void
    {
        // get all roles
        try {
            //get all products by company
            $products = $this->dao->getBy(Product::class, ['productFamily' => $productFamilyId]);

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
     * @OA\Put(
     *     path="/product/{id}",
     *     tags={"Product"},
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
     *         @OA\JsonContent(ref="#/components/schemas/ProductRequest")
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
            $product = $this->dao->getOneBy(Product::class, ['id' => $id]);
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
        $productFamily = $requestBody['productFamily'] ?? $product->getProductFamily();
        $vat = $requestBody['vat'] ?? $product->getVat();
        $company = $requestBody['company'] ?? $product->getCompany();
        $quantityUnit = $requestBody['quantityUnit'] ?? $product->getQuantityUnit();
        $supplier = $requestBody['supplier'] ?? $product->getSupplier();

        try {

            $productFamily = $this->dao->getOneBy(ProductFamily::class, ['id' => $productFamily->getId()]);
            $vat = $this->dao->getOneBy(Vat::class, ['id' => $vat->getId()]);
            $company = $this->dao->getOneBy(Company::class, ['id' => $company->getId()]);
            $quantityUnit = $this->dao->getOneBy(QuantityUnit::class, ['id' => $quantityUnit->getId()]);
            $supplier = $this->dao->getOneBy(Supplier::class, ['id' => $supplier->getId()]);

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
            $this->dao->update($product);
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
     *     tags={"Product"},
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
     *         description="Successful operation"
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
            $product = $this->dao->getOneBy(Product::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the product is not found
        if (!$product) {
            $this->request->handleErrorAndQuit(404, new Exception('Product not found'));
        }

        // remove the product
        try {
            $this->dao->delete($product);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Product deleted');
    }
}