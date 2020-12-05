<?php defined("PHRAPI") or die("Direct access not allowed!");

$config = array (
	'gmt' => '-05:00',
	'locale' => 'es_MX',
	'php_locale' => 'es_MX',
	'timezone' => 'America/Mexico_City',
	'offline' => false,
	//KORE-VARS
	/*'baseDatos' => 'pcsoluciones',
	'customPath' => 'kore-custom',
	'pathScriptOrden' => 'phpscripts/pdf/',
	'pathScriptTicket' => 'phpscripts/',
	'TheSystem' => 1,
	'systemName' => '',
	'titleName' => '',
	'imgHeader' => 'header',
	'scriptOrden' => 'generar-orden-',
	'scriptTicket' => 'print-ticket-',*/
	'website' => 1,
	'wallpapers_path' => 'assets/images/wallpapers',
	'user_image_path' => 'users/images',
	'system_name' => 'PC-Solucioness',
	'servers' => array(
		'sistema.pcsoluciones.com.mx' => array(
			//'url' => 'https://sistema.pcsoluciones.com.mx/',
			'url' => 'https://sistema.pcsoluciones.com.mx/',
			//'url' => 'http://localhost/edsa-pm/',
			'db' => array(
				array(
					'host' => 'localhost',
					'name' => 'pcsoluc1_sistema_pcsoluciones',
					'user' => 'pcsoluc1_websis',
					'pass' => 'P9Y6$ic_7Fr'
				)
			)
		),
		/*'127.0.0.1' => array(
			//'url' => 'http://127.0.0.1/edsa-pm/',
			//'url' => 'http://127.0.0.1/projects/project-manager/',
			'url' => 'http://127.0.0.1/sistema.pcsoluciones/',
			'db' => array(
				array(
					'host' => 'localhost',
					'name' => 'pcsoluciones_develop',
					'user' => 'root',
					'pass' => ''
				)
			)
		),
		'192.168.100.7' => array(
			'url' => 'http://192.168.100.7/projects/project-manager/',
			'db' => array(
				array(
					'host' => 'localhost',
					'name' => 'pcsoluciones',
					'user' => 'root',
					'pass' => ''
				)
			)
		),*/
	),
	'smtp' => array(
		'host' => '',
		'pass' => '',
		'from' => array(
			'Contacto' => ''
		)
	),
	'routing' => array(
		'access/login' => array('controller' => 'Access', 'action' => 'login'),
		'access/logout' => array('controller' => 'Access', 'action' => 'logout'),
		'access/saveUserConfig' => array('controller' => 'Access', 'action' => 'saveUserConfig'),
		'search/cliente' => array('controller' => 'Pcsoluciones', 'action' => 'typeahead'),
		'search/articulo' => array('controller' => 'Pcsoluciones', 'action' => 'typeaheada'),
		'save/cliente' => array('controller' => 'Pcsoluciones', 'action' => 'guardarCliente'),
		'save/orden' => array('controller' => 'Pcsoluciones', 'action' => 'guardarOrden'),
		'save/articulo' => array('controller' => 'Pcsoluciones', 'action' => 'guardarArticulo'),
		'save/nota' => array('controller' => 'Pcsoluciones', 'action' => 'guardarNota'),
		'cobrar/nota' => array('controller' => 'Pcsoluciones', 'action' => 'cobrarNota'),
		'imprimir/nota' => array('controller' => 'Pcsoluciones', 'action' => 'printTicket'),
		'mostrar/ticket' => array('controller' => 'Pcsoluciones', 'action' => 'showTicket'),
		'cancelar/nota' => array('controller' => 'Pcsoluciones', 'action' => 'cancelarNota'),
		'terminar/orden' => array('controller' => 'Pcsoluciones', 'action' => 'terminarOrden'),
		'validar/articulo' => array('controller' => 'Pcsoluciones', 'action' => 'validarCodigo'),
		'activar/articulo' => array('controller' => 'Pcsoluciones', 'action' => 'activarArticulo'),
		'stock/articulo' => array('controller' => 'Pcsoluciones', 'action' => 'stockArticulo'),
		'precio/articulo' => array('controller' => 'Pcsoluciones', 'action' => 'precioArticulo'),
		'load/cliente' => array('controller' => 'Pcsoluciones', 'action' => 'cargarCliente'),
		'ordenpdf' => array('controller' => 'Pcsoluciones', 'action' => 'ordenPdf'),
		'log' => array('controller' => 'Pcsoluciones', 'action' => 'agregarLog')
	)
);
