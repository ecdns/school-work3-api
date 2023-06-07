<?php

declare(strict_types=1);

namespace Controller;

use DateTime;
use Entity\Estimate;
use Entity\EstimateProduct;
use Entity\EstimateStatus;
use Entity\Product;
use Entity\Project;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="EstimateRequest",
 *     required={"name", "description", "project", "expiredAt", "estimateStatus"},
 *     @OA\Property(property="name", type="string", example="Estimate name"),
 *     @OA\Property(property="description", type="string", example="Estimate description"),
 *     @OA\Property(property="project", type="integer", example="1"),
 *     @OA\Property(property="expiredAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="estimateStatus", type="integer", example="1")
 * )
 *
 * @OA\Schema (
 *     schema="EstimateProductRequest",
 *     required={"estimate", "product", "quantity"},
 *     @OA\Property(property="estimate", type="integer", example="1"),
 *     @OA\Property(property="product", type="integer", example="1"),
 *     @OA\Property(property="quantity", type="integer", example="1")
 * )
 *
 * @OA\Schema (
 *     schema="EstimateResponse",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="name", type="string", example="Estimate name"),
 *     @OA\Property(property="description", type="string", example="Estimate description"),
 *     @OA\Property(property="project", type="object", ref="#/components/schemas/ProjectResponse"),
 *     @OA\Property(property="expiredAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="estimateStatus", type="integer", example="1"),
 *     @OA\Property(property="estimateProducts", type="array", @OA\Items(ref="#/components/schemas/EstimateProductResponse")),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 *
 * @OA\Schema (
 *     schema="EstimateProductResponse",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="estimate", type="object", ref="#/components/schemas/EstimateResponse"),
 *     @OA\Property(property="product", type="object", ref="#/components/schemas/ProductResponse"),
 *     @OA\Property(property="quantity", type="integer", example="1"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 *
 */
class EstimateController extends AbstractController
{

    private const REQUIRED_FIELDS = ['name', 'description', 'project', 'expiredAt', 'estimateStatus'];
    private DAO $dao;
    private Request $request;

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }


    /**
     * @OA\Post(
     *     path="/estimate",
     *     tags={"Estimate"},
     *     summary="Add a new estimate",
     *     description="Add a new estimate to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Estimate object that needs to be added to the database",
     *         @OA\JsonContent(ref="#/components/schemas/EstimateRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Estimate created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="EstimateStatus, Project, ExpiredDate not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Estimate already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function addEstimate(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//         {
//             "name": "Estimate 1",
//             "description": "This is the first estimate",
//             "project": 1,
//             "expiredAt": "2021-09-30",
//             "estimateStatus": 1
//         }


        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }


        // get the estimate data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];
        $project = $requestBody['project'];
        $expiredAt = $requestBody['expiredAt'];
        $estimateStatus = $requestBody['estimateStatus'];

        // get the estimate from the database by its id
        try {
            $projectObject = $this->dao->getOneBy(Project::class, ['id' => $project]);
            $estimateStatusObject = $this->dao->getOneBy(EstimateStatus::class, ['id' => $estimateStatus]);
            $expiredAt = DateTime::createFromFormat('Y-m-d', $expiredAt);


            if (!$estimateStatusObject || !$projectObject || !$expiredAt) {
                $this->request->handleErrorAndQuit(404, new Exception('EstimateStatus, Project, ExpiredDate not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // create a new estimate
        $estimate = new Estimate($name, $description, $projectObject, $expiredAt, $estimateStatusObject);

        // add the estimate to the database
        try {
            $this->dao->add($estimate);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Estimate already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'Estimate created');
    }

    /**
     * @OA\Get(
     *     path="/estimate",
     *     tags={"Estimate"},
     *     summary="Get all estimates",
     *     description="Returns all estimates",
     *     @OA\Response(
     *         response=200,
     *         description="Estimates found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/EstimateResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getEstimates(): void
    {
        // get all roles
        try {
            //get all estimates
            $estimates = $this->dao->getAll(Estimate::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($estimates as $estimate) {
            $response[] = $estimate->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Estimates found', $response);
    }

    /**
     * @OA\Get(
     *     path="/estimates/project/{projectId}",
     *     tags={"Estimate"},
     *     summary="Get all estimates by project",
     *     description="Returns all estimates by project",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Project id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *              )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Estimates found",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/EstimateResponse")
     *     )
     * ),
     *     @OA\Response(
     *          response=500,
     *          description="Internal server error"
     *      )
     * )
     */
    public function getEstimatesByProject(int $projectId): void
    {
        // get all roles
        try {
            //get all estimates by company
            $estimates = $this->dao->getBy(Estimate::class, ['project' => $projectId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($estimates as $estimate) {
            $response[] = $estimate->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Estimates found', $response);
    }


    /**
     * @OA\Get(
     *     path="/estimate/{id}",
     *     tags={"Estimate"},
     *     summary="Get estimate by id",
     *     description="Returns an estimate by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the estimate to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estimate found",
     *         @OA\JsonContent(ref="#/components/schemas/EstimateResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estimate not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getEstimateById(int $id): void
    {
        // get the role by id
        try {
            $estimate = $this->dao->getOneBy(Estimate::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$estimate) {
            $this->request->handleErrorAndQuit(404, new Exception('Estimate not found'));
        }

        // set the response
        $response = $estimate->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Estimate found', $response);
    }

    /**
     * @OA\Get(
     *     path="/estimate/company/{companyId}",
     *     tags={"Estimate"},
     *     summary="Get all estimates by company",
     *     description="Returns all estimates by company",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Company id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *              )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Estimates found",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/EstimateResponse")
     *     )
     * ),
     *     @OA\Response(
     *          response=500,
     *          description="Internal server error"
     *      )
     * )
     */
    public function getEstimatesByCompany(int $companyId): void
    {
        // get all roles
        try {
            //get all estimates by company
            $estimates = $this->dao->getBy(Estimate::class, ['company' => $companyId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($estimates as $estimate) {
            $response[] = $estimate->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Estimates found', $response);
    }

    //getEstimatesByCustomer
    /**
     * @OA\Get(
     *     path="/estimate/customer/{customerId}",
     *     tags={"Estimate"},
     *     summary="Get all estimates by customer",
     *     description="Returns all estimates by customer",
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Customer id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              format="int64"
     *              )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Estimates found",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/EstimateResponse")
     *     )
     * ),
     *     @OA\Response(
     *          response=500,
     *          description="Internal server error"
     *      )
     * )
     */
    public function getEstimatesByCustomer(int $customerId): void
    {
        try {
            $estimates = $this->dao->getBy(Estimate::class, ['customer' => $customerId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        $response = [];
        foreach ($estimates as $estimate) {
            $response[] = $estimate->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Estimates found', $response);
    }


    /**
     * @OA\Put(
     *     path="/estimate/{id}",
     *     tags={"Estimate"},
     *     summary="Update estimate by id",
     *     description="Updates an estimate by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the estimate to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Estimate object that needs to be updated",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/EstimateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estimate updated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estimate not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Estimate already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function updateEstimate(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the estimate by id
        try {
            $estimate = $this->dao->getOneBy(Estimate::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the estimate is not found
        if (!$estimate) {
            $this->request->handleErrorAndQuit(404, new Exception('Estimate not found'));
        }

        // it will look like this:
        //         {
        //             "name": "Estimate 1",
        //             "description": "This is the first estimate",
        //             "project": 1,
        //             "expiredAt": "2021-09-30",
        //             "estimateStatus": 1
        //         }


        // get the estimate data from the request body
        $name = $requestBody['name'] ?? $estimate->getName();
        $description = $requestBody['description'] ?? $estimate->getDescription();
        $project = $requestBody['project'] ?? $estimate->getProject()->getId();
        $expiredAt = $requestBody['expiredAt'] ?? $estimate->getExpiredAt();
        $estimateStatus = $requestBody['estimateStatus'] ?? $estimate->getEstimateStatus()->getId();

        try {

            $projectObject = $this->dao->getOneBy(Project::class, ['id' => $project]);

            $estimateStatusObject = $this->dao->getOneBy(EstimateStatus::class, ['id' => $estimateStatus]);

            if (gettype($expiredAt) == 'string') {
                $expiredAt = DateTime::createFromFormat('Y-m-d', $expiredAt);
            }


            if (!$estimateStatusObject || !$projectObject) {
                $this->request->handleErrorAndQuit(404, new Exception('EstimateStatus, Project or ExpiredDate not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // update the estimate
        $estimate->setName($name);
        $estimate->setDescription($description);
        $estimate->setProject($projectObject);
        $estimate->setExpiredAt($expiredAt);
        $estimate->setEstimateStatus($estimateStatusObject);

        // update the estimate in the database
        try {
            $this->dao->update($estimate);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Estimate already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Estimate updated');

    }

    /**
     * @OA\Post(
     *     path="/estimate/{estimateId}/product/{productId}",
     *     tags={"EstimateProduct"},
     *     summary="Add product to estimate",
     *     description="Add product to estimate",
     *     @OA\Parameter(
     *         name="estimateId",
     *         in="path",
     *         description="ID of estimate",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="ID of product",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product added to Estimate"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estimate or Product not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function addProductsToEstimate(int $estimateId, int $productId): void
    {

        // get the estimate by id
        try {
            $estimate = $this->dao->getOneBy(Estimate::class, ['id' => $estimateId]);

            $product = $this->dao->getOneBy(Product::class, ['id' => $productId]);

            //if the estimate is not found
            if (!$estimate || !$product) {
                $this->request->handleErrorAndQuit(404, new Exception('Estimate or Product not found'));
            }
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        //get EstimateProduct by estimate and product
        try {
            $estimateProduct = $this->dao->getOneBy(EstimateProduct::class, ['estimate' => $estimate, 'product' => $product]);
            if ($estimateProduct == null) {
                $estimateProduct = new EstimateProduct($estimate, $product, 1);
                $this->dao->add($estimateProduct);
                $estimate->addEstimateProduct($estimateProduct);
                $this->dao->update($estimate);
            } else {
                $estimateProduct->setQuantity($estimateProduct->getQuantity() + 1);
                $this->dao->update($estimateProduct);
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        $this->request->handleSuccessAndQuit(200, 'Product added to Estimate');
    }


    /**
     * @OA\Delete(
     *     path="/estimate/{estimateId}/product/{productId}",
     *     tags={"EstimateProduct"},
     *     summary="Remove product from estimate",
     *     description="Remove product from estimate",
     *     @OA\Parameter(
     *         name="estimateId",
     *         in="path",
     *         description="ID of estimate",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="ID of product",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product removed from Estimate"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estimate, Product or EstimateProduct not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function removeProductsFromEstimate(int $estimateId, int $productId): void
    {

        // get the estimate by id
        try {
            $estimate = $this->dao->getOneBy(Estimate::class, ['id' => $estimateId]);

            $product = $this->dao->getOneBy(Product::class, ['id' => $productId]);

            //if the estimate is not found
            if (!$estimate || !$product) {
                $this->request->handleErrorAndQuit(404, new Exception('Estimate or Product not found'));
            }
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        //get EstimateProduct by estimate and product
        try {
            $estimateProduct = $this->dao->getOneBy(EstimateProduct::class, ['estimate' => $estimate, 'product' => $product]);
            if ($estimateProduct == null) {
                $this->request->handleErrorAndQuit(404, new Exception('EstimateProduct not found'));
            } else {
                if ($estimateProduct->getQuantity() > 1) {
                    $estimateProduct->setQuantity($estimateProduct->getQuantity() - 1);
                    $this->dao->update($estimateProduct);
                } else {
                    $this->dao->delete($estimateProduct);
                }
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        $this->request->handleSuccessAndQuit(200, 'Product removed from Estimate');
    }


    /**
     * @OA\Delete(
     *     path="/estimate/{id}",
     *     tags={"Estimate"},
     *     summary="Delete an estimate",
     *     description="Delete an estimate by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of estimate",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estimate deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Estimate not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function deleteEstimate(int $id): void
    {
        // get the estimate by id
        try {
            $estimate = $this->dao->getOneBy(Estimate::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the estimate is not found
        if (!$estimate) {
            $this->request->handleErrorAndQuit(404, new Exception('Estimate not found'));
        }

        // remove the estimate
        try {
            $this->dao->delete($estimate);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Estimate deleted');
    }

}