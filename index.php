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
  $hashed_token = hash('sha256', $token); // Faz o hash do token recebido do cookie

  // SOLUÇÃO: Usa Prepared Statements e busca pelo HASH do token
  $stmt = mysqli_prepare($conn, "SELECT * FROM usuario WHERE token_login = ? LIMIT 1");
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $hashed_token);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);
  }

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

  <!-- Fonte Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

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
    /* Adicione este CSS dentro da sua tag <style> */

    .google-login-button {
      display: flex;
      /* Para alinhar o ícone e o texto */
      align-items: center;
      justify-content: center;
      width: 100%;
      max-width: 338px;
      margin: 0 auto;
      /* Centraliza o botão */
      font-weight: 600;
      color: #555;
      background-color: transparent;
      /* Fundo transparente */
      border: 1px solid #ddd;
      /* Borda cinza igual ao outro botão */
      padding: 10px;
      border-radius: 10px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.2s;
    }

    .google-login-button:hover {
      background-color: #f0f0f0;
      /* Efeito ao passar o mouse */
    }

    .google-login-button img {
      width: 18px;
      height: 18px;
      margin-right: 10px;
      /* Espaço entre a imagem e o texto */
    }

    /* Fim do novo CSS */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: linear-gradient(-45deg, #0abde3, #10ac84, #5f27cd, #54a0ff);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
      padding: 20px;
    }

    @keyframes gradientBG {
      0% {
        background-position: 0% 50%;
      }

      50% {
        background-position: 100% 50%;
      }

      100% {
        background-position: 0% 50%;
      }
    }

    .container {
      background: white;
      width: 100%;
      max-width: 400px;
      border-radius: 20px;
      padding: 30px 20px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
      text-align: center;
      box-sizing: border-box;
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
      background-color: #e0f7fa;
      /* Light cyan */
    }

    .link-cadastro .btn-esqueci-senha {
      border: 1px solid transparent;
      /* No border initially */
      color: #777;
      /* Slightly darker gray */
      background-color: transparent;
      font-size: 13px;
      /* Slightly smaller */
    }

    /* Estilos para o <details> */
    details>summary {
      list-style: none;
      /* Remove a seta padrão */
      transition: background-color 0.2s;
    }


    /* Adicione este CSS dentro da sua tag <style> */

    .google-login-button {
      display: flex;
      /* Para alinhar o ícone e o texto */
      align-items: center;
      justify-content: center;
      width: 100%;
      max-width: 338px;
      margin: 0 auto;
      /* Centraliza o botão */
      font-weight: 600;
      color: #555;
      background-color: transparent;
      /* Fundo transparente */
      border: 1px solid #ddd;
      /* Borda cinza igual ao outro botão */
      padding: 10px;
      border-radius: 10px;
      cursor: pointer;
      font-size: 16px;
      transition: background-color 0.2s;
    }

    .google-login-button:hover {
      background-color: #f0f0f0;
      /* Efeito ao passar o mouse */
    }

    .google-login-button img {
      width: 18px;
      height: 18px;
      margin-right: 10px;
      /* Espaço entre a imagem e o texto */
    }

    /* Fim do novo CSS */
  </style>
</head>

<body>
  <div class="container">
    <img src="img/dupla.png" alt="Logo Dupla" class="logo">

    <?php if (!empty($_GET['redirect']) && $_GET['redirect'] === 'confirmar-agendamento') : ?>
      <div id="mensagem-reserva" style="background: #e3fcef; color: #207d4c; padding: 12px; border-radius: 10px; font-size: 15px; margin-bottom: 18px; border: 1px solid #c7eacc;">
        🎾 Está quase lá! <strong>Faça login ou cadastre-se</strong> para confirmar sua reserva.
      </div>
      <script>
        setTimeout(() => {
          const el = document.getElementById('mensagem-reserva');
          if (el) el.style.display = 'none';
        }, 5000);
      </script>
    <?php endif; ?>

    <?php if (!empty($_SESSION['DuplaLogin'])): ?>
      <div style="background:#ffeef0;border:1px solid #ffbdbd;color:#c0392b;padding:10px;border-radius:8px;margin-bottom:18px;font-size:14px;">
        <?php
        echo htmlspecialchars($_SESSION['DuplaLogin']);
        unset($_SESSION['DuplaLogin']);
        ?>
      </div>
    <?php endif; ?>

    <!-- Destaque para Login com Google -->
    <button id="custom-google-btn" class="google-login-button">
      <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Logo Google">
      Fazer Login com o Google
    </button>

    <!-- Divisor "ou" -->
    <div style="display: flex; align-items: center; text-align: center; color: #aaa; margin: 20px 0;">
      <div style="flex-grow: 1; border-bottom: 1px solid #ddd;"></div>
      <span style="padding: 0 10px; font-size: 14px;">ou</span>
      <div style="flex-grow: 1; border-bottom: 1px solid #ddd;"></div>
    </div>

    <!-- Login com Telefone (Secundário, colapsado) -->
    <details>
      <summary style="cursor: pointer; font-weight: 600; color: #555; padding: 10px; border: 1px solid #ddd; border-radius: 10px;" onmouseover="this.style.backgroundColor='#f0f0f0'" onmouseout="this.style.backgroundColor='transparent'">
        Entrar com Telefone e Senha
      </summary>
      <form action="system-autenticacao/valida.php" method="post" style="margin-top: 1rem;">
        <input type="tel" name="telefone" placeholder="Telefone" required>
        <input type="password" name="senha" placeholder="Senha" required>
        <div style="text-align:left; margin: 10px 0;">
          <label style="display: flex; align-items: center; font-size: 15px; color: #555; cursor: pointer;">
            <input checked type="checkbox" name="manter_logado" value="1" style="accent-color: #10ac84; width: 18px; height: 18px; margin-right: 8px;">
            Manter logado
          </label>
        </div>
        <button type="submit" style="margin-top: 5px;">Entrar</button>
      </form>
    </details>
    <br>
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
          body: JSON.stringify({
            token: response.credential,
            provider: 'google'
          }),
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
    // Inicialização do Google Sign-In (CORRIGIDO)
    window.onload = function() {
      // 1. A inicialização continua a mesma
      google.accounts.id.initialize({
        client_id: "718722463767-kadfm0scdru0blvhkfd61mdij55rgo6b.apps.googleusercontent.com",
        callback: handleCredentialResponse
      });

      // 2. REMOVEMOS o google.accounts.id.renderButton()
      //    e adicionamos um evento de clique ao nosso botão customizado.

      const customButton = document.getElementById('custom-google-btn');

      if (customButton) { // Garante que o botão exista antes de adicionar o evento
        customButton.addEventListener('click', (e) => {
          e.preventDefault(); // Previne o comportamento padrão do botão, se houver
          google.accounts.id.prompt(); // Inicia o fluxo de login do Google ao clicar
        });
      }
    };
  </script>
</body>

</html>