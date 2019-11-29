<?php


namespace App\models\TimeEntries;


use System\Model;

class TimeEntriesModel extends Model
{
    /**
     * [
     * "id_ticket" => 2,
     * "id_employee" => 1,
     * "from_date" => "2019-11-29 09:00:00",
     * "to_date" => "2019-11-29 10:00:00",
     * "note" => "My custom note",
     * ]
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        $this->db()
            ->table('times_entries')
            ->insert($data);
        return $this->db()->insertId();
    }

    public function getById(int $idEntry)
    {
        return $this->db()
            ->table('times_entries')
            ->where('id_time_entry', '=', $idEntry)
            ->get();
    }
}