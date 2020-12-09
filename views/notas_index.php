<? require_once 'phincludes/util.php' ?>
<? $estatus = $intranet->notasFiltro("estatus") ?>
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
</style>
<div style="text-align: center; clear: both;">
	<h2 class="titulo">Notas de venta</h2> 
</div>
<div id="divTablaNotas">
	<div class="table-responsive">
		<table id="tabla-ordenes" class="table table-striped table-hover table-clickable table-middle">
			<tr style="">
				<th class="item-num fit">#</th>
				<th class="text-center fit">Nota N&uacute;m</th>
				<th>Orden N&uacute;m</th>
				<th class="">Nombre del Cliente</th>
				<th class="text-center">TOTAL</th>
				<th class="text-center">Creada</th>
				<!-- <th class="text-center">Estatus</th>  -->
				<th class="text-center">
					<select class="listadoFiltro" name="n.estatus" onchange="reload(1,0);" style="text-transform: capitalize;">
						<option value='0' style=''>Estatus</option>
						<? Html::Options($estatus, $valoresFiltros->filtroEs) ?>
					</select>
				</th>
				<th class="text-center">Fecha Cobro</th>
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
			</tr>
			<?if(count($result['ordenes']) < 1):?>
			<tr class="">
				<td colspan="10">
					No se encontraron resultados...
				</td>
			</tr>
			<?else :?>
			<?foreach ($result['ordenes'] as $orden):?>
			<tr class="filaOrd fila">
				<td class="item-num">
					<?=($index + (($paginador->pagina - 1) * $paginador->tamano))?>
					<? $index ++ ?>
				</td>
				<td class="celda-id text-center">
					<a href="notas/<?=$orden->numero?>" class="btn btn-underline btn-default no-shadow"><?=fixFolSize($orden->numero)?></a>
					<a href="notas/<?=$orden->numero?>" title="abrir en ventana" class="ventana">
						<br>
						<span style="font-size:small;">ventana <i class="glyphicon glyphicon-new-window"></i></span>
					</a>
				</td>
				<td class="celda-id">
					<?$n = trim(fixFolSize($orden->folio));?>
					<?=$n == '' ? '- - - - - -' : $n?>
				</td>
				<td class="text-capitalize">
					<?$n = trim(strtolower($orden->nombre_fiscal == '' ? $orden->nombreC : $orden->nombre_fiscal . "<br />(" . $orden->nombreC . ")"));?>
					<?=$n == '' ? '- - - - - -' : $n?>
				</td>
				<td  class="text-right">
					<span class="pull-left">$</span><?=number_format($orden->total, 2, '.', ',')?>
				</td>
				<td class="text-center">
					<?=$orden->fecha; ?>
				</td>
				<td class="text-uppercase text-center">
					<?=estatusNota($orden->idEstatus, $orden->estatus);?>
				</td>
				<td class="text-center">
					<?=$orden->fechaCobro != '' ? $orden->fechaCobro : '- - - - - -'; ?>
				</td>
			</tr>
			<?endforeach;?>
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
		$('button[data-toggle=popover]').each(function(e) {
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
	    });
		$("#numConfig").val(<?=$paginador->tamano?>);
		hideLoading();
		createWindows();
	});	
</script>