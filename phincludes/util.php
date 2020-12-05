<?php
//header("Content-Type: text/html;charset=utf-8");
/*Este script contiene diversas funciones que se utilizan 
 *repetidamente en el sistema. 
 * 
 */

//==============================================================================================
//VALIDACION, FORMATEO Y AJUSTE DE VALORES DE CAMPOS DE FORMULARIOS
//==============================================================================================
/*
 * Pone en blanco los campos de un formulario si el valor del campo
 * es igual a una constante declarada con la descripcion.
 */
function validarVacioAntesDePost( $campo, $constante ) {
	if( isset( $_POST[$campo] ) &&  $_POST[ $campo ] === $constante ) {
		$_POST[ $campo ] = '';
	}
}

/*
 *Devuelve true si el campo del formulario esta vacio( o tiene el valor de la descripcion )
 *o false si existe un valor( en caso de que el usuario ya haya llenado el campo del fomrulario ) 
 */
function campoVacio( $campo, $constante) {
	if( !isset( $_POST[$campo]) ) return true;
	if( $_POST[$campo] === $constante || $_POST[$campo] === '' ) return true;
	return false;
}

/*
 *Obtiene el valor del campo, la descripcion si no existe un valor o
 *el valor como tal si el usuario ya habia llenado dicho campo 
 */
function obtenerValor( $campo, $constante ) {
	if( campoVacio( $campo, $constante ) ) {
		return $constante;
	} return $_POST[$campo];
}

/*
 *Se encarga de decidir si se pone el atributo checked en un checkbox de un formulario
 *se basa en que anteriormente el usuario haya seleccionado o no dicho checkbox 
 */
function seleccionado( $campo ) {
	if( isset($_POST) && count($_POST) == 0 ) return "checked='checked'";
	if( campoVacio( $campo, "checked" ) ) {
		return "";
	} return "checked='checked'";
}

/*
 *Devuelve una clase u otra dependiendo del estado del campo del formulario
 *inp-vacio o inp segun sea el estado del campo 
 */
function obtenerClase( $campo, $constante ) {
	if( campoVacio( $campo, $constante ) ) {
		return 'inp-vacio';
	} return 'inp';
}

/*
 * Ajusta los valores para variables que se utilizan para formatear o preajustar valores de
 * los campos de un formulario.
 * Pueden ser variables que hacen referencia a atributos como:
 * -class
 * -value
 * -error ( que aunque no es un atributo como tal, se rquiere para poder dar la presentacion sel mismo)
 */
function calcularVariables( $campo ) {
	$GLOBALS['clase'] = obtenerClase( $campo, constant( strtoupper( $campo ) ) );
	$GLOBALS['valor'] = obtenerValor( $campo, constant( strtoupper( $campo ) ) );
	$GLOBALS['error'] = obtenerError( $campo );
}
//==============================================================================================
//OBTENCION DE VALORES COMO ERRORES, MESNAJES DE INFORMACION O ADVERTENCIA
//SON FUNCIONES QUE AYUDAN A COMPRENDER QUE ESTA PASANDO CON UN FORMULARIO
//O CUAL ES EL ESTADO ACTUAL DEL MISMO
//==============================================================================================
/*
 * VERIFICA SI EXISTE UN ERROR PARA UN CAMPO DADO
 * EN CASO DE QUE EXISTA UNO, LO REGRESA PARA SER MOSTRADO
 */
function obtenerError( $campo ) {
	if( isset($_SESSION['errores'][$campo]) ) {
		return $_SESSION['errores'][$campo];
	}
}
//==============================================================================================
//FUNCIONES QUE HACEN USO DE LA BASE DE DATOS.
//REGRESAN CODIGO HTML O VALORES NECESARIOS PARA LA PRESENTACION DEL FORMULARIO
//==============================================================================================
/*
 * Crea los tags <option> para un select.
 * Tambien puede poner una opcion como predeterminada si se especifica el valor
 */
function crearOptions( $db, $_tabla, $_clave, $_valor, $_predet ) {
	$options = "";
	
	//Aegurarse de haber creado el $db que se pasa a esta funcion
	if ( $db->Query("SELECT * FROM $_tabla") ) { 
		if( isset( $_predet ) && $_predet !== '' ) {
			while ( $row = $db->RowArray() ) {
				//$options .= "<option value='" . $row->clave . ( $row->clave == $_tipo ? "' selected='selected'>" : "'>" ) . $row->nombre . "</option>";
				$options .= "<option value='" . $row[$_clave] . ( $row[$_clave] == $_predet ? "' selected='selected'>" : "'>" ) . $row[$_valor] . "</option>";
			}
		} else {
			while ( $row = $db->RowArray() ) {
				//$options .= "<option value='" . $row->clave . "'>" . $row->nombre . "</option>";
				$options .= "<option value='" . $row[$_clave] . "'>" . $row[$_valor] . "</option>";
			}
		}
	} else {
		$options = "<option>Error</option>";
	}
	return $options;
}

function fixFolSize( $_data ) {
	if($_data == '') {
		return '';
	}
	$codelength = 6;
	$_br = '';
	$_size = strlen( $_data );
	if( $_size < $codelength ) {
		
		for( $i = 0; $i < $codelength - $_size; $i ++ ) {
			
			$_br .= '0';
		}
		$_br .= $_data;
	} else {
		$_br = $_data;
	}
	return $_br;
}
function statusTextoNota() {
	switch( $GLOBALS['_status'] ) {
		case  0 : return "Nueva";
		case  1 : return "Guardada";
		case  2 : return "Cobrada / Impresa";
		case  3 : return "Cobrada";
		case  4 : return "Cancelada";
	}
}
function statusClassNota() {
	switch( $GLOBALS['_status'] ) {
		case  0 : return "nueva";
		case  1 : return "guardada";
		case  2 : return "cobrada";
		case  3 : return "cobrada";
		case  4 : return "cancelada";
	}
}
function printNumNota() {
	$notation = "Nota ";
	$notation .= (isset ($GLOBALS['_numnota']) ? fixFolSize($GLOBALS['_numnota']) : '- - - - - -');
	$notation .= isset ($GLOBALS['_folio']) ?  " de Orden " . fixFolSize($GLOBALS['_folio']) : "";
	return $notation;
}

function iconoNota($estatusOrden, $estatusNota) {
	/*
	case  1 : return "Guardada";
	case  2 : return "Cobrada/Impresa";
	case  3 : return "Cobrada";
	*/
	/*
	case  1 : return "Guardada";
	case  2 : return "Terminada";
	*/
	$texto = "";
	$icono = "";
	if ($estatusOrden == '1' && ($estatusNota == '' || $estatusNota == null)) {
		$texto = "Aun no se crea la nota";
		$icono = 'facturaFalta';
	} else
	if ($estatusOrden == '2' && ($estatusNota == '' || $estatusNota == null)) {
		$texto = "Orden terminada sin nota";
		$icono = 'facturaWrong';
	} else
	if ($estatusOrden == '1' && $estatusNota == '1') {
		$texto = "Orden abierta y nota creada";
		$icono = 'factura';
	} else
	if ($estatusOrden == '2' && $estatusNota != '1') {
		$texto = "Orden terminada, nota cobrada";
		$icono = 'factura';
	} else
	if ($estatusOrden == '2' && $estatusNota == '1') {
		$texto = "Orden terminada, nota sin cobrar";
		$icono = 'facturaAlert';
	} else
	if ($estatusOrden == '1' && $estatusNota != '1') {
		$texto = "Orden abierta y nota cobrada";
		$icono = 'facturaAlert';
	}
	echo $icono != '' ? '<div class="divInline" style="padding-left: 5px;"><img title="' . $texto . '" src="assets/images/' . $icono . '.png" width="28px" /></div>' : '';
}
function etiquetaEstatus($estatusOrden, $estatusNota, $estatus) {
	/*
	 case  1 : return "Guardada";
	 case  2 : return "Cobrada/Impresa";
	 case  3 : return "Cobrada";
	 */
	/*
	 case  1 : return "Guardada";
	 case  2 : return "Terminada";
	 */
	$texto = "";
	$icono = "ok";
	$clase = $estatusOrden == '1' ? "warning" : "success";
	if ($estatusOrden == '2' && ($estatusNota == '' || $estatusNota == null || $estatusNota == '1')) {
		$icono = 'remove';
		if ($estatusNota == '' || $estatusNota == null) {
			$texto = "La nota no se ha creado ...";
		} else {
			$texto = "La nota no ha sido cobrada...";
			$icono = "alert";
		}
	}
	echo "<h4><label title='$texto' class='label label-$clase'>$estatus <span class='glyphicon glyphicon-$icono'></span></label></h4>";
}
function etiquetaOrden($estatusOrden, $estatus) {
	$clase = $estatusOrden == '1' ? "warning" : ($estatusOrden == '2' ? "success" : "default");
	echo "<label class='label label-$clase'>$estatus</label>";
}
function etiquetaNota($estatusOrden) {
	$clase = "success";
	$estatus = "cobrada";
	switch($estatusOrden) {
		case 1:
			$texto = "nota sin cobrar";
			$icono = "alert";
			$clase = "warning";
			$estatus = "guardada";
			break;
		case 2:
			$icono = "print";
			$estatus = "cobrada / impresa";
			break;
		case 4:
			$texto = "nota cancelada";
			$icono = "remove";
			$clase = "danger";
			$estatus = "cancelada";
			break;
		default:
				
			break;
	}
	echo "<label class='label label-$clase'>$estatus</label>";
}
function estatusNota($idEstatus, $estatus) {
	$texto = "";
	$icono = "usd";
	$clase = "success";
	switch($idEstatus) {
		case 1:
			$texto = "nota sin cobrar";
			$icono = "alert";
			$clase = "warning";
			break;
		case 2:
			$estatus = "cobrada";
			$icono = "print";
			break;
		case 4:
			$texto = "nota cancelada";
			$icono = "remove";
			$clase = "danger";
			break;
		default:
			
			break;
	}
	
	echo "<h4><label title='$texto' class='label label-$clase'>$estatus <span class='glyphicon glyphicon-$icono'></span></label></h4>";
}



function formatoTextoConPuntos($string) {
	//first we make everything lowercase, and then make the first letter if the entire string capitalized
	$string = ucfirst(mb_strtolower($string, 'UTF-8'));
	
	//now we run the function to capitalize every letter AFTER a full-stop (period).
	$string = preg_replace_callback('/[.!?].*?\w/', create_function('$matches', 'return strtoupper($matches[0]);'),$string);
	
	//print the result
	return $string;
}

function htmlDescripcionLarga($descripcion) {
	$descripcion = formatoTextoConPuntos($descripcion);
	$html_ = (strlen($descripcion) > 50) ? mb_substr($descripcion, 0, 50, 'UTF-8') . "<span style='font-weight: bold; font-size: 20px;'>...</span>" : $descripcion;
	//$html_ = formatoTextoConPuntos($html_);
	echo $html_;
}

function contiene($clave, $operador) {
	return true;
}

/**
 *
 * @param string $clave
 */
function htmlValConf($nombre='', $placeholder='', $requerido=false, $tipo="O", $pattern=null, $minLenght=null, $min=null, $max=null) {
	$html = '';
	$msg = '';
	$referencia = $nombre == '' ? "Este dato" : $nombre;
	$placeholder = $placeholder == '' ? $nombre : $placeholder;
	if($requerido) {
		$html .= ' required';
		if(null != $placeholder) {
			$msg = "es requerido";
		}
	}

	if($tipo != "O") {
		$html .= ' type="';
		if($tipo == "A" || $tipo == "NT" || $tipo == "L") {
			$html .= 'text';
		} elseif($tipo == "E") {
			$html .= 'email';
			$msg = ($msg != '' ? $msg . ", " : "") . "debe seguir el formato: ejemplo@dominio.com";
		} elseif($tipo == "U") {
			$html .= 'url';
			$msg = ($msg != '' ? $msg . ", " : "") . "debe seguir el formato: http://dominio.com";
		} elseif($tipo == "N") {
			$html .= 'number';
			$msg = ($msg != '' ? $msg . ", " : "") . "es un n&uacute;mero";
		}
		$html .= '"';
		if($tipo == "A" || $tipo == "NT" || $tipo == "L" || $pattern != null) {
			/*$html .= ' pattern="';
			switch($tipo) {
				case "A":
					$html .= '^[a-zA-Z íÍ]{1,}$';
					$msg = ($msg != '' ? $msg . ", " : "") . "s&oacute;lo admite letras";
					break;
				case "NT":
					$html .= '^[0-9]{1,}$';
					$msg = ($msg != '' ? $msg . ", " : "") . "s&oacute;lo admite numeros";
					break;
				case "L":
					$html .= '^[a-zA-Z0-9 ]{1,}$';
					$msg = ($msg != '' ? $msg . ", " : "") . "s&oacute;lo admite letras y numeros";
					break;
				default:
					$html .= $pattern;
					break;
			}*/
			$html .= '"';
		}
		if(null != $minLenght) {
			$html .= ' data-minlength="' . $minLenght . '"';
			$msg = ($msg != '' ? $msg . ", " : "") . "minimo " . $minLenght . " caracteres";
		}
		if(null != $min && $tipo == "N") {
			$html .= ' min="' . $min . '"';
			$msg = ($msg != '' ? $msg . ", " : "") . "el valor minimo es " . $min;
		}
		if(null != $max && $tipo == "N") {
			$html .= ' max="' . $max . '"';
			$msg = ($msg != '' ? $msg . ", " : "") . "el valor maximo es " . $max;
		}
	}
	return $html . ($msg != '' ? ' data-error="' . $referencia . " " . $msg . '."' : '') . ($placeholder != null ? ' placeholder="' . $placeholder . '"' : '');
}

?>