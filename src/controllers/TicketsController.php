<?php

use App\models\Employees\EmployeesModel;
use App\models\Tickets\TicketsModel;
use App\models\TimeEntries\TimeEntriesModel;
use App\plugins\QueryStringPurifier;
use App\plugins\SecureApi;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\models\EmployeesAssignedTickets\EmployeesAssignedTicketsModel;

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

    public function tickets($idTicket = null, $entity = null, $entityId = null)
    {
        $ticketsModel = new TicketsModel();
        $assignedEmployeesModel = new EmployeesAssignedTicketsModel();

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
                    foreach ($tickets as $ticket) {
                        $ticket->{'employees'} = $assignedEmployeesModel->getByTicketId($ticket->id_ticket);
                    }
                    $this->response->setContent(json_encode($tickets));
                } else {
                    $ticket = $ticketsModel->getById($idTicket);
                    if (!empty($ticket)) {
                        $ticket->{'employees'} = $assignedEmployeesModel->getByTicketId($ticket->id_ticket);
                    }
                    $this->response->setContent(json_encode($ticket));
                }
                $this->response->setStatusCode(200)->send();
                break;
            case 'POST':
                if ($entity === null) {
                    $this->createTicket();
                } else {
                    if ($entity === 'employee') {
                        $this->assignEmployeeToTicket($idTicket);
                    } elseif ($entity === 'time-entries') {
                        $this->setTimeEntriesToTicket($idTicket);
                    }
                }
                break;
            case 'PATCH':
                $ticket = json_decode(file_get_contents('php://input'), true);
                $ticketsModel->update($idTicket, $ticket);
                $ticket = $ticketsModel->getById($idTicket);

                $this->response->setContent(json_encode($ticket))->setStatusCode(201)->send();
                break;
            case 'DELETE':
                if ($entity !== null && $entityId !== null) {
                    if ($entity === 'employee') {
                        $assignedEmployeesModel->remove($idTicket, $entityId);
                    }
                } else {
                    $ticketsModel->delete($idTicket);
                }
                $this->response->setStatusCode(200)->send();
                break;
        }
    }

    private function createTicket()
    {
        $newTicket = json_decode(file_get_contents('php://input'), true);
        $ticketsModel = new TicketsModel();
        $this->isValidStructure(['subject', 'employees', 'description'], $newTicket);
        $assignedEmployess = $newTicket['employees'];

        $newTicket = $this->formatterNewTicket($newTicket);
        $newTicketId = $ticketsModel->create($newTicket);
        $newTicket = $ticketsModel->getById($newTicketId);

        $employeesAssignedTicketsModel = new EmployeesAssignedTicketsModel();

        foreach ($assignedEmployess as $idemployee) {
            $employeesAssignedTicketsModel->create(['id_employee' => $idemployee, 'id_ticket' => $newTicketId]);
        }

        $this->response->setContent(json_encode($newTicket))->setStatusCode(201)->send();
    }

    private function assignEmployeeToTicket($idTicket)
    {
        $employees = json_decode(file_get_contents('php://input'), true);
        $assignedEmployeesModel = new EmployeesAssignedTicketsModel();
        $ticketsModel = new TicketsModel();

        if (is_array($employees['employees'])) {
            foreach ($employees['employees'] as $employee) {
                $assignedEmployeesModel->create(["id_employee" => $employee, "id_ticket" => $idTicket]);
            }
            $ticket = $ticketsModel->getById($idTicket);
            $ticket->{'employees'} = $assignedEmployeesModel->getByTicketId($ticket->id_ticket);
            $this->response->setContent(json_encode($ticket))->setStatusCode(201)->send();
        }
    }

    private function setTimeEntriesToTicket($idTicket)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $employeesModel = new EmployeesModel();
        $employee = $employeesModel->getByToken(str_replace('Bearer ', '', $this->request->headers->get('authorization')));
        $this->isValidStructure(["from_date", "to_date", "note", "employees"], $data);

        $timeEntriesModel = new TimeEntriesModel();
        $newEntryId = $timeEntriesModel->create(
            [
                "id_ticket" => $idTicket,
                "id_employee" => $employee->id_employee,
                "from_date" => $data["from_date"],
                "to_date" => $data["to_date"],
                "note" => $data["note"]
            ]
        );

        if (!empty($data['employees'])) {
            $assignedEmployeesModel = new EmployeesAssignedTicketsModel();
            foreach ($data['employees'] as $employee) {
                $assignedEmployeesModel->create(["id_employee" => $employee, "id_ticket" => $idTicket]);
            }
        }

        $this->response->setContent(json_encode($timeEntriesModel->getById($newEntryId)))->setStatusCode(201)->send();
    }

    /**
     * Compare tow arrays structure for check is a request strcuture supply by
     * request.
     *
     * This method stop the complete script and return 422 status code with a json structure
     *
     * @param $strcuture
     * @param $targetStructure
     */
    private function isValidStructure($strcuture, $targetStructure)
    {
        $errors = [];
        if ($targetStructure === null)
            $errors[] = "Not json found";

        if (is_array($targetStructure)) {
            foreach ($targetStructure as $key => $value) {
                if (!in_array($key, $strcuture)) {
                    $errors[] = "$key not found in structure";
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
            "description" => $newTicket["description"]
        ];

        return $formatter;
    }
}