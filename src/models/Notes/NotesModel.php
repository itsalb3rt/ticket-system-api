<?php


namespace App\models\Notes;


use System\Model;

class NotesModel extends Model
{
    /**
     * [
     *      "id_employee" => 2,
     *      "id_ticket" => 1,
     *      "note" => "some note"
     * ]
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        $this->db()
            ->table('notes')
            ->insert($data);
        return $this->db()->insertId();
    }

    public function getById(int $idNote)
    {
        return $this->db()
            ->select('id_note,note,notes.create_at,notes.id_ticket,employees.id_employee,employees.first_name,employees.last_name,employees.email')
            ->table('notes')
            ->innerJoin('employees','employees.id_employee','=','notes.id_employee')
            ->where('id_note', '=', $idNote)
            ->get();
    }

    public function getAll($fields, $filter, $oderBy, $orderDir, $offset, $limit)
    {
        return $this->db()
            ->select('id_note,note,notes.create_at,notes.id_ticket,employees.id_employee,employees.first_name,employees.last_name,employees.email')
            ->table('notes')
            ->innerJoin('employees','employees.id_employee','=','notes.id_employee')
            ->where($filter)
            ->orderBy($oderBy, $orderDir)
            ->offset($offset)
            ->limit($limit)
            ->getAll();
    }

    public function delete(int $idNote):void
    {
        $this->db()
            ->table('notes')
            ->where('id_note', '=', $idNote)
            ->delete();
    }
}