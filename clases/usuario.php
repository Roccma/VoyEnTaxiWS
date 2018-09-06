<?php 
require_once("clases/clase_base.php");
require_once("db/db.php");
class Usuario extends ClaseBase{
	private $cedula;
	private $nombre;
	private $apellido;
	private $telefono;
	private $email;
	private $contrasenia;
	/* Constructor */

	public function __construct($obj=NULL){
		if(isset($obj)){
			foreach ($obj as $key => $value) {
				$this->$key=$value;
			}
		}
		$tabla = "usuarios";

		parent::__construct($tabla);
	}

	/* Getters */
	public function getCedula(){
		return $this->cedula;
	}

	public function getEmail(){
		return $this->email;
	}

	public function getTelefono(){
		return $this->telefono;
	}

	public function getNombre(){
		return $this->nombre;
	}

	public function getApellido(){
		return $this->apellido;
	}

	public function getContrasenia(){
		return $this->contrasenia;
	}

	/* Setters */

	public function setNombre($nombre){
		$this->nombre = $nombre;
	}

	public function setApellido($apellido){
		$this->apellido = $apellido;
	}

	public function setContrasenia($contrasenia){
		$this->contrasenia = $contrasenia;
	}

	public function insertarSesion($params){
		$sql = "SELECT * FROM usuarios_session WHERE cedulaUsuario = ?";
		$stmt = DB::conexion()->prepare($sql);
		$stmt->bind_param("i", $params[0]);
		$stmt->execute();
		if ($stmt->get_result()->num_rows==0){
			$sql = "INSERT INTO usuarios_session (cedulaUsuario,session_id,token) VALUES(?,?,?)";
			$stmt = DB::conexion()->prepare($sql);
			$stmt->bind_param("iss", $params[0],$params[1],$params[2]);
			$stmt->execute();
			return "Datos de Sesión Creados";
		} else {
			$sql = "UPDATE usuarios_session SET session_id = ?, token = ? WHERE cedulaUsuario = ?";
			$stmt = DB::conexion()->prepare($sql);
			$stmt->bind_param("ssi", $params[1],$params[2],$params[0]);
			$stmt->execute();
			return "Datos de Sesión Actualizados";
		}
	}

	public function login(){

		$sql = "SELECT * FROM voyentaxi_usuarios WHERE cedula=? AND contrasenia = ?";
		
		$stmt = DB::conexion()->prepare($sql);
		$pass = sha1($this->contrasenia);
		$stmt->bind_param("ss", $this->cedula, $pass);

		$stmt->execute();
		
		$resultado = $stmt->get_result();

		if($resultado->num_rows > 0) {
			$usr = new Usuario($resultado->fetch_object());
			return $usr;
		}
		else
			return false;
	}

	public function getTaxista($params){
		$sql = "SELECT v.* FROM voyentaxi_usuarios v INNER JOIN usuarios_session u ON v.cedula = u.cedulaUsuario WHERE u.session_id = ?";
		
		$stmt = DB::conexion()->prepare($sql);
		$stmt->bind_param("s", $params[0]);

		$stmt->execute();
		
		$resultado = $stmt->get_result();

		$taxista;

		while ( $fila = $resultado->fetch_object()){
			$taxista = array("nombre" => $fila->nombre,
							"apellido" => $fila->apellido,
							"cedula" => $fila->cedula,
							"email" => $fila->email,
							"telefono" => $fila->telefono,
							"empresa" => $fila->empresa);
		}
		return $taxista;
	}

	public function getTaxistaPorId($params){
		$sql = "SELECT v.* FROM voyentaxi_usuarios v INNER JOIN llamadas l ON v.cedula = l.cedulaUsuario WHERE l.id = ?";
		
		$stmt = DB::conexion()->prepare($sql);
		$stmt->bind_param("i", $params[0]);

		$stmt->execute();
		
		$resultado = $stmt->get_result();

		$taxista;

		while ( $fila = $resultado->fetch_object()){
			$taxista = array("nombre" => $fila->nombre,
							"apellido" => $fila->apellido,
							"cedula" => $fila->cedula,
							"email" => $fila->email,
							"telefono" => $fila->telefono,
							"empresa" => $fila->empresa);
		}
		return $taxista;
	}

	public function KeepAlive(){
		$stmt = DB::conexion()->prepare("DELETE FROM keep_alive WHERE cedulaUsuario = ?");
		$stmt->bind_param("i",$this->cedula);
		$stmt->execute();
		$stmt = DB::conexion()->prepare("INSERT INTO keep_alive VALUES(?,NOW())");
		$stmt->bind_param("i",$this->cedula);
		$stmt->execute();
		return array("result" => true, "message" => "ok");
	}

	
	

} 

?>