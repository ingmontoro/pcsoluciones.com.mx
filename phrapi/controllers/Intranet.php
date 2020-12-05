<?php defined('PHRAPI') or die("Direct access not allowed!");

class Intranet {

	private $db;
	private $config;
	private $session;
	protected $info = array();
	public $projects;
	public $has_projects;
	public $has_investments;
	public $divisa_prefijo = "";
	public $divisa_sufijo = " €";
	public $paginador;
	public $usuarios;
	public $valores_filtros;
	
	protected $estatus = array(
		'' => 'Esperando Pago',
		'Failure' => 'Sin Pago',
		'Success' => 'Pagado',
		'Process' => 'En proceso',
		'Canceled' => 'Cancelada',
	);

	public function __construct(){
		$this->config = $GLOBALS['config'];
		$this->db = DB::getInstance();
		$this->session = Session::getInstance();
		$this->persistent = Persistent::getInstance();
		$this->lang = $this->persistent->lang;

		if (!isset($this->session->logged) OR !$this->session->logged) {
			redirect("login.php");
		}
	}
	
	public function getAlias() {
		return isset($this->session->alias) ? $this->session->alias : "- - - - ";
	}
	
	public function loadList() {
		
		$pagina = getValueFrom($_GET, 'pagina', 1, FILTER_SANITIZE_PHRAPI_INT);
		$rpp = getValueFrom($_GET, 'rpp', 15, FILTER_SANITIZE_PHRAPI_INT);
		$nombre = getValueFrom($_GET, 'nombre', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$filters = getValueFrom($_GET, 'filters', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$values = getValueFrom($_GET, 'values', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		
		$tamano = $rpp;
		$_where = "";
		$oEst = "0";
		$oRec = "0";
		$oEnt = "0";
		$nombre = "";
		$rinicial = $tamano * ($pagina - 1);
		if($nombre) {
			$_where = "WHERE CONCAT(c.nombre, ' ', c.apellido1, CASE WHEN c.apellido2 IS NOT NULL THEN CONCAT(' ', c.apellido2) ELSE '' END) LIKE CONCAT('%', '" . urldecode($_GET["nombre"]) . "', '%') OR c.nombre_fiscal LIKE CONCAT('%', '" . urldecode($_GET["nombre"]) . "', '%') ";
		}
		$this->valores_filtros = new stdClass();
		$this->valores_filtros->filtroEs = 0;
		$this->valores_filtros->filtroRe = 0;
		$this->valores_filtros->filtroEn = 0;
		if($filters && $values) {
			
			$fa = explode(",", substr($filters, 0, strlen($filters) - 1));
			$va = explode(",", substr($values, 0, strlen($values) - 1));
			$_where = "";
			$i = 0;
			$last = count($fa) - 1;
			foreach ($fa as $f) {
				if ($va[$i] != 0) {
					$_where .= ($_where === '' ? '' : ' AND ');
					$_where .= "$f = $va[$i]";
					switch ($f) {
						case "o.estatus":
						$oEst = $va[$i];
						$this->valores_filtros->filtroEs = $va[$i];
						break;	
						
						case "o.idRecibio":
						$oRec = $va[$i];
						$this->valores_filtros->filtroRe = $va[$i];
						break;
						
						case "o.idEntrego":
						$oEnt = $va[$i];
						$this->valores_filtros->filtroEn = $va[$i];
						break;
					}
				}
				$i ++;
			}
			$_where = $_where === '' ? '' : ' WHERE ' . $_where;
		}
		
		$this->ordenes = $this->db->queryAll("SELECT DISTINCT o.numero, o.descripcion, oe.nombre as estatus, n.numero idNota, n.estatus estatusNota, o.estatus estatusOrden,
				
				(SELECT COUNT(lo.id) FROM log_orden lo WHERE lo.idOrden = o.numero AND lo.activo = 1) notas,
				CONCAT(o.nombre, ' ', o.apellido1, CASE WHEN o.apellido2 IS NOT NULL THEN CONCAT(' ', o.apellido2) ELSE '' END) AS nombreC,
				o.nombre_fiscal,
				p.alias AS nombreR, p1.alias AS nombreE,
				date_format(o.fecha, '%d %b %Y<br />%h:%i %p') AS fecha1,
				if(o.fecha >= curdate() AND o.fecha < date_add(curdate(), INTERVAL 1 DAY), date_format(o.fecha, 'Hoy<br />%h:%i %p'),
					if (o.fecha >= date_sub(curdate(), INTERVAL 1 DAY) AND o.fecha < curdate(), date_format(o.fecha, 'Ayer<br />%h:%i %p'),
						if (o.fecha >= date_sub(curdate(), INTERVAL 2 DAY) AND o.fecha < date_sub(curdate(), INTERVAL 1 DAY), date_format(o.fecha, 'Antier<br />%h:%i %p'),
							date_format(o.fecha, '%d %b %Y<br />%h:%i %p')))) as fecha,
				CASE WHEN o.idEntrego IS NOT NULL THEN 
				if(o.fechaT >= curdate() AND o.fechaT < date_add(curdate(), INTERVAL 1 DAY), date_format(o.fechaT, 'Hoy<br />%h:%i %p'),
					if (o.fechaT >= date_sub(curdate(), INTERVAL 1 DAY) AND o.fechaT < curdate(), date_format(o.fechaT, 'Ayer<br />%h:%i %p'),
						if (o.fechaT >= date_sub(curdate(), INTERVAL 2 DAY) AND o.fechaT < date_sub(curdate(), INTERVAL 1 DAY), date_format(o.fechaT, 'Antier<br />%h:%i %p'),
							date_format(o.fechaT, '%d %b %Y<br />%h:%i %p')))) 
				ELSE '' END AS fechaT,
				p2.alias as nombreA
			FROM (SELECT * FROM orden_servicio o JOIN cliente c ON o.idCliente = c.id $_where ORDER BY o.fecha DESC, o.fechaT DESC, o.numero LIMIT $rinicial, $tamano) o 
				LEFT JOIN usuario p ON o.idRecibio = p.idpersona
				LEFT JOIN usuario p1 ON o.idEntrego = p1.idpersona
				LEFT JOIN usuario p2 ON o.idAsignado = p2.idPersona
				LEFT JOIN nota n ON o.numero = n.folio AND n.estatus != 4
				LEFT JOIN orden_estatus oe ON o.estatus = oe.id ORDER BY o.fecha DESC, o.fechaT DESC, o.numero");
		
		$this->paginador = new Paginador();
		if(sizeof($this->ordenes) > 0) {
			$this->paginador->valido = true;
			$this->paginador->pagina = $pagina;
			$this->paginador->where = $_where;
			$this->paginador->tamano = $tamano;
			$this->paginador->calcularDatos("ordenes");
		}
		return $this->ordenes;
	}
	
	public function loadHistorialOrden() {
	
		$numord = getValueFrom($_GET, 'numord', '', FILTER_SANITIZE_PHRAPI_MYSQL);
	
		if($numord > 0) {
			$this->logs = $this->db->queryAll(
			"SELECT l.id, l.activo, l.log, l.idEstatus, l.idAsignado,
			if(l.fecha >= curdate() AND l.fecha < date_add(curdate(), INTERVAL 1 DAY), date_format(l.fecha, 'Hoy<br />%h:%i %p'),
					if (l.fecha >= date_sub(curdate(), INTERVAL 1 DAY) AND l.fecha < curdate(), date_format(l.fecha, 'Ayer<br />%h:%i %p'),
						if (l.fecha >= date_sub(curdate(), INTERVAL 2 DAY) AND l.fecha < date_sub(curdate(), INTERVAL 1 DAY), date_format(l.fecha, 'Antier<br />%h:%i %p'),
							date_format(l.fecha, '%d %b %Y<br />%h:%i %p')))) as fecha,
			if(l.fechaCambio >= curdate() AND l.fechaCambio < date_add(curdate(), INTERVAL 1 DAY), date_format(l.fechaCambio, 'Hoy<br />%h:%i %p'),
					if (l.fechaCambio >= date_sub(curdate(), INTERVAL 1 DAY) AND l.fechaCambio < curdate(), date_format(l.fechaCambio, 'Ayer<br />%h:%i %p'),
						if (l.fechaCambio >= date_sub(curdate(), INTERVAL 2 DAY) AND l.fechaCambio < date_sub(curdate(), INTERVAL 1 DAY), date_format(l.fechaCambio, 'Antier<br />%h:%i %p'),
							date_format(l.fechaCambio, '%d %b %Y<br />%h:%i %p')))) as fechaM,
			if(l.fechaTermino >= curdate() AND l.fechaTermino < date_add(curdate(), INTERVAL 1 DAY), date_format(l.fechaTermino, 'Hoy<br />%h:%i %p'),
					if (l.fechaTermino >= date_sub(curdate(), INTERVAL 1 DAY) AND l.fechaTermino < curdate(), date_format(l.fechaTermino, 'Ayer<br />%h:%i %p'),
						if (l.fechaTermino >= date_sub(curdate(), INTERVAL 2 DAY) AND l.fechaTermino < date_sub(curdate(), INTERVAL 1 DAY), date_format(l.fechaTermino, 'Antier<br />%h:%i %p'),
							date_format(l.fechaTermino, '%d %b %Y<br />%h:%i %p')))) as fechaT,
			u.alias AS creado, u2.alias AS modificado, u3.alias AS asignado
			FROM log_orden l
			JOIN usuario u on l.idRealizo = u.id
			LEFT JOIN usuario u2 ON l.idCambio = u2.id
			LEFT JOIN usuario u3 on l.idAsignado = u3.id
			WHERE l.idOrden = " . $numord . " AND l.activo = 1 ORDER BY l.fecha DESC, l.fechaCambio DESC");
			if(count($this->logs > 0)) {
				$this->usuarios = $this->db->queryAll(
					"SELECT u.id, u.login, u.alias, concat( p.nombre, ' ', p.apellido1, ' ', p.apellido2 ) as nombre
					FROM usuario u, persona p where u.idpersona = p.id and (u.activo = 1 and u.interno = 1 OR u.id = -1)");
			}
		}
	
		return $this->logs;
	}
	
	public function ordenesFiltro($tipo = "estatus") {
		$datos = [];
		switch($tipo) {
			case "estatus" :
				$datos = $this->db->queryAll("SELECT DISTINCT oe.id as id, oe.nombre as label FROM orden_estatus oe JOIN orden_servicio o ON o.estatus = oe.id");
				break;
			case "recibio" :
				$datos = $this->db->queryAll("SELECT DISTINCT p.id, p.alias as label FROM usuario p JOIN orden_servicio o ON o.idRecibio = p.id");
				break;
			case "entrego" :
				$datos = $this->db->queryAll("SELECT DISTINCT p.id, p.alias as label FROM usuario p JOIN orden_servicio o ON o.idEntrego = p.id");
				break;
		}
		return $datos;
	}
	
	public function validPremium() {
		if(!$this->session->has_premium || (strtotime(null) > strtotime($this->session->premium_date))) {
			redirect("intranet.php");
		}
	}

	public function profile() {
		$id = $this->session->logged;

		$profile = $this->db->queryRow("
			SELECT
				*,
				'' as pass
			FROM usuarios
			WHERE id_usuario = :id
		", array(
			':id' => $id
		));

		if (empty($profile->foto)) {
			$profile->foto = "//placehold.it/150x150&text=" . $GLOBALS['factory']->Lang->get('es:Foto','en:Photo');
		}

		return $profile;
	}

	public function bio() {
			
		return $this->db->queryRow("
			SELECT u.login, u.alias, u.super, CONCAT(p.nombre, ' ', p.apellido1, ' ', p.apellido2) as nombre 
			FROM usuario u, persona p
			WHERE u.id = :id AND u.idpersona = p.id and u.activo = 1", array(':id' => $this->session->logged));
	}

	public function avatarUpdate() {
		$data = getValueFrom($_POST, 'data');

		if (empty($data)) {
			return 400;
		}

		$id = $this->session->logged;

		$stmt = $this->db->prepare("UPDATE usuarios SET foto = :foto WHERE id_usuario = :id");
		$stmt->bindParam(':id', $id);
		$stmt->bindParam(':foto', $data, PDO::PARAM_LOB);
		$stmt->execute();

		return 200;
	}

	public function updateProfile() {
		$data = getHash($_POST, array(
			"nombre" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"apellidos" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"telefono" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"skype" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"direccion" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"ciudad" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"pais" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"dni" => FILTER_SANITIZE_PHRAPI_MYSQL
		));

		$this->db->query("
			UPDATE
				usuarios
			SET
				nombre = IF(LENGTH(TRIM(:nombre)) > 0, :nombre, nombre),
				apellidos = IF(LENGTH(TRIM(:apellidos)) > 0, :apellidos, apellidos),
				telefono = IF(LENGTH(TRIM(:telefono)) > 0, :telefono, telefono),
				skype = IF(LENGTH(TRIM(:skype)) > 0, :skype, skype),
				direccion = IF(LENGTH(TRIM(:direccion)) > 0, :direccion, direccion),
				ciudad = IF(LENGTH(TRIM(:ciudad)) > 0, :ciudad, ciudad),
				pais = IF(LENGTH(TRIM(:pais)) > 0, :pais, pais),
				dni = IF(LENGTH(TRIM(:dni)) > 0, :dni, dni)
			WHERE
				id_usuario = :id
		", array(
			":id" => $this->session->logged,
			":nombre" => $data["nombre"],
			":apellidos" => $data["apellidos"],
			":telefono" => $data["telefono"],
			":skype" => $data["skype"],
			":direccion" => $data["direccion"],
			":ciudad" => $data["ciudad"],
			":pais" => $data["pais"],
			":dni" => $data["dni"],
		));

		return 200;
	}
	
	public function addProject() {
		
		$data = getHash($_POST, array(
			"nombre" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"socios" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"descripcion" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"nicho" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"destinatarios" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"ingresos" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"inversion" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"protegido" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"marca" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"terceros" => FILTER_SANITIZE_PHRAPI_MYSQL,
		));
	
		if($this->session->logged) {
			$this->db->query("
			INSERT INTO
				proyectos
			SET
				id_usuario = :id_usuario,
				nombre = :nombre,
				socios = :socios,
				descripcion = :descripcion,
				nicho = :nicho,
				destinatarios = :destinatarios,
				ingresos = :ingresos,
				inversion = :inversion,
				protegido = :protegido,
				marca = :marca,
				terceros = :terceros,
				desde = NOW(),
				estatus = 'Inactivo'
			", array(
				":id_usuario" => $this->session->logged,
				":nombre" => $data['nombre'],
				":socios" => $data['socios'],
				":descripcion" => $data['descripcion'],
				":nicho" => $data['nicho'],
				":destinatarios" => $data['destinatarios'],
				":ingresos" => $data['ingresos'],
				":inversion" => $data['inversion'],
				":protegido" => $data['protegido'],
				":marca" => $data['marca'],
				":terceros" => $data['terceros'],
			));
			
			$id_proyecto = $this->db->getLastID();
			
			if($id_proyecto) {
				
				$ud = $this->db->queryRow("
				SELECT
					nombre, apellidos, email
				FROM
					usuarios
				WHERE
					id_usuario = :id
				", array(
					':id' => $this->session->logged
				));
				
				$articulo = $this->db->queryRow("
					SELECT
					getPostContent(id_post_title, '{$this->lang}') as title,
					getPostContent(id_post_content, '{$this->lang}') as content
					FROM
					articles
					WHERE
					alias = 'email-nuevo-correo'
				");
				
				$bc = $this->db->queryRow("
					SELECT
						(SELECT value FROM configs WHERE name = 'contact_name') as nombre,
						(SELECT value FROM configs WHERE name = 'contact_email') as email
				");
				
				/*$mail = Mail::makeEmail(array(
					'to' => array("{$ud->nombre} {$ud->apellidos}" => $ud->email),
					'bc' => array($bc->nombre => $bc->email),
					'reply' => array('Propuestas Design & Law' => 'propuestas@designandlaw.com'),
					'subject' => 'Propuesta de Proyecto',
					'body' => String::formated($articulo->content, array(
						':USUARIO' => $ud->nombre,
						':PROYECTO' => $data['nombre']
					), PHRAPI_STRING_FORMATED_UPPER)
				));*/
				
				return $id_proyecto;
			}
		}
		
		return -1;
	}

	public function updatePass() {
		$data = getHash($_POST, array(
			"contrasena" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"confirmar" => FILTER_SANITIZE_PHRAPI_MYSQL,
		));

		if (empty($data['contrasena']) OR strlen($data['contrasena']) < 6 OR $data['contrasena'] != $data['confirmar']) {
			return 400;
		}

		$this->db->query("
			UPDATE
				usuarios
			SET
				pass = MD5(CONCAT(email, desde, :pass))
			WHERE
				id_usuario = :id
		", array(
			":id" => $this->session->logged,
			":pass" => $data["contrasena"],
		));

		return 200;
	}
	
	public function notifications() {
		/*SELECT
		 if(s.id_pre_soporte is null, s.id_soporte, s.id_pre_soporte) as id,
		 s.id_soporte as id_soporte,
		 if(s.id_pre_soporte is null, s.asunto,
		 (SELECT s1.asunto FROM soporte s1 WHERE s1.id_soporte = s.id_pre_soporte)) asunto,
		 s.comentarios,
		 if(s.id_pre_soporte is null, 'ticket', 'mensaje') tipo,
		 if(s.id_pre_soporte is null, 'glyphicon glyphicon-envelope pull-right', 'glyphicon glyphicon-comment pull-right') icon,
		 s.creado
		 FROM
		 soporte s
		 WHERE
		 s.visto_u = 0
		 ORDER BY s.creado*/
		$this->notifications = $this->db->queryAll("
			
				SELECT
					if(s.id_pre_soporte is null, s.id_soporte, s.id_pre_soporte) as id,
					s.id_soporte as id_soporte,
					if(s.id_pre_soporte is null, s.asunto, 
						(SELECT s1.asunto FROM soporte s1 WHERE s1.id_soporte = s.id_pre_soporte AND s1.id_usuario = s.id_usuario)) asunto,
					s.comentarios,
				    if(s.id_pre_soporte is null, 'glyphicon glyphicon-envelope', 'glyphicon glyphicon-comment') icon,
				    if(s.id_pre_soporte is null, 'ticket', 'mensaje') tipo,
				    s.creado
				FROM 
					soporte s
				WHERE
					s.visto_u = 0 AND (s.id_usuario = :idUsuario OR s.id_pre_soporte IN(SELECT s2.id_soporte FROM soporte s2 WHERE s2.id_usuario = :idUsuario))
				UNION
				SELECT 	a.id_soporte as id, a.id_adjunto as id_soporte, '' as asunto, CONCAT('Nuevo documento recibido (', a.nombre, ')') as comentarios,
						'glyphicon glyphicon-paperclip' as icon, 'adjunto' as tipo, a.creado
				FROM adjuntos_soporte a
				WHERE a.visto_u = 0 AND a.id_soporte IN(SELECT s2.id_soporte FROM soporte s2 WHERE s2.id_usuario = :idUsuario)
				UNION
				SELECT 	ps.id_soporte as id, CONCAT(ps.id_pago, '-s')as id_soporte, '' as asunto, CONCAT('servicio ENTREGADO por D&L: ', getPostContent(os.id_post_title, '{$this->lang}')) as comentarios,
						'glyphicon glyphicon-folder-closed' as icon, 'servicio' as tipo, ps.fecha_e
				FROM pagos_servicios ps JOIN otros_servicios os ON os.id_servicio = ps.id_servicio
				WHERE ps.estatus = 'Success' AND ps.entregado = 1 AND ps.visto_u = 0
				ORDER BY creado
		", array(
			":idUsuario" => $this->session->logged,
		));
		return $this->notifications;
	}
	
	public function setAsViewed() {
		$id = getValueFrom($_POST, 'id', 0, FILTER_SANITIZE_PHRAPI_INT);
		$tipo = getValueFrom($_POST, 'tipo', 0, FILTER_SANITIZE_PHRAPI_MYSQL);
		switch($tipo) {
			case "adjunto":
				$this->db->query("UPDATE adjuntos_soporte SET visto_u = 1 WHERE id_adjunto = '{$id}'");
				break;
			case "servicio":
				$this->db->query("UPDATE pagos_servicios SET visto_u = 1 WHERE id_pago = '{$id}'");
				break;
			default:
				$this->db->query("UPDATE soporte SET visto_u = 1 WHERE id_soporte = '{$id}'");
		}
		return 200;
	}
	
	public function updateNotiCounter() {
		$tipo = getValueFrom($_POST, 'tipo', 0, FILTER_SANITIZE_PHRAPI_MYSQL);
		switch($tipo) {
			case "asesoria":
				//select count(id_soporte) from soporte where visto_u = 0
				return (int)$this->db->queryOne("
					SELECT COUNT(r.id) FROM (select 
						id_soporte as id
					from soporte
					where visto_u = 0 AND (id_usuario = :idUsuario OR id_pre_soporte IN(SELECT s2.id_soporte FROM soporte s2 WHERE s2.id_usuario = :idUsuario))
					UNION
					SELECT 
						a.id_adjunto as id
					FROM adjuntos_soporte a
					WHERE a.visto_u = 0 AND a.id_soporte IN(SELECT s2.id_soporte FROM soporte s2 WHERE s2.id_usuario = :idUsuario)
					UNION
					SELECT 
						ps.id_pago as id
					FROM pagos_servicios ps
					WHERE ps.visto_u = 0 AND ps.id_usuario = :idUsuario AND ps.estatus = 'Success' AND ps.entregado = 1
					) r
				"
			, array(
				":idUsuario" => $this->session->logged,
			));
			default:return -1;
		}
	}

	public function projects() {
		$this->projects = $this->db->queryAll("
			SELECT
				p.*,
				p.id_proyecto as id,
				p.nombre as label,
				(p.protegido * 1) as protegido,
				(p.marca * 1) as marca,
				(p.terceros * 1) as terceros,
				(SELECT SUM(pi.monto) FROM proyectos_inversiones pi WHERE pi.id_proyecto = p.id_proyecto) as monto_inversiones,
				(SELECT COUNT(*) FROM proyectos_inversiones pi WHERE pi.id_proyecto = p.id_proyecto) as inversiones
			FROM
				proyectos p
			INNER JOIN
				usuarios u ON p.id_usuario = u.id_usuario
			WHERE
				u.id_usuario = '{$this->session->logged}'
			ORDER BY
				p.nombre
		");

		foreach ($this->projects as &$proyecto) {
			$actual = new DateTime();
			$proyecto->faltan = (int) $actual->diff(new DateTime($proyecto->fecha_objetivo))->format('%R%a');

			if ($proyecto->faltan < 0) {
				$proyecto->faltan = 0;
			}

			$proyecto->avance = 0;
			if ($proyecto->monto_objetivo > 0) {
				$proyecto->avance = $proyecto->monto_inversiones * 100 / $proyecto->monto_objetivo;
			}
			$proyecto->monto_inversiones = $this->cantidadFormateada($proyecto->monto_inversiones);
			$proyecto->monto_objetivo_plano = $proyecto->monto_objetivo;
			$proyecto->monto_objetivo = $this->cantidadFormateada($proyecto->monto_objetivo);
		}


		return $this->projects;
	}
	
	public function suscripciones() {
		
		$id = (int) getValueFrom($_GET, 'project', 0, FILTER_SANITIZE_PHRAPI_INT);
		$premium = (int)$this->db->queryOne("
			SELECT COUNT(id_contratacion) valido
			FROM
				contratacion_premium
			WHERE
				id_usuario = :id
			AND id_proyecto = :id_proyecto
			AND NOW() < fecha_hora_termino
		", array(
			':id' => $this->session->logged,
			':id_proyecto' => $id
		));
		if($premium == 0) {
			return $this->suscripciones = $this->db->queryAll("
				SELECT 	tp.id_tipo,
						getPostContent(tp.id_post_name, '{$this->lang}') as nombre,
						getPostContent(tp.id_post_desc, '{$this->lang}') as descripcion,
						getPostContent(tp.id_post_unit, '{$this->lang}') as unidad_tiempo_txt,
						tp.precio,
						tp.classtipo,
						tp.classbtn,
						tp.style
				FROM tipos_premium tp ORDER BY precio
			");
		}
		
		return array();
	}

	public function projectCover($project) {
		$id_proyecto = (int) getValueFrom($_POST, "id_proyecto", $project);

		$exists = (int) $this->db->queryOne("SELECT COUNT(*) FROM proyectos_portadas WHERE id_proyecto = :id", array(':id' => $id_proyecto));
		if (!$exists) {
			$this->db->query("INSERT INTO proyectos_portadas SET id_proyecto = :id", array(':id' => $id_proyecto));
		}

		$portadas = $this->db->queryRow("SELECT * FROM proyectos_portadas WHERE id_proyecto = :id", array(":id" => $id_proyecto));

		//$title = $this->db->queryOne("SELECT nombre FROM proyectos WHERE id_proyecto = :id", array(':id' => $id_proyecto));

		if (empty($portadas->portada_lg)) {
			$portadas->portada_lg = "//placehold.it/800x450";
		}
		if (empty($portadas->portada_md)) {
			$portadas->portada_md = "//placehold.it/400x225";
		}
		if (empty($portadas->portada_sm)) {
			$portadas->portada_sm = "//placehold.it/300x169";
		}

		return $portadas;
	}

	public function projectCoverUpdate() {
		$id_proyecto = getValueFrom($_POST, 'id_proyecto');
		$portada_lg = getValueFrom($_POST, 'portada_lg');

		if (empty($portada_lg)) {
			return 400;
		}

		$stmt = $this->db->prepare("UPDATE proyectos_portadas SET portada_lg = :foto WHERE id_proyecto = :id");
		$stmt->bindParam(':id', $id_proyecto);
		$stmt->bindParam(':foto', $portada_lg, PDO::PARAM_LOB);
		$stmt->execute();

		$image = preg_replace('/^data:image\/jpeg;base64,/', '', $portada_lg);
		$image = preg_replace('/\ /', '+', $image);
		$image = base64_decode($image);

		$imagick = new Imagick();
		$imagick->readImageBlob($image);

		$imagick->resizeImage(400, 225, imagick::FILTER_CATROM, 0.9, true);
		$imagick->setImageFormat("jpeg");
		$portada_md = "data:image/jpeg;base64,".base64_encode($imagick->getImageBlob());
		$stmt = $this->db->prepare("UPDATE proyectos_portadas SET portada_md = :foto WHERE id_proyecto = :id");
		$stmt->bindParam(':id', $id_proyecto);
		$stmt->bindParam(':foto', $portada_md, PDO::PARAM_LOB);
		$stmt->execute();

		$imagick->resizeImage(300, 169, imagick::FILTER_CATROM, 0.9, true);
		$imagick->setImageFormat("jpeg");
		$portada_sm = "data:image/jpeg;base64,".base64_encode($imagick->getImageBlob());
		$stmt = $this->db->prepare("UPDATE proyectos_portadas SET portada_sm = :foto WHERE id_proyecto = :id");
		$stmt->bindParam(':id', $id_proyecto);
		$stmt->bindParam(':foto', $portada_sm, PDO::PARAM_LOB);
		$stmt->execute();

		$imagick->clear();
		$imagick->destroy();

		return 200;
	}

	public function update() {
		$data = array(
			"id_proyecto" => (int) getValueFrom($_POST, "id_proyecto"),
			"nombre" => getValueFrom($_POST, "nombre"),
			"descripcion" => trim(getValueFrom($_POST, "descripcion")),
			"monto" => (float) getValueFrom($_POST, "monto"),
			"fecha" => getValueFrom($_POST, "fecha"),
			"id_fase" => getValueFrom($_POST, "id_fase"),
			"porcentaje" => getValueFrom($_POST, "porcentaje"),
		);

		$this->db->query("
			UPDATE
				proyectos
			SET
				nombre = :nombre,
				descripcion = :descripcion,
				monto_objetivo = :monto,
				fecha_objetivo = :fecha,
				id_fase = :fase,
				porcentaje = :porcentaje
			WHERE
				id_usuario = :id_usuario
				AND
				id_proyecto = :id_proyecto
		", array(
			":id_proyecto" => $data['id_proyecto'],
			":id_usuario" => $this->session->logged,
			":nombre" => $data['nombre'],
			":descripcion" => $data['descripcion'],
			":monto" => $data['monto'],
			":fecha" => $data['fecha'],
			":fase" => $data['id_fase'],
			":porcentaje" => $data['porcentaje'],
		));

		return 200;
	}

	public function ampliar() {
		$data = array(
			"id_proyecto" => (int) getValueFrom($_POST, "id_proyecto"),
			"socios" => getValueFrom($_POST, "socios"),
			"nicho" => getValueFrom($_POST, "nicho"),
			"destinatarios" => getValueFrom($_POST, "destinatarios"),
			"ingresos" => getValueFrom($_POST, "ingresos"),
			"inversion" => getValueFrom($_POST, "inversion"),
			"protegido" => getValueFrom($_POST, "protegido"),
			"marca" => getValueFrom($_POST, "marca"),
			"terceros" => getValueFrom($_POST, "terceros"),
			"geoubicacion" => trim(getValueFrom($_POST, "geoubicacion")),
			"cofundadores" => trim(getValueFrom($_POST, "cofundadores")),
			"dedicacion" => trim(getValueFrom($_POST, "dedicacion")),
			"primeraversion" => trim(getValueFrom($_POST, "primeraversion")),
			"urlproyecto" => trim(getValueFrom($_POST, "urlproyecto")),
			"sector" => trim(getValueFrom($_POST, "sector")),
			"competencia" => trim(getValueFrom($_POST, "competencia")),
			"urlvideo" => trim(getValueFrom($_POST, "urlvideo")),
			"plannegocios" => trim(getValueFrom($_POST, "plannegocios")),
			"modoingresos" => trim(getValueFrom($_POST, "modoingresos")),
			"franquicia" => trim(getValueFrom($_POST, "franquicia")),
			"planesingresos" => trim(getValueFrom($_POST, "planesingresos")),
			"rondainversion" => trim(getValueFrom($_POST, "rondainversion")),
			"inversionactual" => trim(getValueFrom($_POST, "inversionactual")),
			"inversionquien" => trim(getValueFrom($_POST, "inversionquien")),
		);

		$this->db->query("
			UPDATE
				proyectos
			SET
				socios = :socios,
				nicho = :nicho,
				destinatarios = :destinatarios,
				ingresos = :ingresos,
				inversion = :inversion,
				protegido = :protegido,
				marca = :marca,
				terceros = :terceros,
				geoubicacion = :geoubicacion,
				cofundadores = :cofundadores,
				dedicacion = :dedicacion,
				primeraversion = :primeraversion,
				urlproyecto = :urlproyecto,
				sector = :sector,
				competencia = :competencia,
				urlvideo = :urlvideo,
				plannegocios = :plannegocios,
				modoingresos = :modoingresos,
				franquicia = :franquicia,
				planesingresos = :planesingresos,
				rondainversion = :rondainversion,
				inversionactual = :inversionactual,
				inversionquien = :inversionquien
			WHERE
				id_usuario = :id_usuario
				AND
				id_proyecto = :id_proyecto
		", array(
			":id_proyecto" => $data['id_proyecto'],
			":id_usuario" => $this->session->logged,
			":socios" => $data['socios'],
			":nicho" => $data['nicho'],
			":destinatarios" => $data['destinatarios'],
			":ingresos" => $data['ingresos'],
			":inversion" => $data['inversion'],
			":protegido" => $data['protegido'],
			":marca" => $data['marca'],
			":terceros" => $data['terceros'],
			":geoubicacion" => $data['geoubicacion'],
			":cofundadores" => $data['cofundadores'],
			":dedicacion" => $data['dedicacion'],
			":primeraversion" => $data['primeraversion'],
			":urlproyecto" => $data['urlproyecto'],
			":sector" => $data['sector'],
			":competencia" => $data['competencia'],
			":urlvideo" => $data['urlvideo'],
			":plannegocios" => $data['plannegocios'],
			":modoingresos" => $data['modoingresos'],
			":franquicia" => $data['franquicia'],
			":planesingresos" => $data['planesingresos'],
			":rondainversion" => $data['rondainversion'],
			":inversionactual" => $data['inversionactual'],
			":inversionquien" => $data['inversionquien'],
		));

		return 200;
	}

	public function saveWork($project = 0) {
		$proyecto = (int) getValueFrom($_POST, 'id_proyecto', $project);
		$titulo = getValueFrom($_POST, 'titulo');
		$descripcion = getValueFrom($_POST, 'descripcion');
		$creacion = getValueFrom($_POST, 'creacion');
		$licenciar = getValueFrom($_POST, 'licenciar');
		$tipo_licencia = getValueFrom($_POST, 'tipo_licencia');

		$id_proyecto = (int) $this->db->queryOne("
			SELECT id_proyecto FROM proyectos WHERE id_proyecto = :id AND id_usuario = :user
		", array(
			':id' => $proyecto,
			':user' => $this->session->logged
		));

		if (!$id_proyecto) {
			return 400;
		}

		$this->db->query("
			INSERT INTO proyectos_obra
			SET
				id_proyecto = :proyecto,
				titulo = :titulo,
				descripcion = :descripcion,
				licenciar = :licenciar,
				fecha_creacion = :fecha_creacion,
				fecha_registro = NOW()
		", array(
			':proyecto' => $id_proyecto,
			':titulo' => $titulo,
			':descripcion' => $descripcion,
			':licenciar' => $licenciar,
			':fecha_creacion' => $creacion
		));
		if ($licenciar == "si") {
			$this->db->query ( "
				UPDATE proyectos_obra
				SET
				tipo_licencia = :tipo WHERE id_proyecto = :proyecto
			", array (
				':proyecto' => $id_proyecto,
				':tipo' => $tipo_licencia 
			) );
		}
		return 200;
	}

	public function saveMode($project = 0) {
		$proyecto = (int) getValueFrom($_POST, 'id_proyecto', $project);
		$licenciar = getValueFrom($_POST, 'licenciar');
		$tipo_licencia = getValueFrom($_POST, 'tipo_licencia');

		$id_proyecto = (int) $this->db->queryOne("
			SELECT id_proyecto FROM proyectos WHERE id_proyecto = :id AND id_usuario = :user
		", array(
			':id' => $proyecto,
			':user' => $this->session->logged
		));

		if (!$id_proyecto) {
			return 400;
		}

		$this->db->query("
			UPDATE proyectos_obra
			SET
				licenciar = :licenciar WHERE id_proyecto = :proyecto
			", array(
			':proyecto' => $id_proyecto,
			':licenciar' => $licenciar
		));
		if($licenciar == "si") {
			$this->db->query("
				UPDATE proyectos_obra
				SET
				tipo_licencia = :tipo WHERE id_proyecto = :proyecto
			", array(
				':proyecto' => $id_proyecto,
				':tipo' => $tipo_licencia
			));
		}
		return 200;
	}

	public function legal($project = 0) {
		$proyecto = (int) getValueFrom($_POST, 'proyecto', $project);

		$id_proyecto = (int) $this->db->queryOne("
			SELECT id_proyecto FROM proyectos WHERE id_proyecto = :id AND id_usuario = :user
		", array(
			':id' => $proyecto,
			':user' => $this->session->logged
		));

		$info = $this->db->queryRow("SELECT * FROM proyectos_obra WHERE id_proyecto = :id", array(
			':id' => $proyecto
		));

		$holders = $this->db->queryAll("
			SELECT * FROM proyectos_autores WHERE id_proyecto = :proyecto ORDER BY id_dueno ASC
		", array(
			':proyecto' => $id_proyecto
		));
		
		$holdersl = $this->db->queryAll("
			SELECT * FROM proyectos_licenciatarios WHERE id_proyecto = :proyecto ORDER BY id_licenciatario ASC
		", array(
			':proyecto' => $id_proyecto
		));

		return (object) compact('info','holders','holdersl');
	}

	public function legalAdd() {
		$proyecto = (int) getValueFrom($_POST, 'proyecto');
		$nombre = getValueFrom($_POST, 'nombre');
		$apellidos = getValueFrom($_POST, 'apellidos');
		$tipo = getValueFrom($_POST, 'tipo');
		$email = getValueFrom($_POST, 'email');
		$msg = '';
		$clave = '';
		$id_proyecto = (int) $this->db->queryOne("
			SELECT id_proyecto FROM proyectos WHERE id_proyecto = :id AND id_usuario = :user
		", array(
			':id' => $proyecto,
			':user' => $this->session->logged
		));

		if (!$id_proyecto) {
			return (object)array('status' => 400, 'id' => $msg);
		}
		
		if($tipo == "l") {
			$clave = $this->db->queryOne("
			SELECT
				CAST(CONCAT(DATE_FORMAT(po.fecha_registro, '%d%m%Y-'), po.id_proyecto, '-',
				(SELECT COUNT(id_proyecto) + 1 FROM proyectos_licenciatarios WHERE id_proyecto = po.id_proyecto))AS CHAR)
			FROM proyectos_obra po
			WHERE po.id_proyecto = :id
			", array(
				':id' => $proyecto,
			));
				
				$this->db->query("
				INSERT INTO proyectos_licenciatarios
				SET
					id_proyecto = :proyecto, nombre = :nombre, apellidos = :apellidos, email = :email, clave_registro = :clave
			", array(
				':proyecto' => $id_proyecto,
				':nombre' => $nombre,
				':apellidos' => $apellidos,
				':email' => $email,
				':clave' => $clave
			));
			$msg = $this->db->getLastID();
		} else {
			$this->db->query("
			INSERT INTO proyectos_autores
			SET
				id_proyecto = :proyecto, nombre = :nombre, apellidos = :apellidos
		", array(
				':proyecto' => $id_proyecto,
				':nombre' => $nombre,
				':apellidos' => $apellidos
			));
			$msg = $this->db->getLastID();
		}

		return (object)array('status' => 200, 'id' => $msg, 'clave' => $clave);
	}
	
	public function legalDelete() {
		$proyecto = (int) getValueFrom($_POST, 'proyecto');
		$tipo = getValueFrom($_POST, 'tipo');
		$id = getValueFrom($_POST, 'id');
		$id_proyecto = (int) $this->db->queryOne("
			SELECT id_proyecto FROM proyectos WHERE id_proyecto = :id AND id_usuario = :user
		", array(
					':id' => $proyecto,
					':user' => $this->session->logged
			));
	
		if (!$id_proyecto) {
			return 400;
		}
	
		if($tipo == "l") {
			$clave = $this->db->query("DELETE FROM proyectos_licenciatarios WHERE id_licenciatario = :id",
			array(
				':id' => $id,
			));
		} else {
			$clave = $this->db->query("DELETE FROM proyectos_autores WHERE id_dueno = :id",
			array(
				':id' => $id,
			));
		}
		return 200;
	}
	
	public function legalLicensee() {
		$proyecto = (int) getValueFrom($_POST, 'proyecto');
		$nombre = getValueFrom($_POST, 'nombre');
		$apellidos = getValueFrom($_POST, 'apellidos');
	
		$id_proyecto = (int) $this->db->queryOne("
			SELECT id_proyecto FROM proyectos WHERE id_proyecto = :id AND id_usuario = :user
		", array(
					':id' => $proyecto,
					':user' => $this->session->logged
			));
	
		if (!$id_proyecto) {
			return 400;
		}
		$clave = $this->db->queryOne("
		SELECT
			CAST(CONCAT(DATE_FORMAT(po.fecha_registro, '%d%m%Y-'), po.id_proyecto, '-',
			(SELECT COUNT(id_proyecto) + 1 FROM proyectos_licenciatarios WHERE id_proyecto = po.id_proyecto))AS CHAR)
		FROM proyectos_obra po
		WHERE po.id_proyecto = :id
		", array(
					':id' => $proyecto,
			));
	
		$this->db->query("
			INSERT INTO proyectos_licenciatarios
			SET
				id_proyecto = :proyecto, nombre = :nombre, apellidos = :apellidos, clave_registro = :clave
		", array(
					':proyecto' => $id_proyecto,
					':nombre' => $nombre,
					':apellidos' => $apellidos,
					':clave' => $clave
			));
	
		return 200;
	}

	public function legalRemove() {
		$dueno = (int) getValueFrom($_POST, 'dueno');
		$proyecto = (int) getValueFrom($_POST, 'proyecto');

		$id_proyecto = (int) $this->db->queryOne("
			SELECT id_proyecto FROM proyectos WHERE id_proyecto = :id AND id_usuario = :user
		", array(
			':id' => $proyecto,
			':user' => $this->session->logged
		));

		if (!$id_proyecto) {
			return 400;
		}

		$this->db->query("
			DELETE FROM proyectos_autores
			WHERE
				id_proyecto = :proyecto
				AND
				id_dueno = :dueno
		", array(
			':proyecto' => $id_proyecto,
			':dueno' => $dueno
		));

		return 200;
	}

	public function rewards($project = 0) {
		$id = (int) getValueFrom($_GET, 'project', $project);
		$orden = $this->db->queryOne("SELECT orden_recompensas FROM proyectos WHERE id_proyecto = {$id}");
		$sql_orden = "";
		switch ($orden) {
			case 'PrecioZA':
				$sql_orden = "precio_numero DESC";
				break;
			case 'PrecioAZ':
				$sql_orden = "precio_numero ASC";
				break;
			case 'NombreZA':
				$sql_orden = "nombre DESC";
				break;
			case 'NombreAZ':
				$sql_orden = "nombre ASC";
				break;
		}
		$rewards = $this->db->queryAll("
			SELECT
				*,
				CONCAT(FORMAT(precio, 2), ' €') as precio,
				precio as precio_numero
			FROM
				proyectos_recompensas
			WHERE
				id_proyecto = :project
			ORDER BY {$sql_orden}
		", array(
			':project' => $id
		));
		return $rewards;
	}

	public function detailRewards($reward = 0) {
		$id = (int) getValueFrom($_POST, 'reward', $reward);
		$reward = $this->db->queryRow("
			SELECT
				*
			FROM
				proyectos_recompensas
			WHERE
				id_recompensa = :reward
		", array(
			':reward' => $id
		));
		return $reward;
	}
	
	public function servicios() {
		return $this->db->queryAll("
			SELECT
				id_servicio as id,
				CONCAT(getPostContent(id_post_title, '{$this->lang}'), ' / ', CAST(precio AS CHAR), ' &euro;') as label
			FROM otros_servicios
			WHERE estatus = 'Activo'
		");
	}
	
	public function serviciosFull() {
		$investments = $this->db->queryAll("
			SELECT
				id_servicio as id,
				getPostContent(id_post_title, '{$this->lang}') as nombre,
				getPostContent(id_post_desc, '{$this->lang}') as descripcion,
				precio, estatus
			FROM otros_servicios
			WHERE estatus = 'Activo'
			");
		foreach ($investments as &$inv) {
			$inv->monto_formateado = number_format($inv->precio, 2) . " &euro;";
		}
		return $investments;
	}
	
	public function fases() {
		return $this->db->queryAll("
			SELECT id_fase as id,
				CONCAT(getPostContent(id_post_name, '{$this->lang}'), ' (Avance ~%', porcentaje, ')') as label
			FROM proyectos_fases
			WHERE estatus = 'Activo'
			ORDER BY porcentaje ASC
		");
	}

	public function saveReward() {
		$data = array(
			"id_proyecto" => getValueFrom($_POST, "id_proyecto", 0),
			"id_recompensa" => getValueFrom($_POST, "id_recompensa", 0),
			"nombre" => getValueFrom($_POST, "nombre"),
			"inventario" => getValueFrom($_POST, "inventario", 0),
			"ilimitado" => getValueFrom($_POST, "ilimitado", 0),
			"precio" => getValueFrom($_POST, "precio"),
			"descripcion" => getValueFrom($_POST, "descripcion"),
		);

		$id_proyecto = (int) $this->db->queryOne("
			SELECT id_proyecto FROM proyectos WHERE id_proyecto = :id AND id_usuario = :user
		", array(
			':id' => $data['id_proyecto'],
			':user' => $this->session->logged
		));

		if (!$id_proyecto) {
			return 400;
		}

		$id_recompensa = (int) $this->db->queryOne("
			SELECT id_recompensa FROM proyectos_recompensas WHERE id_recompensa = :id
		", array(
			':id' => $data['id_recompensa']
		));

		if ($id_recompensa) {
			$this->db->query("
				UPDATE proyectos_recompensas
				SET
					id_proyecto = :id_proyecto,
					nombre = :nombre,
					inventario = :inventario,
					ilimitado = :ilimitado,
					precio = :precio,
					descripcion = :descripcion,
					creado = NOW(),
					estatus = 'activo'
				WHERE
					id_recompensa = :id_recompensa
			", array(
				':id_proyecto' => $id_proyecto,
				':id_recompensa' => $id_recompensa,
				':nombre' => $data['nombre'],
				':inventario' => $data['inventario'],
				':ilimitado' => $data['ilimitado'],
				':precio' => $data['precio'],
				':descripcion' => $data['descripcion'],
			));
		} else {
			$this->db->query("
				INSERT INTO proyectos_recompensas
				SET
					id_proyecto = :id_proyecto,
					nombre = :nombre,
					inventario = :inventario,
					ilimitado = :ilimitado,
					precio = :precio,
					descripcion = :descripcion,
					creado = NOW(),
					estatus = 'activo'
			", array(
				':id_proyecto' => $id_proyecto,
				':nombre' => $data['nombre'],
				':inventario' => $data['inventario'],
				':ilimitado' => $data['ilimitado'],
				':precio' => $data['precio'],
				':descripcion' => $data['descripcion'],
			));
		}

		return 200;
	}

	public function removeReward() {
		$id = (int) getValueFrom($_POST, 'id');
		$id_recompensa = (int) $this->db->queryOne("
			SELECT
				r.id_recompensa
			FROM
				proyectos_recompensas r
			INNER JOIN
				proyectos p ON p.id_proyecto = r.id_proyecto
			WHERE
				p.id_usuario = :usuario
				AND
				r.id_recompensa = :recompensa
		", array(
			':usuario' => $this->session->logged,
			':recompensa' => $id
		));

		if (!$id_recompensa) {
			D($this->db->last_query);
			return 400;
		}

		$this->db->query("DELETE FROM proyectos_recompensas WHERE id_recompensa = :recompensa", array(
			':recompensa' => $id_recompensa
		));

		return 200;
	}

	public function orderRewards() {
		$id = (int) getValueFrom($_POST, 'id');
		$orden = getValueFrom($_POST, 'orden');

		$this->db->query("
			UPDATE
				proyectos
			SET
				orden_recompensas = :orden
			WHERE
				id_usuario = :id_usuario
				AND
				id_proyecto = :id_proyecto
		", array(
			":id_proyecto" => $id,
			":id_usuario" => $this->session->logged,
			":orden" => $orden,
		));

		return 200;
	}

	public function listInvestments($project = 0) {
		if ($project) {
			$investments = $this->db->queryAll("
				SELECT
					pi.id_inversion,
					pi.monto,
					pi.moneda,
					pi.estatus,
					pi.creado,
					CONCAT(u.nombre, ' ', u.apellidos) as inversor,
					pr.nombre as recompensa
				FROM
					proyectos_inversiones pi
				INNER JOIN
					proyectos p ON p.id_proyecto = pi.id_proyecto
				INNER JOIN
					proyectos_recompensas pr ON pr.id_recompensa = pi.id_recompensa
				INNER JOIN
					usuarios u ON u.id_usuario = pi.id_usuario
				WHERE
					p.id_usuario = :usuario
					AND
					p.id_proyecto = :project
				ORDER BY
					pi.creado
			", array(
				':usuario' => $this->session->logged,
				':project' =>  $project
			));
		} else {
			$investments = $this->db->queryAll("
				SELECT
					pi.*,
					p.nombre
				FROM
					proyectos_inversiones pi
				INNER JOIN
					proyectos p ON p.id_proyecto = pi.id_proyecto
				WHERE
					pi.id_usuario = :usuario
			", array(
				':usuario' => $this->session->logged
			));
		}

		foreach ($investments as &$inv) {
			$inv->monto_formateado = number_format($inv->monto, 2) . " " . $inv->moneda;
			$inv->estatus = $this->estatus[$inv->estatus];
		}

		return $investments;
	}
	
	public function listPayments($project = 0) {
		if ($project) {
			/*$investments = $this->db->queryAll("
				SELECT
					pi.id_inversion,
					pi.monto,
					pi.moneda,
					pi.estatus,
					pi.creado,
					CONCAT(u.nombre, ' ', u.apellidos) as inversor,
					pr.nombre as recompensa
				FROM
					proyectos_inversiones pi
				INNER JOIN
					proyectos p ON p.id_proyecto = pi.id_proyecto
				INNER JOIN
					proyectos_recompensas pr ON pr.id_recompensa = pi.id_recompensa
				INNER JOIN
					usuarios u ON u.id_usuario = pi.id_usuario
				WHERE
					p.id_usuario = :usuario
					AND
					p.id_proyecto = :project
				ORDER BY
					pi.creado
			", array(
						':usuario' => $this->session->logged,
						':project' =>  $project
				));*/
		} else {
			$investments = $this->db->queryAll("
				SELECT nombre, descripcion, monto, estatus, creado, moneda, tipo, id_pago, proyecto, vigente, fecha_hora_termino, entregado FROM 
				(SELECT getPostContent(tp.id_post_name, '{$this->lang}') as nombre, getPostContent(tp.id_post_desc, '{$this->lang}')as descripcion, pp.monto, pp.estatus, pp.creado, pp.moneda, '1' as tipo, pp.id_pago, p.nombre as proyecto, IF(NOW() < cp.fecha_hora_termino, 'Vigente', '') as vigente, fecha_hora_termino,
				'1' as entregado, '' as id_soporte 
				from pago_premium pp
				inner join contratacion_premium cp using(id_pago)
				inner join tipos_premium tp ON tp.id_tipo = pp.id_tipo_premium
				inner join proyectos p on p.id_proyecto = cp.id_proyecto 
				where cp.id_usuario = :usuario
				UNION
				SELECT getPostContent(os.id_post_title, '{$this->lang}') as nombre, getPostContent(os.id_post_desc, '{$this->lang}') as descripcion, ps.monto, ps.estatus, ps.creado, ps.moneda, '2' as tipo, ps.id_pago, '' as proyecto, '' as vigente, '' as fecha_hora_termino,
				ps.entregado, ps.id_soporte 
				FROM pagos_servicios ps
				inner join otros_servicios os using(id_servicio)
				WHERE ps.id_usuario = :usuario) r ORDER BY r.fecha_hora_termino DESC
				
			", array(
					':usuario' => $this->session->logged
			));
		}
	
		foreach ($investments as &$inv) {
			$inv->monto_formateado = number_format($inv->monto, 2) . " " . $inv->moneda;
			$inv->estatus = $this->estatus[$inv->estatus];
		}
	
		return $investments;
	}

	public function investment($investment = 0) {
		$id = (int) getValueFrom($_POST, 'id', $investment);
		$investment = $this->db->queryRow("
			SELECT
				CONCAT(u.nombre, ' ', u.apellidos) as inv_nombre,
				CONCAT(u.direccion, ', Ciudad ', u.ciudad, ', País ', u.pais) as inv_direccion,
				u.email as inv_email,
				u.telefono as inv_tel,
				u.skype as inv_skype,
				pi.monto,
				pi.moneda,
				pi.metodo,
				pi.ppraw,
				pi.estatus,
				pi.creado,
				p.nombre as proyecto,
				pr.nombre as recompensa,
				pr.descripcion
			FROM
				proyectos_inversiones pi
			INNER JOIN
				proyectos_recompensas pr ON pi.id_recompensa = pr.id_recompensa
			INNER JOIN
				proyectos p ON p.id_proyecto = pi.id_proyecto
			INNER JOIN
				usuarios u ON u.id_usuario = pi.id_usuario
			WHERE
				(
					pi.id_usuario = :usuario
					AND
					pi.id_inversion = :id
				)
				OR
				(
					p.id_usuario = :usuario
					AND
					pi.id_inversion = :id
				)
		", array(
			':usuario' => $this->session->logged,
			':id' => $id
		));

		$investment->monto_formateado = number_format($investment->monto, 2) . " " . $investment->moneda;
		$investment->estatus = @$this->estatus[$investment->estatus];

		$investment->transaccion = "No";
		if ($investment->metodo == "tarjeta") {
			$investment->ppraw = unserialize($investment->ppraw);
			$investment->transaccion = $investment->ppraw->Ds_AuthorisationCode;
		}

		return $investment;
	}
	
	public function payment($investment = 0) {
		$id = (int) getValueFrom($_POST, 'id', $investment);
		$tipo = (int) getValueFrom($_POST, 'tipo', $investment);
		switch($tipo) {
			case 1:
				$investment = $this->db->queryRow("
				SELECT  getPostContent(tp.id_post_name, '{$this->lang}') as nombre,
						getPostContent(tp.id_post_desc, '{$this->lang}')as descripcion,
						pp.monto, pp.estatus, pp.creado,
						pp.moneda, pp.metodo, pp.ppraw,
			    	    'N/A' as asunto, '1' as entregado, '' as id_soporte
				from pago_premium pp
				inner join contratacion_premium cp using(id_pago)
				inner join tipos_premium tp ON tp.id_tipo = pp.id_tipo_premium
				where cp.id_usuario = :usuario
				AND pp.id_pago = :id",
				array(
						':usuario' => $this->session->logged,
						':id' => $id
				));
				break;
			case 2:
				$investment = $this->db->queryRow("
					SELECT
						getPostContent(os.id_post_title, '{$this->lang}') as nombre,
						getPostContent(os.id_post_desc, '{$this->lang}')as descripcion,
						ps.entregado, ps.monto, ps.estatus, ps.creado, ps.moneda, ps.metodo, ps.ppraw,
					s.asunto, s.id_soporte
					FROM pagos_servicios ps
					inner join otros_servicios os using(id_servicio)
					inner join soporte s on ps.id_soporte = s.id_soporte
					WHERE ps.id_usuario = :usuario
					AND ps.id_pago = :id
				", array(
						':usuario' => $this->session->logged,
						':id' => $id
				));
				break;
		}
		
		$investment->monto_formateado = number_format($investment->monto, 2) . " " . $investment->moneda;
		$investment->estatus = @$this->estatus[$investment->estatus];
	
		$investment->transaccion = "No";
		if ($investment->metodo == "tarjeta" && $investment->estatus == "Success") {
			$investment->ppraw = unserialize($investment->ppraw);
			$investment->transaccion = $investment->ppraw->Ds_AuthorisationCode;
		}
	
		return $investment;
	}

	public function documents($project = 0, $kind = '') {
		$id = (int) getValueFrom($_GET, 'project', $project);
		$kind = getValueFrom($_GET, 'kind', $kind);

		$documents = $this->db->queryAll("
			SELECT
				id_documento,
				id_proyecto,
				tipo,
				tamano,
				nombre,
				descripcion,
				version,
				creado,
				contenido,
				'' as link
			FROM
				proyectos_documentos
			WHERE
				id_proyecto = :id
				AND
				clasificacion = :kind AND contenido = 'inDropbox'
		", array(
			':id' => $id,
			':kind' => $kind
		));
		foreach($documents as &$doc) {
			if ($doc->contenido == 'inDropbox') {
				$params = array(
				'http' => array(
						'method' => 'POST',
						'content' => http_build_query(array('path' => "{$doc->id_proyecto}/{$doc->nombre}", 'servicio' => 'escrow'))
					)
				);
				
				$ctx = stream_context_create($params);
				$fp = @fopen("https://designandlaw.com/dropbox_upload/anexo/descargar", 'rb', false, $ctx);
				if ($fp) {
					$json = json_decode(stream_get_contents($fp), true);
					$doc->link = $json[0];
				} else {
					$doc->link = '#error';
				}
			}
		}

		return $documents;
	}

	public function download() {
		ob_start();
		$id = getValueFrom($_GET, 'id', 0, FILTER_SANITIZE_PHRAPI_INT);
		$type = getValueFrom($_GET, 'type', 0, FILTER_SANITIZE_PHRAPI_INT);
		if($type == 1) {
			$doc = $this->db->queryRow("
			SELECT
				d.*
			FROM
				adjuntos_soporte d
			INNER JOIN
				soporte p ON p.id_soporte = d.id_soporte
			WHERE
				d.id_adjunto = :id
			AND
				p.id_usuario = :user
			", array(
				':user' => $this->session->logged,
				':id' => $id,
			));
		} else {
			$doc = $this->db->queryRow("
			SELECT
				d.*
			FROM
				proyectos_documentos d
			INNER JOIN
				proyectos p ON p.id_proyecto = d.id_proyecto
			WHERE
				d.id_documento = :id
				AND
				p.id_usuario = :user
			", array(
					':user' => $this->session->logged,
					':id' => $id,
			));
		}
		
		ob_clean();
		header("Content-type: $doc->tipo");
		header("Content-length: $doc->tamano");
		header("Content-Disposition: attachment; filename=\"$doc->nombre\"");
		echo $doc->contenido;
	}
	
	public function tickets() {
		$tickets = $this->db->queryAll("
			SELECT
				s.id_soporte,
				s.asunto,
				(SELECT COUNT(*) FROM soporte s2 WHERE s2.id_pre_soporte = s.id_soporte OR s2.id_soporte = s.id_soporte) AS total,
				(SELECT COUNT(*) FROM adjuntos_soporte a WHERE a.id_soporte = s.id_soporte AND a.es_dl != 1) AS adjuntos,
				(SELECT COUNT(*) FROM adjuntos_soporte adl WHERE adl.id_soporte = s.id_soporte AND adl.es_dl = 1) AS adjuntosdl,
				(SELECT COUNT(*) FROM pagos_servicios ps WHERE ps.id_soporte = s.id_soporte) AS pagos,
				s.creado,
				(SELECT s3.creado FROM soporte s3 WHERE s3.id_pre_soporte = s.id_soporte OR s3.id_soporte = s.id_soporte ORDER BY s.creado DESC limit 1) AS ultimo,
				CAST(CONCAT(DATE_FORMAT(s.creado, '%Y%m%d%H%i%s'), s.id_soporte) AS CHAR) as id_ticket
			FROM
				soporte s
			WHERE
				ISNULL(s.id_pre_soporte)
				AND
				s.id_usuario = :usuario
			ORDER BY
				s.creado DESC
		", array(
			':usuario' => $this->session->logged
		));
		return $tickets;
	}

	public function ticketDetail() {
		$id = getValueFrom($_POST, 'id', 0, FILTER_SANITIZE_PHRAPI_INT);
		$detalle = new stdClass();
		$detalle->mensajes = $this->db->queryAll("
			SELECT
				s.*,
				IF(ISNULL(s.id_usuario), 'Design & Law', (SELECT CONCAT(u.nombre, ' ', u.apellidos) FROM usuarios u WHERE u.id_usuario = s.id_usuario)) as autor
			FROM
				soporte s
			WHERE
				(s.id_soporte = :soporte OR s.id_pre_soporte = :soporte)
			ORDER BY
				s.creado ASC
		", array(
			':soporte' => $id,
			//':usuario' => $this->session->logged
		));
		$detalle->anexos = $this->db->queryAll(
			"SELECT id_adjunto, nombre, tamano, creado
			FROM adjuntos_soporte
			WHERE id_soporte = :id_soporte
			AND es_dl != 1",
			array(
				':id_soporte' => $id
			));
		foreach($detalle->anexos as &$anexo) {
			$anexo->tamano = bytesToHuman($anexo->tamano);
		}
		$detalle->anexosdl = $this->db->queryAll(
				"SELECT id_adjunto, nombre, tamano, creado, visto_u
			FROM adjuntos_soporte
			WHERE id_soporte = :id_soporte
			AND es_dl = 1",
			array(
					':id_soporte' => $id
			));
		foreach($detalle->anexosdl as &$anexodl) {
			$anexodl->tamano = bytesToHuman($anexodl->tamano);
		}
		$detalle->pagos = $this->db->queryAll(
			"SELECT ps.id_pago,
				getPostContent(os.id_post_title, '{$this->lang}') as nombre,
				ps.monto, ps.creado, ps.estatus, ps.entregado, ps.visto_u, ps.fecha_e
			FROM pagos_servicios ps
			INNER JOIN otros_servicios os USING(id_servicio)
			WHERE id_soporte = :id_soporte
			",
		array(
			':id_soporte' => $id
		));
		foreach($detalle->pagos as &$pago) {
			$pago->cf = $this->cantidadFormateada($pago->monto);
		}
		
		return $detalle;
	}

	public function request() {
		$data = getHash($_POST, array(
			"id_soporte" => FILTER_SANITIZE_PHRAPI_INT,
			"asunto" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"comentarios" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"requiere" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"servicio" => FILTER_SANITIZE_PHRAPI_INT,
			"total_archivos" => FILTER_SANITIZE_PHRAPI_INT
		));
		
		$id_soporte = (int) $this->db->queryOne("
			SELECT id_soporte
			FROM soporte
			WHERE
				id_soporte = :soporte
				AND
				id_usuario = :usuario
		", array(
			':soporte' => $data['id_soporte'],
			':usuario' => $this->session->logged
		));

		if ($id_soporte) {
			$this->db->query("
				INSERT INTO
					soporte
				SET
					id_pre_soporte = :pre,
					id_usuario = :usuario,
					asunto = '',
					comentarios = :comentarios,
					creado = NOW(),
					visto_u = 1
			", array(
				':pre' => $id_soporte,
				':usuario' => $this->session->logged,
				':comentarios' => $data['comentarios'],
			));

			return array(
				'autor' => $this->db->queryOne("SELECT CONCAT(nombre, ' ', apellidos) FROM usuarios WHERE id_usuario = {$this->session->logged}"),
				'comentarios' => $data['comentarios'],
				'creado' => date('Y-m-d H:i:s')
			);
		} else {
			$this->db->query("
				INSERT INTO
					soporte
				SET
					id_usuario = :usuario,
					asunto = :asunto,
					comentarios = :comentarios,
					creado = NOW(),
					visto_u = 1
			", array(
				':usuario' => $this->session->logged,
				':asunto' => $data['asunto'],
				':comentarios' => $data['comentarios'],
			));
			//Guardamos archivos
			$id_soporte = $this->db->getLastID();
			for ($i = 1; $i < $data['total_archivos']; $i ++) {
				
				$nombre = "archivo_$i";
				$doc = isset($_FILES[$nombre]) ? $_FILES[$nombre] : null;

				if (is_array($doc) && $doc['size'] && $id_soporte) {
					$this->db->query("
					INSERT INTO adjuntos_soporte SET
						id_soporte = :id_soporte,
						tipo = :tipo,
						tamano = :tamano,
						nombre = :nombre,
						creado = NOW(),
						visto_u = 1
				", array(
						":id_soporte" => $id_soporte,
						":tipo" => $doc['type'],
						":tamano" => $doc['size'],
						":nombre" => $doc['name']
				));
					$id_documento = $this->db->getLastID();
					
					$blob = fopen($doc['tmp_name'], 'rb');
					$stmt = $this->db->prepare("UPDATE adjuntos_soporte SET contenido = :contenido WHERE id_documento = :id_documento");
					$stmt->bindParam(':id_documento', $id_documento);
					$stmt->bindParam(':contenido', $blob, PDO::PARAM_LOB);
					$stmt->execute();
				}
			}
		}
		return (int)$id_soporte;
	}
	
	public function adjuntar() {
		$data = getHash($_POST, array(
				"id_soporte" => FILTER_SANITIZE_PHRAPI_INT
		));
	
		$id_soporte = (int) $this->db->queryOne("
			SELECT id_soporte
			FROM soporte
			WHERE
				id_soporte = :soporte
				AND
				id_usuario = :usuario
		", array(
			':soporte' => $data['id_soporte'],
			':usuario' => $this->session->logged
		));
	
		if ($id_soporte) {
			
		$doc = isset($_FILES["documento"]) ? $_FILES["documento"] : null;
			
			if (is_array($doc) && $doc['size'] && $id_soporte) {
				$this->db->query("
				INSERT INTO adjuntos_soporte SET
					id_soporte = :id_soporte,
					tipo = :tipo,
					tamano = :tamano,
					nombre = :nombre,
					creado = NOW(),
					visto_u = 1
				", array(
					":id_soporte" => $id_soporte,
					":tipo" => $doc['type'],
					":tamano" => $doc['size'],
					":nombre" => $doc['name']
				));
				$id_documento = $this->db->getLastID();
		
				$blob = fopen($doc['tmp_name'], 'rb');
				$stmt = $this->db->prepare("UPDATE adjuntos_soporte SET contenido = :contenido WHERE id_documento = :id_documento");
				$stmt->bindParam(':id_documento', $id_documento);
				$stmt->bindParam(':contenido', $blob, PDO::PARAM_LOB);
				$stmt->execute();
			}
		}
		return array(
			'id_adjunto' => $id_documento,
			"creado" => date('Y-m-d h:i:s'),
			"tamano" => bytesToHuman($doc['size']),
			"nombre" => $doc['name']
		);
	}

	public function cantidadFormateada($cantidad) {
		return $this->divisa_prefijo . number_format($cantidad, 2) . $this->divisa_sufijo;
	}
	
	public function dropbox() {
		
		$anexo = isset($_FILES["anexo"]) ? $_FILES["anexo"] : null;
		$id_proyect = (int) getValueFrom($_POST, 'prefix');
		$descripcion = getValueFrom($_POST, 'descripcion', FILTER_SANITIZE_PHRAPI_MYSQL);
		
		if (is_array($anexo) && $anexo['size']) {
			
			$exists = $this->db->queryOne("
					SELECT COUNT(*)
					FROM proyectos_documentos
					WHERE
					id_proyecto = '{$id_proyect}'
					AND
					nombre = '{$anexo["name"]}'
					");
			
			if (!$exists) {
				$this->db->query("
						INSERT INTO proyectos_documentos SET
						id_proyecto = '{$id_proyect}',
						tipo = '{$anexo["type"]}',
						nombre = '{$anexo["name"]}',
						clasificacion = 'Juridico',
						tamano = '{$anexo["size"]}',
						contenido = 'inDropbox',
						creado = NOW(),
						descripcion = '{$descripcion}'
						");
			} else {
				$this->db->query("
						UPDATE proyectos_documentos SET
						tipo = '{$anexo["type"]}',
						tamano = '{$anexo["size"]}',
						contenido = 'inDropbox',
						actualizado = NOW(),
						descripcion = '{$descripcion}'
						WHERE
						id_proyecto = '{$id_proyect}'
						AND
						nombre = '{$anexo["name"]}'
						");
			}
			if($id_proyect && $id_proyect > 0) {
				define('MULTIPART_BOUNDARY', '--------------------------'.microtime(true));
				$header = 'Content-Type: multipart/form-data; boundary='.MULTIPART_BOUNDARY;
				// equivalent to <input type="file" name="uploaded_file"/>
				define('FORM_FIELD', 'anexo'); 
				$filename = $anexo['name'];
				$file_contents = file_get_contents($anexo['tmp_name']);

				$content =  "--".MULTIPART_BOUNDARY."\r\n".
							"Content-Disposition: form-data; name=\"".FORM_FIELD."\"; filename=\"".basename($filename)."\"\r\n".
							"Content-Type: " . $anexo["type"] . "\r\n\r\n".
							$file_contents."\r\n";

				// add some POST fields to the request too: $_POST['foo'] = 'bar'
				$content .= "--".MULTIPART_BOUNDARY."\r\n".
							"Content-Disposition: form-data; name=\"prefix\"\r\n\r\n".
							$id_proyect . "\r\n";
				$content .= "--".MULTIPART_BOUNDARY."\r\n".
							"Content-Disposition: form-data; name=\"servicio\"\r\n\r\n".
							"escrow\r\n";

				// signal end of request (note the trailing "--")
				$content .= "--".MULTIPART_BOUNDARY."--\r\n";
				
				
				$params = array(
					'http' => array(
						'method' => 'POST',
						'header' => $header,
						'content' => $content
					)
				);
				$ctx = stream_context_create($params);
				$fp = @fopen('https://designandlaw.com/dropbox_upload/anexo/subir1', 'rb', false, $ctx);
				if ($fp) {
					return @stream_get_contents($fp);
				}
				$this->db->query("
					DELETE
					FROM proyectos_documentos
					WHERE id_proyecto = '{$id_proyect}' AND
					nombre = '{$anexo["name"]}'
					");
				return "Error al conectar servicio dropbox...";
			}
		}
		return "Parece que no se recibió ningún archivo...";
	}
	
	public function dbs($url, $file, $prefix, $servicio) { 
		
		define('MULTIPART_BOUNDARY', '--------------------------'.microtime(true));
		$header = 'Content-Type: multipart/form-data; boundary='.MULTIPART_BOUNDARY;
		$filename = $file['name'];
		$file_contents = file_get_contents($file['tmp_name']);

		$content =  "--".MULTIPART_BOUNDARY."\r\n".
					"Content-Disposition: form-data; name=\"".FORM_FIELD."\"; filename=\"".basename($filename)."\"\r\n".
					"Content-Type: " . $file["type"] . "\r\n\r\n".
					$file_contents."\r\n";

		// add some POST fields to the request too: $_POST['foo'] = 'bar'
		$content .= "--".MULTIPART_BOUNDARY."\r\n".
					"Content-Disposition: form-data; name=\"prefix\"\r\n\r\n".
					$id_proyect . "\r\n".
					"Content-Disposition: form-data; name=\"servicio\"\r\n\r\n".
					$servicio . "\r\n";

		// signal end of request (note the trailing "--")
		$content .= "--".MULTIPART_BOUNDARY."--\r\n";
		
		
		$params = array(
			'http' => array(
				'method' => 'POST',
				'header' => $header,
				'content' => $content
			)
		);
		$ctx = stream_context_create($params);
		$fp = @fopen($url, 'rb', false, $ctx);
		if ($fp) {
			return @stream_get_contents($fp);
		}
		$this->db->query("
			DELETE
			FROM proyectos_documentos
			WHERE id_proyecto = '{$prefix}' AND
			nombre = '{$file["name"]}'
			");
		return "Error al conectar servicio dropbox...";
	}
	
	public function dropbox1() {
		$name = getValueFrom($_POST, 'path');
		$name = basename($name);
	
		$id_proyect = (int) getValueFrom($_POST, 'argc0');
		$descripcion = getValueFrom($_POST, 'argc1');
		$final = getValueFrom($_POST, 'argc4');
		$mime = getValueFrom($_POST, 'mime_type');
		$size = getValueFrom($_POST, 'bytes');
	
		$exists = $this->db->queryOne("
				SELECT COUNT(*)
				FROM proyectos_documentos
				WHERE
				id_proyecto = '{$id_proyect}'
				AND
				nombre = '{$name}'
				");
	
		if (!$exists) {
			$this->db->query("
					INSERT INTO proyectos_documentos SET
					id_proyecto = '{$id_proyect}',
					tipo = '{$mime}',
					nombre = '{$name}',
					tamano = '{$size}',
					contenido = 'inDropbox',
					created = NOW(),
					descripcion = '{$descripcion}'
					");
			$id_proyect = $this->db->getLastID();
		} else {
			$this->db->query("
					UPDATE proyectos_documentos SET
					tipo = '{$mime}',
					tamano = '{$size}',
					contenido = 'inDropbox',
					actualizado = NOW(),
					descripcion = '{$descripcion}',
					WHERE
					id_proyecto = '{$id_proyect}'
					AND
					nombre = '{$name}'
					");
		}
	
		//$final_url = $this->config->app->url_libs[0] . "do={$final}&id=" . $id_proyect;
		//echo "<script>location.href = '{$final_url}';</script>";
		//return array('status' => 200, 'data' => $id_proyect);
		return 200;
	}
}