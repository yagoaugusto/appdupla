<?php

$servidor = "localhost";
$usuario = "root";
$senha = "";
$dbname = "rating_beachtennis";
//Criar a conexao
$conn = mysqli_connect($servidor, $usuario, $senha, $dbname);

if(!$conn){
	die("Falha na conexao: " . mysqli_connect_error());
}else{
	//echo "Conexao realizada com sucesso";
}

?>
