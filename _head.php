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
    $_SESSION['DuplaUserApelido'] = $usuario['apelido'];
    $_SESSION['DuplaUserTelefone'] = $usuario['telefone'];
    $_SESSION['DuplaUserSenha'] = $usuario['senha'];
    $_SESSION['DuplaUserCidade'] = $usuario['cidade'];
    $_SESSION['DuplaUserEmpunhadura'] = $usuario['empunhadura'];
  } else {
    // Token inválido: limpa o cookie e força login
    setcookie('DuplaLoginToken', '', time() - 3600, '/');
    header("Location: index.php");
    exit;
  }
} else {
  if (!isset($_SESSION['DuplaUserId']) && !isset($_COOKIE['DuplaLoginToken'])) {
    header("Location: index.php");
    exit;
  }
}
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
  <!-- SEO Meta Tags -->
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
  <meta name="color-scheme" content="light">
  <?php $version = time(); // Use um timestamp para forçar o recarregamento durante o desenvolvimento. Em produção, use uma string de versão fixa. 
  ?>
  <script src="https://cdn.tailwindcss.com?v=<?php echo $version; ?>"></script>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.4.20/dist/full.css?v=<?php echo $version; ?>" rel="stylesheet" type="text/css" />

  <script>
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const backdrop = document.getElementById('sidebar-backdrop');
      if (sidebar && backdrop) {
        sidebar.classList.toggle('-translate-x-full'); // Toggles sidebar visibility
        backdrop.classList.toggle('hidden'); // Toggles backdrop visibility
        document.body.classList.toggle('overflow-hidden'); // Toggles body scroll lock
      }
    }
  </script>
</head>