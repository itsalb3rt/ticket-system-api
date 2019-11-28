<?php
/**
 * Created by PhpStorm.
 * User: destroid
 * Date: 18/7/2019
 * Time: 10:00 PM
 */

namespace App\plugins;


class QueryStringPurifier
{

    public function fieldsToFilter()
    {
        if (empty($_GET)) {
            return null;
        }else{
            //Nombre de campos que ya existen y que pueden venir en el queryString
            $omitFields = ['p','offset','limit','sort','sorting','fields','offset'];
            $data = [];
            foreach ($_GET as $key => $value){
                if(!in_array($key,$omitFields)){
                    $data[$key] =$value;
                }
            }
            return $data;
        }
    }

    public function fieldsToFilterInSerachServices()
    {
        if (empty($_GET)) {
            return null;
        }else{
            //Nombre de campos que ya existen y que pueden venir en el queryString
            $omitFields = ['p','offset','limit','sort','sorting','fields','offset','query'];
            $data = [];
            foreach ($_GET as $key => $value){
                if(!in_array($key,$omitFields)){
                    $data[$this->getCompleteFieldName($key)] =$value;
                }
            }
            return $data;
        }
    }

    private function getCompleteFieldName(string $field):string
    {
        $result = '';

        switch ($field) {
            case 'category_name':
                $result = 'categories.name';
                break;
            case 'category_id':
                $result = 'categories.id_category';
                break;
            case 'aptitude_id':
                $result = 'aptitudes.id_aptitud';
                break;
            case 'aptitude_name':
                $result = 'aptitudes.name';
                break;
        }

        return $result;
    }

    public function getFields()
    {
        if (isset($_GET['fields'])) {
            return $this->getPurifyFields($_GET['fields']);
        } else {
            return '*';
        }
    }

    public function getOrderBy()
    {
        if (isset($_GET['sort'])) {
            return strip_tags(stripslashes($_GET['sort']));
        } else {
            return '1';
        }
    }

    public function getSorting()
    {
        if (isset($_GET['sorting'])) {
            if (strtolower($_GET['sorting']) == 'desc') {
                return 'DESC';
            } else {
                return 'ASC';
            }
        } else {
            return 'DESC';
        }
    }

    public function getLimit()
    {
        if (isset($_GET['limit'])) {
            return strip_tags(stripslashes($_GET['limit']));
        } else {
            return null;
        }
    }

    public function getOffset()
    {
        if (isset($_GET['offset'])) {
            return strip_tags(stripslashes($_GET['offset']));
        } else {
            return null;
        }
    }

    private function getPurifyFields($fields)
    {
        $fields = explode(',', $fields);
        $temp = null;
        $fieldCount = count($fields) - 1;
        foreach ($fields as $key => $value) {
            if ($fieldCount == $key) {
                $temp .= strip_tags(stripslashes($value));
            } else {
                $temp .= strip_tags(stripslashes($value)) . ',';
            }
        }
        return $temp;
    }
}