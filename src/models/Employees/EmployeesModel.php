<?php


namespace App\models\Employees;


use System\Model;

class EmployeesModel extends Model
{
    /**
     * @param array $employee
     * @return int
     * [
     * "firstName"=>"Albert",
     * "lastName"=>"Hidalgo",
     * "email"=>"alhidalgodev@gmail.com",
     * "status"=>"active",
     * "password"=>"12345678",
     * "confirmPassword"=>"12345678",
     * "role" => "user",
     * ]
     */
    public function create(array $employee): int
    {
        $this->db()
            ->table('employees')
            ->insert($employee);
        return $this->db()->insertId();
    }

    public function getById($idEmployee)
    {
        return $this->db()
            ->table('employees')
            ->where('id_employee', '=', $idEmployee)
            ->get();
    }

    public function getAll($fields, $filter, $oderBy, $orderDir, $offset, $limit)
    {
        return $this->db()
            ->select($fields)
            ->table('employees')
            ->where($filter)
            ->orderBy($oderBy, $orderDir)
            ->offset($offset)
            ->limit($limit)
            ->getAll();
    }

    public function getByEmail(string $email)
    {
        return $this->db()
            ->table('employees')
            ->where('email', '=', $email)
            ->get();
    }

    public function update(int $idEmployee, array $employee): void
    {
        $this->db()
            ->table('employees')
            ->where('id_employee', '=', $idEmployee)
            ->update($employee);
    }

    public function getByToken($token){
        return $this->db()
            ->table('employees')
            ->where('token','=',$token)
            ->get();
    }
}