<?php defined('PHRAPI') or die("Direct access not allowed!");

class Lang {
	private $config;
	private $persistent;
	private $lang;
	public $lang_short;
	protected $data = array();

	public function __construct(){
		$this->config = $GLOBALS['config'];
		$this->persistent = Persistent::getInstance();
		$this->lang = $this->persistent->lang;
		$this->lang_short = substr($this->lang, 0, 2);

		$file = PHRAPI_PATH . "langs" . DS . $this->lang . ".ini";
		if (file_exists($file) && is_readable($file))
		{
			$this->data = parse_ini_file($file, false);
		}
	}

	public function __get($prop) {
		$prop_lower = strtolower($prop);
		$default = preg_replace('/\_/', ' ', $prop);
		return isset($this->data[$prop_lower]) ? $this->data[$prop_lower] : $default;
	}

	public function get() {
		$translations = array();
		foreach (func_get_args() as $arg) {
			$tokens = explode(':', $arg);
			if (sizeof($tokens) == 2) {
				$translations[$tokens[0]] = $tokens[1];
			}
		}

		if (isset($translations[$this->lang]))
			return $translations[$this->lang];

		if (isset($translations[$this->lang_short]))
			return $translations[$this->lang_short];

		return "";
	}
}
