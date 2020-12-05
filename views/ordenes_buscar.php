<? require_once 'phincludes/util.php' ?>
<style>
label.normal {
	font-weight: normal;
}
</style>
<h2>Buscar una orden de servicio</h2>
<div id="div_busqueda">
	<div class="row">
		<div class="form-group col-md-4">
			<label  class="normal" for="nombre" >Buscar por número de orden.</label><br />
			<div class="input-group">
				<input <?=htmlValConf('', "Número de orden...", false)?> value="" class="form-control" id="numero" name="numero">
				<span class="input-group-btn">
					<button class="btn btn-primary" onclick="findOrder();"><i class="glyphicon glyphicon-search"></i></button>
				</span>
			</div>
		</div>
		<div class="form-group col-md-7"></div>
	</div>
	<div class="row">
		<div class="form-group col-md-4">
			<label  class="normal" for="nombre" >Buscar por nombre, razón social, nombre fiscal, etc...</label><br />
			<div class="input-group">
				<input <?=htmlValConf('', "Nombre, Nombre Fiscal, RFC ó email del cliente...", false)?> id="nombre" class="form-control" />
				<span class="input-group-btn">
					<button class="btn btn-primary" onclick="findOrders();"><i class="glyphicon glyphicon-search"></i></button>
				</span>
			</div>
		</div>
		<div class="form-group col-md-7"></div>
	</div>
</div>
<div id="_orden_result"></div>
<script>
	$(document).ready( function() {
		hideLoading();
		$('#numero').bind("enterKey",function(e){
			findOrder();
		});
		$('#numero').keyup(function(e){
		    if(e.keyCode == 13) {
		        $(this).trigger("enterKey");
		    }
		});
		$('#nombre').bind("enterKey",function(e){
			findOrders();
		});
		$('#nombre').keyup(function(e){
		    if(e.keyCode == 13) {
		        $(this).trigger("enterKey");
		    }
		});
		$('#numero').focus();
	});
	function findOrders() {
		var nombre = $('#nombre').val();
		$("#_orden_result").html('');
		if (nombre.length > 3) {
			$("#_orden_result").load("ajaxsearch/ordenes?nombre=" + encodeURI(nombre));
		}
	}
	function findOrder() {
		var nombre = $('#numero').val();
		$("#_orden_result").html('');
		$("#_orden_result").load("ajaxsearch/ordenes?id=" + nombre);
	}
</script>