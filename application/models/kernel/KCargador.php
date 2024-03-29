<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * MamonSoft
 *
 * Kernel
 *
 * @package pace\application\modules\panel\model\beneficiario
 * @subpackage utilidad
 * @author Carlos Peña
 * @copyright Derechos Reservados (c) 2015 - 2016, MamonSoft C.A.
 * @link http://www.mamonsoft.com.ve
 * @since version 1.0
 */

class KCargador extends CI_Model{

  /**
  * @var MBeneficiario
  */
  var $Beneficiario = null;

  /**
  * @var Complejidad del Manejador (Driver)
  */
  var $Nivel = 0;

  /**
  * @var array
  */
  var $Resultado = array();

  /**
  * Iniciando la clase, Cargando Elementos Pace
  *
  * @access public
  * @return void
  */
  public function __construct(){
    parent::__construct();
    if(!isset($this->DBSpace)) $this->load->model('comun/DBSpace');
    $this->load->model('kernel/KCalculo');
    $this->load->model('kernel/KGenerador');

  }


  function ConsultarBeneficiario($id = '', $param = array()){
    $this->load->model('fisico/MBeneficiario');
    $this->MBeneficiario->ObtenerID('11953710');
    $this->KCalculo->Ejecutar($this->MBeneficiario);
    return $this->MBeneficiario;
  }

  /**
   * Generar Indices para procesos de lotes (Activos)
   *
   * Creación de tablas para los cruce en el esquema space como
   * tablacruce permite ser indexada para evaluar la tabla movimiento
   * tipos de movimiento [3,31,32] dando como resultado del crosstab
   * cedula | Deposito AA | Deposito Dia Adicionales | Deposito Garantias
   *
   *  ---------------------------------------------
   *  INICIANDO PROCESOS POR LOTES
   *  ---------------------------------------------
   *
   * @return  void
   */
  public function PrepararIndices($estatus = 201){
    $this->load->model('kernel/KSensor');
    $this->load->model('comun/DBSpace');

    $rs = $this->DBSpace->consultar(
            "select a.cedula,tipo_movimiento_id,case when f_contable<'2018-08-20' then round(monto/100000,2) else monto end as monto into temp mov from movimiento a,beneficiario b where a.cedula=b.cedula;
            DROP TABLE IF EXISTS space.tablacruce;
            CREATE TABLE space.tablacruce AS SELECT * FROM space.crosstab(
              'SELECT C.cedula, C.id, COALESCE(sum(monto),0)AS monto FROM (
              SELECT A.cedula, A.status_id, B.id FROM (select cedula,status_id
              from beneficiario WHERE status_id=" . $estatus . ") AS A, (SELECT id from tipo_movimiento t WHERE
                t.id IN (3,5,9,14,25,31,32) ) AS B) AS C
              LEFT JOIN mov m ON m.cedula=C.cedula AND C.id=m.tipo_movimiento_id
              WHERE C.status_id=" . $estatus . "
              GROUP BY C.cedula, C.id
              ORDER BY C.cedula, C.id' ) AS rs
              (cedula character varying(12),
              cap_banco numeric, -- CAPITAL EN BANCO
              anticipo numeric,  -- ANTICIPO
              fcap_banco numeric, -- FINIQUITO
              dif_asi_anti numeric, -- DIF. DE FINIQUITO
              anticipor numeric, -- REVERSO
              dep_adicional numeric, -- DEPOSITO ADICIONAL
              dep_garantia numeric -- DEPOSITO DE GARANTIA

            );
            CREATE INDEX tablacruce_cedula ON space.tablacruce (cedula);");

    return $rs;
  }



  public function IniciarLote($arr, $archivo, $autor){

    ini_set('memory_limit', '1024M'); //Aumentar el limite de PHP


    $this->load->model('comun/Dbpace');
    $this->load->model('kernel/KSensor');
    $this->load->model('fisico/MBeneficiario');
    $fecha = $arr->fe == ''?$arr->fha:$arr->fe;

    $fde = $arr->fde;

    $condicion = ' beneficiario.status_id= ' . $arr->sit;
    if($arr->com != "99") $condicion .= ' AND beneficiario.componente_id=' . $arr->com;
    if($arr->gra != "99") $condicion .= ' AND grado.codigo=' . $arr->gra;

    if( $fde != "") {
      if($arr->sit == 201){
        $condicion .= ' AND beneficiario.fecha_ingreso BETWEEN \'' . $arr->fde . '\' AND \'' . $arr->fha . '\'';
      }else{
        $condicion .= ' AND beneficiario.f_retiro_efectiva BETWEEN \'' . $arr->fde . '\' AND \'' . $arr->fha . '\'';
        //$condicion .= ' AND beneficiario.f_ult_modificacion BETWEEN \'' . $arr->fde . '\' AND \'' . $arr->fha . '\'';
      }
    }

    $sConsulta = "
      SELECT
        beneficiario.nombres, beneficiario.apellidos,
        beneficiario.cedula, fecha_ingreso,f_ult_ascenso, grado.codigo,grado.nombre as gnombre,
        beneficiario.componente_id, n_hijos, st_no_ascenso, beneficiario.status_id,
        tablacruce.cap_banco,tablacruce.dep_adicional,tablacruce.dep_garantia,
        tablacruce.fcap_banco, tablacruce.anticipo-tablacruce.anticipor AS anticipo,
        tablacruce.dif_asi_anti,
        st_profesion,anio_reconocido, mes_reconocido,dia_reconocido,beneficiario.status_id as status_id
        FROM
          beneficiario
        JOIN
          grado ON beneficiario.grado_id=grado.id
        LEFT JOIN space.tablacruce ON beneficiario.cedula=space.tablacruce.cedula
        WHERE " . $condicion . "";

    $con = $this->DBSpace->consultar($sConsulta);

    //echo $sConsulta;
    $this->asignarBeneficiario($con->rs, $arr->id, $fecha, $archivo, $autor);

    $this->evaluarLotesLinuxComando($archivo,  $arr->sit);
  }


  private function asignarBeneficiario($obj, $id, $fecha, $archivo, $autor){
    $this->load->model('kernel/KCalculoLote');
    $this->load->model('kernel/KDirectiva');
    $Directivas = $this->KDirectiva->Cargar($id); //Directivas
    $this->load->model('kernel/KPerceptron'); //Red Perceptron Aprendizaje de patrones
    $file = fopen("tmp/" . $archivo . ".csv","a") or die("Problemas");
    //$file_log = fopen("tmp/" . $archivo . ".log","a") or die("Problemas");

    $linea  = 'CEDULA;CODGRA;GRADO;CODFZA;COMPONENTE;APELLIDOS Y NOMBRES;FECHA DE INGRESO;';
    $linea .= 'TIEMPO DE SERVICIO;NUMERO DE HIJOS;FECHA ULTIMO ASCENSO;TIEMPO DE SERVICIO EN EL GRADO;SUELDO BASICO;';
    $linea .= 'FACTOR PRIMA TRANSPORTE;PRIMA TRANSPORTE;PRIMA TIEMPO DE SERVICIO;';
    $linea .= 'FACTOR PRIMA DESCENDENCIA;PRIMA DESCENDENCIA;FACTOR PRIMA DE PRODUC.;PRIMA DE PRODUCTIVIDAD;ESTATUS NO ASCENSO;';
    $linea .= 'PRIMA NO ASCENSO;FACTOR PRIMA PROF.;PRIMA PROF.;FACTOR PRIMA COMP.;PRIMA COMPENSACION ESPECIAL;';
    $linea .= 'SUELDO MENSUAL;ALI. BONO FIN AÑO;DIA BON. VAC.;ALICUOTA BONO VAC.;SUELDO INTEGRAL;ASIGNACION DE ANTIGUEDAD;';
    $linea .= 'DEP. BANCO;ANTICIPOS;GARANTIAS ACUM.;DIAS ADI. ACUM.;DIF. NO DEP. BANCO;GARANTIAS;DIAS ADICIONALES;FINIQUITO CAPITAL;DIF. ASIG. ANTIGUEDAD';
    fputs($file,$linea);
    fputs($file,"\n");
    foreach ($obj as $k => $v) {
      $Bnf = new $this->MBeneficiario;
      $this->KCalculoLote->Instanciar($Bnf, $Directivas);
      $linea = $this->generarConPatrones($Bnf,  $this->KCalculoLote, $this->KPerceptron, $fecha, $Directivas, $v);
      /**
      if($Bnf->cedula == "15472216" || $Bnf->cedula == "16009573"  || $Bnf->cedula == "15739493" ){
        $linea_log = $Bnf->cedula . ';' . $Bnf->ano_reconocido . ';' . $Bnf->mes_reconocido . ';' . $Bnf->dia_reconocido . ';' .
        $Bnf->tiempo_servicio . ';' . $Bnf->prima_tiemposervicio . ';' . $Bnf->asignacion_antiguedad;

        fputs($file_log, $linea_log);
        fputs($file_log,"\n");
      }
      **/

      fputs($file,$linea);
      fputs($file,"\n");
      unset($Bnf);
    }

    fclose($file);
    //fclose($file_log);
    return true;
  }



  /**
  * Generar Codigos por Patrones en la Red de Inteligencia
  *
  * @param MBeneficiario
  * @param KCalculoLote
  * @param KPerceptron
  * @param KDirectiva
  * @param object
  * @return void
  */
  private function generarConPatrones(MBeneficiario &$Bnf, KCalculoLote &$CalculoLote, KPerceptron &$Perceptron, $fecha, $Directivas, $v){
      $Bnf->cedula = $v->cedula;
      $Bnf->deposito_banco = $v->cap_banco; //Individual de la Red
      $Bnf->anticipo = $v->anticipo; //Anticipos
      $Bnf->estatus_activo = $v->status_id;
      $Bnf->finiquito = $v->fcap_banco; //Finiquito
      $Bnf->apellidos = $v->apellidos; //Individual del Objeto
      $Bnf->diferencia_asig_a = $v->dif_asi_anti; // Diferencia de Asignacion de Antiguedad
      $Bnf->nombres = $v->nombres; //Individual del Objeto
      $Bnf->garantias_acumuladas = $v->dep_garantia; //Individual del Objeto
      $Bnf->dias_adicionales_acumulados = $v->dep_adicional; //Individual del Objeto
      $Bnf->fecha_ingreso = $v->fecha_ingreso;
      $Bnf->numero_hijos = $v->n_hijos;
      $Bnf->no_ascenso = $v->st_no_ascenso;
      $Bnf->componente_id = $v->componente_id;
      $Bnf->componente_nombre = $Directivas['com'][$v->componente_id];
      $Bnf->grado_codigo = $v->codigo;
      $Bnf->grado_nombre = $v->gnombre;
      $Bnf->fecha_ultimo_ascenso = $v->f_ult_ascenso;
      $Bnf->fecha_retiro = $fecha;
      $Bnf->prima_profesionalizacion_mt = $v->st_profesion;
      $Bnf->ano_reconocido = $v->anio_reconocido;
      $Bnf->mes_reconocido = $v->mes_reconocido;
      $Bnf->dia_reconocido = $v->dia_reconocido;
      $Bnf->estatus_activo = $v->status_id;

      $patron = md5($v->fecha_ingreso.$v->n_hijos.$v->st_no_ascenso.$v->componente_id.
        $v->codigo.$v->f_ult_ascenso.$v->st_profesion.$v->anio_reconocido.$v->mes_reconocido.$v->dia_reconocido);

      //GENERADOR DE CALCULOS DINAMICOS
      if(!isset($Perceptron->Neurona[$patron])){
        $CalculoLote->Ejecutar();

        $segmentoincial = $Bnf->antiguedad_grado . ';' . $Bnf->sueldo_base . ';' . $Bnf->prima_transporte_mt . ';' .
                          $Bnf->prima_transporte . ';' . //$Bnf->prima_tiemposervicio_mt . ';' .
                          $Bnf->prima_tiemposervicio . ';' . $Bnf->prima_descendencia_mt . ';' .
                          $Bnf->prima_descendencia . ';' . $Bnf->prima_especial_mt . ';' .
                          $Bnf->prima_especial . ';' . $Bnf->no_ascenso . ';' .
                          $Bnf->prima_noascenso . ';' . $Bnf->prima_profesionalizacion_mt . ';' .
                          $Bnf->prima_profesionalizacion . ';' . $Bnf->prima_compensacion_especial_mt . ';' .
                          $Bnf->prima_compensacion_especial . ';' . $Bnf->sueldo_mensual . ';' .
                          $Bnf->aguinaldos . ';' . $Bnf->dia_vacaciones . ';' . $Bnf->vacaciones . ';' .
                          $Bnf->sueldo_integral . ';' . $Bnf->asignacion_antiguedad . ';';
        $segmentofinal =  $Bnf->garantias . ';' . $Bnf->dias_adicionales ;

        $Perceptron->Aprender($patron, array(
          'DIA_VAC' => $Bnf->dia_vacaciones,
          'T_SERVICIO' => $Bnf->tiempo_servicio,
          'A_ANTIGUEDAD' => $Bnf->asignacion_antiguedad,
          'S_INTEGRAL' => $Bnf->sueldo_integral,
          'SINCIAL' => $segmentoincial,
          'SFINAL' =>  $segmentofinal)
        );

        $linea = $this->generarLinea($Bnf);

      }else{
        $Bnf->dia_vacaciones =  $Perceptron->Neurona[$patron]['DIA_VAC'];
        $Bnf->tiempo_servicio = $Perceptron->Neurona[$patron]['T_SERVICIO'];
        $Bnf->asignacion_antiguedad = $Perceptron->Neurona[$patron]['A_ANTIGUEDAD'];
        $Bnf->sueldo_integral = $Perceptron->Neurona[$patron]['S_INTEGRAL'];
        $CalculoLote->GenerarNoDepositadoBanco();
        $linea = $this->generarLineaMemoria($Bnf, $Perceptron->Neurona[$patron]);
      }
      return $linea;

  }


  private function generarLinea($Bnf){

    return
        $Bnf->cedula . ';' .  // 1
        $Bnf->grado_codigo . ';' . // 2
        $Bnf->grado_nombre . ';' . // 3
        $Bnf->componente_id . ';' .  //4
        $Bnf->componente_nombre . ';' . // 5
        $Bnf->apellidos . ' ' . $Bnf->nombres . ';' .  // 6
        $Bnf->fecha_ingreso . ';' . // 7
        $Bnf->tiempo_servicio . ';' . // 8
        $Bnf->numero_hijos . ';' . // 9
        $Bnf->fecha_ultimo_ascenso . ';' . // 10
        $Bnf->antiguedad_grado . ';' . // 11
        $Bnf->sueldo_base . ';' . // 12
        $Bnf->prima_transporte_mt . ';' . // 13
        $Bnf->prima_transporte . ';' . // 14
        $Bnf->prima_tiemposervicio . ';' .  // 15
        $Bnf->prima_descendencia_mt . ';' . // 16
        $Bnf->prima_descendencia . ';' .  // 17
        $Bnf->prima_especial_mt . ';' . // 18
        $Bnf->prima_especial . ';' . // 19
        $Bnf->no_ascenso . ';' . // 20
        $Bnf->prima_noascenso . ';' . // 21
        $Bnf->prima_profesionalizacion_mt . ';' . // 22
        $Bnf->prima_profesionalizacion . ';' .  // 23
        $Bnf->prima_compensacion_especial_mt . ';' . // 24
        $Bnf->prima_compensacion_especial . ';' .  // 25
        $Bnf->sueldo_mensual . ';' . // 26
        $Bnf->aguinaldos . ';' . // 27
        $Bnf->dia_vacaciones . ';' . //28
        $Bnf->vacaciones . ';' . // 29
        $Bnf->sueldo_integral . ';' . // 30
        $Bnf->asignacion_antiguedad . ';' . // 31
        $Bnf->deposito_banco . ';' . // 32
        $Bnf->anticipo . ';' . // 33
        $Bnf->garantias_acumuladas  . ';' . // 34
        $Bnf->dias_adicionales_acumulados . ';' . // 35
        $Bnf->no_depositado_banco  . ';' .  // 36
        $Bnf->garantias . ';' .  // 37
        $Bnf->dias_adicionales . ';' . // 38
        $Bnf->finiquito . ';' . // 39
        $Bnf->diferencia_asig_a;  // 40


  }


  private function generarLineaMemoria($Bnf, $Recuerdo){
    return
        $Bnf->cedula . ';' .
        $Bnf->grado_codigo . ';' .
        $Bnf->grado_nombre . ';' .
        $Bnf->componente_id . ';' .
        $Bnf->componente_nombre . ';' .
        $Bnf->apellidos . ' ' . $Bnf->nombres . ';' .
        $Bnf->fecha_ingreso . ';' .
        $Bnf->tiempo_servicio . ';' .
        $Bnf->numero_hijos . ';' .
        $Bnf->fecha_ultimo_ascenso . ';' .
        $Recuerdo['SINCIAL'] .
        $Bnf->deposito_banco . ';' .
        $Bnf->anticipo . ';' .
        $Bnf->garantias_acumuladas  . ';' .
        $Bnf->dias_adicionales_acumulados . ';' .
        $Bnf->no_depositado_banco  . ';' .
        $Recuerdo['SFINAL']  . ';' .
        $Bnf->finiquito . ';' .
        $Bnf->diferencia_asig_a;

  }

  private function evaluarLotesLinuxComando($archivo, $situacion){
    $comando = "wc -l tmp/" . $archivo . ".csv | awk -F\  '{print $1}'"; //Contar lineas del archivo generadas
    exec($comando, $linea);
    $comando = "cd tmp/; zip " . $archivo . ".zip " . $archivo . ".csv";
    exec($comando);
    $comando = "cd tmp/; md5sum " . $archivo . ".csv  | awk -F\  '{print $1}'";
    exec($comando, $firma);
    $comando = "cd tmp/; awk -F\; '{SUMG += $37; SUMD += $38; SUMA += $36} END {printf \"%.2f\", SUMG; printf \";%.2f\", SUMD; printf \";%.2f\", SUMA}' " . $archivo . ".csv";
    exec($comando, $monto);
    $g_d = explode(";", $monto[0]);
    $comando = "cd tmp; du -sh " . $archivo . ".csv | awk  '{print $1}'";
    exec($comando, $peso);
    $time = date("Y-m-d H:i:s");


    //exec("cd tmp/; rm -rf " . $archivo . ".csv");

    $sInsert = 'INSERT INTO space.archivos (arch,tipo,peso,cert,regi,usua,gara,diaa,asig,fech,esta)
    VALUES (\'' . $archivo . '\',9,\'' . $peso[0] . '\',\'' . $firma[0] . '\',\'' . $linea[0] . '\',\'' .
    $_SESSION['usuario'] . '\',' . $g_d[0] . ',' . $g_d[1] . ',' . $g_d[2] . ',\'' . $time . '\',0);';


    $rs = $this->DBSpace->consultar($sInsert);

    $this->Resultado = array(
      'l' => $linea[0],
      'f' => $firma[0],
      'c' => $archivo,
      'g' => number_format($g_d[0], 2, ',','.'),
      'd' => number_format($g_d[1], 2, ',','.'),
      'a' => number_format($g_d[2], 2, ',','.'),
      'p' => $peso[0],
      't' => $time,
      'e' => $rs,
      'i' => $sInsert
    );
  }

  /**
  *
  * @param string
  * @param int | 0 Garantias | 1 Dias Adicionales
  * @param int Porcentaje de distribucíón de pago
  *
  * @retun bool
  */
  function GarantiasDiasAdicionales($archivo =  '',  $tipo = 0, $porce = 100){
    $fecha = Date("Y-m-d");

    switch ($tipo) {
      case 0:
        $columna = "37";
        $parametro = "G";
        $codigo = 33; //tipo de movimientos para calculos Garantias
        break;
      case 1:
        $columna = "38";
        $parametro = "D";
        $codigo = 30;  //Calculo de dias adicionales
        break;
      case 2:
        $columna = "36";
        $parametro = "A";
        $codigo = 8;  //Aporte de Asignacion
        break;
      default:
        $columna = "36";
        $parametro = "A";
        $codigo = 8;  //Aporte de Asignacion
        break;
    }

    $ruta = explode("/", BASEPATH);
    $c = count($ruta)-2;
    $r = '/';
    for ($i=1; $i < $c; $i++) {
      $r .= $ruta[$i] . '/';
    }

    $r .= 'tmp/';
    $porcen =  '$' . $columna;
    if($porce < 100){
      $porcen = '( $' . $columna . '* ' . $porce . ')/100';
    }

    $sub = substr($archivo, 24, 32);
    $file = $parametro . $archivo;

    $comando = 'cd tmp/; rm -rf '. $file . '; mkdir '. $file;
    exec($comando, $err);

    if($tipo == 2){
      //$comando = "cd tmp/; awk -F';' '{ for (x=1; x<=34; x++) {  printf \"%s;\", $x } printf \"\n\" }' " . $archivo . ".csv >>  " . $file . "/" . $file . ".csv";
      $comando = 'cd tmp/; awk -F\';\' \' $36 > 0 { for (x=1; x<=34; x++) {  printf "%s;", $x } SUM=' . $porcen . '; printf "%.2f\n", SUM } \' ' . $archivo . '.csv >> ' . $file . '/' . $file . '.csv';
      exec($comando, $firma);
    }else if($tipo == 1){
      //**SE MODIFICO INCLUYENDO CONDICION PARA QUE MONTO DA<0 NO SALGA EN EL REPORTE FINAL
      $comando = 'cd tmp/; awk -F\';\' \' $38 > 0 { for (x=1; x<=30; x++) {  printf "%s;", $x } SUM=' . $porcen . '; printf "%.2f\n", SUM } \' ' . $archivo . '.csv >> ' . $file . '/' . $file . '.csv';
      exec($comando, $firma);
     }else if($tipo == 0){
      $comando = 'cd tmp/; awk -F\';\' \' { for (x=1; x<=30; x++) {  printf "%s;", $x } SUM=' . $porcen . '; printf "%.2f\n", SUM } \' ' . $archivo . '.csv >> ' . $file . '/' . $file . '.csv';
      exec($comando, $firma);
     }
     
    


    $comando = 'cd tmp/' . $file . '/; awk -F\';\' \'{SUM+=$NF} END {printf "%.2f", SUM }\' ' . $file . '.csv';
    exec($comando, $monto);

    $comando = 'cd tmp/' . $file . '/; awk -F\';\' \'{printf $1 ";' . $sub . ';' . $codigo . ';" $NF ";'
    . $fecha . ';' . $fecha . ';' . $_SESSION['usuario'] . '\n" }\' ' . $file . '.csv >> ' . $sub . '.csv';
    exec($comando, $insert);

    $comando = 'cd tmp/' . $file . '/; du -sh ' .  $file . '.csv | awk  \'{print $1}\'';
    exec($comando, $peso);

    $comando = 'cd tmp/' . $file . '/; zip ' .  $file . '.zip ' .  $file . '.csv';
    exec($comando, $firma);

    $time = date("Y-m-d H:i:s");


    $sUpdate = 'UPDATE  space.archivos SET esta=1, tipo=' . $tipo . ' WHERE arch=\'' . $archivo . '\';';


    $rs = $this->DBSpace->consultar($sUpdate);



    $this->Resultado = array(
      'd' => number_format($monto[0], 2, ',','.'),
      'p' => $peso[0],
      't' => $time,
      'a' => $parametro . $archivo, //,
      //'rs' => $bash,
      'tipo' => $tipo
    );

    return true;
  }



  function ConsultarArchivos(){
    $s = 'SELECT * FROM  space.archivos WHERE esta=2';
    $obj = $this->DBSpace->consultar($s);
    $arr = array();
    if($obj->code == 0 ){
      foreach ($obj->rs as $clv => $val) {


        $arr[] = array(
          'id' => $val->arch,
          'tipo' => $val->tipo,
          'tipotexto' => $this->tipoMovimiento($val->tipo),
          'fecha' => $val->fech,
          'peso' => $val->peso,
          'usuario' => $val->usua,
          'registro' => $val->regi,
          'aporte' => $val->apor,
          'apertura' => $val->aper,
          'sub' => $sub = substr($val->arch, 24, 33)
        );
      }
    }
    return $arr;
  }

function tipoMovimiento($id) {
    $tipo = '';
    switch ($id) {
      case 0:
        $tipo = 'Garantias';
        # code...
        break;
      case 1:
        $tipo = 'Días Adicionales';
        # code...
        break;
      case 2:
        $tipo = 'Asignación de Antiguedad';
        # code...
        break;
      default:
        $tipo = 'Días Adicionales';
        # code...
        break;
    }
    return $tipo;
}


function listarResumen($llave, $tipo, $fecha){

  $sConsulta = '
  SELECT tb.cod, cmp.nombre, monto, cantidad  FROM (
  SELECT b.componente_id as cod, 
  COUNT(*) As cantidad, SUM(monto) As monto  FROM  movimiento a, beneficiario b
  WHERE a.cedula=b.cedula AND codigo=\'' . $llave . '\' 
  GROUP BY cod
  ORDER BY cod ) AS tb
  JOIN componente cmp ON cmp.id = tb.cod
  ORDER BY cmp.id
  ';

  //print_r($sConsulta);
  $obj = $this->DBSpace->consultar($sConsulta);
  $suma = 0;
  $cantidad = 0;
  $fila = '';
  $total = '';
  $descripcion = '';
  $fecha = substr($fecha, 0, 10); 
  $f =  explode('-',$fecha);

  switch ($tipo) {
      case 0:
        $descripcion = 'RESUMEN DE GARANTIAS ';
        break;
      case 1:
        $descripcion = 'RESUMEN DE DIAS ADICIONALES ';
        break;
      case 2:
        $descripcion = 'RESUMEN ASIGNACION DE ANTIGUEDAD ';
        break;
  }

  $table = "<CENTER><tr style='height:50px; background-color: #dddddd'><br><br><br><td  style='text-align: center;'><b>" .   $descripcion . $f[2] . "-" . $f[1] . "-" . $f[0] . "</b></td></br></br></br></tr><TABLE style=width:40%  cellspacing='0' border=1 celladding='0' ><tr style='height:50px; background-color: #dddddd'><td  style='text-align: center;'><b>CODIGO</b></td><td style='text-align: center;'><b>COMPONENTE</b></td><td style='text-align: center;'><b>CANTIDAD</b></td><td style='text-align: center;'><b>MONTO</b></td></tr>";

  if($obj->code == 0 ){
      foreach ($obj->rs as $clv => $val) {

        $fila .= '<tr style="height:50px"><td style="text-align: center;">' . $val->cod . '</td><td>' . $val->nombre . '</td><td style="text-align: right;">' . number_format($val->cantidad, 0, ',','.') . '</td><td style="text-align: right;">' . 
        number_format($val->monto, 2, ',','.') . '</td></tr>';
        $suma += $val->monto;
        $cantidad += $val->cantidad;
      }
      $total = '<tr style="background-color: #dddddd;"><td></td><td style="height:50px;"><b>TOTAL</b></td><td style="text-align: right;"><b>' . number_format($cantidad, 0, ',','.') . '</b></td><td style="text-align: right;"><b>' . number_format($suma, 2, ',','.') . '</b></td></tr></table></CENTER>';
      
      $imprimir = '<br><br><center><button onclick="imprimir()" id="btnPrint">Imprimir Resumen</button></center>
          <script language="Javascript">
              function imprimir(){
                  document.getElementById("btnPrint").style.display = "none";
                  window.print();
                  window.close();
              }</script>';
  }

  return $table . $fila . $total . $imprimir;
}

  /*
  *
    DROP TABLE space.archivos;
    CREATE TABLE space.archivos(
      oid serial NOT NULL,
      arch character varying(128) NOT NULL,
      tipo integer,
      peso character varying(16) NOT NULL,
      cert character varying(128) NOT NULL,
      regi integer,
      usua character varying(64) NOT NULL,
      fech timestamp without time zone,
      gara numeric,
      diaa numeric,
      asig numeric,
      esta integer,
      aper numeric,
      apor numeric,
      CONSTRAINT archivos_pkey PRIMARY KEY (oid)
    );

  */


  /**
  * Crear Txt Para los bancos e insertar movimientos
  *
  * @param string
  * @param int
  * @return array
  */
  function CrearTxtMovimientos( $archivo =  '', $tipo = 0){
    $fecha = Date("Y-m-d");


    $ruta = explode("/", BASEPATH);
    $c = count($ruta)-2;
    $r = '/';
    for ($i=1; $i < $c; $i++) {
      $r .= $ruta[$i] . '/';
    }

    $r .= 'tmp/' . $archivo . '/';
    $sub = substr($archivo, 25, 33);
   
    $file = $this->KGenerador->AperturaTXT($archivo, $sub, $tipo);
    $fils = $this->KGenerador->AporteTXT($archivo, $sub, $tipo);


    $comando = 'cd tmp/' . $archivo . '/; zip APERT' . $sub . '.zip APERT' . $sub . '.txt';
    exec($comando, $bash);

    $comando = 'cd tmp/' . $archivo . '/; zip APORT' . $sub . '.zip APORT' . $sub . '.txt';
    exec($comando, $bash);

    $comando = 'cd tmp/;time ./script.sh ' . $r . $sub . ' 2>&1';
    exec($comando, $bash);

    $sUpdate = 'UPDATE  space.archivos SET esta=2, aper=' . $file['c'] . ', apor=' . $fils['c'] . ' WHERE arch=\'' . substr($archivo, 1, 38) . '\';';


    $rs = $this->DBSpace->consultar($sUpdate);


    $this->Resultado = array(
      'a' => $archivo,
      'aper' =>  'APERT' . $sub . '.zip',
      'apor' =>  'APORT' . $sub . '.zip',
      'rs' => $bash,
      'd' => $file['d'],
      'caper' => $file['c'], //,
      'capro' => $fils['c']
    );
    return $this->Resultado;
  }


  function actualizarMovimiento($llave, $tipo, $fecha){

  $sConsulta = '
  SELECT tb.cod, cmp.nombre, monto, cantidad  FROM (
  SELECT b.componente_id as cod, 
  COUNT(*) As cantidad, SUM(monto) As monto  FROM  movimiento a, beneficiario b
  WHERE a.cedula=b.cedula AND codigo=\'' . $llave . '\' 
  GROUP BY cod
  ORDER BY cod ) AS tb
  JOIN componente cmp ON cmp.id = tb.cod
  ORDER BY cmp.id
  ';

  //print_r($sConsulta);
  $obj = $this->DBSpace->consultar($sConsulta);
  $suma = 0;
  $cantidad = 0;
  $fila = '';
  $total = '';
  $descripcion = '';
  $fecha = substr($fecha, 0, 10); 
  $f =  explode('-',$fecha);

  switch ($tipo) {
      case 0:
        $descripcion = 'RESUMEN DE GARANTIAS ';
        break;
      case 1:
        $descripcion = 'RESUMEN DE DIAS ADICIONALES ';
        break;
      case 2:
        $descripcion = 'RESUMEN ASIGNACION DE ANTIGUEDAD ';
        break;
  }

  $table = "<CENTER><tr style='height:50px; background-color: #dddddd'><br><br><br><td  style='text-align: center;'><b>" .   $descripcion . $f[2] . "-" . $f[1] . "-" . $f[0] . "</b></td></br></br></br></tr><TABLE style=width:40%  cellspacing='0' border=1 celladding='0' ><tr style='height:50px; background-color: #dddddd'><td  style='text-align: center;'><b>CODIGO</b></td><td style='text-align: center;'><b>COMPONENTE</b></td><td style='text-align: center;'><b>CANTIDAD</b></td><td style='text-align: center;'><b>MONTO</b></td></tr>";

  if($obj->code == 0 ){
      foreach ($obj->rs as $clv => $val) {

        $fila .= '<tr style="height:50px"><td style="text-align: center;">' . $val->cod . '</td><td>' . $val->nombre . '</td><td style="text-align: right;">' . number_format($val->cantidad, 0, ',','.') . '</td><td style="text-align: right;">' . 
        number_format($val->monto, 2, ',','.') . '</td></tr>';
        $suma += $val->monto;
        $cantidad += $val->cantidad;
      }
      $total = '<tr style="background-color: #dddddd;"><td></td><td style="height:50px;"><b>TOTAL</b></td><td style="text-align: right;"><b>' . number_format($cantidad, 0, ',','.') . '</b></td><td style="text-align: right;"><b>' . number_format($suma, 2, ',','.') . '</b></td></tr></table></CENTER>';
      
      $imprimir = '<br><br><center><button onclick="imprimir()" id="btnPrint">Imprimir Resumen</button></center>
          <script language="Javascript">
              function imprimir(){
                  document.getElementById("btnPrint").style.display = "none";
                  window.print();
                  window.close();
              }</script>';
  }

  return $table . $fila . $total . $imprimir;
}



  /**
   *
   * Creación de tablas para los cruce en el esquema space como
   * tablacruce permite ser indexada para evaluar la tabla movimiento
   * tipos de movimiento [3,31,32] dando como resultado del crosstab
   * cedula | Deposito AA | Deposito Dia Adicionales | Deposito Garantias
   *
   *  -----------------------------------------------------
   *  INICIANDO PROCESOS POR LOTES PARA EFECTOS DE ESTUDIO
   *  -----------------------------------------------------
   *
   * @return  void
   */
  public function IniciarLoteEstudiar($directiva, $fecha, $archivo, $autor, $limit){
    ini_set('memory_limit', '512M'); //Aumentar el limite de PHP

    $this->load->model('comun/Dbpace');
    $this->load->model('fisico/MBeneficiario');
    $con = $this->DBSpace->consultar("
      SELECT
        beneficiario.nombres, beneficiario.apellidos,
        beneficiario.cedula, fecha_ingreso,f_ult_ascenso, grado.codigo,grado.nombre as gnombre,
        beneficiario.componente_id, n_hijos, st_no_ascenso,
        tablacruce.cap_banco,tablacruce.dep_adicional,tablacruce.dep_garantia,
        st_profesion,anio_reconocido, mes_reconocido,dia_reconocido,beneficiario.status_id as status_id
        FROM
          beneficiario
        JOIN
          grado ON beneficiario.grado_id=grado.id
        LEFT JOIN space.tablacruce ON beneficiario.cedula=space.tablacruce.cedula
        WHERE beneficiario.status_id=201 LIMIT " . $limit . ";");

      $this->asignarBeneficiarioEstudiarPatron($con->rs, $directiva, $fecha, $archivo, $autor);

      //$this->evaluarLotesLinuxComando($archivo);
  }

  private function asignarBeneficiarioEstudiarPatron($obj, $id, $fecha, $archivo, $autor){
    $this->load->model('kernel/KCalculoLote');
    $this->load->model('kernel/KDirectiva');
    $Directivas = $this->KDirectiva->Cargar($id); //Directivas
    //print_r($Directivas);
    $this->load->model('kernel/KPerceptron'); //Red Perceptron Aprendizaje de patrones
    //$lst = array(); //
    //$file = fopen("tmp/" . $archivo . ".csv","a") or die("Problemas");

    //$linea = 'cedula;grado_codigo;grado;componente_id;componente;apellidos_nombres;fecha_ingreso;tiempo_servicio;n_hijos;fecha_ultimo_ascenso;antiguedad_grado;sueldo_base;';
    //$linea .= 'prima_tmt;prima_transporte;prima_smt;prima_tiemposervicio;prima_dmt;prima_descendencia;prima_emt;prima_especial;prima_nmt;';
    //$linea .= 'prima_noascenso;prima_pmt;prima_profesionalizacion;';
    //$linea .= 'sueldo_mensual;alicuota_aguinaldos;alicuota_vacaciones;sueldo_integral;asignacion_antiguedad;';
    //$linea .= 'deposito_banco;garantias_acumuladas;dias_adicionales_acumulados;no_depositado_banco;garantias;dias_adicionales';
    //fputs($file,$linea);
    //fputs($file,"\n");
    foreach ($obj as $k => $v) {
      $Bnf = new $this->MBeneficiario;
      $this->KCalculoLote->Instanciar($Bnf, $Directivas);

      //$linea = $this->generarConPatrones($Bnf,  $this->KCalculoLote, $this->KPerceptron, $fecha, $Directivas, $v);
      $linea = $this->generarSinPatrones($Bnf,  $this->KCalculoLote, $this->KPerceptron, $fecha, $Directivas, $v);

      //fputs($file,$linea);
      //fputs($file,"\n");
      //unset($Bnf);
    }

    //fclose($file);
    print_r($this->KPerceptron->NeuronaArtificial);
    echo count($this->KPerceptron->NeuronaArtificial) . '<br>';
  }

  /**
  * Generar Codigos por Patrones en la Red de Inteligencia
  *
  * @param MBeneficiario
  * @param KCalculoLote
  * @param KPerceptron
  * @param KDirectiva
  * @param object
  * @return void
  */
  private function generarSinPatrones(MBeneficiario &$Bnf, KCalculoLote &$CalculoLote, KPerceptron &$Perceptron, $fecha, $Directivas, $v){
      $Bnf->cedula = $v->cedula;
      $Bnf->deposito_banco = $v->cap_banco; //Individual de la Red
      $Bnf->apellidos = $v->apellidos; //Individual del Objeto
      $Bnf->nombres = $v->nombres; //Individual del Objeto
      $Bnf->garantias_acumuladas = $v->dep_garantia; //Individual del Objeto
      $Bnf->dias_adicionales_acumulados = $v->dep_adicional; //Individual del Objeto
      $Bnf->fecha_ingreso = $v->fecha_ingreso;
      $Bnf->numero_hijos = $v->n_hijos;
      $Bnf->no_ascenso = $v->st_no_ascenso;
      $Bnf->componente_id = $v->componente_id;
      $Bnf->componente_nombre = $Directivas['com'][$v->componente_id];
      $Bnf->grado_codigo = $v->codigo;
      $Bnf->grado_nombre = $v->gnombre;
      $Bnf->fecha_ultimo_ascenso = $v->f_ult_ascenso;
      $Bnf->fecha_retiro = $fecha;
      $Bnf->prima_profesionalizacion_mt = $v->st_profesion;
      $Bnf->ano_reconocido = $v->anio_reconocido;
      $Bnf->mes_reconocido = $v->mes_reconocido;
      $Bnf->dia_reconocido = $v->dia_reconocido;
      $Bnf->estatus_activo = $v->status_id;
      $patron = md5($v->fecha_ingreso.$v->n_hijos.$v->st_no_ascenso.$v->componente_id.
        $v->codigo.$v->f_ult_ascenso.$v->st_profesion.$v->anio_reconocido.$v->mes_reconocido.$v->dia_reconocido);

      //GENERADOR DE CALCULOS DINAMICOS
      //if(!isset($Perceptron->Neurona[$patron])){
        $CalculoLote->Ejecutar();

        $segmentoincial = $Bnf->sueldo_base . ';' . $Bnf->prima_transporte_mt . ';' .
                          $Bnf->prima_transporte . ';' . //$Bnf->prima_tiemposervicio_mt . ';' .
                          $Bnf->prima_tiemposervicio . ';' . $Bnf->prima_descendencia_mt . ';' .
                          $Bnf->prima_descendencia . ';' . $Bnf->prima_especial_mt . ';' .
                          $Bnf->prima_especial . ';' . $Bnf->prima_noascenso_mt . ';' .
                          $Bnf->prima_noascenso . ';' . $Bnf->prima_profesionalizacion_mt . ';' .
                          $Bnf->prima_profesionalizacion . ';' . $Bnf->prima_compensacion_mt . ';' .
                          $Bnf->prima_compensacion . ';' .$Bnf->sueldo_mensual . ';' .
                          $Bnf->aguinaldos . ';' . $Bnf->vacaciones . ';' . $Bnf->sueldo_integral . ';' . $Bnf->asignacion_antiguedad . ';';
        $segmentofinal =  $Bnf->garantias . ';' . $Bnf->dias_adicionales;

        $Perceptron->AprenderArtificial($patron, $Bnf->cedula, array(
          'T_SERVICIO' => $Bnf->tiempo_servicio,
          'A_ANTIGUEDAD' => $Bnf->asignacion_antiguedad,
          'S_INTEGRAL' => $Bnf->sueldo_integral,
          'SINCIAL' => $segmentoincial,
          'SFINAL' =>  $segmentofinal)
        );

        //$linea = $this->generarLinea($Bnf);

      //}else{
        //$Bnf->tiempo_servicio = $Perceptron->Neurona[$patron]['T_SERVICIO'];
        //$Bnf->asignacion_antiguedad = $Perceptron->Neurona[$patron]['A_ANTIGUEDAD'];
        //$Bnf->sueldo_integral = $Perceptron->Neurona[$patron]['S_INTEGRAL'];
        //$CalculoLote->GenerarNoDepositadoBanco();
        //$linea = $this->generarLineaMemoria($Bnf, $Perceptron->Neurona[$patron]);
        $linea = "";
      //}
      return $linea;
  }



  public function ConsultarGrupos($json){
    ini_set('memory_limit', '512M'); //Aumentar el limite de PHP
    $lst = array();
    $fecha = date("Y-m-d");
    $this->load->model('comun/Dbpace');
    $this->load->model('kernel/KSensor');
    $this->load->model('fisico/MBeneficiario');
    $this->load->model('kernel/KCalculoLote');
    $this->load->model('kernel/KDirectiva');
    $Directivas = $this->KDirectiva->Cargar('', $fecha); //Directivas
    $this->load->model('kernel/KPerceptron'); //Red Perceptron Aprendizaje de patrones


    $fde = $json->fde;
    $nom = $json->nom;
    $condicion = ' beneficiario.status_id=' . $json->sit;
    if($nom != "") $condicion .= ' AND (beneficiario.nombres ~* \'' . $json->nom . '\' OR apellidos ~* \'' . $json->nom . '\')';
    if($json->gra != "99") $condicion .= ' AND grado.codigo=' . $json->gra;
    if($json->com != "99") $condicion .= ' AND beneficiario.componente_id=' . $json->com;
    if( $fde != "") $condicion .= ' AND beneficiario.fecha_ingreso BETWEEN \'' . $json->fde . '\' AND \'' . $json->fha . '\'';

    $sConsulta = '
      SELECT
        beneficiario.nombres, beneficiario.apellidos,
        beneficiario.cedula, fecha_ingreso,f_ult_ascenso, grado.codigo,grado.nombre as gnombre,
        beneficiario.componente_id, n_hijos, st_no_ascenso,
        st_profesion,anio_reconocido, mes_reconocido,dia_reconocido
        FROM
          beneficiario
        JOIN
          grado ON beneficiario.grado_id=grado.id

        WHERE ' . $condicion . ' ;';

    $obj = $this->DBSpace->consultar($sConsulta);

    //echo $sConsulta;

    if ($obj->cant < 3000){
      if($fde == "" && $nom == "") {
        $lst = $this->generarMenoraMil($this->KCalculoLote, $this->KPerceptron, $fecha, $Directivas, $obj->rs);
      } else{
         $lst = $this->generarSinCalculos($obj->rs);
      }

    }else{

      if($fde == "" && $nom == ""){
        $lst = $this->generarMayoraMil($this->KCalculoLote, $this->KPerceptron, $fecha, $Directivas, $obj->rs);
      }else{
         $lst = $this->generarSinCalculosCsv($obj->rs);
      }

    }

    //fclose($file);
    //print_r($this->KPerceptron->NeuronaArtificial);
    //echo count($this->KPerceptron->NeuronaArtificial);

    return $lst;

      //$this->evaluarLotesLinuxComando($archivo);
  }


  private function generarMenoraMil(KCalculoLote &$CalculoLote, KPerceptron &$Perceptron, $fecha, $Directivas, $obj){
    $lst = array();
    foreach ($obj as $k => $v) {
      $Bnf = new $this->MBeneficiario;
      $this->KCalculoLote->Instanciar($Bnf, $Directivas);
      $linea = $this->generarConPatronesReporte($Bnf,  $this->KCalculoLote, $this->KPerceptron, $fecha, $Directivas, $v);
      $lst[] = array(
        'ced' => $Bnf->cedula,
        'nom' => $Bnf->apellidos . ' ' . $Bnf->nombres,
        'gra' => $Bnf->grado_nombre,
        'com' => $Bnf->componente_nombre,
        'fin' => $Bnf->fecha_ingreso,
        'tse' => $Bnf->tiempo_servicio,
        'sme' => $Bnf->sueldo_mensual,
        'sin' => $Bnf->sueldo_integral
      );
    }
    return $lst;
  }

  private function generarMayoraMil(KCalculoLote &$CalculoLote, KPerceptron &$Perceptron, $fecha, $Directivas, $obj){
    $lst = array();
    $firma = md5(date('Y-m-d H:i:s'));
    $file = fopen("tmp/" . $firma . ".csv","a") or die("Problemas");
    $linea = 'CEDULA;GRADO;COMPONENTE;BENEFICIARIO;TIEMPO DE SERVICIO;SUELDO MENSUAL;SUELDO INTEGRAL';
    fputs($file,$linea);
    fputs($file,"\n");

    foreach ($obj as $k => $v) {
      $Bnf = new $this->MBeneficiario;
      $this->KCalculoLote->Instanciar($Bnf, $Directivas);
      $lin = $this->generarConPatronesReporte($Bnf,  $this->KCalculoLote, $this->KPerceptron, $fecha, $Directivas, $v);
      $linea =
        $Bnf->cedula . ';' .
        $Bnf->grado_nombre . ';' .
        $Bnf->componente_nombre . ';' .
        $Bnf->apellidos . ' ' . $Bnf->nombres . ';' .
        $Bnf->tiempo_servicio . ';' .
        $Bnf->sueldo_mensual . ';' .
        $Bnf->sueldo_integral . ';';

      fputs($file,$linea);
      fputs($file,"\n");
      unset($Bnf);
    }
    fclose($file);

    $lst[] = array('file' => $firma . '.csv');
    return $lst;
  }

  private function generarSinCalculosCsv($obj){
    $lst = array();
    $firma = md5(date('Y-m-d H:i:s'));
    $file = fopen("tmp/" . $firma . ".csv","a") or die("Problemas");
    $linea = 'CEDULA;GRADO;COMPONENTE;BENEFICIARIO;FECHA INGRESO;TIEMPO DE SERVICIO';
    fputs($file,$linea);
    fputs($file,"\n");

    foreach ($obj as $k => $v) {
      $linea =
        $v->cedula . ';' .
        $v->apellidos . ' ' . $v->nombres . ';' .
        $v->fecha_ingreso . ';';
      fputs($file,$linea);
      fputs($file,"\n");
      unset($Bnf);
    }
    fclose($file);

    $lst[] = array('file' => $firma . '.csv');
    return $lst;
  }

  private function generarSinCalculos($obj){
    $lst = array();
    foreach ($obj as $k => $v) {
      $lst[] = array(
        'ced' => $v->cedula,
        'nom' => $v->apellidos . ' ' . $v->nombres,
        'fin' => $v->fecha_ingreso
      );
    }
    return $lst;
  }
  /**
  * Generar Codigos por Patrones en la Red de Inteligencia
  *
  * @param MBeneficiario
  * @param KCalculoLote
  * @param KPerceptron
  * @param KDirectiva
  * @param object
  * @return void
  */
  private function generarConPatronesReporte(MBeneficiario &$Bnf, KCalculoLote &$CalculoLote, KPerceptron &$Perceptron, $fecha, $Directivas, $v){
      $Bnf->cedula = $v->cedula;
      $Bnf->apellidos = $v->apellidos; //Individual del Objeto
      $Bnf->nombres = $v->nombres; //Individual del Objeto
      $Bnf->fecha_ingreso = $v->fecha_ingreso;
      $Bnf->numero_hijos = $v->n_hijos;
      $Bnf->no_ascenso = $v->st_no_ascenso;
      $Bnf->componente_id = $v->componente_id;
      $Bnf->componente_nombre = $Directivas['com'][$v->componente_id];
      $Bnf->grado_codigo = $v->codigo;
      $Bnf->grado_nombre = $v->gnombre;
      $Bnf->fecha_ultimo_ascenso = $v->f_ult_ascenso;
      $Bnf->fecha_retiro = $fecha;
      $Bnf->prima_profesionalizacion_mt = $v->st_profesion;
      $Bnf->ano_reconocido = $v->anio_reconocido;
      $Bnf->mes_reconocido = $v->mes_reconocido;
      $Bnf->dia_reconocido = $v->dia_reconocido;

      $patron = md5($v->fecha_ingreso.$v->n_hijos.$v->st_no_ascenso.$v->componente_id.
        $v->codigo.$v->f_ult_ascenso.$v->st_profesion.$v->anio_reconocido.$v->mes_reconocido.$v->dia_reconocido);

      //GENERADOR DE CALCULOS DINAMICOS
      if(!isset($Perceptron->Neurona[$patron])){
        $CalculoLote->Ejecutar();

        $segmentoincial = $Bnf->sueldo_base . ';' . $Bnf->prima_transporte_mt . ';' .
                          $Bnf->prima_transporte . ';' . //$Bnf->prima_tiemposervicio_mt . ';' .
                          $Bnf->prima_tiemposervicio . ';' . $Bnf->prima_descendencia_mt . ';' .
                          $Bnf->prima_descendencia . ';' . $Bnf->prima_especial_mt . ';' .
                          $Bnf->prima_especial . ';' . $Bnf->prima_noascenso_mt . ';' .
                          $Bnf->prima_noascenso . ';' . $Bnf->prima_profesionalizacion_mt . ';' .
                          $Bnf->prima_profesionalizacion . ';' . $Bnf->prima_compensacion_mt . ';' .
                          $Bnf->prima_compensacion . ';' .$Bnf->sueldo_mensual . ';' .
                          $Bnf->aguinaldos . ';' . $Bnf->dia_vacaciones . ';' . $Bnf->vacaciones . ';' .
                          $Bnf->sueldo_integral . ';' . $Bnf->asignacion_antiguedad . ';';
        $segmentofinal =  $Bnf->garantias . ';' . $Bnf->dias_adicionales;

        $Perceptron->Aprender($patron, array(
          'DIA_VAC' => $Bnf->dia_vacaciones,
          'T_SERVICIO' => $Bnf->tiempo_servicio,
          'A_ANTIGUEDAD' => $Bnf->asignacion_antiguedad,
          'S_INTEGRAL' => $Bnf->sueldo_integral,
          'SINCIAL' => $segmentoincial,
          'SFINAL' =>  $segmentofinal)
        );


      }else{
        $Bnf->dia_vacaciones =  $Perceptron->Neurona[$patron]['DIA_VAC'];
        $Bnf->tiempo_servicio = $Perceptron->Neurona[$patron]['T_SERVICIO'];
        $Bnf->asignacion_antiguedad = $Perceptron->Neurona[$patron]['A_ANTIGUEDAD'];
        $Bnf->sueldo_integral = $Perceptron->Neurona[$patron]['S_INTEGRAL'];
        $CalculoLote->GenerarNoDepositadoBanco();
      }
      return true;

  }
}
