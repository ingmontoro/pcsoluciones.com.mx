<? require_once 'phincludes/util.php' ?>
<? $valoresFiltros = $intranet->valores_filtros ?>
<? $paginador = $intranet->paginador ?>
<? $index = $result['navigation']->items->starting ?>
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
<div style="text-align: center; clear: both;">
	<h2 class="titulo">Listado de art&iacute;culos en el inventario<?//=$result['order']?>
		<input type="hidden" id="esGeneral" value="true">
	</h2> 
</div>
<div id="divTablaArticulos"></div>
	<div class="table-responsive">
		<table id="tabla-articulos" class="table table-striped table-hover table-clickable">
			<tr style="background-color:;">
				<th class="item-num">#</th>
				<th class="text-center">
					Código <select class="listadoFiltro" name="a.codigo" onchange="reload(1,0);" style="text-transform: capitalize;">
						<option value=""></option>
						<? Html::Options(array("ASC"=>'A-Z', "DESC"=>'Z-A'), $valoresFiltros->codigo) ?>
					</select>
				</th>
				<th>Descripción (corta)  <select class="listadoFiltro" name="a.corta" onchange="reload(1,0);" style="text-transform: capitalize;">
						<option value=""></option>
						<? Html::Options(array("ASC"=>'A-Z', "DESC"=>'Z-A'), $valoresFiltros->corta) ?>
					</select>
				</th>
				<th class="">Categoría</th>
				<th class="text-center">Cantidad</th>
				<th class="">
					Precio <select class="listadoFiltro" name="a.precio" onchange="reload(1,0);" style="text-transform: capitalize;">
						<option value=""></option>
						<? Html::Options(array("ASC"=>'Menor', "DESC"=>'Mayor'), $valoresFiltros->precio) ?>
					</select>
				</th>
				<th class="text-center">
					Activo <select class="listadoFiltro" name="a.activo" onchange="reload(1,0);" style="text-transform: capitalize;">
						<option value=""></option>
						<? Html::Options(array("ASC"=>'Ultimo', "DESC"=>'Primero'), $valoresFiltros->activo) ?>
					</select>
				</th>
			</tr>
			<tr class="">
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
					<td colspan="7">
						No se encontraron resultados...
					</td>
				</tr>
			<?else :?>
				<?foreach ($result['ordenes'] as $orden):?>
				<tr class="">
					<td class="item-num">
						<?=($index + (($paginador->pagina - 1) * $paginador->tamano))?>
						<? $index ++ ?>
					</td>
					<td class="text-center celdaId" style="cursor: pointer; font-weight: bold; cursor: pointer;">
						<a href="articulos/<?=$orden->codigo?>" class="btn btn-default no-shadow"><?=fixFolSize($orden->codigo)?></a>
						<a href="articulos/<?=$orden->codigo?>" title="abrir en ventana" class="ventana "><i class="icono-ventana glyphicon glyphicon-new-window"></i></a>
					</td>
					<td class="ord ordCap ordLeft" style="">
						<?=$orden->corta?>
					</td>
					<td class="" style="">
						<?=$orden->categoria == '' ? "-&nbsp;&nbsp;-&nbsp;&nbsp;-&nbsp;&nbsp;-&nbsp;&nbsp;-&nbsp;&nbsp;-&nbsp;&nbsp;-&nbsp;&nbsp;-&nbsp;&nbsp;-&nbsp;&nbsp;-&nbsp;&nbsp;-" : $orden->categoria; ?>
					</td>
					<td class="text-center stock" data-id="<?=$orden->clave?>" title="Click para editar stock..." style="cursor: text;">
						<span class="stockSpa"><?=$orden->cantidad ?></span>
						<input class="stockInp" type="text" style="display: none;" size="2" maxlength="4" value="<?=$orden->cantidad?>" />
					</td>
					<td class="ord ordRight precio" data-id="<?=$orden->clave; ?>" title="Click para editar precio..." style="cursor: text;">
						<span class="_simbolo">$</span>
						<span class="precioSpa">
							<?=number_format($orden->precio, 2, '.', ','); ?>
						</span>
						<input class="precioInp" type="text" style="display: none;" size="2" maxlength="" onkeypress="return checkNumeric();" value="<?=number_format($orden->precio, 2, '.', ',')?>" />
					</td>
					<td class="text-center" style="" title="Clic para cambiar estatus...">
						<input type="checkbox" class="check" data-id="<?=$orden->clave?>" value="<?=$orden->activo?>" <?=$orden->activo == 1 ? "checked" : "" ?> />
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
	function activarArticulo(check_) {
		var clave = check_.data("id");
		var activo = check_.is(":checked") ? 1 : 0;
		$.ajax({
		  type: "POST",
		  async: false,
		  url: 'phrapi/activar/articulo',
		  data: { clave: clave },
		  success: function(msg) {
			  //alert(msg);
			  if(msg <= 0) {
					if(check_.is(":checked")) {
						check_.prop("checked", false);
					} else {
						check_.prop("checked", true);
					}
				}
			}
		});
	}
	function cambiarPrecio(id, precioSpa, precioInp) {
		var precio = currencyFormat(parseFloat(precioInp.val()));
		$.ajax({
		  type: "POST",
		  async: false,
		  url: 'phrapi/precio/articulo',
		  data: { clave: id, precio: precio},
		  success: function(msg) {
			  if(msg == 200) {
				  precioSpa.html(precio);
				}
			}
		});
		/*precioSpa.show();
		precioInp.hide();*/
	}
	function cambiarStock(id, stockSpa, stockInp) {
		var stock = parseInt(stockInp.val());
		$.ajax({
		  type: "POST",
		  async: false,
		  url: 'phrapi/stock/articulo',
		  data: { clave: id, stock: stock},
		  success: function(msg) {
			  if(msg == 200) {
				  stockSpa.html(stock);
				}
			}
		});
		/*stockSpa.show();
		stockInp.hide();*/
	}
	$( function() {
		$('div[data-toggle=popover]').popover({
			trigger: "hover" 
		});
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
		$(".precio").on("click", function(e) {
			var precioSpa = $(this).find(".precioSpa");
			var precioInp = $(this).find(".precioInp");
			var id = $(this).data("id");
			precioSpa.hide();
			precioInp.show();
			precioInp.select();
			precioInp.on("focusout", function() {
				//alert(parseFloat(precioSpa.html()).toFixed(2) + ' - - ' + precioInp.val()); 
				if(parseFloat(precioSpa.html()).toFixed(2) != precioInp.val()) {
					cambiarPrecio(id, precioSpa, precioInp);
				}
				precioSpa.show();
				precioInp.hide();
			});
		});
		$(".stock").on("click", function(e) {
			var stockSpa = $(this).find(".stockSpa");
			var stockInp = $(this).find(".stockInp");
			var id = $(this).data("id");
			stockSpa.hide();
			stockInp.show();
			stockInp.select();
			stockInp.on("focusout", function() {
				if(parseInt(stockSpa.html()) != stockInp.val()) {
					cambiarStock(id, stockSpa, stockInp);
				}
				stockSpa.show();
				stockInp.hide(); 
			});
		});
		$(".check").on("click", function(e) {
			var id = $(this).data("id");
			activarArticulo($(this));
		});
		$("#numConfig").val(<?=$paginador->tamano?>);
		hideLoading();
		createWindows();
	});	
</script>