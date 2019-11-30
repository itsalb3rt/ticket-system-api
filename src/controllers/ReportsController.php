<?php

use App\models\Reports\ReportsModel;
use App\plugins\SecureApi;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ReportsController extends Controller
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

    public function employees($startDate,$endDate){
        $reportModel = new ReportsModel();
        $result = $reportModel->employeesBetweenDates($startDate,$endDate);
        $this->response->setContent(json_encode($result))->setStatusCode(200)->send();
    }
}