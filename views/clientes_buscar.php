<? require_once 'phincludes/util.php' ?>
<?$tiposTelefono = $intranet->tiposTelefono() ?>
<? $edicion = false; ?>
<style>
.dropdown-menu>li>a {
	color: black;
}
a.dropdown-item {
    text-transform: capitalize;
}
</style>
<h2>Buscar un cliente</h2>
	<div id="div_busqueda">
		<div class="row">
			<div class="form-group col-md-4">
				<label  class="normal" for="nombre" >&nbsp;</label><br />
				<div class="input-group">
					<input <?=htmlValConf('', "Nombre, Nombre Fiscal, RFC ó email del cliente...", false)?>value="" class="form-control" id="nombre_cliente" name="nombre_cliente">
					<span class="input-group-btn">
						<button class="btn btn-primary" onclick="findOrders();"><i class="glyphicon glyphicon-search"></i></button>
					</span>
				</div>
			</div>
			<div class="form-group col-md-7"></div>
		</div>
	</div>
	<div id="div_cliente">
		<?$showficha = true;?>
		<?php require 'phincludes/datos-cliente.php'; ?>
	</div>
<script>
$(document).ready( function() {
	hideLoading();
	$("#div_cliente").hide();
	$('#nombre_cliente').focus();
});
var _txt;
var ibc = $("#nombre_cliente").typeahead({
    source: function (cadena, process) {
        var result = null;
        _txt = cadena;
        $.ajax({
            url: "phrapi/search/cliente",
            data: {query: cadena},
            type: "post",
            dataType: "json",
            async: false,
            success: function(data) {
                result = data;
            } 
         });
        return process(result);
    },
    minLength: 4,
    autoSelect: false,
    afterSelect: function(item) {
        	if(item.id == -1) {
            	$("#div_cliente").show();
            	$("#div_busqueda").hide();
        	} else {
        		//window.location = 'clientes/' + item.id;
        		$("#nombre_cliente").val(_txt);
        		cargarCliente(item.id);
        		$("#div_busqueda").show();
        	}
        },
    displayText: function(item) {
        return item.nombre;
    },
    //addItem: {id: -1, nombre: "¿No encuentra el cliente? Agregar Nuevo"}
});
ibc.on("click", function() {ibc.typeahead("lookup");})
function cargarCliente(numcli) {
	var url = 'phrapi/load/cliente';
	var _request = $.post(url, {id: numcli}, 'json');
	_request.done(function(response) {
		response = JSON.parse(response);
		var clase = '';
		if (response.code == 200) {
			$('#numcli').val(response.id);
			$("#rfc").val(response.rfc );
			$("#rfcdiv").html(response.rfc );
			$("#nombrec").val( utf8_decode (response.nombre));
			$("#nombrediv").html( utf8_decode (response.nombre));
			if(response.nombre_fiscal) {
				$('#nombref').val(utf8_decode (response.nombre_fiscal));
				$('#nombrefdiv').html(utf8_decode (response.nombre_fiscal));
			}
			$("#apellido1c").val( utf8_decode (response.apellido1));
			$("#apellido1cdiv").html( utf8_decode (response.apellido1));
			if(response.apellido2) {
				$("#apellido2c").val (utf8_decode(response.apellido2));
				$("#apellido2cdiv").html(utf8_decode(response.apellido2));
			}
			$("#emailc").val(response.email );
			$("#emailcdiv").html(response.email );
			if(response.calle) {
				$("#called").val( utf8_decode (response.calle));
				$("#calleddiv").html( utf8_decode (response.calle));
			}
			$("#numextd").val(response.numext );
			$("#numextddiv").html(response.numext );
			$("#numintd").val(response.numint );
			$("#numintddiv").html(response.numint );
			if(response.colonia) {
				$("#colonia").val( utf8_decode (response.colonia));
				$("#coloniadiv").html( utf8_decode (response.colonia));
			}
			$("#ciudadd").val( utf8_decode (response.ciudad));
			$("#ciudadddiv").html( utf8_decode (response.ciudad));
			$("#estadod").val( utf8_decode (response.estado));
			$("#estadoddiv").html( utf8_decode (response.estado));
			$("#cpd").val(response.cp );
			$("#cpddiv").html(response.cp );
			
			$("#numerot").val(response.numero );
			var ico = '';
			if(response.numero != '') {
				if(response.tipo == 1) {
					ico = '&nbsp;&nbsp;<i class="glyphicon glyphicon-phone-alt"></i>';
				} else if(response.tipo == 2) {
					ico = '&nbsp;&nbsp;<i class="glyphicon glyphicon-phone"></i>';
				}
			}
			$("#numerotdiv").html(response.numero + ico);
			
			if(response.numero2) {
				$("#numerot2").val(response.numero2);
				ico = '';
				if(response.numero2 != '') {
					if(response.tipo2 == 1) {
						ico = '&nbsp;&nbsp;<i class="glyphicon glyphicon-phone-alt"></i>';
					} else if(response.tipo2 == 2) {
						ico = '&nbsp;&nbsp;<i class="glyphicon glyphicon-phone"></i>';
					}
				}
				$("#numerot2div").html(response.numero2 + ico );
			}
			$("#tipot").val(response.tipo);
			//$("#tipotdiv").val(response.tipo);
			$("#tipot2").val(response.tipo2);
			//$("#tipot2div").val(response.tipo2);
			$("#div_cliente").show();
        	//$("#div_busqueda").hide();
			$("#div_orden").show();
		} else {
			clase = "warning";
			configAlert("cliente", clase, response.message);
		}
	});
	_request.fail( function( jqXHR, textStatus ) {
		configAlert(entity, 'danger', textStatus);
	});
}
</script>