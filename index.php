<?php 
session_start();

// 1. Se já está logado via session, redireciona
if (isset($_SESSION['DuplaUserId'])) {
  header("Location: principal.php");
  exit;
}

// 2. Se não tem session mas tem cookie, tenta logar pelo cookie
if (!isset($_SESSION['DuplaUserId']) && isset($_COOKIE['DuplaLoginToken'])) {
  include_once("system-autenticacao/conexao.php");
  $token = $_COOKIE['DuplaLoginToken'];

  $query = "SELECT * FROM usuario WHERE token_login = '{$token}' LIMIT 1";
  $resultado = mysqli_query($conn, $query);
  $usuario = mysqli_fetch_assoc($resultado);

  if ($usuario) {
    $_SESSION['DuplaUserId'] = $usuario['id'];
    $_SESSION['DuplaUserNome'] = $usuario['nome'];
    $_SESSION['DuplaUserApelido'] = $usuario['apelido'];
    $_SESSION['DuplaUserTelefone'] = $usuario['telefone'];
    $_SESSION['DuplaUserSenha'] = $usuario['senha'];
    $_SESSION['DuplaUserCidade'] = $usuario['cidade'];
    $_SESSION['DuplaUserEmpunhadura'] = $usuario['empunhadura'];
    header("Location: principal.php");
    exit;
  } else {
    // Cookie inválido: limpa e segue para login
    setcookie('DuplaLoginToken', '', time() - 3600, '/');
    // Não redireciona, mostra o formulário normalmente
  }
}

// 3. Se não tem session nem cookie, mostra o formulário normalmente
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
  <title>DUPLA - Seu Ranking de Beach Tennis</title>
  <meta name="description" content="Registre partidas, evolua no ranking, crie comunidades e compartilhe seus resultados com amigos. DUPLA é o app ideal para beach tennis.">
  <meta name="keywords" content="beach tennis, dupla, ranking, partidas, esportes, app, comunidades, torneios, validação de partidas">
  <meta name="author" content="DUPLA">

  <!-- Open Graph (Facebook, WhatsApp) -->
  <meta property="og:title" content="DUPLA - Seu Ranking de Beach Tennis">
  <meta property="og:description" content="Registre partidas e acompanhe rankings personalizados.">
  <meta property="og:image" content="https://beta.appdupla.com/img/og.png"> <!-- imagem com dimensões 1200x630 -->
  <meta property="og:url" content="https://beta.appdupla.com/">
  <meta property="og:type" content="website">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="DUPLA - Ranking de Beach Tennis">
  <meta name="twitter:description" content="Valide partidas, suba no ranking e jogue com amigos!">
  <meta name="twitter:image" content="https://beta.appdupla.com/img/og.jpg">
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

    .link-cadastro .btn-discreto {
      display: inline-block;
      padding: 8px 15px;
      margin-top: 10px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .link-cadastro .btn-cadastro {
      border: 1px solid #0abde3;
      color: #0abde3;
      background-color: transparent;
    }

    .link-cadastro .btn-cadastro:hover {
      background-color: #e0f7fa; /* Light cyan */
    }

    .link-cadastro .btn-esqueci-senha {
      border: 1px solid transparent; /* No border initially */
      color: #777; /* Slightly darker gray */
      background-color: transparent;
      font-size: 13px; /* Slightly smaller */
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
    <form action="system-autenticacao/valida.php" method="post">
      <!-- Botão de Login do Google -->
      <div id="google-signin-button" style="display: flex; justify-content: center;"></div>

      <!-- Divisor "ou" -->
      <div style="display: flex; align-items: center; text-align: center; color: #aaa; margin: 20px 0;">
        <div style="flex-grow: 1; border-bottom: 1px solid #ddd;"></div>
        <span style="padding: 0 10px; font-size: 14px;">ou</span>
        <div style="flex-grow: 1; border-bottom: 1px solid #ddd;"></div>
      </div>
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
      <p>Não tem conta? <a href="cadastrar.php" class="btn-discreto btn-cadastro">Cadastre-se aqui</a></p>
      <p><a href="recuperar-senha.php" class="btn-discreto btn-esqueci-senha">Esqueci minha senha ):</a></p>
    </div>
  </div>

  <!-- Scripts para Login Social -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>
  <script>
    // Função para lidar com a resposta do Google
    function handleCredentialResponse(response) {
      // Envia o token para o seu backend
      fetch('system-autenticacao/social-login-controller.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ token: response.credential, provider: 'google' }),
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Redireciona para a página principal em caso de sucesso
          window.location.href = 'principal.php';
        } else {
          // Exibe uma mensagem de erro
          alert('Erro no login com Google: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Erro na comunicação com o backend:', error);
        alert('Não foi possível conectar ao servidor. Tente novamente.');
      });
    }

    // Inicialização do Google Sign-In
    window.onload = function () {
      google.accounts.id.initialize({
        client_id: "718722463767-kadfm0scdru0blvhkfd61mdij55rgo6b.apps.googleusercontent.com", // 🚨 SUBSTITUA PELO SEU CLIENT ID DO GOOGLE
        callback: handleCredentialResponse
      });
      google.accounts.id.renderButton(
        document.getElementById("google-signin-button"),
        { theme: "outline", size: "large", width: "338" } // Personalize a aparência do botão
      );
    };
  </script>
</body>
</html>