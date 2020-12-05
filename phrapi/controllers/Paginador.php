<?php defined('PHRAPI') or die("Direct access not allowed!");

class Paginador {

	private $db;
	private $config;
	private $session;
	public $valido = false;
	public $paginadorOffset = 0;
	public $maxPaginas = 10;
	public $pagina = 1;
	public $tamano = 15;
	public $totalPaginas = 0;
	public $totalRegistros = 0;
	public $limitRegistros;
	public $hayMas = false;
	public $rinicial;
	public $tipo = '';
	public $where = '';
	public $info = array();
	
	public function __construct() {
		
		$this->config = $GLOBALS['config'];
		$this->db = DB::getInstance();
		$this->session = Session::getInstance();
		$this->persistent = Persistent::getInstance();
		$this->lang = $this->persistent->lang;
	}
	
	public function calcularDatos($tipo) {
		
		$this->limitRegistros = $this->maxPaginas * $this->tamano;
		$this->paginadorOffset = floor(($this->pagina - 1)/ $this->maxPaginas) * $this->maxPaginas;
		$this->rinicial = $this->tamano * ($this->pagina - 1);
		
		$sql = "";
		switch($tipo) {
			case "ordenes":
				$sql = "SELECT COUNT(*) total " .
				"FROM (SELECT o.numero FROM orden_servicio o JOIN cliente c ON o.idCliente = c.id
				LEFT JOIN usuario p ON o.idRecibio = p.idpersona
				LEFT JOIN usuario p1 ON o.idEntrego = p1.idpersona
				LEFT JOIN usuario p2 ON o.idAsignado = p2.idPersona
				LEFT JOIN orden_estatus oe ON o.estatus = oe.id $this->where ORDER BY o.numero, o.fechaT LIMIT $this->limitRegistros OFFSET " . (floor(($this->pagina - 1) / $this->maxPaginas) * $this->limitRegistros) . ") AS a";
				
				$sql1 = "SELECT COUNT(*) total " .
				"FROM (SELECT o.numero FROM orden_servicio o JOIN cliente c ON o.idCliente = c.id
				LEFT JOIN usuario p ON o.idRecibio = p.idpersona
				LEFT JOIN usuario p1 ON o.idEntrego = p1.idpersona
				LEFT JOIN usuario p2 ON o.idAsignado = p2.idPersona
				LEFT JOIN orden_estatus oe ON o.estatus = oe.id $this->where ORDER BY o.numero, o.fechaT LIMIT 1 OFFSET " . (floor((($this->pagina - 1) / $this->maxPaginas) + 1) * $this->limitRegistros) . ") AS a";
				break;
		}
		
		$this->totalRegistros = (int)$this->db->queryOne($sql);
		if($this->totalRegistros > 0) {
			$this->totalPaginas = ceil($this->totalRegistros / $this->tamano);
			$this->hayMas = ((int)$this->db->queryOne($sql1)) > 0 ? true : false;
		}
	}
}