<?php 
if (!defined('BASEPATH'))
   	exit('No direct script access allowed');


class pruebatxt extends CI_Model{

	var $sql_codigo = 0;
  	var $sql_status_prof = 0;
  	var $sql_cedula = 0;
  	var $sql_grado = 0;
  	var $sql_num_hijo = 0;
  	var $sql_porc_no_asc = 0;
  	var $sql_an_rec_ser = 0;
  	var $sql_mes_recon_ser = 0;
  	var $sql_dia_recon_ser = 0;
  	var $sql_fec_ult_ascenso = 0;
  	var $sql_fecha_ingreso = 0;
  	var $sql_apellidos = '';
  	var $sql_nombres = '';
  	var $sql_apellidos2 = '';
  	var $sql_nombres2 = '';
  	var $sql_categoria = '';

    public function __construct(){
  		parent::__construct();
    	if(!isset($this->Dbpace)) $this->load->model('comun/Dbpace');
    }
      	
    public function leerArchivo(){
   	 	
   	 	$fp = fopen("/home/mary/www/space/tmp/Fideicomitentes_2017.txt", "r");
    	while(!feof($fp)) {
			//$linea = fgets($fp);
			fscanf($fp,"%1d %2d %8ld %15s %15s %15s %15s %4d %2d %2d %2d %2d %2d %8d %8d %2s",$sql_codigo,$sql_status_prof,$sql_cedula,$sql_apellidos,$sql_nombres,$sql_apellidos2,$sql_nombres2,$sql_grado,$sql_num_hijo,$sql_porc_no_asc,$sql_an_rec_ser,$sql_mes_recon_ser,$sql_dia_recon_ser,$sql_fecha_ingreso,$sql_fec_ult_ascenso,$sql_categoria);
			$apellidos = sprintf("%-15s",$sql_apellidos.' '.$sql_nombres);
			$nombres = sprintf("%-15s",$sql_apellidos2.' '.$sql_nombres2);
			echo $apellidos, $nombres;
		 	
			insertarLinea($sql_codigo,$sql_status_prof,$sql_cedula,$apellidos,$nombres,$sql_grado,$sql_num_hijo,$sql_porc_no_asc,$sql_an_rec_ser,$sql_mes_recon_ser,$sql_dia_recon_ser,$sql_fecha_ingreso,$sql_fec_ult_ascenso,$sql_categoria);
		}

		fclose($fp);
    }
    			
	public function insertarLinea($sql_codigo,$sql_status_prof,$sql_cedula,$apellidos,$nombres,$sql_grado,$sql_num_hijo,$sql_porc_no_asc,$sql_an_rec_ser,$sql_mes_recon_ser,$sql_dia_recon_ser,$sql_fecha_ingreso,$sql_fec_ult_ascenso,$sql_categoria){
  			

   			$sInsert = 'INSERT INTO data_componente(
    			componente,
    			status_prof,
   	   			cedula,
      			apellidos,
      			nombres,
      			cod_grado,
      			num_hijos,
      			no_ascenso,
      			year_reconocidos,
      			mes_reconocidos,
      			dias_reconocidos,
      			fecha_ingreso,
      			fecha_ultimo_ascenso,
      			categoria
    		) VALUES (';

    		$sInsert .=
      		'\''. $sql_codigo . '\',
      		\'' . $sql_status_prof . '\',
      		\'' . $sql_cedula . '\',
      		\'' . $apellidos . '\',
      		\'' . $nombres . '\',
      		\'' . $sql_grado . '\',
      		\'' . $sql_num_hijo . '\',
      		\'' . $sql_porc_no_asc. '\',
      		\'' . $sql_an_rec_ser . '\',
      		\'' . $sql_mes_recon_ser . '\',
      		\'' . $sql_dia_recon_ser . '\',
      		\'' . $sql_fecha_ingreso . '\',
      		\'' . $sql_fec_ult_ascenso . '\',
      		\'' . $sql_categoria . '\')';
    
    		echo $sInsert;
    		$obj = $this->Dbpace->consultar($sInsert);
  		}
}