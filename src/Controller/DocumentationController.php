<?php

declare(strict_types=1);

namespace Controller;

use Exception;
use Service\Request;

class DocumentationController
{

    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getDocumentation(): void
    {
        // echo openapi.html page content
        try {
            echo file_get_contents(__DIR__ . '/../../openapi.html');
        } catch (Exception $e) {
            $this->request->handleErrorAndQuit(500, new Exception("Erreur de lecture de la documentation : " . $e->getMessage()));
        }
    }
}
