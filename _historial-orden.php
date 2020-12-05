<? include_once 'phrapi/index.php' ?>
<? require_once 'phincludes/util.php' ?>
<? $intra = $factory->Pcsoluciones?>
<? $datos = $intra->loadHistorialOrden(isset($datos) ? $datos->numero : 0) ?>
<? $logs = $datos['logs'] ?>
<? $login = $factory->Access->logged() ?>
<? $usuarios = $datos['usuarios'] ?>
<style type="text/css">
th {
	font-weight:normal;
}
</style>
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
				<td class="text-center">
					<div class="acciones" style="display:;">
						<button type="button" class="btn btn-default guardar" style="cursor: pointer; display: none;">
							<span class=" glyphicon glyphicon-floppy-save"></span>
						</button>
						<button type="button" class="btn btn-default editar">
							<span class=" glyphicon glyphicon-edit"></span>
						</button>
						<button type="button" class="btn btn-default borrar">
							<span class=" glyphicon glyphicon-remove"></span>
						</button>
						<button type="button" class="btn btn-default cancelar " style="cursor: pointer; display: none;">
							<span class="glyphicon glyphicon-ban-circle"></span>
						</button>
						<?php if($log->idEstatus == '1' && ($log->idAsignado == -1 || $log->idAsignado == $login)) { ?>
							<button type="button" class="btn btn-default terminar ">
								<span class="glyphicon glyphicon-check"></span>
							</button>
						<?php } ?>
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
					<?php
						if ($log->idAsignado == '0') {
							 echo "- - - - - - - -";
						}
						elseif ($log->creado == $intra->getAlias() && $log->idEstatus == 1) {
							$asignadoOptions = "";
							foreach($usuarios as $__user) {
								$asignadoOptions .= "<option value='" . $__user->id . "'" . ( $__user->id == $log->idAsignado ? "selected='selected'" : "" ) . ">" . $__user->alias . "</option>";
						    }
						    echo "<select onchange='saveAssign(" . $log->id . ", this);'>" . $asignadoOptions . "</select>";
						} else {
							echo $log->asignado;
						}
					?>
				</td>
				<td class="text-center"><?php echo $log->fechaT == '' ? '<span class="glyphicon glyphicon-warning-sign"></span>' : $log->fechaT ; ?>
				</td>
			</tr>
			<?endforeach;?>
			<?endif;?>
		</table>
	</div>
</div>
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

function deleteLog(idLog, realizo) {
	var result = false;
	$.ajax({
		  type: "POST",
		  async: false,
		  url: './phpscripts/log-orden.php?eliminar=true',
		  data: { json: JSON.stringify({
				realizo:		realizo,
	            idLog:		idLog
	        })},
		  success: function(data) {
	        	var data = data.split('||');
				if (data[0] == 1 || data[0] == '1') {
					loadLogs();
				} else {
					showMessage(data[1], 'Error');
				}
	       }
		});
}
function loadLogs() {
	$.ajax({
    	url: 'historial-orden.php',
        data: {numord: $('#numord').val()},
        dataType: 'html',
        success: function(html) {
            $("#historialOrden").html(html);
        }
    });
}
function agregarLog() {
	var datos = JSON.stringify({
		numord: 		$('#numord').val(),
        log: 		$('#comentario').val(),
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
$( function() {
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
				deleteLog (elTd.prop('id'), $("#realizo").val());
				closeConfirm();
		};
		spanBorrar.click( function() { showConfirm("&iquest;Esta seguro de querer eliminar este comentario permanentemente?", fc); } );
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
				terminarTarea (elTd.prop('id'));
				closeConfirm();
		};
		spanTerminar.click( function() { showConfirm("&iquest;Esta seguro de querer marcar la tarea como TERMINADA?", fct);} );
	});
});
</script>