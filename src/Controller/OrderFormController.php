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

/**
 * @OA\Schema (
 *     schema="OrderFormRequest",
 *     required={"name", "description", "project"},
 *     @OA\Property(property="name", type="string", example="OrderForm 1"),
 *     @OA\Property(property="description", type="string", example="This is the first orderForm"),
 *     @OA\Property(property="project", type="integer", example=1)
 * )
 *
 * @OA\Schema (
 *     schema="OrderFormResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="OrderForm 1"),
 *     @OA\Property(property="description", type="string", example="This is the first orderForm"),
 *     @OA\Property(property="project", type="object", ref="#/components/schemas/ProjectResponse"),
 *     @OA\Property(property="orderFormProducts", type="array", @OA\Items(ref="#/components/schemas/OrderFormProductResponse")),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 *
 * @OA\Schema (
 *     schema="OrderFormProductResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="orderForm", type="object", ref="#/components/schemas/OrderFormResponse"),
 *     @OA\Property(property="product", type="object", ref="#/components/schemas/ProductResponse"),
 *     @OA\Property(property="quantity", type="integer", example=1),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 *
 * @OA\Schema (
 *     schema="OrderFormProductRequest",
 *     required={"orderForm", "product", "quantity"},
 *     @OA\Property(property="orderForm", type="integer", example=1),
 *     @OA\Property(property="product", type="integer", example=1),
 *     @OA\Property(property="quantity", type="integer", example=1)
 * )
 */
class OrderFormController extends AbstractController
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
     *     path="/orderForm",
     *     tags={"OrderForm"},
     *     summary="Create a new OrderForm",
     *     @OA\RequestBody(
     *         required=true,
     *         description="OrderForm object that needs to be created",
     *         @OA\JsonContent(ref="#/components/schemas/OrderFormRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="OrderForm created",
     *         @OA\JsonContent(ref="#/components/schemas/OrderFormResponse")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="OrderForm already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
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


    /**
     * @OA\Get(
     *     path="/orderForm",
     *     tags={"OrderForm"},
     *     summary="Get all OrderForms",
     *     @OA\Response(
     *         response=200,
     *         description="OrderForms found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/OrderFormResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
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


    /**
     * @OA\Get(
     *     path="/orderForm/project/{projectId}",
     *     tags={"OrderForm"},
     *     summary="Get all OrderForms by project",
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
     *         response=200,
     *         description="OrderForms found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/OrderFormResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getOrderFormsByProject(int $projectId): void
    {
        // get all roles
        try {
            //get all orderForms by company
            $orderForms = $this->dao->getBy(OrderForm::class, ['project' => $projectId]);
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

    /**
     * @OA\Get(
     *     path="/orderForm/company/{id}",
     *     tags={"OrderForm"},
     *     summary="Get all OrderForms by company",
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
     *         response=200,
     *         description="OrderForms found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/OrderFormResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getOrderFormsByCompanyId(int $companyId): void
    {
        // get all roles
        try {
            //get all orderForms by company
            $orderForms = $this->dao->getBy(OrderForm::class, ['company' => $companyId]);
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



    /**
     * @OA\Get(
     *     path="/orderForm/{id}",
     *     tags={"OrderForm"},
     *     summary="Get OrderForm by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the OrderForm",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OrderForm found",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/OrderFormResponse"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="OrderForm not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
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

    //getOrderFormsByCustomer
    /**
     * @OA\Get(
     *     path="/orderForm/customer/{customerId}",
     *     tags={"OrderForm"},
     *     summary="Get all OrderForms by customer",
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
     *         response=200,
     *         description="OrderForms found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/OrderFormResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getOrderFormsByCustomer(int $customerId): void
    {
        try {
            $projects = $this->dao->getBy(Project::class, ['customer' => $customerId]);
            $orderForms = [];
            foreach ($projects as $project) {
                $orderForms = array_merge($orderForms, $this->dao->getBy(OrderForm::class, ['project' => $project->getId()]));
            }
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        $response = [];
        foreach ($orderForms as $orderForm) {
            $response[] = $orderForm->toArray();
        }

        $this->request->handleSuccessAndQuit(200, 'OrderForms found', $response);
    }


    /**
     * @OA\Put(
     *     path="/orderForm/{id}",
     *     tags={"OrderForm"},
     *     summary="Update OrderForm by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the OrderForm",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="OrderForm object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/OrderFormRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OrderForm updated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="OrderForm not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="OrderForm already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
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


    /**
     * @OA\Post(
     *     path="/orderForm/{orderFormId}/product/{productId}",
     *     tags={"OrderFormProduct"},
     *     summary="Add products to an order form",
     *     description="Add products to an order form",
     *     @OA\Parameter(
     *         name="orderFormId",
     *         in="path",
     *         description="ID of the order form",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="ID of the product",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to OrderForm"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="OrderForm or Product not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function addProductsToOrderForm(int $orderFormId, int $productId): void
    {

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
            if ($orderFormProduct == null) {
                $orderFormProduct = new OrderFormProduct($orderForm, $product, 1);
                $this->dao->add($orderFormProduct);
                $orderForm->addOrderFormProduct($orderFormProduct);
                $this->dao->update($orderForm);
            } else {
                $orderFormProduct->setQuantity($orderFormProduct->getQuantity() + 1);
                $this->dao->update($orderFormProduct);
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        $this->request->handleSuccessAndQuit(200, 'Product added to OrderForm');
    }


    /**
     * @OA\Delete(
     *     path="/orderForm/{orderFormId}/product/{productId}",
     *     tags={"OrderForm"},
     *     summary="Remove products from an order form",
     *     description="Remove products from an order form",
     *     @OA\Parameter(
     *         name="orderFormId",
     *         in="path",
     *         description="ID of the order form",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="ID of the product",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product removed from OrderForm"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="OrderForm, Product or OrderFormProduct not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
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
            if ($orderFormProduct == null) {
                $this->request->handleErrorAndQuit(404, new Exception('OrderFormProduct not found'));
            } else {
                if ($orderFormProduct->getQuantity() > 1) {
                    $orderFormProduct->setQuantity($orderFormProduct->getQuantity() - 1);
                    $this->dao->update($orderFormProduct);
                } else {
                    $this->dao->delete($orderFormProduct);
                }
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        $this->request->handleSuccessAndQuit(200, 'Product removed from OrderForm');
    }


    /**
     * @OA\Delete(
     *     path="/orderForm/{id}",
     *     tags={"OrderForm"},
     *     summary="Delete an order form",
     *     description="Delete an order form",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the order form",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OrderForm deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="OrderForm not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
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