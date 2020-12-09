<?php header( 'Content-type: text/html; charset=UTF-8' );?>
<?php defined('PHRAPI') or die("Direct access not allowed!");

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\CapabilityProfiles\DefaultCapabilityProfile;
class Pcsoluciones {

	private $db;
	private $config;
	private $session;
	protected $info = array();
	public $projects;
	public $has_projects;
	public $has_investments;
	public $divisa_prefijo = "";
	public $divisa_sufijo = " â‚¬";
	public $paginador;
	public $valores_filtros;
	
	protected $estatus = array(
		'' => 'Esperando Pago',
		'Failure' => 'Sin Pago',
		'Success' => 'Pagado',
		'Process' => 'En proceso',
		'Canceled' => 'Cancelada',
	);
	
	protected $meses = array("Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic");
	
	public function __construct(){
		$this->config = $GLOBALS['config'];
		$this->db = DB::getInstance();
		$this->session = Session::getInstance();
		$this->persistent = Persistent::getInstance();
		$this->lang = $this->persistent->lang;

		/*if (!isset($this->session->logged) OR !$this->session->logged) {
			redirect("login.php");
		}*/
		if(!$this->isLogged()) {
			redirect("login.php");
		}
	}
	
	private function isLogged() {
		return isset($this->session->logged) OR $this->session->logged;
	}
	
	public function fixFolSize( $_data ) {
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
	
	public function CVA() {
		return simplexml_load_file("https://www.grupocva.com/catalogo_clientes_xml/lista_precios.xml?cliente=18706&marca=HP&grupo=%25impresora%25&clave=%25&codigo=%25");
		
	}
	
	public function terminarOrden() {
	
		$message = "Falta implementar la logica de terminado...";
		$id = getValueFrom($_POST, 'numord', '0', FILTER_SANITIZE_PHRAPI_INT);
		$code = 200;
		
		if(!isset($id) || $id < 1) {
			$code = 400;
			$message = "No se recibio el identificador de orden...";
		} else {
			if($id > 0) {
				$id = (int)$this->db->queryOne("select numero from orden_servicio where numero = {$id} AND estatus = 1");
				if($id > 0) {
					$this->db->query(
					"UPDATE orden_servicio set estatus = '2', fechaT = default, idEntrego = {$this->session->logged}" .
					" WHERE numero = {$id}");
					$message = "Orden terminada correctamente";
				} else {
					$message = "Parece que la orden ya ha habia sido terminada.";
				}
			}
		}
		return compact('code', 'id', 'message');
	}
	
	public function ordenPdf() {
		$folio = getValueFrom($_GET, 'folio', '0', FILTER_SANITIZE_PHRAPI_INT);
		/*$filaDatos = $this->db->queryRow(
				"SELECT
				if(os.accesorios is null or os.accesorios = '', os.descripcion, (CONCAT(os.descripcion, '\n\r\n\rSe entrega con:\n\r', os.accesorios))) descripcion, os.numero,
				date_format( os.fecha, '%d / %m / %Y' ) as fecha,
				ifnull (c.nombre_fiscal, concat( c.nombre, ' ', c.apellido1, ifnull(CONCAT(' ', c.apellido2), ''))) as nombre,
				concat( IF(d.calle IS NULL, '', d.calle), IF(d.numext IS NULL, '', CONCAT(' #', d.numext)), IF(d.numint IS NULL, '', CONCAT(' int. ', d.numint)), IF(d.cp IS NULL, '', CONCAT(' CP ', d.cp)), IF(d.colonia IS NULL, '', CONCAT(' Col ', d.colonia))) as domicilio, concat( d.ciudad, ', ', d.estado ) as ciudad,
				concat( t.numero, ' (', tt.nombre, ')') as telefono
				FROM orden_servicio os, cliente_domicilio cd, cliente_telefono ct, cliente c, domicilio d, telefono t, tipotelefono tt
				WHERE os.numero = '{$folio}' and os.idcliente = c.id and cd.idcliente = c.id and ct.idcliente = c.id and cd.claveDomicilio = d.clave
				and ct.claveTelefono = t.clave and ct.default = 1 and cd.default = 1 and tt.clave = t.tipo");*/
		$filaDatos = $this->db->queryRow(
				"SELECT
				if(os.accesorios is null or os.accesorios = '', os.descripcion, (CONCAT(os.descripcion, '\n\r\n\rSe entrega con:\n\r', os.accesorios))) descripcion, os.numero,
				date_format( os.fecha, '%d / %m / %Y' ) as fecha,
				if(c.nombre_fiscal is null OR c.nombre_fiscal = '', concat( c.nombre, ' ', c.apellido1, ifnull(CONCAT(' ', c.apellido2), '')), c.nombre_fiscal) as nombre,
				concat( IF(d.calle IS NULL, '', d.calle), IF(d.numext IS NULL, '', CONCAT(' #', d.numext)), IF(d.numint IS NULL, '', CONCAT(' int. ', d.numint)), IF(d.cp IS NULL, '', CONCAT(' CP ', d.cp)), IF(d.colonia IS NULL OR d.colonia = '', '', CONCAT(' Col ', d.colonia))) as domicilio, concat( d.ciudad, ', ', d.estado ) as ciudad,
				concat( t.numero, ' (', tt.nombre, ')') as telefono
				FROM orden_servicio os, cliente_domicilio cd, cliente_telefono ct, cliente c, domicilio d, telefono t, tipotelefono tt
				WHERE os.numero = '{$folio}' and os.idcliente = c.id and cd.idcliente = c.id and ct.idcliente = c.id and cd.claveDomicilio = d.clave
				and ct.claveTelefono = t.clave and ct.default = 1 and cd.default = 1 and tt.clave = t.tipo");
		if(isset($filaDatos)) {
		
				// Creaciï¿½n del objeto de la clase heredada
			$_nombre = "orden-servicio-$folio.pdf";
			//$pdf = new FPDF('P', 'mm', 'half-Letter');
			$pdf = new PDF('P', 'mm', 'half-Letter');
			$pdf->AliasNbPages();
			$pdf->AddPage();
			$pdf->SetAutoPageBreak( true );
			$pdf->SetFont('Arial','',11);
			//Plantilla
			//$pdf->Image("imgs/orden-servicio.jpg", 0, 0, 215.9 );
			//LOGO
			$pdf->Image('../assets/images/pcsol-logo.png', 6, 10, 75);
			
			//FECHA
			//Etiqueta
			$pdf->SetFont('Arial','',12);
			$pdf->SetXY( 175, 21 );
			$pdf->Cell(35, 3, "Fecha", 0, null, "C" );
			//$pdf->Rect(170, 29, 35, 5, 'B');
			//Dato
			$pdf->SetXY( 175, 25 );
			$pdf->SetFont('Arial','',11);
			$pdf->Cell(35, 5, $filaDatos->fecha, 0, null, "C" );
			
			//CODIGO DE BARRAS
			//include 'barcodegenerator.php';
			$pdf->SetXY( 155, 32 );
			//$pdf->Image("imgs/tempbarcodes/ximgbr$barnumber.gif", 170, 35 );
			//$pdf->Image("imgs/tempbarcodes/ximgbr$barnumber.gif", 164, 31);
			
			//CUADRO DE INFO, SE PONE AQUI PARA QUE QUEDE POR ENCIMA DEL CODIGO DE BARRAS
			//$pdf->Rect(81, 10, 85, 30, 'B');
			$pdf->SetFillColor(255);
			$pdf->RoundedRect(87, 10, 85, 30, 2, '1234', 'DF');
			//INFO
			//$pdf->Rect(81, 10, 85, 30, 'B');
			$pdf->SetXY( 87, 13 );
			$pdf->MultiCell(85, 4, utf8_decode("ERNESTO MONTORO RODRIGUEZ\nR.F.C MORE820615PI1\nCURP MORE820615HJCNDR00\nMEDRAN0 #2799\nCOL. RESIDENCIAL DEL PARQUE C.P. 44810\nTEL (33)33319708 CEL (044)3318421540"), 0, 'C');
			
			//NUMERO DE ORDEN
			//Etiqueta
			$pdf->SetFont('Arial','',12);
			$pdf->SetXY( 175, 10 );
			$pdf->Cell(35, 3, "Número de orden", 0, null, "C" );
			//Dato
			$pdf->SetXY( 175, 14 );
			$pdf->SetFont('Arial','',11);
			$pdf->Cell(35, 5, $this->fixFolSize($folio), 1, null, "C" );
			
			//Para calcular los espacios entre las sig lineas
			$_yposs = 45;
			$_yoffset = 5;
			//NOMBRE
			//Etiqueta
			$pdf->SetFont('Arial','',12);
			//Dato
			$pdf->SetXY( 8, $_yposs - 3);
			$pdf->Cell(200, 5, "Nombre: " . ucwords( utf8_decode($filaDatos->nombre) ), '0', null, "L" );
			
			//DIRECCION
			$_yposs += $_yoffset;
			//Dato
			$pdf->SetXY( 8, $_yposs - 3);
			$pdf->Cell(200, 5, "Telefono: " . $filaDatos->telefono, '0', null, "L" );
			
			//TELEFONO
			$_yposs += $_yoffset;
			//Dato
			$pdf->SetXY( 8, $_yposs - 3);
			$pdf->Cell(200, 5, "Dirección: " . ucwords( $filaDatos->domicilio != '' ? $filaDatos->domicilio : ' -  -  -  -  -  -  -  -  -  - ' ), '0', null, "L" );
			
			//DETALLE-ORDEN 1
			$pdf->SetXY( 8, 58 );
			$pdf->SetFont('Arial','', 12);
			$pdf->Cell(200, 5, "Descripción del equipo y/o servicio:", '0', null, "L" );
			//$pdf->Rect(7, 63, 202, 52, 'B');
			$pdf->RoundedRect(7, 63, 202, 52, 2, '1234', 'DF');
			$pdf->SetXY( 8, 66 );
			$pdf->SetFont('Arial','', 11);
			$pdf->MultiCell(200, 5, utf8_decode($filaDatos->descripcion), 0);
			
			//Terminos
			$pdf->SetFont('Arial','', 9);
			//$pdf->Rect(7, 115, 202, 20, 'B');
			$pdf->SetXY( 8, 116 );
			$pdf->MultiCell(200, 3, "Después de 15 dias de recibido el equipo se cobrará almacenaje(por día excedido). Después de 90 dias NO nos harémos responsables por el equipo. Para hacer válida la entrega, garantía o cualquier aclaración, es INDISPENSABLE presentar este comprobante.", 0, 'C');
			$pdf->SetXY( 80, 130 );
			$pdf->SetFont('Arial','', 10);
			$pdf->Cell(60, 5, "Firma de conformidad cliente", 'T', null, "C" );
			
			//$pdf->Output( $_destino_back . $_destino . $_nombre, 'D');
			$pdf->Output( $_nombre, 'D');
			exit;
		} else {
			echo "La orden de servicio no tiene los datos completos o no existe.";
		}
		return;
	}
	
	public function guardarNota() {
	
		$actualizar = false;
		$message = "Falta implementar la logica de guardado...";
		$data = json_decode($_POST['data']['json']);
	
		$code = 200;
		$id = -1;
		/*
		 * numero: 		$('#numnota').val(),
			folio: 			$('#folio').val(),
            idCliente: 		$('#numcli').val(),
            itemCantidad: 	itemCantidad,
            aplicarIva: 	aplicarIVA
		 */
		/*if(!isset($data->idCliente) || $data->idCliente == '') {
			$code = 400;
			$message = "No se recibio el identificador de usuario...";
		} else*/ if(!isset($data->itemCantidad) || $data->itemCantidad == '') {
			$code = 400;
			$message = "No se recibieron los datos de la nota...";
		} else {
			//Borramos el detalle si ya existe la nota...
			if(isset($data->numero) && $data->numero != '') {
				$id = (int)$this->db->queryOne("select numero from nota where numero = {$data->numero}");
				if($id > 0) {
					$actualizar = true;
					$this->db->query("DELETE from detalle_nota where folio = {$data->numero}");
				}
			}
			//CALCULAMOS ALGUNAS VARIABLES
			$subtotal = 0;
			$iva = 0;
			$total = 0;
			$itemsCantidad = explode( ',', $data->itemCantidad );
			$message = '';
			//Iteramos para obtener precios de los items del INVENTARIO y/o 'free'
			foreach( $itemsCantidad as $dato ) {
				//para Items INVENTARIO codigo, cantidad
				//para Items FREE	    codigo, descripcion, precio, cantidad
				$valores = explode( '||', $dato );
				$precio = 0;
				$cantidad = 0;
				//if( isset( $_SESSION['items'][ $valores[ 0 ] ] )) {
				if (count($valores) > 2) {
					//El item es 'free', no se busca en la BD
					$precio = $valores[2];
					$cantidad = $valores[3];
				} else {
					//Es item de INVENTARIO, buscar en BD
					$articulo = $this->db->queryRow("select * from articulo where codigo = '{$valores[0]}'");
					if($articulo && $articulo->cantidad > 0) {
						$precio = $articulo->precio;
						$cantidad = $valores[1];
					} else {
						$message .= "Articulo " . $articulo->corta . " ( CODIGO: " . $articulo->codigo . " ) 'SIN STOCK'<br />";
					}
				}
				$subtotal += $precio * $cantidad;
			}
			if ($message == "") {//No hubo ningun error de stock
				if($data->aplicarIva && $data->aplicarIva == "true" ) {
					$iva = $subtotal * .16;
				} else { $iva = 0; }
				$total = $subtotal + $iva;
				//VALORES DE NOTA
				$valoresNota = array(
					":folio" => $data->folio,
					":idCliente" => $data->idCliente,
					":subtotal" => $subtotal,
					":iva" => $iva,
					":total" => $total
				);
				if($actualizar) {
					$valoresNota[":numero"] = $data->numero;
					$this->db->query(
						"UPDATE nota set idCliente = :idCliente, folio = :folio, subtotal = :subtotal, iva = :iva, total = :total" .
						" WHERE numero = :numero", $valoresNota);
				} else {
					$valoresNota[":estatus"] = 1;
					//GUARDAR NOTA
					$this->db->query(
						"INSERT INTO nota set idCliente = :idCliente, folio = :folio, subtotal = :subtotal, iva = :iva, total = :total, estatus = :estatus",
						$valoresNota);;
					$id = $this->db->getLastID();
				}
				foreach( $itemsCantidad as $dato ) {//guardamos items de nota
						
					//para Items INVENTARIO codigo, cantidad
					//para Items FREE	    codigo, descripcion, precio, cantidad
					$valores = explode( '||', $dato );
					$valoresDetalle = array();
					$valoresDetalle[":folio"] = $id;
					if (count($valores) > 2) {
						//Items 'free'
						$valoresDetalle[":claveArticulo"] = 'free';
						$valoresDetalle[":descripcion"] = $valores[1];
						$valoresDetalle[":precio"] 	= $valores[2];
						$valoresDetalle[":cantidad"] = $valores[3];
						/*if (count($valores) > 4) {
							$valoresDetalle[":accesorio"] = $valores[4];
						}*/
					} else {
						//Items de INVENTARIO
						$valoresDetalle[":claveArticulo"] = $valores[0];
						$valoresDetalle[":cantidad"] = $valores[1];
						$valoresDetalle[":descripcion"] = '';
						$valoresDetalle[":precio"] 	= 0;
					}
					$this->db->query(
						"INSERT INTO detalle_nota set folio = :folio, claveArticulo = :claveArticulo, descripcion = :descripcion, precio = :precio, cantidad = :cantidad",
						$valoresDetalle);
					$message = "Nota guardada correctamente";
				}
			} else {
				$code = 400;
			}
		}
		return compact('code', 'id', 'message');
	}
	
	public function showTicket(){
		$message = "Falta implementar la logica de guardado...";
		$numero = getValueFrom($_GET, 'numero', '0', FILTER_SANITIZE_PHRAPI_INT);
		
		$ticket = new Ticket();
		$html = $ticket->showTicket($numero);
		echo $html;
		/*$code = 400;
		$id = $numero;
		return compact('code', 'id', 'message');*/
	}
	
	public function cobrarNota() {
	
		$message = "Falta implementar la logica de guardado...";
		$data = json_decode($_POST['data']['json']);
	
		$code = 400;
		$id = -1;
		
		if(!isset($data->numero) || $data->numero < 1 || !isset($data->entregado) || $data->entregado < 1) {
			$message = "No se recibieron los datos de la nota...";
		} else {
			//Verificamos el numero de nota
			if(isset($data->numero) && $data->numero != '') {
				$datos = $this->db->queryRow("SELECT numero, claveCobro, total FROM nota where numero = {$data->numero}");
				$id = (int)$datos->numero;
				if($id > 0) {
					$cc = (int)$datos->claveCobro;
					if($cc > 0) {
						$message = "La nota ya ha sido cobrada";
					} else {
						$cambio = $data->entregado - $datos->total;
						$valores = array(
							":numero" => $id,
							":total" => $datos->total,
							":entregado" => $data->entregado,
							":cambio" => $cambio,
							":idUsuario" => $this->session->logged,
						);
						//Generamos cobro
						$this->db->beginTransaction();
						$this->db->query(
						"INSERT INTO cobro SET claveNota = :numero, total = :total, entregado = :entregado, cambio = :cambio, idUsuario = :idUsuario", $valores);
						
						$idCobro = $this->db->getLastID();
						$datosCobro = $this->db->queryRow("SELECT * FROM cobro WHERE clave = {$idCobro}");
						
						//Actualizamos fecha y claveCobro en nota
						$valores = array(
								":cc" => $idCobro,
								":fecha" => $datosCobro->fechaCobro,
								":estatus" => 3,
								":numero" => $id
						);
						$this->db->query("UPDATE nota SET claveCobro = :cc, fechaCobro = :fecha, estatus = :estatus WHERE numero = :numero", $valores);
						
						//Actualizamos stock, esto deberia ir en un procedimiento almacenado
						$articulos = $this->db->queryAll("SELECT dn.claveArticulo, (a.cantidad - dn.cantidad) AS total FROM detalle_nota dn JOIN articulo a ON a.codigo = dn.claveArticulo WHERE dn.folio = {$id} AND dn.claveArticulo != 'free'");
						if(count($articulos) > 0) {
							foreach($articulos as $articulo) {
								$this->db->query("UPDATE articulo SET cantidad = {$articulo->total} WHERE codigo = '{$articulo->claveArticulo}'");
							}
						}
						$message = "Cobro registrado correctamente.";
						$code = 200;
						if(isset($data->imprimir) && $data->imprimir == 'true') {
							//$message .= $this->printTicket($id, $data->entregado, $cambio, $datosCobro->fechaCobro);
							$ticket = new Ticket();
							$result = $ticket->printTicket($id, $data->entregado, $cambio, $datosCobro->fechaCobro, null, $data->tipo);
							if($result == '') {
								$message .= " Ticket Impreso O.K. ";
								$this->db->query("UPDATE nota SET estatus = 2 WHERE numero = {$id}");
								$this->db->query("UPDATE cobro SET impresora = {$data->tipo} WHERE clave = {$idCobro}");
							} else {
								$message .= " No se pudo imprimir el ticket: [{$result}]";
							}
						}
						$this->db->commit();
					}
				} else {
					$message = "Id de nota invalido...";
				}
			}
		}
		return compact('code', 'id', 'message');
	}
	
	public function printTicket() {
		$message = "Falta implementar la logica de guardado...";
		$id = getValueFrom($_POST, 'numero', 0, FILTER_SANITIZE_PHRAPI_INT);
		$code = 400;
		//$id = -1;
		if(!isset($id) || $id < 1) {
			$message = "No se recibio el id de nota...";
		} else {
			$message = "";
			$data = $this->db->queryRow("SELECT entregado, fechaCobro, cambio, ifnull(impresora, 2) impresora, u.alias FROM cobro JOIN usuario u ON idUsuario = u.id AND claveNota = {$id}");
			$ticket = new Ticket();
			$result = $ticket->printTicket($id, $data->entregado, $data->cambio, $data->fechaCobro, $data->alias, $data->impresora);
			//$result = $ticket->printTicket1($id, $data->entregado, $data->cambio, $data->fechaCobro);
			if($result == '') {
				$message .= " Ticket Impreso O.K. ";
			} else {
				$message .= " No se pudo imprimir el ticket: [{$result}]";
			}
		}
		return compact('code', 'id', 'message');
	}
	
	public function cancelarNota() {
	
		$message = "Falta implementar la logica de guardado...";
		$data = json_decode($_POST['data']['json']);
	
		$code = 400;
		$id = -1;
		
		if(!isset($data->numero) || $data->numero < 1) {
			$message = "No se recibio el id de nota...";
		} else {
			//Verificamos el numero de nota
			if(isset($data->numero) && $data->numero != '') {
				$datos = $this->db->queryRow("SELECT numero, claveCobro, total FROM nota where numero = {$data->numero}");
				$id = (int)$datos->numero;
				if($id > 0) {
					$cc = (int)$datos->claveCobro;
					if(!isset($cc) || $cc < 1) {
						$message = "No se encontrï¿½ el cobro para l nota";
					} else {
						$valores = array(
							":cc" => $cc,
							":fechaCancelacion" => "CURRENT_TIMESTAMP",
							":idUsuario" => $this->session->logged,
						);
						//cancelamos cobro
						$this->db->beginTransaction();
						$this->db->query("UPDATE cobro SET fechaCancelacion = :fechaCancelacion, idUsuarioCancel = :idUsuario WHERE clave = :cc", $valores);
						
						//Actualizamos estatus de nota
						$this->db->query("UPDATE nota SET estatus = 4 WHERE numero = {$id}");
						
						//Actualizamos stock, esto deberia ir en un procedimiento almacenado
						$articulos = $this->db->queryAll("SELECT dn.claveArticulo, (a.cantidad + dn.cantidad) AS total FROM detalle_nota dn JOIN articulo a ON a.codigo = dn.claveArticulo WHERE dn.folio = {$id} AND dn.claveArticulo != 'free'");
						if(count($articulos) > 0) {
							foreach($articulos as $articulo) {
								$this->db->query("UPDATE articulo SET cantidad = {$articulo->total} WHERE codigo = '{$articulo->claveArticulo}'");
							}
						}
						$this->db->commit();
						$message = "Nota cancelada correctamente.";
						$code = 200;
					}
				} else {
					$message = "Id de nota invalido...";
				}
			}
		}
		return compact('code', 'id', 'message');
	}
	
	public function guardarArticulo() {
	
		$actualizar = false;
		$message = "Falta implementar la logica de guardado...";
		$data = json_decode($_POST['data']['json']);
	
		$code = 200;
		$id = -1;
		if(isset($data->clave) && $data->clave > 0) {
			$id = (int)$this->db->queryOne("SELECT clave FROM articulo where clave = {$data->clave}");
			if($id > 0) {
					$actualizar = true;
				}
			}
			$valoresarticulo = array(
					":categoria" => $data->categoria,
					":descripcion" => $data->larga,
					":corta" => $data->corta,
					":precio" => $data->precio,
					":cantidad" => $data->cantidad,
					":activo" => $data->activo
			);
			if ($actualizar) {
				$valoresarticulo[':clave'] = $data->clave;
				$this->db->query(
						"UPDATE articulo SET categoria = :categoria, descripcion = :descripcion, corta = :corta, precio = :precio, cantidad = :cantidad, activo = :activo" .
						" WHERE clave = :clave",
						$valoresarticulo);
	
			} else {
				$valoresarticulo[':codigo'] = $data->codigo;
				$this->db->query(
						"INSERT INTO articulo SET codigo = :codigo, categoria = :categoria, descripcion = :descripcion, corta = :corta, precio = :precio, cantidad = :cantidad, activo = :activo",
						$valoresarticulo);
				$id = $this->db->getLastID();
	
			}
			$message = "Articulo guardado correctamente";
		return compact('code', 'id', 'message');
	}
	
	public function agregarLog() {
		$actualizar = false;
		$message = "Falta implementar la logica de guardado...";
		$data = json_decode($_POST['data']['json']);
		
		$code = 200;
		$id = -1;
		
		if(!isset($data->numord) || $data->numord == '') {
			$code = 400;
			$message = "No se recibio el identificador de orden...";
		} else {
			if(isset($data->idLog) && $data->idLog > 0) {
				$id = (int)$this->db->queryOne("select id from log_orden where id = {$data->idLog}");
				if($id > 0) {
					$actualizar = true;
				}
			}
			if($actualizar && isset($data->asignar) && $data->asignar > 0) {
				$this->db->query("UPDATE log_orden SET idAsignado = {$data->id} WHERE id = {$id} AND idOrden = {$data->numord} AND idRealizo = {$this->session->logged}");
				$message = "Log asignado correctamente";
			} else
			if($actualizar && isset($data->terminar) && $data->terminar > 0) {
				$this->db->query("UPDATE log_orden SET idEstatus = 2, fechaTermino = CURRENT_TIMESTAMP WHERE id = {$id} AND idOrden = {$data->numord} AND (idAsignado = '-1' OR idAsignado = {$this->session->logged})");
				$message = "Log terminado correctamente";
			} else
			if($actualizar && isset($data->borrar) && $data->borrar > 0) {
				$this->db->query("UPDATE log_orden SET idElimino = {$this->session->logged}, activo = 0 WHERE id = {$id} AND idOrden = {$data->numord} AND idRealizo = {$this->session->logged}");
				$message = "Log eliminado correctamente";
			} else {
				$valores = array(
						//":numord" => $data->numord,
						":log" => $data->log,
						//":idRealizo" => $this->session->logged,
						//":idCambio" => $this->session->logged,
						//":idElimino" => 0
				);
				if ($actualizar) {
					$valores[':id'] = $data->idLog;
					$valores[':idCambio'] = $this->session->logged;
					$this->db->query(
							"UPDATE log_orden set idCambio = :idCambio, log = :log, fechaCambio = default " .
							" WHERE id = :id",
							$valores);
				
				} else {
					$valores[':numord'] = $data->numord;
					$valores[':idRealizo'] = $this->session->logged;
					$valores[':asignado'] = $data->asignado;
					$this->db->query(
							"insert into log_orden set idRealizo = :idRealizo, log = :log, idOrden = :numord, idAsignado = :asignado",
							$valores);
					$id = $this->db->getLastID();
				
				}
				$message = "Log guardado correctamente";
			}
		}
		return compact('code', 'id', 'message');
	}
	
	public function guardarOrden() {
		
		$actualizar = false;
		$message = "Falta implementar la logica de guardado...";
		$data = json_decode($_POST['data']['json']);
		
		$code = 200;
		$id = -1;
	
		if(!isset($data->idCliente) || $data->idCliente == '') {
			$code = 400;
			$message = "No se recibio el identificador de usuario...";
		} else {
			if(isset($data->numord) && $data->numord != '') {
				$id = (int)$this->db->queryOne("select numero from orden_servicio where numero = {$data->numord}");
				if($id > 0) {
					$actualizar = true;
				}
			}
			$valoresOrden = array(
				":idCliente" => $data->idCliente,
				":descripcion" => $data->descripcion,
				":accesorios" => $data->accesorios,
				":idRecibio" => $this->session->logged,
				":idAsignado" => $this->session->logged
					
			);
			if ($actualizar) {
				$valoresOrden[':numero'] = $data->numord;
				$this->db->query(
				"UPDATE orden_servicio set idCliente = :idCliente, descripcion = :descripcion, accesorios = :accesorios, idRecibio = :idRecibio, idAsignado = :idAsignado, estatus = 1" . 
				" WHERE numero = :numero",
				$valoresOrden);
				
			} else {
				$this->db->query(
				"insert into orden_servicio set idCliente = :idCliente, descripcion = :descripcion, accesorios = :accesorios, idRecibio = :idRecibio, idAsignado = :idAsignado, estatus = 1",
				$valoresOrden);
				$id = $this->db->getLastID();
				
			}
			$message = "Orden guardada correctamente";
		}
		return compact('code', 'id', 'message');
	}
	
	public function guardarCliente() {
		$actualizar = false;
		$message = "Falta implementar la logica de guardado...";
		$data = json_decode($_POST['data']['json']);
		$code = 200;
		$id = -1;
		
		if(isset($data->numcli) && $data->numcli != '') {
			$id = (int)$this->db->queryOne("select id from cliente where id={$data->numcli}");
			if($id > 0) {
				$actualizar = true;
			}
		}
		if (!$actualizar) {
			$where = " WHERE c.nombre = '" . $data->nombre . "' AND c.apellido1 = '" . $data->apellido1 . "'";
			if($data->apellido2 != null && $data->apellido2 != '') {
				$where .= " AND c.apellido2 = '" . $data->apellido2 . "'";
			}
			//$where .= " AND t.numero = '" . $data->numero . "'";
			//No se mando la bandera de actualizar paero se busca un cliente que coincida con el nombre y telefono
			/*$idCliente = (int)$this->db->queryOne("select c.id as idCliente from cliente c
					left join cliente_telefono ct on c.id = ct.idCliente
					left join telefono t on ct.claveTelefono = t.clave $where");*/
			$idCliente = (int)$this->db->queryOne("select c.id from cliente c $where");
			if(isset($idCliente) && $idCliente > 0) {
				$id = $idCliente;
				$message = "El cliente se guardo correctamente [RECY].";
				return compact('code', 'id', 'message');
			}
		}
		//Verificamos que el RFC no pertenezca a un cliente existente...
		if($data->rfc != '' && $data->rfc != null && !$actualizar) {
			$clientes = $this->db->queryAll("select id from cliente where rfc = '{$data->rfc}'");
			if(count($clientes) > 0 ) {
				$code = 0;
				$message = "El RFC: " . $data->rfc . "ya esta asociado a otro cliente. Trate buscar el cliente por R.F.C.";
				return compact('code', 'id', 'message');
			}
		}
		$valoresCliente = array(
						":no" => $data->nombre,
						":nf" => $data->nombre_fiscal,
						":a1" => $data->apellido1,
						":a2" => $data->apellido2,
						":rfc" => $data->rfc,
						":em" => $data->email);
		$valoresDomicilio = array(
						":ca" => $data->calle,
						":ni" => $data->numint != '' ? $data->numint : null,
						":ne" => $data->numext != '' ? $data->numext : null,
						":co" => $data->colonia,
						":ci" => $data->ciudad != '' ? $data->ciudad : 'Guadalajara',
						":es" => $data->estado != '' ? $data->estado : 'Jalisco',
						":cp" => $data->cp > 0? $data->cp : null);
		$valoresTelefono = array(
						":nu" => $data->numero,
						":tt" => $data->tipo,
						":nu2" => $data->numero2,
						":tt2" => $data->tipo2);
		$this->db->beginTransaction();
		if($actualizar) {
			//Actualizamos los datos del cliente
			$valoresCliente[':id'] = $data->numcli;
			$this->db->query(
				"update cliente set nombre = :no, nombre_fiscal = :nf, apellido1 = :a1, apellido2 = :a2, rfc = :rfc, email = :em WHERE id = :id",
				$valoresCliente);
			//Actualizamos el domicilio del cliente
			$claveDomicilio = (int)$this->db->queryOne(
					"select d.clave from cliente_domicilio cd left join domicilio d on cd.claveDomicilio = d.clave
					where cd.idCliente = {$data->numcli}");
			if($claveDomicilio > 0) {
				$valoresDomicilio[':clave'] = $claveDomicilio;
				$this->db->query(
						"update domicilio set calle = :ca, numint = :ni, numext = :ne, ciudad = :ci,
						colonia = :co, estado = :es, cp = :cp WHERE clave = :clave", $valoresDomicilio);
			} else {
				//No se encontro registro de domicilio para este cliente...
			}
			//Actualizamos el telefono
			$claveTelefono = (int)$this->db->queryOne(
					"select t.clave from cliente_telefono ct left join telefono t on ct.claveTelefono = t.clave 
					where ct.idCliente = {$data->numcli}");
			if($claveTelefono > 0) {
				$valoresTelefono[':clave'] = $claveTelefono;
				$this->db->query("update telefono set numero = :nu, tipo = :tt, numero2 = :nu2, tipo2 = :tt2 WHERE clave = :clave", $valoresTelefono);
			} else {
				//No se encontro registro de telefono para este cliente...
			}
			$message = "Cliente actualizado correctamente";
			$id = $data->numcli;
		} else {
		
			$this->db->query(
				"insert into cliente set nombre = :no, nombre_fiscal = :nf, apellido1 = :a1, apellido2 = :a2, rfc = :rfc, email = :em",
				$valoresCliente);
			$idCliente = $this->db->getLastID();
			$this->db->query(
				"insert into domicilio set calle = :ca, numint = :ni, numext = :ne, colonia = :co, ciudad = :ci, estado = :es, cp = :cp", $valoresDomicilio);
			$claveDomicilio = $this->db->getLastID();
			$this->db->query("insert into telefono set numero = :nu, tipo = :tt, numero2 = :nu2, tipo2 = :tt2", $valoresTelefono);
			$claveTelefono = $this->db->getLastID();
			
			$valoresClienteTel = array(
					":idc" => $idCliente,
					":ct" => $claveTelefono);
			$this->db->query("insert into cliente_telefono set idCliente = :idc, claveTelefono = :ct", $valoresClienteTel);
			$valoresClienteDom = array(
					":idc" => $idCliente,
					":cd" => $claveDomicilio);
			$this->db->query("insert into cliente_domicilio set idCliente = :idc, claveDomicilio = :cd", $valoresClienteDom);
			$message = "Cliente agregado correctamente";
			$id = $idCliente;
		}
		$this->db->commit();
		return compact('code', 'id', 'message');
	}
	
	public function getAlias() {
		return isset($this->session->alias) ? $this->session->alias : "- - - - ";
	}
	
	public function editar() {
	
		$id = getValueFrom($_GET, 'id', 0, FILTER_SANITIZE_PHRAPI_MYSQL);
		$idsec = getValueFrom($_GET, 'idsec', 0, FILTER_SANITIZE_PHRAPI_INT);
		$entidad = getValueFrom($_GET, 'seccion', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$datos = new stdClass();
		if($entidad == 'articulos') {
			if($id != '') {
				$datos = $this->db->queryRow("SELECT a.clave, a.codigo, a.corta, a.cantidad, a.precio, a.activo, a.descripcion, a.categoria FROM articulo a WHERE codigo = '{$id}'");
				$id =isset($datos->codigo) ? $datos->codigo : '';
			}
			return compact('id', 'datos');
		}
		if($entidad == 'clientes') {
			$datosCliente;
			if($id != 0) {
				$datosCliente = $this->buscarCliente($id);
			}
			return compact('id', 'datosCliente');
		}
		if($entidad == 'ordenes') {
			$datosCliente;
			if($id != 0) {
				$datos = $this->db->queryRow(
						"SELECT o.numero, o.idCliente, o.descripcion, o.accesorios, o.idRecibio, o.idAsignado, o.idEntrego, o.estatus, oe.nombre nombree, n.numero nota
						FROM orden_servicio o LEFT JOIN nota n ON o.numero = n.folio AND n.estatus != 4
						left join orden_estatus oe ON oe.id = o.estatus WHERE o.numero = '{$id}'");
				if(isset($datos->numero)) {
					$datos->logs = $this->logs($datos->numero);
					//if(count($datos->logs)
				}
				if(isset($datos->idCliente)) {
					$datosCliente = $this->buscarCliente($datos->idCliente);
				} else {
					$id = 0; 
				}
			}
			return compact('datos', 'id', 'datosCliente');
		}
		if($entidad == 'notas') {
			$datos = new stdClass();
			$datos->numero = 0;
			$datos->data = "";
			$datos->estatus = 0;
			$datos->aplicaiva = false;
			$datosCliente = new stdClass();
			$datosCliente->id = 0;
			if($id != 0) {
				//es edicion de nota
				/*$datos = $this->db->queryRow("SELECT n.folio, n.numero,
						n.iva, n.idCliente, n.estatus,
						group_concat(
						IF(d.claveArticulo = 'free',
						CONCAT(d.claveArticulo, '||', d.descripcion, '||', d.precio, '||', d.cantidad, IFNULL(CONCAT('||', d.accesorio), '')),
						CONCAT(d.claveArticulo, '||', d.cantidad))
						ORDER BY d.claveArticulo) data
						FROM detalle_nota d join nota n on n.numero = d.folio
						WHERE numero = '{$id}'");*/
				/*$datos = $this->db->queryRow("SELECT n.folio, n.numero, n.iva, n.idCliente, n.estatus 
						FROM nota n 
						WHERE numero = '{$id}'");*/
				$datos = $this->db->queryRow("SELECT n.folio, n.numero, n.iva, n.idCliente, n.estatus, n1.numero as numero2 FROM nota n LEFT JOIN nota n1 ON (n.folio = n1.folio AND n1.estatus != 4) WHERE n.numero = '{$id}'");
				$datos->data = json_encode($this->db->queryAll("SELECT dn.claveArticulo as codigo,
					IF(dn.claveArticulo = 'free', dn.descripcion, a.corta) as descripcion,
					IF(dn.claveArticulo = 'free', dn.descripcion, a.corta) as corta,
					IF(dn.claveArticulo = 'free', dn.precio, a.precio) as precio,
					dn.cantidad,
					a.cantidad as stock
					FROM detalle_nota dn
					LEFT JOIN articulo a ON a.codigo = dn.claveArticulo
					WHERE dn.folio = '{$id}'"));
				$idsec = (int)$datos->folio;
				$datos->aplicaiva = $datos->iva > 0 ? true : false;
			}
			$datos->orden = new stdClass();
			$datos->orden->numero = 0;
			if($idsec != 0) {
				//Es nota para orden
				$datos->orden = $this->db->queryRow(
						"SELECT o.numero, o.idCliente, o.descripcion, o.accesorios, o.idRecibio, o.idAsignado, o.idEntrego, o.estatus, oe.nombre nombree, n.numero nota
						FROM orden_servicio o LEFT JOIN nota n ON o.numero = n.folio AND n.estatus != 4
						left join orden_estatus oe ON oe.id = o.estatus WHERE o.numero = '{$idsec}'");
			
				$datosCliente = $this->buscarCliente($datos->orden->idCliente);
			}
			return compact('datos', 'id', 'idsec', 'datosCliente');
		}
	}
	
	public function index() {
		//D("HI");
		
		$seccion = getValueFrom($_GET, 'seccion', 'ordenes', FILTER_SANITIZE_PHRAPI_MYSQL);
		$pagina = getValueFrom($_GET, 'pagina', 1, FILTER_SANITIZE_PHRAPI_INT);
		$rpp = getValueFrom($_GET, 'rpp', 15, FILTER_SANITIZE_PHRAPI_INT);
		$nombre = getValueFrom($_GET, 'nombre', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$codigo = getValueFrom($_GET, 'codigo', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$id = getValueFrom($_GET, 'id', '0', FILTER_SANITIZE_PHRAPI_INT);
		$filters = getValueFrom($_GET, 'filters', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$values = getValueFrom($_GET, 'values', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$txtDate1 = getValueFrom($_GET, 'txtDate1', null, FILTER_SANITIZE_PHRAPI_MYSQL);
		$txtDate2 = getValueFrom($_GET, 'txtDate2', null, FILTER_SANITIZE_PHRAPI_MYSQL);
		$showpages = true;
		
		if($seccion == 'dia') {
			
			$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","S&aacute;bado");
			$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
			//date_default_timezone_set("America/Mexico_City");
			
			$hoy = localtime(strtotime('now'), true);
			$hoy = $hoy['tm_year'] + $hoy['tm_mon'] + $hoy['tm_mday'];
			$ayer = localtime(strtotime('-1 day'), true);
			$ayer = $ayer['tm_year'] + $ayer['tm_mon'] + $ayer['tm_mday'];
			$antier = localtime(strtotime('-2 day'), true);
			$antier = $antier['tm_year'] + $antier['tm_mon'] + $antier['tm_mday'];
			
			$actual = localtime(strtotime('now'), true);
			$actual = $actual['tm_year'] + $actual['tm_mon'] + $actual['tm_mday'];
			$_where = "";
			if (isset ($txtDate1)) {
				$parts = explode("-", $txtDate1);
				$parts = "$parts[2]-$parts[1]-$parts[0]";
				$_where = "where c.fechaCobro >= '" . $parts . "'AND c.fechaCobro < date_add('" . $parts . "', INTERVAL 1 DAY)";
				$_where1 = "where cc.fechaCobro >= '" . $parts . "'AND cc.fechaCobro < date_add('" . $parts . "', INTERVAL 1 DAY)";
				$myTime = localtime(strtotime($txtDate1), true);
				$actual = localtime(strtotime($txtDate1), true);
				$actual = $actual['tm_year'] + $actual['tm_mon'] + $actual['tm_mday'];
			} else {
				$_where = "where c.fechaCobro >= curdate() AND c.fechaCobro < date_add(curdate(), INTERVAL 1 DAY)";
				$_where1 = "where cc.fechaCobro >= curdate() AND cc.fechaCobro < date_add(curdate(), INTERVAL 1 DAY)";
				$myTime = localtime(time(), true);
			}
			$datos = new stdClass();
			$datos->vacio = false;
			/*$datos->ordenes = $this->db->queryAll("select
					n.numero, n.folio,
					CONCAT(cl.nombre, ' ', cl.apellido1, ' ', IF(cl.apellido2 IS NULL, '', cl.apellido2)) nombreCliente,
					cl.nombre_fiscal,
					count(dn.claveArticulo) as numArticulos,
					c.total importe,
					(select sum(cc.total) from cobro cc join nota n on cc.clave = n.claveCobro and n.estatus in(2,3) $_where1) total,
					group_concat(
					ifnull(
					if(dn.accesorio IS NULL, CONCAT('<span class=\'item-detail\'>>></span> ', dn.descripcion, '(', dn.cantidad, ')'), CONCAT('<span class=\'item-detail\'>>></span> ', dn.descripcion, '(', dn.cantidad, ')')),
					(select CONCAT('<span class=\'item-detail\'>>></span> ', aa.corta, '(', dn.cantidad, ')') from articulo aa where aa.codigo = dn.claveArticulo)) ORDER BY dn.accesorio DESC SEPARATOR '<br />') data
					from cobro c
					join nota n on c.clave = n.claveCobro
					join detalle_nota dn on n.numero = dn.folio
					left join orden_servicio os on n.folio = os.numero
					left join cliente cl on n.idCliente = cl.id
					$_where AND (c.idUsuarioCancel IS NULL)
					group by n.numero, n.folio");*/
			$datos->ordenes = $this->db->queryAll("select
					n.numero, n.folio,
					CONCAT(cl.nombre, ' ', cl.apellido1, ' ', IF(cl.apellido2 IS NULL, '', cl.apellido2)) nombreCliente,
					cl.nombre_fiscal,
					count(dn.claveArticulo) as numArticulos,
					c.total importe,
					(select sum(cc.total) from cobro cc join nota n on cc.clave = n.claveCobro and n.estatus in(2,3) $_where1) total,
					group_concat(
					ifnull(
					if(dn.claveArticulo = 'free', CONCAT('<span class=\'item-detail\'>>></span> ', dn.descripcion, '(', dn.cantidad, ')'), null),
					(select CONCAT('<span class=\'item-detail\'>>></span> ', aa.corta, '(', dn.cantidad, ')') from articulo aa where aa.codigo = dn.claveArticulo)) ORDER BY dn.accesorio DESC SEPARATOR '<br />') data
					from cobro c
					join nota n on c.clave = n.claveCobro
					join detalle_nota dn on n.numero = dn.folio
					left join orden_servicio os on n.folio = os.numero
					left join cliente cl on n.idCliente = cl.id
					$_where AND (c.idUsuarioCancel IS NULL)
					group by n.numero, n.folio");
			if(count($datos->ordenes) < 1) {
				$datos->vacio = true;
			}
			$datos->meses = $meses;
			$datos->dias = $dias;
			$datos->myTime = $myTime;
			$datos->txtDate1 = $txtDate1;
			$datos->actual = $actual;
			$datos->hoy = $hoy;
			$datos->ayer = $ayer;
			$datos->antier = $antier;
			$ordenes = $datos;
		}else
		if($seccion == 'periodo') {
			$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","S&aacute;bado");
			$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
			//date_default_timezone_set("America/Mexico_City");
			
			$hoy = localtime(strtotime('now'), true);
			$hoy = $hoy['tm_year'] + $hoy['tm_mon'] + $hoy['tm_mday'];
			$ayer = localtime(strtotime('-1 day'), true);
			$ayer = $ayer['tm_year'] + $ayer['tm_mon'] + $ayer['tm_mday'];
			$antier = localtime(strtotime('-2 day'), true);
			$antier = $antier['tm_year'] + $antier['tm_mon'] + $antier['tm_mday'];
			
			$actual = localtime(strtotime('now'), true);
			$actual = $actual['tm_year'] + $actual['tm_mon'] + $actual['tm_mday'];
			$_where = "";
			$parts = array();
			if (isset ($txtDate1) && isset ($txtDate2)) {
				$parts = explode("-", $txtDate1);
				$parts = "$parts[2]-$parts[1]-$parts[0]";
				$parts2 = explode("-", $txtDate2);
				$parts2 = "$parts2[2]-$parts2[1]-$parts2[0]";
				$_where = "where c.fechaCobro >= '" . $parts . "'AND c.fechaCobro < date_add('" . $parts2 . "', INTERVAL 1 DAY)";
				$myTime = localtime(strtotime($txtDate1), true);
				$myTime2 = localtime(strtotime($txtDate2), true);
			} else {
				$_where = "where c.fechaCobro >= curdate() AND c.fechaCobro < date_add(curdate(), INTERVAL 1 DAY)";
				$myTime = localtime(time(), true);
				$myTime2 = localtime(time(), true);
			}
			$datos = new stdClass();
			$datos->vacio = false;
			$datos->ordenes = $this->db->queryAll("select count(aa.codigo) ventas, sum(dn.cantidad) cantidad, UPPER(aa.codigo) codigo, aa.corta, aa.descripcion, aa.cantidad stock, aa.activo
					from cobro c
					join nota n on c.clave = n.claveCobro
					join detalle_nota dn on n.numero = dn.folio
					join articulo aa on aa.codigo = dn.claveArticulo
					$_where AND aa.codigo != 'free' AND c.idUsuarioCancel IS NULL
					group by aa.codigo");
			if(count($datos->ordenes) < 1) {
				$datos->vacio = true;
			}
			$datos->meses = $meses;
			$datos->dias = $dias;
			$datos->myTime = $myTime;
			$datos->myTime2 = $myTime2;
			$datos->txtDate1 = $txtDate1;
			$datos->txtDate2 = $txtDate2;
			$ordenes = $datos;
		}else
		if($seccion == 'articulos') {
			$tamano = $rpp;
			$_where = "WHERE a.codigo != 'free'";
			$oEst = "0";
			$oRec = "0";
			$oEnt = "0";
			//$nombre = "";
			$rinicial = $tamano * ($pagina - 1);
			$this->valores_filtros = new stdClass();
			$this->valores_filtros->order = '';
			if(isset($codigo) && $codigo != '') {
				$_where .= " AND a.codigo = '" . urldecode($codigo) . "' ";
				$showpages = false;
			} else
			if(isset($nombre) && $nombre != '') {
				$_where .= " AND a.corta LIKE CONCAT('%', '" . urldecode($nombre) . "', '%') OR a.descripcion LIKE CONCAT('%', '" . urldecode($nombre) . "', '%') OR a.codigo LIKE CONCAT('%', '" . urldecode($nombre) . "', '%') ";
				$showpages = false;
			}
			$order = ' ORDER BY a.codigo, a.corta, a.precio';
			$this->valores_filtros->codigo = "";
			$this->valores_filtros->precio = "";
			$this->valores_filtros->corta = "";
			$this->valores_filtros->activo = "";
			if($filters && $values) {
				$order = '';
				$fa = explode(",", substr($filters, 0, strlen($filters) - 1));
				$va = explode(",", substr($values, 0, strlen($values) - 1));
				$i = 0;
				$last = count($fa) - 1;
				foreach ($fa as $f) {
					if ($va[$i] != '') {
						$order .= ($order === '' ? '' : ' , ');
						$order .= "$f $va[$i]";
						switch ($f) {
							case "a.codigo":
								$oEst = $va[$i];
								$this->valores_filtros->codigo = $va[$i];
								break;
			
							case "a.precio":
								$oRec = $va[$i];
								$this->valores_filtros->precio = $va[$i];
								break;
			
							case "a.corta":
								$oEnt = $va[$i];
								$this->valores_filtros->corta = $va[$i];
								break;
							case "a.activo":
								$oEnt = $va[$i];
								$this->valores_filtros->activo = $va[$i];
								break;
						}
					}
					$i ++;
				}
				$order = $order === '' ? '' : ' ORDER BY ' . $order;
			}
			
			$this->paginador = new Paginador();
			$navigation = $this->db->getNavigation([
					'per_page' => getInt('per_page', $tamano),
					'offset' => getInt('offset', 0),
					'sql_total' =>
					"SELECT COUNT(*) total " .
					"FROM articulo a WHERE a.codigo != 'free'"
			]);
			$navigation->link .= $seccion;
			$this->ordenes = $this->db->queryAll(
					"SELECT c.nombre as categoria, UPPER(TRIM(a.codigo)) codigo, a.clave, a.corta, a.cantidad, a.precio, a.activo FROM articulo a LEFT JOIN categoria c ON c.clave = a.categoria {$_where} {$order}" . ($showpages ? $navigation->limit : ''));
			$ordenes = $this->ordenes;
		} else 
		if($seccion == 'ordenes') {
			$tamano = $rpp;
			$_where = "";
			$oEst = "0";
			$oRec = "0";
			$oEnt = "0";
			//$nombre = "edgar";
			$rinicial = $tamano * ($pagina - 1);
			if(isset($id) && $id > 0) {
				$_where = "WHERE o.numero = '{$id}' ";
				$showpages = false;
			} else
			if(isset($nombre) && $nombre != '') {
				$_where = "WHERE CONCAT(c.nombre, ' ', c.apellido1, CASE WHEN c.apellido2 IS NOT NULL THEN CONCAT(' ', c.apellido2) ELSE '' END) LIKE CONCAT('%', '" . urldecode($nombre) . "', '%') OR c.nombre_fiscal LIKE CONCAT('%', '" . urldecode($nombre) . "', '%') OR c.rfc LIKE CONCAT('%', '" . urldecode($nombre) . "', '%') OR c.email LIKE CONCAT('%', '" . urldecode($nombre) . "', '%')";
				$showpages = false;
			}
			$this->valores_filtros = new stdClass();
			$this->valores_filtros->filtroEs = 0;
			$this->valores_filtros->filtroRe = 0;
			$this->valores_filtros->filtroEn = 0;
			if($filters && $values) {
					
				$fa = explode(",", substr($filters, 0, strlen($filters) - 1));
				$va = explode(",", substr($values, 0, strlen($values) - 1));
				//$_where = "";
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
			
			$this->paginador = new Paginador();
			/*if(sizeof($this->ordenes) > 0) {
			 $this->paginador->valido = true;
			 $this->paginador->pagina = $pagina;
			 $this->paginador->where = $_where;
			 $this->paginador->tamano = $tamano;
			 $this->paginador->calcularDatos("ordenes");
			 }*/
			$navigation = $this->db->getNavigation([
					'per_page' => getInt('per_page', $tamano),
					'offset' => getInt('offset', 0),
					'sql_total' =>
					"SELECT COUNT(*) total " .
					"FROM (SELECT o.numero FROM orden_servicio o JOIN cliente c ON o.idCliente = c.id
					LEFT JOIN usuario p ON o.idRecibio = p.idpersona
					LEFT JOIN usuario p1 ON o.idEntrego = p1.idpersona
					LEFT JOIN usuario p2 ON o.idAsignado = p2.idPersona
					LEFT JOIN orden_estatus oe ON o.estatus = oe.id {$_where} ORDER BY o.numero, o.fechaT) AS a"
					]);
			$navigation->link .= $seccion;
			$this->ordenes = $this->db->queryAll("SELECT DISTINCT o.numero, o.descripcion, oe.nombre as estatus, n.numero idNota, n.estatus estatusNota, o.estatus estatusOrden,
			
					(SELECT COUNT(lo.id) FROM log_orden lo WHERE lo.idOrden = o.numero AND lo.activo = 1) notas,
					LOWER(CONCAT(o.nombre, ' ', o.apellido1, CASE WHEN o.apellido2 IS NOT NULL THEN CONCAT(' ', o.apellido2) ELSE '' END)) AS nombreC,
					LOWER(o.nombre_fiscal) AS nombre_fiscal,
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
					FROM (SELECT * FROM orden_servicio o JOIN cliente c ON o.idCliente = c.id {$_where} ORDER BY o.fecha DESC, o.fechaT DESC, o.numero " . ($showpages ? $navigation->limit : '') . ") o
					LEFT JOIN usuario p ON o.idRecibio = p.idpersona
					LEFT JOIN usuario p1 ON o.idEntrego = p1.idpersona
					LEFT JOIN usuario p2 ON o.idAsignado = p2.idPersona
					LEFT JOIN nota n ON o.numero = n.folio AND n.estatus != 4
					LEFT JOIN orden_estatus oe ON o.estatus = oe.id ORDER BY o.fecha DESC, o.fechaT DESC, o.numero");
			//return $this->ordenes;
			$ordenes = $this->ordenes;
		} else 
		if($seccion == 'notas') {
			$tamano = $rpp;
			$_where = "";
			$_wherec = "";
			$oEst = "0";
			$oRec = "0";
			$oEnt = "0";
			//$nombre = "";
			$rinicial = $tamano * ($pagina - 1);
			$this->valores_filtros = new stdClass();
			$this->valores_filtros->filtroEs = 0;
			if(isset($id) && $id > 0) {
				$_where = "WHERE n.numero = '{$id}' ";
				$showpages = false;
			}
			else
			if(isset($nombre) && $nombre != '') {
				$_wherec = "WHERE CONCAT(c.nombre, ' ', c.apellido1, CASE WHEN c.apellido2 IS NOT NULL THEN CONCAT(' ', c.apellido2) ELSE '' END) LIKE CONCAT('%', '" . urldecode($nombre) . "', '%') OR c.nombre_fiscal LIKE CONCAT('%', '" . urldecode($nombre) . "', '%') OR c.rfc LIKE CONCAT('%', '" . urldecode($nombre) . "', '%') OR c.email LIKE CONCAT('%', '" . urldecode($nombre) . "', '%')";
				$showpages = false;
			}
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
							case "n.estatus":
								$oEnt = $va[$i];
								$this->valores_filtros->filtroEs = $va[$i];
								break;
						}
					}
					$i ++;
				}
				$_where = $_where === '' ? '' : ' WHERE ' . $_where;
			}
				
			$this->paginador = new Paginador();
			$navigation = $this->db->getNavigation([
					'per_page' => getInt('per_page', $tamano),
					'offset' => getInt('offset', 0),
					'sql_total' =>
					"SELECT COUNT(*) total " .
					"FROM (Select n.numero FROM nota n $_where) s"
					]);
			$navigation->link .= $seccion;
			$this->ordenes = $this->db->queryAll(
			"SELECT mnota.numero, o.numero folio,CONCAT(c.nombre, ' ', c.apellido1, CASE WHEN c.apellido2 IS NOT NULL THEN CONCAT(' ', c.apellido2) ELSE '' END) AS nombreC,
			c.nombre_fiscal,
			if(mnota.fechaCobro >= curdate() AND mnota.fechaCobro < date_add(curdate(), INTERVAL 1 DAY), date_format(mnota.fechaCobro, 'Hoy<br />%h:%i %p'),
				if (mnota.fechaCobro >= date_sub(curdate(), INTERVAL 1 DAY) AND mnota.fechaCobro < curdate(), date_format(mnota.fechaCobro, 'Ayer<br />%h:%i %p'),
					if (mnota.fechaCobro >= date_sub(curdate(), INTERVAL 2 DAY) AND mnota.fechaCobro < date_sub(curdate(), INTERVAL 1 DAY), date_format(mnota.fechaCobro, 'Antier<br />%h:%i %p'),
						date_format(mnota.fechaCobro, '%d %b %Y<br />%h:%i %p')))) as fechaCobro,
			if(mnota.fecha >= curdate() AND mnota.fecha < date_add(curdate(), INTERVAL 1 DAY), date_format(mnota.fecha, 'Hoy<br />%h:%i %p'),
				if (mnota.fecha >= date_sub(curdate(), INTERVAL 1 DAY) AND mnota.fecha < curdate(), date_format(mnota.fecha, 'Ayer<br />%h:%i %p'),
					if (mnota.fecha >= date_sub(curdate(), INTERVAL 2 DAY) AND mnota.fecha < date_sub(curdate(), INTERVAL 1 DAY), date_format(mnota.fecha, 'Antier<br />%h:%i %p'),
						date_format(mnota.fecha, '%d %b %Y<br />%h:%i %p')))) as fecha,
			ne.nombre estatus, mnota.estatus idEstatus,
				round(sum(IF(d.claveArticulo <> 'free', a.precio * d.cantidad, d.precio * d.cantidad)) + mnota.iva, 2) as total
				from (SELECT * FROM nota n {$_where} ORDER BY n.fecha DESC, n.fechaCobro DESC, n.numero " . ($showpages ? $navigation->limit : '') . ") mnota left join orden_servicio o on mnota.folio = o.numero
				left join cliente c on mnota.idCliente = c.id
				left join detalle_nota d on mnota.numero = d.folio
				left join nota_estatus ne on mnota.estatus = ne.id
				left join articulo a on concat(d.claveArticulo, '') = a.codigo
				{$_wherec}
				group by mnota.numero ORDER BY mnota.fecha DESC, mnota.fechaCobro DESC, mnota.numero");
			$ordenes = $this->ordenes;
		}
		return compact('navigation', 'ordenes', 'showpages', 'order');
	}
	
	public function logs($numord = 0) {
		$numord = getValueFrom($_GET, 'numero', $numord, FILTER_SANITIZE_PHRAPI_INT);
		$logs = array();
		if($numord > 0) {
			$logs = $this->db->queryAll(
					"SELECT l.id, l.activo, l.log, l.idEstatus, l.idAsignado, l.idOrden, l.idRealizo,
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
			//WHERE l.idOrden = " . $numord . " AND l.activo = 1 ORDER BY l.fecha DESC, l.fechaCambio DESC");
		}
		return $logs;
	}
	
	/*public function loadHistorialOrden($numero = 0) {
	
		$numord = getValueFrom($_GET, 'numord', $numero, FILTER_SANITIZE_PHRAPI_MYSQL);
		$logs = array();
		$usuarios = array();
		if($numord > 0) {
			$logs = $this->db->queryAll(
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
			if(count($logs > 0)) {
				$usuarios = $this->getUsuarios();
			}
		}
	
		return compact('logs', 'usuarios');
	}*/
	
	public function getUsuarios() {
		return $this->db->queryAll(
			"SELECT u.id id, u.alias label, u.id, u.alias FROM usuario u, persona p where u.idpersona = p.id and (u.activo = 1 and u.interno = 1 OR u.id = -1)");
	}
	
	public function getTiposTel() {
		return $this->db->queryAll("SELECT clave id, nombre label FROM tipotelefono");
	}
	public function categorias() {
		return $this->db->queryAll("SELECT DISTINCT clave id, nombre label FROM categoria");
	}
	
	public function buscar() {
		/*$id = getValueFrom($_GET, 'id', 0, FILTER_SANITIZE_PHRAPI_INT);
		$cadena = getValueFrom($_GET, 'cadena', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$datos = new stdClass();
		if(!$id || $id < 1) {
			
		} else {
			$id = (int)$this->db->queryOne("SELECT o.numero
			FROM orden_servicio o WHERE o.numero = '{$id}'");
			if($id) redirect("orden/{$id}");
		}
		return $id;*/
	}
	public function typeaheada() {
	
		$tipo = getValueFrom($_POST, 'tipo', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$cadena = getValueFrom($_POST, 'query', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$result = $this->db->queryAll(
				"SELECT distinct TRIM(codigo) as codigo, LOWER(descripcion) as descripcion, LOWER(corta) as corta, precio, cantidad, cantidad as stock, activo FROM articulo WHERE (UPPER(codigo) LIKE '%" .
				$cadena . "%' OR UPPER(corta) LIKE '%" .
				$cadena . "%' OR UPPER(descripcion) LIKE '%" .
				$cadena . "%') AND activo = 1");
		return $result;
	}
	
	public function typeahead() {
		
		$tipo = getValueFrom($_POST, 'tipo', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$cadena = getValueFrom($_POST, 'query', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$result = $this->db->queryAll(
			"SELECT DISTINCT c.id,
			CONCAT (LOWER(c.Nombre), ' ', LOWER(c.apellido1), ' ', LOWER((CASE WHEN c.apellido2 is null THEN '' ELSE c.apellido2 END)),
					LOWER((CASE WHEN (c.nombre_fiscal = '' OR c.nombre_fiscal IS NULL)THEN '' ELSE CONCAT(' / ' ,c.nombre_fiscal) END)),
					UPPER((CASE WHEN c.rfc is null OR c.rfc = '' THEN '' ELSE CONCAT(' / ' ,c.rfc) END)),
					(CASE WHEN t.numero is null OR t.numero = '' THEN '' ELSE CONCAT(' / ' ,t.numero) END)
				) nombre, c.id
			FROM cliente c left join cliente_telefono ct on c.id = ct.idCliente left join telefono t on t.clave = ct.claveTelefono
			WHERE UPPER(CONCAT(c.nombre, ' ', c.apellido1, CASE WHEN c.apellido2 IS NOT NULL THEN CONCAT(' ', c.apellido2) ELSE '' END)) LIKE
			CONCAT('%', '" . $cadena . "', '%')
			OR UPPER(c.nombre_fiscal) LIKE CONCAT('%', '" . $cadena . "', '%')
			OR UPPER(t.numero) LIKE CONCAT('%', '" . $cadena . "', '%')
			OR UPPER(c.rfc) LIKE CONCAT('%', '" . $cadena . "', '%')");
		return $result;
	}
	
	function cargarCliente() {
		$id = getValueFrom($_POST, 'id', '0', FILTER_SANITIZE_PHRAPI_MYSQL);
		if($id == 0) {
			return json_encode(array("code"=>0, "message"=>"No se recibio el id de cliente"));
		}
		return $this->buscarCliente($id, false);
	}
	
	public function validarCodigo() {
		$codigo = getValueFrom($_POST, 'codigo', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$existe = 1;
		$code = 400;
		if($codigo != '') {
			$existe = (int)$this->db->queryOne("SELECT COUNT(clave) FROM articulo WHERE codigo = '{$codigo}'");
			$code = 200;
		}
		return compact('existe', 'code');
	}
	public function activarArticulo() {
		$clave = getValueFrom($_POST, 'clave', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$existe = 0;
		if($clave != '') {
			$existe = (int)$this->db->queryOne("SELECT clave FROM articulo where clave = {$clave}");
			if($existe > 0) 
				$this->db->query("update articulo set activo = if(activo = 1, 0, 1) where clave = {$clave}");
		}
		return $existe;
	}
	public function stockArticulo() {
		$clave = getValueFrom($_POST, 'clave', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$stock = getValueFrom($_POST, 'stock', '', FILTER_SANITIZE_PHRAPI_INT);
		
		if($clave != '') {
			$this->db->queryOne("update articulo set cantidad = {$stock} where clave = {$clave}");
		}
		return 200;
	}
	public function precioArticulo() {
		$clave = getValueFrom($_POST, 'clave', '', FILTER_SANITIZE_PHRAPI_MYSQL);
		$precio = getValueFrom($_POST, 'precio', '', FILTER_SANITIZE_PHRAPI_MYSQL);
	
		if($clave != '') {
			$this->db->queryOne("update articulo set precio = '{$precio}' where clave = {$clave}");
		}
		return 200;
	}
	
	public function buscarCliente($id, $RFC=false) {
		$datos = $this->db->queryRow(
		"select 200 as code, c.nombre, c.nombre_fiscal, c.apellido1, c.apellido2, c.email, c.rfc, c.id,
		d.calle, d.numext, d.numint, d.cruzacon, d.ycon, d.colonia, d.estado, d.cp, d.ciudad,
	    t.numero, t.tipo, t.numero2, t.tipo2
		from cliente c, telefono t, domicilio d, cliente_domicilio cd, cliente_telefono ct
		where ".( $RFC ? "c.rfc" : "c.id" ). "='{$id}' and c.id = cd.idcliente
		and c.id = ct.idcliente
		and cd.clavedomicilio = d.clave
		and ct.clavetelefono = t.clave"
		);
		return $datos;
	}
	
	public function tiposTelefono() {
		return $this->db->queryAll("SELECT DISTINCT clave as id, nombre as label FROM tipotelefono");
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
	public function notasFiltro($tipo = "estatus") {
		$datos = [];
		switch($tipo) {
			case "estatus" :
				$datos = $this->db->queryAll("SELECT DISTINCT ne.id as id, ne.nombre as label FROM nota_estatus ne JOIN nota n ON n.estatus = ne.id");
				break;
		}
		return $datos;
	}
}