<? require_once 'phincludes/util.php' ?>
<?$tiposTelefono = $intranet->tiposTelefono() ?>
<?$edicion = $result['id'] > 0 ? true : false ?>
<?$datosCliente = $edicion ? $result['datosCliente'] : null?>
<div id="div_cliente">
	<?php require 'phincludes/datos-cliente.php'; ?>
</div><br><br>