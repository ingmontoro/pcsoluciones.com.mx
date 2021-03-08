<?php header('Content-type: text/html; charset=iso-8859-1');?>
<?php defined('PHRAPI') or die("Direct access not allowed!");
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\CapabilityProfiles\DefaultCapabilityProfile;

class Ticket {
	protected $estatus = array(
			'' => 'Esperando Pago',
			'Failure' => 'Sin Pago',
			'Success' => 'Pagado',
			'Process' => 'En proceso',
			'Canceled' => 'Cancelada',
	);
	protected $meses = array("Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic");
	protected $theHTML = "";
	protected $tipo = 1;
	
	public function __construct(){
		$this->config = $GLOBALS['config'];
		$this->db = DB::getInstance();
		$this->session = Session::getInstance();
		$this->persistent = Persistent::getInstance();
		$this->lang = $this->persistent->lang;
	
		if (!isset($this->session->logged) OR !$this->session->logged) {
			redirect("login.php");
		}
		date_default_timezone_set("America/Mexico_City");
	}
	
	public function dataTicket($numero) {
		$data = new stdClass();
		$data->numero = $numero;
		$impresora = (int)$this->db->queryOne("SELECT impresora FROM cobro WHERE claveNota = {$numero}");
		$data->impresora = $impresora;
		if($impresora == 1) {
			//$data->datosCobro = $this->db->queryRow("SELECT c.fechaCobro, c.total, c.entregado, c.cambio, ifnull(c.impresora, 2) impresora, u.alias FROM nota n INNER JOIN cobro c INNER JOIN usuario u ON n.claveCobro = c.clave AND n.numero = c.claveNota AND c.idUsuario = u.id AND n.numero = {$numero}");
			$data->datosCobro = $this->db->queryRow("SELECT c.fechaCobro, c.total, c.entregado, c.cambio, ifnull(c.impresora, 2) impresora, ifnull(u.alias, 'Mostrador') as alias FROM nota n INNER JOIN cobro c INNER JOIN usuario u ON n.claveCobro = c.clave AND n.numero = c.claveNota AND c.idUsuario = u.id AND n.numero = {$numero}");
		} else {
			$data->datosCobro = $this->db->queryRow("SELECT c.fechaCobro, c.total, c.entregado, c.cambio, ifnull(c.impresora, 2) impresora, ifnull(u.alias, 'Mostrador') as alias FROM nota n INNER JOIN cobro c ON n.claveCobro = c.clave AND n.numero = c.claveNota AND n.numero = {$numero} LEFT JOIN usuario u ON c.idUsuario = u.id");
			//$data->datosCobro = $this->db->queryRow("SELECT c.fechaCobro, c.total, c.entregado, c.cambio, ifnull(c.impresora, 2) impresora FROM nota n INNER JOIN cobro c ON n.claveCobro = c.clave AND n.numero = c.claveNota AND n.numero = {$numero}");
		}
		$data->detalleTicket = 
		$this->db->queryAll(
				"SELECT * FROM (select n.numero,
				n.subtotal,
				n.iva,
				n.total,
				UPPER(if(a.codigo = 'free', '0', a.codigo)) codigo,
				dn.precio,
				dn.cantidad,
				dn.descripcion,
				dn.precio * dn.cantidad importe
				from nota n
				join detalle_nota dn on n.numero = dn.folio
				left join articulo a on a.codigo = dn.claveArticulo
				where n.numero = $numero
				and dn.claveArticulo = 'free'
				and a.codigo = 'free'
				UNION
				select n.numero,
				n.subtotal,
				n.iva,
				n.total,
				UPPER(a.codigo),
				a.precio,
				dn.cantidad,
				a.corta,
				a.precio * dn.cantidad importe
				from nota n
				join detalle_nota dn on n.numero = dn.folio
				left join articulo a on a.codigo = dn.claveArticulo
				where n.numero = $numero
				and dn.claveArticulo != 'free') K ORDER BY k.codigo, k.descripcion");
		return $data;
	}
	
	private function ticketToHTML($data) {
		
		$impresora = $data->impresora;
		$datosCobro = $data->datosCobro;
		$myTime = localtime(strtotime($datosCobro->fechaCobro), true);
		$fecha = $myTime['tm_mday'] . "/" . $this->meses[$myTime['tm_mon']] . "/" . (1900 + $myTime['tm_year']);
		$detalleNota = $data->detalleTicket;
		if(isset($detalleNota) && count($detalleNota) > 0) {
			$this->tipo = $datosCobro->impresora;
			$this->startTicket();
			$this->printHeader($data->numero, $fecha, $datosCobro->impresora);
			$this->printItems($detalleNota, $datosCobro->impresora);
			$this->printTotal($detalleNota, $datosCobro, $datosCobro->impresora);
			$this->printFooter($datosCobro);
			$this->endTicket();
		}
		return $this->theHTML;
	}
	
	public function showTicketRemote($data) {
		return $this->ticketToHTML($data);
		/*//$data = json_decode($_POST['data']['json']);
		//$data = json_decode($data);
		$impresora = $data->impresora;
		$datosCobro = $data->datosCobro;
		//$datosCobro = $this->db->queryRow("SELECT c.fechaCobro, c.total, c.entregado, c.cambio, ifnull(c.impresora, 2) impresora, u.alias FROM nota n INNER JOIN cobro c INNER JOIN usuario u ON n.claveCobro = c.clave AND n.numero = c.claveNota AND c.idUsuario = u.id AND n.numero = {$numnota}");
		$myTime = localtime(strtotime($datosCobro->fechaCobro), true);
		$fecha = $myTime['tm_mday'] . "/" . $this->meses[$myTime['tm_mon']] . "/" . (1900 + $myTime['tm_year']);
		$detalleNota = $data->detalleTicket;
		
		if(isset($detalleNota) && count($detalleNota) > 0) {
			$this->tipo = $datosCobro->impresora;
			$this->startTicket();
			$this->printHeader($data->numero, $fecha, $datosCobro->impresora);
			$this->printItems($detalleNota, $datosCobro->impresora);
			$this->printTotal($detalleNota, $datosCobro, $datosCobro->impresora);
			$this->printFooter($datosCobro);
			$this->endTicket();
		}
		return $this->theHTML;*/
	}
	
	public function showTicket($numnota = 0) {
		return $this->ticketToHTML($this->dataTicket($numnota));
		
		/*$impresora = (int)$this->db->queryOne("SELECT impresora FROM cobro WHERE claveNota = {$numnota}");
		if($impresora == 1) {
			$datosCobro = $this->db->queryRow("SELECT c.fechaCobro, c.total, c.entregado, c.cambio, ifnull(c.impresora, 2) impresora, u.alias FROM nota n INNER JOIN cobro c INNER JOIN usuario u ON n.claveCobro = c.clave AND n.numero = c.claveNota AND c.idUsuario = u.id AND n.numero = {$numnota}");
		} else {
			$datosCobro = $this->db->queryRow("SELECT c.fechaCobro, c.total, c.entregado, c.cambio, ifnull(c.impresora, 2) impresora FROM nota n INNER JOIN cobro c ON n.claveCobro = c.clave AND n.numero = c.claveNota AND n.numero = {$numnota}");
		}
		//$datosCobro = $this->db->queryRow("SELECT c.fechaCobro, c.total, c.entregado, c.cambio, ifnull(c.impresora, 2) impresora, u.alias FROM nota n INNER JOIN cobro c INNER JOIN usuario u ON n.claveCobro = c.clave AND n.numero = c.claveNota AND c.idUsuario = u.id AND n.numero = {$numnota}");
		$myTime = localtime(strtotime($datosCobro->fechaCobro), true);
		$fecha = $myTime['tm_mday'] . "/" . $this->meses[$myTime['tm_mon']] . "/" . (1900 + $myTime['tm_year']);
		$detalleNota = $this->db->queryAll
		("SELECT * FROM (select n.numero,
		n.subtotal,
		n.iva,
		n.total,
		UPPER(if(a.codigo = 'free', '0', a.codigo)) codigo,
		dn.precio,
		dn.cantidad,
		dn.descripcion,
		dn.precio * dn.cantidad importe
		from nota n
		join detalle_nota dn on n.numero = dn.folio
		left join articulo a on a.codigo = dn.claveArticulo
		where n.numero = {$numnota}
		and dn.claveArticulo = 'free'
		and a.codigo = 'free'
		UNION
		select n.numero,
		n.subtotal,
		n.iva,
		n.total,
		UPPER(a.codigo),
		a.precio,
		dn.cantidad,
		a.corta,
		a.precio * dn.cantidad importe
		from nota n
		join detalle_nota dn on n.numero = dn.folio
		left join articulo a on a.codigo = dn.claveArticulo
		where n.numero = {$numnota}
		and dn.claveArticulo != 'free') K ORDER BY k.codigo, k.descripcion");
		
		if(isset($detalleNota) && count($detalleNota) > 0) {
			$this->tipo = $datosCobro->impresora;
			$this->startTicket();
			$this->printHeader($numnota, $fecha, $datosCobro->impresora);
			$this->printItems($detalleNota, $datosCobro->impresora);
			$this->printTotal($detalleNota, $datosCobro, $datosCobro->impresora);
			$this->printFooter($datosCobro);
			$this->endTicket();
		}
		//D($this->theHTML);
		return $this->theHTML;*/
	}
	private function startTicket() {
		$this->theHTML .=  "<table style='font-family: arial; font-size: 12px; pading: 0;'>";
	}
	private function printHeader($numnota, $fecha, $tipo = 1) {
		if($tipo == 1) {
			$this->theHTML .=  "<tr><td colspan='48' align='center'><img src='assets/images/ticket-logo.png' /></td></tr>";
			$this->printBlankLine();
			
			$this->printLine($this->printWithFormat('MEDRANO #2799 RESIDENCIAL DEL PARQUE C.P. 44810', 'C'));
			$this->printLine($this->printWithFormat('GUADALAJARA JAL. TEL(33)33319708', 'C'));
			$this->printLine($this->printWithFormat('ERNESTO MONTORO RODRIGUEZ', 'C'));
			$this->printLine($this->printWithFormat('R.F.C: MORE820615PI1', 'C'));
			$this->printLine($this->printWithFormat('TICKET# ' . $this->fixFieldSize($numnota, 7, 'L', '0') . " FECHA:" . $this->fixFieldSize($fecha, 11, 'L', '0'), 'C'));
			$this->printLine($this->printWithFormat(''));
			$this->printLine($this->printWithFormat('------------------------------------------------'));
			$this->printLine($this->printWithFormat('CODIGO CONCEPTO        PRECIO/UN CANT.   IMPORTE'));
			$this->printLine($this->printWithFormat('------------------------------------------------'));
		} else {
			$this->printLine($this->printWithFormat('P C-S O L U C I O N E S', 'C'));
			$this->printBlankLine();
			$this->printLine($this->printWithFormat('MEDRANO #2799 RESIDENCIAL DEL', 'C'));
			$this->printLine($this->printWithFormat('PARQUE 44810 TEL (33)33319708', 'C'));
			$this->printLine($this->printWithFormat('CEL (044)3314862595', 'C'));
			$this->printLine($this->printWithFormat('ERNESTO MONTORO RODRIGUEZ', 'C'));
			$this->printLine($this->printWithFormat('R.F.C: MORE820615PI1', 'C'));
			$this->printLine($this->printWithFormat('TICKET#' . $this->fixFieldSize($numnota, 7, 'L', '0') . " FECHA:" . $this->fixFieldSize($fecha, 11, 'L', '0')));
			$this->printLine($this->printWithFormat(''));
			$this->printLine($this->printWithFormat("--------------------------------"));
			$this->printLine($this->printWithFormat("NUM CODIGO ARTICULO             "));
		}
		
	}
	private function printItems($detalleNota, $tipo = 1) {
		if($tipo == 1) {
			foreach ($detalleNota as $item) {
				$line = "";
				$line .= $this->fixFieldSize($item->codigo, 6, '', '0') . " ";
				$line .= $this->fixFieldSize($item->descripcion, 15, 'R') . " ";
				$line .= $this->formatPrecio($item->precio) . " ";
				$line .= $this->formatCantidad($item->cantidad) . " ";
				$line .= $this->formatPrecio($item->importe);
				$this->printLine($this->printWithFormat($line));
			}
		} else {
			$i = 1;
			foreach ($detalleNota as $item) {
				$line = "";
				$line .= $this->formatIndex($i);
				$line .= $this->fixFieldSize($item->codigo, 6, '', '0');
				$line .= ":";
				$line .= $this->fixFieldSize($item->descripcion, 21, 'R');
				$this->printLine($this->printWithFormat($line));
				$i ++;
			}
			$this->printLine($this->printWithFormat("--------------------------------"));
			$this->printLine($this->printWithFormat("NUM PRECIO/U  CANTIDAD   IMPORTE"));
			$i = 1;
			foreach ($detalleNota as $item) {
					
				$line = "";
				$line .= $this->formatIndex($i);
				$line .= "$";
				$line .= $this->formatPrecio($item->precio);
				$line .= "  ";
				$line .= $this->fixFieldSize($item->cantidad, 5, 'R');
				$line .= " ";
				$line .= "$";
				$line .= $this->formatPrecio($item->importe);
				$this->printLine($this->printWithFormat($line));
				$i ++;
			}
			$this->printLine($this->printWithFormat("--------------------------------"));
		}
	}
	private function printWithFormat($text, $align = 'L', $car = ' ') {
		$maxXLine = $this->tipo == 1 ? 48 : 32;//caracteres por linea
		$total = strlen($text);
		//Types T = Texto, P = precio ($99,999.00) Dos decimales y signo de pesos
		if ($align == 'C') {
			//echo "TOTAL: $total<br />";
			if ($total < $maxXLine) {
				$resto = $maxXLine - $total;
				//echo "RESTO: $re$theHTML .= br />";
				$residuo = $resto % 2;
				//echo "RESI: $residuo<br />";
				if ($residuo > 0) {
					$fix = $resto - 1;
					//echo "FIX: $fix<br />";
					$text = $this->centerTicketField($text, $fix / 2, $car);
					$text .= " ";
				} else {
					$text = $this->centerTicketField($text, $resto / 2, $car);
				}
			}
		} else {
			if ($total > $maxXLine) {
				$text = substr($text, 0, $maxXLine);
			}
		}
		//echo $text . "(" . strlen($text) . ")";
		//echo "<br />";
		
		//$this->printLine($text);
		return $text;
	}
	private function centerTicketField($_data, $length, $car = ' ') {
	
		$_br = '';
		for( $i = 0; $i < $length; $i ++ ) {
			$_br .= $car;
		}
		$_br .= $_data . $_br;
		return $_br;
	}
	private function printLine($text) {
		$this->theHTML .=  "<tr>";
		$data = str_split($text);
		foreach ($data as $letra) {
			$this->theHTML .=  "<td style='padding: 0px; margin: 0;'>$letra</td>";
		}
		$this->theHTML .=  "</tr>";
	}
	
	private function printTotal($detalleNota, $datosCobro, $tipo = 1) {
		$item = $detalleNota{0};
		if($tipo == 1) {
			$this->printWithFormat('------------------------------------------------');
			$line = "";
			if ($item->iva > 0) {
				$line .= $this->fixFieldSize("SUBTOTAL", 38) . " " . $this->formatPrecio($item->subtotal);
				$this->printLine($this->printWithFormat($line));
				$line = "";
				$line .= $this->fixFieldSize("IVA", 38) . " " . $this->formatPrecio($item->iva);
				$this->printLine($this->printWithFormat($line));
			}
			$line = "";
			$line .= $this->fixFieldSize("TOTAL", 38) . " " . $this->formatPrecio($item->total);
			$this->printLine($this->printWithFormat($line));
			$line = "";
			$line .= $this->fixFieldSize("RECIBE", 38) . " " . $this->formatPrecio($datosCobro->entregado);
			$this->printLine($this->printWithFormat($line));
			$line = "";
			$line .= $this->fixFieldSize("CAMBIO", 38) . " " . $this->formatPrecio($datosCobro->cambio);
			$this->printLine($this->printWithFormat($line));
			$totalf = number_format($datosCobro->total, 2, '.', '');
			$enletra = $this->num2letras($totalf);
			$this->printLine($this->printWithFormat($enletra));
			$this->printBlankLine();
			$this->printBlankLine();
		} else {
			$line = "";
			if ($item->iva > 0) {
				$line .= "SUBTOTAL              $";
				$line .= $this->formatPrecio($item->subtotal);
				$this->printLine($this->printWithFormat($line));
				$line = "";
				$line .= "IVA                   $";
				$line .= $this->formatPrecio($item->iva);
				$this->printLine($this->printWithFormat($line));
			}
			$line = "";
			$line .= "TOTAL                 $";
			$line .= $this->formatPrecio($datosCobro->total);
			$this->printLine($this->printWithFormat($line));
			$line = "";
			$line .= "RECIBE                $";
			$line .= $this->formatPrecio($datosCobro->entregado);
			$this->printLine($this->printWithFormat($line));
			$line = "";
			$line .= "CAMBIO                $";
			$line .= $this->formatPrecio($datosCobro->cambio);
			$this->printLine($this->printWithFormat($line));
		}
		
	}
	private function printBlankLine() {
		$this->theHTML .=  "<tr><td><br /></td></tr>";
	}
	//private function printFooter($atendio = null, $tipo = 1) {
	private function printFooter($datosCobro) {
		if($datosCobro->impresora == 1) {
			if(isset($datosCobro->alias) && $datosCobro->alias != null){
				$this->printLine($this->printWithFormat("Le atendió {$datosCobro->alias}", 'C'));
			}
			$this->printLine($this->printWithFormat('------------------------------------------------'));
			$this->printLine($this->printWithFormat('Gracias por su Visita', 'C'));
			$this->printLine($this->printWithFormat('Recuerde conservar este ticket como comprobante', 'C'));
			$this->printLine($this->printWithFormat('para cualquier aclaración o garantía.', 'C'));
			$this->printLine($this->printWithFormat('------------------------------------------------'));
		} else {
			$this->printBlankLine();
			$this->printLine($this->printWithFormat('Gracias por su Visita', 'C'));
			$this->printBlankLine();
			$this->printLine($this->printWithFormat(  'Recuerde conservar este', 'C'));
			$this->printLine($this->printWithFormat(  'comprobante para cualquier', 'C'));
			$this->printLine($this->printWithFormat(  'aclaración o garantía', 'C'));
		}
		
	}
	private function endTicket() {
		$this->theHTML .=  "</table>";
	}
	
	private function formatIndex($data) {
		$newData = "[" . $data . "]";
		if ($data < 10) {
			$newData .= " ";
		}
		return $newData;
	}
	public function detalleTicket($numero) {
		$detalleTicket = $this->db->queryAll(
				"SELECT * FROM (select n.numero,
				n.subtotal,
				n.iva,
				n.total,
				UPPER(if(a.codigo = 'free', '0', a.codigo)) codigo,
				dn.precio,
				dn.cantidad,
				dn.descripcion,
				dn.precio * dn.cantidad importe
				from nota n
				join detalle_nota dn on n.numero = dn.folio
				left join articulo a on a.codigo = dn.claveArticulo
				where n.numero = $numero
				and dn.claveArticulo = 'free'
				and a.codigo = 'free'
				UNION
				select n.numero,
				n.subtotal,
				n.iva,
				n.total,
				UPPER(a.codigo),
				a.precio,
				dn.cantidad,
				a.corta,
				a.precio * dn.cantidad importe
				from nota n
				join detalle_nota dn on n.numero = dn.folio
				left join articulo a on a.codigo = dn.claveArticulo
				where n.numero = $numero
				and dn.claveArticulo != 'free') K ORDER BY k.codigo, k.descripcion");
		return $detalleTicket;
	}
	
	public function printTicketRemote($data) {
		$mode = "prod";
		$numero = $data->numero;
		$entregado = $data->datosCobro->entregado;
		$cambio = $data->datosCobro->cambio;
		$fechaCobro = $data->datosCobro->fechaCobro;
		$atendio = $data->datosCobro->alias;
		$impresora = $data->datosCobro->impresora;
		
		date_default_timezone_set("America/Mexico_City");
		$myTime = localtime(strtotime($fechaCobro), true);
		$fecha = $myTime['tm_mday'] . "/" . $this->meses[$myTime['tm_mon']] . "/" . (1900 + $myTime['tm_year']);
		$message = "";
		//$message .= " - {$impresora}";
		$detalleTicket = $data->detalleTicket;
		if(isset($detalleTicket) && count($detalleTicket) > 0) {
			if($impresora == 1) {
				try {
					$connector = null;
					$connector = new WindowsPrintConnector("EPSONT20");
					//$connector = new WindowsPrintConnector("SamsungM2020");
					//$connector = new WindowsPrintConnector("faltaDato");
					$profile = DefaultCapabilityProfile::getInstance();
					$printer = new Printer($connector);
					//$atendio = $atendio == null ? $this->db->queryOne("SELECT u.alias FROM usuario u INNER JOIN cobro c on u.id = c.idUsuario INNER JOIN nota n on n.claveCobro = c.clave WHERE n.numero = '{$numero}'") : $atendio;
						
					try {
						//INICIA CONFIGURACION DEL TICKET
						$img = EscposImage::load('../assets/images/pcsol-logo.png', false);
						$printer -> setJustification(Printer::JUSTIFY_CENTER);
						$printer -> graphics($img);
						$printer -> feed();
						$printer -> text("MEDRANO #2799 RESIDENCIAL DEL PARQUE C.P. 44810\n");
						$printer -> text("GUADALAJARA JAL. TEL(33)33319708\n");
						$printer -> text("ERNESTO MONTORO RODRIGUEZ\n");
						$printer -> text("R.F.C MORE820615PI1\n");
						$printer -> text("TICKET# ");
						$this->bold($printer, $this->fixFieldSize($numero, 7, 'L', '0'));
						$printer -> text(" FECHA:");
						$this->bold($printer, $this->fixFieldSize($fecha, 11, 'L', '0'));
						$printer -> text("\n");
						$printer -> text("------------------------------------------------");
						$printer -> text("CODIGO CONCEPTO        PRECIO/UN CANT.   IMPORTE");
						$printer -> text("------------------------------------------------");
			
						foreach ($detalleTicket as $item) {
							$printer -> text($this->fixFieldSize($item->codigo, 6, '', '0') . " ");
							$printer -> text($this->fixFieldSize($item->descripcion, 15, 'R') . " ");
							$printer -> text($this->formatPrecio($item->precio) . " ");
							$printer -> text($this->formatCantidad($item->cantidad) . " ");
							$printer -> text($this->formatPrecio($item->importe));
						}
						$printer -> text("------------------------------------------------");
						if ($item->iva > 0) {
							$printer -> text($this->fixFieldSize("SUBTOTAL", 38) . " " . $this->formatPrecio($item->subtotal) . "\n");
							$printer -> text($this->fixFieldSize("IVA", 38) . " " . $this->formatPrecio($item->iva) . "\n");
						}
						$this->bold($printer, $this->fixFieldSize("TOTAL", 38) . " " . $this->formatPrecio($item->total) . "\n");
						$printer -> text($this->fixFieldSize("RECIBE", 38) . " " . $this->formatPrecio($entregado) . "\n");
						$printer -> text($this->fixFieldSize("CAMBIO", 38) . " " . $this->formatPrecio($cambio) . "\n");
						$totalf = number_format($item->total, 2, '.', '');
						$enletra = $this->num2letras($totalf);
						$printer -> setJustification(Printer::JUSTIFY_LEFT);
						$printer -> text("\n{$enletra}\n");
						$printer -> setJustification(Printer::JUSTIFY_CENTER);
						if(isset($atendio)) {
							$printer -> text("\n\nLe atendió {$atendio}.\n");
						}
						$printer -> text("------------------------------------------------");
						$this->bold($printer, "Gracias por su Visita.\n");
						$printer -> text("Recuerde conservar este ticket como comprobante\n");
						$printer -> text("para cualquier aclaración o garantía.\n");
						$printer -> text("------------------------------------------------");
						$printer -> feed();
						//FIN CONFIGURACION DEL TICKET
						//INICIAN PRUEBAS
						//FIN DE PRUEBAS
						$printer -> cut();
						//$printer -> pulse();
					} catch (Exception $e2) {
						/* Images not supported on your PHP, or image file not found */
						$message .= $printer->text($e2->getMessage() . "\n");
					}
					$printer -> close();
				} catch(Exception $e1) {
					$message = $e1->getMessage();
				}
			} else {
				try {
					$printer = "\\\\SERVER\\POS58";
					if ($ph = @printer_open($printer)) {
						$atendio = $atendio == null ? $this->db->queryOne("SELECT u.alias FROM usuario u INNER JOIN cobro c on u.id = c.idUsuario INNER JOIN nota n on n.claveCobro = c.clave WHERE n.numero = '{$numero}'") : $atendio;
							
						printer_set_option($ph, PRINTER_MODE, "RAW");
						//PRINT HEADER
						printer_write($ph, $this->printWithFormat('P C-S O L U C I O N E S', 'C'));
						printer_write($ph, "\n\r");
						printer_write($ph, $this->printWithFormat('MEDRANO #2799 RESIDENCIAL DEL', 'C'));
						printer_write($ph, $this->printWithFormat('PARQUE 44810 TEL (33)33319708', 'C'));
						//printer_write($ph, printWithFormat('CEL (044)3314862595', 'C'));
						printer_write($ph, $this->printWithFormat('ERNESTO MONTORO RODRIGUEZ', 'C'));
						printer_write($ph, $this->printWithFormat('R.F.C: MORE820615PI1', 'C'));
						printer_write($ph, $this->printWithFormat('TICKET#' . $this->fixFieldSize($numero, 7, 'L', '0') . " FECHA:" . $this->fixFieldSize($fecha, 11, 'L', '0')));
						//FIN HEADER
						printer_write($ph, $this->printWithFormat("--------------------------------"));
						printer_write($ph, $this->printWithFormat("NUM CODIGO ARTICULO             "));
							
							
						$i = 1;
						foreach ($detalleTicket as $item) {
							$line = "";
							$line .= $this->formatIndex($i);
							$line .= $this->fixFieldSize($item->codigo, 6, '', '0');
							$line .= ":";
							$line .= $this->fixFieldSize($item->descripcion, 21, 'R');
							printer_write($ph, $this->printWithFormat($line));
							$i ++;
						}
							
						printer_write($ph, $this->printWithFormat("--------------------------------"));
						printer_write($ph, $this->printWithFormat("NUM PRECIO/U  CANTIDAD   IMPORTE"));
							
						$i = 1;
						foreach ($detalleTicket as $item) {
								
							$line = "";
							$line .= $this->formatIndex($i);
							$line .= "$";
							$line .= $this->fixFieldSize(number_format($item->precio, 2, '.', ','));
							$line .= "  ";
							$line .= $this->fixFieldSize($item->cantidad, 5, 'R');
							$line .= " ";
							$line .= "$";
							$line .= $this->fixFieldSize(number_format($item->importe, 2, '.', ','));
							printer_write($ph, $this->printWithFormat($line));
							$i ++;
						}
							
						printer_write($ph, $this->printWithFormat("--------------------------------"));
							
						//printTotal($ph, $item, $recibe);
						$line = "";
						if ($item->iva > 0) {
							$line .= "SUBTOTAL              $";
							$line .= $this->fixFieldSize(number_format($item->subtotal, 2, '.', ','));
							printer_write($ph, $this->printWithFormat($line));
							$line = "";
							$line .= "IVA                   $";
							$line .= $this->fixFieldSize(number_format($item->iva, 2, '.', ','));
							printer_write($ph, $this->printWithFormat($line));
						}
						$line = "";
						$line .= "TOTAL                 $";
						$line .= $this->fixFieldSize(number_format($item->total, 2, '.', ','));
						printer_write($ph, $this->printWithFormat($line));
						$line = "";
						$line .= "RECIBE                $";
						$line .= $this->fixFieldSize(number_format($entregado, 2, '.', ','));
						printer_write($ph, $this->printWithFormat($line));
						$line = "";
						$line .= "CAMBIO                $";
						$line .= $this->fixFieldSize(number_format($cambio, 2, '.', ','));
						printer_write($ph, $this->printWithFormat($line));
							
						//FOOTER
						printer_write($ph, "\n\r");
						printer_write($ph, $this->printWithFormat('Gracias por su Visita', 'C'));
						printer_write($ph, "\n\r");
						printer_write($ph, $this->printWithFormat(  'Recuerde conservar este', 'C'));
						printer_write($ph, $this->printWithFormat(  'comprobante para cualquier', 'C'));
						printer_write($ph, $this->printWithFormat(  'aclaración o garantía.', 'C'));
						//Avanzar papel
						printer_write($ph, "\n\r\n\r\n\r\n\r\n\r");
						//FIN
						printer_close($ph);
					} else {
						$message .= "No se pudo conectar con la impresora.";
					}
				} catch(Exception $e1) {
					$message .= $e1->getMessage();
				}
			}
		} else {
			$message = "La nota parece estar vacia.";
		}
		return $message;
	}
	
	
	public function printTicket($numero = 0, $entregado, $cambio, $fechaCobro, $atendio = null, $impresora = 1) {
		$mode = "prod";
		date_default_timezone_set("America/Mexico_City");
		$myTime = localtime(strtotime($fechaCobro), true);
		$fecha = $myTime['tm_mday'] . "/" . $this->meses[$myTime['tm_mon']] . "/" . (1900 + $myTime['tm_year']);
		$message = "";
		//$message .= " - {$impresora}";
		$detalleTicket = $this->detalleTicket($numero);
		if(isset($detalleTicket) && count($detalleTicket) > 0) {
			if($impresora == 1) {
				try {
					$connector = null;
					$connector = new WindowsPrintConnector("EPSONT20");
					//$connector = new WindowsPrintConnector("SamsungM2020");
					//$connector = new WindowsPrintConnector("faltaDato");
					$profile = DefaultCapabilityProfile::getInstance();
					$printer = new Printer($connector);
					$atendio = $atendio == null ? $this->db->queryOne("SELECT u.alias FROM usuario u INNER JOIN cobro c on u.id = c.idUsuario INNER JOIN nota n on n.claveCobro = c.clave WHERE n.numero = '{$numero}'") : $atendio;
						
					try {
						//INICIA CONFIGURACION DEL TICKET
						$img = EscposImage::load('../assets/images/pcsol-logo.png', false);
						$printer -> setJustification(Printer::JUSTIFY_CENTER);
						$printer -> graphics($img);
						$printer -> feed();
						$printer -> text("MEDRANO #2799 RESIDENCIAL DEL PARQUE C.P. 44810\n");
						$printer -> text("GUADALAJARA JAL. TEL(33)33319708\n");
						$printer -> text("ERNESTO MONTORO RODRIGUEZ\n");
						$printer -> text("R.F.C MORE820615PI1\n");
						$printer -> text("TICKET# ");
						$this->bold($printer, $this->fixFieldSize($numero, 7, 'L', '0'));
						$printer -> text(" FECHA:");
						$this->bold($printer, $this->fixFieldSize($fecha, 11, 'L', '0'));
						$printer -> text("\n");
						$printer -> text("------------------------------------------------");
						$printer -> text("CODIGO CONCEPTO        PRECIO/UN CANT.   IMPORTE");
						$printer -> text("------------------------------------------------");
			
						foreach ($detalleTicket as $item) {
							$printer -> text($this->fixFieldSize($item->codigo, 6, '', '0') . " ");
							$printer -> text($this->fixFieldSize($item->descripcion, 15, 'R') . " ");
							$printer -> text($this->formatPrecio($item->precio) . " ");
							$printer -> text($this->formatCantidad($item->cantidad) . " ");
							$printer -> text($this->formatPrecio($item->importe));
						}
						$printer -> text("------------------------------------------------");
						if ($item->iva > 0) {
							$printer -> text($this->fixFieldSize("SUBTOTAL", 38) . " " . $this->formatPrecio($item->subtotal) . "\n");
							$printer -> text($this->fixFieldSize("IVA", 38) . " " . $this->formatPrecio($item->iva) . "\n");
						}
						$this->bold($printer, $this->fixFieldSize("TOTAL", 38) . " " . $this->formatPrecio($item->total) . "\n");
						$printer -> text($this->fixFieldSize("RECIBE", 38) . " " . $this->formatPrecio($entregado) . "\n");
						$printer -> text($this->fixFieldSize("CAMBIO", 38) . " " . $this->formatPrecio($cambio) . "\n");
						$totalf = number_format($item->total, 2, '.', '');
						$enletra = $this->num2letras($totalf);
						$printer -> setJustification(Printer::JUSTIFY_LEFT);
						$printer -> text("\n{$enletra}\n");
						$printer -> setJustification(Printer::JUSTIFY_CENTER);
						if(isset($atendio)) {
							$printer -> text("\n\nLe atendió {$atendio}.\n");
						}
						$printer -> text("------------------------------------------------");
						$this->bold($printer, "Gracias por su Visita.\n");
						$printer -> text("Recuerde conservar este ticket como comprobante\n");
						$printer -> text("para cualquier aclaración o garantía.\n");
						$printer -> text("------------------------------------------------");
						$printer -> feed();
						//FIN CONFIGURACION DEL TICKET
						//INICIAN PRUEBAS
						//FIN DE PRUEBAS
						$printer -> cut();
						//$printer -> pulse();
					} catch (Exception $e2) {
						/* Images not supported on your PHP, or image file not found */
						$message .= $printer->text($e2->getMessage() . "\n");
					}
					$printer -> close();
				} catch(Exception $e1) {
					$message = $e1->getMessage();
				}
			} else {
				try {
					$printer = "\\\\SERVER\\POS58";
					if ($ph = @printer_open($printer)) {
						$atendio = $atendio == null ? $this->db->queryOne("SELECT u.alias FROM usuario u INNER JOIN cobro c on u.id = c.idUsuario INNER JOIN nota n on n.claveCobro = c.clave WHERE n.numero = '{$numero}'") : $atendio;
							
						printer_set_option($ph, PRINTER_MODE, "RAW");
						//PRINT HEADER
						printer_write($ph, $this->printWithFormat('P C-S O L U C I O N E S', 'C'));
						printer_write($ph, "\n\r");
						printer_write($ph, $this->printWithFormat('MEDRANO #2799 RESIDENCIAL DEL', 'C'));
						printer_write($ph, $this->printWithFormat('PARQUE 44810 TEL (33)33319708', 'C'));
						//printer_write($ph, printWithFormat('CEL (044)3314862595', 'C'));
						printer_write($ph, $this->printWithFormat('ERNESTO MONTORO RODRIGUEZ', 'C'));
						printer_write($ph, $this->printWithFormat('R.F.C: MORE820615PI1', 'C'));
						printer_write($ph, $this->printWithFormat('TICKET#' . $this->fixFieldSize($numero, 7, 'L', '0') . " FECHA:" . $this->fixFieldSize($fecha, 11, 'L', '0')));
						//FIN HEADER
						printer_write($ph, $this->printWithFormat("--------------------------------"));
						printer_write($ph, $this->printWithFormat("NUM CODIGO ARTICULO             "));
							
							
						$i = 1;
						foreach ($detalleTicket as $item) {
							$line = "";
							$line .= $this->formatIndex($i);
							$line .= $this->fixFieldSize($item->codigo, 6, '', '0');
							$line .= ":";
							$line .= $this->fixFieldSize($item->descripcion, 21, 'R');
							printer_write($ph, $this->printWithFormat($line));
							$i ++;
						}
							
						printer_write($ph, $this->printWithFormat("--------------------------------"));
						printer_write($ph, $this->printWithFormat("NUM PRECIO/U  CANTIDAD   IMPORTE"));
							
						$i = 1;
						foreach ($detalleTicket as $item) {
								
							$line = "";
							$line .= $this->formatIndex($i);
							$line .= "$";
							$line .= $this->fixFieldSize(number_format($item->precio, 2, '.', ','));
							$line .= "  ";
							$line .= $this->fixFieldSize($item->cantidad, 5, 'R');
							$line .= " ";
							$line .= "$";
							$line .= $this->fixFieldSize(number_format($item->importe, 2, '.', ','));
							printer_write($ph, $this->printWithFormat($line));
							$i ++;
						}
							
						printer_write($ph, $this->printWithFormat("--------------------------------"));
							
						//printTotal($ph, $item, $recibe);
						$line = "";
						if ($item->iva > 0) {
							$line .= "SUBTOTAL              $";
							$line .= $this->fixFieldSize(number_format($item->subtotal, 2, '.', ','));
							printer_write($ph, $this->printWithFormat($line));
							$line = "";
							$line .= "IVA                   $";
							$line .= $this->fixFieldSize(number_format($item->iva, 2, '.', ','));
							printer_write($ph, $this->printWithFormat($line));
						}
						$line = "";
						$line .= "TOTAL                 $";
						$line .= $this->fixFieldSize(number_format($item->total, 2, '.', ','));
						printer_write($ph, $this->printWithFormat($line));
						$line = "";
						$line .= "RECIBE                $";
						$line .= $this->fixFieldSize(number_format($entregado, 2, '.', ','));
						printer_write($ph, $this->printWithFormat($line));
						$line = "";
						$line .= "CAMBIO                $";
						$line .= $this->fixFieldSize(number_format($cambio, 2, '.', ','));
						printer_write($ph, $this->printWithFormat($line));
							
						//FOOTER
						printer_write($ph, "\n\r");
						printer_write($ph, $this->printWithFormat('Gracias por su Visita', 'C'));
						printer_write($ph, "\n\r");
						printer_write($ph, $this->printWithFormat(  'Recuerde conservar este', 'C'));
						printer_write($ph, $this->printWithFormat(  'comprobante para cualquier', 'C'));
						printer_write($ph, $this->printWithFormat(  'aclaración o garantía.', 'C'));
						//Avanzar papel
						printer_write($ph, "\n\r\n\r\n\r\n\r\n\r");
						//FIN
						printer_close($ph);
					} else {
						$message .= "No se pudo conectar con la impresora.";
					}
				} catch(Exception $e1) {
					$message .= $e1->getMessage();
				}
			}
		} else {
			$message = "La nota parece estar vacia.";
		}
		return $message;
	}
	
	/*
	public function printTicket2($numero = 0, $entregado, $cambio, $fechaCobro, $atendio = null) {
		
		$message = "";
		date_default_timezone_set("America/Mexico_City");
		$mode = "prod";
		$myTime = localtime(strtotime($fechaCobro), true);
		$fecha = $myTime['tm_mday'] . "/" . $this->meses[$myTime['tm_mon']] . "/" . (1900 + $myTime['tm_year']);
		$detalleTicket = $this->detalleTicket($numero);
		
		if(isset($detalleTicket) && count($detalleTicket) > 0) {
			try {
				$printer = "\\\\SERVER\\POS58";
				if ($ph = @printer_open($printer)) {
					$atendio = $atendio == null ? $this->db->queryOne("SELECT u.alias FROM usuario u INNER JOIN cobro c on u.id = c.idUsuario INNER JOIN nota n on n.claveCobro = c.clave WHERE n.numero = '{$numero}'") : $atendio;
				
					printer_set_option($ph, PRINTER_MODE, "RAW");
					//PRINT HEADER
					printer_write($ph, $this->printWithFormat('P C-S O L U C I O N E S', 'C'));
					printer_write($ph, "\n\r");
					printer_write($ph, $this->printWithFormat('MEDRANO #2799 RESIDENCIAL DEL', 'C'));
					printer_write($ph, $this->printWithFormat('PARQUE 44810 TEL (33)33319708', 'C'));
					//printer_write($ph, printWithFormat('CEL (044)3314862595', 'C'));
					printer_write($ph, $this->printWithFormat('ERNESTO MONTORO RODRIGUEZ', 'C'));
					printer_write($ph, $this->printWithFormat('R.F.C: MORE820615PI1', 'C'));
					printer_write($ph, $this->printWithFormat('TICKET#' . $this->fixFieldSize($numero, 7, 'L', '0') . " FECHA:" . $this->fixFieldSize($fecha, 11, 'L', '0')));
					//FIN HEADER
					printer_write($ph, $this->printWithFormat("--------------------------------"));
					printer_write($ph, $this->printWithFormat("NUM CODIGO ARTICULO             "));
						
						
					$i = 1;
					foreach ($detalleTicket as $item) {
						$line = "";
						$line .= $this->formatIndex($i);
						$line .= $this->fixFieldSize($item->codigo, 6, '', '0');
						$line .= ":";
						$line .= $this->fixFieldSize($item->descripcion, 21, 'R');
						printer_write($ph, $this->printWithFormat($line));
						$i ++;
					}
						
					printer_write($ph, $this->printWithFormat("--------------------------------"));
					printer_write($ph, $this->printWithFormat("NUM PRECIO/U  CANTIDAD   IMPORTE"));
						
					$i = 1;
					foreach ($detalleTicket as $item) {
							
						$line = "";
						$line .= $this->formatIndex($i);
						$line .= "$";
						$line .= $this->fixFieldSize(number_format($item->precio, 2, '.', ','));
						$line .= "  ";
						$line .= $this->fixFieldSize($item->cantidad, 5, 'R');
						$line .= " ";
						$line .= "$";
						$line .= $this->fixFieldSize(number_format($item->importe, 2, '.', ','));
						printer_write($ph, $this->printWithFormat($line));
						$i ++;
					}
						
					printer_write($ph, $this->printWithFormat("--------------------------------"));
						
					//printTotal($ph, $item, $recibe);
					$line = "";
					if ($item->iva > 0) {
						$line .= "SUBTOTAL              $";
						$line .= $this->fixFieldSize(number_format($item->subtotal, 2, '.', ','));
						printer_write($ph, $this->printWithFormat($line));
						$line = "";
						$line .= "IVA                   $";
						$line .= $this->fixFieldSize(number_format($item->iva, 2, '.', ','));
						printer_write($ph, $this->printWithFormat($line));
					}
					$line = "";
					$line .= "TOTAL                 $";
					$line .= $this->fixFieldSize(number_format($item->total, 2, '.', ','));
					printer_write($ph, $this->printWithFormat($line));
					$line = "";
					$line .= "RECIBE                $";
					$line .= $this->fixFieldSize(number_format($entregado, 2, '.', ','));
					printer_write($ph, $this->printWithFormat($line));
					$line = "";
					$line .= "CAMBIO                $";
					$line .= $this->fixFieldSize(number_format($cambio, 2, '.', ','));
					printer_write($ph, $this->printWithFormat($line));
						
					//FOOTER
					printer_write($ph, "\n\r");
					printer_write($ph, $this->printWithFormat('Gracias por su Visita', 'C'));
					printer_write($ph, "\n\r");
					printer_write($ph, $this->printWithFormat(  'Recuerde conservar este', 'C'));
					printer_write($ph, $this->printWithFormat(  'comprobante para cualquier', 'C'));
					printer_write($ph, $this->printWithFormat(  'aclaración o garantía.', 'C'));
					//Avanzar papel
					printer_write($ph, "\n\r\n\r\n\r\n\r\n\r");
					//FIN
					printer_close($ph);
				} else {
					$message .= "No se pudo conectar con la impresora.";
				}
			} catch(Exception $e1) {
				$message .= $e1->getMessage();
			}
		}
		return $message;
	}
	
	public function printTicket1($numero = 0, $entregado, $cambio, $fechaCobro, $atendio = null) {
		$connector = null;
		$message = "";
		try {
			//$connector = new WindowsPrintConnector("EPSONT20");
			//$connector = new WindowsPrintConnector("SamsungM2020");
			$connector = new WindowsPrintConnector("faltaDato");
			$profile = DefaultCapabilityProfile::getInstance();
			$printer = new Printer($connector);
				
			date_default_timezone_set("America/Mexico_City");
				
			$mode = "prod";
			$myTime = localtime(strtotime($fechaCobro), true);
			$fecha = $myTime['tm_mday'] . "/" . $this->meses[$myTime['tm_mon']] . "/" . (1900 + $myTime['tm_year']);
			$detalleTicket = $this->detalleTicket($numero);
				
			if(isset($detalleTicket) && count($detalleTicket) > 0) {
	
				$atendio = $atendio == null ? $this->db->queryOne("SELECT u.alias FROM usuario u INNER JOIN cobro c on u.id = c.idUsuario INNER JOIN nota n on n.claveCobro = c.clave WHERE n.numero = '{$numero}'") : $atendio;
	
				try {
					//INICIA CONFIGURACION DEL TICKET
					$img = EscposImage::load('../assets/images/pcsol-logo.png', false);
					$printer -> setJustification(Printer::JUSTIFY_CENTER);
					$printer -> graphics($img);
					$printer -> feed();
					$printer -> text("MEDRANO #2799 RESIDENCIAL DEL PARQUE C.P. 44810\n");
					$printer -> text("GUADALAJARA JAL. TEL(33)33319708\n");
					$printer -> text("ERNESTO MONTORO RODRIGUEZ\n");
					$printer -> text("R.F.C MORE820615PI1\n");
					$printer -> text("TICKET# ");
					$this->bold($printer, $this->fixFieldSize($numero, 7, 'L', '0'));
					$printer -> text(" FECHA:");
					$this->bold($printer, $this->fixFieldSize($fecha, 11, 'L', '0'));
					$printer -> text("\n");
					$printer -> text("------------------------------------------------");
					$printer -> text("CODIGO CONCEPTO        PRECIO/UN CANT.   IMPORTE");
					$printer -> text("------------------------------------------------");
						
					foreach ($detalleTicket as $item) {
						$printer -> text($this->fixFieldSize($item->codigo, 6, '', '0') . " ");
						$printer -> text($this->fixFieldSize($item->descripcion, 15, 'R') . " ");
						$printer -> text($this->formatPrecio($item->precio) . " ");
						$printer -> text($this->formatCantidad($item->cantidad) . " ");
						$printer -> text($this->formatPrecio($item->importe));
					}
					$printer -> text("------------------------------------------------");
					if ($item->iva > 0) {
						$printer -> text($this->fixFieldSize("SUBTOTAL", 38) . " " . $this->formatPrecio($item->subtotal) . "\n");
						$printer -> text($this->fixFieldSize("IVA", 38) . " " . $this->formatPrecio($item->iva) . "\n");
					}
					$this->bold($printer, $this->fixFieldSize("TOTAL", 38) . " " . $this->formatPrecio($item->total) . "\n");
					$printer -> text($this->fixFieldSize("RECIBE", 38) . " " . $this->formatPrecio($entregado) . "\n");
					$printer -> text($this->fixFieldSize("CAMBIO", 38) . " " . $this->formatPrecio($cambio) . "\n");
					$totalf = number_format($item->total, 2, '.', '');
					$enletra = $this->num2letras($totalf);
					$printer -> setJustification(Printer::JUSTIFY_LEFT);
					$printer -> text("\n{$enletra}\n");
					$printer -> setJustification(Printer::JUSTIFY_CENTER);
					if(isset($atendio)) {
						$printer -> text("\n\nLe atendió {$atendio}.\n");
					}
					$printer -> text("------------------------------------------------");
					$this->bold($printer, "Gracias por su Visita.\n");
					$printer -> text("Recuerde conservar este ticket como comprobante\n");
					$printer -> text("para cualquier aclaración o garantía.\n");
					$printer -> text("------------------------------------------------");
					$printer -> feed();
					//FIN CONFIGURACION DEL TICKET
	
					//INICIAN PRUEBAS
					//FIN DE PRUEBAS
						
					$printer -> cut();
					//$printer -> pulse();
				} catch (Exception $e2) {
					//Images not supported on your PHP, or image file not found
					$message .= $printer->text($e2->getMessage() . "\n");
				}
				$printer -> close();
			}
		} catch(Exception $e1) {
			$message = $e1->getMessage();
		}
		return $message;
	}
	*/
	
	/* INICIA DECLARACION DE FUNCIONES AUXILIARES */
	private function fixFieldSize($_data, $length = 9, $side = 'L', $car = ' ') {
		$_br = '';
		$_size = strlen( $_data );
		if( $_size < $length ) {
			for( $i = 0; $i < $length - $_size; $i ++ ) {
				$_br .= $car;
			}
			if ($side == 'R') {
				$_br = $_data . $_br;
			} else {
				$_br .= $_data;
			}
		} else if ($_size > $length) {
			$_br = substr($_data, 0, $length);
		} else {
			$_br = $_data;
		}
		return $_br;
	}
	private function formatPrecio($precio) {
		$precio = number_format($precio, 2, '.', ',');
		return $this->fixFieldSize($precio);
	}
	private function formatCantidad($cantidad) {
		if($cantidad < 10) {
			return "  " . $cantidad . "  ";
		}
		if($cantidad < 100) {
			return "  " . $cantidad . " ";
		}
		if($cantidad < 1000) {
			return " " . $cantidad . " ";
		}
		if($cantidad < 10000) {
			return $cantidad . " ";
		}
		return $cantidad;
	}
	private function bold(Printer $printer, $text)
	{
		$printer -> setEmphasis(true);
		$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
		$printer -> text($text);
		$printer -> setEmphasis(false);
	}
	/*!
	 @function num2letras ()
	 @abstract Dado un n?mero lo devuelve escrito.
	 @param $num number - N?mero a convertir.
	 @param $fem bool - Forma femenina (true) o no (false).
	 @param $dec bool - Con decimales (true) o no (false).
	 @result string - Devuelve el n?mero escrito en letra.
	
	 */
	private function num2letras($num, $fem = false, $dec = true) {
		$matuni[2]  = "dos";
		$matuni[3]  = "tres";
		$matuni[4]  = "cuatro";
		$matuni[5]  = "cinco";
		$matuni[6]  = "seis";
		$matuni[7]  = "siete";
		$matuni[8]  = "ocho";
		$matuni[9]  = "nueve";
		$matuni[10] = "diez";
		$matuni[11] = "once";
		$matuni[12] = "doce";
		$matuni[13] = "trece";
		$matuni[14] = "catorce";
		$matuni[15] = "quince";
		$matuni[16] = "dieciseis";
		$matuni[17] = "diecisiete";
		$matuni[18] = "dieciocho";
		$matuni[19] = "diecinueve";
		$matuni[20] = "veinte";
		$matunisub[2] = "dos";
		$matunisub[3] = "tres";
		$matunisub[4] = "cuatro";
		$matunisub[5] = "quin";
		$matunisub[6] = "seis";
		$matunisub[7] = "sete";
		$matunisub[8] = "ocho";
		$matunisub[9] = "nove";
	
		$matdec[2] = "veint";
		$matdec[3] = "treinta";
		$matdec[4] = "cuarenta";
		$matdec[5] = "cincuenta";
		$matdec[6] = "sesenta";
		$matdec[7] = "setenta";
		$matdec[8] = "ochenta";
		$matdec[9] = "noventa";
		$matsub[3]  = 'mill';
		$matsub[5]  = 'bill';
		$matsub[7]  = 'mill';
		$matsub[9]  = 'trill';
		$matsub[11] = 'mill';
		$matsub[13] = 'bill';
		$matsub[15] = 'mill';
		$matmil[4]  = 'millones';
		$matmil[6]  = 'billones';
		$matmil[7]  = 'de billones';
		$matmil[8]  = 'millones de billones';
		$matmil[10] = 'trillones';
		$matmil[11] = 'de trillones';
		$matmil[12] = 'millones de trillones';
		$matmil[13] = 'de trillones';
		$matmil[14] = 'billones de trillones';
		$matmil[15] = 'de billones de trillones';
		$matmil[16] = 'millones de billones de trillones';
			
		//Zi hack
		$float=explode('.',$num);
		$num=$float[0];
	
		$num = trim((string)@$num);
		if ($num[0] == '-') {
			$neg = 'menos ';
			$num = substr($num, 1);
		}else
			$neg = '';
			while ($num[0] == '0') $num = substr($num, 1);
			if ($num[0] < '1' or $num[0] > 9) $num = '0' . $num;
			$zeros = true;
			$punt = false;
			$ent = '';
			$fra = '';
			for ($c = 0; $c < strlen($num); $c++) {
				$n = $num[$c];
				if (! (strpos(".,'''", $n) === false)) {
					if ($punt) break;
					else{
						$punt = true;
						continue;
					}
	
				}elseif (! (strpos('0123456789', $n) === false)) {
					if ($punt) {
						if ($n != '0') $zeros = false;
						$fra .= $n;
					}else
	
						$ent .= $n;
				}else
	
					break;
	
			}
			$ent = '     ' . $ent;
			if ($dec and $fra and ! $zeros) {
				$fin = ' coma';
				for ($n = 0; $n < strlen($fra); $n++) {
					if (($s = $fra[$n]) == '0')
						$fin .= ' cero';
						elseif ($s == '1')
						$fin .= $fem ? ' una' : ' un';
						else
							$fin .= ' ' . $matuni[$s];
				}
			}else
				$fin = '';
				if ((int)$ent === 0) return 'Cero ' . $fin;
				$tex = '';
				$sub = 0;
				$mils = 0;
				$neutro = false;
				while ( ($num = substr($ent, -3)) != '   ') {
					$ent = substr($ent, 0, -3);
					if (++$sub < 3 and $fem) {
						$matuni[1] = 'una';
						$subcent = 'as';
					}else{
						$matuni[1] = $neutro ? 'un' : 'uno';
						$subcent = 'os';
					}
					$t = '';
					$n2 = substr($num, 1);
					if ($n2 == '00') {
					}elseif ($n2 < 21)
					$t = ' ' . $matuni[(int)$n2];
					elseif ($n2 < 30) {
						$n3 = $num[2];
						if ($n3 != 0) $t = 'i' . $matuni[$n3];
						$n2 = $num[1];
						$t = ' ' . $matdec[$n2] . $t;
					}else{
						$n3 = $num[2];
						if ($n3 != 0) $t = ' y ' . $matuni[$n3];
						$n2 = $num[1];
						$t = ' ' . $matdec[$n2] . $t;
					}
					$n = $num[0];
					if ($n == 1) {
						$t = ' ciento' . $t;
					}elseif ($n == 5){
						$t = ' ' . $matunisub[$n] . 'ient' . $subcent . $t;
					}elseif ($n != 0){
						$t = ' ' . $matunisub[$n] . 'cient' . $subcent . $t;
					}
					if ($sub == 1) {
					}elseif (! isset($matsub[$sub])) {
						if ($num == 1) {
							$t = ' mil';
						}elseif ($num > 1){
							$t .= ' mil';
						}
					}elseif ($num == 1) {
						$t .= ' ' . $matsub[$sub] . '?n';
					}elseif ($num > 1){
						$t .= ' ' . $matsub[$sub] . 'ones';
					}
					if ($num == '000') $mils ++;
					elseif ($mils != 0) {
						if (isset($matmil[$sub])) $t .= ' ' . $matmil[$sub];
						$mils = 0;
					}
					$neutro = true;
					$tex = $t . $tex;
				}
				$tex = $neg . substr($tex, 1) . $fin;
				//Zi hack --> return ucfirst($tex);
				$end_num=ucfirst($tex).' pesos '.$float[1].'/100 M.N.';
				return $end_num;
	}
}