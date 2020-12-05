<style>
.error_cambio {
	color: red;
}
h3.totales {
	margin:5px;
}
</style>
<div>
	<div class="row">
		<div class="col-md-6">
	      	<h3 class="totales" for="">TOTAL</h3>
	    </div>
	    <div class="col-md-6">
	      	<h3 class="totales" ><span>$</span><span class="pull-right" id="cobro-total"></span></h3>
	    </div>
	</div>
	<div class="row">
		<div class="col-md-6">
	      	<h3 class="totales" for="">Entregado</h3>
	    </div>
	    <div class="col-md-6">
	      	<h3 class="totales" ><span>$</span><input class="pull-right text-right" id="cobro-entregado" maxlength="10" size="7" onkeypress="return checkNumeric();" onblur="currencyFormatUp(this);" /></h3>
	    </div>
	</div>
	<div class="row">
		<div class="col-md-6">
	      	<h3 class="totales" for="">Cambio</h3>
	    </div>
	    <div class="col-md-6">
	      	<h3 class="totales" ><span>$</span><span class="pull-right" id="cobro-cambio"></span></h3>
	    </div>
	</div>
	<div class="row">
		<div class="col-md-6">
	      	<h3 class="totales" for="">Impresora</h3>
	    </div>
	    <div class="col-md-6">
	      	<h3 class="totales" ><select class="pull-right" id="impresora"><option value="1">EPSONT20</option><option value="2">POS58</option></select></h3>
	    </div>
	</div>
</div>
<script>
/*
 * Formatea un numero a 1,999.00 (PRECIO) y lo asigna a un elemento 
 */
function currencyFormatUp(e) {
	e.value = currencyFormat (e.value);
}
function bloquearCobro() {
	$("#btn-cobrar").prop('disabled', 'disabled');
	$("#btn-imprimir").prop('disabled', 'disabled');
}
function desbloquearCobro() {
	$("#btn-cobrar").prop('disabled', false);
	$("#btn-imprimir").prop('disabled', false);
}
function cambio() {
	var total = $('#total').html().replace(',', '');
	var e = $('#cobro-entregado').val().replace(',', '');
	var cambio = e - total;
	if (cambio < 0) {
		$("#cobro-cambio").addClass('error_cambio');
		bloquearCobro();
	} else {
		$("#cobro-cambio").removeClass('error_cambio');
		desbloquearCobro();
	}
	$('#cobro-cambio').html(currencyFormat(cambio));
}
/*
 * Registra el cobro de una nota
 */
function printTicket() {
	var entity = 'nota';
    var url = 'phrapi/imprimir/' + entity;
	var _request = $.post(url, {numero: $('#numnota').val()}, 'json');
	_request.done(function(response) {
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
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
/*
 * Registra el cobro de una nota
 */
function generarCobro(imprimir) {
	//Antes que nada guardamos la nota
	if (hayCambios('cambios')) {
		guardarNota();
	}
	var datos = JSON.stringify({
		numero: $('#numnota').val(),
		entregado: $('#cobro-entregado').val().replace(',',''),
		tipo: $('#impresora').val(),
		imprimir: imprimir
    });
    var entity = 'nota';
    var url = 'phrapi/cobrar/' + entity;
	var _request = $.post(url, {data: {json: datos}}, 'json');
	_request.done(function(response) {
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			$('#statusNota').val(3);
			bloquearCobro();
			setBotones();
			clase = "success";
		} else {
			clase = "warning";
		}
		configAlert('cobro', clase, response.message);
	});
	_request.fail( function( jqXHR, textStatus ) {
		configAlert('cobro', 'danger', textStatus);
	});
}
function cancelarConfirm() {
	var modal = $("#confirm-modal").modal();
	modal.find("#confirm-modal-title").html("Confirmaci&oacute;n cancelacion de nota");
	modal.find("#confirm-modal-text").html("¿Estás seguro de querer cancelar ésta nota?");
	modal.find("#btn-aceptar").on("click", function(e) {
		cancelarNota(modal);
	});
}
function cancelarNota(modal) {
	var datos = JSON.stringify({
		numero: $('#numnota').val()
    });
    var entity = 'nota';
    var url = 'phrapi/cancelar/' + entity;
	var _request = $.post(url, {data: {json: datos}}, 'json');
	_request.done(function(response) {
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			$('#statusNota').val(4);
			clase = "success";
			setBotones();
			bloquearNota();
			modal.modal("hide");
		} else {
			clase = "warning";
		}
		configAlert(entity, clase, response.message);
	});
	_request.fail( function( jqXHR, textStatus ) {
		configAlert(entity, 'danger', textStatus);
	});
}
/*
 * Decide si la nota esta vacia o no
 */
function notaVacia() {
	return ( $("._item").length > 0 ? false: true );
}

$(function () {
	$("#cobro-entregado").on('keyup', function(e) {
		cambio();
	});
	$("#cobro-entregado").on('click', function(e) {
		$(this).select();
	});
});
</script>