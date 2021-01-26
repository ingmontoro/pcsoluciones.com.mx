<? require_once 'phincludes/util.php' ?>
<?$tiposTelefono = $intranet->tiposTelefono() ?>
<?$edicion = $result['idUsuario'] > 0 ? true : false ?>
<?$datosUsuario = $edicion ? $result['datosUsuario'] : null?>
<?$tab = $result['tab']?>
<div id="div_cliente">
	<?php require 'phincludes/datos-usuario.php'; ?>
</div><br><br>