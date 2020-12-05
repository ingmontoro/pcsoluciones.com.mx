/*##############################################################################
CONTIENE EL JS DE EL SITIO ENTERO, ESTA DIVIDIDO POR SECCIONES:
-CLIENTE
-NOTA
-ORDEN SERVICIO
-UTIL
-VENTAS
-ARTICULO
################################################################################*/

function showLoading() {
	$("#loading").show();
}

function hideLoading() {
	$("#loading").hide();
	$(window).scrollTop(0);
}

/*##############################################################################

COMIENZA - UTIL

################################################################################*/
/*
 * Carga una url en la seccion CUE
 */
function loadSection(URL) {
	showLoading();
	if (URL != '') {
		$("#cue").fadeOut('slow', //{direction: 'left'}, 250, 
				function(){
			//var done = function(){
				$("#cue").load(URL, 
						function(){
					$("#cue").fadeIn('slow'//, {direction: 'right'}, 250
							);/*hideLoading();*/});
			//};
			//setTimeout(done, 0);
			});
	} else {
		$("#cue").fadeOut('slow', //{direction: 'left'}, 250, 
				function(){
				$("#cue").fadeIn('slow'//, {direction: 'right'}, 250
						);hideLoading();});
	}
}

function loadList(elemento, listado) {
	$("#" + elemento).load(listado, function() { hideLoading();});
}
/*
 * Cierra la ventana de confirmacion
 */
function closeConfirm() {
	$("#_mensaje_conf").dialog("close");
}
//Funcion para decodificar caracteres como acentos en objetos json
function utf8_decode(s) {
	//alert(s);
	try {
		return decodeURIComponent(escape(s));
	} catch(e) {
		//alert(e);
		return s;
	}
}
//Funcion para decodificar caracteres como acentos en objetos json
function utf8_encode(s) {
	//alert(s);
	try {
		return unescape (encodeURIComponent (string));
	} catch(e) {
		//alert(e);
		return s;
	}
}
/*
 * Permite esbribir solo numeros en un input 
 */
function checkNumeric() {
    return event.keyCode >= 48 && event.keyCode <= 57 || event.keyCode == 46 || event.keyCode == 45;
}

/*
 * Permite esbribir solo numeros, letras y punto en un input 
 */
function checkAlphaNumeric() {
    return 	(event.keyCode >= 48 && event.keyCode <= 57) || (event.keyCode >= 65 && event.keyCode <= 90) ||
    (event.keyCode >= 97 && event.keyCode <= 122) || event.keyCode == 32 || event.keyCode == 46;
}

/*
 * Formatea un numero a 1,999.00 (PRECIO) y lo asigna a un elemento 
 */
function currencyFormatUp(e) {
	e.value = currencyFormat (e.value);
}

/*
 * Formatea un numero a 1,999.00 (PRECIO) 
 */
function currencyFormat (val) {
    //return num.toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
	//var val = ctrl.value;
	val += '';
	//alert(val);
    val = val.replace(/,/g, "");
    //ctrl.value = "";
    val = parseFloat(val).toFixed(2);
    val += '';
    x = val.split('.');
    x1 = x[0];
    x2 = x[1];

    var rgx = /(\d+)(\d{3})/;
    
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
        //alert(x1);
    }
    //alert(x2);
    return x1 + '.' + x2;
}

/*
 * Resalta la parte de la cadena encontrada en una busqueda
 * Nota: este es experimental
 */
function markUpMatch(searchMask, txtOriginal) {
	var regEx = new RegExp(searchMask, "ig");
	var match = regEx.exec(txtOriginal);
	return null != txtOriginal ? txtOriginal.replace(regEx, '<span style="color: rgb(4, 119, 190); font-weight: bold;">' + match + '</span>') : '';
}

/*
 * Resalta la parte de la cadena encontrada en una busqueda
 * Nota: este es el original semi-funcional del de aarriba
 */
function markUpMatch_BACKUP(_cadena, txtOriginal) {
	
	return null != txtOriginal ? txtOriginal.replace(_cadena, '<span style="color: red;">' + _cadena + '</span>') : '';
}

/*
 * Prepara el div de resultados de un tooltip de busqueda para que sea valido y mostrar en el los resultados
 * Nota: requiere la variable lastDivUsed
 */
function prepareResultDiv(divName) {
	lastDivUsed = divName;
	$("#" + divName).attr('id', 'result');
}

/*
 * Libera el div de resultados de un tooltip de busqueda para poder ser usado en algun otro
 * Nota: requiere la variable lastDivUsed
 */
function releaseResultDiv(divName) {
	$("#result").attr('id', divName);
}

/*
 * Formatea el numero de orden para ser mostrado en 6 digitos (000009)
 */
function fixFolSize(fol_) {
	fol_ += '';
	var codelength = 6;
	var _br = '';
	var _size = fol_.length;
	if( _size < codelength ) {
		
		for( var i = 0; i < codelength - _size; i ++ ) {
			
			_br += '0';
		}
		_br += fol_;
	} else {
		_br = fol_;
	}
	return _br;
}

/*
 * Concatena texto(_text) a un elemento(_inputToAdd)
 */
function addTextToInput ( _inputToAdd, _text ) {
	
	_inputToAdd.val( _inputToAdd.val() + _text);
}

/*
 * Carga una url en el div cue (DIV principal del sistema)
 */
function loadOnBody( _url ) {
	var _req = $.get( _url, { 
		//variable: 'itecan'
	});
	_req.done( function( _html ) {
		$("#cue").html( _html );
	});
	_req.fail( function( msg ) {
		$("#cue").html( "Error:" + msg );
	});
}
/*
 * Revisa que un valor(param s) respete el tipo(param tipo) de dato.
 * NOTA: no parece estarse usando
 */
function checkRegEx(s, tipo) {
	var patt = null;
	if (tipo == 'alfa_num') {
		patt = /^([a-z0-9])+$/ig;
	} else if (tipo == 'alfa') {
		patt = /^([a-z])+$/i;
	} else if (tipo == 'num') {
		patt = /^([0-9])+$/ig;
	} else if (tipo == 'dec') {
		patt = /^\d+(\.\d{1,2})?$/;
	} else if (tipo == 'email') {
		patt = /^([\w-]+(?:(\.)[\w-]+)*(?:(\+)[\.\w-]+)?)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
	} else if (tipo == 'alfa_num_dir') {
		patt = /^([\w\d\s\-])+$/ig;
	}
	if (null != patt) {
		return patt.test(s);
	}
	return true;
}

/*
 * Cambia el texto del estatus de la orden o nota a uno nuevo. Aplica un efecto
 * Nota: solo para elementos con prefijo _stat
 */
function cambiarStatus(_type,  _status, _class ) {
	$("#_stat" + _type ).effect( 'explode', {}, 500, function() {
		$("#_stat" + _type ).removeClass();
		$("#_stat" + _type ).addClass('status');
		$("#_stat" + _type ).addClass('status-' + _class);
  	  	$("#_stat" + _type ).html( _status );
  	  	$("#_stat" + _type ).fadeIn( 500 );
    });
}
/*
 * Muestra un mensaje de confirmacion o error en ventana
 */
function showMessage(msg, _title) {
	$( "#_mensaje_ajax" ).prop('title', _title);
	$( "#_mensaje_ajax" ).html( msg );
	$( "#_mensaje_ajax" ).dialog({
		dialogClass: "",
		modal: true,
		resize: false,
		width: 'auto',
		position: 
        {
            my: 'center', 
            at: 'center', 
            of: '#cue'
        },
		buttons: { Aceptar: function() {	
          $( this ).dialog( "close" );
          $(this).dialog("destroy");
        }
      }
    });
}
/*
 * Abre una ventana con una orden nueva
 */
function createOrden() {
	var tempDiv = $("<div></div>");
	tempDiv.load("orden-servicio.php?clear=true");
	$( "#ventana_ordenn" ).prop('title', "Nueva orden");
	$( "#ventana_ordenn" ).html(tempDiv);
	$( "#ventana_ordenn" ).dialog({
		dialogClass: "",
		modal: false,
		resize: true,
		width: 'auto',
		height: 'auto',
		position: 
        {
            my: 'center', 
            at: 'center', 
            of: '#cue'
        },
		buttons: { Aceptar: function() {	
          $( this ).dialog( "close" );
          $(this).dialog("destroy");
        }
      }
    });
}

/*
 * Muestra un mensaje de confirmacion o error en div
 */
function showMessageDiv(msg, tipo, idMessage, timeOut) {
	timeOut = timeOut || 5000;
	var iconClass = tipo + "Icon";
	idMessage += 'Message';
	$("#" + idMessage).removeClass("success");
	$("#" + idMessage).removeClass("alert");
	$("#" + idMessage).removeClass("error");
	$("#" + idMessage).removeClass("successIcon");
	$("#" + idMessage).removeClass("alertIcon");
	$("#" + idMessage).removeClass("errorIcon");
	$("#" + idMessage).addClass(tipo);
	$("#" + idMessage).addClass(iconClass);
	$("#" + idMessage).html(msg);
	$("#" + idMessage).fadeIn(function() { setTimeout(function(){ $("#" + idMessage).fadeOut(); }, timeOut);});
}

/*
 * Para el inicio de sesion con usuario y pass
 * NOTA: no se usa actualmente
 */


/*
 * Hacer scroll a una seccion en particular
 */
function moveToAnchor(anchorId) {
	var url = location.href;               //Save down the URL without hash.
    location.href = "#" + anchorId;                 //Go to the target element.
    history.replaceState(null, null, url);   //Don't like hashes. Changing it back.
	//var element_to_scroll_to = document.getElementById(anchorId);
	//element_to_scroll_to.scrollIntoView();
}

/*
 * Muestra una ventana de confirmacion, requiere una funcion que sera ejecutada tras la confirmacion
 */
function showConfirm(msg, fC) {
	var pos = { my: 'left', at: 'center', of: event };
	var botones = { Aceptar: fC, Cancelar: function() { $(this).dialog("close");} };
	if (null == fC) {
		botones = { Aceptar: function() { $(this).dialog("close");} };
	}
	$( "#_mensaje_conf" ).prop('title', '¡Atencion!');
	$( "#_mensaje_conf" ).html( msg );
	$( "#_mensaje_conf" ).dialog({
		dialogClass: "no-close",
		modal: true,
		resize: false,
		width: 'auto',
		buttons: botones,
		position: pos        
    });
}
/*##############################################################################

FINALIZA - UTIL

################################################################################*/

/*##############################################################################

COMIENZA - ORDEN

################################################################################*/
/*
 * Busca una orden de servicio por numero de orden
 */
function buscarOrden(idSeccion) {
	//alert($('#numorden').val());
	var section = (idSeccion == '' ? 'cue' : idSeccion);
	var num_ = parseInt($('#numorden').val());
	showLoading();
	$("#" + section).fadeOut('slow', //{direction: 'left'}, 250, 
			function(){
		var _req = $.get( "orden-search.php?orden=" + num_, { 
		});
		$('#numorden').val('');
		_req.done( function( _html ) {
			$("#" + section).html( _html );
		});
		_req.fail( function( msg ) {
			$("#" + section).html( "Error:" + msg );
		});
		_req.always( function( msg ) {
			$("#" + section).fadeIn('slow');
			hideLoading();
			moveToAnchor('descripcion');
		});
	});
}

/*
 * Trata de poner el foco en el campo de busqueda
 */
function focusBuscarOrden() {
	$('#numorden').focus();
}

/*
 * recarga una pagina de ordenes(listado)
 */
function reloadOrders(pag) {
	var numero = $("#numConfig").val();
	var filters = "";
	var vals = "";
	var nombre = $('#nombre').val();
	if(typeof numero === "undefined") {
		numero = 15;
	}
	if(typeof nombre === "undefined") {
		nombre = "";
	}
	$(".listadoFiltro").each( function(i) {
		filters += $(this).prop('name') + ',';
		vals += $(this).val() + ',';
	});
	showLoading();
	loadList("divTablaOrdenes", "ordenes-listado.php?filters=" + filters + "&values=" + vals + "&pagina=" + pag + "&nombre=" +  encodeURI(nombre) + "&rpp=" + numero);
}

/*
 * recarga una pagina de tareas(listado)
 */
function reloadTareas(pag) {
	var filters = "";
	var vals = "";
	var nombre = $('#nombre').val();
	$(".listOrdF").each( function(i) {
		filters += $(this).prop('name') + ',';
		vals += $(this).val() + ',';
	});
	var filtroL = $("#universo").prop("checked");
	if (filtroL == true) {
		filters += 'universo,';
		vals += '1,';
	}
	//alert(filtroL);
	showLoading();
	loadList("divTablaTareas", "phpscripts/t-listado.php?filters=" + filters + "&values=" + vals + "&pagina=" + pag + "&nombre=" +  encodeURI(nombre));
}

function agregarLog() {
	$.ajax({
		  type: "POST",
		  async: false,
		  url: './phpscripts/log-orden.php',
		  data: { json: JSON.stringify({
			  numord: 		$('#numord').val(),
	            log: 		$('#log').val(),
	            asignado:		($("#vaasignar").prop("checked") ? $('#asignarNota').val() : '0'),
	            realizo:		$('#realizo').val(),
	            idLog:		''
	        })},
		  success: function(data) {
	        	var data = data.split('||');
				if (data[0] == 1 || data[0] == '1') {
	        		//$("#logForm").toggle();
		        	loadLogs();
	        	} else {
	        		showMessage( data[1], 'Error');
	        	}
	       }
		});
			
}

function saveAssign(idLog, select__) {
	$.ajax({
		  type: "POST",
		  async: false,
		  url: './phpscripts/asignar-log-orden.php',
		  data: { json: JSON.stringify({
			  	idLog: 			idLog,
	            asignado:		$(select__).val()
	        })},
		  success: function(data) {
	        	var data = data.split('||');
				if (data[0] == 1 || data[0] == '1') {
					//alert('all right');
					loadLogs();
	        	} else {
	        		showMessage( data[1], 'Error');
	        	}
	       }
		});
	//alert(idLog + '-' + $(select__).val());
}

function terminarTarea(idLog) {
	$.ajax({
		  type: "POST",
		  async: false,
		  url: './phpscripts/terminar-log-orden.php',
		  data: { json: JSON.stringify({
			  	idLog: 			idLog
	        })},
		  success: function(data) {
	        	var data = data.split('||');
				if (data[0] == 1 || data[0] == '1') {
					//alert('all right');
					loadLogs();
	        	} else {
	        		showMessage( data[1], 'Error');
	        	}
	       }
		});
	//alert(idLog + '-' + $(select__).val());
}

function guardarOrden() {
	var validator = $( "#datos-orden" ).validate({
		rules: {
			numcli: 		{ required: true },
			descripcion: 	{ required: true, maxlenght: 200 },
			accesorios: 	{ required: true, maxlenght: 256 },
			recibio:		{ required: true },
			asignado:	 	{ required: true }
	  }
	});
	if( validator.form() ) {
		var _request = $.post( './phpscripts/guardar-orden.php', { 
			data: { json: JSON.stringify({
				numord: 		$('#numord').val(),
	            idCliente: 		$('#numcli').val(),
	            descripcion: 	utf8_encode($('#descripcion').val()),
	            accesorios: 	utf8_encode($('#accesorios').val()),
	            recibio:		$('#recibio').val(),
	            asignado:		$('#asignado').val()
	        })}
	    });
		_request.done( function( msg ) {
			var data = msg.split('||');
			if (data[0] == 1 || data[0] == '1') {
				showMessageDiv(data[1], "success", "orden");
				//showMessage(data[1], 'Orden Guardada');
				cambiarStatus("_orden", 'Guardada', 'guardada');
				var _req = $.post( './phpscripts/getsessionvaluesbypost.php', { 
					variable: 'numord'
				});
				_req.done( function( msg ) {
					$("#numord").val( msg );
					$('#status').val( 1 );
					$('#_numord_fol').html(fixFolSize(msg));
					//setSessionValue('post', 'status', '1');
					mostrarOcultarBotonesOrden();
				});
			} else {
				showMessage(data[1], 'Error');
			}
		});
		_request.fail( function( jqXHR, textStatus ) {
			showMessage(textStatus, 'Error');
		});
	} else {
		
	}
}

function setOrdenAsModificada() {
	///setSessionValue('post', 'status', '2');
	$("#_stat_orden").html('Sin guardar*');
}

function habiliatarDeshabilitarLog() {
	var estatus = parseInt( $("#status").val() );
	if( estatus == 1 ) {
		$( "realizo" ).prop( "disabled", false );
		$( "estatusOrden" ).prop( "disabled", false );
		$( "btnAgregarLog" ).prop( "disabled", false );
		$( "log" ).prop( "disabled", false );
	} else {
		$( "realizo" ).prop( "disabled", true );
		$( "estatusOrden" ).prop( "disabled", true);
		$( "btnAgregarLog" ).prop( "disabled", true );
		$( "log" ).prop( "disabled", true );
	}
	
}

function habiliatarDeshabilitarLog() {
	var estatus = parseInt( $("#status").val() );
	if( estatus == 1 ) {
		$( "realizo" ).prop( "disabled", false );
		$( "estatusOrden" ).prop( "disabled", false );
		$( "btnAgregarLog" ).prop( "disabled", false );
		$( "log" ).prop( "disabled", false );
	} else {
		$( "realizo" ).prop( "disabled", true );
		$( "estatusOrden" ).prop( "disabled", true);
		$( "btnAgregarLog" ).prop( "disabled", true );
		$( "log" ).prop( "disabled", true );
	}
	
}

function creatNota() {
	var numcli = $("#numcli").val();
	var folio = $("#numord").val();
	terminarOrden();
	//alert('nota.php?numcli=' + numcli + '&folio=' + folio);
	loadSection('nota.php?numcli=' + numcli + '&folio=' + folio);
	moveToAnchor("busquedaItem");
}

function ordenNueva(numOrden) {
	//Borrar valores de las siguientes variables:
	//numcli=, folnot=, itecan=, status = -1
	//_iva marcar (checked='checked')
	loadSection('orden-servicio.php');
	$("#numcli").val('');
	$("#numord").val('');
	$("#status").val(-1);
	$("#numclic").val( 0 );
	//unsetSessionVar('recibio');
	//unsetSessionVar('asignado');
	//unsetSessionVar('realizo');
	buscarCliente();
	limpiarOrden();
}

function ordenVacia() {
	return $("#descripcion").val().length > 0 ? false: true ;
}


function limpiarOrden() {
	$("#descripcion").val("");
	$("#accesorios").val("");
	mostrarOcultarBotonesOrden();
}

function mostrarOcultarBotonesOrden() {
	var estatus = parseInt( $("#status").val() );
	/*
	 * CLAVE	STATUS						=>	ACCIONES POSIBLES
	 *________________________________________________________________________________________
	 *
	 * -1		VACIA( SIN GUARDAR) 		=>	GUARDAR
	 *  0		SIN GUARDAR( TIENE ITEMS )	=>	GUARDAR( LIMPIAR SI TIENE ITEMS )
	 *  1		GUARDADA					=>	TERMINAR, GUARDAR( LIMPIAR SI TIENE ITEMS ), IMPRIMIR, CREAR NOTA
	 *  2		TERMINADA 					=>	NUEVA, IMPRIMIR
	 *________________________________________________________________________________________
	 */
	$("#_bot_go").hide();
	$("#_bot_no").hide();
	$("#_bot_lo").hide();
	$("#_bot_to").hide();
	$("#_bot_cn").hide();
	$("#_bot_io").hide();
	$("#mainLog").hide();
	switch( estatus ) {
		case 0:
			$("#_bot_go").show();
			if( !ordenVacia ) $("#_bot_lo").show();
			break;
		case -1:
			$("#_bot_go").show();
			if( !ordenVacia ) $("#_bot_lo").show();
			break;
		case 1:
			if( !ordenVacia ) {
				
				$("#_bot_lo").show();
			}
			$("#_bot_to").show();
			$("#_bot_go").show();
			$("#_bot_cn").show();
			$("#_bot_io").show();
			$("#mainLog").show();
			break;
		case 2:
			$("#_bot_no").show();
			$("#_bot_cn").show();
			$("#_bot_io").show();
			$("#mainLog").show();
			break;
	}
}

function showOrder() {
	$("#contOrd").hide();
	$(".upo").each(function (index) {
		$(this).show();
	});
	moveToAnchor('descripcion');
	$("#descripcion").focus();
}
function hideOrder() {
	$(".upo").each(function (index) {
		$(this).hide();
	});
}
/*
 * Agrega tags(inventario de equipo) al campo de inventario de orden de servicio
 */
function addTag( idEle, idTag ) {
	
	var _ele = $("#" + idEle);
	var _tag = $( idTag );
	var isAdd = false;
	var txt = _tag.html();
	txt = _tag.html().replace('[X]', '').replace('[ ]', '');
	isAdd = _ele.val().indexOf('-' + txt + ' ') != -1 ? false : true;
	if (isAdd) {
		addTextToInput ( _ele, '-' + txt + ' ');
	} else {
		 _ele.val(_ele.val().replace('-' + txt + ' ', ''));
	}
	if (isAdd) {
		_tag.html(_tag.html().replace("[ ]", "[X]"));
	} else {
		_tag.html(_tag.html().replace("[X]", "[ ]"));
	}
	if (isAdd) {
		$("#contOrd").show();
	} else if(_ele.val() == '') {
		$("#contOrd").hide();
		hideOrder();
	}
	return false;
}
/*
 * Establece la orden de servicio actual como modificada
 * NOTA: No se usa, usa setSessionValue el cual se desea erradicar
 */
function setOrdenAsModificada() {
	setSessionValue('post', 'status', '2');
	$("#_stat_orden").html('Sin guardar*');
}

/*
 * Concatena texto a un campo y agrega un salto de linea
 * NOTA: Dejo de usarse
 */
function startAddTag( idEle, idTag ) {
	
	var _ele = $("#" + idEle);
	var _tag = $( idTag );
	addTextToInput ( _ele, "\n" + _tag.html() );
	return false;
}
/*##############################################################################

FINALIZA - ORDEN

################################################################################*/

/*##############################################################################

COMIENZA - CLIENTE

################################################################################*/

/*
 * Muestra los resultados de la barra de busqueda de cliente
 */
function activarToolTipCliente() {
	var _cadena = $("#busquedaCliente").val();
	var carcs = _cadena.length;
	var numord = $("#numord").val();
	if( carcs >= 3 ) {
		//$( "#busqueda" ).tooltip('disable');
		$.post("phrapi/search/cliente", 
				{ tipo: 'cliente', cadena: utf8_encode(_cadena) },
				function( data ){
					$("#result").html('');
					var ddm = jQuery( "<ul class='dropdown-menu'>" );
					var botCliNue = jQuery( "<li id='divNueCli'>" );
					botCliNue.append('&iquest;No se encuentra el cliente? puede capturar un <button id="botNueCli" type="button" onclick="bloquearCliente();"> nuevo cliente </button></div>');
					//alert(data);
					$.each( $.parseJSON(data), function( i, item ) {
						var _a = jQuery( "<li>" );
						var imgPerson = 'archivos/imagenes/sitio/person.png';
						var imgTel = 'archivos/imagenes/sitio/telefono.png';
						var imgRFC = 'archivos/imagenes/sitio/rfc.png';
						var imgNomFis = 'archivos/imagenes/sitio/fiscal.png';
						_a.prop( "id", item.id + "-autocomplete" );
						_a.addClass( "_not_close" );
						_a.addClass( "_autocomplete" );
						_a.bind('mouseenter',function( event ) {
							$(this).addClass('_autocomplete_in');
						});
						_a.bind('mouseleave',function( event ) {
							$(this).removeClass('_autocomplete_in');
						});
						_a.click(function( event ) {
							$("#numclic").val(item.id);
							if (numord != null && numord != '') {
								var toDo = function() { buscarCliente(); closeConfirm(); };
								showConfirm("Estas a punto de cambiar el Cliente a una orden ya existente.<br />Una vez que se carguen los datos, sera necesario guardar la orden con el nuevo cliente.", toDo);
								
							} else {
								buscarCliente();
							}
							$("#busquedaCliente").val('');
							$("#result").hide();
						});
						
						_a.append(
								(item.Nombre != '' && item.Nombre != null ? 
								'<img src="' + imgPerson + '" /> <div class="_autocomplete_name">' + markUpMatch(_cadena, utf8_decode(item.Nombre)) + '</div>' : '') +
								(item.nombre_fiscal != '' && item.nombre_fiscal != null ?
								'&nbsp;&nbsp;<img width="16px" src="' + imgNomFis + '" />  ' +	markUpMatch(_cadena, item.nombre_fiscal) + ' ' : '') +
								(item.rfc != '' && item.rfc != null ?
								'&nbsp;&nbsp;<img src="' + imgRFC + '" />  ' +	markUpMatch(_cadena.toUpperCase(), item.rfc.toUpperCase()) : '') +
								(item.telefono != '' && item.telefono != null ?
										'&nbsp;&nbsp;<img src="' + imgTel + '" />  ' +	markUpMatch(_cadena, item.telefono) : '')
								);
						ddm.append(_a);
						//$("#result").append( _a );
				    });
					//$("#result").append(botCliNue);
					ddm.append(botCliNue);
					$("#result").append(ddm);
				}, "json");
		$("#result").show();
	} else {
		//$( "#busqueda" ).tooltip('disable');
		$("#result").hide();
	}
}
/*
 * Guarda el clientecapturado en el formulario datos-cliente.
 */
function guardarCliente() {
	jQuery.validator.addMethod( "alpha_numeric", function( value, element) {
		return this.optional(element) || /^([a-z0-9])+$/i.test( value );
	}, "solo letras y numeros");
	var validator = $( "#datos-cliente" ).validate({
		rules: {
			rfc: 			{ required: false, alpha_numeric: true, minlength: 12, maxlength: 13 },
			nombrec: 		{ required: true, minlength: 2, maxlength: 30 },
			nombref:		{ required: false, maxlength: 199 },
			apellido1c: 	{ required: true, maxlength: 30 },
			apellido2c: 	{ required: false, maxlength: 30 },
			email: 			{ required: false },
			
			called: 		{ required: false, maxlength: 128 },
			numextd: 		{ required: false, alpha_numeric: true },
			numintd: 		{ required: false, alpha_numeric: true },
			cruzacond: 		{ required: false, maxlength: 128 },
			ycond: 			{ required: false, maxlength: 128 },
			colonia: 		{ required: false, maxlength: 128 },
			ciudadd: 		{ required: false, maxlength: 64 },
			estadod: 		{ required: false, maxlength: 64 },
			cpd: 			{ required: false, number: true, minlength: 5 },
			
			numerot: 		{ required: true, number: true, minlength: 4 },
			numerot2: 		{ required: false, number: true, minlength: 4 }
	  }
	});
	if( validator.form() ) {
		//$( "#action-nuevo" ).submit();
		//guardar datos via AJAX
		//alert( "Datos completos, se guardaran los datos del cliente" );
		//var __jsond = JSON.stringify( datos );
		var _request = $.post( './phpscripts/guardar-cliente.php', { 
			data: { json: JSON.stringify({
				numcli: 	$('#numclic').val(),
                rfc: 		$('#rfc').val(),
                nombre: 	$('#nombrec').val(),
                nombref:	$('#nombref').val(),
                apellido1: 	$('#apellido1c').val(),
                apellido2: 	$('#apellido2c').val(),
                email: 		$('#emailc').val(),
                calle: 		$('#called').val(),
                numext: 	$('#numextd').val(),
                numint: 	$('#numintd').val(),
                cruzacon: 	$('#cruzacond').val(),
                ycon: 		$('#ycond').val(),
                colonia:	$('#colonia').val(),
                ciudad: 	$('#ciudadd').val(),
                estado: 	$('#estadod').val(),
                cp: 		$('#cpd').val(),
                tipo: 		$('#tipot').val(),
                numero: 	$('#numerot').val(),
                tipo2: 		$('#tipot2').val(),
                numero2: 	$('#numerot2').val()
            })}
        });
		_request.done( function( msg ) {
			var data = msg.split('||');
			if (data[0] == 1 || data[0] == '1') {
				$('#numclic').val(data[2]);
				$('#numcli').val(data[2]);
				//showMessage(data[1], 'Cliente guardado.');
				showMessageDiv(data[1], 'success', "cliente");
				$("#div_nota").show();
			} else {
				showMessageDiv(data[1], 'error', "cliente");
			}
		});
		_request.fail( function( jqXHR, textStatus ) {
			showMessage(textStatus, 'Error.');
		});
    }
}
/*
 * Busca un cliente en base al valor numclic(id de cliente)
 * Si lo encuentra:
 * - autocomlpeta formulario de cliente
 * - ocultara la barra general de busqueda de cliente
 * - mostrara/ocultara los botones respectivos del formulario cliente
 */
function buscarCliente() {
	var numcli = $("#numclic").val();
	if( numcli != '' ) {
		$.getJSON( "cliente-search.php", 
			{ cliente: numcli },
			function( data ){
				//alert(data);
				if( data == false || data == 'false' ) {
					//setSessionValue('post', 'numcli', '');
					$("#numcli").val( '' );
					/*$("#rfc").val( '' );
					$("#nombrec").val('');
					$("#apellido1c").val( '' );
					$("#apellido2c").val( '' );
					$("#emailc").val( '' );
					
					$("#called").val( '' );
					$("#numextd").val( '' );
					$("#numintd").val( '' );
					$("#cruzacond").val( '' );
					$("#ycond").val( '' );
					$("#ciudadd").val( '' );
					$("#estadod").val( '' );
					$("#cpd").val( '' );
					
					$("#tipot").val( '' );
					$("#numerot").val( '' );
					
					$("#div_nota").hide();
					//$("#acciones_cliente").show();*/
					$("#numclic").val('');
					
					$("#_mensaje_cliente").html('Numero de cliente no encotrado.');
					
					$("#div_nota").hide();
				} else {
					
					var datos = data[ 0 ];
					//setSessionValue('post', 'numcli', numcli);
					$("#numcli").val( numcli );
					$("#rfc").val( datos.rfc );
					$("#nombrec").val( utf8_decode (datos.nombre));
					$('#nombref').val(utf8_decode (datos.nombre_fiscal));
					$("#apellido1c").val( utf8_decode (datos.apellido1));
					$("#apellido2c").val (utf8_decode (datos.apellido2));
					$("#emailc").val( datos.email );
					
					$("#called").val( utf8_decode (datos.calle));
					$("#numextd").val( datos.numext );
					$("#numintd").val( datos.numint );
					$("#cruzacond").val( utf8_decode (datos.cruzacon));
					$("#ycond").val( utf8_decode (datos.ycon));
					$("#colonia").val( utf8_decode (datos.colonia));
					$("#ciudadd").val( utf8_decode (datos.ciudad));
					$("#estadod").val( utf8_decode (datos.estado));
					$("#cpd").val( datos.cp );
					
					$("#tipot").val( datos.tipo );
					$("#numerot").val( datos.numero );
					$("#tipot2").val( datos.tipo2 );
					$("#numerot2").val( datos.numero2 );
					
					$("#div_nota").show();
					//$("#acciones_cliente").hide();
					
					$("#_mensaje_cliente").html('Cliente encontrado.');
					bloquearCliente();
					$("#referencia").hide();
					moveToAnchor("accesorios");
					shoeHideBCliente();
				}
			} );
	} else {
		$("#_mensaje_cliente").html('Escriba un numero de cliente valido.');
		$("#numclic").val( $("#numcli").val() );
	}
}
/*
 * Busca un cliente en baso a un RFC.
 * Si lo encuetra: autocompletara el formulario de cliente
 */
function buscarRFC() {
	var rfc = $("#rfc").val();
	$("#rfc").val( $("#rfc").val().toUpperCase() );
	if( rfc.length == 13 ) {
		$.getJSON( "cliente-search.php", 
		{ rfc: rfc },
		function( data ){
			if( data == false || data == 'false' ) {
				$("#numcli").val( '' );
				
				$("#acciones_cliente").show();
				$("#numclic").val('');
				
				$("#_mensaje_rfc").html('RFC no encotrado.');
				$("#div_nota").hide();
			} else {
				var datos = data[ 0 ];
				$("#numcli").val( datos.id );
				$("#numclic").val( datos.id );
				$("#nombrec").val( datos.nombre );
				$('#nombref').val(datos.nombref);
				$("#apellido1c").val( datos.apellido1 );
				$("#apellido2c").val( datos.apellido2 );
				$("#emailc").val( datos.email );
				
				$("#called").val( datos.calle );
				$("#numextd").val( datos.numext );
				$("#numintd").val( datos.numint );
				$("#cruzacond").val( datos.cruzacon );
				$("#ycond").val( datos.ycon );
				$("#ciudadd").val( datos.ciudad );
				$("#estadod").val( datos.estado );
				$("#cpd").val( datos.cp );
				
				$("#tipot").val( datos.tipo );
				$("#numerot").val( datos.numero );
				$("#tipot2").val( datos.tipo2 );
				$("#numerot2").val( datos.numero2 );
				
				$("#div_nota").show();
				//$("#acciones_cliente").hide();
				$("#_mensaje_rfc").html('RFC encontrado.');
			}
			
		} );
	}
}

/*
 * Oculta la barra general de busqueda y muestra el formulario de cliente
 * Nota: se usa en orden de servicio
 */
function bloquearCliente() {
	$('#tablaBusqueda').hide();
	$('#div_cliente').show();
}

/*
 * Limpia el formulario de cliente
 * Si el parametro hideDiv == true:
 * - oculta el div #div_nota (usado en orden de servicio)
 * Nota: se usa en orden servicio y nota 
 */
function limpiarCliente(hideDiv) {
	var toDo = function() {
		$("#numcli").val( '' );
		$("#numclic").val('');
		$("#rfc").val( '' );
		$("#nombrec").val('');
		$('#nombref').val('');
		$("#apellido1c").val( '' );
		$("#apellido2c").val( '' );
		$("#emailc").val( '' );
		
		$("#called").val( '' );
		$("#numextd").val( '' );
		$("#numintd").val( '' );
		$("#cruzacond").val( '' );
		$("#ycond").val( '' );
		$("#colonia").val( '' );
		$("#ciudadd").val( 'Guadalajara' );
		$("#estadod").val( 'Jalisco' );
		$("#cpd").val( '' );
		
		$("#tipot").val( '' );
		$("#numerot").val( '' );
		$("#tipot2").val( '' );
		$("#numerot2").val( '' );
		shoeHideBCliente();
		if (hideDiv == 'true') {
			$("#div_nota").hide();
		}
		closeConfirm();
	};
	showConfirm("Estas seguro de querer limpiar los datos del cliente?<br />Si elijes aceptar, se limpiara el formulario y se ocultara la orden hasta que captures un cliente.", toDo);
}

function lockOptions() {
	$("#tipot").val(2);
	$("#tipot2").val(1);
	//$("#tipot").disable('true');
}

/*
 * Muestra/Oculta los botones del formulario de cliente en base al estado del mismo
 */
function shoeHideBCliente() {
	var nc = $("#numclic").val();
	var exists = null == nc || '' == nc ? false : true;
	$("#_bot_gc").hide();
	$("#_bot_ac").hide();
	if (exists) {
		$("#_bot_ac").show();
	} else {
		$("#_bot_gc").show();
		lockOptions();
	}
}
/*##############################################################################

FINALIZA - CLIENTE

################################################################################*/

/*##############################################################################

COMIENZA - NOTA

################################################################################*/
/*
 * Lleva a una nota
 */
function cargarNota(numNota) {
	$("#numnota").val(numNota);
	buscarNota('');
}

/*
 * Busca una nota y la muestra
 */
function buscarNota(idSeccion) {
	var section = (idSeccion == '' ? 'cue' : idSeccion);
	var num_ = parseInt($('#numnota').val());
	//setSessionValue('post', 'numnota', num_);
	showLoading();
	$("#" + section).fadeOut('slow', //{direction: 'left'}, 250, 
			function(){
		var _req = $.get( "nota-search.php?nota=" + num_, { 
		});
		$('#numnota').val('');
		_req.done( function( _html ) {
			$("#" + section).html( _html );
		});
		_req.fail( function( msg ) {
			$("#" + section).html( "Error:" + msg );
		});
		_req.always( function( msg ) {
			$("#" + section).fadeIn('slow');
			hideLoading();
			moveToAnchor("busquedaItem");
		});
	});
}
/*
 * Levanta una bandera que indica que algo cambio en la "ventana" actual
 */
function algoCambio(nombreCampo) {
	$("#" + nombreCampo).val(1);
}
/*
 * Obtiene una bandera que indica si hay cambios sin guardar
 */
function hayCambios(nombreCampo) {
	return $("#" + nombreCampo).val() == 1 ? true : false;
}
/*
 * Baja bandera de cambios
 */
function sinCambios(nombreCampo) {
	return $("#" + nombreCampo).val(0);
}
/*
 * Registra el cobro de una nota
 */
function generarCobro(imprimir) {
	//Antes que nada guardamos la nota
	if (hayCambios('cambios')) {
		guardarNota();
	}
	$("#msgCobro").removeClass('error');
	$("#msgCobro").removeClass('ok');
	$("#msgCobro").addClass('waiting');
	$("#msgCobro").html('Espere...');
	var numero = $('#numnota').val();
	var entregado = $('#entregado').val().replace(',','');
	var _request = $.post( './phpscripts/generar-cobro.php', { 
		data: { json: JSON.stringify({
			numero: 		numero,
			entregado: 		entregado
        })}
    });
	_request.done( function( msg ) {
		var data = msg.split('||');//Se espera true:mensaje o false:mensaje
		if (data[0] == 1 || data[0] == '1') {
			$("#statusNota").val(3);
			//$("#_stat_nota").html( "Cobrada" );
			cambiarStatus("_nota", "Cobrada", "cobrada")
			$("#msgCobro").removeClass('waiting');
			$("#msgCobro").addClass('ok');
			$("#msgCobro").html(data[1]);//actualizar botones
			var botones = { Cerrar: function() { $(this).dialog("close");} };
			$("#_mensaje_cobrar").dialog("option", "buttons", botones);
			if (imprimir) {
				//alert('Ticket impreso');
				//$("#_mensaje_cobrar").dialog("close");
				printTicket();
			} else {
				//$("#_mensaje_cobrar").dialog("close");
			}
			mostrarOcultarBotonesNota();
		} else {
			$("#msgCobro").html(data[1]);
			$("#msgCobro").removeClass('waiting');
			$("#msgCobro").addClass('error');
		}
	});
	_request.fail( function( jqXHR, textStatus ) {
		$("#msgCobro").removeClass('waiting');
		$("#msgCobro").addClass('error');
		$("#msgCobro").html("Error al intentar registrar el cobro");
	});
	_request.always( function() {
		$("#msgCobro").show();
	});
	
}
/*
 * Cancela un ticket
 */
function cancelarTicket() {
	var toDo = function () {
		var folio = '';
		var numNota = $('#numnota').val();
		var numCli = $('#numcli').val();
		folio = $('#folio').val();
		var _request = $.post( './phpscripts/cancelar-nota.php', { 
			data: { json: JSON.stringify({
				numero: 		numNota
	        })}
	    });
		_request.done( function( msg ) {
			var data = msg.split('||');
			if (data[0] == 1 || data[0] == '1') {
				closeConfirm();
				cambiarStatus("_nota", "Cancelada", "cancelada");
				showMessageDiv("La nota " + numNota + (folio != '' ? " de la orden no: " + folio : "") +
						" ha sido cancelada.<br />Para crear una nueva nota" +
						(folio != '' ? " para la misma orden" : "") +
						" haz clic <span class='status status-small status-nueva' style='cursor: pointer;' onclick=\"" +
						(folio != '' ? "loadSection('nota.php?numcli=" + numCli + "&folio=" + folio + "')" : "notaNueva()") +
						"\"> aqui.</span>",
						"error", "nota", 36000);
				/*if (folio != '') {
					loadSection('nota.php?numcli=' + $("#numcli").val() + '&folio=' + folio);
					moveToAnchor("busquedaItem");
				} else {
					notaNueva();
				}*/
			} else {
				showMessageDiv(data[1], 'error', 'nota');
			}
		});
		_request.fail( function( jqXHR, textStatus ) {
			showMessageDiv(data[1], 'error', 'nota');
		});
	}
	showConfirm("Estas seguro de querer cancelar la nota?<br />Si elijes aceptar, se mostrara una nota nueva, la actual se cancelara y el stock sera reabastecido en el inventario.", toDo);
}
/*
 * Muestra una vista previa de un ticket
 */
function showTicket() {
	var numero = $('#numnota').val();
	var content = "";
	var pos = { my: 'center', at: 'center', of: "#cue" };
	var botones = { Aceptar: function() { $(this).dialog("close"); $(this).dialog("destroy");} };
	var _request = $.get( 'phpscripts/show-ticket.php', { 
		nota: numero
    });
	_request.done(function(msg) {
		content = msg;
	});
	_request.fail( function( jqXHR, textStatus ) {
		content = "Error al intentat mostrar el ticket.";
	});
	_request.always(function() {
		$( "#_mensaje_ajax" ).prop('title', 'Vista previa de ticket');
		$( "#_mensaje_ajax" ).html(content);
		$( "#_mensaje_ajax" ).dialog({
			dialogClass: "no-close",
			modal: true,
			resize: false,
			width: 'auto',
			buttons: botones,
			position: pos        
	    });
	});
}

/*
 * Imprime el ticket
 */
//SE COMENTA PARA INCLUIRSE EN EL ARCHIVO PHP Y PODER USAR UNA VARIABLE EN EL NOMBRE DEL SCRIPT QUE IMPRIME
/*function printTicket(lment) {
	var oldTxt = '';
	if (lment) {
		oldTxt = $(lment).html();
		$(lment).addClass('waiting');
		$(lment).html('espere...');
	} else {
		$("#msgImpresion").addClass('waiting');
		$("#msgImpresion").html('Imprimiendo...');
	}
	var numero = $('#numnota').val();
	var content = "";
	var _request = $.post("phpscripts/print-ticket.php", { 
		nota: numero });
	
	_request.done( function( msg ) {
		var data = msg.split('||');//Se espera true:mensaje o false:mensaje
		if (data[0] == 1 || data[0] == '1') {
			//alert(data[0]);
			$("#statusNota").val(2);//impreso
			$("#_stat_nota").html("Cobrada / Impresa");
		}
		content = data[1];//para mensaje de impresion REAL del ticket
	});
	_request.fail( function( jqXHR, textStatus ) {
		content = "Error al intentar imprimir ticket";
	});
	_request.always( function() {
		//hideLoading();
		if (lment) {
			$(lment).removeClass('waiting');
			$(lment).html(oldTxt);
		} else {
			$("#msgImpresion").hide();
		}
		var pos = { my: 'center', at: 'center', of: "#cue" };
		var botones = { Aceptar: function() { $(this).dialog("close"); $(this).dialog("destroy");} };
		$( "#_mensaje_ajax" ).prop('title', 'Impresion de ticket');
		$( "#_mensaje_ajax" ).html(content);
		$( "#_mensaje_ajax" ).dialog({
			dialogClass: "no-close",
			modal: true,
			resize: false,
			width: 'auto',
			buttons: botones,
			position: pos        
	    });
	});
}/*

/*
 * Muestra una ventana para hacer el cobro de la nota
 */
function showCobrar(msg, numero) {
	
		var pos = { my: 'center', at: 'center', of: '#cue' };
		var botones = { Cobrar: function() { generarCobro(false); }, 'Cobrar e Imprimir': function() { generarCobro(true); }, Cerrar: function() { $(this).dialog("close"); $(this).dialog("destroy");} };
		$( "#_mensaje_cobrar" ).prop('title', 'Cobro de Ticket #' + numero);
		$( "#_mensaje_cobrar" ).html( msg );
		$( "#_mensaje_cobrar" ).dialog({
			dialogClass: "no-close",
			modal: true,
			resize: false,
			width: 'auto',
			buttons: botones,
			position: pos        
	    });
		
}

/*
 * Recarga una pagina de notas(listado)
 */function reloadNotas(pag) {
	var numero = $("#numConfig").val();
	var filters = "";
	var vals = "";
	$(".listOrdF").each( function(i) {
		filters += $(this).prop('name') + ',';
		vals += $(this).val() + ',';
	});
	showLoading();
	loadList("divTablaNotas", "phpscripts/n-listado.php?filters=" + filters + "&values=" + vals + "&pagina=" + pag + "&rpp=" + numero);
}

/*
 * Crea una fila(elementos HTML) de la nota de venta
 */
function crearFila( item, cantidadPre, uIdPre, descripcion, precio, esAcce) {
	//Checar si ya existe el item
	var uniqueId = '';
	var needDesc = false;
	var wasEmpty = notaVacia();
	if( isFreeItem (item.codigo)) {
		if( null == uIdPre || uIdPre == '' || uIdPre == 0 || uIdPre == '0' || uIdPre == 'free') {
			uniqueId = createUniqueID();
			needDesc = true;
		} else {
			uniqueId = uIdPre;
		}
	}
	var filasExistentes = $( '#' + item.codigo );
	if( filasExistentes.length && !isFreeItem (item.codigo) ) {
		
		var inputCantidad = $( '#' + item.codigo + '-cantidad' );
		var spanTotal = $( '#' + item.codigo + '-total' );
		var cantidad = 0;
		try {
			cantidad = parseInt( inputCantidad.val() );
		} catch( e ) {
			cantidad = 1;
		}
		if( cantidad < item.cantidad ) {
			inputCantidad.val( ++cantidad );
			spanTotal.html( currencyFormat(parseFloat( item.precio * cantidad )));
			calcularTotales();
		}
		if (wasEmpty) {
			mostrarOcultarBotones();
		}
		return;
	}
	
	//alert(item['nombre']);
	var filaNueva = jQuery("<tr>");
	filaNueva.addClass("_item");
	filaNueva.prop( 'id', isFreeItem (item.codigo) ? uniqueId : item.codigo );
	var cuerpoTabla = $("#_prods > tbody");
	var linea = cuerpoTabla.children('tr:nth-last-child(4)');
	
	//var nombreCeldas = new Array('codigo','cantidad','nombre','corta','descripcion','precio', 'npiezas', 'total', 'acciones');
	var nombreCeldas = new Array('codigo','corta','precio', 'npiezas', 'total', 'acciones');
	var nombresBotones = new Array('quitar');
	
	var spanSPesos = jQuery("<span>"); spanSPesos.addClass("_simbolo").append("$");
	var spanMXN = jQuery("<span>"); spanMXN.addClass("_simbolo").append("");
	var spanImporte = jQuery("<span>");
	
	var celdas = new Array();
	var botones = new Array();
	//var cantidad = 2;
	
	
	
	for( var i = 0; i < nombreCeldas.length; i ++ ) {
		
		celdas[ i ] = jQuery("<td>");
		celdas[ i ].addClass("_campo_nota");
		if( nombreCeldas[ i ] == 'acciones' ) {
			celdas[ i ].addClass("td_c");
			celdas[ i ].prop('colspan', nombresBotones.length );
			for( var b = 0; b < nombresBotones.length; b ++ ) {
				
				botones[ b ] = jQuery("<button>");
				botones[ b ].addClass("_bot_acc");
				botones[ b ].append( nombresBotones[ b ] );
				if( nombresBotones[ b ] == 'quitar' ) {
					
					botones[ b ].addClass("_bot_eli");
					botones[ b ].bind('click', function( e ){
						algoCambio('cambios');
						e.preventDefault();
						filaNueva.removeClass('_item');
						filaNueva.next().effect( 'fade', {}, 650, function() { filaNueva.next().remove(); } );
						filaNueva.effect( 'fade', {}, 650, function() { filaNueva.remove(); calcularTotales(); } );
						if( null != uniqueId && uniqueId != '' ) {
							
							//eliminar de la variable se session>>items>>uniqueId
							//unsetSessionVar( 'items>>' + uniqueId );
						}
					});
				} else {
					botones[ b ].prop("disabled", true);
				}
				celdas[ i ].append( botones[ b ] );
			}
		} else if( nombreCeldas[ i ] == 'precio' ) {
			celdas[ i ].addClass("td_r");
			if (isFreeItem (item.codigo)) {
				
				var inputPrecio = jQuery("<input id='" + uniqueId + "-precio' style='font-family: arial; text-align: right; font-size: 16px;' size=5 maxlength=9 onkeypress='return checkNumeric();' onblur='currencyFormatUp(this);' />");
				if( null == precio || precio == '' ) {
					
					precio = 0;
				}
				inputPrecio.change(function() { algoCambio('cambios'); });
				inputPrecio.val( currencyFormat (parseFloat (precio)) );
				inputPrecio.blur( function() {
					
					//saveFreeDataForItem( uniqueId );
					//saveIteCanInfo();
					arreglaCantidadFree( uniqueId );
				});
				inputPrecio.click( function() {
					
					this.select();
				});
				celdas[ i ].append( spanSPesos.clone() ).append( inputPrecio ).append( spanMXN.clone() );
			} else {
				
				celdas[ i ].append( spanSPesos.clone() ).append( currencyFormat (parseFloat (item.precio)) ).append( spanMXN.clone() );
			}
		} else if( nombreCeldas[ i ] == 'npiezas' ) {
			celdas[ i ].addClass("td_c");
			var divMas = jQuery("<div>");
			var divMenos = jQuery("<div>");
			var inputCantidadFila = jQuery("<input>");
			var idInputCantidadFila = ( uniqueId == '' ?  item.codigo : uniqueId ) + '-cantidad';
			inputCantidadFila.prop('id', idInputCantidadFila);
			inputCantidadFila.prop('readonly', 'readonly');
			inputCantidadFila.focus(function() {
				inputCantidadFila.addClass('_focused');
			});
			inputCantidadFila.blur(function() {
				inputCantidadFila.removeClass('_focused');
			});
			inputCantidadFila.change(function() {
				if( null != uniqueId && uniqueId != '' ) {
					
					arreglaCantidadFree( uniqueId );
				} else {
					
					arreglaCantidad( inputCantidadFila, item );
				}
			});
			inputCantidadFila.keydown(function( event ) {
				//var _val = parseInt( inputCantidadFila.val() );
				if ( event.which == 38 ) {
					event.preventDefault();
					//_val ++;
					sumarUno( inputCantidadFila, item );
					if( null != uniqueId && uniqueId != '' ) {
						
						arreglaCantidadFree( uniqueId );
					} else {
						
						arreglaCantidad( inputCantidadFila, item );
					}
				} else if ( event.which == 40 ) {
					event.preventDefault();
					//_val --;
					restarUno( inputCantidadFila, item );
					if( null != uniqueId && uniqueId != '' ) {
						
						arreglaCantidadFree( uniqueId );
					} else {
						
						arreglaCantidad( inputCantidadFila, item );
					}
				}
				//inputCantidadFila.val( _val );
				//inputCantidadFila.change();
			});
			inputCantidadFila.prop("size", '2');
			inputCantidadFila.prop("maxlength", '3');
			inputCantidadFila.val( null != cantidadPre ? cantidadPre : 1 );
			
			divMas.append('&#x25B2;');
			divMas.click( function() {
				sumarUno( inputCantidadFila, item );
				//alert('UID: ' + uniqueId);
				if( null != uniqueId && uniqueId != '' ) {
					
					arreglaCantidadFree( uniqueId );
				} else {
					
					arreglaCantidad( inputCantidadFila, item );
				}
				//inputCantidadFila.change();
			}); 
			divMenos.append('&#x25BC;');
			divMenos.click( function() {
				restarUno( inputCantidadFila, item );
				if( null != uniqueId && uniqueId != '' ) {
					
					arreglaCantidadFree( uniqueId );
				} else {
					
					arreglaCantidad( inputCantidadFila, item );
				}
				//inputCantidadFila.change();
			});
			divMas.addClass('_momb');
			divMenos.addClass('_momb');
			//inputCantidadFila.after( divMas );
			//inputCantidadFila.after( divMenos );
			celdas[ i ].append( inputCantidadFila ).append( divMas ).append( divMenos );
		} else if( nombreCeldas[ i ] == 'total' ) {
			celdas[ i ].addClass("td_r");
			if( null != uniqueId && uniqueId != '' ) {
				//existe un uniqueId
				spanImporte.append( currencyFormat (parseFloat( precio * cantidadPre ) ) );//Usar variables
			} else {
				//Es item nuevo de la lista
				spanImporte.append( currencyFormat (parseFloat( item.precio * ( null == cantidadPre ? 1 : cantidadPre ) ) ));//Si es de la lista
			}
			spanImporte.addClass( "_importe" );
			spanImporte.prop( 'id', ( uniqueId == '' ?  item.codigo : uniqueId ) + '-total' );
			celdas[ i ].append( spanSPesos.clone() ).append( spanImporte.clone() ).append( spanMXN.clone() );
		//} else if( isFreeItem (item.codigo) && nombreCeldas[ i ] == 'descripcion' ){
		} else if( isFreeItem (item.codigo) && nombreCeldas[ i ] == 'corta' ){
			var checkedAsAcce = '';
			if (esAcce && esAcce == 1 || esAcce == '1') {
				checkedAsAcce = ' checked="checked" ';
			}
			var inputCantidadFila = jQuery("<textarea id='" + uniqueId + "-descripcion' " +
					"class='txtAreaItemNota' " +
					"onkeypress='return checkAlphaNumeric();'></textarea>" +
					"<div style='float: right;'>" +
					"<label class='labelItemNota' title='&iquest;es accesorio?' for='" + uniqueId + "-esAccesorio'>" + txtLabelItemNota + "</label><input class='checkItemNota' type='checkbox'" + checkedAsAcce + "id='" + uniqueId + "-esAccesorio'/></div>");
			inputCantidadFila.change(function() { algoCambio('cambios'); });
			if( null != descripcion && descripcion != '' ) {
				
				inputCantidadFila.val( descripcion );
			}
			inputCantidadFila.blur( function() {
				
				//saveFreeDataForItem( uniqueId );
				//saveIteCanInfo();
			});
			celdas[ i ].append( inputCantidadFila );
		} else if (nombreCeldas[ i ] == 'codigo') {
			var codigo = "000000";
			if (!isFreeItem (item.codigo)) {
				codigo = fixFolSize(item.codigo.toUpperCase());				
			}
			celdas[ i ].append(codigo);
			celdas[ i ].prop('id', 'codigo');
		} else {
			
			celdas[ i ].append( item[ nombreCeldas[ i ] ]);
		}
		filaNueva.append( celdas[ i ] );
	}
	linea.after( filaNueva );
	filaNueva.after( linea.clone() );
	if (wasEmpty) {
		//alert('');
		mostrarOcultarBotonesNota();
	}
	if (needDesc) {
		$("#" + uniqueId + "-descripcion").focus();
	}
	calcularTotales();
}
/*
 * Recupera los articulos de la nota y crea la cadena para el server
 */
function saveIteCanInfo() {
	
	var itemsCantidad = obtenerItemsCantidad();
	//setSessionValue( 'post', 'itecan', itemsCantidad );
	$("#itecan").val( itemsCantidad );
}
/*
 * Decide si el item en cuestion es abierto o codigo del inventario
 */
function isFreeItem(data) {
	return data == 'free' || data == '0' || data == 0;
}
/*
 * Muestra los resultados de articulos en base a una busqueda
 * Nota: Implementada en nota de venta
 */
function activarToolTip() {
	var carcs = $("#busquedaItem").val().length;
	var _cadena = $("#busquedaItem").val();
	if( carcs >= 3 ) {
		//$( "#busqueda" ).tooltip('disable');
		$.getJSON( "search.php", 
				{ tipo: "item", cadena: utf8_encode(_cadena) },
				function( data ){
					$("#result").html('');
					if (data == '') {
						$("#result").append("<div style=' font-style: italic; padding: 5px; font-weight: bold; color: orangered;'>No existen coincidencias...</div>");
					} else {
						$.each( data, function( i, item ) {
							var _a = jQuery( "<div style=' font-style: italic;' title='texto de prueba' class='toolTip'>" );
							//var imgCod = 'archivos/imagenes/sitio/codigo.png';
							var imgPre = 'archivos/imagenes/sitio/billete.png';
							var theClass = "precioItem";
							//var imgDes = 'archivos/imagenes/sitio/info.png';
							_a.prop( "id", item.codigo + "-autocomplete" );
							_a.addClass( "_not_close" );
							_a.addClass( "_autocomplete" );
							_a.bind('mouseenter',function( event ) {
								$(this).addClass('_autocomplete_in');
							});
							_a.bind('mouseleave',function( event ) {
								$(this).removeClass('_autocomplete_in');
							});
							if (item.cantidad > 0) {
								_a.click(function( event ) {
									crearFila( item );
								});
							} else {
								theClass += " precioItemNs";
								_a.addClass( "_autocomplete_na" );
								item.cantidad = "SIN STOCK";
							}
							//_a.append( item.clave + ' - ' + item.nombre + ' - ' + item.corta + ' - ' + item.descripcion );
							//_a.append( item.nombre + ', ' + item.corta + ', ' + item.descripcion );
							_a.append(
									//'<img width="22px" style="vertical-align: middle;" src="' + imgCod + '" />  ' +	markUpMatch(_cadena, fixFolSize(item.codigo)).toUpperCase() + ' ' +
									//'<img width="22px" style="vertical-align: middle;" src="' + imgDes + '" />  ' + markUpMatch(_cadena, item.corta) + ' ' +
									'<div style="">' + markUpMatch(_cadena, item.corta) + '' +
									//'<img width="22px" style="vertical-align: middle;" src="' + imgPre + '" />  $' +	markUpMatch(_cadena, currencyFormat(item.precio))
									'<span class="' + theClass + '">$' +	markUpMatch(_cadena, currencyFormat(item.precio) + "  (" + item.cantidad + ")</span></div>")
									);
							$("#result").append( _a );
							//$("#" + i).click(function( event ) {
								//alert("Agregar item con clave: " + item.clave );
							//	crearFila( item );
							//});
					    });
					}
				} );
		$("#result").show();
	} else {
		//$( "#busqueda" ).tooltip('disable');
		$("#result").hide();
	}
	$(".toolTip").each(function() {
		//alert($(this).prop('title'));
		$(this).tooltip({content: $(this).prop('title'), track: true});
	});
}

/*
 * Agrega un articulo(del inventario a la nota de venta)
 * NOTA: quizas sea necesario excluir los free y crear directamente la entrada sin
 * consultar el server
 */
function addItemRowByCodigo( codigo, cantidad, _uniqueID, descripcion, precio, esAcce) {
	$.ajax({
		  type: "GET",
		  async: false,
		  url: 'search.php',
		  dataType: 'json',
		  data: { tipo: 'itemNota', cadena: codigo },
		  success: function(data) {
			  $.each( data, function( i, item ) {
					//if( )
					crearFila( item, cantidad, _uniqueID, descripcion, precio, esAcce);
			    });
	       }
		});
}

/*
 * Agrega un articulo abierto a la nota de venta
 * NOTA: Se usa para poblar la nota en base a la data que regresa el server
 */
function addFreeItemRowByCodigo( codigo, descripcion, precio, cantidad, esAcce) {
	
	addItemRowByCodigo( 'free', cantidad, codigo, descripcion, precio, esAcce );
}

/*
 * Agrega una entrada abierta nueva a la nota de venta
 */
function addOpenDescItem() {
	
	addItemRowByCodigo( 'free', 1 );
}
function IVAClick() {
	algoCambio('cambios');
	calcularTotales();
}
/*
 * Calcula importes y totales enl a nota de venta
 * NOTA: usa metodos que guardan datos en sesion los cuales se deben erradicar 
 */
function calcularTotales() {
	var dato_iva = .16;
	var aplicarIVA = $("#_iva").prop("checked");
	var _subn = 0;
	var _ivan = 0;
	var _totaln = 0;
	//_____________________________________
	var _sub = $("#subtotal");
	var _iva = $("#iva");
	var _total = $("#total");
	//_____________________________________
	$("._importe").each( function( index ) {
		_subn += parseFloat( $(this).html().replace(',', '') );
	});
	if( aplicarIVA ) {
		_ivan = _subn * dato_iva;
		_totaln = _ivan + _subn;
	} else {
		_ivan = 0;
		_totaln = _subn;
	}
	
	//setSessionValue( 'post', 'apliva', aplicarIVA );
	
	_sub.html( currencyFormat (parseFloat ( _subn )));
	_iva.html( currencyFormat (parseFloat ( _ivan )));
	_total.html ( currencyFormat (parseFloat ( _totaln )));
	
	//saveIteCanInfo();
	/*var _req = $.post( './phpscripts/getsessionvaluesbypost.php', { 
		variable: 'itecan'
	});
	_req.done( function( msg ) {
		
	});
	_req.fail( function( msg ) {
		
	});*/
}

/*
 * Arregla o valida que la cantidad de un articulo sea siempre
 * mayor a 1 y menor o igual al stock de un articulo del sistema
 */
function arreglaCantidad( _inp, _item ) {
	var _val = 1;
	try {
		_val = parseInt( _inp.val() );
	} catch( e ) {				
	}
	if( _val < 1 || isNaN( _val ) ) {
		_inp.val( 1 );
	} else if( _val > parseInt( _item.cantidad ) ) {
		_inp.val( _item.cantidad );
	}
	$('#' + _item.codigo + '-total').html (currencyFormat (parseFloat ( _item.precio * _inp.val())));
	calcularTotales();
}

/*
 * Arregla o valida que la cantidad de un articulo sea siempre
 * mayor a 1 de un articulo abierto (free)
 */
function arreglaCantidadFree( uniqueId ) {
	var _precio = $( "#" + uniqueId + '-precio' );
	var _cantidad = $( "#" + uniqueId + '-cantidad' );
	if( _precio.val().replace(',', '') == 0 ) {
		
		_precio.val( 0 );
	}
	if( _cantidad.val() < 0 ) {
		
		_cantidad.val(1);
	}
	$('#' + uniqueId + '-total').html( currencyFormat (parseFloat( _precio.val().replace(',', '') * _cantidad.val() )) );
	calcularTotales();
}

/*
 * Suma uno a la cantidad de un item dado
 * Puede ser disparado por usar las flecas fisicas
 * del teclado o las flechas de la interfaz
 */
function sumarUno( _inp, item ) {
	var _val = parseInt( _inp.val() );
	_val ++;
	_inp.val( _val );
	algoCambio('cambios');
	//arreglaCantidad( _inp, item, '' );
}

/*
 * Resta uno a la cantidad de un item dado
 * Puede ser disparado por usar las flecas fisicas
 * del teclado o las flechas de la interfaz
 */
function restarUno( _inp, item ) {
	var _val = parseInt( _inp.val() );
	_val --;
	if (_val < 1) {
		_val = 1;
	}
	_inp.val( _val );
	algoCambio('cambios');
	//arreglaCantidad( _inp, item, '' );
}

/*
 * Crea una cadena(id) unica para los items/articulos abiertos que se usan en la nota de venta
 * Nota: puede usarse para cualquier cosa el ID generado
 */
function createUniqueID() {
	var _time = new Date();
	var _rnumber = _time.getFullYear() + '' + _time.getMonth() + '' + _time.getDay() + '' + _time.getHours() + '' + _time.getMinutes() + '' + _time.getMilliseconds();
	//alert(_rnumber);
	return _rnumber;
}

/*
 * Muestra una ventana para registrar el cobro de una nota de venta
 */
function cobrarTicket() {
	if (obtenerItemsCantidad())	{
		var total = $('#total').html().replace(',', '');
		var totalN = parseFloat(total);
		if (totalN > 0) {
			var numero = $('#numnota').val();
		      $("#divCobrar").load("phpscripts/cobrar.php?total=" + total + "&numero=" + numero, function() { showCobrar($("#divCobrar"), numero); });
		} else {
			showMessageDiv("No se puede cobrar la cantidad de $0.00, revise la nota y vuelva a intentarlo.", 'alert', 'nota', 10000);
		}
	}
}

/*
 * Obtiene una cadena que guarda la info de los articulos, descripciones, cantidades y precios de la nota de venta.
 * Nota: se usa para enviar la info al server.
 */
function obtenerItemsCantidad() {
	var mensaje = "";
	var itemCantidad = '';
	$("._item").each( function( index ) {
		var iid = $(this).prop('id');
		var code = $(this).find("#codigo").html();
		//alert(code);
		var cant = $("#" + iid + "-cantidad").val();
		var prec = $("#" + iid + "-precio").val();
		var desc = $("#" + iid + "-descripcion").val();
		var acce = $("#" + iid + "-esAccesorio").prop("checked") ? '1' : '0';
		if( code == '000000' ) {
			prec = prec.replace(',', '');
			if (null == prec || prec == '' || parseFloat(prec) == 0) {
				mensaje += "Articulo #" + (index + 1) + " de la lista SIN PRECIO.<br />";
			}
			if (null == desc || desc == '') {
				mensaje += "Articulo #" + (index + 1) + " de la lista SIN DESCRIPCION.<br />";
			}
			
			if (acce == 1 || acce == '1') {
				itemCantidad += iid + "||" + desc.replace('-', '_') + "||" + prec + "||" + cant + "||" + acce + ",";
			} else {
				itemCantidad += iid + "||" + desc.replace('-', '_') + "||" + prec + "||" + cant + ",";
			}
		} else {
			
			itemCantidad += iid + "||" + cant + ",";
		}
	});
	itemCantidad = itemCantidad.substring(0, itemCantidad.length - 1);
	if (mensaje != "") {
		//mensaje += "<br /><div class='tipAlert'>" + tipAlertNota + "</div>";
		showMessageDiv(mensaje, "alert", "nota", 15000);
		return false;
	} else {
		$("#itecan").val(itemCantidad);
		return true;
	}
	//return itemCantidad;
}

/*
 * Guarda la nota de venta acutal
 */
function guardarNota(estatus) {
	//var nota = $( "#form-nota" ).validate();
	estatus = estatus || 1;
	var itemCantidad = '';
	var aplicarIVA = $('#_iva').prop('checked') ? 'true' : 'false';
	if (obtenerItemsCantidad()) {
		//itemCantidad = obtenerItemsCantidad();
		itemCantidad = $("#itecan").val();
		var _request = $.post( './phpscripts/guardar-nota.php', { 
			data: { json: JSON.stringify({
				numero: 		$('#numnota').val(),
				folio: 			$('#folio').val(),
	            idCliente: 		$('#numcli').val(),
	            itemCantidad: 	itemCantidad,
	            aplicarIva: 	aplicarIVA
	        })}
	    });
		_request.done( function( msg ) {
			var data = msg.split('||');
			if (data[0] == 1 || data[0] == '1') {
				//showMessage(data[1], 'Nota Guardada');
				sinCambios('cambios');
				showMessageDiv(data[1], 'success', 'nota');
				cambiarStatus( "_nota", "Guardada", "guardada");
				var _req = $.post( './phpscripts/getsessionvaluesbypost.php', { 
					variable: 'folnot'
				});
				_req.done( function( msg ) {
					$("#numnota").val( msg );
					$('#statusNota').val(estatus);
					$('#_numnota_fol').html(printNumNota());
					//setSessionValue('post', 'status', '1');
					mostrarOcultarBotonesNota();
				});
			} else {
				showMessageDiv(data[1], 'error', 'nota');
			}
		});
		_request.fail( function( jqXHR, textStatus ) {
			showMessageDiv(data[1], 'error', 'nota');
		});
	}
}

/*
 * Limpia los datos de la nota para crear una nueva
 * Cambia los botones a mostrar
 */
function notaNueva() {
	//Borrar valores de las siguientes variables:
	//numcli=, folnot=, itecan=, status = -1
	//_iva marcar (checked='checked')
	loadSection('nota.php');
	$("#_iva").prop( 'checked', 'true' );
	$("#numcli").val('');
	$("#numnota").val('');
	$("#folio").val('');
	$("#itecan").val('');
	$("#statusNota").val(-1);
	//$("#_stat_nota").html( 'Nueva' );
	cambiarStatus("_nota", "Nueva", "nueva")
	$("#numclic").val( 0 );
	//buscarCliente();
	//limpiarNota();
	//$("#mainLog").hide();
	mostrarOcultarBotonesNota();
}

/*
 * Genera una cadena conl a informacion del numero de nota
 * Puede ser:
 * - nota vacia(xxx)
 * - nota de num de orden(xxx de orden xxx)
 * Ej. Nota 0006 de Orden 0010
 */
function printNumNota() {
	var notation = "Nota ";
	var numnota = $("#numnota").val();
	var folio = $("#folio").val();
	notation += (numnota != null && numnota != '' ? fixFolSize(numnota) : '- - - - - -');
	notation += folio != null && folio != '' ?  " de Orden " + fixFolSize(folio) : "";
	return notation;
}

/*
 * Muestra/oculta botones de la nota de venta en base al estatus actual de la misma
 */
function mostrarOcultarBotonesNota() {
	var estatus = parseInt( $("#statusNota").val() );
	var estaVacia = notaVacia();
	//alert( estatus );
	//alert( estaVacia );
	/*
	 * CLAVE	STATUS						=>	ACCIONES POSIBLES
	 *________________________________________________________________________________________
	 *
	 *  0		NUEVA						=>	GUARDA, LIMPIAR (SI TIENE ITEMS)
	 *  1		GUARDADA					=>	TERMINAR, GUARDAR( LIMPIAR SI TIENE ITEMS )
	 *  2		IMPRESA(Y COBRADA) 			=>	NUEVA, VER TICKET, IMPRIMIR TICKET
	 *  3		COBRADA						=>  NUEVA, VER TICKET, IMPRIMIR TICKET
	 *  4		CANCELADA					=>  NUEVA
	 *  
	 *________________________________________________________________________________________
	 */
	$("#_bot_can").hide();
	$("#_bot_gn").hide();
	$("#_bot_nn").hide();
	$("#_bot_ln").hide();
	$("#_bot_in").hide();
	$("#_bot_vn").hide();
	$("#_bot_cn").hide();
	if(estatus > 0 && estatus < 4) {
		$("#_bot_can").show();
	}
	switch( estatus ) {
		case 0:
			if( !estaVacia ) {
				$("#_bot_gn").show();
				$("#_bot_ln").show();
			}
			break;
		case 1:
			if( !estaVacia ) {
				$("#_bot_ln").show();
				$("#_bot_cn").show();
				$("#_bot_gn").show();
			}
			break;
		case 2:
			$("#_bot_in").show();
			$("#_bot_vn").show();
			$("#_bot_nn").show();
			//$("#_bot_gn").show();
			break;
		case 3:
			$("#_bot_vn").show();
			$("#_bot_in").show();
			$("#_bot_nn").show();
			break;
		case 4:
			var folio = $("#folio").val();
			var numNota = $("#numnota").val();
			var numCli = $("#numcli").val();
			//$("#_bot_nn").show();
			//showMessageDiv("Esta nota ah sido cancelada.<br />Puede crear una nueva desde:<br /> - La Orden de Servicio(Si pertenece a una)<br /> - Desde el menu Nota de Venta->Nueva nota(Si es nota sin orden)", "error", "nota", 60000);
			showMessageDiv("La nota " + numNota + (folio != '' ? " de la orden no: " + folio : "") +
					" ha sido cancelada.<br />Para crear una nueva nota" +
					(folio != '' ? " para la misma orden" : "") +
					" haz clic <span class='status status-small status-nueva'  style='cursor: pointer;' onclick=\"" +
					(folio != '' ? "loadSection('nota.php?numcli=" + numCli + "&folio=" + folio + "')" : "notaNueva()") +
					"\"> aqui.</span>",
					"error", "nota", 36000);
			break;
	}
}

/*
 * Decide si la nota esta vacia o no
 */
function notaVacia() {
	return ( $("._item").length > 0 ? false: true );
}


/*##############################################################################

FINALIZA - NOTA

################################################################################*/

/*##############################################################################

COMIENZA - VENTAS

################################################################################*/
function reloadVentas(txtDate) {
	//alert("phpscripts/v-listado.php?txtDate=" + txtDate);
	showLoading();
	loadList("divTablaVentas", "phpscripts/v-listado.php?txtDate=" + txtDate);
}

function reloadVtasPeriod() {
	var data;
	data = $("#date1").val().split('-');
	$("#date1").val(data[2] + '-' + data[1] + '-' + data[0]);
	data = $("#date2").val().split('-');
	$("#date2").val(data[2] + '-' + data[1] + '-' + data[0]);
	showLoading();
	loadList("divTablaVentas", "phpscripts/vp-listado.php?txtDate1=" + $("#date1").val() + "&txtDate2=" + $("#date2").val());
}
/*##############################################################################

FINALIZA - VENTAS

################################################################################*/

/*##############################################################################

COMIENZA - ARTICULO

################################################################################*/
function guardarArticulo() {
	jQuery.validator.addMethod( "alpha_numeric", function( value, element) {
		return this.optional(element) || /^([a-z0-9])+$/i.test( value );
	}, "solo letras y numeros");
	var validator = $( "#datos-articulo" ).validate({
		rules: {
			codigo: 		{ required: true, alpha_numeric: true, rangelength: [5, 15] },
			corta: 			{ required: true, rangelength: [10, 256] },
			larga:		 	{ required: true, minlength: 10 },
			cantidad: 		{ required: true, digits: true },
		    precio: 		{ required: true, number: true }
	  }
	});
	if( validator.form() ) {
		var _request = $.post( './phpscripts/guardar-articulo.php', { 
			data: { json: JSON.stringify({
				clave:	 			$('#clave').val(),
				codigo: 			$('#codigo').val(),
	            corta: 				utf8_encode($('#corta').val()),
	            larga:				utf8_encode($('#larga').val()),
	            cantidad:			$('#cantidad').val(),
	            precio:				$('#precio').val().replace(',', ''),
	            categoria:			$('#categoria').val(),
	            activo:				$('#activo').prop("checked") == '1' ? '1' : '0'
	        })}
	    });
		_request.done( function( msg ) {
			var data = msg.split("||");
			if (data[0] == 1) {
				$("#clave").val(data[2]);
				//showMessage(data[1], 'Articulo guardado');
				showMessageDiv(data[1], 'success', 'articulo');
				//mostrarOcultarBotonesArticulo();
			} else {
				showMessage(data[1], 'Error');
			}
		});
		_request.fail( function( jqXHR, textStatus ) {
			showMessage(data[1], 'Error');
		});
	} else {
		//showMessage(data[1], 'Error');
	}
}

function checkArticulo() {
	var _cadena = $("#codigo").val();
	if(_cadena != null && _cadena != '') {
		$.getJSON( "search.php", 
		{ tipo: 'articulo', cadena: _cadena },
		function( data ){
			if(data[0].exist == 1) {
				showMessage(checkCodigoError, "Atencion!");
				$("#codigo").val('');
			} else {
				$("#codigo").prop("disabled", true);
				$("#alertMes").fadeOut('slow', function(){ $("#successMes").fadeIn('slow', function(){setTimeout(function(){$("#successMes").fadeOut();}, 5000);}); continuarCapArt(); });
			}
		} );
	}
}
function reloadArticulos(pag) {
	var numero = $("#numConfig").val();
	var tipo = $("#esGeneral").val();
	var busqueda = tipo == 'true' ? '-1' : encodeURI($("#busqueda").val());
	var orden = encodeURI($("#order").val());
	showLoading();
	loadList("divTablaArticulos", "phpscripts/a-listado.php?" + "&pagina=" + pag + "&busqueda=" + busqueda + "&rpp=" + numero + "&order=" + orden);
}

/*
 * Busca una orden de servicio por numero de orden
 */
function buscarArticulo(idSeccion, ventana) {
	//alert($('#numorden').val());
	var section = (idSeccion == '' ? 'cue' : idSeccion);
	var num_ = $('#codigo').val();
	showLoading();
	$("#" + section).fadeOut('slow', //{direction: 'left'}, 250, 
			function(){
		var _req = $.get( "articulo-search.php?codigo=" + num_, { 
		});
		$('#codigo').val('');
		_req.done( function( _html ) {
			$("#" + section).html( _html );
		});
		_req.fail( function( msg ) {
			$("#" + section).html( "Error:" + msg );
		});
		_req.always( function( msg ) {
			$("#" + section).fadeIn('slow');
			hideLoading();
		});
	});
}
/*##############################################################################

FINALIZA - ARTICULO

################################################################################*/
function limpiarArticulo() {
	//$("#clave").val('');
	//$("#codigo").val('');
	$("#categoria").val('7');
	$("#corta").val('');
	$("#larga").val('');
	$("#precio").val('');
	$("#cantidad").val('');
	$("#activo").prop('checked', 'checked');
} 
function articuloNuevo() {
	$("#clave").val('');
	$("#codigo").val('');
	loadSection('datos-articulo.php');
}
function cargarImgs() {
	window.location = "./upload.php";
}
function guardarYCargarImgs() {
	$('#cargar').val('true');
	guardar();
}

function vaciarAntesDeValidar( e ) {
	if( $(e).attr("class").indexOf('vacio') >= 0  ) {
		$(e).val('');
	}
}
function checkIfEmpty( _camp_id ) {
	var _campo = $( "#" + _camp_id );
	if( _campo ) {
		if( null == _campo.val() || _campo.val() == '' ) {
			//alert( _campo.prop('type') );
			_campo.attr( 'class', _campo.prop('type') == 'text' ? 'inp-vacio' : 'txta-vacio');
			_campo.val( _campo.attr('title') );
		}
	}
}
function checkIfVirgin( _camp_id ) {
	var _campo = $( "#" + _camp_id );
	if( _campo ) {
		if( null == _campo.val() || _campo.val() == _campo.attr('title') ) {
			//alert( _campo.prop('type') );
			_campo.val( '' );
			_campo.attr( 'class', _campo.prop('type') == 'text' ? 'inp' : 'txta');
		}
	}
}
function mostrarError() {
	$("#error").show();
}
function ocultarError() {
	$("#error").hide();
}


