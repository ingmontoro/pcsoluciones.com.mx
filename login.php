<? include_once 'phrapi/index.php' ?>
<? $access = $factory->Access ?>
<? if($access->is_logged()) header("location: index.php") ?>
<? $_users = $access->loadUsers() ?>

<html>
	<head>
		<script src="//code.jquery.com/jquery.js"></script>
		<script src="assets/js/jquery-ui.js"></script>
		<script src="assets/js/crypto-google.js"></script>
		<script src="assets/js/pcsoluciones-lock.js"></script>
		
		<link rel="stylesheet" type="text/css" href="assets/css/login.css?v=00000.1">
		<link rel="stylesheet" type="text/css" href="assets/css/jquery-ui.css">
		<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
		
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
		
		<style type="text/css">
		/* PARA QUITAR UNA RAYA Y COLOR INDESEADO EN LOS DIALOG DE JQUERY (OJO: NO FUNCIONA DESDE ARCHIVO CSS EXTERNO)*/
		.ui-dialog-buttonpane {
			text-align: center;
		}
		
		.ui-widget-overlay {
			background: #000;
			opacity: .7;
			-moz-opacity: 0.7;
			filter: alpha(opacity = 70);
		}
		.db-name, .db-status {
			font-weight: bold;
		}
		.db-ok {
			color: #67bc43;
		}
		.db-error {
			color: #ff4531;
		}
		.db-name {
			color: #ffb82a;
		}
		input[type="password"] {
			margin: 6px;
			height: 25px;
			text-align: center;
		}
		div.div-password-label {
			display:block;
		}
		</style>
		<title><?php echo $GLOBALS['config']['system_name']; ?></title>
	</head>
<body>
	<div id="login" style="display: none;">
		<form id="do-login" method="post" action="">
			<!-- label for="usuario">usuario: </label-->
			<input name="usuario" id="usuario" type="hidden" class="normal"
				size="15" value="" />
			<!-- label for="contrasena">constrase&ntilde;a: </label -->
			<input type="hidden" name="contrasena" class="normal" id="contrasena"
				size="15" value="noSeUsa" /> <input type="hidden" id="md5p"
				name="md5p" />
			<!-- button type="button" onclick="return login();">entrar</button -->
		</form>
	</div>
	<div id="divBloqueoF" style="background:; z-index: auto; background-color: black;"></div>
	<div class="div-bloqueo">
		<!-- div class="div-title">
			<span id="changer" style="cursor: pointer;"><?=substr($GLOBALS['config']['system_name'], 0, 1)?></span><?=substr($GLOBALS['config']['system_name'], 1)?>
		</div -->
		<div class="div-title">
				<span id="changer" style="cursor: pointer;"><span style="color:#00a1ec;"><?=substr($GLOBALS['config']['system_name'], 0, 1)?></span><span style="color:orange;"><?=substr($GLOBALS['config']['system_name'], 1, 1)?></span></span><?=substr($GLOBALS['config']['system_name'], 2)?>
			</div>
		<div class="users-container">
			<!-- <div class="div-title">
				<span id="changer" style="cursor: pointer;"><?=substr($GLOBALS['config']['system_name'], 0, 1)?></span><?=substr($GLOBALS['config']['system_name'], 1)?>
			</div>  -->
			<?if (isset( $_users) && count ($_users) > 0):?>
				<?foreach ($_users as $_user):?>
				<? //$style = "background-image:url('" . ($_user->imagen == '' ? 'assets/images/icon-user-default.png' :$GLOBALS['config']['user_image_path'] . "/" . $_user->imagen) . "'); background-size:$_user->tamano; background-position:$_user->posicion;"; ?>
				<? $style = $_user->imagen == '' ? '' : "background-image:url('" . $GLOBALS['config']['user_image_path'] . "/" . $_user->imagen . "'); background-size:$_user->tamano; background-position:$_user->posicion;"; ?>
				<? $class = "profile $_user->profile"; ?>
					<div class="cell-user">
						<div class="cell-user-img <?=$class?>"
							style="<?=$style?>"
							id="<?=$_user->id?>"
							onclick="startSession('<?=$_user->login?>', '<?=$_user->id?>');">
						</div>
						<div class="div-username-label" id="label<?=$_user->id?>"><?=$_user->alias?>
						</div>
						<div class="div-password-label">
							<span style="font-size:large">password:</span>
							<br/>
							<input class="input-pass" type="password" />
						</div>
						<!-- <div class="div-user-config"><img src="assets/images/gears.png" style="width:48px;"></div>  -->
					</div>
				<?endforeach;?>
			<!-- <div class="div-footer">Bienvenido</div> -->
			<?else:?>
				<div class="alertDiv">No Existen Usuarios para Iniciar Sesion</div>
			<?endif;?>
			<?include 'phincludes/login-wallpapers.php';?>
		</div>
		<div class="div-footer"><div style="padding-right:20px;">Bienveni<span style="color:#00a1ec;">d</span><span style="color:orange;">o</span></div></div>
		<!-- div class="div-footer">Bienvenido</div -->
		<div style="background-color: black; color: white; font-size: 15px; display: table-row-group;">
		<?$pruebas = strpos($GLOBALS['config']['db'][0]['name'], 'develop')?>
		<?$produccion = $pruebas === false ? true : false?>
		<?$dbmd5 = $produccion ? "dd96c1aee6c32bca651d83247649d290" : "9f42dd65006d44338c8cd37d968b56d5"?>
		<?$dbstat = $dbmd5 == md5($GLOBALS['config']['db'][0]['name'])?>
		Modo: <span class="db-name"><?=$produccion ? "PRODUCCION" : "PRUEBAS" ?></span> - Conexión BD: <?=$dbmd5?><span class="db-status db-<?=$dbstat ? 'ok' : 'error'?>"> <?=$dbstat ? "OK" : "E R R O R"?></span>
		</div>
		<div id="configuracion" title="Configuraci&oacute;n inicial">
			<form id="userConfig" method="post">
				<input type="file" id="usrImg" name="usrImg" />
				<input type="text" id="usrName" name="alias" />
				<input type="text" id="usrId" name="id" />
				<input type="text" id="eliminar" name="eliminar" />
				<div style="text-align: left;">
					<div style="text-align: left;">
						Clic en la imagen para cambiarla<img src="assets/images/down-right.png"
							style="vertical-align: top; width: 48px;">
					</div>
					<div
						style="display:; padding-top: 205px; float: left; vertical-align: middle; align-items: baseline; text-align: right; padding-right: 15px;">
						Clic en nombre<br>para editarlo<br> <img
							src="assets/images/left-down.png"
							style="vertical-align: top; width: 48px;">
					</div>
					<div id="content" style="float: left;"></div>
					<div style="display: inline-table; text-align: left; float: left;">
						Ajuste de<br>visualizaci&oacute;n <img
							src="assets/images/down-right.png"
							style="width: 48px; vertical-align: top;"> <br> <br>Ajuste<br> <select
							name="ajuste" id="ajuste">
							<option value="alto">Alto</option>
							<option value="ancho">Ancho</option>
						</select> <br> <br>Posici&oacute;n<br> <select name="posicion"
							id="posicion">
					<?php if($_user->ajuste == "alto") {?>
						<option value="center">Centrada</option>
							<option value="left">Izquierda</option>
							<option value="right">Derecha</option>
					<?php } else if($_user->ajuste == "ancho") {?>
						<option value="center">Centrada</option>
							<option value="top">Arriba</option>
							<option value="bottom">Abajo</option>
					<?php }?>
				</select>
					</div>
				</div>
			</form>
		</div>
	</div>

</body>
<script src="assets/js/login-footer.js"></script>
<script type="text/javascript">
$(document).ready( function() {
	//ocultamos los password
	$(".div-password-label").hide();
	$(".div-password-label").keypress(function(e) {
		var contra;
		var code = (e.keyCode ? e.keyCode : e.which);
		if(code==13){
			//alert(e.target.value);
			contra = e.target.value;
			if(contra.trim() != "") {
				$("#contrasena").val(contra);
				$("#do-login").submit();
			}
		}
	});
	$(document).on('keyup', function(evt) {
		if (evt.keyCode == 27) {
		   hideAllPass();
		}
	});
	$("form#do-login").submit(function(e) {
        e.preventDefault();
    	var formData = new FormData($(this)[0]);
    	$.ajax({
            url: 'phrapi/access/login', 
            dataType: 'text',  // what to expect back from the PHP script, if anything
            cache: false,
            processData: false,
            contentType: false,
            data: formData,                         
            type: 'post',
            success: function(response){
                //alert(response);
                if(response != 200) {
                	alert("Error al iniciar sesion...");
                } else {
                	//alert("Exito...");
                	window.location = "index.php";
                }
            }
 		});
    });
});
</script>
</html>