<?php
/**
 * Created by PhpStorm.
 * User: destroid
 * Date: 18/7/2019
 * Time: 10:00 PM
 */

namespace App\plugins;

use Symfony\Component\HttpFoundation\Request;

class QueryStringPurifier
{


    public function fieldsToFilter()
    {
        $request = new Request($_GET);
        if (count($request->query) == 1) {
            return null;
        } else {
            //Define the list of query string posible in que query string for not get one of this
            $omitFields = ['p', 'offset', 'limit', 'sort', 'sorting', 'fields', 'offset'];
            $data = [];
            foreach ($request->query as $key => $value) {
                if (!in_array($key, $omitFields)) {
                    $data[$key] = $request->query->get($key);
                }
            }
            return $data;
        }
    }

    public function fieldsToFilterInSerachServices()
    {
        $request = new Request($_GET);
        if (count($request->query) == 1) {
            return null;
        } else {
            //Nombre de campos que ya existen y que pueden venir en el queryString
            $omitFields = ['p', 'offset', 'limit', 'sort', 'sorting', 'fields', 'offset', 'query'];
            $data = [];
            foreach ($request->query as $key => $value) {
                if (!in_array($key, $omitFields)) {
                    $data[$this->getCompleteFieldName($key)] = $value;
                }
            }
            return $data;
        }
    }

    private function getCompleteFieldName(string $field): string
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
        $request = new Request($_GET);

        if ($request->query->get('fields')) {
            return $this->getPurifyFields($request->query->get('fields'));
        } else {
            return '*';
        }
    }

    public function getOrderBy()
    {
        $request = new Request($_GET);
        if ($request->query->get('sort')) {
            return strip_tags($request->query->get('sort'));
        } else {
            return '1';
        }
    }

    public function getSorting()
    {
        $request = new Request($_GET);
        if ($request->query->get('sorting')) {
            if (strtolower($request->query->get('sorting')) == 'desc') {
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
        $request = new Request($_GET);
        if ($request->query->get('limit')) {
            return strip_tags($request->query->get('limit'));
        } else {
            return null;
        }
    }

    public function getOffset()
    {
        $request = new Request($_GET);
        if ($request->query->get('offset')) {
            return strip_tags($request->query->get('offset'));
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
                $temp .= strip_tags($value);
                return;
            }
            $temp .= strip_tags($value) . ',';
        }
        return $temp;
    }
}