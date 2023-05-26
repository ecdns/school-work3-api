<?php

declare(strict_types=1);

namespace Controller;

use Entity\Company;
use Entity\Role;
use Entity\User;
use Exception;
use Service\Auth;
use Service\DAO;
use Service\Http;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="UserRequest",
 *     required={"firstName", "lastName", "email", "password", "job", "phone", "role", "company"},
 *     @OA\Property(property="firstName", type="string", example="John"),
 *     @OA\Property(property="lastName", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", example="john.doe@gmail.com"),
 *     @OA\Property(property="password", type="string", format="password", example="password"),
 *     @OA\Property(property="job", type="string", example="Developer"),
 *     @OA\Property(property="phone", type="string", example="0123456789"),
 *     @OA\Property(property="role", type="integer", example="1"),
 *     @OA\Property(property="company", type="integer", example="1")
 * )
 *
 * @OA\Schema (
 *     schema="UserResponse",
 *     @OA\Property(property="id", type="integer", example="1"),
 *     @OA\Property(property="firstName", type="string", example="John"),
 *     @OA\Property(property="lastName", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", example="john.doe@gmail.com"),
 *     @OA\Property(property="job", type="string", example="Developer"),
 *     @OA\Property(property="phone", type="string", example="0123456789"),
 *     @OA\Property(property="role", type="object", ref="#/components/schemas/RoleResponse"),
 *     @OA\Property(property="company", type="object", ref="#/components/schemas/CompanyResponse"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-03-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-03-01 00:00:00")
 * )
 */
class UserController extends AbstractController
{
    private DAO $dao;
    private Request $request;
    private Auth $auth;
    private Http $http;
    private string $jwtKey;
    private const REQUIRED_FIELDS = ['firstName', 'lastName', 'email', 'password', 'job', 'phone', 'role', 'company'];

    public function __construct(DAO $dao, Request $request, Auth $auth, Http $http, string $jwtKey)
    {
        $this->dao = $dao;
        $this->request = $request;
        $this->auth = $auth;
        $this->http = $http;
        $this->jwtKey = $jwtKey;
    }

    /**
     * @OA\Post(
     *     path="/user",
     *     tags={"User"},
     *     summary="Add a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="User created"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Role or company not found"
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="User already exists"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function addUser(): void
    {

        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "firstName": "John",
        //     "lastName": "Doe",
        //     "email": "john.doe@gmail",
        //     "password": "password",
        //     "job": "Developer",
        //     "phone": "0123456789",
        //     "role": "Admin",
        //     "company": "Cube 3"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user data from the request body
        $firstName = $requestBody['firstName'];
        $lastName = $requestBody['lastName'];
        $email = $requestBody['email'];
        $password = $requestBody['password'];
        $job = $requestBody['job'];
        $phone = $requestBody['phone'];
        $role = $requestBody['role'];
        $company= $requestBody['company'];


        // get the company and role from the database
        try {
            $role = $this->dao->getOneBy(Role::class, ['id' => $role]);
            $company = $this->dao->getOneBy(Company::class, ['id' => $company]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the role is not found
        if (!$role) {
            $this->request->handleErrorAndQuit(404, new Exception('Role not found'));
        }

        // if the company is not found
        if (!$company) {
            $this->request->handleErrorAndQuit(404, new Exception('Company not found'));
        }

        // create a new user
        $user = new User($firstName, $lastName, $email, $password, $role, $job, $phone, $company);

        // add the user to the database
        try {
            $this->dao->add($user);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('User already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'User created');
    }


    /**
     * Get all users
     *
     * @OA\Get(
     *     path="/user/all",
     *     tags={"User"},
     *     summary="Get all users",
     *     description="Returns all users",
     *     @OA\Response(
     *         response="200",
     *         description="Users found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/UserResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="No users found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getUsers(): void
    {
        // get the users from the database
        try {
            $users = $this->dao->getAll(User::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if there are no users
        if (!$users) {
            $this->request->handleErrorAndQuit(404, new Exception('No users found'));
        }

        // get the users data
        $usersData = [];
        foreach ($users as $user) {
            $usersData[] = $user->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Users found', $usersData);
    }


    /**
     * Get user by ID
     *
     * @OA\Get(
     *     path="/user/{id}",
     *     tags={"User"},
     *     summary="Get user by ID",
     *     description="Returns a user by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User found",
     *         @OA\JsonContent(ref="#/components/schemas/UserResponse")
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getUserById(int $id): void
    {
        // get the user from the database by its id
        try {
            $user = $this->dao->getOneBy(User::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the user doesn't exist
        if (!$user) {
            $this->request->handleErrorAndQuit(404, new Exception('User not found'));
        }

        // get the user data
        $userData = $user->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'User found', $userData);
    }



    /**
     * Update user by ID
     *
     * @OA\Put(
     *     path="/user/{id}",
     *     tags={"User"},
     *     summary="Update user by ID",
     *     description="Updates a user by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data to update",
     *         @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User updated"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="User already exists"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function updateUser(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
        // {
        //     "firstName": "John",
        //     "lastName": "Doe",
        //     "email": "john.doe@gmail",
        //     "password": "password",
        //     "job": "Developer",
        //     "phone": "0123456789",
        //     "role": "Admin",
        //     "company": "Cube 3"
        // }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateDataUpdate($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the user from the database by its id
        try {
            $user = $this->dao->getOneBy(User::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the user doesn't exist
        if (!$user) {
            $this->request->handleErrorAndQuit(404, new Exception('User not found'));
        }

        // get the user data from the request body
        $firstName = $requestBody['firstName'] ?? $user->getFirstName();
        $lastName = $requestBody['lastName'] ?? $user->getLastName();
        $email = $requestBody['email'] ?? $user->getEmail();
        $password = $requestBody['password'] ?? $user->getPassword();
        $job = $requestBody['job'] ?? $user->getJob();
        $phone = $requestBody['phone'] ?? $user->getPhone();
        $role = $requestBody['role'] ?? $user->getRole()->getName();
        $company = $requestBody['company'] ?? $user->getCompany()->getName();

        // get the company and role from the database
        try {
            $role = $this->dao->getOneBy(Role::class, ['id' => $role]);
            $company = $this->dao->getOneBy(Company::class, ['id' => $company]);

            if (!$role || !$company) {
                $this->request->handleErrorAndQuit(404, new Exception('Role or company not found'));
            }

        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // update the user data
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setJob($job);
        $user->setPhone($phone);
        $user->setRole($role);
        $user->setCompany($company);

        // update the user in the database
        try {
            $this->dao->update($user);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('User already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'User updated');
    }

    /**
     * Delete user by ID
     *
     * @OA\Delete(
     *     path="/user/{id}",
     *     tags={"User"},
     *     summary="Delete user by ID",
     *     description="Deletes a user by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User deleted"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteUser(int $id): void
    {
        // get the user from the database by its id
        try {
            $user = $this->dao->getOneBy(User::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the user doesn't exist
        if (!$user) {
            $this->request->handleErrorAndQuit(404, new Exception('User not found'));
        }

        // remove the user
        try {
            $this->dao->delete($user);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'User deleted');
    }


    /**
     * Login user
     *
     * @OA\Post(
     *     path="/user/login",
     *     tags={"User"},
     *     summary="Login user",
     *     description="Logs in a user",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User credentials",
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@gmail.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="User logged in",
     *         @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *        )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Incorrect password or company is not active"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function loginUser(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // get the user data from the request body
        $email = $requestBody['email'];
        $password = $requestBody['password'];

        // get the user from the database by its email
        try {
            $user = $this->dao->getOneBy(User::class, ['email' => $email]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the user doesn't exist
        if (!$user) {
            $this->request->handleErrorAndQuit(404, new Exception('User not found'));
        }

        // if the password is incorrect
        if ($this->auth->isValidPassword($password, $user->getPassword())) {
            $this->request->handleErrorAndQuit(401, new Exception('Incorrect password'));
        }

        $jwt = $this->auth->encodeJWT($email, $this->jwtKey);

        $response = [
            'jwt' => $jwt
        ];

        // add the jwt to the user
        $user->setJwt($jwt);

        try {
            $this->dao->update($user);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, new Exception('Could not update user'));
        }

        // check if the user company is active
        if (!$user->getCompany()->getIsEnabled()) {
            $this->request->handleErrorAndQuit(401, new Exception('Company is not active'));
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'User logged in', $response);
    }


    /**
     * Get current user
     *
     * @OA\Get(
     *     path="/user/me",
     *     tags={"User"},
     *     summary="Get current user",
     *     description="Returns the current user",
     *     @OA\Response(
     *         response="200",
     *         description="User found",
     *         @OA\JsonContent(ref="#/components/schemas/UserResponse")
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="No users found"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     */
    public function getMe(): void
    {

        $token = $this->http->getAuthHeaderValue();

        if (!$token) {
            $this->request->handleErrorAndQuit(401, new Exception('Unauthorized'));
        }

        try {
            $user = $this->dao->getOneBy(User::class, ['jwt' => $token]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, new Exception('Could not get user'));
        }

        if (!$user) {
            $this->request->handleErrorAndQuit(404, new Exception('User not found'));
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'User found', $user->toArray());
    }


}