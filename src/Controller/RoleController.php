<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Role;
use Exception;
use Service\Request;

class RoleController extends AbstractController
{
    private EntityManager $entityManager;
    private const REQUIRED_FIELDS = ['name', 'description'];

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
        if (!$this->validatePostData($requestBody, self::REQUIRED_FIELDS)) {
            Request::handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user data from the request body
        $name = $requestBody['name'];
        $description = $requestBody['description'];

        // create a new role
        $role = new Role($name, $description);

        // persist the role
        try {
            $this->entityManager->persist($role);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(409, new Exception('Role already exists'));
            }
            Request::handleErrorAndQuit(500, $e);
        }

        // handle the response
        Request::handleSuccessAndQuit(201, 'Role created');
    }

    public function getRoles(): void
    {
        // get all roles
        try {
            $roles = $this->entityManager->getRepository(Role::class)->findAll();
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($roles as $role) {
            $response[] = $role->toArray();
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'Roles found', $response);
    }

    public function getRoleById(int $id): void
    {
        // get the role by id
        try {
            $role = $this->entityManager->find(Role::class, $id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$role) {
            Request::handleErrorAndQuit(404, new Exception('Role not found'));
        }

        // set the response
        $response = $role->toArray();

        // handle the response
        Request::handleSuccessAndQuit(200, 'Role found', $response);
    }

    public function updateRole(int $id): void
    {
        // get the role by id
        try {
            $role = $this->entityManager->find(Role::class, $id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$role) {
            Request::handleErrorAndQuit(404, new Exception('Role not found'));
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
        //     "name": "Admin",
        //     "description": "Administrator"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // get the user data from the request body
        $name = $requestBody['name'] ?? null;
        $description = $requestBody['description'] ?? null;

        // update the role
        $role->setName($name ?? $role->getName());
        $role->setDescription($description ?? $role->getDescription());

        // persist the role
        try {
            $this->entityManager->persist($role);
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
        Request::handleSuccessAndQuit(200, 'Role updated');

    }

    public function deleteRole(int $id): void
    {
        // get the role by id
        try {
            $role = $this->entityManager->find(Role::class, $id);
        } catch (Exception $e) {
            Request::handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$role) {
            Request::handleErrorAndQuit(404, new Exception('Role not found'));
        }

        // remove the role
        try {
            $this->entityManager->remove($role);
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
        Request::handleSuccessAndQuit(200, 'Role deleted');
    }
}