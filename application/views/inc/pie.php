
 
    <?php $this->load->view('inc/mensaje.php');?>


    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/notify/bootstrap-notify.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap-file-input/js/plugins/piexif.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap-file-input/js/plugins/purify.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap-file-input/js/plugins/sortable.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap-file-input/js/fileinput.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap-file-input/js/locales/es.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap-file-input/themes/fa/theme.min.js"></script>

    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/dist/js/adminlte.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/select2/dist/js/select2.full.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/datatables.net/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/datatables.net/js/buttons.print.min.js"></script>

    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/datatables.net-bs/js/dataTables.select.min.js"></script>


    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/barcode/dist/JsBarcode.all.js"></script>
    <!-- InputMask -->
    <script src="<?php echo __RUTA_PLUGINS?>/plugins/input-mask/jquery.inputmask.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/plugins/input-mask/jquery.inputmask.extensions.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/formato/numeral.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/moment/min/moment.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/iCheck/icheck.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap-datepicker/dist/locales/bootstrap-datepicker.es.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/bootstrap-waitingfor/bootstrap-waitingfor.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/fancy/dist/jquery.fancybox.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/accounting/accounting.min.js"></script>
    <script src="<?php echo __RUTA_PLUGINS?>/bower_components/bs-stepper/bs-stepper.min.js"></script>


  <!--FIN DATE-->
  <!-- Page script -->
  <script>

    function principal(){
        $(location).attr('href', sUrlP + "index");
     }

    function check(){
      $('.mailbox-messages input[type="checkbox"]').iCheck({
          checkboxClass: 'icheckbox_flat-blue',
          radioClass: 'iradio_flat-blue'
        });

        //Enable check and uncheck all functionality
        $(".checkbox-toggle").click(function () {
          var clicks = $(this).data('clicks');
          if (clicks) {
            //Uncheck all checkboxes
            $(".mailbox-messages input[type='checkbox']").iCheck("uncheck");
            $(".fa", this).removeClass("fa-check-square-o").addClass('fa-square-o');
          } else {
            //Check all checkboxes
            $(".mailbox-messages input[type='checkbox']").iCheck("check");
            $(".fa", this).removeClass("fa-square-o").addClass('fa-check-square-o');
          }
          $(this).data("clicks", !clicks);
        });

    }
    

    $(function () {
        //Initialize Select2 Elements


        check();
        $(".select2").select2();

        $('#fingreso').datepicker({
          format: 'dd/mm/yyyy',
          autoclose: true
        });

        $('#fuascenso').datepicker({
          format: 'dd/mm/yyyy',
          autoclose: true
        });     

        //Datemask dd/mm/yyyy
        $("#datemask").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
        //Datemask2 mm/dd/yyyy
        $("#datemask2").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
        //Money Euro
        $("[data-mask]").inputmask();

        //Date range picker
        $('#reservation').daterangepicker();
        //Date range picker with time picker
        $('#reservationtime').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'DD/MM/YYYY h:mm A'});
        //Date range as a button
        $('#daterange-btn').daterangepicker(
        {
          ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
          },
          startDate: moment().subtract(29, 'days'),
          endDate: moment()
        },
        function (start, end) {
          $('#daterange-btn span').html(start.format('D MMMM, YYYY') + ' - ' + end.format('D MMMM, YYYY'));
        }
        );

        //Date picker
        $('#datepicker').datepicker({

          format: 'dd/mm/yyyy',
          autoclose: true
        });
        $('#datepicker1').datepicker({
          format: 'dd/mm/yyyy',
          autoclose: true
        });
        $('#datepicker2').datepicker({
          format: 'dd/mm/yyyy',
          autoclose: true
        });
        /**
        //iCheck for checkbox and radio inputs
        $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
          checkboxClass: 'icheckbox_minimal-blue',
          radioClass: 'iradio_minimal-blue'
        });
        //Red color scheme for iCheck
        $('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
          checkboxClass: 'icheckbox_minimal-red',
          radioClass: 'iradio_minimal-red'
        });
        //Flat red color scheme for iCheck
        $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
          checkboxClass: 'icheckbox_flat-green',
          radioClass: 'iradio_flat-green'
        });


        //Colorpicker
        $(".my-colorpicker1").colorpicker();
        //color picker with addon
        $(".my-colorpicker2").colorpicker();

        //Timepicker
        $(".timepicker").timepicker({
          showInputs: false
        });
        **/
      });
    </script>

    <script src="<?php echo base_url()?>application/views/js/gblmenu.js"></script>