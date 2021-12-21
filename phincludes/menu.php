<?php defined('PHRAPI') or die("Direct access not allowed!");?>
<!-- Fixed navbar -->
<style>
img.logo {
	width: 185px;
    padding: 0;
    margin: -10px;
    padding-top: 0;
    margin-right: 10px;
    margin-left: 20px;
}
#navbar-logo {
	float:none;
}
.navbar-nav>li>a {
    color: #777;
    font-size: large;
    color: #0d8bca;
}
.navbar-default .navbar-nav>li>a, .dropdown-menu>li>a {
    color: #777;
    font-size: large;
    color: #0d8bca;
}
.navbar-default .navbar-nav>li>a:hover, .dropdown-menu>li>a {
    color: #eb693c;
}
.dropdown-menu>li>a:hover {
	background-color: #0d8bca7d;
	color: #eb693c;
}
/*fix para que no cambie el color en clientes*/
.mi-menu>li>a {
	color: #eb693c !important;
}
.mi-menu>li>a.salir {
	color: #337ab7 !important;
	font-weight : bold;
}
.navbar-default .navbar-nav>.open>a.tt, .navbar-default .navbar-nav>.open>a.tt:focus, .navbar-default .navbar-nav>.open>a.tt:hover {
    color: #fff;
    background-color: transparent;
}
.navbar-fixed-bottom .navbar-collapse, .navbar-fixed-top .navbar-collapse {
    max-height: 500px;
}
</style>
<nav id="menu" class="navbar navbar-default navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed"
				data-toggle="collapse" data-target="#navbar" aria-expanded="false"
				aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span> <span
					class="icon-bar"></span> <span class="icon-bar"></span> <span
					class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="#" id="navbar-logo">
				<img class="img-responsive logo" src="assets/images/pcsol-logo-t.png">
			</a>
		</div>
		<div id="navbar" class="navbar-collapse collapse">
			<ul class="nav navbar-nav" style="padding-top:10px;">
				<li class="active_ dropdown">
					<a href="ordenes" class="dropdown-toggle"data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
						&Oacute;rdenes <span class="caret"></span>
					</a>
					<ul class="dropdown-menu mi-menu">
						<li><a class="click-loader" href="ordenes/add">Nueva</a></li>
						<li><a class="click-loader" href="buscar/ordenes">Buscar</a></li>
						<li><a class="click-loader" href="ordenes">Listado</a></li>
						<li role="separator" class="divider"></li>
						<!-- <li class="dropdown-header">Nav header</li>
						<li><a href="#">...</a></li> -->
					</ul>
				</li>
				<li class="active_ dropdown"><a href="#" class="dropdown-toggle"
					data-toggle="dropdown" role="button" aria-haspopup="true"
					aria-expanded="false">Notas <span class="caret"></a>
					<ul class="dropdown-menu mi-menu">
						<li><a class="click-loader" href="notas/add">Nueva</a></li>
						<li><a class="click-loader" href="buscar/notas">Buscar</a></li>
						<li><a class="click-loader" href="notas">Listado</a></li>
						<li role="separator" class="divider"></li>
						<!-- <li class="dropdown-header">Nav header</li>
						<li><a href="#">...</a></li> -->
					</ul>
				</li>
				<li class="active_ dropdown"><a href="#" class="dropdown-toggle"
					data-toggle="dropdown" role="button" aria-haspopup="true"
					aria-expanded="false">Ventas <span class="caret"></a>
					<ul class="dropdown-menu mi-menu">
						<li><a class="click-loader" href="periodo">Por per&iacute;odo</a></li>
						<li><a class="click-loader" href="dia">Por d&iacute;a</a></li>
						<li role="separator" class="divider"></li>
						<!-- <li class="dropdown-header">Nav header</li>
						<li><a href="#">...</a></li> -->
					</ul>
				</li>
				<li class="active_ dropdown"><a href="#" class="dropdown-toggle"
					data-toggle="dropdown" role="button" aria-haspopup="true"
					aria-expanded="false">Facturaci&oacute;n <span class="caret"></a>
					<ul class="dropdown-menu mi-menu">
						<li><a class="click-loader" href="clientes/add">Alta de cliente</a></li>
						<li><a class="click-loader" href="buscar/clientes">Buscar cliente</a></li>
						<li role="separator" class="divider"></li>
						<!-- <li class="dropdown-header">Nav header</li>
						<li><a href="#">...</a></li> -->
					</ul>
				</li>
				<li class="active_ dropdown"><a href="#" class="dropdown-toggle"
					data-toggle="dropdown" role="button" aria-haspopup="true"
					aria-expanded="false">Inventario <span class="caret"></a>
					<ul class="dropdown-menu mi-menu">
						<li><a class="click-loader" href="articulos/add">Alta de art&iacute;culo</a></li>
						<li><a class="click-loader" href="buscar/articulos">Buscar art&iacute;culo</a></li>
						<li><a class="click-loader" href="articulos">Listado de art&iacute;culos</a></li>
						<li role="separator" class="divider"></li>
						<!-- <li class="dropdown-header">Nav header</li>
						<li><a href="#">...</a></li> -->
					</ul>
				</li>
				<!-- <li><a href="#about">About</a></li>
				<li><a href="#contact">Contact</a></li>
				<li class="dropdown"><a href="#" class="dropdown-toggle"
					data-toggle="dropdown" role="button" aria-haspopup="true"
					aria-expanded="false">Dropdown <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="#">Action</a></li>
						<li><a href="#">Another action</a></li>
						<li><a href="#">Something else here</a></li>
						<li role="separator" class="divider"></li>
						<li class="dropdown-header">Nav header</li>
						<li><a href="#">Separated link</a></li>
						<li><a href="#">One more separated link</a></li>
					</ul></li> -->
			</ul>
			<div></div>
			<ul class="nav navbar-nav navbar-right">
				<li class="active_ dropdown">
					<a href="#" class="dropdown-toggle"
						data-toggle="dropdown" role="button" aria-haspopup="true"
						aria-expanded="false" style="font-size:small;">Sistema<span class="caret"></span>
					</a>
					<ul class="dropdown-menu mi-menu">
						<li><a class="click-loader" href="config/editar" style="font-size:small;">Editar config</a></li>
					</ul>
				</li>
				<li style="margin-right: 15px;">
					<a style="padding: 10px; padding-bottom:0; color: #337ab7;font-size: small;font-weight: 700;text-align:center;"
					href="#" class="dropdown-toggle tt"
					data-toggle="dropdown" role="button" aria-haspopup="true"
					aria-expanded="false"><?=$factory->Access->alias()?><span class="caret"></span>
						<div><img <?=$factory->Access->imagen() != '' ? 'src="' . $factory->Access->imagen() . '"' : ''?> style="height:32px;width:32px; border-radius:20px;" class="profile_thumbs <?=$factory->Access->profile()?>" /></div>
					</a>
					<ul class="dropdown-menu mi-menu">
						<li><a class="click-loader" href="usuarios/editar" style="font-size:small;">Editar mis datos</a></li>
						<li><a class="click-loader" href="usuarios/editar?tab=password" style="font-size:small;">Cambiar password</a></li>
						<li role="separator" class="divider" style="margin: 4px 0"></li>
						<li><a class="click-loader salir" href="phrapi/access/logout" style="font-size:small;">Salir <i class="glyphicon glyphicon-off" style="float: right;color: tomato;font-size:medium;"></i></a></li>
					</ul>
				</li>
				<!-- <li><a href="../navbar/">Default</a></li>
				<li><a href="../navbar-static-top/">Static top</a></li>
				<li class="active_"><a href="./">Fixed top <span class="sr-only">(current)</span></a></li> -->
			</ul>
		</div>
		<div id="timer"></div>
		<!--/.nav-collapse -->
	</div>
</nav>
