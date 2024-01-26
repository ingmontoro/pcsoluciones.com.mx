<? //include_once 'phrapi/index.php' ?>
<? //require_once 'phincludes/util.php' ?>
<? $allowActions = getValueFrom($_GET, 'alla', '', FILTER_SANITIZE_PHRAPI_MYSQL);?>
<? $logs = isset($datos) ? $datos->logs : $result?>
<? $usuarios = isset($usuarios) ? $usuarios : $intranet->getUsuarios() ?>
<? $login = $factory->Access->logged() ?>
<? $disabled = (!isset($allowActions) || $allowActions == '' ? '' : 'disabled') ?>
<style type="text/css">
th {
	font-weight:normal;
}
</style>
<div id="logs-<?=$logs{0}->idOrden?>">
	<div id="divTablaLogs">
		<div class="table-responsive">
			<table id="tablaLog" class="table table-striped table-hover" style="margin: 0;">
				<tr>
					<th class="" style="width: 30%;">Comentario / mensaje
					</th>
					<th class="text-center">Acciones
					</th>
					<th class="text-center">Creado por
					</th>
					<th class="text-center">Fecha
					</th>
					<th class="text-center">Modificado por
					</th>
					<th class="text-center">Modificado
					</th>
					<th class="text-center">Asignado a:
					</th>
					<th class="text-center">Terminado
					</th>
				</tr>
				<tr class="">
					<td colspan="8"></td>
				</tr>
				<?if(count($logs) < 1):?>
				<tr>
					<td colspan="8">No existen comentarios para esta orden...</td>
				</tr>
				<?else: ?>
				<?foreach($logs as $log):?>
				<tr class="filaLog filaLog<?=($log->activo == 0 ? "Eli" : ($log->idAsignado == $login || $log->idAsignado == -1 ? ($log->idEstatus == '1' ? "AsignadoNT" : "AsignadoT") : '')) ?>">
					<td class="celdaDescripcion" id="<?=$log->id; ?>" style="font-size: 12px; padding: 10px;"><?php echo $log->log; ?></td>
					<td class="" style="width:145px;">
						<div class="acciones">
							<button title="guardar cambios" type="button" class="btn btn-default guardar" style="cursor: pointer; display: none;">
								<i class="glyphicon glyphicon-save"></i>
							</button>
							<button title="cancelar edicion" type="button" class="btn btn-default cancelar " style="cursor: pointer; display: none;">
								<span class="glyphicon glyphicon-ban-circle"></span>
							</button>
							<?if($log->idEstatus == '1' && $log->idRealizo == $login):?>
							<button title="editar comentario" type="button" class="btn btn-default editar" <?=$disabled?>>
								<span class=" glyphicon glyphicon-edit"></span>
							</button>
							<button title="borrar comentario" type="button" class="btn btn-default borrar" <?=$disabled?>>
								<span class=" glyphicon glyphicon-trash"></span>
							</button>
							<?else:?>
							<button type="button" class="btn btn-default" disabled>
								<span class=" glyphicon glyphicon-edit"></span>
							</button>
							<button type="button" class="btn btn-default" disabled>
								<span class=" glyphicon glyphicon-trash"></span>
							</button>
							<?endif;?>
							<?if($log->idEstatus == '1' && ($log->idAsignado == -1 || $log->idAsignado == $login)):?>
								<button  title="marcar como terminado" type="button" class="btn btn-default terminar " <?=$disabled?>>
									<span class="glyphicon glyphicon-check"></span>
								</button>
							<?else:?>
								<button type="button" class="btn btn-default" disabled>
									<span class="glyphicon glyphicon-check"></span>
								</button>
							<?endif;?>
						</div>
					</td>
					<td class="text-center"><?=$log->creado?>
					</td>
					<td class="text-center"><?=$log->fecha?>
					</td>
					<td class="text-center"><?=$log->modificado == '' ? '- - - - - - -' : $log->modificado ?>
					</td>
					<td class="text-center"><?=$log->modificado == '' ? '- - - - - - -' : $log->fechaM ?>
					</td>
					<td class="text-center">
						<?if ($log->idAsignado == '0'):?>
								 <?="- - - - - - - -";?>
						<?//elseif ($log->creado == $intranet->getAlias() && $log->idEstatus == 1):?>
						<?elseif ($log->idRealizo == $login && $log->idEstatus == '1'):?>
							<select name="tipot" class="form-control asigner" style="width: 85%;display:inline-block;" data-id="<?=$log->id?>" <?=$disabled?>>
								<? Html::Options($usuarios,  $log->idAsignado)?>
							</select>
						<?else:?>
							<select class="form-control" style="width: 85%;display:inline-block;" disabled>
								<? Html::Options($usuarios,  $log->idAsignado)?>
							</select>
						<?endif;?>
					</td>
					<td class="text-center"><?php echo $log->fechaT == '' ? '<span class="glyphicon glyphicon-warning-sign"></span>' : $log->fechaT ; ?>
					</td>
				</tr>
				<?endforeach;?>
				<?endif;?>
			</table>
		</div>
	</div>
</div>
<input type="hidden" id="numord" name="numord" value="<?=$log->idOrden?>">
<script>
function updateLog(idLog, log) {
	var datos = JSON.stringify({
		idLog: idLog,
        log: log,
        numord: $('#numord').val(),
    });
    var url = 'phrapi/log';
	var _request = $.post(url, {data: {json: datos}}, 'json');
	_request.done(function(response) {
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			loadLogs();
		}
	});
	_request.fail( function( jqXHR, textStatus ) {
		configAlert(entity, 'danger', textStatus);
	});
}
function asignarTarea(idLog, id) {
	var datos = JSON.stringify({
		idLog: idLog,
		id: id,
		asignar: '1',
        numord: $('#numord').val(),
    });
    var url = 'phrapi/log';
	var _request = $.post(url, {data: {json: datos}}, 'json');
	_request.done(function(response) {
		response = JSON.parse(response);
		var clase = '';
		/*if (response.code == 200) {
			//loadLogs();
		}*/
	});
	_request.fail( function( jqXHR, textStatus ) {
		configAlert(entity, 'danger', textStatus);
	});
}

function deleteConfirm(idLog) {
	var modal = $("#confirm-modal").modal();
	modal.find("#confirm-modal-title").html("Confirmaci&oacute;n eliminar comentario/mensaje");
	modal.find("#confirm-modal-text").html("¿Estás seguro de querer borrar ésta tarea/mensaje/comentario?");
	modal.find("#btn-aceptar").on("click", function(e) {
		deleteLog(idLog, modal);
	});
}
function deleteLog(idLog, modal) {
	var datos = JSON.stringify({
		idLog: idLog,
		borrar: '1',
        numord: $('#numord').val(),
    });
    var url = 'phrapi/log';
	var _request = $.post(url, {data: {json: datos}}, 'json');
	_request.done(function(response) {
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			loadLogs();
			modal.modal("hide");
		}
	});
	_request.fail( function( jqXHR, textStatus ) {
		configAlert(entity, 'danger', textStatus);
	});
}
function terminarConfirm(idLog) {
	var modal = $("#confirm-modal").modal();
	modal.find("#confirm-modal-title").html("Confirmaci&oacute;n terminacion de tarea");
	modal.find("#confirm-modal-text").html("¿Estás seguro de querer marcar ésta tarea como terminada?");
	modal.find("#btn-aceptar").on("click", function(e) {
		terminarLog(idLog, modal);
	});
}
function terminarLog(idLog, modal) {
	var datos = JSON.stringify({
		idLog: idLog,
		terminar: '1',
        numord: $('#numord').val(),
    });
    var url = 'phrapi/log';
	var _request = $.post(url, {data: {json: datos}}, 'json');
	_request.done(function(response) {
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			loadLogs();
			modal.modal("hide");
		}
	});
	_request.fail( function( jqXHR, textStatus ) {
		configAlert(entity, 'danger', textStatus);
	});
}
function loadLogs() {
	var numero = $('#numord').val();
	$("#logs-" + numero).load('ajaxsearch/ordenes?accion=logs&numero=' + numero);
}
function agregarLog() {
	var datos = JSON.stringify({
		numord: $('#numord').val(),
        log: $('#comentario').val(),
        asignado: ($("#asignar").is(":checked") && $('#asignado').val() != 0 ? $('#asignado').val() : '0'),
    });
    var url = 'phrapi/log';
	var _request = $.post(url, {data: {json: datos}}, 'json');
	_request.done(function(response) {
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			loadLogs();
			$('#comentario').val('');
		}
	});
	_request.fail( function( jqXHR, textStatus ) {
		configAlert(entity, 'danger', textStatus);
	});
}
$( function() {
	$(".asigner").on('change', function(e) {
		var id = $(this).data('id');
		//alert(id + " - " + $(this).val());
		asignarTarea(id, $(this).val());
	});
	$("#tablaLog").find("tr.filaLog, tr.filaLogEli").each( function( i ) {
		var $this = $(this);
		var divAcciones = $this.find("div.acciones");
		var spanGuardar = divAcciones.find("button.guardar");
		var spanEditar = divAcciones.find("button.editar");
		var spanBorrar = divAcciones.find("button.borrar");
		var spanCancelar = divAcciones.find("button.cancelar");
		var spanTerminar = divAcciones.find("button.terminar");
		var original;
		//
		/*$this.hover(
			function() {
				divAcciones.show();
			},
			function() {
				divAcciones.hide();
			}
		);*/
		spanEditar.click( function() {
			//if( confirm( "Esta seguro de quere editar este log? Es posible que se pierda la descripcion original" ) ) {
				var elTd = $this.find("td.celdaDescripcion");
				var laDescripcion = elTd.text();
				original = laDescripcion;
				var elTextArea = $("<textarea id='" + elTd.prop('id') + "' style='width: 100%; height: 80px; border: 0px;'>" + laDescripcion + "</textarea>");
				elTd.html(elTextArea);
				spanEditar.hide();
				spanBorrar.hide();
				spanGuardar.show();
				spanCancelar.show();
				elTextArea.focus();
			//}
		});
		var fc = function() {
			//if( confirm( "Esta seguro de querer eliminar este log permanentemente?" ) ){
				//var laLinea = $this.next("tr");
				//$this.remove();
				//laLinea.remove();
				var elTd = $this.find("td.celdaDescripcion");
				//deleteLog(elTd.prop('id'));
				deleteConfirm(elTd.prop('id'));
				//closeConfirm();
		};
		//spanBorrar.click( function() { showConfirm("&iquest;Esta seguro de querer eliminar este comentario permanentemente?", fc); } );
		spanBorrar.click( function() { fc(); } );
		var fcg = function() {
			//if( confirm( "Esta seguro de querer guardar los cambios al log?" ) ){
				var elTd = $this.find("td.celdaDescripcion");
				var elTextArea = elTd.find("textarea");
				var laDescripcion = elTextArea.val();
				updateLog (elTextArea.prop('id'), laDescripcion);
				//elTd.html(laDescripcion);
				spanEditar.show();
				spanBorrar.show();
				spanGuardar.hide();
				spanCancelar.hide();
				//closeConfirm();
			//}
		};
		spanGuardar.click( function() { fcg(); } );
		spanCancelar.click( function() {
			var elTd = $this.find("td.celdaDescripcion");
			elTd.html(original);
			spanEditar.show();
			spanBorrar.show();
			spanGuardar.hide();
			spanCancelar.hide();
		});
		var fct = function() {
				var elTd = $this.find("td.celdaDescripcion");
				//terminarLog(elTd.prop('id'));
				terminarConfirm(elTd.prop('id'));
				//closeConfirm();
		};
		//spanTerminar.click( function() { showConfirm("&iquest;Esta seguro de querer marcar la tarea como TERMINADA?", fct);} );
		spanTerminar.click( function() { fct();} );
	});
});
</script>