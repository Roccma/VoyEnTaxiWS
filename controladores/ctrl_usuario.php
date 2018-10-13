<?php
require "clases/clase_base.php";
require "clases/usuario.php";
require_once('clases/template.php');
require_once('clases/Utils.php');
require_once('clases/session.php');
require_once('clases/auth.php');
require_once('clases/llamadas.php');
require_once('clases/servidores.php');

class ControladorUsuario extends ControladorIndex {

public function loginWS($params = array()){

		$usuario = new Usuario(array("cedula" => $params[0],
									"contrasenia" => $params[1]));
		$res = $usuario->login();
		if ($res==false)
			return json_encode(array("result" => false));
		else{
			Session::init();
			Session::set("userid",$res->getCedula());
			return json_encode(array("result"=>true,"cedula" => $res->getCedula(),"telefono" => $res->getTelefono(),"email" => $res->getEmail(),"nombre" => $res->getNombre(),"apellido" => $res->getApellido()));
		}
	}

	public function SesionAlerta($params){

		$usuario = new Usuario();
		Session::init();
		if ($params[0] != "" && $params[1]!="" && $params[2]!=""){ 
			$res = $usuario->insertarSesion($params);
			return json_encode(array("result"=>true,"message"=>$res), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		}
		else
			return json_encode(array("result"=>false,"message"=>"Login Requerido"));	
	}

	public function DatosLlamada($params){
		Session::init();
		if ($params[0] != "" && $params[1]!="" && $params[2]!="" && $params[3]!="" && $params[4]!="" && $params[5]!="" && $params[6]!=""){
			$date=date_create($params[1]);
			if ($date == false){
				return json_encode(array("result"=>false, "id" => ""),JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			} elseif (strpos($params[2], '.') === false || strpos($params[3], '.') ===false) {
				return json_encode(array("result"=>false, "id" => ""),JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}
			else{
				$fecha = date_format($date,"Y/m/d H:i:s");
				$llamada = new Llamadas(array("cedulaUsuario"=>$params[0],"estado"=>true,"fecha_hora_inicial"=>$fecha,
											"latitud_inicial"=>$params[2],"longitud_inicial"=>$params[3], "", "session_finalizar" => $params[4], "token"=>$params[5], "cantidad_desconexiones"=>$params[6], "dias_persistencia" => $params[7]));
				$res = $llamada->InicioLlamada();
				return json_encode(array("result"=>true, "id" => $res));
			}
		}
		else
			return json_encode(array("result"=>false, "id" => ""));	
	}

	public function FinLlamada($params){
		$date=date_create($params[2]);
			
		$fecha = date_format($date,"Y/m/d H:i:s");
		$llamada = new Llamadas(array("id"=>$params[0],"url"=>$params[1],"fecha_hora_final"=>$fecha,
											"latitud_final"=>$params[3],"longitud_final"=>$params[4]));
		$res = $llamada->FinalizarLLamada();
		if ($res == 0){
			return json_encode(array("result"=>false,"message"=>"Llamada No Existente"));
		}
		if ($res == 1){
			return json_encode(array("result"=>false,"message"=>"Llamada Finalizada Previamente"));
		}
		if ($res == 2){
			return json_encode(array("result"=>true,"message"=>"Llamada Finalizada"));
		}
	}

	public function FinLlamadaDesconectada($params){
		$date=date_create($params[2]);
			
		$fecha = date_format($date,"Y/m/d H:i:s");
		$llamada = new Llamadas(array("id"=>$params[0],"fecha_hora_final"=>$fecha));
		$res = $llamada->FinalizarLLamadaDesconectada();
		if ($res == 0){
			return json_encode(array("result"=>false,"message"=>"Llamada No Existente"));
		}
		if ($res == 1){
			return json_encode(array("result"=>false,"message"=>"Llamada Finalizada Previamente"));
		}
		if ($res == 2){
			return json_encode(array("result"=>true,"message"=>"Llamada Finalizada"));
		}
	}

	public function CambiarClaveTokBox($params){
		if ($params[0] != "" && $params[1]!=""){
			$llamada = new Llamadas();
			$res = $llamada->CambiarClaveTokBox($params);
			if ($res)
				return json_encode(array("result"=>true,"message"=>"Credenciales Actualizadas"));
		}
		else
			return json_encode(array("result"=>false));
	}

	public function GetClaveTokBox(){
		$llamada = new Llamadas();
		$res = $llamada->GetClaveTokBox();
		return json_encode($res);
	}

	public function ListadoLlamadas(){
		$llamada = new Llamadas();
		$res = $llamada->ListadoLlamadas();
		return json_encode($res);
	}

	public function ListadoLlamadasFinalizadas(){
		$llamada = new Llamadas();
		$res = $llamada->ListadoLlamadasFinalizadas();
		return json_encode($res);
	}

	public function DatosTaxista($params){
		$usuario = new Usuario();
		if ($params[0] != ""){ 
			$res = $usuario->getTaxista($params);
			return json_encode($res);
		}
	}

	public function DatosLlamadaPorId($params){
		$llamada = new Llamadas(array("id" => $params[0]));
		return json_encode($llamada->getLlamadaCompleta());
	}

	public function ActualizarEstadoLlamada($params){
		$llamada = new Llamadas(array("id" => $params[0], "cantidad_desconexiones" => $params[1], "desconectada" => $params[2]));
		return json_encode($llamada->ActualizarEstadoLlamada());
	}

	public function GetEstadoLlamada($params){
		$llamada = new Llamadas(array("id" => $params[0]));
		return json_encode($llamada->GetEstadoLlamada());
	}

	public function	ActualizarDatosLlamada($params){
		$llamada = new Llamadas(array("id" => $params[0], "latitud_final" => $params[1], "longitud_final" => $params[2], "url_video" => $params[3]));
		return json_encode($llamada->UpdateCallData());
	}

	public function DatosLlamadaTabla($params){
		$llamada = new Llamadas(array("id" => $params[0]));
		return json_encode($llamada->DatosLlamadaTabla());
	}

	public function HistorialLlamadas($params){
		$llamada = new Llamadas(array("cedulaUsuario" => $params[0]));
		return json_encode($llamada->HistorialLlamadas());
	}

	public function GetServidores(){
		$servidores = new Servidores();
		return json_encode($servidores->getServidores());
	}

	public function UpdateServidores($params){
		$servidores = new Servidores(array("servidor_bda" => $params[0],
											"servidor_vet" => $params[1],
											"servidor_vet_respaldo" => $params[2]));
		return json_encode($servidores->updateServidores());
	}

	public function KeepAlive($params){
		$usuario = new Usuario(array("cedula" => $params[0]));
		return json_encode($usuario->KeepAlive());
	}

	public function Estadisticas(){
		$llamadas = new Llamadas();
		return json_encode($llamadas->Estadisticas());
	}

	public function LlamadasExpiradas($params){
		$llamadas = new Llamadas(array("cedulaUsuario" => $params[0]));
		return json_encode($llamadas->LlamadasExpiradas());
	}

	public function UpdatePolyline($params){
		$llamadas = new Llamadas(array("id" => $params[0], "polyline" => $params[1], "distancia" => $params[2]));
		return json_encode($llamadas->UpdatePolyline());
	}

}
?>