<?php


namespace App\models\Reports;


use System\Model;

class ReportsModel extends Model
{
    /**
     *
     * @param null $where ["from_date"=>"2019-11-30", "to_date" => "2019-12-1"]
     * @return array|bool|\Buki\Pdox|false|int|mixed|string|void|null
     */
    public function employeesBetweenDates($startDate,$endDate){
        return $this->db()
            ->select('id_ticket,times_entries.note,employees.first_name,employees.last_name, times_entries.from_date, times_entries.to_date')
            ->table('times_entries')
            ->innerJoin('employees','employees.id_employee','=','times_entries.id_employee')
            ->between('times_entries.create_at',$startDate,$endDate)
            ->orderBy('1')
            ->getAll();
    }
}