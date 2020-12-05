<? require_once 'phincludes/util.php' ?>
<style>
label.normal {
	font-weight: normal;
}
</style>
<h2>Buscar un artículo en inventario</h2>
<div id="div_busqueda">
	<div class="row">
		<div class="form-group col-md-4">
			<label class="normal" for="codigo" >Buscar por codigo del articulo</label>
			<div class="input-group">
				<input id="codigo" class="form-control" <?=htmlValConf('', "Código de artículo...", false)?>/>
				<span class="input-group-btn">
					<button class="btn btn-primary" onclick="findOrder();"><i class="glyphicon glyphicon-search"></i></button>
				</span>
			</div>
		</div>
		<div class="form-group col-md-7"></div>
	</div>
	<div class="row">
		<div class="form-group col-md-4">
			<label for="nombre"  class="normal" >Buscar por descripción del artículo...</label>
			<div class="input-group">
				<input <?=htmlValConf('', "Descripción larga o corta del artículo...", false)?> id="nombre" class="form-control" />
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
		$('#codigo').bind("enterKey",function(e){
			findOrder();
		});
		$('#codigo').keyup(function(e){
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
		$('#codigo').focus();
	});
	function findOrders() {
		var nombre = $('#nombre').val();
		$("#_orden_result").html('');
		if (nombre.length > 3) {
			$("#_orden_result").load("ajaxsearch/articulos?nombre=" + encodeURI(nombre));
		}
	}
	function findOrder() {
		var nombre = $('#codigo').val();
		$("#_orden_result").html('');
		$("#_orden_result").load("ajaxsearch/articulos?codigo=" + nombre);
	}
</script>