<?php 
session_start();

if (!isset($_SESSION['DuplaUserId']) && isset($_COOKIE['DuplaLoginToken'])) {
	include_once("system-autenticacao/conexao.php");
	$token = $_COOKIE['DuplaLoginToken'];

	$query = "SELECT * FROM usuario WHERE token_login = '{$token}' LIMIT 1";
	$resultado = mysqli_query($conn, $query);
	$usuario = mysqli_fetch_assoc($resultado);

	if ($usuario) {
		$_SESSION['DuplaUserId'] = $usuario['id'];
		$_SESSION['DuplaUserNome'] = $usuario['nome'];
		$_SESSION['DuplaUserTelefone'] = $usuario['telefone'];
		$_SESSION['DuplaUserSenha'] = $usuario['senha'];
		$_SESSION['DuplaUserCidade'] = $usuario['cidade'];
		$_SESSION['DuplaUserEmpunhadura'] = $usuario['empunhadura'];
	}
 
}else{
  if (!isset($_SESSION['DuplaUserId']) && !isset($_COOKIE['DuplaLoginToken'])) {
    header("Location: index.php");
    exit;
  }
}
?>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
  <title>DUPLA</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.4.20/dist/full.css" rel="stylesheet" type="text/css" />
</head>