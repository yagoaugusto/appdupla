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

        <div class="mb-3">
          <div class="flex items-center gap-3 bg-blue-600 text-white rounded-xl px-4 py-2 shadow">
            <svg class="w-5 h-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 2l2.39 4.84 5.34.78-3.87 3.77.91 5.32L10 13.27l-4.77 2.51.91-5.32-3.87-3.77 5.34-.78L10 2z" />
            </svg>
            <span class="font-bold text-base sm:text-lg flex-1 truncate">Ranking Geral</span>
          </div>
        </div>


        <!-- Gráfico e ranking -->
        <div class="grid grid-cols-1 gap-3 mb-3">

          <div class="bg-white rounded-xl shadow p-3">
            <ul class="space-y-1">
              <!-- Jogadores acima da posição do usuário -->
              <?php foreach ($ranking_superior as $rank): ?>
                <li class="flex items-center gap-3 rounded-lg px-4 py-2 shadow-sm border-l-8 border-yellow-500 bg-yellow-200">
                  <span class="text-lg font-bold text-yellow-700"><?= $p_sup ?>º</span>
                  <span class="flex-1 font-semibold text-yellow-900">
                    <?= $rank['nome'] ?>
                    <?php if (!empty($rank['apelido'])): ?>
                      <div class="text-xs text-yellow-800 mt-0.5"><?= htmlspecialchars($rank['apelido']) ?></div>
                    <?php endif; ?>
                  </span>
                  <span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded">⭐ <?= $rank['rating'] ?></span>
                </li>
              <?php $p_sup = $p_sup + 1;
              endforeach; ?>

              <!-- Usuário em destaque -->
              <li id="usuario-destaque" class="flex items-center gap-3 rounded-lg px-4 py-2 shadow-sm border-l-8 border-blue-700 bg-blue-200">
                <span class="text-lg font-bold text-blue-800"><?= $usuario[0]['posicao'] ?>º</span>
                <span class="flex-1 font-bold text-blue-900">
                  <?= $usuario[0]['nome'] ?> <span class="ml-2 bg-blue-400 text-blue-900 px-2 py-0.5 rounded-full text-xs font-bold">VOCÊ</span>
                  <?php if (!empty($usuario[0]['apelido'])): ?>
                    <div class="text-xs text-blue-800 mt-0.5"><?= htmlspecialchars($usuario[0]['apelido']) ?></div>
                  <?php endif; ?>
                </span>
                <span class="bg-blue-400 text-blue-900 text-xs font-bold px-2 py-1 rounded">⭐ <?= $usuario[0]['rating'] ?></span>
              </li>

              <!-- Jogadores abaixo da posição do usuário -->
              <?php foreach ($ranking_inferior as $rank): ?>
                <li class="flex items-center gap-3 rounded-lg px-4 py-2 shadow-sm border-l-8 border-gray-500 bg-gray-200">
                  <span class="text-lg font-bold text-gray-700"><?= $p_inf ?>º</span>
                  <span class="flex-1 font-semibold text-gray-900">
                    <?= $rank['nome'] ?>
                    <?php if (!empty($rank['apelido'])): ?>
                      <div class="text-xs text-gray-700 mt-0.5"><?= htmlspecialchars($rank['apelido']) ?></div>
                    <?php endif; ?>
                  </span>
                  <span class="bg-gray-400 text-gray-900 text-xs font-bold px-2 py-1 rounded">⭐ <?= $rank['rating'] ?></span>
                </li>
              <?php $p_inf = $p_inf + 1;
              endforeach; ?>
            </ul>
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