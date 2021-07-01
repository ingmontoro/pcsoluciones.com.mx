<? require_once 'phincludes/util.php' ?>
<? $datosConfig = $result['datosConfig']?>
<? $ipEjemplo = $datosConfig->modolocal == '1' ? "127.0.0.1" : $datosConfig->ip ?>
<style>
#tabla-cliente>tbody>tr>td {
	border: 2px solid #f2f2f2;
	padding:0px 10px;
}
table>tbody>tr>td>div {
	text-transform: capitalize;
}
</style>
<h3>Configuración del sistema.</h3>
<div>
	<ul class="nav nav-tabs" id="myTab" role="tablist">
		<li class="nav-item active">
			<a class="nav-link " id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">URL Servidor Tickets</a>
		</li>
	</ul>
</div>
<form id="config-form" role="form" data-toggle="validator">
<input type="hidden" id="numcli" name="numcli" value="<?=$edicion ? $datosConfig->id : ''?>"/>
<div class="tab-content" id="myTabContent">
	<div class="tab-pane active" id="home" role="tabpanel" aria-labelledby="home-tab">
		<br>
		<div class="row has-feedback">
    		<div class="form-group col-md-4">
      			<label for="">IP SERVIDOR:</label><br>
      			<input <?=htmlValConf("IP MAQUINA SERVIDOR", '', true, "A", null, 3)?> value="<?=$datosConfig->ip?>" class="form-control" id="myip" name="myip">
				<div><span>http://</span><span id="span_ip"><?=$datosConfig->ip?></span></div>
      			<div class="help-block with-errors"></div>
    		</div>
  			<div class="form-group col-md-4">
      			<label for="">Modo Sin Snternet (Sólo en maquina servidor)</label>
      			<br>
				<span>Activar Modo Local?&nbsp;</span>&nbsp;<input style="height:24px;width:24px;" type="checkbox" class="check" name="modolocal" id="modolocal" value="" <?=$datosConfig->modolocal == '1' ? "checked" : "" ?> />
      			<div class="help-block with-errors"></div>
    		</div>
  		</div>
		<div class="row has-feedback">
    		<div class="form-group col-md-4">
      			<label for="nombrec">URL Mostrar Ticket</label>
      			<input <?=htmlValConf("URL mostrar", '', true, "A", null, 3)?> value="<?=$datosConfig->showTicketRemote?>" class="form-control" id="mostrar" name="mostrar">
				<div><span>http://</span><span id="span_ip"><?=$ipEjemplo?>/</span><span id="span_mostrar"><?=$datosConfig->showTicketRemote?></span></div>
      			<div class="help-block with-errors"></div>
    		</div>
  			<div class="form-group col-md-4">
      			<label for="">URL Imprimir Ticket</label>
      			<input <?=htmlValConf("URL imprimir", '', true, "A")?> value="<?=$datosConfig->printTicketRemote?>" class="form-control" id="imprimir" name="imprimir">
				<div><span>http://</span><span id="span_ip"><?=$ipEjemplo?>/</span><span id="span_imprimir"><?=$datosConfig->printTicketRemote?></span></div>
      			<div class="help-block with-errors"></div>
    		</div>
  		</div>
		<div class="row">
			<div class="form-group col-md-4">
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
</div>
<script type="text/javascript">
function guardarUsuario() {
	var datos = JSON.stringify({
		mostrar: 	$('#mostrar').val(),
        imprimir: 	$('#imprimir').val(),
		modolocal: 	$('#modolocal').val(),
		myip: 	$('#myip').val()
    });
    var entity = 'config';
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

$('#config-form').validator().on('submit', function (e) {
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
$("#modolocal").on("click", function(e) {
	if($(this).is(":checked")) {
		$(this).val(1);
	} else {
		$(this).val(0);
	}
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