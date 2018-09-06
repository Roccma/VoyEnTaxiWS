<?php
require "db/db.php";
require 'vendor/autoload.php';
require "controladores/ctrl_index.php";
$controlIndex=new ControladorIndex();
$params=array();
$controllerObj=$controlIndex->cargarControlador("usuario");
$controlIndex->ejecutarAccion($controllerObj,"loginFB",$params);
?>