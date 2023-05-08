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
use Service\Request;

class ProductController extends AbstractController
{
    private EntityManager $entityManager;
    private const REQUIRED_FIELDS = ['name', 'description', 'buyPrice', 'sellPrice', 'quantity', 'discount', 'isDiscount', 'productFamily', 'vat', 'company', 'quantityUnit', 'supplier'];

        public function __construct(EntityManager $entityManager)
        {
            $this->entityManager = $entityManager;
        }

    public function addProduct(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

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


        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
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


        // create a new product
        $product = new Product($name, $description, $buyPrice, $sellPrice, $quantity, $discount, $isDiscount, $productFamily, $vat, $company, $quantityUnit, $supplier);

        // persist the role
        try {
            $this->entityManager->persist($product);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(409, new Exception('Product already exists'));
            }
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(201, 'Product created');
    }

    public function getProductByCompany(int $id): void
    {
        // get all roles
        try {
            //get all products by company
            $products = $this->entityManager->getRepository(Product::class)->findBy(['company' => $id]);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($products as $product) {
            $response[] = $product->toArray();
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'Products found', $response);
    }

    public function getProductById(int $id): void
    {
        // get the role by id
        try {
            $product = $this->entityManager->find(Product::class, $id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$product) {
            Request::handleErrorAndQuit(404, new Exception('Product not found'));
        }

        // set the response
        $response = $product->toArray();

        // handle the response
        Request::handleSuccessAndQuit(200, 'Product found', $response);
    }

    public function updateProduct(int $id): void
    {
        // get the role by id
        try {
            $product = $this->entityManager->find(Product::class, $id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$product) {
            Request::handleErrorAndQuit(404, new Exception('Product not found'));
        }

        // get the request body
        $requestBody = file_get_contents('php://input');

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validatePutData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
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

        // decode the json
        $requestBody = json_decode($requestBody, true);

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



        // update the product
        $product->setName($name ?? $product->getName());
        $product->setDescription($description ?? $product->getDescription());
        $product->setBuyPrice($buyPrice ?? $product->getBuyPrice());
        $product->setSellPrice($sellPrice ?? $product->getSellPrice());
        $product->setQuantity($quantity ?? $product->getQuantity());
        $product->setDiscount($discount ?? $product->getDiscount());
        $product->setIsDiscount($isDiscount ?? $product->getIsDiscount());
        $product->setProductFamily($productFamily ?? $product->getProductFamily());
        $product->setVat($vat ?? $product->getVat());
        $product->setCompany($company ?? $product->getCompany());
        $product->setQuantityUnit($quantityUnit ?? $product->getQuantityUnit());
        $product->setSupplier($supplier ?? $product->getSupplier());

        // persist the role
        try {
            $this->entityManager->persist($product);
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
        Request::handleSuccessAndQuit(200, 'Product updated');

    }

    public function deleteProduct(int $id): void
    {
        // get the product by id
        try {
            $product = $this->entityManager->find(Product::class, $id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the product is not found
        if (!$product) {
            Request::handleErrorAndQuit(404, new Exception('Product not found'));
        }

        // remove the product
        try {
            $this->entityManager->remove($product);
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
        Request::handleSuccessAndQuit(200, 'Product deleted');
    }
}