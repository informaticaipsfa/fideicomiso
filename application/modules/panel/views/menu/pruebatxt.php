<?php 
if (!defined('BASEPATH'))
   	exit('No direct script access allowed');


class pruebatxt extends CI_Model{

	    public function __construct(){
  		parent::__construct();
    	if(!isset($this->Dbpace)) $this->load->model('comun/Dbpace');
        $this->load->model('beneficiario/leerArchivo');
        $this->leerArchivo->conexion();
        $this->leerArchivo->cargar_data();
        $this->leerArchivo->reporte();
    }
    
     
}