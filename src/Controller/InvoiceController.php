<?php

declare(strict_types=1);

namespace Controller;

use Entity\Invoice;
use Entity\InvoiceProduct;
use Entity\Product;
use Entity\Project;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="InvoiceRequest",
 *     required={"name", "description", "project"},
 *     @OA\Property(property="name", type="string", example="Invoice 1"),
 *     @OA\Property(property="description", type="string", example="This is the first invoice"),
 *     @OA\Property(property="project", type="integer", example=1)
 * )
 *
 * @OA\Schema (
 *     schema="InvoiceResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Invoice 1"),
 *     @OA\Property(property="description", type="string", example="This is the first invoice"),
 *     @OA\Property(property="project", type="object", ref="#/components/schemas/ProjectResponse"),
 *     @OA\Property(property="products", type="array", @OA\Items(ref="#/components/schemas/InvoiceProductResponse")),
 *     @OA\Property(property="createdAt", type="string", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", example="2021-01-01 00:00:00")
 * )
 *
 * @OA\Schema (
 *     schema="InvoiceProductRequest",
 *     required={"invoice", "product", "quantity"},
 *     @OA\Property(property="invoice", type="integer", example=1),
 *     @OA\Property(property="product", type="integer", example=1),
 *     @OA\Property(property="quantity", type="integer", example=1)
 * )
 *
 * @OA\Schema (
 *     schema="InvoiceProductResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="invoice", type="object", ref="#/components/schemas/InvoiceResponse"),
 *     @OA\Property(property="product", type="object", ref="#/components/schemas/ProductResponse"),
 *     @OA\Property(property="quantity", type="integer", example=1),
 *     @OA\Property(property="createdAt", type="string", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", example="2021-01-01 00:00:00")
 * )
 *
 *
 */
class InvoiceController extends AbstractController
{

    private const REQUIRED_FIELDS = ['name', 'description', 'project'];
    private DAO $dao;
    private Request $request;

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }


    /**
     * @OA\Post(
     *     path="/invoice",
     *     tags={"Invoice"},
     *     summary="Create a new invoice",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Invoice data",
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceRequest")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Invoice created"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Project not found"
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="Invoice already exists"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function addInvoice(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//                 {
//                     "name": "Invoice 1",
//                     "description": "This is the first invoice",
//                     "project": 1
//                 }


        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }


        // get the invoice data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];
        $project = $requestBody['project'];

        // get the invoice from the database by its id
        try {
            $projectObject = $this->dao->getOneBy(Project::class, ['id' => $project]);


            if (!$projectObject) {
                $this->request->handleErrorAndQuit(404, new Exception('Project not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // create a new invoice
        $invoice = new Invoice($name, $description, $projectObject);

        // add the invoice to the database
        try {
            $this->dao->add($invoice);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Invoice already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'Invoice created');
    }


    /**
     * @OA\Get(
     *     path="/invoice",
     *     tags={"Invoice"},
     *     summary="Get all invoices",
     *     @OA\Response(
     *         response="200",
     *         description="Invoices found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/InvoiceResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getInvoices(): void
    {
        // get all roles
        try {
            //get all invoices
            $invoices = $this->dao->getAll(Invoice::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($invoices as $invoice) {
            $response[] = $invoice->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Invoices found', $response);
    }


    /**
     * @OA\Get(
     *     path="/invoice/project/{id}",
     *     tags={"Invoice"},
     *     summary="Get all invoices by project",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Project ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Invoices found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/InvoiceResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getInvoicesByProject(int $projectId): void
    {
        // get all roles
        try {
            //get all invoices by company
            $invoices = $this->dao->getBy(Invoice::class, ['project' => $projectId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($invoices as $invoice) {
            $response[] = $invoice->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Invoices found', $response);
    }


    /**
     * @OA\Get(
     *     path="/invoice/{id}",
     *     tags={"Invoice"},
     *     summary="Get invoice by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Invoice ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Invoice found",
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Invoice not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getInvoiceById(int $id): void
    {
        // get the role by id
        try {
            $invoice = $this->dao->getOneBy(Invoice::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$invoice) {
            $this->request->handleErrorAndQuit(404, new Exception('Invoice not found'));
        }

        // set the response
        $response = $invoice->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Invoice found', $response);
    }

    // getInvoicesByCompany
    /**
     * @OA\Get(
     *     path="/invoice/company/{companyId}",
     *     tags={"Invoice"},
     *     summary="Get all invoices by company",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Company ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Invoices found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/InvoiceResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getInvoicesByCompany(int $companyId): void
    {
        try {
            $invoices = $this->dao->getBy(Invoice::class, ['company' => $companyId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        $response = [];
        foreach ($invoices as $invoice) {
            $response[] = $invoice->toArray();
        }

        $this->request->handleSuccessAndQuit(200, 'Invoices found', $response);
    }

    /**
     * @OA\Get(
     *     path="/invoice/customer/{customerId}",
     *     tags={"Invoice"},
     *     summary="Get all invoices by customer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Customer ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Invoices found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/InvoiceResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getInvoicesByCustomer(int $customerId): void
    {
        // get all invoices by customer
        try {
           // get all projects of the customer
            $projects = $this->dao->getBy(Project::class, ['customer' => $customerId]);
            $invoices = [];
            foreach ($projects as $project) {
                $invoices = array_merge($invoices, $this->dao->getBy(Invoice::class, ['project' => $project->getId()]));
            }
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($invoices as $invoice) {
            $response[] = $invoice->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Invoices found', $response);
    }

    /**
    * @OA\Get(
    *     path="/invoice/totalAmount/{invoiceId}",
    *     tags={"Invoice"},
    *     summary="Get total amount of an invoice",
    *     @OA\Parameter(
    *         name="invoiceId",
    *         in="path",
    *         description="ID of the invoice",
    *         required=true,
    *         @OA\Schema(
    *             type="integer"
    *         )
    *     ),
    *     @OA\Response(
    *         response="200",
    *         description="Invoice total amount found",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(
    *                 property="totalAmount",
    *                 type="number",
    *                 format="float"
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response="404",
    *         description="Invoice not found"
    *     ),
    *     @OA\Response(
    *         response="500",
    *         description="Internal server error"
    *     )
    * )
    */
    public function getTotalAmount(int $invoiceId): void
    {
        // get the invoice
        try {
            $invoice = $this->dao->getOneBy(Invoice::class, ['id' => $invoiceId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the invoice is not found
        if (!$invoice) {
            $this->request->handleErrorAndQuit(404, new Exception('Invoice not found'));
        }

        $response = $invoice->getTotalAmount();

        // build the json response
        $response = [
            'totalAmount' => $response
        ];

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Invoice total amount found', $response);
    }

    /**
    * @OA\Get(
    *     path="/invoice/totalAmountWithVat/{invoiceId}",
    *     tags={"Invoice"},
    *     summary="Get total amount with VAT of an invoice",
    *     @OA\Parameter(
    *         name="invoiceId",
    *         in="path",
    *         description="ID of the invoice",
    *         required=true,
    *         @OA\Schema(
    *             type="integer"
    *         )
    *     ),
    *     @OA\Response(
    *         response="200",
    *         description="Invoice total amount with VAT found",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(
    *                 property="totalAmountWithVat",
    *                 type="number",
    *                 format="float"
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response="404",
    *         description="Invoice not found"
    *     ),
    *     @OA\Response(
    *         response="500",
    *         description="Internal server error"
    *     )
    * )
    */
    public function getTotalAmountWithVat(int $invoiceId): void
    {
        // get the invoice
        try {
            $invoice = $this->dao->getOneBy(Invoice::class, ['id' => $invoiceId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the invoice is not found
        if (!$invoice) {
            $this->request->handleErrorAndQuit(404, new Exception('Invoice not found'));
        }

        $response = $invoice->getTotalAmountWithVat();

        // build the json response
        $response = [
            'totalAmountWithVat' => $response
        ];

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Invoice total amount with vat found', $response);
    }

    /**
     * @OA\Put(
     *     path="/invoice/{id}",
     *     tags={"Invoice"},
     *     summary="Update invoice by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Invoice ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/InvoiceRequest")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Invoice updated"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Invoice not found"
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="Invoice already exists"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function updateInvoice(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the invoice by id
        try {
            $invoice = $this->dao->getOneBy(Invoice::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the invoice is not found
        if (!$invoice) {
            $this->request->handleErrorAndQuit(404, new Exception('Invoice not found'));
        }

        // it will look like this:
        //         {
        //             "name": "Invoice 1",
        //             "description": "This is the first invoice",
        //             "project": 1,
        //             "expiredAt": "2021-09-30",
        //             "invoiceStatus": 1
        //         }


        // get the invoice data from the request body
        $name = $requestBody['name'] ?? $invoice->getName();
        $description = $requestBody['description'] ?? $invoice->getDescription();
        $project = $requestBody['project'] ?? $invoice->getProject()->getId();
        try {

            $projectObject = $this->dao->getOneBy(Project::class, ['id' => $project]);


            if (!$projectObject) {
                $this->request->handleErrorAndQuit(404, new Exception('InvoiceStatus, Project or ExpiredDate not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // update the invoice
        $invoice->setName($name);
        $invoice->setDescription($description);
        $invoice->setProject($projectObject);


        // update the invoice in the database
        try {
            $this->dao->update($invoice);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Invoice already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Invoice updated');

    }


    /**
     * @OA\Post(
     *     path="/invoice/{id}/product/{productId}",
     *     tags={"InvoiceProduct"},
     *     summary="Add products to an invoice",
     *     description="Add products to an invoice",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the invoice to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="ID of the product to add",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Product added to Invoice"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Invoice or Product not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function addProductsToInvoice(int $invoiceId, int $productId): void
    {

        // get the invoice by id
        try {
            $invoice = $this->dao->getOneBy(Invoice::class, ['id' => $invoiceId]);

            $product = $this->dao->getOneBy(Product::class, ['id' => $productId]);

            //if the invoice is not found
            if (!$invoice || !$product) {
                $this->request->handleErrorAndQuit(404, new Exception('Invoice or Product not found'));
            }
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        //get InvoiceProduct by invoice and product
        try {
            $invoiceProduct = $this->dao->getOneBy(InvoiceProduct::class, ['invoice' => $invoice, 'product' => $product]);
            if ($invoiceProduct == null) {
                $invoiceProduct = new InvoiceProduct($invoice, $product, 1);
                $this->dao->add($invoiceProduct);
                $invoice->addInvoiceProduct($invoiceProduct);
                $this->dao->update($invoice);
            } else {
                $invoiceProduct->setQuantity($invoiceProduct->getQuantity() + 1);
                $this->dao->update($invoiceProduct);
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        $this->request->handleSuccessAndQuit(200, 'Product added to Invoice');
    }


    /**
     * @OA\Delete(
     *     path="/invoice/{id}/product/{productId}",
     *     tags={"InvoiceProduct"},
     *     summary="Remove products from an invoice",
     *     description="Remove products from an invoice",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the invoice to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="ID of the product to remove",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Product removed from Invoice"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Invoice, Product or InvoiceProduct not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function removeProductsFromInvoice(int $invoiceId, int $productId): void
    {

        // get the invoice by id
        try {
            $invoice = $this->dao->getOneBy(Invoice::class, ['id' => $invoiceId]);

            $product = $this->dao->getOneBy(Product::class, ['id' => $productId]);

            //if the invoice is not found
            if (!$invoice || !$product) {
                $this->request->handleErrorAndQuit(404, new Exception('Invoice or Product not found'));
            }
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        //get InvoiceProduct by invoice and product
        try {
            $invoiceProduct = $this->dao->getOneBy(InvoiceProduct::class, ['invoice' => $invoice, 'product' => $product]);
            if ($invoiceProduct == null) {
                $this->request->handleErrorAndQuit(404, new Exception('InvoiceProduct not found'));
            } else {
                if ($invoiceProduct->getQuantity() > 1) {
                    $invoiceProduct->setQuantity($invoiceProduct->getQuantity() - 1);
                    $this->dao->update($invoiceProduct);
                } else {
                    $this->dao->delete($invoiceProduct);
                }
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        $this->request->handleSuccessAndQuit(200, 'Product removed from Invoice');
    }

    /**
     * @OA\Delete(
     *     path="/invoice/{id}",
     *     tags={"Invoice"},
     *     summary="Delete an invoice",
     *     description="Delete an invoice",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the invoice to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Invoice deleted"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Invoice not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteInvoice(int $id): void
    {
        // get the invoice by id
        try {
            $invoice = $this->dao->getOneBy(Invoice::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the invoice is not found
        if (!$invoice) {
            $this->request->handleErrorAndQuit(404, new Exception('Invoice not found'));
        }

        // remove the invoice
        try {
            $this->dao->delete($invoice);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Invoice deleted');
    }

}