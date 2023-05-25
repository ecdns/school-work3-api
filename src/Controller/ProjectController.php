<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Company;
use Entity\Customer;
use Entity\Project;
use Entity\ProjectStatus;
use Entity\User;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="ProjectRequest",
 *     required={"name", "description", "company", "creator", "customer", "projectStatus"},
 *     @OA\Property(property="name", type="string", example="Project 1"),
 *     @OA\Property(property="description", type="string", example="This is the first project"),
 *     @OA\Property(property="company", type="integer", example="1"),
 *     @OA\Property(property="creator", type="integer", example="1"),
 *     @OA\Property(property="customer", type="integer", example="1"),
 *     @OA\Property(property="projectStatus", type="integer", example="1")
 * )
 *
 * @OA\Schema (
 *     schema="ProjectResponse",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="name", type="string", example="Project 1"),
 *     @OA\Property(property="description", type="string", example="This is the first project"),
 *     @OA\Property(property="company", type="object", ref="#/components/schemas/CompanyResponse"),
 *     @OA\Property(property="creator", type="object", ref="#/components/schemas/UserResponse"),
 *     @OA\Property(property="customer", type="object", ref="#/components/schemas/CustomerResponse"),
 *     @OA\Property(property="projectStatus", type="integer", example="1"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 *
 */
class ProjectController extends AbstractController
{
    
    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name', 'description', 'company', 'creator', 'customer', 'projectStatus'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *     path="/project",
     *     tags={"Project"},
     *     summary="Add a new project",
     *     description="Add a new project to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Project object that needs to be added to the database",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Project created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Company, User, Customer or ProjectStatus not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Project already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function addProject(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//        {
//            "name": "Project 1",
//             "description": "This is the first project",
//             "company": 1,
//             "creator": 1,
//             "customer": 1,
//             "projectStatus": 1
//         }


        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }


        // get the project data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];
        $company = $requestBody['company'];
        $creator = $requestBody['creator'];
        $customer = $requestBody['customer'];
        $projectStatus = $requestBody['projectStatus'];


        // get the project FK from the database by its id
        try {
            $companyObject = $this->dao->getOneEntityBy(Company::class, ['id' => $company]);
            $creatorObject = $this->dao->getOneEntityBy(User::class, ['id' => $creator]);
            $customerObject = $this->dao->getOneEntityBy(Customer::class, ['id' => $customer]);
            $projectStatusObject = $this->dao->getOneEntityBy(ProjectStatus::class, ['id' => $projectStatus]);

            if (!$companyObject || !$creatorObject || !$customerObject || !$projectStatusObject) {
                $this->request->handleErrorAndQuit(404, new Exception('Company, User, Customer or ProjectStatus not found'));
            }
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // create a new project
        $project = new Project($name, $description, $companyObject, $creatorObject, $customerObject, $projectStatusObject);

        // add the Project to the database
        try {
            $this->dao->addEntity($project);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Project already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'Project created');
    }


    /**
     * @OA\Get(
     *     path="/project/all",
     *     tags={"Project"},
     *     summary="Get all projects",
     *     description="Returns an array of all projects",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ProjectResponse")
     *        )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getProjects(): void
    {
        // get all roles
        try {
            //get all Projects
            $projects = $this->dao->getAllEntities(Project::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($projects as $project) {
            $response[] = $project->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Projects found', $response);
    }

    /**
     * @OA\Get(
     *     path="/project/company/{id}",
     *     tags={"Project"},
     *     summary="Get all projects by company",
     *     description="Returns an array of all projects by company",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the company",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ProjectResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getProjectsByCompany(int $id): void
    {
        // get all roles
        try {
            //get all project by company
            $projects = $this->dao->getEntitiesBy(Project::class, ['company' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($projects as $project) {
            $response[] = $project->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Projects found', $response);
    }



    /**
     * @OA\Get(
     *     path="/project/{id}",
     *     tags={"Project"},
     *     summary="Get project by ID",
     *     description="Returns a project by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the project",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getProjectById(int $id): void
    {
        // get the project by id
        try {
            $project= $this->dao->getOneEntityBy(Project::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the Project is not found
        if (!$project) {
            $this->request->handleErrorAndQuit(404, new Exception('Project not found'));
        }

        // set the response
        $response = $project->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Project found', $response);
    }


    /**
     * @OA\Put(
     *     path="/project/{id}",
     *     tags={"Project"},
     *     summary="Update project by ID",
     *     description="Updates a project by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the project",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Project object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/ProjectRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
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
     *         description="Project already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function updateProject(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the Project by id
        try {
            $project = $this->dao->getOneEntityBy(Project::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the Project is not found
        if (!$project) {
            $this->request->handleErrorAndQuit(404, new Exception('Project not found'));
        }

        // it will look like this:
        // {
        //     "name": "Project 1",
        //     "description": "This is the first project",
        //     "company": 1,
        //     "creator": 1,
        //     "customer": 1,
        //     "projectStatus": 1
        // }

        // get the Project data from the request body
        $name = $requestBody['name'] ?? $project->getName();
        $description = $requestBody['description'] ?? $project->getDescription();
        $company = $requestBody['company'] ?? $project->getCompany()->getId();
        $creator = $requestBody['creator'] ?? $project->getCreator()->getId();
        $customer = $requestBody['customer'] ?? $project->getCustomer()->getId();
        $projectStatus = $requestBody['projectStatus'] ?? $project->getProjectStatus()->getId();


        try {
            $companyObject = $this->dao->getOneEntityBy(Company::class, ['id' => $company]);
            $creatorObject = $this->dao->getOneEntityBy(User::class, ['id' => $creator]);
            $customerObject = $this->dao->getOneEntityBy(Customer::class, ['id' => $customer]);
            $projectStatusObject = $this->dao->getOneEntityBy(ProjectStatus::class, ['id' => $projectStatus]);

            if (!$companyObject || !$creatorObject || !$customerObject || !$projectStatusObject) {
                $this->request->handleErrorAndQuit(404, new Exception('Company, User, Customer or ProjectStatus not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // update the project
        $project->setName($name);
        $project->setDescription($description);
        $project->setCompany($companyObject);
        $project->setCreator($creatorObject);
        $project->setCustomer($customerObject);
        $project->setProjectStatus($projectStatusObject);

        // update the project in the database
        try {
            $this->dao->updateEntity($project);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Project already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Project updated');

    }


    /**
     * @OA\Delete(
     *     path="/project/{id}",
     *     tags={"Project"},
     *     summary="Delete project by ID",
     *     description="Deletes a project by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the project",
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
     *         description="Project not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteProject(int $id): void
    {
        // get the Project by id
        try {
            $project = $this->dao->getOneEntityBy(Project::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the Project is not found
        if (!$project) {
            $this->request->handleErrorAndQuit(404, new Exception('Project not found'));
        }

        // remove the Project
        try {
            $this->dao->deleteEntity($project);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Project deleted');
    }
}