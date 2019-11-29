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
    public function create($data)
    {
        $this->db()
            ->table('employees_assigned_tickets')
            ->insert($data);
    }

    public function getByTicketId($ticketId)
    {
        return $this->db()
            ->select('employees.id_employee, employees.first_name, employees.last_name, employees.email,employees_assigned_tickets.create_at')
            ->table('employees_assigned_tickets')
            ->innerJoin('employees', 'employees.id_employee', '=', 'employees_assigned_tickets.id_employee')
            ->where('id_ticket', '=', $ticketId)
            ->getAll();
    }

    public function remove($idTicket, $idEmployee)
    {
        $this->db()
            ->table('employees_assigned_tickets')
            ->where('id_ticket = ? AND id_employee = ?', [$idTicket, $idEmployee])
            ->delete();
    }
}