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

                    foreach ($tickets as $ticket){
                        $ticket->{'employees'} = $this->getAssignedEmployees($ticket);
                    }
                    $this->response->setContent(json_encode($tickets));
                } else {
                    if ($entity === 'time-entries') {
                        $entries = $this->getTimeEntriesByIdTicket($idTicket);
                        $this->response->setContent(json_encode($entries));
                    } else if ($entity === null) {
                        $ticket = $this->getTicketById($idTicket);
                        if (!empty($ticket)) $ticket->{'employees'} = $this->getAssignedEmployees($ticket);
                        $this->response->setContent(json_encode($ticket));
                    }
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
                    } elseif ($entity === 'time-entries') {
                        $this->deleteTimesEntries($entityId);
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

        $structureDiff = $this->getStructureDiff(['subject', 'employees', 'description'], $newTicket);

        if(count($structureDiff) > 0 ){
            $this->sendValidationFailed($structureDiff);
        }else{
            $assignedEmployess = $newTicket['employees'];

            $newTicket = $this->formatterNewTicket($newTicket);
            $newTicketId = $ticketsModel->create($newTicket);
            $newTicket = $ticketsModel->getById($newTicketId);

            $this->saveAssigneEmployees($assignedEmployess, $newTicketId);

            $this->response->setContent(json_encode($newTicket))->setStatusCode(201)->send();
        }

    }

    private function assignEmployeeToTicket($idTicket)
    {
        $employees = json_decode(file_get_contents('php://input'), true);
        $ticketsModel = new TicketsModel();

        if (is_array($employees['employees'])) {
            $this->saveAssigneEmployees($employees['employees'], $idTicket);

            $ticket = $ticketsModel->getById($idTicket);
            $ticket->{'employees'} = $this->getAssignedEmployees([$ticket]);
            $this->response->setContent(json_encode($ticket))->setStatusCode(201)->send();
        }
    }

    private function setTimeEntriesToTicket($idTicket)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $employeesModel = new EmployeesModel();
        $employee = $employeesModel->getByToken(str_replace('Bearer ', '', $this->request->headers->get('authorization')));

        $structureDiff = $this->getStructureDiff(["from_date", "to_date", "note", "employees"], $data);

        if(count($structureDiff) > 0 ){
            $this->sendValidationFailed($structureDiff);
        }else{
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
    }

    private function employeeHasAuthorizationToDeleteTimeEntry(stdClass $entry): bool
    {
        $employeesModel = new EmployeesModel();
        $employee = $employeesModel->getByToken(str_replace('Bearer ', '', $this->request->headers->get('authorization')));

        if ($employee->id_employee === $entry->id_employee) {
            return true;
        } elseif ($employee->role === 'admin') {
            return true;
        }
        return false;
    }

    /**
     * Compare tow arrays structure for check is a request strcuture supply by
     * request.
     *
     * @param $strcuture
     * @param $targetStructure
     * @return bool
     */
    private function getStructureDiff($strcuture, $targetStructure):array
    {
        $errors = [];
        if ($targetStructure === null || !is_array($targetStructure))
            $errors[] = "Not json found";

        foreach ($targetStructure as $key => $value) {
            if (!in_array($key, $strcuture)) {
                $errors[] = "$key not found in structure";
            }
        }

        return $errors;
    }

    /**
     * Retorned array in correct strcuture for insert data on database
     * @param array $newTicket
     * @return array
     */
    private function formatterNewTicket(array $newTicket): array
    {
        $formatter = [
            "subject" => $newTicket['subject'],
            "status" => "open",
            "description" => $newTicket["description"]
        ];

        return $formatter;
    }

    /**
     * Return all employees assigned on ticket(s), this use on a single ticket
     * and group ticket
     * @param stdClass $ticket
     * @return array
     */
    private function getAssignedEmployees(stdClass $ticket): array
    {
        $assignedModel = new EmployeesAssignedTicketsModel();
        return $assignedModel->getByTicketId($ticket->id_ticket);
    }

    /**
     * Return all time entries by id ticket
     * @param int $idTicket
     * @return stdClass
     */
    private function getTimeEntriesByIdTicket(int $idTicket): array
    {
        $timeEntriesModel = new TimeEntriesModel();
        $employeeModel = new EmployeesModel();
        $entries = $timeEntriesModel->getByTicketId($idTicket);

        foreach ($entries as $entry) {
            $entry->{'employee'} = $employeeModel->getById($entry->id_employee, 'first_name,last_name');
        }
        return $entries;
    }

    /**
     * Return ticket data by ticket id on request of ticket/{id}
     * @param int $idTicket
     * @return array|bool|\Buki\Pdox|false|int|mixed|string|void|null
     */
    private function getTicketById(int $idTicket)
    {
        $ticketsModel = new TicketsModel();
        $ticket = $ticketsModel->getById($idTicket);
        return $ticket;
    }

    private function deleteTimesEntries(int $entityId): void
    {
        $timeEntriesModel = new TimeEntriesModel();
        $entry = $timeEntriesModel->getById($entityId);

        if ($this->employeeHasAuthorizationToDeleteTimeEntry($entry)) {
            $timeEntriesModel->delete($entityId);
            $this->response->setStatusCode(200)->send();
        }

        $message = [
            "code" => 403,
            "message" => "You role not have permission for due that"
        ];
        $this->response->setContent(json_encode($message))->setStatusCode(403)->send();
    }

    /**
     * Save all employees assigne on ticket, this receive array of ids of
     * employees
     *
     * Example: [1 , 2, 10]
     *
     * @param $employees
     * @param $idTicket
     */
    private function saveAssigneEmployees(array $employees, int $idTicket)
    {
        $employeesAssigned = new EmployeesAssignedTicketsModel();
        foreach ($employees as $idemployee) {
            $employeesAssigned->create(['id_employee' => $idemployee, 'id_ticket' => $idTicket]);
        }
    }

    /**
     * Send HTTP 422 code and json with the errors on validation
     *
     * @param $errors
     */
    private function sendValidationFailed($errors)
    {
        $message = [
            "code" => 422,
            "message" => "Validation failed",
            "errors" => $errors
        ];
        $this->response->setContent(json_encode($message))->setStatusCode(422)->send();
    }

}