<?php
namespace App\controllers;
defined("APPPATH") OR die("Access denied");

use \Core\View;
use \Core\Controller;
use \App\models\Vuelos as VuelosDao;
use \App\models\Linea as LineaDao;
use \App\controllers\Mailer;

class PickUpLahe extends Controller{


    private $_contenedor;

    function __construct(){
        parent::__construct();
        $this->_contenedor = new Contenedor;
        View::set('header',$this->_contenedor->header());
        View::set('footer',$this->_contenedor->footer());
        if(Controller::getPermisosUsuario($this->__usuario, "seccion_pickup",1) == 0)
            header('Location: /Principal/');
    }

    public function getUsuario(){
        return $this->__usuario;
    }

    public function index()
    {

        View::render("pickup_all");
    }



}
