<?php 
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * MamonSoft 
 *
 * Componente
 *
 * @package pace\application\modules\panel\model\beneficiario
 * @subpackage utilidad
 * @author Carlos Peña
 * @copyright Derechos Reservados (c) 2015 - 2016, MamonSoft C.A.
 * @link http://www.mamonsoft.com.ve
 * @since version 1.0
 */

 

function cargar(){
    alert('hola');
    //$handle = fopen("tmp/" . $sub . ".csv", "r");
    //$handle = fopen("tmp/" . $archivo , "r");
    $handle = fopen("tmp/Fideicomitentes_2017.txt", "r");

    while(($buffer = fgets($handle, 93)) !== false) {     
       fscanf($handle,"%1d %2d %8ld %25c %25c %4d %2d %2d %2d %2d %2d %8d %8d %2c",$sql_codigo,$sql_status_prof,$sql_cedula,$sql_apellido,$sql_nombre,$sql_grado,$sql_num_hijo,$sql_porc_no_asc,$sql_an_rec_ser,$sql_mes_recon_ser,$sql_dia_recon_ser,$sql_fecha_ingreso,$sql_fec_ult_ascenso,$sql_categoria);
       alert('hola');
       //sprintf("%1d %2d %8ld %25c",$sql_codigo, $sql_status_prof, $sql_cedula, $sql_apellido);
       
       insertarLinea();
    }
    fclose($handle);
}



function insertarLinea(){

    $sInsert = 'INSERT INTO data_componente(
      componente,
      status_prof,
      cedula,
      apellidos
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
      \'' . $sql_apellidos . '\',
      \'' . $sql_nombres . '\',
      \'' . $sql_grado . '\',
      \'' . $sql_num_hijo . '\',
      \'' . $sql_porc_no_asc. '\',
      \'' . $sql_an_rec_ser . '\',
      \'' . $sql_mes_recon_ser . '\',
      \'' . $sql_dia_recon_ser . '\',
      \'' . $sql_fecha_ingreso '\',
      \'' . $sql_fec_ult_ascenso . '\',
      \'' . $sql_categoria . '\',)';
    
    //echo $sInsert;
    $obj = $this->Dbpace->consultar($sInsert);


  }