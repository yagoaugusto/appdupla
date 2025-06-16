<?php
session_start();
include_once("conexao.php");

if (isset($_SESSION['DuplaUserId'])) {
	$id = $_SESSION['DuplaUserId'];
	mysqli_query($conn, "UPDATE usuario SET token_login = NULL WHERE id = {$id}");
}

// Expira o cookie
setcookie('DuplaLoginToken', '', time() - 3600, "/");

// Destroi a sessão
session_destroy();
header("Location: ../index.php");
exit();