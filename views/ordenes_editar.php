<? require_once 'phincludes/util.php' ?>
<?$datos = $result['datos']?>
<?$tiposTelefono = $intranet->tiposTelefono() ?>
<?$usuarios = $intranet->getUsuarios() ?>
<?$tiposTelefono = $intranet->getTiposTel() ?>
<?$edicion = $result['id'] > 0 ? true : false ?>
<?$datosCliente = $edicion ? $result['datosCliente'] : null ?>
<?$datos = $edicion ? $result['datos'] : null ?>
<?//$clientes = $intranet->typeaheadAll(); ?>
<?$clientes = $result['clientes']; ?>
<style>
<!--
.btn {
	margin-bottom:3px;
}
.label {
	font-weight:normal;
	text-transform: uppercase;
	font-size:18px;
}
.dropdown-menu>li>a {
	color: black;
}
a.dropdown-item {
    text-transform: capitalize;
}
-->
</style>
	<div class="text-center">
	<h2>
	<?if($edicion):?>
	Edición de orden de servicio n° <?=$datos->numero?><br><?=etiquetaOrden($datos->estatus, $datos->nombree)?>
	<?else:?>
	Nueva de orden de servicio.
	<?endif;?>
	</h2>
	</div>
	<div id="div_busqueda">
		<div class="row">
	    	<div class="form-group col-md-8">
	      		<label for="nombre_cliente">Buscar un Cliente</label>
	      		<input autocomplete="off" data-provider="typeahead" <?=htmlValConf('', "Nombre, Nombre Fiscal, RFC ó Número telefónico del cliente...", false)?>value="" class="form-control" id="nombre_cliente" name="nombre_cliente">
	    	</div>
	  	</div>
	</div>
	<div id="div_cliente">
		<?php require 'phincludes/datos-cliente.php'; ?>
	</div>
	<br><br>
	<div id="div_orden">
		<form id="orden-form" role="form" data-toggle="validator">
		<input type="hidden" id="numord" name="numord" value="<?=$edicion ? $datos->numero : ''?>">
		<input type="hidden" id="status" name="status" value="<?=$edicion ? $datos->estatus : 0?>">
		<input type="hidden" id="numnota" name="numnota" value="<?=$edicion ? $datos->nota : ''?>">
		<h3>Accesorios / Cartuchos / Aditamentos con los que se recibe el equipo.</h3>
		<div class="row">
			<div class="col-md-6">
				<textarea readonly 
					id="accesorios"
					placeholder="Accesorios con los que recibe el equipo..."
					rows="12" class="form-control"><?=$edicion ? $datos->accesorios : ''?></textarea>
			</div>
			<h3>Seleccione al menos una opción <span class="glyphicon glyphicon-share-alt" style="transform: rotate(90deg)" aria-hidden="true"></span></h3>
			<div class="col-md-6">
				<div class="form-check">
			        <label class="form-check-label">
			          <input class="form-check-input check-orden bloqueable" type="checkbox" value="Cable corriente"> Cable corriente
			    	</label>
			    </div>
			    <div class="form-check">
			        <label class="form-check-label">
			          <input class="form-check-input check-orden bloqueable" type="checkbox" value="Cargador"> Cargador
			    	</label>
			    </div>
			    <div class="form-check">
			        <label class="form-check-label">
			          <input class="form-check-input check-orden bloqueable" type="checkbox" value="Sin Cables"> Sin Cables
			    	</label>
			    </div>
			    <div class="form-check">
			        <label class="form-check-label">
			          <input class="form-check-input check-orden bloqueable" type="checkbox" value="Cartucho negro"> Cartucho negro
			    	</label>
			    </div>
			    <div class="form-check">
			        <label class="form-check-label">
			          <input class="form-check-input check-orden bloqueable" type="checkbox" value="Cartucho color"> Cartucho color
			    	</label>
			    </div>
			    <div class="form-check">
			        <label class="form-check-label">
			          <input class="form-check-input check-orden bloqueable" type="checkbox" value="Sin cartuchos"> Sin cartuchos
			    	</label>
			    </div>
			</div>
		</div>
		<div id="div_descripcion">
			<h3>Descripci&oacute;n de c&oacute;mo se recibe el equipo y/o servicio que se requiere.</h3>
			<div class="row">
				<div class="form-group col-md-8">
					<textarea
					required
					style="height:150px;"
					type="text"
					data-minlength="5"
					data-error="Escriba una descripción..."
					id="descripcion"
					placeholder="Describa el equipo, marca, caracteristicas, condiciones en que se recibe, etc..."
					class="form-control"><?=$edicion ? trim($datos->descripcion) : ''?></textarea>
					<div class="help-block with-errors"></div>
				</div>
			</div>
			<div id="orden-alert" class="alert alert-dismissible col-md-6" role="alert">
			  <a class="close alert-close" data-id="orden">×</a>
			  <div id="mensaje"></div>
			</div>
			<div class="row col-md-12">
				<button id="boton_terminar_orden" type="button" class="btn btn-success" onclick="terminarOrden()">Terminar Orden</button>
				<button id="boton_guardar_orden" type="submit" class="btn btn-success">Guardar Orden</button>
				<button id="boton_limpiar_orden" type="button" class="btn btn-success" onclick="guardarCliente()">Limpiar Orden</button>
				<a id="boton_nueva_orden" type="button" class="btn btn-success" href="ordenes/add">Orden Nueva</a>
				<?if(isset($datos->nota) && $datos->nota > 0):?>
					<a id="boton_nota" type="button" class="btn btn-primary" href="notas/<?=$datos->nota?>">Ver Nota</a>
				<?else:?>
					<a id="boton_nota" type="button" class="btn btn-primary" href="notas/add/<?=$datos->numero?>">Crear Nota</a>
				<?endif?>
				<button id="boton_abrir_pdf" type="button" class="btn btn-warning">Abrir PDF</button>
			</div>
		</div>
		</form>
		<form id="log-form" role="form" data-toggle="validator">
		<div id="div_logs">
			<div class="row col-md-12">
				<h3>Comentarios para la orden de servicio</h3>
				<div id="historialOrden">
				<?if($edicion):?>
					<?include_once 'views/ordenes_logs.php';?>
				<?endif;?>
				</div>
			</div>
			<div class="row col-md-12">
				<h3>Agregar nuevo mensaje/comentario</h3>
				
				<div class="row">
					<div class="form-group col-md-6">
						<textarea
						required
						type="text"
						data-minlength="5"
						data-error="Escriba un comentario / mensaje..."
						style="height:160px;"
						id="comentario"
						placeholder="Escriba el mensaje o comentario relacionado con la orden de servicio..."
						rows="5"
						class="form-control"></textarea>
						<div class="help-block with-errors"></div>
					</div>
					<div class="col-md-6">
						<div class="row">
							<div class="col-md-6">
								<label for="asignar">¿Es una tarea para asignar?</label>&nbsp;&nbsp;<input id="asignar" class="form-check-input check-orden" type="checkbox" value="Cable corriente" style="vertical-align:text-bottom;">
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<select id="asignado" name="tipot" class="form-control" style="width:100%;">
									<option value="0">¿A qui&eacute;n será asignada?</option>
									<? Html::Options($usuarios, '') ?>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<br>
								<button type="submit" class="btn btn-success" id="_bot_gc">Agregar<br>Comentario</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</form>
	</div>
<script type="text/javascript">

var charMap = {
    "á": "a", "à": "a",
	"é": "e", "è": "e",
	"í": "i", "ì": "i",
	"ó": "o", "ò": "o",
	"ú": "u", "ù": "u"
};

var normalize = function (input) {
 $.each(charMap, function (unnormalizedChar, normalizedChar) {
    var regex = new RegExp(unnormalizedChar, 'gi');
    input = input.replace(regex, normalizedChar);
 });
 return input;
}

var $ibc = $("#nombre_cliente").typeahead({
	/*highlighter: function(item){
		var string_norm = item.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
		this.query = this.query.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
		//string_norm = "<div>" + string_norm + "</div>";
		var myRegex = "" + this.query + "";
		//console.log(string_norm);
		var re = new RegExp(myRegex, "gi");
		string_norm = string_norm.replaceAll(this.query, "<span style='font-weight:600;'>" + this.query + "</span>");
		//string_norm = string_norm.replaceAll(this.query, "XXX" + this.query + "XXX");
		//return string_norm;
		return "<div>" + string_norm + "</div>";
    },*/
	highlighter: function(item){
		var string_norm = item.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
		this.query = this.query.normalize('NFD').replace(/[\u0300-\u036f]/g, "");
		var myRegex = "" + this.query + "";
		var re = new RegExp(myRegex, "gi");
		var indice = string_norm.toLowerCase().indexOf(this.query.toLowerCase());
		string_norm = string_norm.replace(re, "<span style='font-weight:600;'>" + string_norm.substring(indice, indice + this.query.length) + "</span>");
		return "<div>" + string_norm + "</div>";
    },
    source: <?=$clientes?>,
    minLength: 3,
    autoSelect: true,
	items: 15,
	selectOnBlur: false,
	changeInputOnSelect: true,
	changeInputOnMove: true,
	matcher: function(item) {
		var normalizedQuery = normalize(this.query);
		var normalizedNombre = normalize(item.nombre);
		//console.log(normalizedNombre);
		//if(this.query.length && ~normalizedNombre.toLowerCase().indexOf(normalizedQuery.toLowerCase()))
		//{
			//console.log(normalizedNombre + " - " + normalizedQuery);
		//}
		if(this.query.length) return ~normalizedNombre.toLowerCase().indexOf(normalizedQuery.toLowerCase());
	},
	
    afterSelect: function(item) {
        	if(item.id == -1) {
            	$("#div_cliente").show();
            	$("#div_busqueda").hide();
        	} else {
        		cargarCliente(item.id);
        	}
        },
    displayText: function(item) {
        return item.nombre;
    },
    addItem: {id: -1, nombre: "¿No encuentra el cliente? Agregar Nuevo"}
});
$ibc.on("click", function() {$ibc.typeahead("lookup");})
<?if(!$edicion):?>
$("#div_cliente").hide();
$("#div_orden").hide();
$("#div_logs").hide();
$("#div_descripcion").hide();
<?else:?>
$("#div_busqueda").hide();
<?endif;?>

$(".check-orden").each(function(e) {
	if($("#accesorios").val().indexOf($(this).val()) > -1) {
		$(this).prop('checked', true);
		$(this).attr('checked', true);
	}
}); 

$(".check-orden").on("click", function(e){
	var newTag = '- ' + $(this).val() + ' ';
	//alert($(this).prop('checked'));
	if($(this).prop('checked')) {
		$("#accesorios").html($("#accesorios").html() + newTag);
	} else {
		$("#accesorios").html($("#accesorios").html().replace(newTag, ''));
	}
	if($("#accesorios").html() == '') {
		$("#div_descripcion").hide();
	} else {
		$("#div_descripcion").show();
	}
});

function loadLogs() {
	$("#historialOrden").load('ajaxsearch/ordenes?accion=logs&numero=' + $('#numord').val());
}
function configAlert(idAlert, clase, mensaje) {
	idAlert =  '#' + idAlert + '-alert';
	$(idAlert).find("#mensaje").html(mensaje);
	$(idAlert).removeClass("alert-warning");
	$(idAlert).removeClass("alert-success");
	$(idAlert).removeClass("alert-danger");
	$(idAlert).addClass('alert-' + clase);
	$(idAlert).show();
}
function guardarOrden() {
	var numero = $('#numord').val();
	var datos = JSON.stringify({
		numord: 		numero,
        idCliente: 		$('#numcli').val(),
        descripcion: 	utf8_encode($('#descripcion').val()),
        accesorios: 	utf8_encode($('#accesorios').val()),
        recibio:		$('#recibio').val(),
        asignado:		$('#asignado').val()
    });
    var entity = 'orden';
    var url = 'phrapi/save/' + entity;
	var _request = $.post(url, {data: {json: datos}}, 'json');
	_request.done(function(response) {
		validarSesion(entity, response);
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			clase = "success";
			if(numero == '' && response.id != '') {
				window.location = "ordenes/" + response.id;
			} else {
				loadLogs();
				$("#div_logs").show();
				botones();
			}
		} else {
			clase = "warning";
		}
		configAlert(entity, clase, response.message);
	});
	_request.fail( function( jqXHR, textStatus ) {
		configAlert(entity, 'danger', textStatus);
	});
}
function cargarCliente(numcli) {
	var url = 'phrapi/load/cliente';
	var _request = $.post(url, {id: numcli}, 'json');
	_request.done(function(response) {
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			$('#numcli').val(response.id);
			$("#rfc").val(response.rfc );
			$("#nombrec").val( utf8_decode (response.nombre));
			$('#nombref').val(utf8_decode (response.nombre_fiscal));
			$("#apellido1c").val( utf8_decode (response.apellido1));
			$("#apellido2c").val (utf8_decode (response.apellido2));
			$("#emailc").val(response.email );
			
			$("#called").val( utf8_decode (response.calle));
			$("#numextd").val(response.numext );
			$("#numintd").val(response.numint );
			$("#colonia").val( utf8_decode (response.colonia));
			$("#ciudadd").val( utf8_decode (response.ciudad));
			$("#estadod").val( utf8_decode (response.estado));
			$("#cpd").val(response.cp );
			
			$("#numerot").val(response.numero );
			$("#numerot2").val(response.numero2 );
			$("#div_cliente").show();
        	$("#div_busqueda").hide();
			$("#div_orden").show();
		} else {
			clase = "warning";
			configAlert("cliente", clase, response.message);
		}
	});
	_request.fail( function( jqXHR, textStatus ) {
		configAlert(entity, 'danger', textStatus);
	});
}
function terminarOrden() {
	var result = false;
	var numord = $('#numord').val();
	var entity = 'orden';
    var url = 'phrapi/terminar/' + entity;
	$.ajax({
		  type: "POST",
		  async: false,
		  url: url,
		  data: { numord: numord },
		  success: function(response) {
			  validarSesion(entity, response);
			  response = JSON.parse(response);
			  var clase = '';
				if (response.code == 200) {
					clase = "success";
					$("#status").val(2);
				} else {
					clase = "warning";
				}
				botones();
				configAlert("orden", clase, response.message);
				//mostrarOcultarBotonesOrden();
	       },
	       fail: function(response) {
	    	   configAlert(entity, 'danger', textStatus);
	       }
		});
	return result;
}
function bloquearOrden() {
	 $("#descripcion").attr('disabled', 'disabled');
	 $(".bloqueable").attr('disabled', 'disabled');
}
function botones() {
	var estatus = parseInt( $("#status").val() );
	/*
	 * CLAVE	STATUS						=>	ACCIONES POSIBLES
	 *________________________________________________________________________________________
	 *
	 * -1		VACIA( SIN GUARDAR) 		=>	GUARDAR
	 *  0		SIN GUARDAR( TIENE ITEMS )	=>	GUARDAR( LIMPIAR SI TIENE ITEMS )
	 *  1		GUARDADA					=>	TERMINAR, GUARDAR( LIMPIAR SI TIENE ITEMS ), IMPRIMIR, CREAR NOTA
	 *  2		TERMINADA 					=>	NUEVA, IMPRIMIR
	 *________________________________________________________________________________________
	 */
	$("#boton_guardar_orden").hide();
	$("#boton_nueva_orden").hide();
	$("#boton_limpiar_orden").hide();
	$("#boton_terminar_orden").hide();
	$("#boton_nota").hide();
	$("#boton_abrir_pdf").hide();
	$("#mainLog").hide();
	switch( estatus ) {
		case 0:
			$("#boton_guardar_orden").show();
			if( !ordenVacia ) $("#boton_limpiar_orden").show();
			break;
		case -1:
			$("#boton_guardar_orden").show();
			if( !ordenVacia ) $("#boton_limpiar_orden").show();
			break;
		case 1:
			if( !ordenVacia ) {
				
				$("#boton_limpiar_orden").show();
			}
			$("#boton_terminar_orden").show();
			$("#boton_guardar_orden").show();
			$("#boton_nota").show();
			$("#boton_abrir_pdf").show();
			$("#mainLog").show();
			break;
		case 2:
			$("#boton_nueva_orden").show();
			$("#boton_nota").show();
			$("#boton_abrir_pdf").show();
			$("#mainLog").show();
			bloquearOrden();
			break;
	}
}
$("#boton_abrir_pdf").click(function(e) {
	//generarPDFOrden();
	e.preventDefault();  //stop the browser from following
	//window.location.href = 'phpscripts/pdf/generar-orden.php?folnot=' + $("#numord").val();
	window.location.href = "phrapi/ordenpdf?folio=" + $("#numord").val();
	//var objeto_window_referencia;
	//var configuracion_ventana = "menubar=1,location=1,resizable=1,scrollbars=1,status=1,height=500,width=800";
	//objeto_window_referencia = window.open("phrapi/ordenpdf?folio=" + $("#numord").val(), '_blank', configuracion_ventana);
});

/*$(".pdf").on('click', function(e) {
	e.preventDefault();
	alert($(this).prop("href"));
	var ventana = window.open($(this).prop("href"), "Orden de servicio PDF", "menubar=no,location=no,resizable=yes,scrollbars=yes,status=yes,width=1100,height=600");
});*/

$("#asignar").on('click', function(e) {
	var activo = $(this).is(":checked") ? 1 : 0;
	if(activo == 1) {
		$("#asignado").attr("disabled", false);
	} else {
		$("#asignado").val(0);
		$("#asignado").attr("disabled", "disabled");
	}
});
$('#orden-form').validator().on('submit', function (e) {
  if (e.isDefaultPrevented()) {
	  configAlert('orden', 'warning', 'Tiene un error en el formulario, favor de verificarlo.');
  } else {
	  e.preventDefault();
	  guardarOrden();
  }
});
$('#log-form').validator().on('submit', function (e) {
	  if (e.isDefaultPrevented()) {
		  configAlert('log', 'warning', 'Tiene un error en el formulario, favor de verificarlo.');
	  } else {
		  e.preventDefault();
		  agregarLog();
	  }
	});
$("#asignar").prop("checked", false);
$("#asignado").attr("disabled", "disabled");
$("#orden-alert").hide();

botones();
</script>