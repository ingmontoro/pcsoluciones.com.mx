<?php

include("phpclasses/ultimatemysql/mysql.class.php");

$result = "No se envio ninguna cadena...";
$tipo = $_GET['tipo'];
if( isset( $_GET['cadena'] ) && isset($tipo)) {
	$cadena = $_GET['cadena'];
	$cadena = strtoupper(utf8_decode(urldecode($cadena)));
	$db = new MySQL();
	$result = array();
	require_once("phpclasses/ultimatemysql/create-connection.php");
	switch($tipo) {
		case 'item':
		if( $db->Query("SELECT distinct codigo, descripcion, corta, precio, cantidad, activo FROM articulo WHERE (UPPER(codigo) LIKE '%" . $cadena . "%' OR UPPER(corta) LIKE '%" . $cadena . "%' OR UPPER(descripcion) LIKE '%" . $cadena . "%') AND activo = 1")) {
			for ($index = 0; $index < $db->RowCount(); $index++) {
				$r = $db->RowArray($index, MYSQL_ASSOC);
				$result[] = array('codigo'=>utf8_encode(trim($r['codigo'])), 'corta'=>utf8_encode($r['corta']), 'precio'=>$r['precio'], 'descripcion'=>utf8_encode($r['descripcion']), 'cantidad'=>$r['cantidad'], 'activo'=>$r['activo']);
				//array_push( $result, $db->RowArray($index, MYSQL_ASSOC) );
			}
		} else {
			$result = "";
			$db->Kill();
		}
		break;
		case 'articulo':
		if( $db->Query("SELECT codigo FROM articulo WHERE UPPER(codigo) = '" . $cadena . "'")) {
			if ($db->HasRecords()) {
				$result[] = array('exist'=>'1');
			} else {
				$result[] = array('exist'=>'0');
			}
		} else {
			$result = "No hay coincidencias...";
			$db->Kill();
		}
		break;
		case 'itemNota':
		if( $db->Query("SELECT codigo, descripcion, corta, precio, cantidad FROM articulo WHERE codigo ='" . $_GET['cadena'] . "'")) {
			for ($index = 0; $index < $db->RowCount(); $index++) {
				$r = $db->RowArray($index, MYSQL_ASSOC);
				$result[] = array('codigo'=>utf8_encode(trim($r['codigo'])), 'corta'=>utf8_encode($r['corta']), 'precio'=>utf8_encode($r['precio']), 'descripcion'=>utf8_encode($r['descripcion']), 'cantidad'=>$r['cantidad']);
			}
		} else {
			$result = "No hay coincidencias...";
			$db->Kill();
		}
		break;
		case 'cliente':
		//if( $db->Query("SELECT DISTINCT id, CONCAT(Nombre, ' ', apellido1, ' ', (CASE WHEN apellido2 is null THEN '' ELSE apellido2 END)) as Nombre, email, rfc FROM cliente WHERE UPPER(CONCAT(Nombre, ' ', apellido1, ' ', (CASE WHEN apellido2 is null THEN '' ELSE apellido2 END))) like UPPER('%$cadena%') OR email like UPPER('%$cadena%') OR rfc like UPPER('%$cadena%')")) {
		if( $db->Query("SELECT DISTINCT c.id, c.nombre_fiscal,
						CONCAT(c.Nombre, ' ', c.apellido1, ' ', (CASE WHEN c.apellido2 is null THEN '' ELSE c.apellido2 END)) as Nombre,
						t.numero, c.rfc FROM cliente c left join cliente_telefono ct on c.id = ct.idCliente left join telefono t on t.clave = ct.claveTelefono 
						WHERE UPPER(CONCAT(c.nombre, ' ', c.apellido1, CASE WHEN c.apellido2 IS NOT NULL THEN CONCAT(' ', c.apellido2) ELSE '' END)) LIKE
						CONCAT('%', '" . $cadena . "', '%')
						OR UPPER(c.nombre_fiscal) LIKE CONCAT('%', '" . $cadena . "', '%')
						OR UPPER(t.numero) LIKE CONCAT('%', '" . $cadena . "', '%')  
						OR UPPER(c.rfc) LIKE CONCAT('%', '" . $cadena . "', '%')") ) {
		//if( $db->Query("SELECT distinct * FROM articulo WHERE nombre LIKE '%$cadena%'")) {
			for ($index = 0; $index < $db->RowCount(); $index++) {
				$r = $db->RowArray($index, MYSQL_ASSOC);
				$result[] = array('id'=>$r['id'], 'Nombre'=>utf8_encode($r['Nombre']), 'nombre_fiscal'=>utf8_encode($r['nombre_fiscal']), 'rfc'=>$r['rfc'], 'telefono'=>$r['numero']);
			}
		} else {
			$result = "No hay coincidencias...";
			//$result[] = array('Nombre'=>'No existen coincidencias.');
			$db->Kill();
		}
		break;
	}
}
echo json_encode($result);//json_encode(utf8_encode($result));
?>