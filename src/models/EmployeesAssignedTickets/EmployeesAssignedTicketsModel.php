<?php


namespace App\models\EmployeesAssignedTickets;


use System\Model;

class EmployeesAssignedTicketsModel extends Model
{
    /**
     * @param $data
     * [
     *  "id_employee" => 1,
     *  "id_ticket"=> 1
     * ]
     */
    public function create($data){
        $this->db()
            ->table('employees_assigned_tickets')
            ->insert($data);
    }
}