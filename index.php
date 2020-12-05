<? include_once 'phrapi/index.php' ?>
<? $intranet = $factory->Pcsoluciones ?>
<?
	//error_reporting(-1);
	
	//ini_set('display_errors', 'true');
?>
<? $accion = getString('accion', 'index') ?>
<? $seccion = getString('seccion', 'ordenes') ?>
<? //$accion = 'index' ?>
<? //$seccion = 'ordenes' ?>
<!DOCTYPE html>
<html lang="en">
  <head>
  	<base href="<?=$config['url']?>">
  	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    

    <!-- Bootstrap core CSS -->
    <!-- Bootstrap CSS -->
	<!-- link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" -->
	<link href="assets/css/bootstrap.min.css?v=1.0001" rel="stylesheet">
    <!-- Site CSS -->
    <link href="assets/css/custom.css?v=00000000003210" rel="stylesheet">
    <link href="assets/css/loading.css?v=0000000002" rel="stylesheet">
    <link href="assets/css/tables-style.css?v=000000000200000" rel="stylesheet">
    <link href="assets/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    
    
    <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
    
    <script src="assets/js/crypto-google.js"></script>
	<script src="assets/js/script.js?v=0.0008878000002"></script>
	<script src="assets/js/pcsoluciones-vars.js?v=0000000.20"></script>
	<script src="assets/js/pcsoluciones-security.js?v=00000000000.1"></script>
	
	<script src="assets/js/jquery.js"></script>
	<script src="assets/js/jquery-ui.js"></script>
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.2/bootstrap3-typeahead.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/1000hz-bootstrap-validator/0.11.9/validator.js"></script>
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.js"></script>
	
	<!-- Custom styles for this site -->

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <title>Fixed Top Navbar Example for Bootstrap</title>
    <style type="text/css">
    	.icono-ventana {
    		color: rgba(0,0,0,.5);
    	}
    	.btn {
    		box-shadow:3px 3px 5px grey;
    	}
    	a.no-shadow {
			box-shadow:0 0 0;
		}
		.navbar-default {
			background-color: #fff;
		}
		.navbar-default .navbar-nav>.open>a, .navbar-default .navbar-nav>.open>a:focus, .navbar-default .navbar-nav>.open>a:hover {
		    color: #fff;
		    background-color: #0093d0;
		}
		h2.titulo {
			 margin-bottom: 35px;
		}
		.no-js #loader { display: none;  }
		.js #loader { display: block; position: absolute; left: 100px; top: 0; }
		.se-pre-con {
			position: fixed;
			left: 0px;
			top: 0px;
			width: 100%;
			height: 100%;
			z-index: 9999;
			display: none;
			background: url(assets/images/loader.gif) center no-repeat #fff;
			filter: opacity(70%);
		}
		 
    </style>
  </head>
  <div class="se-pre-con"></div>
  <body>
  	<? $ie = getValueFrom($_GET, 'ext', 0, FILTER_SANITIZE_PHRAPI_MYSQL);?>
  	<? if($ie == 0):?>
  		<?include_once 'phincludes/menu.php';?>
  	<?else:?>
  		<style>
  		body {
	  		min-height: 2000px;
	  		padding-top: 0px;
		}
		</style>
  	<?endif;?>
  	
	<a id="inicio"></a>
	<div class="container">
	  <!-- Main component for a primary marketing message or call to action -->
      <!-- div class="jumbotron" -->
      	<div id="cue" class="col-md-12">
      		<? $result = $intranet->$accion() ?>
			<? if (file_exists("views/{$seccion}_{$accion}.php")) include_once "views/{$seccion}_{$accion}.php" ?>
      	</div>
      <!-- /div -->

    </div> <!-- /container -->
	<!-- Modal confirm para todas aquellas acciones que requieren confirmacion de aceptar - cancelar-->
	<div class="modal fade" id="confirm-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content" style="margin:60% 0;max-width:460px;">
	      <div class="modal-header">
	        <div class="modal-title" id="confirm-modal-title"></div>
	      </div>
	      <div class="modal-body"><h4 class="modal-title" id="confirm-modal-text"></h4></div>
	      <div class="modal-footer" style="text-align:center;">
	      	<button id="btn-aceptar" type="button" class="btn btn-primary">Si, hazlo</button>
	      	<button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
	      </div>
	    </div>
	  </div>
	</div>
	
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!-- jQuery -->
		<script src="//code.jquery.com/jquery.js"></script>
		<script src="https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
		
		
		<!-- Bootstrap JavaScript -->
		<script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
		
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="assets/js/ie10-viewport-bug-workaround.js"></script>
  </body>
  <script type="text/javascript">
  	function validarSesion(entity, response) {
  		if (response.indexOf('html') > -1) {
			configAlert(entity, "warning", "Sesi&oacute;n inv&aacute;lida...");
	      	window.location.reload();
	    }
  	}
  	function limpiarForm(nombreForm) {
		$("#" + nombreForm)[0].reset();
	}
  	function configAlert(idAlert, clase, mensaje) {
		idAlert =  '#' + idAlert + '-alert';
		$(idAlert).find("#mensaje").html(mensaje);
		$(idAlert).removeClass("alert-warning");
		$(idAlert).removeClass("alert-success");
		$(idAlert).removeClass("alert-danger");
		$(idAlert).addClass('alert-' + clase);
		$(idAlert).fadeIn();
	}
	function createWindows() {
		$(".ventana").on('click', function(e) {
			e.preventDefault();
			//alert($(this).prop("href"));
			window.open($(this).prop("href") + "?ext=1", "Nota de venta", "menubar=no,location=no,resizable=yes,scrollbars=yes,status=no,width=1100,height=600");
		});
	}
	$(function() {
		startTimer();
		$(document).on('mousemove', function() {
			//resetTimer();
			startTimer();
		});
		$(document).keydown(function() {
			//resetTimer();
			startTimer();
		});
		createWindows();
		$(".alert-close").on("click", function (e) {
			$('#' + $(this).data('id') + '-alert').fadeOut()
		});
		$(".click-loader, .no-shadow").on("click", function(e){
			$(".se-pre-con").show();
		});
	});
  </script>
  <script>
	//paste this code under the head tag or in a separate js file.
		// Wait for window load
		$(window).load(function() {
			// Animate loader off screen
			//$(".se-pre-con").fadeOut("slow");
			$(".se-pre-con").hide();
		});
	</script>
</html>
