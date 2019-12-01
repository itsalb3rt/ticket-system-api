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
    private $acceptOrigin = '*';

    /**
     * This class use for check is the user send the authorization token
     * in headers, this not required in all areas of the app.
     *
     * Example: In Auth area is no required, login.
     *
     * SecureApi constructor.
     * @param bool $publicArea
     */
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

    /**
     * This mechanism works by sending an OPTIONS HTTP method with
     * Access-Control-Request-Method and Access-Control-Request-Headers in
     * the header to notify the server about the type of request it wants to send.
     * The response it retrieves determine if the actual request is allowed to be sent or not.
     */
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

    /**
     * this only check if the token have assigne user, if no exists in database
     * return 403 status code
     */
    private function isTokenValid(): void
    {
        $employeeToken = str_replace('Bearer ', '', $this->request->headers->get('authorization'));
        $employeeModel = new EmployeesModel();
        $employee = $employeeModel->getByToken($employeeToken);
        if (empty($employee)) {
            $message = [
                "code"=>403,
                "message"=>"Forbidden"
            ];
            $this->response->headers->set('content-type', 'application/json');
            $this->response->setContent(json_encode($message))->setStatusCode(403)->send();
            die();
        }
    }
}
