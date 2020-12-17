function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}

function setWallpaper(wp) {
	/*$('.div-bloqueo').fadeTo('slow', 0.1, function()
	{
	    $(this).css('background-image', 'url(' + wp + ')');
	}).fadeTo('slow', 1);*/
	$('.div-bloqueo').css('background-image', 'url(' + wp + ')');
	setCookie('pcsw', wp, 360);
}

function login() {
	//alert(CryptoJS.MD5($("#contrasena").val()));
	var letsgo = false;
	jQuery.validator.addMethod( "alpha_numeric", function( value, element) {
		return this.optional(element) || /^([a-z0-9])+$/i.test( value );
	}, "solo letras y numeros");
	var validator = $("#do-login").validate({
		rules: {
			usuario: 		{ required: true, rangelength: [5, 15] },
			contrasena:		{ required: false, rangelength: [5, 15] }
	  	},
	  	errorPlacement: function(error, element) {
	  	    //error.appendTo( element.parent("td").next("td") );
	  	},
	  	onsubmit: false
	});
	if( validator.form() ) {
		
		$( "#md5p" ).val( CryptoJS.MD5( $( "#contrasena" ).val() ) );
		$( "#contrasena" ).val('');
		//alert($("#md5p").val());
		$( "#do-login" ).submit();
	} else {
		
		return false;
	}
}

function hideAllPass() {
	$(".div-password-label").hide();
	$(".div-username-label").show();
}

function startSession(userName, userId) {
	var inputId;
	$("#usuario").val(userName);
	hideAllPass();
	//$("div#" + userId).parent().find(".div-username-label").toggle();
	$("div#" + userId).parent().find(".div-password-label").toggle();
	inputId = $("div#" + userId).parent().find(".div-password-label").find("input.input-pass").attr("id");
	//alert(inputId);
	$("#id-input").val(inputId);
	$("input#" + inputId).focus();
	//$("div#" + userId).parent().find(".div-password-label").find("input.input-pass").focus();
	//alert($("div#" + userId).parent().find(".div-username-label").is(""));
	//$("#do-login").submit();
	//login();
	//logged = true;
	//unlockScreen();
}