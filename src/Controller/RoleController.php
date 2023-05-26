<?php

declare(strict_types=1);

namespace Controller;

use Entity\Role;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="RoleRequest",
 *     required={"name", "description"},
 *     @OA\Property(property="name", type="string", example="Admin"),
 *     @OA\Property(property="description", type="string", example="Administrator")
 * )
 *
 * @OA\Schema (
 *     schema="RoleResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Admin"),
 *     @OA\Property(property="description", type="string", example="Administrator"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 *
 */
class RoleController extends AbstractController
{

    private DAO $dao;
    private Request $request;
    private const REQUIRED_FIELDS = ['name', 'description'];

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *     path="/role",
     *     tags={"Role"},
     *     summary="Add a new role",
     *     description="Add a new role to the database",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Role object that needs to be added to the database",
     *         @OA\JsonContent(ref="#/components/schemas/RoleRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Role created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Role created")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Role already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function addRole(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "Admin",
        //     "description": "Administrator"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];

        // create a new role
        $role = new Role($name, $description);

        // add the role to the database
        try {
            $this->dao->add($role);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Role already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'Role created');
    }


    /**
     * @OA\Get(
     *     path="/role/all",
     *     tags={"Role"},
     *     summary="Get all roles",
     *     description="Get all roles from the database",
     *     @OA\Response(
     *         response=200,
     *         description="Roles found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/RoleResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getRoles(): void
    {
        // get all roles
        try {
            $roles = $this->dao->getAll(Role::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($roles as $role) {
            $response[] = $role->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Roles found', $response);
    }



    /**
     * @OA\Get(
     *     path="/role/{id}",
     *     tags={"Role"},
     *     summary="Get a role by id",
     *     description="Get a role from the database by its id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the role to get",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role found",
     *         @OA\JsonContent(ref="#/components/schemas/RoleResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getRoleById(int $id): void
    {
        // get the role by id
        try {
            $role = $this->dao->getOneBy(Role::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$role) {
            $this->request->handleErrorAndQuit(404, new Exception('Role not found'));
        }

        // set the response
        $response = $role->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Role found', $response);
    }


    /**
     * @OA\Put(
     *     path="/role/{id}",
     *     tags={"Role"},
     *     summary="Update a role by id",
     *     description="Update a role from the database by its id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the role to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Role object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/RoleRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role updated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Role already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function updateRole(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "name": "Admin",
        //     "description": "Administrator"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the role by id
        try {
            $role = $this->dao->getOneBy(Role::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$role) {
            $this->request->handleErrorAndQuit(404, new Exception('Role not found'));
        }

        // get the user data from the request body
        $name = $requestBody['name'] ?? $role->getName();
        $description = $requestBody['description'] ?? $role->getDescription();

        // update the role
        $role->setName($name);
        $role->setDescription($description);

        // flush the entity manager
        try {
            $this->dao->update($role);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Role already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Role updated');
    }


    /**
     * @OA\Delete(
     *     path="/role/{id}",
     *     tags={"Role"},
     *     summary="Delete a role by id",
     *     description="Delete a role from the database by its id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the role to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Role not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteRole(int $id): void
    {
        // get the role by id
        try {
            $role = $this->dao->getOneBy(Role::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$role) {
            $this->request->handleErrorAndQuit(404, new Exception('Role not found'));
        }

        // remove the role
        try {
            $this->dao->delete($role);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Role deleted');
    }

}