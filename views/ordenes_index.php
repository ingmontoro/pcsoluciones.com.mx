<? require_once 'phincludes/util.php' ?>
<? $estatus = $intranet->ordenesFiltro("estatus") ?>
<? $recibio = $intranet->ordenesFiltro("recibio") ?>
<? $entrego = $intranet->ordenesFiltro("entrego") ?>
<? $valoresFiltros = $intranet->valores_filtros ?>
<? $paginador = $intranet->paginador ?>
<? $index = $result['navigation']->items->starting ?>
<style type="text/css">
th {
	font-weight:normal;
}
.label {
	font-weight: normal;
	font-family: monospace;
	display:inline-block;
	min-width:105px;
}
/*
.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
	vertical-align: middle;
}
*/
</style>
<div style="text-align: center; clear: both;">
	<h2 class="titulo">&Oacute;rdenes de servicio</h2> 
</div>
<div id="divTablaOrdenes">
	<div class="table-responsive">
		<table id="tabla-ordenes" class="table table-striped table-hover table-clickable table-middle">
			<tr style="background-color: ">
				<th class="item-num fit">#</th>
				<th class="text-center">Orden#</th>
				<th>Nombre del Cliente</th>
				<th>Descripci&oacute;n</th>
				<th class="text-center">Mensajes</th>
				<th>
					<select class="listadoFiltro" name="o.estatus" onchange="reload(1,0);" style="text-transform: capitalize;">
						<option value='0' style=''>Estatus</option>
						<? Html::Options($estatus, $valoresFiltros->filtroEs) ?>
					</select>
				</th>
				<th>
					<select class="listadoFiltro" name="o.idRecibio" onchange="reload(1,0);" style="text-transform: capitalize;">
						<option value='0' style=''>Recibi&oacute;</option>
						<? Html::Options($recibio, $valoresFiltros->filtroRe) ?>
					</select>
				</th>
				<th>
					Recepci&oacute;n
				</th>
				<th>
					<select class="listadoFiltro" name="o.idEntrego" onchange="reload(1,0);" style="text-transform: capitalize;">
						<option value='0' style=''>Entreg&oacute;</option>
						<? Html::Options($entrego, $valoresFiltros->filtroEn) ?>
					</select>
				</th>
				<th>
					Entregado
				</th>
				<!-- th class="ord">
					Log
				</th -->
			</tr>
			<tr class="">
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<?if(count($result['ordenes']) < 1):?>
			<tr class="">
				<td colspan="10">
					No se encontraron resultados...
				</td>
			</tr>
			<?else :?>
			<?foreach ($result['ordenes'] as $orden):?>
			<tr class="fila-datos">
				<td class="item-num">
					<?=($index + (($paginador->pagina - 1) * $paginador->tamano))?>
					<? $index ++ ?>
				</td>
				<td class="celda-id text-center">
					<!-- a href="index.php?accion=edicion&amp;id=<?=$orden->numero?>" class="btn btn-default"><?=fixFolSize($orden->numero)?></a -->
					<a href="ordenes/<?=$orden->numero?>" class="btn btn-underline btn-default no-shadow"><?=fixFolSize($orden->numero)?></a>
					<a href="ordenes/<?=$orden->numero?>" title="abrir en ventana" class="ventana">
						<br>
						<span style="font-size:small;">ventana <i class="glyphicon glyphicon-new-window"></i></span>
					</a>
				</td>
				<td class="text-capitalize">
					<?=$orden->nombre_fiscal == '' ? $orden->nombreC : $orden->nombre_fiscal . "<br />(" . $orden->nombreC . ")"; ?>
				</td>
				<td>
					<div data-toggle="popover" data-placement="bottom" data-content="<?=strlen($orden->descripcion) > 50 ? formatoTextoConPuntos($orden->descripcion) : ''; ?>" data-original-title="">
						<?=htmlDescripcionLarga($orden->descripcion)?>
					</div>
				</td>
				<td class="text-center" style="vertical-align: middle">
					<? if($orden->notas > 0):?>
					<button data-id="<?=$orden->numero?>" data-placement="bottom" type="button" class="btn btn-default btn-sm btn-historico" data-toggle="popover" data-content="" data-original-title="Comentarios orden de servicio #<?=$orden->numero?>">
			          ver notas &nbsp;<span class="glyphicon glyphicon-pencil"></span>
			        </button>
			        <? endif; ?>
				</td>
				<td class="text-uppercase">
					<?=etiquetaEstatus($orden->estatusOrden, $orden->estatusNota, $orden->estatus);?>
				</td>
				<td>
					<?=$orden->nombreR; ?>
				</td>
				<td>
					<?=$orden->fecha; ?>
				</td>
				<td>
					<?=$orden->nombreE; ?>
				</td>
				<td>
					<?=$orden->fechaT; ?>
				</td>
				<!-- td class="ord" style="">
					<?=$orden->nombreA; ?>
				</td -->
			</tr>
			<?endforeach;?>
			<?if($result['showpages'] == true):?>
			<tr><td colspan="10" class="text-right"><?=$result['navigation']->items->starting?> a <?=$result['navigation']->items->ending?> de <?=$result['navigation']->total?></td></tr>
			<?endif;?>
			<?endif;?>
		</table>
	</div>
	
	<input id="numorden" type="hidden" />
	<?include_once 'phincludes/paginador.php';?>
</div>
<script>
	function cargarOrden(numOrden) {
		$("#numorden").val(numOrden);
		buscarOrden('');
	}
	$( function() {
		/*$("#tabla-ordenes").find("tr.fila-datos").each( function( i ) {
			var $this = $(this);
			var elTd = $this.find("td.celda-id");
			var idOrden = elTd.text().trim();
			//$(this).click( function() {
			elTd.click( function() {
				//cargarOrden(idOrden);
				alert(idOrden);
			});
		})*/;
		
		$('div[data-toggle=popover]').popover({
			trigger: "hover" 
		});
		
		//$('button[data-toggle=popover]').popover();
		/*$('button[data-toggle=popover]').each(function(e) {
		    var $tr = $(this);
		    id = $tr.data('id');
	        $.ajax({
	        	url: 'historial-orden.php',
	            data: {numord: id},
	            dataType: 'html',
	            success: function(html) {
	                $tr.popover({
		                title: 'Relance',
	                    content: html,
	                    placement: 'bottom',
	                    html: true,
	                    trigger: "click"
	                });
	            }
	        });
	    });*/

	    $('button[data-toggle=popover]').on('mouseover', function(e) {
		    var $tr = $(this);
		    $tr.off('hover');
	        if(!$tr.prop("created")) {
	        	id = $tr.data('id');
			    $.get('ajaxsearch/ordenes?accion=logs&alla=0&numero=' + id, function(d) {
			    	$tr.popover({
		                title: 'Relance',
		                content: d,
		                placement: 'bottom',
		                html: true,
		                trigger: "click"
		            });
		            $tr.prop("created", true);
			    	//$tr.popover('show');
			    });
	        } else {
	        	//$tr.popover('toggle');
	        }
	    });
	    
		$("#numConfig").val(<?=$paginador->tamano?>);
		hideLoading();
		createWindows();
	});	
</script>