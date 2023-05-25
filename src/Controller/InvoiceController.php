<?php

declare(strict_types=1);

namespace Controller;

use DateTime;
use Entity\Invoice;
use Entity\InvoiceProduct;
use Entity\Product;
use Entity\Project;
use Exception;
use Service\DAO;
use Service\Request;

class InvoiceController extends AbstractController
{
    
    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name', 'description', 'project'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }


    public function addInvoice(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//         {
//             "name": "Invoice 1",
//             "description": "This is the first invoice",
//             "project": 1
//         }


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

    public function getInvoicesByProject(int $id): void
    {
        // get all roles
        try {
            //get all invoices by company
            $invoices = $this->dao->getBy(Invoice::class, ['project' => $id]);
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

    //add Prducts To Invoice
    public function addProductsToInvoice(int $invoiceId, int $productId): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // decode the json
        $requestBody = json_decode($requestBody, true);




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
            if ($invoiceProduct==null) {
                $invoiceProduct = new InvoiceProduct($invoice, $product, 1);
                $this->dao->add($invoiceProduct);
                $invoice->addInvoiceProduct($invoiceProduct);
                $this->dao->update($invoice);
            }else{
                $invoiceProduct->setQuantity($invoiceProduct->getQuantity()+1);
                $this->dao->update($invoiceProduct);
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        $this->request->handleSuccessAndQuit(200, 'Product added to Invoice');
    }

    //remove Prducts From Invoice
    public function removeProductsFromInvoice(int $invoiceId, int $productId): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // decode the json
        $requestBody = json_decode($requestBody, true);

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
            if ($invoiceProduct==null) {
                $this->request->handleErrorAndQuit(404, new Exception('InvoiceProduct not found'));
            }else{
                if ($invoiceProduct->getQuantity()>1) {
                    $invoiceProduct->setQuantity($invoiceProduct->getQuantity()-1);
                    $this->dao->update($invoiceProduct);
                }else{
                    $this->dao->delete($invoiceProduct);
                }
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        $this->request->handleSuccessAndQuit(200, 'Product removed from Invoice');
    }


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