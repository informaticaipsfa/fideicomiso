

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>...:: Prestaciones ::...</title>


    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/bower_components/Ionicons/css/ionicons.min.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/dist/css/skins.ipsfa/_all-skins_1.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/bower_components/select2/dist/css/select2.min.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/css/carnet.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/css/semantic.min.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/css/dataTables.semanticui.min.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/bower_components/fancy/dist/jquery.fancybox.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap-file-input/css/fileinput.min.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap-file-input/css/fileinput-rtl.min.css">

  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/css/fonts.googleapis.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/bower_components/animate/animate.css">

  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/bower_components/bs-stepper/bs-stepper.min.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/bower_components/datatables.net-bs/css/select.dataTables.min.css">
  <link rel="stylesheet" href="<?php echo __RUTA_PLUGINS?>/bower_components/datatables.net/buttons.dataTables.min.css">



    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    
    
    <script type="text/javascript">
      /**
      * Validar Ubicacion
      * @return ubicacion
      */
      var sUrl = "<?php echo base_url(); ?>";
      var sUrlP = sUrl + 'index.php' + <?php echo "/" . __CONTROLADOR . "/";?>;
      function cerrar(){
        window.history.back();  
      }
      
      Number.prototype.formatMoney = function(c, d, t){
      var n = this, 
          c = isNaN(c = Math.abs(c)) ? 2 : c, 
          d = d == undefined ? "." : d, 
          t = t == undefined ? "," : t, 
          s = n < 0 ? "-" : "", 
          i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", 
          j = (j = i.length) > 3 ? j % 3 : 0;
         return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
       };

    </script>

    

  </head>
  <body class="hold-transition skin-blue-light sidebar-mini">