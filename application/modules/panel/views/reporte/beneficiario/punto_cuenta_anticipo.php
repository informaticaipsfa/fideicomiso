<?php
  $usuario = '';
  $monto = 0;

  foreach ($Beneficiario->HistorialOrdenPagos as $c => $v) {
    if($v->id == $codigo){
      $f = explode('-', substr($v->fecha_creacion, 0, 10));
      $finalidad = $v->motivo;
      $monto = $v->monto;
      $porcentaje = $v->porcentaje;//se agrega para mostrar el porcentaje otorgado en el punto de cuenta
      $usuario = $v->usuario_modificacion;
    }

    //$anticipo =  $Beneficiario->Calculo['anticipos_aux'] - $monto;
    $anticipo =  $Beneficiario->Calculo['anticipos_aux'];

  }

  function fecha($fecha = ''){
    $mes = 'Enero';
    switch ($fecha) {
      case 1:
        $mes="Enero";
        break;
      case 2:
        $mes="Febrero";
        break;
      case 3:
        $mes="Marzo";
        break;
      case 4:
        $mes="Abril";
        break;
      case 5:
        $mes="Mayo";
        break;
      case 6:
        $mes="Junio";
        break;
      case 7:
        $mes="Julio";
        break;
      case 8:
        $mes="Agosto";
        break;
      case 9:
        $mes="Septiembre";
        break;
      case 10:
        $mes="Octubre";
        break;
      case 11:
        $mes="Noviembre";
        break;
      case 12:
        $mes="Diciembre";
        break;
      default:
        # code...
        break;
    }
    return $mes;

  }

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Formulario</title>
 <style>
    table {
        font-family: arial, sans-serif;
        font-size: 12px;
        border-collapse: collapse;
        width: 800px;
    }

    td{

        text-align: left;
        padding: 8px;
    }
    th {
        border: 1px solid #dddddd;
        text-align: left;
        background-color: #dddddd;
        padding: 8px;
    }

    /*tr:nth-child(even) {
        background-color: #dddddd;
    }*/
  </style>
  </head>
<BODY>
 <center>
 <table style="width: 1000px">
 <tr>
   <td style="width: 65%;  border: 0px solid #dddddd; text-align: center; font-size: 10px">
     REPÚBLICA BOLIVARIANA DE VENEZUELA<BR>
     MINISTERIO DEL PODER POPULAR PARA LA DEFENSA<BR>
     VICEMINISTERIO DE SERVICIOS, PERSONAL Y LOGISTICA<BR>
     DIRECCIÓN GENERAL DE EMPRESAS<BR>
     INSTITUTO DE PREVISIÓN SOCIAL<BR>
     DE LAS FUERZAS ARMADAS<BR>
   </td>
   <td style="width: 35%;  border: 0px solid #dddddd; text-align: right;">
     Sistema PACE<br><b>
     <?php echo 'Caracas, ' . $f[2] . ' de ' . fecha($f[1]*1) . ' de '. $f[0] ?></b>

   </td>
 </tr>
 </table>

 <table style="width: 1000px;  text-align: justify;  font-size: 14px" >

  <tr>
    <td>PARA:</td><td><b>PRESIDENTA DEL IPSFA</b></td>
    <td><center><b>
      Nro. 320.600-<?php echo substr(md5($Beneficiario->cedula . $Beneficiario->fecha_ultima_modificacion), 0,6);?>r
      </b></center>
    </td>
  </tr>
  <tr>
    <td>DE:</td><td><b>GERENCIA DE BIENESTAR Y SEGURIDAD SOCIAL</b></td>
    <td><center>
    <button onclick="imprimir()" id="btnPrint">Imprimir Reporte</button>
  </center></td>
  </tr>

 </table>

 <table style="width: 1000px;" border="1">
   <tr><td style="width: 550px; height: 100%;text-align: center"><b>ASUNTO</b></td><td style="text-align: center"><b>DECISIÓN</b></td><td style="width: 250px;text-align: center"><b>MARCO LEGAL</b></td></tr>
   <tr>
      <td style="text-align: justify; font-size: 14px; line-height: 1.5" valign="top">
        &emsp;Esta Gerencia somete a la consideración de la ciudadana VA. Presidenta de la Junta Administradora del IPSFA,
        la solicitud formulada por <b><?php
          echo $Beneficiario->Componente->Grado->nombre . ' ' . $Beneficiario->nombres . ' ' . $Beneficiario->apellidos; ?>
         </b> titular de la cédula de identidad <b><?php echo $Beneficiario->cedula . ' (' . $Beneficiario->Componente->nombre . ')';?>
         </b> de un adelanto de su
         Asignación de Antiguedad, con la finalidad:<b> <?php echo strtoupper($finalidad);?></b><br><br>

         &emsp;Al profesional le corresponde por concepto de Asignación de Antigüedad,
         la cantidad de Bs. <b><?php echo $Beneficiario->Calculo['asignacion_antiguedad'];?></b> Actualmente se le ha depositado un monto
         total de Bs.<b><?php echo $Beneficiario->Calculo['asignacion_depositada'];?></b> lo que representa el
         <b><?php echo $Beneficiario->Calculo['porcentaje_cancelado'];?>%</b> de la Asignación de Antiguedad y se le han
         otorgado adelantos que totalizan la cantidad de Bs. <b><?php echo number_format($anticipo, 2, ',','.');?>.</b>
         El monto a otorgar es de Bs. <b><?php echo number_format($monto, 2, ',','.');?></b> lo que representa el
         <b><?php echo number_format($porcentaje, 0, ',','.');?>%</b> del total depositado en banco.
         <br><br>

         &emsp;Esta solicitud cumple con lo establecido en el Artículo 59 de la LOSSFANB (LEY NEGRO PRIMERO).<br><br>

         &emsp;Por lo que me permito realizar esta tramitación con opinión favorable.<br><br><br>

         <center><b>CNEL. JUAN MIGUEL QUINTERO MORALES<BR>
         GERENTE DE BIENESTAR Y SEGURIDAD SOCIAL<B><center>



      </td>
      <td style="width: 325px; height: 170px; text-align: justify; font-size: 14px; line-height: 1.5" valign="top">

      <table style="width: 100%">
        <tr style="height: 50%; width: 100%">
          <td>APROBADO</td>
          <td><input type="text" style="width:20px"></input></td>
          <td>NEGADO</td>
          <td><input type="text" style="width:20px"></input></td>
        </tr>
        <tr style="height: 50%; width: 100%">
          <td>VISTO</td>
          <td><input type="text" style="width:20px"></input></td>
          <td>DIFERIDO</td>
          <td><input type="text" style="width:20px"></input></td>
        </tr>
        <tr style="height: 50%; width: 100%">
          <td>OTRO</td>
          <td><input type="text" style="width:20px"></input></td>
          <td></td>
          <td></td>
        </tr>
      </table>
      
      <br><br><br><br><br><br>
      <br><br><br><br><br><br>
      <br><br><br><br>
      <center><b>ERIKA COROMOTO VIRGÜEZ OVIEDO<BR>
                VICEALMIRANTE<BR>
                PRESIDENTA DEL I.P.S.F.A<B><center>
      </td>


           <!--tr style="height: 30%; width: 100%; text-align: justify; font-size: 14px; line-height: 1.5" valign="bottom">
            <td colspan="4">
              <br><br><br><br><br><br>
              <br><br><br><br><br><br>
              <br>
              <b><center>
              ERIKA COROMOTO VIRGÜEZ OVIEDO<BR>
              VICEALMIRANTE<BR>
              PRESIDENTA DEL I.P.S.F.A
              </center>
              </B>
            </td>
          </tr-->

          



      <td style="width: 225px;text-align: justify; font-size: 14px; line-height: 1.5" valign="top">
        De conformidad con lo dispuesto en el Artículo 59 de la Ley Negro Primero de fecha <b>29DIC2015</b>, en concordancia con lo
        establecido en los Artículos 5,6,7 y 8 del Reglamento Parcial de la Ley de Seguridad Social de las Fuerzas Armadas
        Nacionales Relativo al Pago de la Asignación de Antigüedaqd y Fallecimiento al Personal Militar Profesional de la Fuerza
        Armada Nacional de fecha <b>27JUN2005</b>.
      </td>
    </tr>
 </table>
</center>
&emsp;&emsp;&emsp;EMG/<?php echo $usuario;?>

  <script language="Javascript">
    function imprimir(){
        document.getElementById('btnPrint').style.display = 'none';
        window.print();
        window.close();
    }
  </script>
</body>
</html>
