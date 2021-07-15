<?php defined('PHRAPI') or die("Direct access not allowed!");

//die("llegue en access.php");

class Access {

	private $db;
	private $config;
	private $session;
	protected $info = array();

	public function __construct(){
	    //die("llegue a constructor de access");
		$this->config = $GLOBALS['config'];
		$this->db = DB::getInstance();
		$this->session = Session::getInstance();
	}
	
	public static function esHoraLaboral() {
		$now = new Datetime("now");
		$begintime = new DateTime('09:30');
		$endtime = new DateTime('20:30');
		//if(($this->session->logged != 1 && $this->session->logged != 3)) {
			if($now >= $begintime && $now <= $endtime){
				// between times
				//echo "yay";
				return true;
			} else {
				// not between times
				//echo "nay";
				return false;
			}
		//}
		//return true;
	}

	public function is_logged() {
		return isset($this->session->logged);
	}
	
	public function logged() {
		return $this->session->logged;
	}
	
	public function alias() {
		return $this->session->alias;
	}
	
	public function profile() {
		return $this->session->profile;
	}
	
	public function imagen() {
		return $this->session->imagen;
	}
	
	public function showTicketURL() {
		return $this->session->showTicketURL;
	}
	
	public function printTicketURL() {
		return $this->session->printTicketURL;
	}
	
	public function logout() {
		if (isset($this->session->logged)) {
			unset($this->session->logged);
		}

		redirect("index.php");

		return 200;
	}

	public function login() {
		
		$user = getValueFrom($_POST, 'usuario', '', FILTER_SANITIZE_STRING);
		$pass = getValueFrom($_POST, 'contrasena', '', FILTER_SANITIZE_STRING);
		//$pass = "no-se-usa";
		if (empty($user) OR empty($pass)) {
			return 401;
		}
		
		$id = (int)$this->db->queryOne("
			SELECT id
			FROM usuario 
			WHERE login = :login and activo = 1 AND password = SHA2(:pass, 512)",
		array(
			':login' => $user, ':pass' => $pass
		));
		
		if (!$id) {
			return 402;
		}

		if ($id) {
			$this->session->logged = $id;
			$this->session->alias = $this->db->queryOne("
			SELECT alias
			FROM usuario
			WHERE login = :login and activo = 1",
			array(
				':login' => $user
			));
			$this->session->profile = $this->db->queryOne("
			SELECT profile
			FROM usuario
			WHERE login = :login and activo = 1",
			array(
				':login' => $user
			));
			$this->session->imagen = $this->db->queryOne("
			SELECT imagen
			FROM usuario
			WHERE login = :login and activo = 1",
			array(
				':login' => $user
			));
			$this->session->showTicketURL = $this->db->queryOne("SELECT IF((SELECT valor FROM config WHERE llave = 'modolocal') = '1', CONCAT('http://127.0.0.1/', valor), CONCAT('http://', (SELECT valor FROM config WHERE llave = 'ip'), '/', valor)) as valor FROM config WHERE llave = 'showTicketRemote'");
			$this->session->printTicketURL = $this->db->queryOne("SELECT IF((SELECT valor FROM config WHERE llave = 'modolocal') = '1', CONCAT('http://127.0.0.1/', valor), CONCAT('http://', (SELECT valor FROM config WHERE llave = 'ip'), '/', valor)) as valor FROM config WHERE llave = 'printTicketRemote'");
			return 200;
		}
		
		return 400;
	}
	
	public function loadUsers() {
	
		$usuarios = $this->db->queryAll("
			SELECT id, login, alias, imagen, ajuste, profile, IF(imagen != '', IF(ajuste = 'alto', 'auto 300px', '300px'), imagen) tamano, posicion FROM usuario WHERE activo = 1 ORDER BY orden desc
		");
		
		foreach ($usuarios as $usuario) {
			$usuario->estilo = $usuario->imagen == '' ? '' : "background-image:url('assets/images/" . $usuario->imagen . "'); background-size:" . $usuario->tamano . "; background-position:" . $usuario->posicion . ";";
			$usuario->clase = "profile " . $usuario->profile;
			if ($usuario->id != 10 && $usuario->id != 3 && !$this->esHoraLaboral()) {
				$usuario->estilo = "";
				$usuario->clase = "profilef " . $usuario->profile;
				$usuario->alias = "ZzZzZz...";
			}
		}
		return $usuarios;
	}
	
	public function loadWallpapers() {
		$images = array();
		//return ;
		if(isset($this->config['wallpapers_path'])) {
				
			if($handle = opendir($this->config['wallpapers_path'])) {
				while (false !== ($entry = readdir($handle))) {
					$files[] = $entry;
				}
				$images = preg_grep('/\.(jpg|jpeg|png|gif)(?:[\?\#].*)?$/i', $files);
				closedir($handle);
			}
		}
		return $images;
	}
	
	public function savePassword() {
		if($this->session->logged) {
			$errorMsg = "";
			$data = getHash($_POST, array(
				"id" => FILTER_SANITIZE_PHRAPI_MYSQL,
				"password" => FILTER_SANITIZE_PHRAPI_MYSQL,
				"repassword" => FILTER_SANITIZE_PHRAPI_MYSQL
			));
			if($data['id'] == $this->session->logged) {
				if($data['password'] && $data['repassword']) {
					if(trim($data['password']) != "" && trim($data['repassword']) != "" && trim($data['password']) === trim($data['repassword'])) {
						$this->db->query ("UPDATE usuario SET password = SHA2(:password, 512) WHERE id = :id",
						array (":id" => $data['id'], ":password" => $data['password']));
						return 200;
					} else {
						$errorMsg = "No coinciden los passwords o estan vacios";
					}
				} else {
					$errorMsg = "No se recibio el nuevo password";
				}
			} else {
				$errorMsg = "No se recibio el id de usuario correcto";
			}
		}
	}
	
	public function saveUserConfig() {
		$data = getHash($_POST, array(
			"ajuste" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"posicion" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"alias" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"id" => FILTER_SANITIZE_PHRAPI_MYSQL,
			"eliminar" => FILTER_SANITIZE_PHRAPI_MYSQL
		));
		if($data['id']) {
			$image = isset($_FILES['usrImg']) ? $_FILES['usrImg'] : null;
			$currentImage = $this->db->queryOne ( "
					SELECT imagen
					FROM usuario
					WHERE id = :id
			", array (
					':id' => $data ['id']
			));
			$imageName = $currentImage;
			if($data['id'] == $data['eliminar']) {
				
				if($currentImage && $currentImage != "") {
					unlink("./../" . $this->config['user_image_path'] . '/' . $currentImage);
				}
				$imageName = '';
				$data['ajuste'] = '';
				$data['posicion'] = '';
			} else if (is_array($image) && $image['size']) {
				
				if($currentImage && $currentImage != "") {
					unlink("./../" . $this->config['user_image_path'] . '/' . $currentImage);
				}
				
				$info = getimagesize($image['tmp_name']);
				$mime = getValueFrom($info, 'mime', '');
				$date = new DateTime();
				$prefix = $data['id'] . $date->format('YmdHis');
				$ext = "";
				if ($mime == 'image/jpeg') {
					$ext = ".jpg";
				} elseif ($mime == 'image/png') {
					$ext = ".png";
				} elseif ($mime == 'image/gif') {
					$ext = ".gif";
				}
				$imageName = $prefix . $ext;
				move_uploaded_file($image['tmp_name'], "./../" . $this->config['user_image_path'] . '/' . $imageName);
				
			}
			
			$this->db->query ( "
			UPDATE
				usuario
			SET
				ajuste = :ajuste,
				posicion = :posicion,
				alias = :alias,
				imagen = :imagen
			WHERE
				id = :id
			", array (
				":id" => $data['id'],
				":ajuste" => $data['ajuste'],
				":posicion" => $data['posicion'],
				":alias" => $data['alias'],
				":imagen" => $imageName  
			) );
			return 200;
		}
	}
}