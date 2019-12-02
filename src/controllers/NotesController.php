<?php

use App\models\Employees\EmployeesModel;
use App\models\Notes\NotesModel;
use App\plugins\QueryStringPurifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\plugins\SecureApi;

class NotesController extends Controller
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

    public function notes($idNote = null)
    {
        switch ($this->request->server->get('REQUEST_METHOD')) {
            case 'GET':
                $notesModel = new NotesModel();

                if ($idNote === null) {
                    $qString = new QueryStringPurifier();
                    $notes = $notesModel->getAll($qString->getFields(),
                        $qString->fieldsToFilter(),
                        $qString->getOrderBy(),
                        $qString->getSorting(),
                        $qString->getOffset(),
                        $qString->getLimit());
                    $this->response->setContent(json_encode($notes));
                } else {
                    $notes = $notesModel->getById($idNote);
                    $this->response->setContent(json_encode($notes));
                }
                $this->response->setStatusCode(200)->send();
                break;
            case 'POST':
                $employeesModel = new EmployeesModel();
                $notesModel = new NotesModel();

                $note = json_decode(file_get_contents('php://input'), true);

                $structureDiff = $this->getStructureDiff(['id_ticket', 'note'], $note);

                if (count($structureDiff) > 0) {
                    $this->sendValidationFailed($structureDiff);
                    return;
                }

                $employee = $employeesModel->getByToken(str_replace('Bearer ', '', $this->request->headers->get('authorization')));
                $newNote = $notesModel->create(
                    [
                        "id_employee" => $employee->id_employee,
                        "id_ticket" => $note["id_ticket"],
                        "note" => $note["note"]
                    ]
                );
                $newNote = $notesModel->getById($newNote);
                $this->response->setContent(json_encode($newNote))->setStatusCode(201)->send();
                break;
            case 'DELETE':
                $notesModel = new NotesModel();
                $note = $notesModel->getById($idNote);

                if ($this->employeeHasAuthorizationToDeleteTimeEntry($note)) {
                    $notesModel->delete($idNote);
                    $this->response->setStatusCode(200)->send();
                    return;
                }
                $this->sendForbiddenResponse('You role not have permission for due that');
                break;
        }
    }

    /**
     * Compare tow arrays structure for check is a request strcuture supply by
     * request.
     *
     * @param $strcuture
     * @param $targetStructure
     * @return array
     */
    private function getStructureDiff($strcuture, $targetStructure): array
    {
        $errors = [];
        if ($targetStructure === null || !is_array($targetStructure))
            $errors[] = "Not json found";

        foreach ($targetStructure as $key => $value) {
            if (!in_array($key, $strcuture)) {
                $errors[] = "$key not found in structure. $value";
            }
        }

        return $errors;
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


    /**
     * Check if the user request for delete have a permission to due the action
     *
     * The normal users no have permission for delete other users notes, only the admins can due this
     * @param stdClass $note
     * @return bool
     */
    private function employeeHasAuthorizationToDeleteTimeEntry(stdClass $note): bool
    {
        $employeesModel = new EmployeesModel();
        $employee = $employeesModel->getByToken(str_replace('Bearer ', '', $this->request->headers->get('authorization')));

        if ($employee->id_employee === $note->id_employee) {
            return true;
        } elseif ($employee->role === 'admin') {
            return true;
        }
        return false;
    }

    /**
     * Send 403 HTTP status code and body message
     * @param $bodyMessage
     */
    private function sendForbiddenResponse($bodyMessage){
        $message = [
            "code" => 403,
            "message" => $bodyMessage
        ];
        $this->response->setContent(json_encode($message))->setStatusCode(403)->send();
    }
}