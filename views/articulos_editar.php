<? require_once 'phincludes/util.php' ?>
<? $datos = $result['datos']?>
<? $categorias = $intranet->categorias() ?>
<? $edicion = $result['id'] != '' ? true : false ?>
<? $datos = $edicion ? $result['datos'] : null ?>
<? $ie = getValueFrom($_GET, 'ext', 0, FILTER_SANITIZE_PHRAPI_MYSQL);?>
<? $ie = $ie == 0 ? '' : '?ext=1'?>
<style>
.check {
	margin:0;height:32px;width:32px;margin-left:30px !important;
}
</style>
	<div class="text-center">
		<h2>
		<?if($edicion):?>
		Edición de art&iacute;culo <?=$datos->codigo?>
		<?else:?>
		Alta de art&iacute;culo en inventario.
		<?endif;?>
		</h2>
	</div>
	<div id="div_orden">
		<h3>Datos del art&iacute;culo/item</h3>
		<form id="datos-articulo" role="form"  data-toggle="validator">
			<div class="row">
				<div class="form-group col-md-4">
	    			<label for="numerot">Código</label>
					<input id="codigo" name="codigo" maxlength="6" class="form-control"
						onkeypress="return checkAlphaNumeric();" value="<?=$edicion ? $datos->codigo : ''?>"/>
					<div id="the-alert">
						<div id="mi-alert" class="alert alert-warning " role="alert">
							<div id="mensaje">Para comenzar, escriba un c&oacute;digo para el art&iacute;culo nuevo... Pulse TAB para continuar.</div>
						</div>
					</div>
					<input id="clave" type="hidden" value="<?=$edicion ? $datos->clave : ''?>" />
				</div>
				<div class="form-group col-md-4 ocultable">
	    			<label for="numerot">Categor&iacute;a</label>
	      			<select id="categoria" name="categoria" class="form-control">
						<? Html::Options($categorias, $edicion ? $datos->categoria : '') ?>
					</select>
	    		</div>
				<div class="form-group col-md-4 ocultable">
					<label for="activo">&iquest;Está activo?</label><br />
					<input id="activo" name="activo" type="checkbox" class="check" <?=$edicion && $datos->activo !== '1' ? '' : 'checked=\'checked\''; ?> />
				</div>
			</div>
			<div class="row ocultable">
				<div class="form-group col-md-4">
					<label for="corta" class="">Descripci&oacute;n corta</label>
					<textarea id="corta" placeholder="Descripcion corta..." class="form-control" required
						type="text" data-minlength="5" data-error="Escriba una descripción..."
						style="" rows="" cols="" onkeyup="markText();"><?=$edicion ? $datos->corta : ''?></textarea>
					<div class="help-block with-errors"></div>
					<br>
					<label for="corta" class="">Texto en TICKET</label>
					<div id="enTicket" style="text-align: center; text-transform: uppercase; padding: 3px; border: 1px solid gainsboro; font-weight: bold;">* * * * * * * * * *</div>
				</div>
				<div class="form-group col-md-4">
					<label for="larga">Descripci&oacute;n larga</label>
					<textarea id="larga" class="form-control" 
						type="text" data-minlength="20" data-error="Una descripción larga necesita al menos 20 caracteres..."
						placeholder="Descripcion larga(completa)..."
						style="height: 130px;" rows="" cols=""><?=$edicion ? $datos->descripcion : ''?></textarea>
					<div class="help-block with-errors"></div>
				</div>
				<div class="form-group col-md-4"></div>
			</div>
			<div class="row ocultable">
				<div class="form-group col-md-4">
					<label for="precio">Precio</label>
					$<input id="precio" name="precio" class="form-control" onkeypress="return checkNumeric();" onblur="currencyFormatUp(this);"
						required type="text" data-error="Cantidad con hasta dos decimales"
						value="<?=$edicion ? number_format($datos->precio, 2, '.', ',') : ''?>" />
				</div>
				<div class="form-group col-md-4">
					<label for="cantidad">Stock</label>
					<input required type="text" data-error="Solo numero enteros" id="cantidad" name="cantidad" class="form-control" onkeypress="return checkNumeric();" value="<?=$edicion ? $datos->cantidad : ''?>" />
					<div class="help-block with-errors"></div>
				</div>
				<div class="form-group col-md-4"></div>
			</div>
			<div class="row ocultable">
				<div class="col-md-8">
					<div id="articulo-alert" class="alert alert-dismissible col-md-12" role="alert">
					  <button type="button" class="close" id="articulo-alert-button" aria-label="Close">
					    <span aria-hidden="true">&times;</span>
					  </button>
					  <div id="mensaje"></div>
					</div>
				</div>
				<div class="form-group col-md-4 text-center"></div>
			</div>
			<div class="row ocultable">
				<div class="col-md-8 text-right">
					<button type="submit" id="_bot_ga" class="btn btn-primary" onclick="">Guardar cambios</button>
					<a type="button" id="_bot_ga" class="btn btn-success" href="articulos/add<?=$ie?>">Nuevo articulo</a>
					<button type="button" id="_bot_la" class="btn btn-default" onclick="limpiarForm('datos-articulo')">Reestablecer formulario</button>
				</div>
				<div class="form-group col-md-4 text-center"></div>
			</div>
		</form>
	</div>

<script>
function configAlert(idAlert, clase, mensaje) {
	idAlert =  '#' + idAlert + '-alert';
	$(idAlert).find("#mensaje").html(mensaje);
	$(idAlert).removeClass("alert-warning");
	$(idAlert).removeClass("alert-success");
	$(idAlert).removeClass("alert-danger");
	$(idAlert).addClass('alert-' + clase);
	$(idAlert).show();
}
function markText() {
	var ele = $("#corta");
	if($(ele).val() == "" || $(ele).val() == null || $(ele).val().length == 0) {
		$("#enTicket").html("* * * * * * * * * *");
	} else {
		if ($(ele).val().length > 20) {
			$("#enTicket").html($(ele).val().substring(0, 21));
		} else {
			$("#enTicket").html($(ele).val());
		}
	}
}
function continuarCaptura() {
	$(".ocultable").each(function(index) {
		$(this).show();
	});
	$("#the-alert").hide();
	$("#codigo").prop("disabled", true);
}
function detenerCaptura() {
	$(".ocultable").each(function(index) {
		$(this).hide();
	});
	$("#the-alert").show();
}
function checkArticulo() {
	var codigo = $("#codigo").val();
	if(codigo != null && codigo != '') {
		var entity = 'articulo';
	    var url = 'phrapi/validar/' + entity;
		$.ajax({
		  type: "POST",
		  async: false,
		  url: url,
		  data: { codigo: codigo },
		  success: function(response) {
			  response = JSON.parse(response);
			  var clase = '';
			  var message = "Codigo valido, puede continuar.";
				if (response.code == 200 && response.existe < 1) {
					clase = "success";
					continuarCaptura();
				} else {
					clase = "warning";
					message = "El codigo ya existe, elija otro.";
					detenerCaptura();
				}
				configAlert('mi', clase, message);
				
				//mostrarOcultarBotonesOrden();
	       },
	       fail: function(response) {
	    	   configAlert('mi', 'danger', response.textStatus);
	       }
		});
	}
}
$('#datos-articulo').validator().on('submit', function (e) {
	  if (e.isDefaultPrevented()) {
		  configAlert('articulo', 'warning', 'Tienes un error en el formulario, favor de verificarlo.');
	  } else {
		  e.preventDefault();
		  guardarArticulo();
	  }
	});
function guardarArticulo() {
	var datos = JSON.stringify({
		clave:	 			$('#clave').val(),
		codigo: 			$('#codigo').val(),
        corta: 				utf8_encode($('#corta').val()),
        larga:				$('#larga').val(),
        cantidad:			$('#cantidad').val(),
        precio:				$('#precio').val().replace(',', ''),
        categoria:			$('#categoria').val(),
        activo:				$('#activo').prop("checked") == '1' ? '1' : '0'
    });
    var entity = 'articulo';
    var url = 'phrapi/save/' + entity;
	var _request = $.post(url, {data: {json: datos}}, 'json');
	_request.done(function(response) {
		validarSesion(entity, response);
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			$('#clave').val(response.id);
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
$(document).ready(function() {
	hideLoading();
	$("#articulo-alert").hide();
	var clave = $("#clave").val();
	var codigo = $("#codigo").val();
	if (clave != '' && codigo != '') {
		//Se busco y encontro un  articulo
		$("#codigo").prop("disabled", true);
		$("#the-alert").hide();
		continuarCaptura();
		markText();
	} else {
		//es articulo nuevo
		$("#codigo").blur(function() { checkArticulo(); });
		detenerCaptura();
	}
});
</script>