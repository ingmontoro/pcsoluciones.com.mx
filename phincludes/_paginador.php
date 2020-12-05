<?php
	if ($paginador->valido && $paginador->totalPaginas > 1 || $paginador->paginadorOffset > 0) {
		$primera = 0;
		$ultima = 0;
		echo "<ul class='pagination pagination-lg'>";
		if ($paginador->pagina > 1) {
			$anterior = $paginador->pagina - 1;
			echo "<li><a href='#' onclick='reloadOrders($anterior); return false;'><</a></li>";
		}
		if ($paginador->pagina + $paginador->paginadorOffset > $paginador->maxPaginas) {
			echo "<li><a href='#'onclick='reloadOrders(" . ($paginador->paginadorOffset) . "); return false;' title='mostrar $paginador->maxPaginas paginas anteriores'>...</a></li>";
		}
		for ($i = 1; $i <= $paginador->totalPaginas; $i ++) {
			$classa = "";
			if ($paginador->pagina == ($i + $paginador->paginadorOffset)) {
				$classa = " class='active'";
			}	
			echo "<li $classa><a href='#' onclick='reloadOrders($paginador->paginadorOffset + $i); return false;'>" . ($paginador->paginadorOffset + $i) . "</a></li>";
		}
		if ($paginador->hayMas) {
			echo "<li><a href='#'onclick='reloadOrders(" . ($paginador->paginadorOffset + $paginador->maxPaginas + 1) . "); return false;' title='mostrar $paginador->maxPaginas paginas siguientes'>...</a></li>";
		}
		if ($paginador->hayMas) {
			$paginador->paginadorOffset ++;
		}
		if ($paginador->pagina < ($paginador->totalPaginas + $paginador->paginadorOffset) ) {
			$siguiente = $paginador->pagina + 1;
			echo "<li><a href='#' onclick='reloadOrders($siguiente); return false;'>></a></li>";
		}
		$paginador->paginadorOffset --;
		echo "</ul>";
	}
	echo '<div>
			Mostrar <select class="" id="numConfig" onchange="reloadOrders(1)">
			<option value="15">15</option>
			<option value="20">20</option>
			<option value="30">30</option>
			<option value="50">50</option>
			<option value="100">100</option>
			<option value="999999999">Todos</option>
			</select> registros por p&aacute;gina.
		</div>';
	//echo "Total R: $paginador->totalRegistros - Total pag: $paginador->totalPaginas - Pagina: $paginador->pagina - Offset: $paginador->paginadorOffset - MaxPag: $paginador->maxPaginas - Hay mas. $paginador->hayMas.";
?>