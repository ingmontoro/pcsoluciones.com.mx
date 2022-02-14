<style>
#tabla-cliente>tbody>tr>td {
	border: 2px solid #f2f2f2;
	padding:0px 10px;
}
table>tbody>tr>td>div {
	text-transform: capitalize;
}
</style>
<h3>Datos del cliente.</h3>
<div>
	<ul class="nav nav-tabs" id="myTab" role="tablist">
		<?if(isset($showficha) && $showficha):?>
		<li class="nav-item">
			<a class="nav-link active" id="sheet-tab" data-toggle="tab" href="#sheet" role="tab" aria-controls="sheet" aria-selected="false">Ficha</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Nombre y contacto</a>
		</li>
		<?else:?>
		<li class="nav-item">
			<a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Nombre y contacto</a>
		</li>
		<?endif;?>
		<li class="nav-item">
			<a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Datos fiscales</a>
		</li>
	</ul>
</div>
<form id="cliente-form" role="form" data-toggle="validator">
<input type="hidden" id="numcli" name="numcli" value="<?=$edicion ? $datosCliente->id : ''?>"/>
<div class="tab-content" id="myTabContent">
	<?if(isset($showficha) && $showficha):?>
	<div class="tab-pane fade active" id="sheet" role="tabpanel" aria-labelledby="sheet-tab">
		<div class="row">
			<table id="tabla-cliente" style="margin:2%;width: 35%;margin-left:15px;">
				<tr>
					<td style="width: 155px"><label for="rfc">Nombre Fiscal</label></td>
					<td colspan=""><div id="nombrefdiv"></div></td>
				</tr>
				<tr>
					<td style="width: 155px"><label for="rfc">RFC</label></td>
					<td><div class="text-uppercase" id="rfcdiv"></div></td>
				</tr>
				<tr>
					<td><label for="called">Calle</label></td>
					<td><div id="calleddiv"></div>
				</td></tr>
		      	<tr>
					<td><label for="numextd">N&uacute;mero exterior</label></td>
					<td colspan=""><div id="numextddiv"></div>
				</td></tr>
		      	<tr>
					<td><label for="numintd">N&uacute;mero interior</label></td>
					<td colspan=""><div id="numintddiv"></div>
				</td></tr>
		      	<tr>
					<td><label for="colonia">Colonia</label></td>
					<td colspan=""><div id="coloniadiv"></div>
				</td>
		      	<tr>
					<td><label for="estadod">Estado</label></td>
					<td colspan=""><div id="estadoddiv"></div>
				</td></tr>
		      	<tr>
					<td><label for="numextd">C&oacute;digo postal</label></td>
					<td colspan=""><div id="cpddiv"></div></td>
				</tr>
		      	<tr>
					<td><label for="ciudadd">Ciudad</label></td>
					<td colspan=""><div id="ciudadddiv"></div></td>
				</tr>
				<tr>
					<td><label>Nombre</label></td>
					<td colspan=""><div id="nombrediv"></div></td>
				</tr>
		      	<tr>
					<td><label for="apellido1c">Primer apellido</label></td>
					<td colspan=""><div id="apellido1cdiv"></div></td>
				</tr>
		      	<tr>
					<td><label for="apellido2c">Segundo apellido</label></td>
					<td colspan=""><div id="apellido2cdiv"></div></td>
				</tr>
		      	<tr>
					<td><label for="numerot">Tel&eacute;fono principal</label></td>
					<td><div id="numerotdiv"></div></td>
					
				</tr>
		      	<tr>
					<td><label for="numerot2">Otro tel&eacute;fono</label></td>
					<td><div id="numerot2div"></div></td>
					
					</td>
				</tr>
				<tr>
					<td><label for="emailc">Correo electr&oacute;nico</label></td>
					<td colspan=""><div style="text-transform:lowercase;" id="emailcdiv"></div>
					</td>
				</tr>
				<tr>
					<td><label for="regimen">Régimen</label></td>
					<td colspan=""><div id="regimendiv"></div>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<div class="tab-pane fade" id="home" role="tabpanel" aria-labelledby="home-tab">
	<?else:?>
	<div class="tab-pane fade active" id="home" role="tabpanel" aria-labelledby="home-tab">
	<?endif;?>
		<br>
		<div class="row has-feedback">
    		<div class="form-group col-md-4">
      			<label for="nombrec">Nombre</label>
      			<input <?=htmlValConf("Nombre", '', true, "A", null, 3)?> value="<?=$edicion ? $datosCliente->nombre : ''?>" class="form-control" id="nombrec" name="nombrec">
      			<div class="help-block with-errors"></div>
    		</div>
    		<div class="form-group col-md-4">
      			<label for="apellido1c">Primer apellido</label>
      			<input <?=htmlValConf("Primer apellido", '', true, "A", null, 3)?> value="<?=$edicion ? $datosCliente->apellido1 : ''?>" class="form-control" id="apellido1c" name="apellido1c">
      			<div class="help-block with-errors"></div>
    		</div>
    		<div class="form-group col-md-4">
      			<label for="apellido2c">Segundo apellido</label>
      			<input <?=htmlValConf("Segundo apellido", '', false, "A", null, 3)?> value="<?=$edicion ? $datosCliente->apellido2 : ''?>" class="form-control" id="apellido2c" name="apellido2c">
      			<div class="help-block with-errors"></div>
    		</div>
  		</div>
  		<div class="row">
    		<div class="form-group col-md-3">
      			<label for="numerot">Tel&eacute;fono principal</label>
      			<input <?=htmlValConf("Telefono principal", '', true, "NT")?> value="<?=$edicion ? $datosCliente->numero : ''?>" class="form-control" id="numerot" name="numerot">
      			<div class="help-block with-errors"></div>
    		</div>
    		<div class="form-group col-md-1">
    			<label for="numerot">Tipo</label>
      			<select id="tipot" name="tipot" class="form-control">
					<? Html::Options($tiposTelefono, $edicion ? $datosCliente->tipo : '') ?>
				</select>
    		</div>
    		<div class="form-group col-md-3">
      			<label for="numerot2">Otro tel&eacute;fono</label>
      			<input <?=htmlValConf("Otro telefono", '', false, "NT")?> value="<?=$edicion ? $datosCliente->numero2 : ''?>" class="form-control" id="numerot2" name="numerot2">
      			<div class="help-block with-errors"></div>
    		</div>
    		<div class="form-group col-md-1">
    			<label for="numerot">Tipo(2)</label>
      			<select id="tipot2" name="tipot2" class="form-control">
					<? Html::Options($tiposTelefono, $edicion ? $datosCliente->tipo2 : '') ?>
				</select>
    		</div>
    		<div class="form-group col-md-4">
      			<label for="emailc">Correo electr&oacute;nico</label>
      			<input <?=htmlValConf("Correo electronico", '', false, "E")?> value="<?=$edicion ? $datosCliente->email : ''?>" class="form-control" id="emailc" name="emailc">
      			<div class="help-block with-errors"></div>
    		</div>
  		</div>
	</div>
  	<div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
  		<br>
  		<div class="row">
    		<div class="form-group col-md-12">
      			<label for="nombref">Nombre Fiscal</label>
      			<input <?=htmlValConf("Nombre Fiscal", '', false, "", null, 3)?> value="<?=$edicion ? $datosCliente->nombre_fiscal : ''?>" class="form-control" id="nombref" name="nombref">
      			<div class="help-block with-errors"></div>
    		</div>
    	</div>
    	<div class="row">
    		<div class="form-group col-md-2">
      			<label for="rfc">RFC</label>
      			<input <?=htmlValConf("RFC", '', false, "L", null, 10)?> value="<?=$edicion ? $datosCliente->rfc : ''?>" class="form-control" id="rfc" name="rfc">
      			<div class="help-block with-errors"></div>
    		</div>
			<div class="form-group col-md-3">
				<label for="regimen">R&eacute;gimen Fiscal</label>
				<input <?=htmlValConf("regimen", '', false, "L")?> value="<?=$edicion ? $datosCliente->regimen : ''?>" class="form-control" id="regimen" name="regimen">
				<div class="help-block with-errors"></div>
			</div>
    		<div class="form-group col-md-3">
      			<label for="called">Calle</label>
      			<input <?=htmlValConf("Calle", '', false, "L", null, 3)?> value="<?=$edicion ? $datosCliente->calle : ''?>" class="form-control" id="called" name="called">
      			<div class="help-block with-errors"></div>
    		</div>
    		<div class="form-group col-md-2">
      			<label for="numextd">N&uacute;mero exterior</label>
      			<input <?=htmlValConf("Numero exterior", '', false, "L")?>value="<?=$edicion ? $datosCliente->numext : ''?>" class="form-control" id="numextd" name="numextd">
      			<div class="help-block with-errors"></div>
    		</div>
    		<div class="form-group col-md-2">
      			<label for="numintd">N&uacute;mero interior</label>
      			<input <?=htmlValConf("Numero interior", '', false, "L")?> value="<?=$edicion ? $datosCliente->numint : ''?>" class="form-control" id="numintd" name="numintd">
      			<div class="help-block with-errors"></div>
    		</div>
  		</div>
  		<div class="row">
    		<div class="form-group col-md-3">
      			<label for="colonia">Colonia</label>
      			<input <?=htmlValConf("Colonia", '', false, "L")?> value="<?=$edicion ? $datosCliente->colonia : ''?>" class="form-control" id="colonia" name="colonia">
      			<div class="help-block with-errors"></div>
    		</div>
    		<div class="form-group col-md-3">
      			<label for="estadod">Estado</label>
      			<input <?=htmlValConf("Estado", '', false, "L")?> value="<?=$edicion ? $datosCliente->estado : 'Jalisco'?>" class="form-control" id="estadod" name="estadod">
      			<div class="help-block with-errors"></div>
    		</div>
    		<div class="form-group col-md-3">
      			<label for="numextd">C&oacute;digo postal</label>
      			<input <?=htmlValConf("Codigo postal", '', false, "NT")?> value="<?=$edicion ? $datosCliente->cp : ''?>" class="form-control" id="cpd" name="cpd">
      			<div class="help-block with-errors"></div>
    		</div>
    		<div class="form-group col-md-3">
      			<label for="ciudadd">Ciudad</label>
      			<input <?=htmlValConf("Ciudad", '', false, "L")?> value="<?=$edicion ? $datosCliente->ciudad : 'Guadalajara'?>" class="form-control" id="ciudadd" name="ciudadd">
      			<div class="help-block with-errors"></div>
    		</div>
  		</div>
		<div class="row">
			
		</div>
	</div>
</div>
<div id="cliente-alert" class="alert alert-dismissible col-md-6" role="alert">
	<a class="close alert-close" data-id="cliente">&times;</a>
  <div id="mensaje"></div>
</div>
<div class="row col-md-12">
	<button type="submit" class="btn btn-success" id="_bot_gc" onclick=""><?=$edicion ? 'Actualizar' : 'Guardar'?> cliente</button>
	<button type="button" class="btn btn-default" id="_bot_lc" onclick="limpiarForm('cliente-form')">Reestablecer formulario</button>
</div>
</form>
<script>

function guardarCliente() {
	var datos = JSON.stringify({
		numcli: 	$('#numcli').val(),
        rfc: 		$('#rfc').val(),
		regimen: 	$('#regimen').val(),
        nombre: 	$('#nombrec').val(),
        nombre_fiscal:	$('#nombref').val(),
        apellido1: 	$('#apellido1c').val(),
        apellido2: 	$('#apellido2c').val(),
        email: 		$('#emailc').val(),
        calle: 		$('#called').val(),
        numext: 	$('#numextd').val(),
        numint: 	$('#numintd').val(),
        /*cruzacon: 	$('#cruzacond').val(),
        ycon: 		$('#ycond').val(),*/
        colonia:	$('#colonia').val(),
        ciudad: 	$('#ciudadd').val(),
        estado: 	$('#estadod').val(),
        cp: 		$('#cpd').val(),
        tipo: 		$('#tipot').val(),
        numero: 	$('#numerot').val(),
        tipo2: 		$('#tipot2').val(),
        numero2: 	$('#numerot2').val()
    });
    var entity = 'cliente';
    var url = 'phrapi/save/' + entity;
	var _request = $.post(url, {data: {json: datos}}, 'json');
	_request.done(function(response) {
		validarSesion(entity, response);
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			$('#numcli').val(response.id);
			clase = "success";
			$("#div_orden").show();
		} else {
			clase = "warning";
		}
		configAlert(entity, clase, response.message); 
	});
	_request.fail( function( jqXHR, textStatus ) {
		configAlert(entity, 'danger', textStatus);
	});
}

$('#cliente-form').validator().on('submit', function (e) {
  if (e.isDefaultPrevented()) {
	  configAlert('cliente', 'warning', 'Tiene un error en el formulario, favor de verificarlo.');
  } else {
	  e.preventDefault();
	  guardarCliente();
  }
});
$("#cliente-alert-button").on("click", function(e) {
	e.preventDefault();
	$("#cliente-alert").hide();
});
$(document).ready(function() {
	hideLoading();
	$("#cliente-alert").hide();
	<?if(isset($showficha) && $showficha):?>
	$('.nav-tabs a[href="#sheet"]').tab('show');
	<?else:?>
	$('.nav-tabs a[href="#home"]').tab('show');
	<?endif;?>
});
</script>