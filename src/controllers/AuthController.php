<?php

use App\models\Employees\EmployeesModel;
use App\plugins\SecureApi;
use Ingenerator\Tokenista;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends Controller
{
    private $request;
    private $response;

    public function __construct()
    {
        new SecureApi(true);
        $this->request = Request::createFromGlobals();
        $this->response = new Response();
        $this->response->headers->set('content-type', 'application/json');
    }

    public function login()
    {
        if ($this->request->server->get('REQUEST_METHOD') === 'POST') {
            $requestUser = json_decode(file_get_contents('php://input'), true);
            $employeesModel = new EmployeesModel();
            $employee = $employeesModel->getByEmail($requestUser['email']);
            $this->employeeFound($employee);

            if (password_verify($requestUser['password'], $employee->password)) {

                unset($employee->password);
                $token = new Tokenista('Bearer');
                $token = $token->generate();
                $employee->token = $token;
                $employeesModel->update($employee->id_employee, ['token' => $token]);
                $this->response->setContent(json_encode($employee));
                $this->response->setStatusCode(200);
            } else {
                $message = [
                    "code" => 401,
                    "message" => "Unauthorized"
                ];
                $this->response->setContent(json_encode($message));
                $this->response->setStatusCode(401);
            }

            $this->response->send();
        }
    }

    public function employeeFound($user)
    {
        if (empty($user)) {
            $message = [
                "code" => 404,
                "message" => "User nof found"
            ];
            $this->response->setContent(json_encode($message))->setStatusCode(404)->send();
            die();
        }
    }
}