<?php 
require_once("clases/clase_base.php");
require_once("db/db.php");
class Llamadas extends ClaseBase{
	private $id;
	private $cedulaUsuario;
	private $estado;
	private $longitud_inicial;
	private $longitud_final;
	private $latitud_inicial;
	private $latitud_final;
	private $fecha_hora_inicial;
	private $fecha_hora_final;
	private $url_video;
	private $session_finalizar;
	private $token;
	private $cantidad_desconexiones;
	private $desconectada;
	private $dias_persistencia;
	private $polyline;

	/* Constructor */

	public function __construct($obj=NULL){
		if(isset($obj)){
			foreach ($obj as $key => $value) {
				$this->$key=$value;
			}
		}
		$tabla = "llamadas";

		parent::__construct($tabla);
	}

	public function getid(){
		return $this->id;
	}

	public function getEstado(){
		return $this->estado;
	}

	public function getUrl(){
		return $this->url;
	}

	public function getCedula(){
		return $this->cedulaUsuario;
	}

	public function getDateI(){
		return $this->fecha_hora_inicial;
	}

	public function getDateF(){
		return $this->fecha_hora_final;
	}

	public function getLongitudI(){
		return $this->longitud_inicial;
	}

	public function getLongitudF(){
		return $this->longitud_final;
	}

	public function getLatitudI(){
		return $this->latitud_inicial;
	}

	public function getLatitudF(){
		return $this->latitud_final;
	}

	public function InicioLlamada(){
		$stmt = DB::conexion()->prepare("SELECT count(*) as cantidad FROM llamadas WHERE fecha_hora_inicial = ?");
		$date = $this->getDateI();
		$stmt->bind_param("s",$date);
		$stmt->execute();
		$resultado = $stmt->get_result();
		$cantidad = $resultado->fetch_object()->cantidad;

		if($cantidad == 0){
			$stmt = DB::conexion()->prepare("INSERT INTO llamadas (cedulaUsuario, estado, fecha_hora_inicial, latitud_inicial, longitud_inicial, session_finalizar, token, cantidad_desconexiones, dias_persistencia, desconectada, expirada) VALUES(?,?,?, ?, ?, ?, ?, ?, ?, 0, 0)");
			$userid = $this->getCedula();
			$lat = $this->getLatitudI();
			$long = $this->getLongitudI();
			$date = $this->getDateI();
			$estado = 1;
			$stmt->bind_param("iisssssii",$userid,$estado,$date,$lat,$long, $this->session_finalizar, $this->token, $this->cantidad_desconexiones, $this->dias_persistencia);
			$stmt->execute();			
		}

		$stmt = DB::conexion()->prepare("SELECT max(id) as id FROM llamadas");
		$stmt->execute();
		$resultado = $stmt->get_result();
		//$id = $resultado->fetch_object()->id;
		//$stmt = DB::conexion()->query("SET GLOBAL event_scheduler = ON"); // variable necesaria que este activa para poder programar eventos
		/*if ($stmt = DB::conexion()->query("CREATE EVENT ON SCHEDULE AT CURRENT_TIMESTAMP + interval 120 second DO UPDATE llamadas SET expirada = 1 WHERE id = ".$id.";")){ // Se programa el evento para la fecha actual más un intervalo que pueden ser segundos, minutos, horas o dias
		}*/
		$idLlamada = $resultado->fetch_object()->id;

		
		$stmt = DB::conexion()->query("SET GLOBAL event_scheduler = ON"); // variable necesaria que este activa para poder programar eventos
		//$stmt->execute();
		$nombreEvento = "expirar" . $idLlamada;
		if ($stmt = DB::conexion()->query("CREATE EVENT IF NOT EXISTS " . $nombreEvento . " ON SCHEDULE AT CURRENT_TIMESTAMP + interval " . $this->dias_persistencia . " DAY DO UPDATE llamadas SET expirada = 1 WHERE id = ".$idLlamada . ";")){ 
			//$stmt->execute();// Se programa el evento para la fecha actual más un intervalo que pueden ser segundos, minutos, horas o dias
		}

		/*$stmt = DB::conexion()->prepare("SELECT max(id) as id FROM llamadas");
		$stmt->execute();
		$resultado = $stmt->get_result();*/
		//$id = $resultado->fetch_object()->id;
		//$stmt = DB::conexion()->query("SET GLOBAL event_scheduler = ON"); // variable necesaria que este activa para poder programar eventos
		/*if ($stmt = DB::conexion()->query("CREATE EVENT ON SCHEDULE AT CURRENT_TIMESTAMP + interval 120 second DO UPDATE llamadas SET expirada = 1 WHERE id = ".$id.";")){ // Se programa el evento para la fecha actual más un intervalo que pueden ser segundos, minutos, horas o dias
		}*/
		return $idLlamada;
	}

	public function FinalizarLLamada(){
		/*if ($stmt = DB::conexion()->prepare("SELECT * FROM llamadas WHERE id=$this->id")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			if ($resultado->num_rows<1)
				return 0;
			else{
				if ($resultado->fetch_object()->estado==0)
					return 1;
			}
		}*/
		if ($stmt = DB::conexion()->prepare("UPDATE llamadas SET estado=0,fecha_hora_final=now() - INTERVAL 3 HOUR,latitud_final=?,longitud_final=? WHERE id=$this->id")){
			$stmt->bind_param("ss",$this->latitud_final,$this->longitud_final);
			$stmt->execute();
			return 2; 
		} 
	}

	public function FinalizarLLamadaDesconectada(){
		$stmt = DB::conexion()->prepare("SELECT latitud_inicial, longitud_inicial FROM llamadas WHERE id = $this->id");
		$stmt->execute();
		$resultado = $stmt->get_result();
		$latitud_inicial;
		$longitud_inicial;
		while($fila = $resultado->fetch_object()){
			$latitud_inicial = $fila->latitud_inicial;
			$longitud_inicial = $fila->longitud_inicial;
		}
		if ($stmt = DB::conexion()->prepare("UPDATE llamadas SET estado=0,fecha_hora_final=now() - INTERVAL 3 HOUR, latitud_final = $latitud_inicial, longitud_final = $longitud_inicial WHERE id=$this->id")){
			$stmt->execute();
			return 2; 
		} 
	}

	public function CambiarClaveTokBox($params){
		$stmt = DB::conexion()->prepare("DELETE FROM credenciales_opentok");
		$stmt->execute();
		$stmt = DB::conexion()->prepare("INSERT INTO credenciales_opentok (api_key,secret_key) VALUES(?,?)");
		$stmt->bind_param("ss",$params[0],$params[1]);
		$stmt->execute();
		return true;
	}

	public function GetClaveTokBox(){
		$stmt = DB::conexion()->prepare("SELECT * FROM credenciales_opentok");
		$stmt->execute();
		$resultado = $stmt->get_result();
		$resultados = array();
		while($fila = $resultado->fetch_object()){
			$resultados = array("api_key" => $fila->api_key,
								"secret_key" => $fila->secret_key);
		}
		return $resultados;
	}

	public function ListadoLlamadas(){
		if ($stmt = DB::conexion()->prepare("SELECT id,cedula, concat(nombre,' ',apellido) as nombre,telefono,email,fecha_hora_inicial,latitud_inicial,longitud_inicial, session_finalizar, token FROM llamadas as l, voyentaxi_usuarios as u 
			WHERE estado = 1 AND l.cedulaUsuario = u.cedula order by fecha_hora_inicial")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			$resultados=array();
			while ( $fila = $resultado->fetch_object()){
				$resultados[] = $fila;
			}
			return $resultados;
		}
	}

	public function ListadoLlamadasFinalizadas(){
		if ($stmt = DB::conexion()->prepare("SELECT id,cedula, concat(nombre,' ',apellido) as nombre,telefono,email,fecha_hora_inicial,fecha_hora_final,latitud_inicial,latitud_final,longitud_inicial,longitud_final,url_video FROM llamadas as l, voyentaxi_usuarios as u 
			WHERE estado = 0 AND l.cedulaUsuario = u.cedula ORDER BY fecha_hora_inicial")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			$resultados=array();
			while ( $fila = $resultado->fetch_object()){
				$resultados[] = $fila;
			}
			return $resultados;
		}
	}

	public function ActualizarEstadoLlamada(){
		if ($stmt = DB::conexion()->prepare("UPDATE llamadas SET cantidad_desconexiones=?, desconectada=? WHERE id=$this->id")){
			$stmt->bind_param("ii",$this->cantidad_desconexiones, $this->desconectada);
			$stmt->execute();
			return array("result" => true); 
		} 
	}

	public function getLlamadaCompleta(){
		if ($stmt = DB::conexion()->prepare("SELECT TIMESTAMPDIFF(SECOND, fecha_hora_inicial, fecha_hora_final) AS diff, l.*, v.* FROM llamadas l INNER JOIN voyentaxi_usuarios v ON cedulaUsuario = cedula WHERE id = $this->id")){
			$stmt->execute();
			$resultado=$stmt->get_result();
			while ( $fila = $resultado->fetch_object()){

				
				$hours = floor($fila->diff / 3600);
				$mins = floor($fila->diff / 60 % 60);
				$secs = floor($fila->diff % 60);
				$duracion = sprintf('%02d:%02d:%02d', $hours, $mins, $secs); 
				$fila->duracion= $duracion;
				$resultados = $fila;
			}
			return $resultados;
		} 
	}

	public function GetEstadoLlamada(){
		if ($stmt = DB::conexion()->prepare("SELECT cantidad_desconexiones, desconectada, estado FROM llamadas WHERE id = $this->id")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			while ( $fila = $resultado->fetch_object()){
				$resultados = array("cantidad_desconexiones" => $fila->cantidad_desconexiones, "desconectada" => $fila->desconectada, "estado" => $fila->estado);
			}
			return $resultados;
		}
	}

	public function UpdateCallData(){
		if ($stmt = DB::conexion()->prepare("UPDATE llamadas SET latitud_final=?, longitud_final=?, url_video=? WHERE id=$this->id")){
			$stmt->bind_param("sss",$this->latitud_final, $this->longitud_final, $this->url_video);
			$stmt->execute();
			return array("result" => true); 
		} 
	}

	public function DatosLlamadaTabla(){
		if ($stmt = DB::conexion()->prepare("SELECT id, concat(nombre,' ',apellido) as nombre,telefono,email,fecha_hora_inicial FROM llamadas l INNER JOIN voyentaxi_usuarios u ON u.cedula = l.cedulaUsuario WHERE estado = 0 AND id=$this->id")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			$resultados;
			while ( $fila = $resultado->fetch_object()){
				$resultados = $fila;
			}
			return $resultados;
		} 
	}

	public function HistorialLlamadas(){
		if ($stmt = DB::conexion()->prepare("SELECT id, DATE_FORMAT(l.fecha_hora_inicial, '%d-%m-%Y %H:%i:%s') AS fecha_hora_inicial, TIMESTAMPDIFF(SECOND, fecha_hora_inicial, fecha_hora_final) AS diff FROM llamadas l WHERE estado = 0 AND cedulaUsuario=$this->cedulaUsuario AND expirada = 0 ORDER BY l.fecha_hora_inicial DESC")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			$resultados = array();
			while ( $fila = $resultado->fetch_object()){				
				$hours = floor($fila->diff / 3600);
				$mins = floor($fila->diff / 60 % 60);
				$secs = floor($fila->diff % 60);
				$duracion = sprintf('%02d:%02d:%02d', $hours, $mins, $secs); 
				$fila->duracion= $duracion;
				$resultados[] = $fila;
			}
			return array("resultado" => $resultados);
		} 
	}

	public function UpdatePolyline(){
		if ($stmt = DB::conexion()->prepare("UPDATE llamadas SET polyline = ? WHERE id = ?")){
			$stmt->bind_param("si",$this->polyline, $this->id);
			$stmt->execute();
			return array("result" => true); 
		}
	}

	public function LlamadasExpiradas(){
		if ($stmt = DB::conexion()->prepare("SELECT id, DATE_FORMAT(l.fecha_hora_inicial, '%d-%m-%Y %H:%i:%s') AS fecha_hora_inicial, TIMESTAMPDIFF(SECOND, fecha_hora_inicial, fecha_hora_final) AS diff FROM llamadas l WHERE estado = 0 AND cedulaUsuario=$this->cedulaUsuario AND expirada = 1 ORDER BY l.fecha_hora_inicial DESC")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			$resultados = array();
			while ( $fila = $resultado->fetch_object()){				
				$hours = floor($fila->diff / 3600);
				$mins = floor($fila->diff / 60 % 60);
				$secs = floor($fila->diff % 60);
				$duracion = sprintf('%02d:%02d:%02d', $hours, $mins, $secs); 
				$fila->duracion= $duracion;
				$resultados[] = $fila;
			}
			return array("resultado" => $resultados);
		} 
	}

	public function Estadisticas(){
		$resultados = array();
		$anios=array();
		$meses=array();
		$horas=array();
		if ($stmt = DB::conexion()->prepare("SELECT SUBSTRING(fecha_hora_inicial,1,7) as mes, count(SUBSTRING(fecha_hora_inicial,1,7)) as cantidad from llamadas group by mes;")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			while ( $fila = $resultado->fetch_object()){
				$meses[$fila->mes] = $fila->cantidad;
			}
		}
		if ($stmt = DB::conexion()->prepare("SELECT SUBSTRING(fecha_hora_inicial,1,4) as anio, count(SUBSTRING(fecha_hora_inicial,1,4)) as cantidad from llamadas group by anio;")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			while ( $fila = $resultado->fetch_object()){
				$anios[$fila->anio] = $fila->cantidad;
			}
		}
		if ($stmt = DB::conexion()->prepare("SELECT SUBSTRING(fecha_hora_inicial,12,2) as hora, count(SUBSTRING(fecha_hora_inicial,12,2)) as cantidad from llamadas group by hora;")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			while ( $fila = $resultado->fetch_object()){
				$horas[$fila->hora.":00"] = $fila->cantidad;
			}

			$i = 0;
			foreach ($horas as $h) {
				if($i < 10)
					$key = "0" . $i . ":00";
				else
					$key = $i . ":00";
				if(!isset($horas[$key]))
					$horas[$key] = 0;
				$i++;
			}
		}

		$resultados["meses"] = $meses;
		$resultados["anios"] = $anios;
		$resultados["horas"] = $horas;
		
		if ($stmt = DB::conexion()->prepare("SELECT *, DATE_FORMAT(fecha_hora_inicial, '%Y-%m-%d') as fecha_desde, TIMESTAMPDIFF(SECOND, fecha_hora_inicial, fecha_hora_final) AS diff FROM llamadas WHERE estado = 0 ORDER BY fecha_hora_inicial ASC")){
			$stmt->execute();
			$resultado = $stmt->get_result();
			$llamadas = array();
			$cantidadLlamadas = $resultado->num_rows;
			while ( $fila = $resultado->fetch_object()){
				$llamadas[] = $fila;
			}
		}

		if($cantidadLlamadas > 0){
			$fecha_desde = $llamadas[0]->fecha_desde;
			$cantidadDias = 0;
			while($fecha_desde != date('Y-m-d')){
				//$cantidad2++;
				$cantidadDias++;
				$fecha_desde = date('Y-m-d', strtotime($fecha_desde . ' +1 day'));
			}
			$resultados["promedioPorDia"] = number_format((float)$cantidadLlamadas / $cantidadDias, 2, '.', '');

			$tiempoTotal = 0;
			for($i = 0; $i < $cantidadLlamadas; $i++){
				$tiempoTotal += $llamadas[$i]->diff;
			}

			$promedioSegundos = $tiempoTotal / $cantidadLlamadas;

			$hours = floor($promedioSegundos / 3600);
			$mins = floor($promedioSegundos / 60 % 60);
			$secs = floor($promedioSegundos % 60);
			$tiempoPromedio = sprintf('%02d:%02d:%02d', $hours, $mins, $secs); 

			$resultados["promedioDuracion"] = $tiempoPromedio;
			/*$tiempoTotal = 0;
			$limite = $cantidadLlamadas - 1;*
				$fecha1 = new DateTime($llamadas[$i]->fecha_hora_inicial);//fecha inicial
				$fecha2 = new DateTime($llamadas[$i + 1]->fecha_hora_inicial);//fecha de cierre

				$intervalo = $fecha1->diff($fecha2);
				//$tiempoTotal += $intervalo->format('%H:%i:%s');
			}
			$resultados["promedioReporte"] = $intervalo;*/
		}
		else{
			$resultados["promedioPorDia"] = "0";
			$resultados["promedioDuracion"] = "00:00:00";
		}

		$resultados["cantidad"] = $cantidadLlamadas;
		
		return $resultados;
	}
}

?>