<?php 
session_start();
if (isset($_SESSION['DuplaUserId'])) {
  header("Location: principal.php");
  exit;
}

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
  header("Location: principal.php");
  exit;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
  <title>DUPLA - Login</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', sans-serif;
    }

    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: linear-gradient(135deg, #0abde3, #10ac84);
      padding: 20px;
    }

    .container {
      background: white;
      width: 100%;
      max-width: 400px;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
      text-align: center;
    }

    .logo {
      width: 100%;
      margin-bottom: 10px;
    }

    h1 {
      font-size: 28px;
      color: #333;
      margin-bottom: 4px;
    }

    p.slogan {
      font-size: 14px;
      color: #555;
      margin-bottom: 30px;
    }

    input[type="tel"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border-radius: 10px;
      border: 1px solid #ccc;
    }

    button {
      width: 100%;
      background: #10ac84;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }

    button:hover {
      background: #0e9473;
    }

    .link-cadastro {
      margin-top: 15px;
      font-size: 14px;
    }

    .link-cadastro a {
      color: #0abde3;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="container">
    <img src="img/dupla.png" alt="Logo Dupla" class="logo">
    <form action="system-autenticacao/valida.php" method="post">
      <input type="tel" name="telefone" placeholder="Telefone" required>
      <input type="password" name="senha" placeholder="Senha" required>
      <div style="text-align:left; margin: 10px 0;">
      <label style="display: flex; align-items: center; font-size: 15px; color: #555; cursor: pointer;">
        <input checked type="checkbox" name="manter_logado" value="1" style="accent-color: #10ac84; width: 18px; height: 18px; margin-right: 8px;">
        Manter logado
      </label>
      </div><br>
      <button type="submit">Entrar</button>
    </form>
    <div class="link-cadastro">
      <p>Não tem conta? <a href="cadastrar.php">Cadastre-se aqui</a></p>
      <br><br>
      <p><a href="cadastrar.php" style="color:#aaa;">Esqueci minha senha ):</a></p>
    </div>
  </div>
</body>
</html>