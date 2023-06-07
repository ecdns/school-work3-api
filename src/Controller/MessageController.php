<?php

namespace Controller;

use Entity\Message;
use Entity\Project;
use Entity\User;
use Exception;
use Service\DAO;
use Service\Request;

/**
 * @OA\Schema (
 *     schema="MessageRequest",
 *     required={"sender", "project", "message"},
 *     @OA\Property(property="sender", type="integer", example=1),
 *     @OA\Property(property="project", type="integer", example=1),
 *     @OA\Property(property="message", type="string", example="Hello world"),
 * )
 *
 * @OA\Schema (
 *     schema="MessageResponse",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="sender", type="integer", example=1),
 *     @OA\Property(property="project", type="integer", example=1),
 *     @OA\Property(property="message", type="string", example="Hello world"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", example="2021-01-01 00:00:00")
 * )
 */
class MessageController extends AbstractController
{

    private const REQUIRED_FIELDS = ['sender', 'project', 'message'];
    private DAO $dao;
    private Request $request;

    public function __construct(DAO $dao, Request $request)
    {
        $this->dao = $dao;
        $this->request = $request;
    }

    /**
     * @OA\Post(
     *     path="/message",
     *     tags={"Message"},
     *     summary="Add a new message",
     *     description="Add a new message",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Message object that needs to be added",
     *         @OA\JsonContent(ref="#/components/schemas/MessageRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message created"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Message already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function addMessage(): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

//         it will look like this:
//        {
//            "sender": 1,
//            "project": 1,
//            "message": "Hello world"
//        }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the message data from the request body
        $sender = $requestBody['sender'];
        $project = $requestBody['project'];
        $message = $requestBody['message'];

        //get the sender and project from the database
        try {
            $sender = $this->dao->getOneBy(User::class, ['id' => $sender]);
            $project = $this->dao->getOneBy(Project::class, ['id' => $project]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }


        // create a new message
        $message = new Message($sender, $project, $message);

        // flush the entity manager
        try {
            $this->dao->add($message);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Message already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(201, 'Message created');

    }

    /**
     * @OA\Get(
     *     path="/message/all",
     *     tags={"Message"},
     *     summary="Get all messages",
     *     description="Get all messages",
     *     @OA\Response(
     *         response=200,
     *         description="Messages found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/MessageResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getMessages(): void
    {
        // get all the licenses from the database
        try {
            $messages = $this->dao->getAll(Message::class);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($messages as $message) {
            $response[] = $message->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Messages found', $response);
    }


    /**
     * @OA\Get(
     *     path="/message/{id}",
     *     tags={"Message"},
     *     summary="Get a message by id",
     *     description="Get a message by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the message to get",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message found",
     *         @OA\JsonContent(ref="#/components/schemas/MessageResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getMessageById(int $id): void
    {
        // get the license from the database by its id
        try {
            $message = $this->dao->getOneBy(Message::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the license is not found
        if (!$message) {
            $this->request->handleErrorAndQuit(404, new Exception('Message not found'));
        }

        // set the response
        $response = $message->toArray();

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Message found', $response);
    }

    //getMessageByProjectId
    /**
     * @OA\Get(
     *     path="/message/project/{messageId}",
     *     tags={"Message"},
     *     summary="Get a message by project id",
     *     description="Get a message by project id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the project to get messages from",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Messages found",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/MessageResponse")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Messages not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getMessageByProject(int $messageId): void
    {
        // get the license from the database by its id
        try {
            $messages = $this->dao->getBy(Message::class, ['project' => $messageId]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // set the response
        $response = [];
        foreach ($messages as $message) {
            $response[] = $message->toArray();
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Messages found', $response);
    }

    /**
     * @OA\Put(
     *     path="/message/{id}",
     *     tags={"Message"},
     *     summary="Update a message by id",
     *     description="Update a message by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the message to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Message object that needs to be updated",
     *         @OA\JsonContent(ref="#/components/schemas/MessageRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message updated"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request data"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Message already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function updateMessage(int $id): void
    {
        // get the request body
        $requestBody = file_get_contents('php://input');

        // it will look like this:
//         {
//             "sender" : 1,
//             "project" : 1,
//             "message" : "Hello world"
//         }

        // decode the json
        $requestBody = json_decode($requestBody, true);

        // validate the data
        if (!$this->validateData($requestBody, self::REQUIRED_FIELDS)) {
            $this->request->handleErrorAndQuit(400, new Exception('Invalid request data'));
        }

        // get the message from the database by its id
        try {
            $message = $this->dao->getOneBy(Message::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the message is not found
        if (!$message) {
            $this->request->handleErrorAndQuit(404, new Exception('Message not found'));
        }

        // get the message data from the request body
        $sender = $requestBody['sender'] ?? $message->getSender();
        $project = $requestBody['project'] ?? $message->getProject();
        $message = $requestBody['message'] ?? $message->getMessage();

        //get the sender and project from the database
        try {
            $sender = $this->dao->getOneBy(User::class, ['id' => $sender]);
            $project = $this->dao->getOneBy(Project::class, ['id' => $project]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // update the message
        $message->setSender($sender);
        $message->setProject($project);
        $message->setMessage($message);

        // flush the entity manager
        try {
            $this->dao->update($message);
        } catch (Exception $e) {
            $error = $e->getMessage();
            if (str_contains($error, 'constraint violation')) {
                $this->request->handleErrorAndQuit(409, new Exception('Message already exists'));
            }
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Message updated');

    }

    /**
     * @OA\Delete(
     *     path="/message/{id}",
     *     tags={"Message"},
     *     summary="Delete a message by id",
     *     description="Delete a message by id",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the message to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message deleted"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function deleteMessage(int $id): void
    {
        // get the message from the database by its id
        try {
            $message = $this->dao->getOneBy(Message::class, ['id' => $id]);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // if the message is not found
        if (!$message) {
            $this->request->handleErrorAndQuit(404, new Exception('Message not found'));
        }

        // remove the message
        try {
            $this->dao->delete($message);
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, $e);
        }

        // handle the response
        $this->request->handleSuccessAndQuit(200, 'Message deleted');
    }

}