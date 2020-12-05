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
	<h2>Ventas por per&iacute;odo<br /></h2>
	<div>
		Entre el d&iacute;a <input value="<?=$datos->txtDate1?>"  id="ventasDatePicker" style="text-align: center; cursor: pointer;" /> y el d&iacute;a <input value="<?=$datos->txtDate2?>" id="ventasDatePicker2" style="text-align: center; cursor: pointer;" /><br /><br />
		<input type="hidden" id="date1" value="" />
		<input type="hidden" id="date2" value="" />
	</div>
</div>
<div id="divTablaVentas">
	<div class="text-center">
		<h3>
		<?=$datos->myTime['tm_mday'] . " de " . $datos->meses[$datos->myTime['tm_mon']] . " de " . (1900 + $datos->myTime['tm_year']); ?>&nbsp;&nbsp;-&nbsp;&nbsp;
		<?=$datos->myTime2['tm_mday'] . " de " . $datos->meses[$datos->myTime2['tm_mon']] . " de " . (1900 + $datos->myTime2['tm_year']); ?>
		</h3>
	</div>
	<table id="tablaVentas" style="width:100%;" class="table table-striped table-hover table-clickable">
		<tr style="background-color:;">
			<th class="">
				C&oacute;digo
			</th>
			<th class="">
				Descripci&oacute;n
			</th>
			<th class="text-center">
				Ventas(tickets)
			</th>
			<th class="text-center">
				Unidades vendidas
			</th>
			<th class="text-center">
				Stock actual
			</th>
			<th class="text-center">
				Activo
			</th>
		</tr>
		<?if (isset($datos->ordenes) && !$datos->vacio) {?>
		<tr class="">
			<td colspan="6">
			</td>
		</tr>
		<?foreach ($datos->ordenes as $venta) { ?>
		<tr>
			<td class="celdaId">
				<a href="articulos/<?=$venta->codigo?>" class="btn btn-default no-shadow"><?=fixFolSize($venta->codigo); ?></a>
				<a href="articulos/<?=$venta->codigo?>" class="ventana "><i class="icono-ventana glyphicon glyphicon-new-window"></i></a>
			</td>
			<td>
				<div data-toggle="popover" data-placement="bottom" data-content="<?=strlen($venta->descripcion) > 0 ? utf8_encode($venta->descripcion) : ''; ?>" data-original-title="">
					<?=$venta->corta; ?>
				</div>
			</td>
			<td class="text-center">
				<?=$venta->ventas; ?>
			</td>
			<td class="text-center">
				<?=$venta->cantidad; ?>
			</td>
			<td class="text-center">
				<?=$venta->stock; ?>
			</td>
			<td class="text-center">
				<input type="checkbox" class="check" disabled="disabled" <?=$venta->activo == 1 ? "checked='checked'" : ""; ?> />
			</td>
		</tr>
		<?} ?>
		<?} else { ?>
			<tr class="">
				<td class="" style="" colspan="6">
					No se encontraron resultados...
				</td>
			</tr>
		<?} ?>
	</table>
</div>
<script>
function reloadVtasPeriod() {
	var data;
	window.location = "periodo?txtDate1=" + $("#date1").val() + "&txtDate2=" + $("#date2").val();
}
$(function() {
	$("#ventasDatePicker").datepicker({
			onSelect: function(dateTxt, inst) { $("#date1").val(dateTxt); $("#date2").val($("#ventasDatePicker2").val()); reloadVtasPeriod();},
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
	$("#ventasDatePicker2").datepicker({
			onSelect: function(dateTxt, inst) { $("#date2").val(dateTxt); $("#date1").val($("#ventasDatePicker").val()); reloadVtasPeriod();},
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
	<?if(!isset($datos->txtDate1)):?>
	$("#ventasDatePicker").datepicker('setDate', new Date());
	<?endif;?>
	<?if(!isset($datos->txtDate2)):?>
	$("#ventasDatePicker2").datepicker('setDate', new Date());
	<?endif;?>
	$("#date1").val($("#ventasDatePicker").val());
	$("#date2").val($("#ventasDatePicker2").val());
	hideLoading();
});
</script>