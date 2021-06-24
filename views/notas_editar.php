<? require_once 'phincludes/util.php' ?>
<?$datos = $result['datos']?>
<?$usuarios = $intranet->getUsuarios() ?>
<?//$xml = $intranet->CVA() ?>
<?$tiposTelefono = $intranet->tiposTelefono() ?>
<?$edicion = $result['id'] > 0 ? true : false ?>
<?$datosCliente = $result['datosCliente']?>
<?$hayOrden = isset($datos->orden->numero) && $datos->orden->numero > 0 ? true : false?>
<?
$totalColumnas = 6;//Total de columnas del formulario
$totalAcciones = 1;//Total de acciones de cada registro de la nota
?>
<style>
<!--
.nota-precio-input, .nota-cantidad-input {
	width: auto;
}
.nota-cantidad-input {
	display: inline-block;
}
.divBotones {
	display: inline-block;
	margin-left: 5px;
	vertical-align: middle;
}
hr{
	margin-top: 10px;
	margin-bottom: 10px;
}
.special {
	font-size:large;
}
.txtAreaItemNota {
	min-width:200px;
	width: 45%;
}
.flechasmm {
	cursor: pointer;
}
.table>tbody>tr>td {
	border:0;
	vertical-align:inherit;
	padding:0 8px;
}
.label {
	font-weight:normal;
	text-transform: uppercase;
	font-size:18px;
}
.dropdown-menu>li>a {
	color: black;
}
-->
</style>
<div class="text-center">
	<h2>
	<?if($edicion):?>
		Edición de nota n° <?=$datos->numero?><br><?=etiquetaNota($datos->estatus)?>
	<?else:?>
		Nueva de nota de venta <span id="numnotatxt"></span>
	<?endif;?>
	</h2>
	<?if($hayOrden):?>
		<a href="ordenes/<?=$result['idsec']?>">(orden n°<?=$result['idsec']?>)</a>
	<?endif;?>
</div>
	<?if($hayOrden):$temp = $edicion; $edicion = true;?>
		<div id="div_cliente">
			<?php require 'phincludes/datos-cliente.php'; ?>
		</div><br><br>
	<?$edicion = $temp; unset($temp); endif?>
	<br>
	<div class="row">
		<div class="col-md-12">
			<h3>Detalle de la nota</h3>
		</div>
	</div>
	<div id="div_nota">
		<?if(!isset($datos->estatus) || $datos->estatus == 1 || $datos->estatus == ''):?>
		<div id="div_busqueda_articulo">
			<div class="row">
		    	<div class="form-group col-md-8">
		      		<label for="nombre_articulo">Buscar un artículo</label>
		      		<input <?=htmlValConf('', "Código, nombre, descripción del artículo...", false)?>value="" class="form-control" id="nombre_articulo" name="nombre_articulo">
		    	</div>
		    	<div class="form-group col-md-4">
		    		<label> &Oacute; tambi&eacute;n puedes </label><br>
					<button class="btn btn-default" type="button" onclick="addOpenDescItem()"> Agregar descripci&oacute;n abierta </button>
				</div>
		  	</div>
		</div>
		<?endif;?>
		<br>
		<form action="" method="post" target="_blank" id="ticket_form" class="hidden">
			<textarea style="text-align:left; width: 100%;resize: both;overflow: auto;" rows="7" id="ticket" name="data"><?=isset($datos->dataTicket) && $datos->dataTicket != "" ? $datos->dataTicket : ""?></textarea>
		</form>
		<form id="form-nota">
		<div id="ticketPrevio"></div>
		<div id="divCobrar"></div>
		<input type="hidden" id="numcli" name='numcli' value="<?=$datosCliente->id?>" />
		<input type="hidden" id="numnota" name='numnota' value="<?=$datos->numero?>" />
		<input type="hidden" id="folio" name='folio' value="<?=$datos->orden->numero?>" />
		<input type="hidden" id="itecan" name='itecan' value="" />
		<input type="hidden" id="statusNota" value="<?=$datos->estatus?>" />
		<input type="hidden" id="cambios" value="0" />
		<div class="table-responsive">
			<table class="table" id="_prods" style="width: 100%;">
				<tr class="_nota_cabeza">
					<td>C&oacute;digo</td>
					<td >Art&iacute;culo</td>
					<td style="width:10%;"class="text-center">Precio Unitario</td>
					<td style="width:10%;"class="text-center">Cantidad</td>
					<td style="width:10%;" class="text-right">Importe</td>
					<td style="width:10%;" colspan="<?=$totalAcciones;?>"></td>
				</tr>
				<tr class="text-center">
					<td colspan="<?=$totalColumnas;?>"> <hr /> </td>
				</tr>
				<tr class="_nota_fila"><!-- BOTONES -->
					<td style="vertical-align: top;padding-left:15px;" colspan="<?=$totalColumnas - 3?>" rowspan="3">
						<div class="row">
							<div id="nota-alert" class="alert alert-dismissible col-md-12" role="alert">
							  <a class="close alert-close" data-id="nota">×</a>
							  <div id="mensaje"></div>
							</div>
						</div>
						<div class="row">
							<div style="float: right; display: inline-block; ">
								<button id='boton_cancelar_nota' class="btn btn-danger" type="button" onclick="cancelarConfirm();">Cancelar Nota</button>
								<button id='boton_cobrar_nota' class="btn btn-primary" type="button" onclick="cobrarTicket();">Cobrar</button>
								<!--
								<button id='boton_ver_ticket' class="btn btn-warning" type="button" onclick="showTicketR();">Ver ticket (AJAX)</button>
								<button id='boton_imprimir_ticket' class="btn btn-warning" type="button" onclick="printTicketR(this);">Imprimir ticket (AJAX)</button>
								-->
								<button id='boton_ver_ticket' type="button" class="btn btn-warning" onclick="enviarMostrar();">Ver Ticket</button>
								<button id='boton_imprimir_ticket' type="button" class="btn btn-warning" onclick="enviarImprimir();">Imprimir Ticket</button>
								<button id='boton_guardar_nota' class="btn btn-primary" type="button" onclick="guardarNota();">Guardar nota</button>
								<button id="boton_limpiar_nota" class="btn btn-default" type="button" onclick="limpiarConfirm();">Limpiar Nota</button>
								<?if(isset($datos->orden->numero) && $datos->orden->numero != '' && $datos->orden->numero > 0 && $datos->estatus == 4):?>
									<?if(isset($datos->numero2) && $datos->numero2 != '' && $datos->numero2 > 0):?>
										<a id='boton_nueva_nota' class="btn btn-success" type="button" href="notas/<?=$datos->numero2?>">Ver nota activa(<?=$datos->numero2?>) para orden <?=$datos->orden->numero?></a>
									<?else:?>
										<a id='boton_nueva_nota' class="btn btn-success" type="button" href="notas/add/<?=$datos->orden->numero?>">Nueva nota para orden <?=$datos->orden->numero?></a>
									<?endif;?>
								<?else:?>
									<a id='boton_nueva_nota' class="btn btn-success" type="button" href="notas/add">Nota Nueva</a>
								<?endif;?>
							</div>
						</div>
					</td>
					<td class="text-right special">Subtotal</td>
					<td class="text-right special"><span class="pull-left" style="float: left;">$</span><span id="subtotal">0.00</span><!-- span class="_simbolo">MXN</span --></td>
				</tr>
				<tr class="_nota_fila">
					<td class="text-right special"><input 	type="checkbox" id="_iva"
											style="width: 20px; height: 20px; border: 2px; cursor: pointer;vertical-align: sub;margin-right:5px;"
											<?php 
												if($datos->aplicaiva){
													echo "checked='checked'";
												}
											?>"
											onclick="IVAClick();" />IVA</td>
					<td class="text-right special"><span class="pull-left">$</span><span id="iva">0.00</span><!-- span class="_simbolo">MXN</span --></td>
				</tr>
				<tr class="_nota_fila">
					<td class="text-right special">Total</td>
					<td class="text-right special" style="min-width: 120px;"><span class="pull-left">$</span><span id="total">0.00</span><!-- span class="_simbolo">MXN</span --></td>
				</tr>
			</table>
		</div>
		<!--
		<div>
			<textarea id="ticketJson"><?=isset($datos->dataTicket) && $datos->dataTicket != "" ? $datos->dataTicket : ""?></textarea>
		</div>
		-->
	</form>
</div>
<!-- Button trigger modal -->
<!-- Modal -->
<div class="modal fade" id="ticket-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="cobrar-modal-title">Ticket #<span id="numero-ticket1"></span></h4>
      </div>
      <div class="modal-body" id="detalle-ticket" style="text-align:-webkit-center;"></div>
      <div class="modal-footer" style="text-align:center;">
      	<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="cobro-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="margin:60% 0;max-width:460px;">
      <div class="modal-header">
        <h4 class="modal-title" id="cobrar-modal-title">Cobro de ticket #<span id="numero-ticket"></span></h4>
      </div>
      <div class="modal-body">
        <?include_once 'phincludes/cobrar.php';?>
      </div>
      <div id="cobro-alert" class="alert alert-dismissible col-md-12" role="alert">
		  <a class="close alert-close" data-id="cobro">×</a>
		  <div id="mensaje"></div>
	  </div>
      <div class="modal-footer" style="text-align:center;">
      	<button id="btn-cobrar" type="button" class="btn btn-primary" onclick="generarCobro(false);">Cobrar</button>
      	<!-- button id="btn-imprimir" type="button" class="btn btn-primary" onclick="generarCobro(true);">Cobrar e imprimir</button -->
		<!-- <button id="btn-imprimir" type="button" class="btn btn-primary" onclick="generarCobroR(true);">Cobrar e imprimir R</button> -->
		<button id="btn-imprimir" type="button" class="btn btn-primary" onclick="generarCobroR(true);">Cobrar e Imprimir</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<!-- <div>
	<table>
		<tr>
			<td>clave</td>
			<td>descripcion</td>
			<td>marca</td>
			<td>disponible</td>
			<td>precio</td>
			<td>moneda</td>
			<td></td>
		</tr>
	<?//foreach ($xml as $item):?>
		<tr>
			<td><?//=$item->clave;?></td>
			<td><?//=$item->descripcion;?></td>
			<td><?//=$item->marca;?></td>
			<td><?//=$item->disponible;?></td>
			<td><?//=$item->precio;?></td>
			<td><?//=$item->moneda;?></td>
			<td><button target='blank' onclick="javascript:window.open('<?=$item->imagen;?>')">Ver imagen</button></td>
		</tr>
	<?//endforeach;?>
	</table>
</div> -->
<script type="text/javascript">
var last_ = "";
/*
 * Agrega una entrada abierta nueva a la nota de venta
 */
 function enviarMostrar() {
	setupForm('<?=$config["showTicketRemote"]?>');
	$("#ticket_form").submit();
}
function enviarImprimir() {
	setupForm('<?=$config["printTicketRemote"]?>');
	$("#ticket_form").submit();
}
function setupForm(accion) {
	$("#ticket_form").attr('action', accion);
}
function showTicketR() {
	var url = '<?=$config["showTicketRemoteAjax"]?>';
	var ticketData = $("#ticket").val();
	if (ticketData.trim() == "") {
		ticketData = $("#test").html();
	}
	
	var _request = $.post(url, {data: ticketData});
	_request.done( function(response) {
		response = JSON.parse(response);
		$("#detalle-ticket").html(response.datos.ticketHTML.replace('null', ''));
		$("#ticket-modal").modal();
	});
	_request.fail( function( jqXHR, textStatus ) {
		alert("FAIL");
	});		
}

function addOpenDescItem() {
	crearFila({codigo:'free', stock:99999}, 1);
}
var $ibc = $("#nombre_articulo").typeahead({
    source: function (cadena, process) {
        last_ = cadena;
        var result = null;
        $.ajax({
            url: "phrapi/search/articulo",
            data: {query: cadena},
            type: "post",
            dataType: "json",
            async: false,
            success: function(data) {
                result = data;
            } 
         });
        return process(result);
    },
    minLength: 4,
    autoSelect: false,
    afterSelect: function(item) {
    	if (item.stock > 0) {
    			crearFila( item );
    			$("#nombre_articulo").val(last_);
			}
        },
    displayText: function(item) {
        return item.corta + " - " + item.precio + " (" + item.stock + ")";
    },
    //addItem: {id: -1, nombre: "¿No encuentra el cliente? Agregar Nuevo"}
});
$ibc.on("click", function() {$ibc.typeahead("lookup");})
<?if(!$hayOrden):?>
$("#div_cliente").hide();
//$("#div_busqueda_articulo").hide();
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
function formarNota() {
	//Verificar y asignar el numero de cliente si ESTE EXISTE
	var _numcli = $("#numcli").val();
	if( _numcli != '' && _numcli > 0) {
		$("#numclic").val( _numcli );
		//buscarCliente();
	}
	//$("#div_nota").show();
	//Autocompletar los items de la nota cuando se refresque la pagina...
	var _itecan = $("#itecan").val();
	_itecan = "adads";
	//_itecan = "12347-1,12345-1,12346-1,23456-1,0-Formateo-350-1,0-mantenimiento-200-1";
	//alert( _itecan ); //datos.length );
	if( _itecan != '' ) {
		//alert(_itecan);
		var data = '<?=$datos->data?>';
		if(data != '') {
			var items = JSON.parse(data);
			//alert(items.length);
			$.each( items, function( index, item ){
				crearFila( item, item.cantidad, item.codigo, item.descripcion, item.precio, 0);
			});
		}
		
		
	}
	setBotones();
}
function ordenNueva() {
	window.location = "orden/add";
}
function crearNota() {
	window.location = "nota/add";
}

function guardarOrden() {
	var datos = JSON.stringify({
		numord: 		$('#numord').val(),
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
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			$('#numord').val(response.id);
			clase = "success";
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
			  response = JSON.parse(response);
			  var clase = '';
				if (response.code == 200) {
					clase = "success";
				} else {
					clase = "warning";
				}
				configAlert("orden", clase, response.message);
				//mostrarOcultarBotonesOrden();
	       },
	       fail: function(response) {
	    	   configAlert(entity, 'danger', textStatus);
	       }
		});
	return result;
}
/*
 * Guarda la nota de venta acutal
 */
function guardarNota(estatus) {
	//var nota = $( "#form-nota" ).validate();
	var result = false;
	//var folio = $('#numord').val();
	var entity = 'nota';
    var url = 'phrapi/save/' + entity;
    var numero = $('#numnota').val();
	estatus = estatus || 1;
	var itemCantidad = '';
	var aplicarIVA = $('#_iva').prop('checked') ? 'true' : 'false';
	if (obtenerItemsCantidad()) {
		//itemCantidad = obtenerItemsCantidad();
		itemCantidad = $("#itecan").val();
		var _request = $.post( url, { 
			data: { json: JSON.stringify({
				numero: 		numero,
				folio: 			$('#folio').val(),
	            idCliente: 		$('#numcli').val(),
	            itemCantidad: 	itemCantidad,
	            aplicarIva: 	aplicarIVA
	        })}
	    });
		/*_request.done( function(response) {
			response = JSON.parse(response);
			  var clase = '';
				if (response.code == 200) {
					clase = "success";
					$('#numnotatxt').html(response.id)
					$('#numnota').val(response.id)
					$("#statusNota").val(1);
					setBotones();
				} else {
					clase = "warning";
				}
				configAlert("nota", clase, response.message);
		});*/
		_request.done( function(response) {
			validarSesion(entity, response);
			response = JSON.parse(response);
			  var clase = '';
				if (response.code == 200) {
					clase = "success";
					if((numero == '' || numero == 0)&& response.id != '') {
						window.location = "notas/" + response.id + "?tip=Nota guardada correctamente.";
						return;
					} else {
						setBotones();
					}
				} else {
					clase = "warning";
				}
				configAlert("nota", clase, response.message);
		});
		_request.fail( function( jqXHR, textStatus ) {
			configAlert("nota", 'danger', textStatus);
		});
	}
}
/*
 * Obtiene una cadena que guarda la info de los articulos, descripciones, cantidades y precios de la nota de venta.
 * Nota: se usa para enviar la info al server.
 */
function obtenerItemsCantidad() {
	var mensaje = "";
	var itemCantidad = '';
	$("._item").each( function( index ) {
		var iid = $(this).prop('id');
		var code = $(this).find("#codigo").html();
		//alert(code);
		var cant = $("#" + iid + "-cantidad").val();
		var prec = $("#" + iid + "-precio").val();
		var desc = $("#" + iid + "-descripcion").val();
		var acce = $("#" + iid + "-esAccesorio").prop("checked") ? '1' : '0';
		if( code == '000000' ) {
			prec = prec.replace(',', '');
			if (null == prec || prec == '' || parseFloat(prec) == 0) {
				mensaje += "Articulo #" + (index + 1) + " de la lista SIN PRECIO.<br />";
			}
			if (null == desc || desc == '') {
				mensaje += "Articulo #" + (index + 1) + " de la lista SIN DESCRIPCION.<br />";
			}
			
			if (acce == 1 || acce == '1') {
				itemCantidad += iid + "||" + desc.replace('-', '_') + "||" + prec + "||" + cant + "||" + acce + ",";
			} else {
				itemCantidad += iid + "||" + desc.replace('-', '_') + "||" + prec + "||" + cant + ",";
			}
		} else {
			
			itemCantidad += iid + "||" + cant + ",";
		}
	});
	itemCantidad = itemCantidad.substring(0, itemCantidad.length - 1);
	if (mensaje != "") {
		//mensaje += "<br /><div class='tipAlert'>" + tipAlertNota + "</div>";
		//showMessageDiv(mensaje, "alert", "nota", 15000);
		configAlert('nota', 'warning', mensaje);
		return false;
	} else {
		$("#itecan").val(itemCantidad);
		return true;
	}
	//return itemCantidad;
}
function crearFila( item, cantidadPre, uIdPre, descripcion, precio, esAcce) {
	//Checar si ya existe el item
	var uniqueId = '';
	var needDesc = false;
	var wasEmpty = notaVacia();
	if( isFreeItem (item.codigo)) {
		if( null == uIdPre || uIdPre == '' || uIdPre == 0 || uIdPre == '0' || uIdPre == 'free') {
			uniqueId = createUniqueID();
			needDesc = true;
		} else {
			uniqueId = uIdPre;
		}
	}
	var filasExistentes = $( '#' + item.codigo );
	if( filasExistentes.length && !isFreeItem (item.codigo) ) {
		
		var inputCantidad = $( '#' + item.codigo + '-cantidad' );
		var spanTotal = $( '#' + item.codigo + '-total' );
		var cantidad = 0;
		try {
			cantidad = parseInt( inputCantidad.val() );
		} catch( e ) {
			cantidad = 1;
		}
		if( cantidad < item.stock ) {
			inputCantidad.val( ++cantidad );
			spanTotal.html( currencyFormat(parseFloat( item.precio * cantidad )));
			calcularTotales();
		}
		if (wasEmpty) {
			mostrarOcultarBotones();
		}
		return;
	}
	
	//alert(item['nombre']);
	var filaNueva = jQuery("<tr>");
	filaNueva.addClass("_item");
	filaNueva.prop( 'id', isFreeItem (item.codigo) ? uniqueId : item.codigo );
	var cuerpoTabla = $("#_prods > tbody");
	var linea = cuerpoTabla.children('tr:nth-last-child(4)');
	
	//var nombreCeldas = new Array('codigo','cantidad','nombre','corta','descripcion','precio', 'npiezas', 'total', 'acciones');
	var nombreCeldas = new Array('codigo','corta','precio', 'npiezas', 'total', 'acciones');
	var nombresBotones = new Array('quitar');
	
	var spanSPesos = jQuery("<span>"); spanSPesos.addClass("pull-left").append("$");
	var spanMXN = jQuery("<span>"); spanMXN.addClass("pull-left").append("");
	var spanImporte = jQuery("<span>");
	
	var celdas = new Array();
	var botones = new Array();
	//var cantidad = 2;
	
	
	
	for( var i = 0; i < nombreCeldas.length; i ++ ) {
		
		celdas[ i ] = jQuery("<td>");
		celdas[ i ].addClass("_campo_nota");
		if( nombreCeldas[ i ] == 'acciones' ) {
			celdas[ i ].addClass("text-center");
			celdas[ i ].prop('colspan', nombresBotones.length );
			for( var b = 0; b < nombresBotones.length; b ++ ) {
				
				botones[ b ] = jQuery("<button>");
				botones[ b ].addClass("btn btn-default _bot_acc");
				//botones[ b ].append( nombresBotones[ b ] );
				botones[ b ].append('<span class=" glyphicon glyphicon-trash"></span>');
				if( nombresBotones[ b ] == 'quitar' ) {
					
					botones[ b ].addClass("_bot_eli");
					botones[ b ].bind('click', function( e ){
						algoCambio('cambios');
						e.preventDefault();
						filaNueva.removeClass('_item');
						//filaNueva.next().effect( 'fade', {}, 650, function() { filaNueva.next().remove(); } );
						filaNueva.next().remove();
						//filaNueva.effect( 'fade', {}, 650, function() { filaNueva.remove(); calcularTotales(); } );
						filaNueva.remove();
						calcularTotales();
						if( null != uniqueId && uniqueId != '' ) {
							
							//eliminar de la variable se session>>items>>uniqueId
							//unsetSessionVar( 'items>>' + uniqueId );
						}
					});
				} else {
					botones[ b ].prop("disabled", true);
				}
				celdas[ i ].append( botones[ b ] );
			}
		} else if( nombreCeldas[ i ] == 'precio' ) {
			celdas[ i ].addClass("text-right");
			if (isFreeItem (item.codigo)) {
				
				var inputPrecio = jQuery("<input id='" + uniqueId + "-precio' class='nota-precio-input text-right pull-right form-control' size=5 maxlength=9 onkeypress='return checkNumeric();' onblur='currencyFormatUp(this);' />");
				if( null == precio || precio == '' ) {
					
					precio = 0;
				}
				inputPrecio.change(function() { algoCambio('cambios'); });
				inputPrecio.val( currencyFormat (parseFloat (precio)) );
				inputPrecio.blur( function() {
					
					//saveFreeDataForItem( uniqueId );
					//saveIteCanInfo();
					arreglaCantidadFree( uniqueId );
				});
				inputPrecio.click( function() {
					
					this.select();
				});
				celdas[ i ].append( spanSPesos.clone() ).append( inputPrecio ).append( spanMXN.clone() );
			} else {
				
				celdas[ i ].append( spanSPesos.clone() ).append( currencyFormat (parseFloat (item.precio)) ).append( spanMXN.clone() );
			}
		} else if( nombreCeldas[ i ] == 'npiezas' ) {
			celdas[ i ].addClass("text-center");
			var divMas = jQuery("<div>");
			var divMenos = jQuery("<div>");
			var inputCantidadFila = jQuery("<input class='text-center form-control nota-cantidad-input'>");
			var idInputCantidadFila = ( uniqueId == '' ?  item.codigo : uniqueId ) + '-cantidad';
			inputCantidadFila.prop('id', idInputCantidadFila);
			inputCantidadFila.prop('readonly', 'readonly');
			inputCantidadFila.focus(function() {
				inputCantidadFila.addClass('_focused');
			});
			inputCantidadFila.blur(function() {
				inputCantidadFila.removeClass('_focused');
			});
			inputCantidadFila.change(function() {
				if( null != uniqueId && uniqueId != '' ) {
					
					arreglaCantidadFree( uniqueId );
				} else {
					
					arreglaCantidad( inputCantidadFila, item );
				}
			});
			inputCantidadFila.keydown(function( event ) {
				//var _val = parseInt( inputCantidadFila.val() );
				if ( event.which == 38 ) {
					event.preventDefault();
					//_val ++;
					sumarUno( inputCantidadFila, item );
					if( null != uniqueId && uniqueId != '' ) {
						
						arreglaCantidadFree( uniqueId );
					} else {
						
						arreglaCantidad( inputCantidadFila, item );
					}
				} else if ( event.which == 40 ) {
					event.preventDefault();
					//_val --;
					restarUno( inputCantidadFila, item );
					if( null != uniqueId && uniqueId != '' ) {
						
						arreglaCantidadFree( uniqueId );
					} else {
						
						arreglaCantidad( inputCantidadFila, item );
					}
				}
				//inputCantidadFila.val( _val );
				//inputCantidadFila.change();
			});
			inputCantidadFila.prop("size", '2');
			inputCantidadFila.prop("maxlength", '3');
			inputCantidadFila.val( null != cantidadPre ? cantidadPre : 1 );
			
			divMas.append('&#x25B2;');
			divMas.click( function() {
				sumarUno( inputCantidadFila, item );
				//alert('UID: ' + uniqueId);
				if( null != uniqueId && uniqueId != '' ) {
					
					arreglaCantidadFree( uniqueId );
				} else {
					
					arreglaCantidad( inputCantidadFila, item );
				}
				//inputCantidadFila.change();
			}); 
			divMenos.append('&#x25BC;');
			divMenos.click( function() {
				restarUno( inputCantidadFila, item );
				if( null != uniqueId && uniqueId != '' ) {
					
					arreglaCantidadFree( uniqueId );
				} else {
					
					arreglaCantidad( inputCantidadFila, item );
				}
				//inputCantidadFila.change();
			});
			divMas.addClass('flechasmm');
			divMenos.addClass('flechasmm');
			//inputCantidadFila.after( divMas );
			//inputCantidadFila.after( divMenos );
			var divbotones = jQuery("<div class='divBotones'>");
			divbotones.append(divMas).append(divMenos);
			celdas[ i ].append(inputCantidadFila).append(divbotones);
		} else if( nombreCeldas[ i ] == 'total' ) {
			celdas[ i ].addClass("text-right");
			if( null != uniqueId && uniqueId != '' ) {
				//existe un uniqueId
				spanImporte.append( currencyFormat (parseFloat( precio * cantidadPre ) ) );//Usar variables
			} else {
				//Es item nuevo de la lista
				spanImporte.append( currencyFormat (parseFloat( item.precio * ( null == cantidadPre ? 1 : cantidadPre ) ) ));//Si es de la lista
			}
			spanImporte.addClass( "_importe" );
			spanImporte.prop( 'id', ( uniqueId == '' ?  item.codigo : uniqueId ) + '-total' );
			celdas[ i ].append( spanSPesos.clone() ).append( spanImporte.clone() ).append( spanMXN.clone() );
		//} else if( isFreeItem (item.codigo) && nombreCeldas[ i ] == 'descripcion' ){
		} else if( isFreeItem (item.codigo) && nombreCeldas[ i ] == 'corta' ){
			var checkedAsAcce = '';
			if (esAcce && esAcce == 1 || esAcce == '1') {
				checkedAsAcce = ' checked="checked" ';
			}
			var inputCantidadFila = jQuery("<textarea id='" + uniqueId + "-descripcion' " +
					"class='txtAreaItemNota form-control' " +
					"onkeypress='return checkAlphaNumeric();'></textarea>" /*+
					"<div style='float: right;'>" +
					"<label class='labelItemNota' title='&iquest;es accesorio?' for='" + uniqueId + "-esAccesorio'>" + txtLabelItemNota + "</label><input class='checkItemNota' type='checkbox'" + checkedAsAcce + "id='" + uniqueId + "-esAccesorio'/></div>"*/);
			inputCantidadFila.change(function() { algoCambio('cambios'); });
			if( null != descripcion && descripcion != '' ) {
				
				inputCantidadFila.val( descripcion );
			}
			inputCantidadFila.blur( function() {
				
				//saveFreeDataForItem( uniqueId );
				//saveIteCanInfo();
			});
			celdas[ i ].append( inputCantidadFila );
		} else if (nombreCeldas[ i ] == 'codigo') {
			var codigo = "000000";
			if (!isFreeItem (item.codigo)) {
				codigo = fixFolSize(item.codigo.toUpperCase());				
			}
			celdas[ i ].append(codigo);
			celdas[ i ].prop('id', 'codigo');
		} else {
			
			celdas[ i ].append( item[ nombreCeldas[ i ] ]);
		}
		filaNueva.append( celdas[ i ] );
	}
	linea.after( filaNueva );
	filaNueva.after( linea.clone() );
	if (wasEmpty) {
		//alert('');
		setBotones();
	}
	if (needDesc) {
		$("#" + uniqueId + "-descripcion").focus();
	}
	calcularTotales();
}
/*
 * Arregla o valida que la cantidad de un articulo sea siempre
 * mayor a 1 y menor o igual al stock de un articulo del sistema
 */
function arreglaCantidad( _inp, _item ) {
	var _val = 1;
	try {
		_val = parseInt( _inp.val() );
	} catch( e ) {				
	}
	if( _val < 1 || isNaN( _val ) ) {
		_inp.val( 1 );
	} else if( _val > parseInt( _item.stock ) ) {
		_inp.val( _item.stock );
	}
	$('#' + _item.codigo + '-total').html (currencyFormat (parseFloat ( _item.precio * _inp.val())));
	calcularTotales();
}

/*
 * Arregla o valida que la cantidad de un articulo sea siempre
 * mayor a 1 de un articulo abierto (free)
 */
function arreglaCantidadFree( uniqueId ) {
	var _precio = $( "#" + uniqueId + '-precio' );
	var _cantidad = $( "#" + uniqueId + '-cantidad' );
	if( _precio.val().replace(',', '') == 0 ) {
		
		_precio.val( 0 );
	}
	if( _cantidad.val() < 0 ) {
		
		_cantidad.val(1);
	}
	$('#' + uniqueId + '-total').html( currencyFormat (parseFloat( _precio.val().replace(',', '') * _cantidad.val() )) );
	calcularTotales();
}

/*
 * Suma uno a la cantidad de un item dado
 * Puede ser disparado por usar las flecas fisicas
 * del teclado o las flechas de la interfaz
 */
function sumarUno( _inp, item ) {
	var _val = parseInt( _inp.val() );
	_val ++;
	_inp.val( _val );
	algoCambio('cambios');
	//arreglaCantidad( _inp, item, '' );
}

/*
 * Resta uno a la cantidad de un item dado
 * Puede ser disparado por usar las flecas fisicas
 * del teclado o las flechas de la interfaz
 */
function restarUno( _inp, item ) {
	var _val = parseInt( _inp.val() );
	_val --;
	if (_val < 1) {
		_val = 1;
	}
	_inp.val( _val );
	algoCambio('cambios');
	//arreglaCantidad( _inp, item, '' );
}

/*
 * Crea una cadena(id) unica para los items/articulos abiertos que se usan en la nota de venta
 * Nota: puede usarse para cualquier cosa el ID generado
 */
function createUniqueID() {
	var _time = new Date();
	var _rnumber = _time.getFullYear() + '' + _time.getMonth() + '' + _time.getDay() + '' + _time.getHours() + '' + _time.getMinutes() + '' + _time.getMilliseconds();
	//alert(_rnumber);
	return _rnumber;
}

/*
 * Muestra una ventana para registrar el cobro de una nota de venta
 */
 /*
  * Muestra una ventana para registrar el cobro de una nota de venta
  */
 function cobrarTicket() {
 	if (obtenerItemsCantidad())	{
 		var total = $('#total').html().replace(',','');
 		total = parseFloat(total);
 		if (total > 0) {
 			var numero = $('#numnota').val();
 			$("#cobro-total").html(currencyFormat(total));
 			$("#cobro-entregado").val(currencyFormat(total));
 			$("#numero-ticket").html($("#numnota").val());
 			cambio();
 			setTimeout(function() {
				$("#cobro-entregado").focus();
				$("#cobro-entregado").select();
			}, 500);
 		} else {
 			
 		}
 		$("#cobro-modal").modal();
 	}
 }

 /*
  * Calcula importes y totales enl a nota de venta
  * NOTA: usa metodos que guardan datos en sesion los cuales se deben erradicar 
  */
 function calcularTotales() {
 	var dato_iva = .16;
 	var aplicarIVA = $("#_iva").prop("checked");
 	var _subn = 0;
 	var _ivan = 0;
 	var _totaln = 0;
 	//_____________________________________
 	var _sub = $("#subtotal");
 	var _iva = $("#iva");
 	var _total = $("#total");
 	//_____________________________________
 	$("._importe").each( function( index ) {
 		_subn += parseFloat( $(this).html().replace(',', '') );
 	});
 	if( aplicarIVA ) {
 		_ivan = _subn * dato_iva;
 		_totaln = _ivan + _subn;
 	} else {
 		_ivan = 0;
 		_totaln = _subn;
 	}
 	
 	//setSessionValue( 'post', 'apliva', aplicarIVA );
 	
 	_sub.html( currencyFormat (parseFloat ( _subn )));
 	_iva.html( currencyFormat (parseFloat ( _ivan )));
 	_total.html ( currencyFormat (parseFloat ( _totaln )));
 	
 	//saveIteCanInfo();
 	/*var _req = $.post( './phpscripts/getsessionvaluesbypost.php', { 
 		variable: 'itecan'
 	});
 	_req.done( function( msg ) {
 		
 	});
 	_req.fail( function( msg ) {
 		
 	});*/
 }
 function bloquearNota() {
	 $("#_iva").attr('disabled', 'disabled');
	 $("._bot_eli").attr('disabled', 'disabled');
	 $(".flechasmm").unbind('click');
	 $(".nota-precio-input").attr('disabled', 'disabled');
	 $(".nota-cantidad-input").unbind();
	 $(".txtAreaItemNota").attr('disabled', 'disabled');
 }
 function limpiarConfirm() {
		var modal = $("#confirm-modal").modal();
		modal.find("#confirm-modal-title").html("Confirmaci&oacute;n borrar artículos de nota");
		modal.find("#confirm-modal-text").html("¿Estás seguro de querer eliminar TODOS los artículos de la nota?");
		modal.find("#btn-aceptar").on("click", function(e) {
			limpiarNota();
			modal.modal("hide");
		});
	}
 /*
  * Limpia los items de la nota de venta
  */
 function limpiarNota() {
 	$("._bot_eli").each( function( index ) {
 		$(this).trigger('click');
 	});
 	setBotones();
 }
 
 /*
  * Muestra/oculta botones de la nota de venta en base al estatus actual de la misma
  */
 function setBotones() {
 	var estatus = parseInt( $("#statusNota").val() );
 	var estaVacia = notaVacia();
 	//alert( estatus );
 	//alert( estaVacia );
 	/*
 	 * CLAVE	STATUS						=>	ACCIONES POSIBLES
 	 *________________________________________________________________________________________
 	 *
 	 *  0		NUEVA						=>	GUARDA, LIMPIAR (SI TIENE ITEMS)
 	 *  1		GUARDADA					=>	TERMINAR, GUARDAR( LIMPIAR SI TIENE ITEMS )
 	 *  2		IMPRESA(Y COBRADA) 			=>	NUEVA, VER TICKET, IMPRIMIR TICKET
 	 *  3		COBRADA						=>  NUEVA, VER TICKET, IMPRIMIR TICKET
 	 *  4		CANCELADA					=>  NUEVA
 	 *  
 	 *________________________________________________________________________________________
 	 */
 	$("#boton_cancelar_nota").hide();
 	$("#boton_guardar_nota").hide();
 	$("#boton_nueva_nota").hide();
 	$("#boton_limpiar_nota").hide();
 	$("#boton_imprimir_ticket").hide();
 	$("#boton_ver_ticket").hide();
 	$("#boton_cobrar_nota").hide();
 	if(estatus > 1 && estatus < 4) {
 		$("#boton_cancelar_nota").show();
 		bloquearNota();
 	}
 	switch( estatus ) {
 		case 0:
 			if( !estaVacia ) {
 				$("#boton_guardar_nota").show();
 				$("#boton_limpiar_nota").show();
 			}
 			break;
 		case 1:
 			if( !estaVacia ) {
 				$("#boton_limpiar_nota").show();
 				$("#boton_cobrar_nota").show();
 				$("#boton_guardar_nota").show();
 			}
 			break;
 		case 2:
 			$("#boton_imprimir_ticket").show();
 			$("#boton_ver_ticket").show();
 			$("#boton_nueva_nota").show();
 			//$("#boton_guardar_nota").show();
 			break;
 		case 3:
 			$("#boton_ver_ticket").show();
 			$("#boton_imprimir_ticket").show();
 			$("#boton_nueva_nota").show();
 			break;
 		case 4:
 			var folio = $("#folio").val();
 			var numNota = $("#numnota").val();
 			var numCli = $("#numcli").val();
 			$("#boton_nueva_nota").show();
 			bloquearNota();
 			break;
 	}
 }
$(document).ready(function() {
	hideLoading();
	$("#nota-alert").hide();
	$("#cobro-alert").hide();
	formarNota();
	setBotones();
	<? if (isset($datos->tip) && $datos->tip != '') { ?>
	configAlert("nota", "success", '<?=$datos->tip?>');
	<? } ?>
});
</script>