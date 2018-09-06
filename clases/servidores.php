<?php

require_once("clases/clase_base.php");
require_once("db/db.php");

class Servidores extends ClaseBase{
	private $servidor_bda;
	private $servidor_vet;
	private $servidor_vet_respaldo;

	public function __construct($obj=NULL){
		if(isset($obj)){
			foreach ($obj as $key => $value) {
				$this->$key=$value;
			}
		}
		$tabla = "url_servidores";

		parent::__construct($tabla);
	}

	public function getServidor_bda(){
		return $this->servidor_bda;
	}

	public function getServidor_vet(){
		return $this->servidor_vet;
	}

	public function getServidor_vet_respaldo(){
		return $this->servidor_vet_respaldo;
	}

	public function setServidor_bda($servidor_bda){
		$this->servidor_bda = $servidor_bda;
	}

	public function setServidor_vet($servidor_vet){
		$this->servidor_vet = $servidor_vet;
	}

	public function setServidor_vet_respaldo($servidor_vet_respaldo){
		$this->servidor_vet_respaldo = $servidor_vet_respaldo;
	}

	public function getServidores(){
		$stmt = DB::conexion()->prepare("SELECT * FROM url_servidores");
		$stmt->execute();
		$resultado = $stmt->get_result();
		$resultados = array();
		while($fila = $resultado->fetch_object()){
			$resultados = array("servidor_bda" => $fila->servidor_bda,
								"servidor_vet" => $fila->servidor_vet,
								"servidor_respaldo_vet" => $fila->servidor_vet_respaldo);
		}
		return $resultados;
	}

	public function updateServidores(){
		$stmt = DB::conexion()->prepare("DELETE FROM url_servidores");
		$stmt->execute();
		$stmt = DB::conexion()->prepare("INSERT INTO url_servidores VALUES(?,?,?)");
		$stmt->bind_param("sss",$this->servidor_bda,$this->servidor_vet, $this->servidor_vet_respaldo);
		$stmt->execute();
		return true;
	}
}

?>