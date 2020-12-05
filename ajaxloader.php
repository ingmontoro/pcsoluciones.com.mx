<? include_once 'phrapi/index.php' ?>
<? $intranet = $factory->Pcsoluciones ?>
<? $accion = getString('accion', 'index') ?>
<? $seccion = getString('seccion', 'ordenes') ?>
<? $result = $intranet->$accion() ?>
<? if (file_exists("views/{$seccion}_{$accion}.php")) include_once "views/{$seccion}_{$accion}.php" ?>