<?php

declare(strict_types=1);

namespace Controller;

use Entity\OrderForm;
use Entity\OrderFormProduct;
use Entity\Product;
use Entity\Project;
use Exception;
use Service\DAO;
use Service\Request;

class OrderFormController extends AbstractController
{
    
    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name', 'description', 'project'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }


    public function addOrderForm(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "OrderForm 1",
        //     "description": "This is the first orderForm",
        //     "project": 1
        // }


        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }


        // get the orderForm data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];
        $project = $requestBody['project'];

        // get the orderForm from the database by its id
        try {
            $projectObject = $this->dao->getOneBy(Project::class, ['id' => $project]);


            if (!$projectObject) {
                $this->request->handleErrorAndQuit(404, new Exception('Project not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }



        // create a new orderForm
        $orderForm = new OrderForm($name, $description, $projectObject);

        // add the orderForm to the database
        try {
            $this->dao->add($orderForm);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('OrderForm already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'OrderForm created');
    }


    public function getOrderForms(): void
    {
        // get all roles
        try {
            //get all orderForms
            $orderForms = $this->dao->getAll(OrderForm::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($orderForms as $orderForm) {
            $response[] = $orderForm->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'OrderForms found', $response);
    }

    public function getOrderFormsByProject(int $id): void
    {
        // get all roles
        try {
            //get all orderForms by company
            $orderForms = $this->dao->getBy(OrderForm::class, ['project' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($orderForms as $orderForm) {
            $response[] = $orderForm->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'OrderForms found', $response);
    }

    public function getOrderFormById(int $id): void
    {
        // get the role by id
        try {
            $orderForm = $this->dao->getOneBy(OrderForm::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$orderForm) {
            $this->request->handleErrorAndQuit(404, new Exception('OrderForm not found'));
        }

        // set the response
        $response = $orderForm->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'OrderForm found', $response);
    }

    public function updateOrderForm(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the orderForm by id
        try {
            $orderForm = $this->dao->getOneBy(OrderForm::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the orderForm is not found
        if (!$orderForm) {
            $this->request->handleErrorAndQuit(404, new Exception('OrderForm not found'));
        }

        // it will look like this:
//         {
//             "name": "OrderForm 1",
//             "description": "This is the first orderForm",
//             "project": 1,
//             "expiredAt": "2021-09-30",
//             "orderFormStatus": 1
//         }


        // get the orderForm data from the request body
        $name = $requestBody['name'] ?? $orderForm->getName();
        $description = $requestBody['description'] ?? $orderForm->getDescription();
        $project = $requestBody['project'] ?? $orderForm->getProject()->getId();
        try {

            $projectObject = $this->dao->getOneBy(Project::class, ['id' => $project]);


            if (!$projectObject) {
                $this->request->handleErrorAndQuit(404, new Exception('OrderFormStatus, Project or ExpiredDate not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // update the orderForm
        $orderForm->setName($name);
        $orderForm->setDescription($description);
        $orderForm->setProject($projectObject);


        // update the orderForm in the database
        try {
            $this->dao->update($orderForm);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('OrderForm already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'OrderForm updated');

    }

    //add Prducts To OrderForm
    public function addProductsToOrderForm(int $orderFormId, int $productId): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // decode the json
        $requestBody = json_decode($requestBody, true);




        // get the orderForm by id
        try {
            $orderForm = $this->dao->getOneBy(OrderForm::class, ['id' => $orderFormId]);

            $product = $this->dao->getOneBy(Product::class, ['id' => $productId]);

            //if the orderForm is not found
            if (!$orderForm || !$product) {
                $this->request->handleErrorAndQuit(404, new Exception('OrderForm or Product not found'));
            }
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        //get OrderFormProduct by orderForm and product
        try {
            $orderFormProduct = $this->dao->getOneBy(OrderFormProduct::class, ['orderForm' => $orderForm, 'product' => $product]);
            if ($orderFormProduct==null) {
                $orderFormProduct = new OrderFormProduct($orderForm, $product, 1);
                $this->dao->add($orderFormProduct);
                $orderForm->addOrderFormProduct($orderFormProduct);
                $this->dao->update($orderForm);
            }else{
                $orderFormProduct->setQuantity($orderFormProduct->getQuantity()+1);
                $this->dao->update($orderFormProduct);
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        $this->request->handleSuccessAndQuit(200, 'Product added to OrderForm');
    }

    //remove Prducts From OrderForm
    public function removeProductsFromOrderForm(int $orderFormId, int $productId): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // get the orderForm by id
        try {
            $orderForm = $this->dao->getOneBy(OrderForm::class, ['id' => $orderFormId]);

            $product = $this->dao->getOneBy(Product::class, ['id' => $productId]);

            //if the orderForm is not found
            if (!$orderForm || !$product) {
                $this->request->handleErrorAndQuit(404, new Exception('OrderForm or Product not found'));
            }
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        //get OrderFormProduct by orderForm and product
        try {
            $orderFormProduct = $this->dao->getOneBy(OrderFormProduct::class, ['orderForm' => $orderForm, 'product' => $product]);
            if ($orderFormProduct==null) {
                $this->request->handleErrorAndQuit(404, new Exception('OrderFormProduct not found'));
            }else{
                if ($orderFormProduct->getQuantity()>1) {
                    $orderFormProduct->setQuantity($orderFormProduct->getQuantity()-1);
                    $this->dao->update($orderFormProduct);
                }else{
                    $this->dao->delete($orderFormProduct);
                }
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        $this->request->handleSuccessAndQuit(200, 'Product removed from OrderForm');
    }


    public function deleteOrderForm(int $id): void
    {
        // get the orderForm by id
        try {
            $orderForm = $this->dao->getOneBy(OrderForm::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the orderForm is not found
        if (!$orderForm) {
            $this->request->handleErrorAndQuit(404, new Exception('OrderForm not found'));
        }

        // remove the orderForm
        try {
            $this->dao->delete($orderForm);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'OrderForm deleted');
    }
}