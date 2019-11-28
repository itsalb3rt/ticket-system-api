<?php


use App\models\Employees\EmployeesModel;
use App\plugins\QueryStringPurifier;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class EmployeesController extends Controller
{
    private $request;
    private $response;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();
        $this->response = new Response();
        $this->response->headers->set('content-type', 'application/json');
    }

    public function employees($idEmployee = null)
    {
        $employeesModel = new EmployeesModel();
        switch ($this->request->server->get('REQUEST_METHOD')) {
            case 'GET':
                if ($idEmployee === null) {
                    $qString = new QueryStringPurifier();
                    $employes = $employeesModel->getAll($qString->getFields(),
                        $qString->fieldsToFilter(),
                        $qString->getOrderBy(),
                        $qString->getSorting(),
                        $qString->getOffset(),
                        $qString->getLimit());
                    $this->response->setContent(json_encode($employes));
                } else {
                    $service = $employeesModel->getById($idEmployee);
                    $this->response->setContent(json_encode($service));
                }
                $this->response->setStatusCode(200)->send();
                break;
            case 'POST':
                $newEmployee = json_decode(file_get_contents('php://input'), true);
                $this->isValidEmail($newEmployee);
                $this->isPasswordSecure($newEmployee);

                $newEmployee = $this->getNewEmployeeFormatter($newEmployee);

                $newEmployeeId = $employeesModel->create($newEmployee);
                $createdEmployee = $employeesModel->getById($newEmployeeId);
                $this->response->setContent(json_encode($createdEmployee))->setStatusCode(201)->send();
                break;
            case 'PATCH':
                break;
        }
    }

    private function isValidEmail($employee)
    {
        if (isset($employee['email'])) {
            $validator = new EmailValidator();
            if ($validator->isValid($employee['email'], new RFCValidation()) === false) {
                $meesage = [
                    "code" => 422,
                    "message" => "Validation failed",
                    "errors" => ["Invalid email"]
                ];
                $this->response->setContent(json_encode($meesage))->setStatusCode(422)->send();
                die();
            }
        }
    }

    private function isPasswordSecure($newEmployee)
    {
        if (strlen($newEmployee['password']) < 8) {
            $meesage = [
                "code" => 422,
                "message" => "Validation failed",
                "errors" => ["The password is not secure"]
            ];
            $this->response->setContent(json_encode($meesage))->setStatusCode(422)->send();
            die();
        }
    }

    private function passwordHasing(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2I);
    }

    public function getNewEmployeeFormatter(array $newEmployee): array
    {
        $formatterEmployee = [
            "first_name" => $newEmployee["firstName"],
            "last_name" => $newEmployee["lastName"],
            "email" => $newEmployee["email"],
            "status" => $newEmployee["status"],
            "role" => "user",
            "password" => $this->passwordHasing($newEmployee['password'])
        ];
        return $formatterEmployee;
    }
}