<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<body class="bg-gray-100 min-h-screen text-gray-800">

  <!-- Navbar superior -->
  <?php require_once '_nav_superior.php' ?>

  <div class="flex pt-16">
    <!-- Menu lateral -->
    <?php require_once '_nav_lateral.php' ?>

    <!-- Conteúdo principal -->
    <main class="flex-1 flex flex-col min-h-screen">
      <section class="flex-grow flex items-center justify-center p-10">
        <div class="text-center max-w-xl">
          <h1 class="text-4xl font-bold mb-4">Bem-vindo ao DUPLA!</h1>
          <p class="text-lg mb-6">Você está no painel principal. Explore suas partidas, veja seu ranking e desbloqueie conquistas jogando Beach Tennis!</p>
        </div>
      </section>

      <!-- Footer -->
      <?php require_once '_footer.php' ?>
    </main>
  </div>

</body>
</html>