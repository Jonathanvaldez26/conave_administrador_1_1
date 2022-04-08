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
        $extraHeader = <<<html
          <style>
            .logo{
              width:100%;
              height:150px;
              margin: 0px;
              padding: 0px;
            }
          </style>
html;
        $extraFooter =<<<html
        <script>
        $(document).ready(function(){
        $('#pickup-list').DataTable({
        "drawCallback": function( settings ) {
          $('.current').addClass("btn bg-gradient-danger btn-rounded").removeClass("paginate_button");
          $('.paginate_button').addClass("btn").removeClass("paginate_button");
          $('.dataTables_length').addClass("m-4");
          $('.dataTables_info').addClass("mx-4");
          $('.dataTables_filter').addClass("m-4");
          $('input').addClass("form-control");
          $('select').addClass("form-control");
          $('.previous.disabled').addClass("btn-outline-danger opacity-5 btn-rounded mx-2");
          $('.next.disabled').addClass("btn-outline-danger opacity-5 btn-rounded mx-2");
          $('.previous').addClass("btn-outline-danger btn-rounded mx-2");
          $('.next').addClass("btn-outline-danger btn-rounded mx-2");
          $('a.btn').addClass("btn-rounded");
        },
        "language": {
         
             "sProcessing":     "Procesando...",
             "sLengthMenu":     "Mostrar _MENU_ registros",
             "sZeroRecords":    "No se encontraron resultados",
             "sEmptyTable":     "Ningún dato disponible en esta tabla",
             "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
             "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
             "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
             "sInfoPostFix":    "",
             "sSearch":         "Buscar:",
             "sUrl":            "",
             "sInfoThousands":  ",",
             "sLoadingRecords": "Cargando...",
             "oPaginate": {
                 "sFirst":    "Primero",
                 "sLast":     "Último",
                 "sNext":     "Siguiente",
                 "sPrevious": "Anterior"
             },
             "oAria": {
                 "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                 "sSortDescending": ": Activar para ordenar la columna de manera descendente"
             }
         }
      });
     });
     </script>
html;
        $pickup = PickUpDao::getAll();
        $tabla= '';
        $modal = '';
        foreach ($pickup as $key => $value) {
            $tabla.=<<<html
              <tr>
                <!--td><input type="checkbox" name="borrar[]" value="{$value['id_pickup']}"/></td-->
                <!--td><h6 class="mb-0 text-sm">{$value['id_pickup']}</h6></td-->
                <td class="text-justify">
                  <h6 class="mb-0 text-sm">
                    <span class="fas fa-user"> </span> {$value['nombre_completo']}<br>
                    <span class="badge badge-secondary text-dark">#Folio {$value['id_pickup']}</span>
                    <hr>
                    <u><a href="mailto:{$value['email']}"><h6 class="mb-0 text-sm"><span class="fa fa-mail-bulk" style="font-size: 13px"></span> {$value['email']}</h6></a></u>
                    <u><a href="https://api.whatsapp.com/send?phone=52{$value['telefono']}&text=Buen%20d%C3%ADa,%20te%20contacto%20de%20parte%20del%20Equipo%20Grupo%20LAHE%20%F0%9F%98%80" target="_blank"><p class="text-sm font-weight-bold text-secondary mb-0"><span class="fa fa-whatsapp" style="font-size: 13px; color:green;"></span> {$value['telefono']}</p></a></u>
                  </h6>
                </td>
                <td><h6 class="mb-0 text-sm">{$value['fecha_cita']}</h6></td>
                <td><h6 class="mb-0 text-sm">{$value['hora_cita']}</h6></td>
                <td><h6 class="mb-0 text-sm">{$value['id_punto_reunion_pickup']}</h6></td>
                <td><h6 class="mb-0 text-sm">{$value['nombre_admin']}</h6></td>
                <td><h6 class="mb-0 text-sm">{$value['fecha_alta']}</h6></td>
                <td>
                  <button type="button" class="btn bg-gradient-primary btn-icon-only" data-toggle="modal" data-target="#Modal_Editar-{$value['id_pickup']}" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-original-title="Editar Registro de {$value['nombre_completo']} - {$value['id_pickup']}"><i class="fa fa-edit" aria-hidden="true"></i></button>
                  <button type="button" class="btn bg-gradient-danger btn-icon-only" onclick="borrarPickUp({$value['id_pickup']})" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Eliminar Registro de {$value['nombre_completo']} - {$value['id_pickup']}">
                      <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
html;
            $modal .= $this->generarModal($value);
        }
        $asistentes = PickUpDao::getAllAsistentes();
        $select_asist = '';
        foreach ($asistentes as $key => $value) {
            $select_asist .= <<<html
      <option value="{$value['utilerias_asistentes_id']}">{$value['nombre_completo']}</option>
html;
        }
        $permisoGlobalHidden = (Controller::getPermisoGlobalUsuario($this->__usuario)[0]['permisos_globales']) != 1 ? "style=\"display:none;\"" : "";
        $asistentesHidden = (Controller::getPermisosUsuario($this->__usuario, "seccion_asistentes", 1) == 0) ? "style=\"display:none;\"" : "";
        $vuelosHidden = (Controller::getPermisosUsuario($this->__usuario, "seccion_vuelos", 1) == 0) ? "style=\"display:none;\"" : "";
        $pickUpHidden = (Controller::getPermisosUsuario($this->__usuario, "seccion_pickup", 1) == 0) ? "style=\"display:none;\"" : "";
        $habitacionesHidden = (Controller::getPermisosUsuario($this->__usuario, "seccion_habitaciones", 1) == 0) ? "style=\"display:none;\"" : "";
        $cenasHidden = (Controller::getPermisosUsuario($this->__usuario, "seccion_cenas", 1) == 0) ? "style=\"display:none;\"" : "";
        $cenasHidden = (Controller::getPermisosUsuario($this->__usuario, "seccion_cenas", 1) == 0) ? "style=\"display:none;\"" : "";
        $aistenciasHidden = (Controller::getPermisosUsuario($this->__usuario, "seccion_asistencias", 1) == 0) ? "style=\"display:none;\"" : "";
        $vacunacionHidden = (Controller::getPermisosUsuario($this->__usuario, "seccion_vacunacion", 1) == 0) ? "style=\"display:none;\"" : "";
        $pruebasHidden = (Controller::getPermisosUsuario($this->__usuario, "seccion_pruebas_covid", 1) == 0) ? "style=\"display:none;\"" : "";
        $configuracionHidden = (Controller::getPermisosUsuario($this->__usuario, "seccion_configuracion", 1) == 0) ? "style=\"display:none;\"" : "";
        $utileriasHidden = (Controller::getPermisosUsuario($this->__usuario, "seccion_utilerias", 1) == 0) ? "style=\"display:none;\"" : "";
        View::set('permisoGlobalHidden', $permisoGlobalHidden);
        View::set('asistentesHidden', $asistentesHidden);
        View::set('vuelosHidden', $vuelosHidden);
        View::set('pickUpHidden', $pickUpHidden);
        View::set('habitacionesHidden', $habitacionesHidden);
        View::set('cenasHidden', $cenasHidden);
        View::set('aistenciasHidden', $aistenciasHidden);
        View::set('vacunacionHidden', $vacunacionHidden);
        View::set('pruebasHidden', $pruebasHidden);
        View::set('configuracionHidden', $configuracionHidden);
        View::set('utileriasHidden', $utileriasHidden);
        View::set('tabla', $tabla);
        View::set('modal', $modal);
        View::set('select_asist', $select_asist);
        View::set('header', $this->_contenedor->header($extraHeader));
        View::set('footer', $this->_contenedor->footer($extraFooter));
        View::render("pickup_all");
    }



}
