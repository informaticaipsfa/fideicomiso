<?php
defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set ( 'America/Caracas' );
define ('__CONTROLADOR', 'panel');
class Panel extends MY_Controller {

	var $_DIRECTIVA = array();

	/**
	* CONSTRUCTOR DEL PANEL
	*
	*/
	function __construct(){
		parent::__construct();
		$this->load->helper('url');
		$this->load->library('session');		
		if(!isset($_SESSION['usuario']))$this->salir();
	}

	public function verificar(){
		$this->load->model('usuario/Iniciar');
	}

	/**
	* 	----------------------------------
	*	Sección de la GUI
	* 	----------------------------------
	*/
	public function index(){
		$this->load->view("view_home");
	}

	

	public function fideicomitente(){
		$this->load->view("menu/beneficiario/fideicomitente");
	}

	public function beneficiario(){
		$this->load->model('beneficiario/MHistorialMovimiento');
		$data['Movimientos'] = $this->MHistorialMovimiento->listarTodo();

		$this->load->view("menu/beneficiario/beneficiario", $data);
	}

	public function asociarcuenta(){
		$this->load->view("menu/beneficiario/cuentabancaria");
	}

	public function reporte(){
		$this->load->model('beneficiario/MComponente');
		$data['componente'] = $this->MComponente->listarTodo();
		$this->load->view("menu/beneficiario/reporte", $data);
	}

	public function actualizar(){
		$this->load->view("menu/beneficiario/actualizarbeneficiario");
	}

	public function finiquitos(){
		$this->load->view("menu/beneficiario/finiquito");
	}
	public function historialsueldo(){
		$this->load->view("relaciondesueldo");
	}

	public function sueldolote(){
		$this->load->view('sueldolote');
	}

	public function ordenpago(){
		$this->load->view("menu/orden_pago/orden");
	}

	public function ordenpagoejecutada(){
		$this->load->view("menu/orden_pago/ejecutada");
	}

	public function consultarmovimiento(){
		$this->load->view("menu/beneficiario/consultarmovimiento");
	}

	public function medidajudicial(){
		$this->load->model('beneficiario/MEstado');
		$this->load->model('beneficiario/MParentesco');
		$this->load->model('beneficiario/MFormaPago');

		$data['Estado'] = $this->MEstado->listar();
		$data['Parentesco'] = $this->MParentesco->listar();
		$data['FormaPago'] = $this->MFormaPago->listar();
		$this->load->view("menu/beneficiario/medidajudicial", $data);
	}
	
	public function anticipo(){
		$this->load->model('beneficiario/MAnticipo');
		$data['lst'] = $this->MAnticipo->listarTodo();
		$this->load->view("menu/beneficiario/anticipo", $data);
	}


	public function directiva(){
		$this->load->model('kernel/KDirectiva');
		$this->load->model('beneficiario/MComponente');
		$data['lst'] = $this->KDirectiva->listarTodo();

		$data['componente'] = $this->MComponente->listarTodo();
		$this->load->view("menu/calculos/directiva", $data);
	}


	public function tasabcv(){
		$this->load->model("beneficiario/MTasabcv");
		$data['Tasa'] = $this->MTasabcv->listarTodo();
		$this->load->view("menu/calculos/tasabcv", $data);
	}


	public function aportecapital(){
		$this->load->model('kernel/KDirectiva');
		$this->load->model('beneficiario/MComponente');
		$data['lst'] = $this->KDirectiva->listarTodo();

		$data['componente'] = $this->MComponente->listarTodo();
		$this->load->view("menu/calculos/aportecapital", $data);
	}

	public function asignacionantiguedad(){
		$this->load->view("menu/calculos/asignacionantiguedad");
	}

	public function pagoaportes(){
		$this->load->model("kernel/KCargador");
		$data['Archivos'] = $this->KCargador->ConsultarArchivos();
		$this->load->view("menu/calculos/pagoaportes", $data);
	}

	public function resumenaporte($llave,$tipo,$fecha){
		$this->load->model("kernel/KCargador");
		$data = $this->KCargador->listarResumen($llave,$tipo,$fecha);
		echo $data;

	}


	public function interesescaidos(){
		$this->load->view("menu/calculos/interesescaidos");
	}

	public function interessemestral(){
		$this->load->view("menu/calculos/interessemestral");
	}

	public function calcinitereses(){
		$this->load->view("menu/calculos/calcinitereses");
	}
	public function reclamos(){
		$this->load->view("menu/reclamos/reclamos");
	}
	public function administrar(){
		$this->load->model("usuario/Usuario");
		$data['usuarios'] = $this->Usuario->listar();
		$this->load->view("menu/administracion/administrar", $data);
	}
	public function auditoria(){

		$this->load->view("menu/administracion/reporteauditoria");
	}

	/**
	*	
	*/
	public function calculadoraspace(){
		$this->load->view("menu/otros/calculadoraspace");
	}

	public function pruebatxt(){
		$this->load->view("menu/pruebatxt");
	}



	/**
	*	--------------------------------------
	*	Reportes Generales del Sistema
	*	--------------------------------------
	*/
	public function hojavida($cedula = '', $cod = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->MBeneficiario->obtenerID($cedula);
		$this->load->model('beneficiario/MOrdenPago');
		$this->MBeneficiario->HistorialOrdenPagos = $this->MOrdenPago->listarPorCedula($cedula);

		$data['Beneficiario'] = $this->MBeneficiario;
		$this->load->view('reporte/beneficiario/hoja_vida', $data);
	}

	public function cartaBanco($cedula = '', $cod = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->load->model('beneficiario/MHistorialMovimiento');

		$this->MBeneficiario->obtenerID($cedula);
		$this->MBeneficiario->HistorialDetalleMovimiento = $this->MHistorialMovimiento->listarDetalle($cedula);

		$data['Beneficiario'] = $this->MBeneficiario;
		$data['codigo'] = $cod;
		$this->load->view('reporte/beneficiario/carta_banco', $data);
	}

	public function cartaBancoFallecido($cedula = '', $cod = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->load->model('beneficiario/MHistorialMovimiento');
		$this->MBeneficiario->obtenerID($cedula);
		$data['Beneficiario'] = $this->MBeneficiario;
		$this->MBeneficiario->HistorialDetalleMovimiento = $this->MHistorialMovimiento->listarDetalle($cedula);
		$data['lst'] = $this->MBeneficiario->detalleMovimientoFamiliar($cedula, $cod);
		$data['codigo'] = $cod;
		$this->load->view('reporte/beneficiario/carta_banco_fallecido', $data);
	}

	public function medidaejecutada($cedula = '', $id = '', $cod = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->load->model('beneficiario/MMedidaJudicial');
		$this->MBeneficiario->obtenerID($cedula);

		$this->MBeneficiario->MedidaJudicial = $this->MMedidaJudicial->listarPorCodigo($cedula, $id);


		$data['Beneficiario'] = $this->MBeneficiario;
		$data['codigo'] = $cod;
		$this->load->view('reporte/beneficiario/medida_judicial_ejecutada', $data);
	}

	public function SuspenderMedidaJudicial($id){
		$this->load->model('beneficiario/MMedidaJudicial');
		$this->load->model('comun/Dbpace');
		$this->MMedidaJudicial->Suspender($id, 222);
		echo "Se ha suspendido la medida...";

	}
	public function cartaBancoFallecidoM($cedula = '', $codigo = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->load->model('beneficiario/MHistorialMovimiento');
		$this->MBeneficiario->obtenerID($cedula);
		$this->MBeneficiario->HistorialDetalleMovimiento = $this->MHistorialMovimiento->listarDetalle($cedula);
		$data['Beneficiario'] = $this->MBeneficiario;
		$data['codigo'] = $codigo;
		$data['lst'] = $this->MBeneficiario->detalleMovimientoFamiliar($cedula, $codigo);
		$this->load->view('reporte/beneficiario/asignacion_menos_diez', $data);
	}
	public function asignacionFAS($cedula = '', $codigo = '', $motivo = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->MBeneficiario->obtenerID($cedula);
		$data['Beneficiario'] = $this->MBeneficiario;
		$data['motivo'] = $motivo;
		$data['lst'] = $this->MBeneficiario->detalleMovimientoFamiliar($cedula, $codigo);
		$this->load->view('reporte/beneficiario/asignacion_fsas', $data);
	}
	public function CausaMuerte($cedula = '', $codigo = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->MBeneficiario->obtenerID($cedula);
		$data['Beneficiario'] = $this->MBeneficiario;

		$data['lst'] = $this->MBeneficiario->detalleMovimientoFamiliar($cedula, $codigo);
		$this->load->view('reporte/beneficiario/causa_muerte', $data);
	}

	public function ConsultoriaJuridica($cedula = '', $codigo = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->MBeneficiario->obtenerID($cedula);
		$data['Beneficiario'] = $this->MBeneficiario;
		$data['lst'] = $this->MBeneficiario->detalleMovimientoFamiliar($cedula, $codigo);
		$this->load->view('reporte/beneficiario/memorandum_fas', $data);
		
	}

	public function DiferenciaAntiguedad($cedula = '', $codigo = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->MBeneficiario->obtenerID($cedula);
		$data['Beneficiario'] = $this->MBeneficiario;
		$data['lst'] = $this->MBeneficiario->detalleMovimientoFamiliar($cedula, $codigo);
		$this->load->view('reporte/beneficiario/diferencia_asignacion', $data);
		
	}

	public function puntoCuenta($cedula = '', $codigo){
		$this->load->model('beneficiario/MBeneficiario');
		$this->MBeneficiario->obtenerID($cedula);
		$this->load->model('beneficiario/MOrdenPago');
		$this->MBeneficiario->HistorialOrdenPagos = $this->MOrdenPago->listarPorCedula($cedula);
		$data['Beneficiario'] = $this->MBeneficiario;
		$data['codigo'] = $codigo;
		$this->load->view('reporte/beneficiario/punto_cuenta_anticipo', $data);
	}

	public function impirmirAnticiposReportes($desde = '', $hasta = '', $componente = '', $nombre_componente = ''){
		$this->load->model('beneficiario/MOrdenPago');
		$data['Componente'] = $nombre_componente;
		$data['desde'] = $desde;
		$data['hasta'] = $hasta;
		$data['Anticipos'] = $this->MOrdenPago->listarPorFecha($desde, $hasta, $componente);
		$this->load->view('reporte/beneficiario/reporte_anticipos', $data);
	}

	public function cartaFinanzas($desde = '', $hasta = '', $componente = '', $nombre_componente = ''){
		$this->load->model('beneficiario/MOrdenPago');
		$this->load->model('utilidad/NumeroLetras');
		$data['Numero'] = $this->NumeroLetras;
		$data['Componente'] = $nombre_componente;
		$data['desde'] = $desde;
		$data['Anticipos'] = $this->MOrdenPago->listarPorFecha($desde, $hasta);
		$this->load->view('reporte/beneficiario/carta_anticipos_finanzas', $data);
	}


	public function medida_judicial($cedula = '', $id = '', $cod = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->load->model('beneficiario/MMedidaJudicial');
		$this->MBeneficiario->obtenerID($cedula);
		$this->MBeneficiario->MedidaJudicial = $this->MMedidaJudicial->listarPorCodigo($cedula, $id);

		$data['Beneficiario'] = $this->MBeneficiario;
		$data['id'] = $id;


		$this->load->view('reporte/beneficiario/medida_judicial', $data);
	}


	public function numeroLetras(){


	}

	/**
	*	---------------------------------------------
	*	FIN DE LOS REPORTES GENERALES DEL SISTEMA
	*	---------------------------------------------
	*/

	public function consultarBeneficiario($cedula = '', $fecha = ''){
		header('Content-Type: application/json');
		$this->load->model('beneficiario/MBeneficiario');
		$this->load->model('beneficiario/MHistorialMovimiento');

		$this->MBeneficiario->obtenerID($cedula, $fecha);
		//print_r($this->MBeneficiario);
		$this->load->model('beneficiario/MOrdenPago');
		$this->MBeneficiario->HistorialOrdenPagos = $this->MOrdenPago->listarPorCedula($cedula);
		$this->MBeneficiario->HistorialDetalleMovimiento = $this->MHistorialMovimiento->listarDetalle($cedula);

		echo json_encode($this->MBeneficiario);
	}

	public function consultarBeneficiarioJudicial($cedula = '', $fecha = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->load->model('beneficiario/MMedidaJudicial');

		$this->MBeneficiario->obtenerID($cedula, $fecha);
		$this->MBeneficiario->MedidaJudicial = $this->MMedidaJudicial->listarTodo($cedula);
		echo json_encode($this->MBeneficiario);
	}




	/**
	 * Generar Indices para procesos de lotes (Activos)
	 *
	 * Creación de tablas para los cruce en el esquema space como
	 * tablacruce permite ser indexada para evaluar la tabla movimiento
	 * tipos de movimiento [3,31,32] dando como resultado del crosstab
	 * cedula | Deposito AA | Deposito Dia Adicionales | Deposito Garantias
	 *
	 * -------------------------------------------------------------------
	 *	INICIANDO PROCESOS APORTE DE CAPITAL
	 * -------------------------------------------------------------------
	 *
	 * @return	void
	 */ 
	public function PrepararIndices($pregunta = 0, $estatus = 201){
		header('Content-Type: application/json');
		$this->load->model('kernel/KSensor');
		$this->load->model('kernel/KCargador');
		$fecha = date('d/m/Y H:i:s');
		$rs = '';
		if ($pregunta != 0) $rs = $this->KCargador->PrepararIndices($estatus);
		$json = array(
			'm' => "Fecha y Hora del Servidor: " . $fecha . " \n" . $this->KSensor->Duracion() . "... \n",
			'r' => $rs
		);
		echo json_encode($json);

	}

	public function GenerarCalculoAporteCapital(){
		//ini_set('memory_limit', '1024M');
		header('Content-Type: application/json');
		$this->load->model('kernel/KSensor');
		$fecha = date('d/m/Y H:i:s');
		$firma = md5($fecha);
		$data = json_decode($_POST['data']);
		//print_r($data);
		
		$this->load->model('kernel/KCargador');	
		//$data['id'] = 49;
		//$data['fe'] = "2016-01-31";
		//$data['estado_id'] = 203;
		//$data['sit'] = 203;
		//$data['com'] = 99;
		//$data['gra'] = 99;
		//$data['fde'] = "2016-01-01";
		//$data['fha'] = "2016-01-31";
		

 		$this->KCargador->IniciarLote($data, $firma, $_SESSION['usuario']);	
 		//$this->KCargador->IniciarLote((object)$data, '2016-01-01', $firma, $_SESSION['usuario']);	
 		
 		$mnt = $this->KCargador->Resultado['l'] - 1;
		$json = array(
			'd' => $data,
			'm' => "Fecha y Hora del Servidor: " . $fecha . 
					"\nFirma del Archivo: " . 	$firma .  
					"\nCantidad de Registros: " . $mnt  .
					"\nMonto Total de las Garantias: " . $this->KCargador->Resultado['g'] .
					"\nMonto Total de Dias Adicionales: " . $this->KCargador->Resultado['d'] .
					"\nMonto Total de Dif. Asignacion: " . $this->KCargador->Resultado['a'] .
					"\nPeso del Archivo: " . $this->KCargador->Resultado['p'] . " " . $this->KCargador->Resultado['f'] . "\n" .
					$this->KSensor->Duracion() . "... ",
			'z' => $firma .".zip",
			'json' => $this->KCargador->Resultado
		);
		echo json_encode($json);
		
	}
	public function GenerarCalculoAporteCapitalEstudiar(){
		//ini_set('memory_limit', '1024M');
		echo "<pre>";
		$this->load->model('kernel/KSensor');
		$fecha = date('d/m/Y H:i:s');
		$firma = md5($fecha); //PID

		$this->load->model('kernel/KCargador');			
 		$this->KCargador->IniciarLoteEstudiar(48, '2017-03-01', $firma, $_SESSION['usuario'], 100);	
 		//$mnt = $this->KCargador->Resultado['l'] - 1;		
	}

	public function ConsultarGrupos(){
		header('Content-Type: application/json');
		$this->load->model('kernel/KSensor');
		$fecha = date('d/m/Y H:i:s');
		$firma = md5($fecha);

		$this->load->model('kernel/KCargador');		
		
		$json = json_decode($_POST['data']);
 		$lst = $this->KCargador->ConsultarGrupos($json);	

 		echo json_encode($lst);

	}
	function Sensor(){
		$this->load->model('kernel/KSensor');
		$sum = 0;
		for ($i =0; $i< 100000; $i++){
			$sum += $i;
			echo "$sum<br>";
		}
		echo $this->KSensor->Duracion();
	}



	function LoteGarantiaDiasAdicionales($archivo = ''){
		header('Content-Type: application/json');
		$data = json_decode($_POST['data']);
		if($archivo == '' || $data->tipo < 0 || $data->tipo > 3){
			echo 'Está intentando acceder a un área restringida.';
		}else{
			$this->load->model("kernel/KCargador");
			$respuesta = $this->KCargador->GarantiasDiasAdicionales($archivo, $data->tipo, $data->porc);	
			echo json_encode($this->KCargador->Resultado);
		}
		
	}

	function CrearTxtMovimientos(){
		header('Content-Type: application/json');
		$data = json_decode($_POST['data']);
		
		
		if($data->id == '' || $data->tipo < 0 ){
			echo json_encode('Está intentando acceder en un área restringida.');
		}else{
			$this->load->model("kernel/KCargador");
			$respuesta = $this->KCargador->CrearTxtMovimientos($data->id, $data->tipo);	
			echo json_encode($this->KCargador->Resultado);
		}
		
	}

	function LoteAsignacionAntiguedad(){
		
	}

	function Apert(){
		//header('Content-Type: application/json');
		$this->load->model("kernel/KCargador");
		$respuesta = $this->KCargador->CrearTxtMovimientos('Ga9e84a49c765405bcdc6696f8f400a2b', 1);	
		//$this->load->model("kernel/KGenerador");
		//$respuesta = $this->KGenerador->AperturaTXT('G920d88b208a3b4380a31aa42c2f988d8', 'c2f988d8', 0);
		echo json_encode($respuesta);
	}


	function LoteConsultar(){
		header('Content-Type: application/json');
		$this->load->model("kernel/KCargador");
		$respuesta = $this->KCargador->ConsultarArchivos();
		echo json_encode($respuesta);
	}

	/**
	 *	---------------------------------------------
	 *	INICIANDO PROCESOS APORTE DE INTERESES
	 *	---------------------------------------------
	 *
	 * @return	void
	 */
	public function PreparaIntereses($mes,$anio){

	}



	/*
	 *	---------------------------------------------
	 *	FIN DE PROCESOS POR LOTES
	 *	---------------------------------------------
	 */

	public function cargarMilitarSAMAN($id = '', $valor = 0){
		$this->load->model('beneficiario/MBeneficiario');
		$lst = $this->MBeneficiario->CargarPersonaMilitar($id, $valor);
		echo json_encode($lst);
	}

	public function consultarHistorialBeneficiario($id = '', $fecha = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->load->model('beneficiario/MHistorialMovimiento');
		$this->load->model('beneficiario/MOrdenPago');

		$this->MBeneficiario->obtenerID($id, $fecha);
		$this->MBeneficiario->HistorialOrdenPagos = $this->MOrdenPago->listarPorCedula($id);
		$this->MBeneficiario->HistorialDetalleMovimiento = $this->MHistorialMovimiento->listarDetalle($id);

		$lst = $this->MBeneficiario->consultarHistorial($id);
		echo json_encode($lst);
		
	}

	public function consultarBeneficiarios($cedula = '', $fecha = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->load->model('beneficiario/MHistorialMovimiento');
		$this->load->model('beneficiario/MOrdenPago');

		$this->MBeneficiario->obtenerID($cedula, $fecha);

		$this->MBeneficiario->HistorialOrdenPagos = $this->MOrdenPago->listarPorCedula($cedula);
		$this->MBeneficiario->HistorialDetalleMovimiento = $this->MHistorialMovimiento->listarDetalle($cedula);


		echo "<pre>";
		print_r($this->MBeneficiario);
		//echo json_encode($this->MBeneficiario);
	}

	public function cargarGradoComponente($id = 1){
		$this->load->model('beneficiario/MGrado');
		echo json_encode($this->MGrado->porComponente($id));
	}

	public function actualizarBeneficiario(){
		$this->load->model('beneficiario/MBeneficiario');
		$data = json_decode($_POST['data']);
		$Persona = $data->Persona;
		$Bnf = new $this->MBeneficiario();

		$this->MBeneficiario->obtenerID($Persona->cedula);

		$Bnf->cedula = $Persona->cedula;
		$Bnf->grado_id = $Persona->grado;
		$Bnf->nombres = $Persona->nombres;
		$Bnf->apellidos = $Persona->apellidos;
		$Bnf->tiempo_servicio_db = $Persona->tservicio;
		$Bnf->fecha_ingreso = $Persona->fingreso;
		//$Bnf->estado_civil = $Persona->cedula;
		$Bnf->numero_hijos = $Persona->nhijos;
		$Bnf->fecha_ultimo_ascenso = $Persona->fuascenso;
		$Bnf->ano_reconocido = $Persona->arec;
		$Bnf->mes_reconocido = $Persona->mrec;
		$Bnf->dia_reconocido = $Persona->drec;
		$Bnf->no_ascenso = $Persona->noascenso;
		$Bnf->profesionalizacion = $Persona->profesionalizacion;
		$Bnf->sexo = $Persona->sexo;
		$Bnf->fecha_creacion = date("Y-m-d H:i:s");
		$Bnf->usuario_creador = $_SESSION['usuario'];
		$Bnf->fecha_ultima_modificacion = date("Y-m-d H:i:s");
		$Bnf->usuario_modificacion = $_SESSION['usuario'];
		if(isset($Persona->comision_servicio)){
			$this->load->model('beneficiario/MHistorialMovimiento');
			$obj["tipo_movimiento"]=28;
			$obj["monto"]=$Persona->comision_servicio;
			$obj["cedula"]=$Bnf->cedula;
			$obj["o_b"]=$Persona->o_b;
			$obj["f_contable"]=$Bnf->fecha_creacion;
			$obj["status_id"]=280;
			$obj["motivo_id"]=1;
			$obj["f_creacion"]=$Bnf->fecha_creacion;
			$obj["usuario_creador"]=$Bnf->usuario_creador;
			$obj["fecha"]=$Bnf->fecha_ultima_modificacion;
			$obj["usr_modificacion"]=$Bnf->usuario_modificacion;
			$obj["o_b"]=$Persona->o_b;
			$this->MHistorialMovimiento->Insertar($obj);

		}
		if(isset($Persona->monto_recuperado)){
			$this->load->model('beneficiario/MHistorialMovimiento');
			$obj["tipo_movimiento"]=35;
			$obj["monto"]=$Persona->monto_recuperado;
			$obj["cedula"]=$Bnf->cedula;
			$obj["o_b"]=$Persona->o_b;
			$obj["f_contable"]=$Bnf->fecha_creacion;
			$obj["status_id"]=280;
			$obj["motivo_id"]=1;
			$obj["f_creacion"]=$Bnf->fecha_creacion;
			$obj["usuario_creador"]=$Bnf->usuario_creador;
			$obj["fecha"]=$Bnf->fecha_ultima_modificacion;
			$obj["usr_modificacion"]=$Bnf->usuario_modificacion;
			$obj["o_b"]=$Persona->o_b;
			$this->MHistorialMovimiento->Insertar($obj);

		}

		//$this->MBeneficiario->InsertarHistorial(); //Creando la traza de la modificacion
		$Bnf->guardar();
		echo 'Proceso exitoso';

	}

	public function actualizarCuenta(){
		$this->load->model('beneficiario/MBeneficiario');
		$data = json_decode($_POST['data']);
		$Persona = $data->Persona;
		$Bnf = new $this->MBeneficiario();

		$this->MBeneficiario->obtenerID($Persona->cedula);

		$Bnf->cedula = $Persona->cedula;
		$Bnf->numero_cuenta = $Persona->numero_cuenta;
		$Bnf->fecha_creacion = date("Y-m-d H:i:s");
		$Bnf->usuario_creador = $_SESSION['usuario'];
		$Bnf->fecha_ultima_modificacion = date("Y-m-d H:i:s");
		$Bnf->usuario_modificacion = $_SESSION['usuario'];

		//$this->MBeneficiario->InsertarHistorial(); //Creando la traza de la modificacion
		$Bnf->actualizaCuenta();
		echo 'Proceso exitoso';

	}

	public function paralizarDesparalizar(){
		$this->load->model('beneficiario/MBeneficiario');
		$Bnf = new $this->MBeneficiario();
		$data = json_decode($_POST['data']);


		$this->MBeneficiario->obtenerID($data->Paralizar->id);
		//$this->MBeneficiario->InsertarHistorial(); //Creando la traza de la modificacion

		$Bnf->cedula = $data->Paralizar->id;
		$Bnf->estatus_activo = $data->Paralizar->estatus;
		$Bnf->motivo_paralizacion = $data->Paralizar->motivo;
		$Bnf->fecha_retiro = '';
		$Bnf->fecha_retiro_efectiva = '';
		$Bnf->observacion = 'PARALIZADO';
		if($data->Paralizar->estatus == '202'){
			$Bnf->observacion = 'RETIRADO';
			$Bnf->fecha_retiro = $data->Paralizar->motivo;
		    $Bnf->fecha_retiro_efectiva = $data->Paralizar->motivo;
		}
		if($data->Paralizar->estatus == '201')$Bnf->observacion = 'ACTIVO';
		$Bnf->ParalizarDesparalizar();

		echo 'Proceso exitoso';


	}

	public function consultarFiniquitos($cedula = '', $fecha = ''){
		$this->load->model('beneficiario/MBeneficiario');
		$this->load->model('beneficiario/MHistorialMovimiento');
		$this->MBeneficiario->obtenerID($cedula, $fecha);
		$this->MBeneficiario->HistorialDetalleMovimiento = $this->MHistorialMovimiento->listarDetalle($cedula)['Comparacion'];
		echo json_encode($this->MBeneficiario);
		//echo "<pre>";
		//print_r($this->MBeneficiario);
	}





	function listarComponente(){
		echo '<pre>';
		$this->load->model('beneficiario/MComponente');
		print_r( $this->MComponente->listar(1) );
	}



	function listarMovimientos(){
		$this->load->model('beneficiario/MHistorialMovimiento');
		$this->MHistorialMovimiento->listarPorComponente(1);
	}

	/**
	* 	-----------------------------
	*	FINIQUITOS admin | dg2010
	*	-----------------------------
	*/
	function registrarFiniquito(){
		$this->load->model('beneficiario/MFiniquito');
		$this->load->model('beneficiario/MPartidaPresupuestaria');
		$data['Motivo'] = $this->MFiniquito->listarMotivos();
		$data['Partida'] = $this->MPartidaPresupuestaria->listarTodo();
		$data['Directiva'] = $this->_DIRECTIVA;
		$this->load->view('menu/beneficiario/registrar_finiquito', $data);
	}

	function listarFiniquito(){
		echo '<pre>';

		$this->load->model('beneficiario/MFiniquito');
		$lst = $this->MFiniquito->listarCodigo('11953710', 'fb08e9fc3f3407bff9e6');

		//$this->MHistorialMovimiento->isertarReverso($lst);
		print_r( $lst );
	}

	/**
	*	Listar Partidas Presupuestarias
	*/
	function listarPartidaPresupuestaria(){
		echo '<pre>';
		$this->load->model('beneficiario/MPartidaPresupuestaria');
		print_r( $this->MPartidaPresupuestaria->listarTodo() );
	}

	function obtenerPartidaPresupuestaria($id = ''){
		$this->load->model('beneficiario/MPartidaPresupuestaria');
		echo json_encode( $this->MPartidaPresupuestaria->obtenerID($id) );
	}

	function obtenerFamiliares($id = ''){
		$this->load->model('beneficiario/MBeneficiario');
		echo json_encode( $this->MBeneficiario->CargarFamiliares($id) );
	}


	/**
	*  Cargar Persona ( X: Saman.- )
	*
	*  @param string
	*  @return json
	*/
	function cargarPersona($id = ''){
		$this->load->model('beneficiario/MBeneficiario');
		echo json_encode( $this->MBeneficiario->CargarPersona($id) );
	}

	function listarDetalleMovimiento($id = ''){
		echo "<pre>";
		$this->load->model('beneficiario/MHistorialMovimiento');
		print_r( $this->MHistorialMovimiento->listarDetalle($id) );
	}

	public function guardarFiniquito(){
		$this->load->model('beneficiario/MBeneficiario', 'Beneficiario');
		$this->load->model('beneficiario/MBeneficiario');
		$this->load->model('beneficiario/MHistorialMovimiento');
		$this->load->model('beneficiario/MMedidaJudicial');

		$json = json_decode($_POST['data']); // 'Hola Mundo'; //Object($_POST);
		$json->u_s = $_SESSION['usuario'];
		//print_r($json);
		$msj = 'Se debe definir una fecha para iniciar el proceso...';
		$fecha_aux = isset($json->f_r) ? $json->f_r : '';
		if($fecha_aux != ''){

			$this->MBeneficiario->obtenerID($json->i_d, '');
			$nombre = $this->MBeneficiario->nombres . ' ' . $this->MBeneficiario->apellidos;

			if($this->MBeneficiario->fecha_retiro == ''){

				$f = explode('/', $fecha_aux);
				$this->Beneficiario->fecha_retiro = $f[2] . '-' . $f[1] . '-' . $f[0];
				$json->f_r = $this->Beneficiario->fecha_retiro;
				$this->Beneficiario->cedula = $json->i_d;
				$this->Beneficiario->estatus_activo = 203;
				$this->Beneficiario->observacion = $json->o_b;

				//echo "<pre>";
				$codigo = $this->MHistorialMovimiento->InsertarDetalle($json);
				$this->Beneficiario->ActualizarPorMovimiento();
				//$this->MBeneficiario->InsertarHistorial();
				$this->MBeneficiario->insertarDetalle($json, $codigo);
				$this->MMedidaJudicial->ejecutarMedidas($json->i_d, 223, $codigo, $json->t_e);
				//print_r($json);
				$msj = 'Se ha procesado exitosamente el finiquito del beneficiario (' . $nombre . ')...';
			}else{
				$msj = 'El beneficiario  (' . $nombre . ') ya posee un finiquito...';
			}


		}

		print_r($msj);
 

	}

	public function reversarFiniquito($ced = '', $codigo = ''){
		$this->load->model('beneficiario/MBeneficiario', 'Beneficiario');
		$this->load->model('beneficiario/MBeneficiario');
		$this->load->model('beneficiario/MHistorialMovimiento');
		$this->load->model('beneficiario/MFiniquito');
		$this->load->model('beneficiario/MMedidaJudicial');



		//$this->MHistorialMovimiento->InsertarDetalle($json);
		$this->MBeneficiario->obtenerID($ced, '');
		$this->Beneficiario->fecha_retiro = '';
		$this->Beneficiario->cedula = $ced;
		$this->Beneficiario->estatus_activo = 201;
		$this->Beneficiario->observacion = 'REVERSO DE FINIQUITO';
		

		$lst = $this->MFiniquito->listarCodigo($ced, $codigo);
		$this->MHistorialMovimiento->isertarReverso($lst);
		
		//$this->MBeneficiario->InsertarHistorial(); //Creando la traza de la modificacion

		$this->MMedidaJudicial->ejecutarMedidas($ced, 220, $codigo);
		$this->Beneficiario->ActualizarPorMovimiento();

		echo 'Se ha procesado exitosamente el reverso';


	}

	public function crearOrdenPago(){

		$this->load->model('beneficiario/MOrdenPago');


		$data = json_decode($_POST['data']);
		//print_r($data);
		$this->MOrdenPago->cedula_beneficiario = $data->Anticipo->id;
		$this->MOrdenPago->cedula_afiliado = $data->Anticipo->id;

		$this->MOrdenPago->nombre = $data->Anticipo->nombre;
		$this->MOrdenPago->apellido = $data->Anticipo->apellido;
		$this->MOrdenPago->fecha =  date("Y-m-d");

		$this->MOrdenPago->motivo = $data->Anticipo->motivo;
		$this->MOrdenPago->estatus = $data->Anticipo->estatus;
		$this->MOrdenPago->tipo = $data->Anticipo->tipo;
		if (isset($data->Anticipo->tipo)){
			$this->MOrdenPago->tipoan = $data->Anticipo->tipoan;
		}
		$this->MOrdenPago->monto = $data->Anticipo->monto;
		$this->MOrdenPago->porcentaje = $data->Anticipo->porcentaje;

		$this->MOrdenPago->fecha_creacion =  date("Y-m-d H:i:s");
		$this->MOrdenPago->usuario_creacion = $_SESSION['usuario'];
		$this->MOrdenPago->fecha_modificacion =  date("Y-m-d H:i:s");
		$this->MOrdenPago->usuario_modificacion = $_SESSION['usuario'];


		$this->MOrdenPago->salvar();


		echo "Se registro el nuevo anticipo en estatus de pendiente";
	}

	public function crearMedidaJudicial($id = ''){

		$this->load->model('beneficiario/MMedidaJudicial');


		$data = json_decode($_POST['data']);
		$this->MMedidaJudicial->cedula = $data->MedidaJudicial->cedula;
		$this->MMedidaJudicial->estatus = '220';
		$this->MMedidaJudicial->numero_oficio = $data->MedidaJudicial->numero_oficio;
		$this->MMedidaJudicial->numero_expediente = $data->MedidaJudicial->numero_expediente;

		$this->MMedidaJudicial->tipo = $data->MedidaJudicial->tipo;
		$this->MMedidaJudicial->fecha = $data->MedidaJudicial->fecha;
		$this->MMedidaJudicial->observacion =  $data->MedidaJudicial->observacion;


		$this->MMedidaJudicial->porcentaje = $data->MedidaJudicial->porcentaje;
		$this->MMedidaJudicial->salario = $data->MedidaJudicial->salario;
		$this->MMedidaJudicial->mensualidades = $data->MedidaJudicial->mensualidades;
		$this->MMedidaJudicial->unidad_tributaria = $data->MedidaJudicial->ut;
		$this->MMedidaJudicial->monto = $data->MedidaJudicial->monto;

		$this->MMedidaJudicial->forma_pago = $data->MedidaJudicial->forma_pago;
		$this->MMedidaJudicial->institucion = $data->MedidaJudicial->institucion;
		$this->MMedidaJudicial->nombre_autoridad = $data->MedidaJudicial->autoridad;
		$this->MMedidaJudicial->cargo = $data->MedidaJudicial->cargo;


		$this->MMedidaJudicial->estado = $data->MedidaJudicial->estado;
		$this->MMedidaJudicial->ciudad = $data->MedidaJudicial->ciudad;
		$this->MMedidaJudicial->municipio = $data->MedidaJudicial->municipio;
		$this->MMedidaJudicial->descripcion_institucion = $data->MedidaJudicial->descripcion_institucion;

		$this->MMedidaJudicial->nombre_beneficiario = $data->MedidaJudicial->nombre_beneficiario;
		$this->MMedidaJudicial->cedula_beneficiario = $data->MedidaJudicial->cedula_beneficiario;
		$this->MMedidaJudicial->parentesco = $data->MedidaJudicial->parentesco;

		$this->MMedidaJudicial->cedula_autorizado = $data->MedidaJudicial->cedula_autorizado;
		$this->MMedidaJudicial->nombre_autorizado = $data->MedidaJudicial->nombre_autorizado;

		$this->MMedidaJudicial->fecha_creacion =  date("Y-m-d H:i:s");
		$this->MMedidaJudicial->usuario_creacion = $_SESSION['usuario'];
		$this->MMedidaJudicial->fecha_modificacion =  date("Y-m-d H:i:s");
		$this->MMedidaJudicial->usuario_modificacion = $_SESSION['usuario'];
		$this->MMedidaJudicial->ultima_observacion = '';

		if($id == ''){
			$this->MMedidaJudicial->salvar();	
		}else{
			$this->MMedidaJudicial->id = $id;
			$this->MMedidaJudicial->actualizar();
		}
		//print_r($this->MMedidaJudicial);
		echo "Se registro nueva Medida Judicial en estatus de activo";
	}


	public function ejecutarAnticipo(){
		//echo "<pre>";

		$this->load->model('beneficiario/MHistorialMovimiento');
		$this->load->model('beneficiario/MOrdenPago');

		$json = json_decode($_POST['data']); // 'Hola Mundo'; //Object($_POST);
		$json->u_s = $_SESSION['usuario'];

		$fecha_aux = isset($json->f_r) ? $json->f_r : '';
		if($fecha_aux != ''){
			$this->MOrdenPago->estatus = 100;
			$this->MOrdenPago->id = $json->o_b;
			$this->MOrdenPago->emisor = $json->emi;
			$this->MOrdenPago->revision = $json->rev;
			$this->MOrdenPago->autoriza = $json->aut;

			$this->MOrdenPago->ultima_observacion = $this->MHistorialMovimiento->InsertarDetalle($json);
			$this->MOrdenPago->ejecutar();
			echo 'Se ha ejecutado exitosamente el anticipo del beneficiario...';
			//print_r($json);
		}


	}

	public function reversarAnticipo(){
		$this->load->model('beneficiario/MOrdenPago');
		$this->load->model('beneficiario/MHistorialMovimiento');
		$this->load->model('beneficiario/MAnticipo');
		$json = json_decode($_POST['data']);
		$ced = $json->cedula;
		$codigo = $json->certificado;

		$this->MOrdenPago->estatus = 103;
		$this->MOrdenPago->cedula_afiliado = $ced;
		$this->MOrdenPago->ultima_observacion = $codigo;
		//echo "<pre>";
		$lst = $this->MAnticipo->listarCodigo($ced, $codigo);
		//print_r($lst);
		$this->MHistorialMovimiento->isertarReverso($lst);
		$this->MOrdenPago->reversar();
		echo 'Se ha procesado exitosamente el reverso';


	}
	public function rechazarAnticipo(){
		$this->load->model('beneficiario/MOrdenPago');
		$json = json_decode($_POST['data']);
		//print_r($json);
		$this->MOrdenPago->estatus = 102;
		$this->MOrdenPago->id = $json->id;
		$this->MOrdenPago->rechazar();
		echo 'Se ha procesado exitosamente el reverso';
	}
	public function lstAnticipoFecha(){
		$this->load->model('beneficiario/MOrdenPago');
		$json = json_decode($_POST['data']);
		$lst = $this->MOrdenPago->listarPorFecha($json->desde, $json->hasta, $json->componente);
		echo json_encode($lst);
	}


	public function listarOrdenesPagoBeneficiario($id){
		$this->load->model('beneficiario/MOrdenPago');
		print_r($this->MOrdenPago->listarPorCedula($id));
	}

	public function actualizarClave($clv){
		$this->load->model('usuario/Usuario');
		$this->Usuario->id = $_SESSION['id'];
		$this->Usuario->clave =  $clv;
		$this->Usuario->actualizar();
		echo 'Actualización de Clave Exitosa';
	}


	public function obtenerCiudades($estado_id = 0){
		$this->load->model('beneficiario/MCiudad');
		$lst = $this->MCiudad->listarID($estado_id);
		echo json_encode($lst);
	}

	public function obtenerMunicipios($ciudad_id = 0){
		$this->load->model('beneficiario/MMunicipio');
		$lst = $this->MMunicipio->listarID($ciudad_id);
		echo json_encode($lst);
	}

	function obtenerMenu(){
		header('Content-Type: application/json');
		$this->load->model("usuario/Perfil");
		print_r($this->Perfil->listarMenu($_SESSION['id']));	
	}

	function obtenerMHijos($id){
		header('Content-Type: application/json');
		$this->load->model("usuario/Perfil");
		print_r($this->Perfil->listarMenu($id));	
	}

	function listarMenu(){
		header('Content-Type: application/json');
		$this->load->model("usuario/Perfil");		
		print_r(json_encode($this->Perfil->listar()));	
	}
	function listarSubMenu($id){
		header('Content-Type: application/json');
		$this->load->model("usuario/Perfil");		
		print_r(json_encode($this->Perfil->listarSubMenu($id)));	
	}

	function listarPerfilPrivilegios($url, $id = 0){
		header('Content-Type: application/json');
		$this->load->model("usuario/Perfil");		
		print_r(json_encode($this->Perfil->listarPerfilPrivilegios($url, $id)));	
	}

	function listarUsuarios(){
		header('Content-Type: application/json');
		$this->load->model("usuario/Usuario");
		print_r(json_encode($this->Usuario->listar()));

	}

	function obtenerUsuario($id = 0){
		header('Content-Type: application/json');
		$this->load->model("usuario/Usuario");
		print_r(json_encode($this->Usuario->obtener($id)));
	}

	function UpsertUsuario(){
		//header('Content-Type: application/json');
		$this->load->model("usuario/Usuario");
		$rs = json_decode($_POST['data']);

		echo $this->Usuario->upsert($rs);
		
		
	}

	function UpsertPerfilPrivilegios(){
		//header('Content-Type: application/json');
		$this->load->model("usuario/Perfil");
		$rs = json_decode($_POST['data']);
		$this->Perfil->InsertMenu($rs->uid,$rs->ids);
		$this->Perfil->InsertPerfil($rs->uid,$rs->idp);
		foreach ($rs->pri as $k => $v) {
			# code...			
			$this->Perfil->UpsertPrivilegio($rs->uid, $rs->idp, $v->id, $v->est);
			print_r($v);
		}
		
	}

	function LHistorialSueldo($id = ''){
		header('Content-Type: application/json');
		$this->load->model("beneficiario/MHistorialSueldo");
		echo json_encode($this->MHistorialSueldo->listar('11953710'));
	}

	function LHistorialMovimiento($id = ''){
		header('Content-Type: application/json');
		$this->load->model("beneficiario/MHistorialMovimiento");
		echo json_encode($this->MHistorialMovimiento->listar('11953710'));
	}


	function LTipoMovimiento(){
		header('Content-Type: application/json');
		$this->load->model("beneficiario/MHistorialMovimiento");
		echo json_encode($this->MHistorialMovimiento->listarTodo());	
	}


	function ListarTasaBCV(){
		header('Content-Type: application/json');
		$this->load->model("beneficiario/MTasabcv");
		echo json_encode($this->MTasabcv->listarTodo());
	}

	function ListarEditarDirectiva($id){
		header('Content-Type: application/json');
		$this->load->model("beneficiario/MDirectiva");
		echo json_encode($this->MDirectiva->listarTodo($id));
	}

	
	function ClonarDirectiva(){
		header('Content-Type: application/json');
		$this->load->model("beneficiario/MDirectiva");
		$data = json_decode($_POST['data']);
		$this->MDirectiva->crearDirectiva($data);
	}

	function EliminarDirectiva(){
		
		$this->load->model("beneficiario/MDirectiva");
		$data = json_decode($_POST['data']);
		$this->MDirectiva->Eliminar($data->id);
	}



	function ActualizarEditarDirectiva(){
		//header('Content-Type: application/json');
		$data = json_decode($_POST['data']);
		$this->load->model("beneficiario/MDirectiva");
		$this->MDirectiva->Actualizar($data);
		//print_r();
	}

	function ConsultarMedidaEjecutada(){
		header('Content-Type: application/json');
		$this->load->model("beneficiario/MMedidaJudicial");
		echo json_encode($this->MMedidaJudicial->listarTodo($_POST['ced'], $_POST['id']));
	}

	function TestUsuario(){
		$this->load->model("comun/DBSpace");
		$rs = $this->DbSaman->consultar("select * from personas limit 1");
		print_r($rs);

		//echo "Test De pruebas usuarios";
	}

	function Apertura(){
		$this->load->model('kernel/KGenerador');
		$this->KGenerador->Apertura('b485e312430614e47deb5657671368dc.csv', 0);
	}

	
	

	public function salir(){
		redirect('panel/Login/salir');
	}


}
