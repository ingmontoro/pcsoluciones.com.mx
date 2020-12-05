<? require_once 'phincludes/util.php' ?>
<? $valoresFiltros = $intranet->valores_filtros ?>
<? $paginador = $intranet->paginador ?>
<? $datos = $result['ordenes']?>
<? //$index = $result['navigation']->items->starting ?>
<style type="text/css">
img.ui-datepicker-trigger {
    vertical-align: top;
    padding-left: 5px;
}
th {
	font-weight:normal;
}
.check {
	margin:0;height:32px;width:32px;margin-left:30px;
}
</style>

<div id="divTablaVentas" style="width: 100%; clear: both;"></div>
<div style="text-align: center; clear: both;">
	<h2>Ventas del d&iacute;a<br /></h2>
	<input value="<?=$datos->txtDate1?>" id="ventasDatePicker" style="text-align: center; cursor: pointer;"/>
</div>
<div id="divTablaVentas">
	<div class="text-center">
		<h3>
		<?=$datos->dias[$datos->myTime['tm_wday']] . " " . $datos->myTime['tm_mday'] . " de " . $datos->meses[$datos->myTime['tm_mon']] . " de " . (1900 + $datos->myTime['tm_year']); ?>
		<?=$datos->actual == $datos->hoy ? '(Hoy)' : ($datos->actual == $datos->ayer ? '(Ayer)' : ($datos->actual == $datos->antier ? '(Antier)' : '')) . ""; ?>
		</h3>
	</div>
	<table id="tablaVentas" style="width:100%;" class="table table-striped table-hover table-clickable">
		<tr>
			<th class="">
				Nota N&uacute;m
			</th>
			<th class="">
				Orden N&uacute;m
			</th>
			<th class="">
				Nombre del Cliente
			</th>
			<th class="text-center">
				Detalle nota
			</th>
			<th class="text-center">
				Monto
			</th>
		</tr>
		<?if (isset($datos->ordenes) && !$datos->vacio) { $index = 0;?>
		<tr class="" <?=($index++ % 2 == 0) ? 'style="background-color: white;"' : ''; ?>>
			<td class="celdaId">
			</td>
			<td class="">
			</td>
			<td class="">
			</td>
			<td class="">
			</td>
			<td class="">
			</td>
		</tr>
		<?foreach ($datos->ordenes as $venta) { ?>
		<tr class="">
			<td class="">
				<a href="notas/<?=$venta->numero?>" class="btn btn-default no-shadow"><?=fixFolSize($venta->numero)?></a>
				<a href="notas/<?=$venta->numero?>" class="ventana "><i class="icono-ventana glyphicon glyphicon-new-window"></i></a>
			</td>
			<td class="">
				<?= isset ($venta->folio) && $venta->folio != '' ? fixFolSize($venta->folio) : '- - - - - -'; ?>
			</td>
			<td class="">
				<? 
					$n = $venta->nombre_fiscal == '' ? utf8_encode($venta->nombreCliente) : utf8_encode($venta->nombre_fiscal) . (trim(utf8_encode($venta->nombreCliente)) == '' ? '' : "<br />(" . utf8_encode($venta->nombreCliente) . ")");
					$n = trim($n) == '' ? '- - - - - -' : $n;
					echo $n; 
				?>
			</td>
			<td class="text-center">
				<div data-toggle="popover" data-placement="bottom" data-content="<?=utf8_encode($venta->data)?>" data-original-title="">
					<?="($venta->numArticulos articulos)"?>
				</div>
			</td>
			<td class="text-right">
				<span class="pull-left">$</span><?=number_format($venta->importe, 2, '.', ','); ?>
			</td>
			
		</tr>
		<? } ?>
		<tr class="" <?=($index++ % 2 == 0) ? 'style="background-color: white;"' : ''; ?>>
			<td colspan="3"></td>
			<td class="text-center" style="font-weight: bold;" >TOTAL DEL DIA</td>
			<td class="text-right">
				<span class="pull-left">$</span><?=number_format($venta->total, 2, '.', ','); ?>
			</td>
			
		</tr>
		<? } else { ?>
			<tr class="">
				<td class="" colspan="5">
					No se encontraron resultados...
				</td>
			</tr>
		<? } ?>
	</table>
</div>
<script>
function reloadVtasPeriod() {
	window.location = "dia?txtDate1=" + $("#ventasDatePicker").val();
}
$(function() {
	$("#ventasDatePicker").datepicker({
		onSelect: function(dateTxt, inst) { reloadVtasPeriod();/*var data = dateTxt.split('-'); reloadVentas(data[2] + '-' + data[1] + '-' + data[0]);*/ },
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
	$('#ventasDatePicker').bind("enterKey",function(e){
		reloadVtasPeriod();
	});
	$('#ventasDatePicker').keyup(function(e){
	    if(e.keyCode == 13) {
	        $(this).trigger("enterKey");
	    }
	});
	$('div[data-toggle=popover]').popover({
		trigger: "hover", html: true 
	});
	<?if(!isset($datos->txtDate1)):?>
	$("#ventasDatePicker").datepicker('setDate', new Date());
	<?endif;?>
	$("#date1").val($("#ventasDatePicker").val());
	hideLoading();
});
</script>