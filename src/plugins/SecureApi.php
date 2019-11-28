<?php

/**
 * Created by PhpStorm.
 * User: destroid
 * Date: 19/7/2019
 * Time: 1:13 PM
 */

namespace App\plugins;

use App\models\Employees\EmployeesModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureApi
{
    private $publicArea;
    private $request;
    private $response;
    private $acceptOrigin = (ENVIROMENT == 'dev') ? "http://localhost:8080" : "https://gibucket.a2hosted.com";

    public function __construct(bool $publicArea = false)
    {
        $this->publicArea = $publicArea;
        $this->request = Request::createFromGlobals();
        $this->response = new Response();

        $this->cors();

        if ($this->publicArea === false) {
            $this->isTokenValid();
        }
    }

    private function cors(): void
    {
        if ($this->request->server->get('REQUEST_METHOD') == 'OPTIONS') {
            header("Access-Control-Allow-Credentials", "true");
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
            header("Access-Control-Allow-Origin:$this->acceptOrigin");
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            die();
        }

        header("Access-Control-Allow-Credentials", "true");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
        header("Access-Control-Allow-Origin:$this->acceptOrigin");
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    }

    private function isTokenValid(): void
    {
        $employeeToken = str_replace('Bearer ', '', $this->request->headers->get('authorization'));
        $employeeModel = new EmployeesModel();
        $employee = $employee->getByToken($employeeToken);

        if (empty($employee)) {
            $this->response->setContent('Forbidden');
            $this->response->setStatusCode(403);
            $this->response->send();
            die();
        }
    }
}