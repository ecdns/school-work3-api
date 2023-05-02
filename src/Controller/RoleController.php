<?php

namespace Controller;

use Doctrine\ORM\EntityManager;
use Entity\Role;
use Exception;
use Service\Request;

class RoleController implements ControllerInterface
{
    private EntityManager $entityManager;
    private const DATA = ['name', 'description'];

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validateData(mixed $data, bool $isPostRequest = true): bool
    {
        if ($isPostRequest) {
            foreach (self::DATA as $key) {
                if (!isset($data[$key])) {
                    return false;
                }
            }
            return true;
        } else {
            foreach (self::DATA as $key) {
                if (isset($data[$key])) {
                    return true;
                }
            }
            return false;
        }
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
        if (!$this->validateData($requestBody)) {
            Request::handleErrorAndQuit(new Exception('Invalid data'), 400);
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
            Request::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                Request::handleErrorAndQuit(new Exception('Role already exists'), 409);
            }
            Request::handleErrorAndQuit($e, 500);
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
            Request::handleErrorAndQuit($e, 500);
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
            Request::handleErrorAndQuit($e, 500);
        }

        // if the role is not found
        if (!$role) {
            Request::handleErrorAndQuit(new Exception('Role not found'), 404);
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
            Request::handleErrorAndQuit($e, 500);
        }

        // if the role is not found
        if (!$role) {
            Request::handleErrorAndQuit(new Exception('Role not found'), 404);
        }

        // get the request body
        $requestBody = file_get_contents('php://input');

        // validate the data
        if (!$this->validateData($requestBody, false)) {
            Request::handleErrorAndQuit(new Exception('Invalid data'), 400);
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
            Request::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
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
            Request::handleErrorAndQuit($e, 500);
        }

        // if the role is not found
        if (!$role) {
            Request::handleErrorAndQuit(new Exception('Role not found'), 404);
        }

        // remove the role
        try {
            $this->entityManager->remove($role);
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // flush the entity manager
        try {
            $this->entityManager->flush();
        } catch (Exception $e) {
            Request::handleErrorAndQuit($e, 500);
        }

        // handle the response
        Request::handleSuccessAndQuit(200, 'Role deleted');
    }
}