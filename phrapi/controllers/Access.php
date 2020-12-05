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

	public function is_logged() {
		return isset($this->session->logged);
	}
	
	public function logged() {
		return $this->session->logged;
	}
	
	public function alias() {
		return $this->session->alias;
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
		$pass = "no-se-usa";
		if (empty($user) OR empty($pass)) {
			return 401;
		}
		
		$id = (int)$this->db->queryOne("
			SELECT id
			FROM usuario 
			WHERE login = :login and activo = 1",
		array(
			':login' => $user
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
			return 200;
		}
		
		return 400;
	}
	
	public function loadUsers() {
	
		$usuarios = $this->db->queryAll("
			SELECT id, login, alias, imagen, ajuste, profile, IF(imagen != '', IF(ajuste = 'alto', 'auto 300px', '300px'), '') tamano, posicion FROM usuario WHERE activo = 1 ORDER BY orden desc
		");
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