<?php
session_start();
require_once '#_global.php'; // Certifique-se de que este arquivo carrega a classe Usuario

$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
$usuario_info = null;

if (empty($token)) {
    $_SESSION['DuplaLogin'] = "Token de recuperação inválido ou ausente.";
    header("Location: index.php");
    exit;
}

// Verifica o HASH do token no banco de dados
$hashed_token = hash('sha256', $token);
$usuario_info = Usuario::getUsuarioByRecoveryToken($hashed_token);

if (!$usuario_info) {
    $_SESSION['DuplaLogin'] = "Token de recuperação inválido ou expirado. Por favor, solicite uma nova recuperação de senha.";
    header("Location: index.php");
    exit;
}

// Se o token é válido, exibe o formulário para redefinir a senha
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
  <title>DUPLA - Redefinir Senha</title>
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
      max-width: 150px;
      margin-bottom: 20px;
    }

    h1 {
      font-size: 24px;
      color: #333;
      margin-bottom: 10px;
    }

    p {
      font-size: 14px;
      color: #555;
      margin-bottom: 20px;
    }

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
  </style>
</head>
<body>
  <div class="container">
    <img src="img/dupla.png" alt="Logo Dupla" class="logo">
    <?php if (!empty($_SESSION['DuplaLogin'])): ?>
      <div style="background:#ffeef0;border:1px solid #ffbdbd;color:#c0392b;padding:10px;border-radius:8px;margin-bottom:18px;font-size:14px;">
        <?php
          echo htmlspecialchars($_SESSION['DuplaLogin']);
          unset($_SESSION['DuplaLogin']);
        ?>
      </div>
    <?php endif; ?>
    <h1>Redefinir Senha</h1>
    <p>Olá, <?= htmlspecialchars($usuario_info['nome']) ?>! Digite sua nova senha.</p>
    <form action="controller-usuario/resetar-senha.php" method="post">
      <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
      <input type="password" name="nova_senha" placeholder="Nova Senha" required>
      <input type="password" name="confirmar_senha" placeholder="Confirmar Nova Senha" required>
      <button type="submit">Redefinir Senha</button>
    </form>
  </div>
</body>
</html>