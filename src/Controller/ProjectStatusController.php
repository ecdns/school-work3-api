<?php

namespace Controller;

// controller for entity ProjectStatus
use Entity\ProjectStatus;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="ProjectStatusRequest",
 *     required={"name", "description"},
 *     @OA\Property(property="name", type="string", example="ProjectStatus 1"),
 *     @OA\Property(property="description", type="string", example="This is the first projectStatus")
 * )
 *
 * @OA\Schema (
 *     schema="ProjectStatusResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="ProjectStatus 1"),
 *     @OA\Property(property="description", type="string", example="This is the first projectStatus"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 *
 */
class ProjectStatusController extends AbstractController
{

    private const REQUIRED_FIELDS = ['name', 'description'];
    private DAO $dao;
    private Request $request;

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *     path="/projectStatus",
     *     tags={"ProjectStatus"},
     *     summary="Add a new ProjectStatus",
     *     description="Add a new ProjectStatus",
     *     @OA\RequestBody(
     *         required=true,
     *         description="ProjectStatus object that needs to be added",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="ProjectStatus created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="ProjectStatus already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function addProjectStatus(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//         {
//             "name": "ProjectStatus 1",
//             "description": "This is the first projectStatus"
//         }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the ProjectStatus data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];

        // create a new projectStatus
        $projectStatus = new ProjectStatus($name, $description);

        // persist the projectStatus
        try {
            $this->dao->add($projectStatus);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('ProjectStatus already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'ProjectStatus created');

    }

    /**
     * @OA\Get(
     *     path="/projectStatus/all",
     *     tags={"ProjectStatus"},
     *     summary="Get all ProjectStatuses",
     *     description="Get all ProjectStatuses",
     *     @OA\Response(
     *         response=200,
     *         description="ProjectStatuses found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ProjectStatusResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getProjectStatuses(): void
    {
        // get all the ProjectStatus from the database
        try {
            $productFamilies = $this->dao->getAll(ProjectStatus::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($productFamilies as $projectStatus) {
            $response[] = $projectStatus->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'ProjectStatus found', $response);
    }


    /**
     * @OA\Get(
     *     path="/projectStatus/{id}",
     *     tags={"ProjectStatus"},
     *     summary="Get a ProjectStatus by ID",
     *     description="Get a ProjectStatus by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the ProjectStatus to get",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ProjectStatus found",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectStatusResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="ProjectStatus not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getProjectStatusById(int $id): void
    {
        // get the license from the database by its id
        try {
            $projectStatus = $this->dao->getOneBy(ProjectStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$projectStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('ProjectStatus not found'));
        }

        // set the response
        $response = $projectStatus->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'ProjectStatus found', $response);
    }


    //function for updating a projectStatus

    /**
     * @OA\Put(
     *     path="/projectStatus/{id}",
     *     tags={"ProjectStatus"},
     *     summary="Update a ProjectStatus by ID",
     *     description="Update a ProjectStatus by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the ProjectStatus to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="ProjectStatus object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectStatusRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ProjectStatus updated",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="ProjectStatus not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="ProjectStatus already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function updateProjectStatus(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "ProjectStatus 1",
        //     "description": "This is the first projectStatus"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the ProjectStatus from the database by its id
        try {
            $projectStatus = $this->dao->getOneBy(ProjectStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the projectStatus is not found
        if (!$projectStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('ProjectStatus not found'));
        }

        // get the ProjectStatus data from the request body
        $name = $requestBody['name'] ?? $projectStatus->getName();
        $description = $requestBody['description'] ?? $projectStatus->getDescription();

        // update the projectStatus
        $projectStatus->setName($name);
        $projectStatus->setDescription($description);

        // persist the projectStatus
        try {
            $this->dao->update($projectStatus);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('ProjectStatus already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'ProjectStatus updated');

    }

    /**
     * @OA\Delete(
     *     path="/projectStatus/{id}",
     *     tags={"ProjectStatus"},
     *     summary="Delete a ProjectStatus by ID",
     *     description="Delete a ProjectStatus by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the ProjectStatus to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="ProjectStatus deleted"
     *         )
     *     ),
     * @OA\Response(
     *         response=404,
     *         description="ProjectStatus not found"
     *     ),
     * @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function deleteProjectStatus(int $id): void
    {
        // get the ProjectStatus from the database by its id
        try {
            $projectStatus = $this->dao->getOneBy(ProjectStatus::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the ProjectStatus is not found
        if (!$projectStatus) {
            $this->request->handleErrorAndQuit(404, new Exception('ProjectStatus not found'));
        }

        // remove the ProjectStatus
        try {
            $this->dao->delete($projectStatus);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'ProjectStatus deleted');
    }


}