<?php

$servidor = "localhost";
$usuario = "root";
$senha = "";
$dbname = "rating_beachtennis";
//$usuario = "u580429014_app";
//$senha = "Caninde.123";
//$dbname = "u580429014_app";
//Criar a conexao
$conn = mysqli_connect($servidor, $usuario, $senha, $dbname);

if(!$conn){
	die("Falha na conexao: " . mysqli_connect_error());
}else{
	//echo "Conexao realizada com sucesso";
}

?>
