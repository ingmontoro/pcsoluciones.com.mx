<?if($result['showpages'] == true):?>
<input id="filtersData" type="hidden">
<div class="div-100-center">
	<ul class="pagination pagination-blue m-no">
		<?if($result['navigation']->offsets->previous >= 0):?>
		<li>
			<!-- <a href="<?=$result['navigation']->link?>?offset=<?=$result['navigation']->offsets->previous?>"><i class="glyphicon glyphicon-chevron-left"></i></a>  -->
			<a href="javascript:reload(2,<?=$result['navigation']->offsets->previous?>)"><i class="glyphicon glyphicon-chevron-left"></i></a>
		</li>
		<?else:?>
		<li class="disabled"><a><i class="glyphicon glyphicon-chevron-left"></i></a></li>
		<?endif?>
		<?foreach ($result['navigation']->offsets->pages as $page => $offset):?>
		<li class="<?=$offset==$result['navigation']->offsets->actual?'active':''?>">
			<?if($offset==$result['navigation']->offsets->actual):?>
				<a><?=$page?></a>
			<?else:?>
				<!-- <a href="<?=$result['navigation']->link?>?offset=<?=$offset?>"><?=$page?></a> -->
				<a class="click-loader " href="javascript:reload(2,<?=$offset?>)"><?=$page?></a>
			<?endif;?>
			</li>
		<?endforeach?>
		<?if($result['navigation']->offsets->next < $result['navigation']->total):?>
		<li>
			<!-- <a href="<?=$result['navigation']->link?>?offset=<?=$result['navigation']->offsets->next?>"><i class="glyphicon glyphicon-chevron-right"></i></a> -->
			<a href="javascript:reload(2,<?=$result['navigation']->offsets->next?>)"><i class="click-loader glyphicon glyphicon-chevron-right"></i></a>
		</li>
		<?else:?>
		<li class="disabled"><a><i class="glyphicon glyphicon-chevron-right"></i></a></li>
		<?endif?>
		</ul>
	</div>
	<div class="div-100-center">
		<div class="dropdown btn-group">
  			<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
    			Elementos por página <span class="caret"></span>
    		</button>
  			<ul class="dropdown-menu">
  				<li><a class="click-loader" href="javascript:reload(3,10);" tabindex="-1">10</a></li>
  				<li><a class="click-loader" href="javascript:reload(3,15);" tabindex="-1">15</a></li>
  				<li><a class="click-loader" href="javascript:reload(3,25);" tabindex="-1">25</a></li>
  				<li><a class="click-loader" href="javascript:reload(3,50);" tabindex="-1">50</a></li>
  				<li><a class="click-loader" href="javascript:reload(3,75);" tabindex="-1">75</a></li>
  				<li><a class="click-loader" href="javascript:reload(3,100);" tabindex="-1">100</a></li>
  				<li><a class="click-loader" href="javascript:reload(3,200);" tabindex="-1">200</a></li>
  				<!-- <li><a href="<?=$result['navigation']->link?>?per_page=10" tabindex="-1">10</a></li>
				<li><a href="<?=$result['navigation']->link?>?per_page=25" tabindex="-1">25</a></li>
				<li><a href="<?=$result['navigation']->link?>?per_page=50" tabindex="-1">50</a></li>
				<li><a href="<?=$result['navigation']->link?>?per_page=100" tabindex="-1">100</a></li>
				<li><a href="<?=$result['navigation']->link?>?per_page=250" tabindex="-1">250</a></li> -->
			</ul>
		</div>
	</div>
<?endif;?>
<script>
	function getFilters(tipo, valor) {
		/*
		Si
		tipo = 1 es filtro de la tabla / se respetan registros y offset
		tipo = 2 es filtro de offset / se respetan filtros y registros
		tipo = 3 es de registros / se respetan filtros 
		*/
		var filters = "";
		var vals = "";
		if(tipo > 0) {
			$(".listadoFiltro").each( function(i) {
				filters += $(this).prop('name') + ',';
				vals += $(this).val() + ',';
			});
		}
		if(tipo == 1) {
			var numero = <?=getInt('per_page', 15)?>;
			$("#filtersData").val("filters=" + filters + "&values=" + vals + "&per_page=" + numero);
		} else if(tipo == 2){
			var numero = <?=getInt('per_page', 15)?>;
			$("#filtersData").val("filters=" + filters + "&values=" + vals + "&per_page=" + numero + "&offset=" + valor);
		} else if(tipo == 3){
			$("#filtersData").val("filters=" + filters + "&values=" + vals + "&per_page=" + valor);
		}
	}
	function reload(tipo, valor) {
		getFilters(tipo, valor);
		window.location = '<?=$result['navigation']->link?>?' + $("#filtersData").val();
	}
</script>