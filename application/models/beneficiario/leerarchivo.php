<?php
	function cargar(){	
		conexion();
		cargar_data();
		reporte();
		$result = $array();
	}


	function conexion(){
    	$dbconn = pg_connect('host=localhost user=postgres password=dg2010 dbname=pace') or var_dump( pg_last_error());
    }

    function cargar_data(){

    	$query 	= "DELETE FROM data_componente";
    	//echo $query;
		$result = pg_query($dbconn,$query);
		$fp = fopen("/home/mary/www/space/tmp/componente.txt", "r");
	
	
    	while(!feof($fp)) {
			fscanf($fp,"%1d %2d %8ld %25c %25c %4d %2d %2d %2d %2d %2d %8d %8d %2s",$sql_codigo,$sql_status_prof,$sql_cedula,$sql_apellidos,$sql_nombres,$sql_grado,$sql_num_hijo,$sql_porc_no_asc,$sql_an_rec_ser,$sql_mes_recon_ser,$sql_dia_recon_ser,$sql_fecha_ingreso,$sql_fec_ult_ascenso,$sql_categoria);
			
			$query = "INSERT INTO data_componente values ($sql_codigo,$sql_status_prof,$sql_cedula,'$sql_apellidos','$sql_nombres',$sql_grado,$sql_num_hijo,$sql_porc_no_asc,$sql_an_rec_ser,$sql_mes_recon_ser,$sql_dia_recon_ser,'$sql_fecha_ingreso','$sql_fec_ult_ascenso','$sql_categoria')";
			$result = pg_query($dbconn,$query) or die('Query failed: ' . pg_last_error()); 

		}
		$query 	= "UPDATE data_componente set nombres = regexp_replace(nombres, '0', ' ', 'g'), apellidos = regexp_replace(apellidos, '0', ' ', 'g');";
		echo $query;
		$result = pg_query($dbconn,$query);
		fclose($fp);
	}


	function reporte(){
	
		$query = "COPY (SELECT DISTINCT b.cedula as cedula_comp,
			lpad(b.apellidos,16)||' '||b.nombres as apellidos_comp,
			a.apellidos as apellidos_pace,
			b.grado as grado_comp,
			c.codigo as grado_pace,
			b.componente as componente_comp,
			a.componente_id as componente_pace,
			b.fecha_ingreso as fec_ingreso_comp,
			a.fecha_ingreso as fec_ingreso_pace,
			b.year_recon as año_reconocido_comp,
			a.anio_reconocido as año_reconicido_pace,
			b.meses_recon as mes_reconocido_comp,
			a.mes_reconocido as mes_reconocido_pace,
			b.dias_recon as dias_reconocido_comp,
			a.dia_reconocido as dia_reconocido_pace,
			b.status_prof as estatus_prof_comp,
			a.st_profesion as estatus_prof_pace,
			b.fec_ult_ascenso as ultimo_ascen_comp,
			a.f_ult_ascenso as ultimo_ascen_pace,
			b.no_ascenso as no_ascenso_comp,
			a.st_no_ascenso as no_ascenso_pace,
			b.num_hijos as num_hijos_comp,
			a.n_hijos as num_hijos_pace
			FROM beneficiario as a, data_componente as b, grado as c
			WHERE a.cedula=b.cedula
			and   a.grado_id= c.id
			and   b.componente=c.componente_id) 
			TO '/home/mary/Descargas/prueba.txt' CSV HEADER DELIMITER '|';";

		$resultado = pg_query($dbconn, $query); 

	}


		/*$resultado = pg_fetch_array($resultado);

		$componente = $resultado["componente"];
		$status_prof = $resultado["status_prof"];
		$cedula = $resultado["cedula"];
		$apellidos = $resultado["apellidos"];
		$nombres = $resultado["nombres"];
		$grado = $resultado["grado"];
		$num_hijos = $resultado["num_hijos"];
		$no_ascenso = $resultado["no_ascenso"];
		$year_recon = $resultado["year_recon"];
		$meses_recon = $resultado["meses_recon"];
		$dias_recon = $resultado["dias_recon"];
		$fecha_ingreso = $resultado["fecha_ingreso"];
		$fec_ult_ascenso = $resultado["fec_ult_ascenso"];
		$categoria = $resultado["categoria"];
		
		echo $componente,$status_prof,$cedula,$apellidos,$nombres,$grado,$num_hijos,$no_ascenso,$meses_recon,$fecha_ingreso,
		$fec_ult_ascenso,$categoria;
		
		/*$num_reg = pg_num_rows($result);
		echo $num_reg;*/

	/*function detalleMovimientoFamiliar(){
		$lst = array();
		$query = 'SELECT * FROM data_componente;';
		$result = pg_query($dbconn,$query);
		
		foreach ($result as $val) {
			$lst[] = array(
				'componente' => $val->componente,
				'status_prof' => $val->status_prof,
				'cedula' => $val->cedula,
				'apellidos' => $val->apellidos,
				'nombres' => $val->nombres,
				'grado' => $val->grado,
				'num_hijos' => $val->num_hijos,
				'no_ascenso' => $val->no_ascenso,
				'year_recon' => $val->year_recon,
				'meses_recon' => $val->meses_recon,
				'dias_recon' => $val->dias_recon,
				'fecha_ingreso' => $val->fecha_ingreso,
				'fec_ult_ascenso' => $val->fec_ult_ascenso,
				'categoria' => $val->categoria
			);
		}
		echo $lst;
		return $lst;
	/*}
    	$m = 34;
    	if($tipo == 1)$m = 35;
    	if($tipo == 2)$m = 33;
    
 
    	$sub = substr($path, 1, 33);
    	$handle = fopen("tmp/" . $sub . ".csv", "r");
    	$file = fopen("tmp/" . $path . '/APORT' . $archivo . ".txt","a") or die("Problemas");
    	$cantidad = 0;
    	$sum = 0;
    	$plan = '03487';
    	if ($handle) {
        	while (($buffer = fgets($handle, 4096)) !== false) {
          		if($sum > 0){ 
              		$l = explode(";", $buffer);
              		if($l[29] > 0 ||  $l[31] > 0 || $l[32] > 0){  
               
                	$nac = 'V';
                	$cedula = $this->completarCero(9, $l[0], '0');
                	$tiptrn = '1';
                	$tippre = '00';
                	$frmpgo = '0';
                	$monto = $l[$m] * 100;  
                	$monto_s = $this->completarCero(13, $monto, '0');
                	$tippta = 'N';
                	$tipcue = '0';
                	$numcue = '0000000000';
                	$tasaint = '000000';
                	$cbrintatp = ' ';
                	$cuomen = '000';
                	$mtoanu = '0000000000000';
                	$cuoanu = '000';
                	$linea = $plan . $nac . $cedula . $tiptrn . $tippre . $frmpgo . $monto_s . $tippta . $tipcue . $numcue . $tasaint . $cbrintatp . $cuomen . $mtoanu . $cuoanu;
                	fputs($file,$linea);
                	fputs($file,"\n");   
                	$cantidad++;             
             		}
        		}	
          		$sum++;

       		}
        	if (!feof($handle)) {
          	 return "Error: unexpected fgets() fail\n";
       	 	}
        	fclose($handle);
        	fclose($file);
    	}
   }*/