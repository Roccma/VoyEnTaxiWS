<?php

require_once('libs/Slim/Slim.php');
require_once('controladores/ctrl_index.php');
require_once('controladores/ctrl_usuario.php');

ini_set("default_socket_timeout", 6000);

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get(
	'/logout', function(){
		Session::init();
		Session::destroy();
		echo json_encode(array("result:"=>true));
	}
);

$app->get(
	'/login', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$cedula = $request -> params('cedula');
		$contrasenia = $request -> params('contrasenia');

		$ctrl = new ControladorUsuario();
		echo $ctrl->loginWS(array($cedula, $contrasenia));
	}
);

$app->get(
	'/DatosTaxista', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$sessionId = $request -> params('sessionId');

		$ctrl = new ControladorUsuario();
		echo $ctrl->DatosTaxista(array($sessionId));
	}
);

$app->get(
	'/DatosLlamadaPorId', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$id = $request -> params('id');

		$ctrl = new ControladorUsuario();
		echo $ctrl->DatosLlamadaPorId(array($id));
	}
);

$app->get(
	'/SesionVideoLlamada', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$idsesion = "";
		$token = "";
		$iduser = $request -> params('userid');
		$idsesion = $request -> params('sessionid');
		$token = $request -> params('token');

		$ctrl = new ControladorUsuario();
		echo $ctrl->SesionAlerta(array($iduser, $idsesion,$token));
	}
);

$app->get(
	'/DatosLlamada', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$fechahora = "";
		$latitud = "";
		$longitud = "";
		$iduser = $request -> params('userid');
		$fechahora = $request -> params('date');
		$latitud = $request -> params('latitud');
		$longitud = $request -> params('longitud');
		$sessionid = $request -> params('sessionid');
		$token = $request -> params('token');
		$cantidad_desconexiones = $request -> params('cantidad_desconexiones');
		$dias_persistencia = $request -> params('dias_persistencia');

		$ctrl = new ControladorUsuario();
		echo $ctrl->DatosLlamada(array($iduser, $fechahora,$latitud,$longitud,$sessionid,$token, $cantidad_desconexiones, $dias_persistencia));
		
	}
);

$app->get(
	'/FinLlamada', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$idllamada="";
		$fechahora = "";
		$latitud = "";
		$longitud = "";
		$url = "";
		$idllamada = $request -> params('callid');
		$fechahora = $request -> params('date');
		$latitud = $request -> params('latitud');
		$longitud = $request -> params('longitud');
		$url = $request -> params('url');

		$ctrl = new ControladorUsuario();
		echo $ctrl->FinLlamada(array($idllamada,$url,$fechahora,$latitud,$longitud));
		
	}
);

$app->get(
	'/ClavesTokBox', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$apiKey="";
		$projectKey = "";
		$apiKey = $request -> params('apiKey');
		$projectKey = $request -> params('projectKey');

		$ctrl = new ControladorUsuario();
		echo $ctrl->CambiarClaveTokBox(array($apiKey,$projectKey));
		
	}
);

$app->get(
	'/GetClaveTokBox', function(){
		$ctrl = new ControladorUsuario();
		echo $ctrl->GetClaveTokBox();
	}
);

$app->get(
	'/VideollamadasActuales', function(){
		$request = \Slim\Slim::getInstance() -> request();

		$ctrl = new ControladorUsuario();
		echo $ctrl->ListadoLlamadas();
	}
);

$app->get(
	'/VideollamadasFinalizadas', function(){
		$request = \Slim\Slim::getInstance() -> request();

		$ctrl = new ControladorUsuario();
		echo $ctrl->ListadoLlamadasFinalizadas();
	}
);

$app->get(
	'/ActualizarEstadoLlamada', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$id = $request -> params('id');
		$cantidad_reconexiones = $request -> params('cantidad_desconexiones');
		$desconectada = $request -> params('desconectada');

		$ctrl = new ControladorUsuario();
		echo $ctrl->ActualizarEstadoLlamada(array($id,$cantidad_reconexiones, $desconectada));
		
	}
);

$app->get(
	'/EstadoLlamada', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$id = $request -> params('id');

		$ctrl = new ControladorUsuario();
		echo $ctrl->GetEstadoLlamada(array($id));
		
	}
);

$app->get(
	'/ActualizarDatosLlamada', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$id = $request -> params('id');
		$latitud_final = $request -> params('latitud_final');
		$longitud_final = $request -> params('longitud_final');
		$url_video = $request -> params('url_video');

		$ctrl = new ControladorUsuario();
		echo $ctrl->ActualizarDatosLlamada(array($id, $latitud_final, $longitud_final, $url_video));
		
	}
);

$app->get(
	'/DatosLlamadaTabla', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$id = $request -> params('id');

		$ctrl = new ControladorUsuario();
		echo $ctrl->DatosLlamadaTabla(array($id));
		
	}
);

$app->get(
	'/HistorialLlamadas', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$cedula = $request -> params('cedula');

		$ctrl = new ControladorUsuario();
		echo $ctrl->HistorialLlamadas(array($cedula));
		
	}
);

$app->get(
	'/GetServidores', function(){
		$ctrl = new ControladorUsuario();
		echo $ctrl->GetServidores();
	}
);

$app->get(
	'/UpdateServidores', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$servidor_bda = $request -> params('servidor_bda');
		$servidor_vet = $request -> params('servidor_vet');
		$servidor_respaldo_vet = $request -> params('servidor_respaldo_vet');

		$ctrl = new ControladorUsuario();
		echo $ctrl->UpdateServidores(array($servidor_bda, $servidor_vet, $servidor_respaldo_vet));
		
	}
);

$app->get(
	'/KeepAlive', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$cedula = $request -> params('id');

		$ctrl = new ControladorUsuario();
		echo $ctrl->KeepAlive(array($cedula));
		
	}
);

$app->get(
	'/Stats', function(){
		
		$ctrl = new ControladorUsuario();
		echo $ctrl->Estadisticas();
		
	}
);

$app->get(
	'/LlamadasExpiradas', function(){
		$request = \Slim\Slim::getInstance() -> request();
	
		$cedula = $request -> params('cedula');

		$ctrl = new ControladorUsuario();
		echo $ctrl->LlamadasExpiradas(array($cedula));
		
	}
);

//




/*Fin WS Voy en Taxi*/

//getDatosPorNotificationId

$app->run();

?>