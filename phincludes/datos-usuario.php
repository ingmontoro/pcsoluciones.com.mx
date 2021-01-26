<style>
#tabla-cliente>tbody>tr>td {
	border: 2px solid #f2f2f2;
	padding:0px 10px;
}
table>tbody>tr>td>div {
	text-transform: capitalize;
}
</style>
<h3>Datos de Usuario.</h3>
<div>
	<ul class="nav nav-tabs" id="myTab" role="tablist">
		<li class="nav-item <?=$tab?> <?=(!isset($tab) || $tab != 'password') ? 'active' : ''?>">
			<a class="nav-link " id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Datos personales</a>
		</li>
		<li class="nav-item <?=(!isset($tab) || $tab != 'password') ? '' : 'active'?>">
			<a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Contraseña</a>
		</li>
	</ul>
</div>
<form id="usuario-form" role="form" data-toggle="validator">
<input type="hidden" id="numcli" name="numcli" value="<?=$edicion ? $datosUsuario->id : ''?>"/>
<div class="tab-content" id="myTabContent">
	<div class="tab-pane <?=(!isset($tab) || $tab != 'password') ? 'active' : ''?>" id="home" role="tabpanel" aria-labelledby="home-tab">
		<br>
		<div class="row has-feedback">
    		<div class="form-group col-md-4">
      			<label for="nombrec">Nombre</label>
      			<input <?=htmlValConf("Nombre", '', true, "A", null, 3)?> value="<?=$edicion ? $datosUsuario->nombre : ''?>" class="form-control" id="nombrec" name="nombrec">
      			<div class="help-block with-errors"></div>
    		</div>
    		<div class="form-group col-md-4">
      			<label for="apellido1c">Primer apellido</label>
      			<input <?=htmlValConf("Primer apellido", '', true, "A", null, 3)?> value="<?=$edicion ? $datosUsuario->apellido1 : ''?>" class="form-control" id="apellido1c" name="apellido1c">
      			<div class="help-block with-errors"></div>
    		</div>
    		<div class="form-group col-md-4">
      			<label for="apellido2c">Segundo apellido</label>
      			<input <?=htmlValConf("Segundo apellido", '', false, "A", null, 3)?> value="<?=$edicion ? $datosUsuario->apellido2 : ''?>" class="form-control" id="apellido2c" name="apellido2c">
      			<div class="help-block with-errors"></div>
    		</div>
  		</div>
  		<div class="row">
    		<div class="form-group col-md-4">
      			<label for="numerot">Login (usuario)</label>
      			<input <?=htmlValConf("Login(usuario)", '', true, "A")?> value="<?=$edicion ? $datosUsuario->login : ''?>" class="form-control" id="" name="" readonly="true">
      			<div class="help-block with-errors"></div>
    		</div>
    		<div class="form-group col-md-4">
      			<label for="numerot2">Alias</label>
      			<input <?=htmlValConf("Alias", '', false, "A")?> value="<?=$edicion ? $datosUsuario->alias : ''?>" class="form-control" id="alias" name="alias">
      			<div class="help-block with-errors"></div>
    		</div>
    		<div class="form-group col-md-4">
      			<label for="emailc">Fecha de nacimiento</label>
      			<!-- input <?=htmlValConf("fecha de ncimiento", 'FN', false, "")?> value="<?=$edicion ? $datosUsuario->fechanacimiento : ''?>" class="form-control" id="fechan" name="fechan" -->
				<br><input value="<?=$datosUsuario->fechanacimiento?>" id="fechanac" name="fechanac" class="form-control" style="cursor: pointer; width:auto; display:inline; margin-right:5px;"/>
      			<div class="help-block with-errors"></div>
    		</div>
  		</div>
		<div class="row">
			<div class="form-group col-md-12">
				<div id="my-alert" class="alert alert-dismissible" role="alert">
					<a class="close alert-close" data-id="my">&times;</a>
					<div id="mensaje"></div>
				</div>
			</div>
		</div>
		<div class="row col-md-12">
			<button type="submit" class="btn btn-success" id="_bot_gu" onclick="">Actualizar datos</button>
		</div>
	</div>
</form>
  	<div class="tab-pane <?=(isset($tab) && $tab == 'password') ? 'active' : ''?>" id="profile" role="tabpanel" aria-labelledby="profile-tab">
  		<div class="row">
    		<div class="form-group col-md-4">
				<h3>Cambiar contraseña</h3>
      			<label for="called">Escribe la Nueva contraseña</label>
      			<input type="password" value="" class="form-control" id="npass" name="npass">
      			<div class="help-block with-errors"></div>
    		</div>
		</div>
		<div class="row">
    		<div class="form-group col-md-4">
      			<label for="numextd">Confirma la Nueva contraseña</label>
      			<input type="password" value="" class="form-control" id="rnpass" name="rnpass">
      			<div class="help-block with-errors"></div>
    		</div>
  		</div>
		<div class="row confirmar">
    		<div class="form-group col-md-4">
      			<label for="rfc">Contraseña actual</label>
      			<input type="password" value="" class="form-control" id="cpass" name="cpass">
      			<div class="help-block with-errors"></div>
    		</div>
		</div>
		<div class="row">
			<div class="form-group col-md-4">
				<div id="my2-alert" class="alert alert-dismissible" role="alert">
					<a class="close alert-close" data-id="my2">&times;</a>
					<div id="mensaje"></div>
				</div>
			</div>
		</div>
		<div class="row col-md-12">
			<button type="button" class="btn btn-success confirmar" id="_bot_cc" onclick="cambiar()">Continuar</button>
			<button type="button" class="btn btn-default confirmar" id="_bot_ca" onclick="cancelar()">Cancelar</button>
			<button type="button" class="btn btn-default" id="_bot_vc" onclick="continuar()">Continuar</button>
		</div>
	</div>
</div>
<script>

function continuar() {
	
	var npass;
	var rnpass;
	
	npass = $("input#npass").val().trim();
	rnpass = $("input#rnpass").val().trim();
	
	if (npass == "" || rnpass == "" ) {
		configAlert("my2", 'danger', "Escribe y confirma tu password");
	} else if(npass != rnpass) {
		configAlert("my2", 'warning', "Los passwords no coinciden, revisalos y vuelve a intentar.");
	} else {
		$(".confirmar").toggle();
		$("#_bot_vc").toggle();
		$("#cpass").focus();
		configAlert("my2", 'info', "Escribe tu contraseña actual para continuar...");
	}
}

function cancelar() {
	$("#my2-alert").hide();
	$("#cpass").val("");
	$(".confirmar").toggle();
	$("#_bot_vc").toggle();
}

function cambiar() {
	var cpass = $("input#cpass").val().trim();
	var datos = JSON.stringify({
		npass: 	$("input#npass").val().trim(),
        rnpass: $("input#rnpass").val().trim(),
        cpass: 	cpass
    });
    var url = 'phrapi/update/password';
	var _request = $.post(url, {data: {json: datos}}, 'json');
	_request.done(function(response) {
		//validarSesion(entity, response);
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			//$('#numcli').val(response.id);
			clase = "success";
			//$("#div_orden").show();
		} else {
			clase = "warning";
		}
		configAlert("my2", clase, response.message); 
	});
	_request.fail( function( jqXHR, textStatus ) {
		configAlert("my2", 'danger', textStatus);
	});
}

function guardarUsuario() {
	var datos = JSON.stringify({
		nombre: 	$('#nombrec').val(),
        apellido1: 	$('#apellido1c').val(),
        apellido2: 	$('#apellido2c').val(),
        alias: 		$('#alias').val(),
		fechanac: 	$('#fechanac').val(),
    });
    var entity = 'usuario';
    var url = 'phrapi/save/' + entity;
	var _request = $.post(url, {data: {json: datos}}, 'json');
	_request.done(function(response) {
		validarSesion(entity, response);
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			//$('#numcli').val(response.id);
			clase = "success";
			//$("#div_orden").show();
		} else {
			clase = "warning";
		}
		configAlert("my", clase, response.message); 
	});
	_request.fail( function( jqXHR, textStatus ) {
		configAlert("my", 'danger', textStatus);
	});
}

$('#usuario-form').validator().on('submit', function (e) {
  if (e.isDefaultPrevented()) {
	  configAlert('cliente', 'warning', 'Tiene un error en el formulario, favor de verificarlo.');
  } else {
	  e.preventDefault();
	  guardarUsuario();
  }
});
$("#my-alert-button").on("click", function(e) {
	e.preventDefault();
	$("#my-alert").hide();
});
$("#my2-alert-button").on("click", function(e) {
	e.preventDefault();
	$("#my2-alert").hide();
});
$(document).ready(function() {
	$(".confirmar").hide();
	hideLoading();
	$("#my-alert").hide();
	$("#my2-alert").hide();
	/*<?if(isset($showficha) && $showficha):?>
	$('.nav-tabs a[href="#sheet"]').tab('show');
	<?else:?>*/
	//$('.nav-tabs a[href="#home"]').tab('show');
	//<?endif;?>
	
	$("input").bind("keypress", function (e) {
		if (e.keyCode == 13) {
			return false;
		}
	});
	$("#fechanac").datepicker({
		//onSelect: function(dateTxt, inst) { reloadVtasPeriod();/*var data = dateTxt.split('-'); reloadVentas(data[2] + '-' + data[1] + '-' + data[0]);*/ },
		dateFormat: 'dd-mm-yy',
		buttonText: 'Elejir fecha',
		autoSize: true,
		showOn: "button",
		buttonImage: "assets/images/calendar.png",
		buttonImageOnly: true,
		monthNames: jsArrayNombresMeses
		//currentText: 'Hoy',
		//defaultDate: '-1d',
		//showButtonPanel: true
	});
});
</script>