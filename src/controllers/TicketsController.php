<?php

use App\models\Employees\EmployeesModel;
use App\models\Tickets\TicketsModel;
use App\plugins\QueryStringPurifier;
use App\plugins\SecureApi;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class TicketsController extends Controller
{
    private $request;
    private $response;

    public function __construct()
    {
        new SecureApi();
        $this->request = Request::createFromGlobals();
        $this->response = new Response();
        $this->response->headers->set('content-type', 'application/json');
    }

    public function tickets($idTicket = null)
    {
        $ticketsModel = new TicketsModel();

        switch ($this->request->server->get('REQUEST_METHOD')) {
            case 'GET':
                if ($idTicket === null) {
                    $qString = new QueryStringPurifier();
                    $tickets = $ticketsModel->getAll($qString->getFields(),
                        $qString->fieldsToFilter(),
                        $qString->getOrderBy(),
                        $qString->getSorting(),
                        $qString->getOffset(),
                        $qString->getLimit());
                    $this->response->setContent(json_encode($tickets));
                } else {
                    $ticket = $ticketsModel->getById($idTicket);
                    $this->response->setContent(json_encode($ticket));
                }
                $this->response->setStatusCode(200)->send();
                break;
            case 'POST':
                $newTicket = json_decode(file_get_contents('php://input'), true);
                $this->isValidTicketStructure($newTicket);

                $newTicket = $this->formatterNewTicket($newTicket);
                $newTicketId = $ticketsModel->create($newTicket);
                $newTicket = $ticketsModel->getById($newTicketId);

                $this->response->setContent(json_encode($newTicket))->setStatusCode(201)->send();
                break;
            case 'PATCH':
                $ticket = json_decode(file_get_contents('php://input'), true);
                $ticketsModel->update($idTicket, $ticket);
                $ticket = $ticketsModel->getById($idTicket);

                $this->response->setContent(json_encode($ticket))->setStatusCode(201)->send();
                break;
            case 'DELETE':
                $ticketsModel->delete($idTicket);
                $this->response->setStatusCode(200)->send();
                break;
        }
    }

    private function isValidTicketStructure($newTicket)
    {
        $basicStructure = ['subject', 'id_employee', 'description'];
        $errors = [];
        if ($newTicket === null)
            $errors[] = "Not json found";

        if (is_array($newTicket)) {
            foreach ($newTicket as $key => $value) {
                if (!in_array($key, $basicStructure)) {
                    $errors[] = "$key not found in json";
                }
            }
        }

        if (count($errors) > 0) {
            $message = [
                "code" => 422,
                "message" => "Validation failed",
                "errors" => $errors
            ];
            $this->response->setContent(json_encode($message))->setStatusCode(422)->send();
            die();
        }
    }

    private function formatterNewTicket(array $newTicket): array
    {
        $formatter = [
            "subject" => $newTicket['subject'],
            "status" => "open",
            "id_employee" => $newTicket["id_employee"],
            "description" => $newTicket["description"]
        ];

        return $formatter;
    }
}