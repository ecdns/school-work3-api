<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Role;
use Exception;
use Service\DAO;
use Service\Request;

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
            $this->dao->addEntity($role);
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

    public function getRoles(): void
    {
        // get all roles
        try {
            $roles = $this->dao->getAllEntities(Role::class);
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

    public function getRoleById(int $id): void
    {
        // get the role by id
        try {
            $role = $this->dao->getOneEntityBy(Role::class, ['id' => $id]);
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
            $role = $this->dao->getOneEntityBy(Role::class, ['id' => $id]);
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
            $this->dao->updateEntity($role);
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

    public function deleteRole(int $id): void
    {
        // get the role by id
        try {
            $role = $this->dao->getOneEntityBy(Role::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$role) {
            $this->request->handleErrorAndQuit(404, new Exception('Role not found'));
        }

        // remove the role
        try {
            $this->dao->deleteEntity($role);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Role deleted');
    }
}