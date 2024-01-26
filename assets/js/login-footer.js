$(document).ready(function () {
	$("#usrImg").hide();
	$("#usrName").hide();
	$("#usrId").hide();
	$("#eliminar").hide();
	var wpDef = '';
	var theDialog;
	var newCss = "";
	$("#changer").on('click', function () {
		$("#panelWP").toggle('slide', { direction: 'left' }, 200);
	});
	$(".wp").each(function (i) {
		$(this).on("click", function () {
			var src = $(this).prop("src");
			//$("#divBloqueo").css("background", "url('" + $(this).prop("src") + "') repeat");
			//$("#divBloqueo").css("background-position", "top");
			if ('url(' + src + ')' != $('.div-bloqueo').css('background-image')) {
				$("#panelWP").toggle('slide', { direction: 'left' }, 200);
				setWallpaper(src);
			}
		});
	});
	theDialog = $("#configuracion").dialog({
		dialogClass: "",
		modal: true,
		resize: true,
		autoOpen: false,
		width: 'auto',
		height: 'auto',
		buttons: {
			'Eliminar imagen': function () {
				$(this).find(".cell-user-img").prop("style", '');
				$("#eliminar").val($(this).find(".cell-user-img").prop("id"));
				newCss = "";
			},
			Aceptar: function () {
				$("#usrName").val($(this).find(".div-username-label").html());
				$("#usrId").val($(this).find(".cell-user-img").prop("id"));
				$("form#userConfig").submit();
			},
			Cancelar: function () {
				$(this).dialog("close");
			}
		}
	});

	$(".cell-user-img").on("mouseenter", function (index) {
		//$(this).delay(1000).fadeTo(1000,0).delay(0).fadeTo(1000,1, blink);
	});


	/*
	 * Guardar configuracion de perfil de usuario
	 */
	$("form#userConfig").submit(function (e) {
		e.preventDefault();
		var formData = new FormData($(this)[0]);
		$.ajax({
			url: 'phrapi/access/saveUserConfig',
			dataType: 'text',  // what to expect back from the PHP script, if anything
			cache: false,
			processData: false,
			contentType: false,
			data: formData,
			type: 'post',
			success: function (response) {
				if (response != 200) {
					alert("Error al actualizar la informaci&oacute;n...");
				} else {
					//alert(newCss);
					$(".cell-user-img#" + $("#configuracion").find("#usrId").val()).attr("style", '');
					$(".cell-user-img#" + $("#configuracion").find("#usrId").val()).attr("style", newCss);
					$(".cell-user-img#" + $("#configuracion").find("#usrId").val()).css("border", "");
					$("#label" + $("#configuracion").find("#usrId").val()).html($("#configuracion").find("#usrName").val());
				}
				theDialog.dialog("close");
			}
		});
		$("#usrImg").val("");
	});

	/*Inicializa los botones de config de cada usuario mostrado en pantalla
	 * */
	$(".div-user-config").each(function (index) {
		$(this).on("click", function () {
			var userDiv = $(this).parent().clone();
			//userDiv.find('.cell-user-img').css('border', "2px dashed gray");
			newCss = userDiv.find('.cell-user-img').attr("style");
			$("#posicion").on("change", function (e) {
				userDiv.find('.cell-user-img').css('background-position', $(this).val());
				newCss = userDiv.find('.cell-user-img').attr("style");
			});
			$("#ajuste").on("change", function (e) {
				var sizeVal = $(this).val() == "alto" ? "auto 300px" : "300px auto";
				var optPosi = $(this).val() == "alto" ?
					'<option value="center">Centrada</option><option value="left">Izquierda</option><option value="right">Derecha</option>' :
					'<option value="center">Centrada</option><option value="top">Arriba</option><option value="bottom">Abajo</option>'
				$("#posicion").html(optPosi);
				userDiv.find('.cell-user-img').css('background-size', sizeVal);
				newCss = userDiv.find('.cell-user-img').attr("style");
			});
			userDiv.find(".cell-user-img").unbind("click");
			userDiv.find(".cell-user-img").attr("onclick", "");
			userDiv.find(".cell-user-img").prop("onclick", "");
			userDiv.find('.cell-user-img').css('background-repeat', "no-repeat");
			userDiv.find(".cell-user-img").click(function () {
				$("#usrImg").click();
				$("#usrImg").on("change", function (e) {
					var reader = new FileReader();
					reader.onload = function (e) {
						// get loaded data and render thumbnail.
						var image = new Image();
						image.src = e.target.result;
						var w = image.width;
						var h = image.height;
						var sizeVal = image.width > image.height ? "auto 300px" : "300px auto";
						var optPosi = image.width > image.height ?
							'<option value="center">Centrada</option><option value="left">Izquierda</option><option value="right">Derecha</option>' :
							'<option value="center">Centrada</option><option value="top">Arriba</option><option value="bottom">Abajo</option>';
						$("#posicion").html(optPosi);
						$("#posicion").val("center");
						$("#ajuste").val("center");
						userDiv.find('.cell-user-img').css('background-position', 'center');
						$("#ajuste").val(image.width > image.height ? "alto" : "ancho");
						userDiv.find('.cell-user-img').css('background-image', 'url(' + e.target.result + ')');
						userDiv.find('.cell-user-img').css('background-size', sizeVal);
						newCss = userDiv.find('.cell-user-img').attr("style");
					};
					// read the image file as a data URL.
					reader.readAsDataURL(this.files[0]);
				});
			});
			userDiv.find(".div-username-label").bind("click", function (e) {
				$(this).attr("contenteditable", true);
			});
			userDiv.find(".div-user-config").remove();
			theDialog.find("#content").html(userDiv);
			theDialog.dialog("open");
		})
	});
	var wp = getCookie("pcsw");
	//alert(wp);
	//alert(wpDef);
	if (wp == "") {
		wp = wpDef;
	}

	//setWallpaper(wp);
	setWallpaper('https://sistema.pcsoluciones.com.mx/assets/images/wallpapers/cool-geometric.jpg');
});
