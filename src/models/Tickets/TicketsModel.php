<?php


namespace App\models\Tickets;


use System\Model;

class TicketsModel extends Model
{
    public function create(array $ticket): int
    {
        $this->db()
            ->table('tickets')
            ->insert($ticket);
        return $this->db()->insertId();
    }

    public function getAll($fields, $filter, $oderBy, $orderDir, $offset, $limit)
    {
        return $this->db()
            ->select($fields)
            ->table('tickets')
            ->where($filter)
            ->orderBy($oderBy, $orderDir)
            ->offset($offset)
            ->limit($limit)
            ->getAll();
    }

    public function getById(int $idTicket)
    {
        return $this->db()
            ->table('tickets')
            ->where('id_ticket', '=', $idTicket)
            ->get();
    }

    public function update(int $idTicket, array $data):void
    {
        $this->db()
            ->table('tickets')
            ->where('id_ticket', '=', $idTicket)
            ->update($data);
    }

    public function delete(int $idTicket)
    {
        $this->db()
            ->table('tickets')
            ->where('id_ticket', '=', $idTicket)
            ->delete();
    }
}