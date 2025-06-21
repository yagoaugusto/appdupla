<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php';

$usuario = Usuario::posicao_usuario($_SESSION['DuplaUserId'] ?? null);
$usuario_id = $_SESSION['DuplaUserId'] ?? null;

$ranking_superior = Usuario::ranking_superior_tela_principal($usuario_id, 112);
$ranking_superior = array_reverse($ranking_superior);
$ranking_inferior = Usuario::ranking_inferior_tela_principal($usuario_id, 112);
$p_sup = $usuario[0]['posicao'] - count($ranking_superior);
$p_inf = $usuario[0]['posicao'] + 1;

?>

<body class="bg-gray-100 min-h-screen text-gray-800 text-sm sm:text-base">

  <?php require_once '_nav_superior.php'; ?>

  <div class="flex pt-16">
    <?php require_once '_nav_lateral.php'; ?>

    <main class="flex-1 flex flex-col min-h-screen p-2">
      <section class="max-w-6xl mx-auto w-full">



        <!-- Botão Nova Partida -->
        <!-- Boas-vindas animada -->

        <!-- Gráfico e ranking -->
        <div class="grid grid-cols-1 gap-3 mb-3">

          <div class="bg-white rounded-xl shadow p-4">
            <h3 class="text-base font-semibold mb-3 flex items-center gap-2 text-gray-700">
              <span class="text-xl">🏆</span>
              Ranking Geral
            </h3>
            <ul class="space-y-1">
              <!-- Jogadores acima da posição do usuário -->
              <?php foreach ($ranking_superior as $rank): ?>
                <li class="flex items-center gap-3 rounded-lg px-4 py-2 shadow-md border-l-4 border-yellow-500 bg-white hover:bg-yellow-50 transition-colors">
                  <span class="text-lg font-bold text-yellow-600 w-8 text-center"><?= $p_sup ?>º</span>
                  <span class="flex-1 font-semibold text-gray-800">
                    <?= $rank['nome'] ?>
                    <?php if (!empty($rank['apelido'])): ?>
                      <span class="text-xs text-gray-500 ml-1">(<?= htmlspecialchars($rank['apelido']) ?>)</span>
                    <?php endif; ?>
                  </span>
                  <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-2 py-1 rounded-full">⭐ <?= htmlspecialchars($rank['rating']) ?></span>
                </li>
              <?php $p_sup = $p_sup + 1;
              endforeach; ?>

              <!-- Usuário em destaque -->
              <li id="usuario-destaque" class="flex items-center gap-3 rounded-lg px-4 py-2 shadow-lg border-l-4 border-blue-600 bg-blue-50 relative">
                <span class="text-lg font-bold text-blue-700 w-8 text-center"><?= $usuario[0]['posicao'] ?>º</span>
                <span class="flex-1 font-bold text-blue-800">
                  <?= htmlspecialchars($usuario[0]['nome']) ?>
                  <?php if (!empty($usuario[0]['apelido'])): ?>
                    <span class="text-xs text-blue-500 ml-1">(<?= htmlspecialchars($usuario[0]['apelido']) ?>)</span>
                  <?php endif; ?>
                </span>
                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full">⭐ <?= htmlspecialchars($usuario[0]['rating']) ?></span>
              </li>

              <!-- Jogadores abaixo da posição do usuário -->
              <?php foreach ($ranking_inferior as $rank): ?>
                <li class="flex items-center gap-3 rounded-lg px-4 py-2 shadow-md border-l-4 border-gray-400 bg-white hover:bg-gray-50 transition-colors">
                  <span class="text-lg font-bold text-gray-600 w-8 text-center"><?= $p_inf ?>º</span>
                  <span class="flex-1 font-semibold text-gray-800">
                    <?= $rank['nome'] ?>
                    <?php if (!empty($rank['apelido'])): ?>
                      <span class="text-xs text-gray-500 ml-1">(<?= htmlspecialchars($rank['apelido']) ?>)</span>
                    <?php endif; ?>
                  </span>
                  <span class="bg-gray-100 text-gray-700 text-xs font-bold px-2 py-1 rounded-full">⭐ <?= htmlspecialchars($rank['rating']) ?></span>
                </li>
              <?php $p_inf = $p_inf + 1;
              endforeach; ?>
            </ul>
            <div class="mt-4 text-center">
                <a href="ranking-geral.php" class="inline-block bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold px-3 py-1.5 rounded-full shadow-sm transition-colors text-xs">
                Ver ranking completo &rarr;
              </a>
            </div>
          </div>
        </div>

      </section>
    </main>
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', function() {
      var destaque = document.getElementById('usuario-destaque');
      if (destaque) {
        destaque.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  </script>

</body>

</html>