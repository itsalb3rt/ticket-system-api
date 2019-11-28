<?php
/**
 * Created by PhpStorm.
 * User: Usuario
 * Date: 1/10/2018
 * Time: 3:34 PM
 */
/**
 * this is a demo controller, you can modificate this, for example
 * if user enter on root domain, redirect to home
**/


class DefaultController extends Controller
{
    /**
     * https://myhost/app/default/index
     */
    public function index()
    {
        $data['framework_name'] = "Ligne Framework";
        $data['version'] = "2.1.13 Dev";
        $data['environment'] = "Dev";
        $data['date'] = "2019-11-23";
        $data['externalComponentsIncluded'] = ["Ligne/Error Handler","Izniburak/PDOX"];
        $data['autor'] = "Albert Eduardo Hidalgo Taveras";
        $this->setData($data);
        $this->render("index",'Ligne 2');
    }
}