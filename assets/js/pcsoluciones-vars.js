//DIV para hacer funcionar barras de busqueda auto-complete cuando hay mas de dos
//en una sola pantalla
var lastDivUsed = 'result';
//Para el control de pantalla de bloqueo(inicio de sesion)
var unlocked = false;

//Etiqueta personalizada para los items de nota
//var txtLabelItemNota = '<img src="archivos/imagenes/sitio/accesorio.png" style="vertical-align: middle; cursor: pointer;" />';
var txtLabelItemNota = '';
//Minutos en los que expira la sesion
var minutes = 10;
//var minutes = 3000;
//Timer para controlar el bloqueo automatico
var myTimer = null;
//Mensaje a mostrar cuando el codigo de articulo ya exista
var checkCodigoError = "El codigo ya existe, elija otro e intente de nuevo.";

var tipAlertNota = "Captura todas las descripciones / precios &oacute; elimina un articulo con el bot&oacute;n \'quitar'\ de la derecha.<br />Recuerda siempre GUARDAR los CAMBIOS antes de cobrar.";

var jsArrayNombresMeses = ["Enero", "Febrero", "Marzo", "Abril",
			                   "Mayo", "Junio", "Julio", "Agosto", "Septiembre",
			                   "Octubre", "Noviembre", "Diciembre"];