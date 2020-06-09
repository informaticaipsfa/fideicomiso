
class Conexion{
  constructor(){
     this.IP = "localhost";
      this.Puerto = ":8080";
      this.PuertoSSL = ":2608";
      this.API = "/ipsfa/api/";
      this.URL = this.API;
      this.URLS = "https://" + this.IP + this.PuertoSSL + this.API;
      this.URLSEC = "https://" + this.IP + this.PuertoSSL;
      this.URLIMG = "http://localhost/imagenes/";
      this.URLTEMP = "temp/";

  }
}

function CargarAPI(sURL, metodo, valores, Objeto){
  var xhttp = new XMLHttpRequest();
  xhttp.open(metodo, sURL);
  xhttp.setRequestHeader("Authorization", "Bearer " + sessionStorage.getItem('ipsfaToken'));

  xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        if(Objeto != undefined){
          Objeto.Crear(JSON.parse(xhttp.responseText));
        }else{
          respuesta = JSON.parse(xhttp.responseText);
          
          if (respuesta.tipo != 0){
            $.notify("Se ha Insertado correctamente", "success");
          }else{
              alert(xhttp.responseText)
          }
        }
      }
  }
  xhttp.onerror = function() {
      if (this.readyState == 4 && this.status == 0) {

        $.notify("No se puede conectar al servidor");
        $("#_cargando").hide();
          //Pace.stop();
      }

  };


  if(valores != undefined){
    xhttp.send(JSON.stringify(valores));
  }else{
    xhttp.send();
  }


}
