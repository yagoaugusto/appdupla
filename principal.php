<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php';
$usuario = Usuario::posicao_usuario($_SESSION['DuplaUserId'] ?? null);


$usuario_id = $_SESSION['DuplaUserId'] ?? null;

$partidas_usuario = Usuario::partidas_usuario($usuario_id);
$variacao_rating = Usuario::variacao_rating($usuario_id, 10);

$ranking_superior = Usuario::ranking_superior_tela_principal($usuario_id, 2);
$ranking_superior = array_reverse($ranking_superior);
$ranking_inferior = Usuario::ranking_inferior_tela_principal($usuario_id, 2);
$p_sup = $usuario[0]['posicao'] - count($ranking_superior);
$p_inf = $usuario[0]['posicao'] + 1;

$parceiro_vitoria = Usuario::quadro_honra_parceiro_vitoria($usuario_id);
$parceiro_derrota = Usuario::quadro_honra_parceiro_derrota($usuario_id);
$adversario_vitoria = Usuario::quadro_honra_adversario_vitoria($usuario_id);
$adversario_derrota = Usuario::quadro_honra_adversario_derrota($usuario_id);

$hist_rating = Usuario::historico_rating($usuario_id);
$hist_rating = array_reverse($hist_rating);
$labels = [];
$dados = [];
foreach ($hist_rating as $registro) {
  $labels[] = date('d M', strtotime($registro['data']));
  $dados[] = $registro['rating_novo'];
}
?>

<body class="bg-gray-100 min-h-screen text-gray-800 text-sm sm:text-base">

  <?php require_once '_nav_superior.php'; ?>

  <div class="flex pt-16">
    <?php require_once '_nav_lateral.php'; ?>

    <main class="flex-1 flex flex-col min-h-screen p-2">
      <section class="max-w-6xl mx-auto w-full">



        <!-- Botão Nova Partida -->
        <!-- Boas-vindas animada -->
        <?php
        $mensagens_boas_vindas = [
          "Bem-vindo de volta, <strong>{$usuario[0]['nome']}</strong>! Pronto para mais uma rodada de desafios?",
          "Olá, <strong>{$usuario[0]['nome']}</strong>! Que tal subir no ranking hoje?",
          "É hora do show, <strong>{$usuario[0]['nome']}</strong>! Mostre seu talento!",
          "Que a sorte esteja com você, <strong>{$usuario[0]['nome']}</strong>! Vamos jogar?",
          "Seja bem-vindo, <strong>{$usuario[0]['nome']}</strong>! Sua próxima vitória está logo ali.",
          "Preparado para fazer história, <strong>{$usuario[0]['nome']}</strong>?",
          "Os campeões nunca descansam, <strong>{$usuario[0]['nome']}</strong>! Boa sorte!",
          "Hora de brilhar, <strong>{$usuario[0]['nome']}</strong>! O topo te espera.",
          "Vamos com tudo, <strong>{$usuario[0]['nome']}</strong>! Hoje é dia de subir no pódio!",
          "Você está de volta, <strong>{$usuario[0]['nome']}</strong>! Bora mostrar quem manda na quadra.",
          "Chegou o momento, <strong>{$usuario[0]['nome']}</strong>! Cada ponto conta.",
          "Com garra e talento, <strong>{$usuario[0]['nome']}</strong>! Vamos conquistar mais uma.",
          "Seu ranking agradece, <strong>{$usuario[0]['nome']}</strong>! Hora de jogar sério.",
          "Foco, força e raquete, <strong>{$usuario[0]['nome']}</strong>! Vamos pra cima!",
          "É só você e a rede, <strong>{$usuario[0]['nome']}</strong>! Mostre do que é feito.",
          "Ninguém segura você hoje, <strong>{$usuario[0]['nome']}</strong>!",
          "Você nasceu pra esse jogo, <strong>{$usuario[0]['nome']}</strong>! Partiu vitória.",
          "Está preparado, <strong>{$usuario[0]['nome']}</strong>? As quadras te esperam!",
          "Mais um dia, mais uma chance de vencer, <strong>{$usuario[0]['nome']}</strong>!",
          "A energia está no ar, <strong>{$usuario[0]['nome']}</strong>! Traga sua melhor versão.",
          "Desafios à vista, <strong>{$usuario[0]['nome']}</strong>! E você está pronto.",
          "O topo é seu destino, <strong>{$usuario[0]['nome']}</strong>! Continue escalando.",
          "Confiança no saque e coragem no smash, <strong>{$usuario[0]['nome']}</strong>! Vamos!"
        ];
        $mensagem_aleatoria = $mensagens_boas_vindas[array_rand($mensagens_boas_vindas)];
        ?>
        <div class="mb-8">
          <div class="flex items-center gap-4 bg-gradient-to-r from-blue-600 via-blue-400 to-indigo-500 text-white rounded-2xl px-6 py-5 shadow-lg animate-fade-in-down">
            <svg class="w-10 h-10 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 2l2.39 4.84 5.34.78-3.87 3.77.91 5.32L10 13.27l-4.77 2.51.91-5.32-3.87-3.77 5.34-.78L10 2z" />
            </svg>
            <div class="text-base font-bold drop-shadow-sm">
              <?= $mensagem_aleatoria ?>
            </div>
          </div>
        </div>

        <!-- Botão Nova Partida estilizado -->
        <div class="mb-3 flex justify-left">
          <a href="nova-partida.php"
            class="group block w-full sm:w-auto text-center px-4 py-2 bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 hover:from-indigo-700 hover:to-blue-700 text-white font-bold text-base rounded-xl shadow border-b-2 border-blue-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-300 uppercase tracking-wide overflow-hidden"
            style="letter-spacing:0.06em;">
            Registrar Partida
            <span class="inline-block align-middle ml-1 text-xl animate-pulse drop-shadow-sm">➕</span>
          </a>
        </div>

        <!-- Cards de informações -->
        <div class="grid grid-cols-2 gap-2 mb-3">
          <div class="bg-white rounded-xl shadow p-2 text-center border-t-2 border-blue-500">
            <div class="text-xl font-bold text-blue-600">⭐ <?= $usuario[0]['rating'] ?></div>
            <div class="text-xs text-gray-500 mt-1">Rating</div>
          </div>
          <div class="bg-white rounded-xl shadow p-2 text-center border-t-2 border-green-500">
            <div class="text-xl font-bold text-green-600">🏅 <?= $partidas_usuario[0]['total_partidas'] ?> | <?= $partidas_usuario[0]['vitorias'] ?></div>
            <div class="text-xs text-gray-500 mt-1">Partidas | Vitórias</div>
          </div>
          <div class="bg-white rounded-xl shadow p-2 text-center border-t-2 border-yellow-500">
            <div class="text-xl font-bold text-yellow-600">🎖️ ?? </div>
            <div class="text-xs text-gray-500 mt-1">Conquistas</div>
          </div>
          <div class="bg-white rounded-xl shadow p-2 text-center border-t-2 border-purple-500">
            <div class="text-xl font-bold text-purple-600">📈 <?= round($variacao_rating[0]['variacao_rating'], 3) ?></div>
            <div class="text-xs text-gray-500 mt-1">Variação (10d)</div>
          </div>
        </div>

        <!-- Gráfico e ranking -->
        <div class="grid grid-cols-1 gap-3 mb-3">
          <div class="bg-white rounded-xl shadow p-3">
            <h3 class="text-base font-semibold mb-2">📊 Histórico de Rating</h3>
            <canvas id="graficoRating" height="100"></canvas>
          </div>
          <div class="bg-white rounded-xl shadow p-3">
            <h3 class="text-base font-semibold mb-2 flex items-center gap-1">
              <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2l2.39 4.84 5.34.78-3.87 3.77.91 5.32L10 13.27l-4.77 2.51.91-5.32-3.87-3.77 5.34-.78L10 2z" />
              </svg>
              Ranking
            </h3>
            <ul class="space-y-1">
              <!-- Jogadores acima da posição do usuário -->
              <?php foreach ($ranking_superior as $rank): ?>
                <li class="flex items-center gap-3 bg-gradient-to-r from-yellow-100 to-yellow-50 rounded-lg px-4 py-2 shadow-sm border-l-4 border-yellow-400 hover:scale-105 transition-transform">
                  <span class="text-lg font-bold text-yellow-600 drop-shadow"><?= $p_sup ?>º</span>
                  <span class="flex-1 font-semibold text-yellow-800">
                    <?= $rank['nome'] ?>
                    <?php if (!empty($rank['apelido'])): ?>
                      <div class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($rank['apelido']) ?></div>
                    <?php endif; ?>
                  </span>
                  <span class="bg-yellow-200 text-yellow-800 text-xs font-bold px-2 py-1 rounded">⭐ <?= $rank['rating'] ?></span>
                </li>
              <?php $p_sup = $p_sup + 1;
              endforeach; ?>

              <!-- Usuário em destaque -->
              <li class="flex items-center gap-3 bg-gradient-to-r from-blue-100 via-blue-50 to-blue-200 rounded-xl px-4 py-3 shadow-lg border-l-8 border-blue-500 scale-105 ring-2 ring-blue-300">
                <span class="text-xl font-extrabold text-blue-700 drop-shadow-lg animate-pulse"><?= $usuario[0]['posicao'] ?>º</span>
                <span class="flex-1 font-extrabold text-blue-900 text-lg tracking-wide">
                  <?= $usuario[0]['nome'] ?> <span class="ml-2 bg-blue-300 text-blue-900 px-2 py-0.5 rounded-full text-xs font-bold">VOCÊ</span>
                  <?php if (!empty($usuario[0]['apelido'])): ?>
                    <div class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($usuario[0]['apelido']) ?></div>
                  <?php endif; ?>
                </span>
                <span class="bg-blue-100 text-blue-800 text-sm font-bold px-3 py-1 rounded shadow">⭐ <?= $usuario[0]['rating'] ?></span>
              </li>

              <!-- Jogadores abaixo da posição do usuário -->
              <?php foreach ($ranking_inferior as $rank): ?>
                <li class="flex items-center gap-3 bg-gradient-to-r from-gray-100 to-white rounded-lg px-4 py-2 shadow-sm border-l-4 border-gray-400 hover:scale-105 transition-transform">
                  <span class="text-lg font-bold text-gray-500"><?= $p_inf ?>º</span>
                  <span class="flex-1 font-semibold text-gray-700">
                    <?= $rank['nome'] ?>
                    <?php if (!empty($rank['apelido'])): ?>
                      <div class="text-xs text-gray-400 mt-0.5"><?= htmlspecialchars($rank['apelido']) ?></div>
                    <?php endif; ?>
                  </span>
                  <span class="bg-gray-200 text-gray-700 text-xs font-bold px-2 py-1 rounded">⭐ <?= $rank['rating'] ?></span>
                </li>
              <?php $p_inf = $p_inf + 1;
              endforeach; ?>
            </ul>
            <div class="mt-2 text-center">
              <a href="ranking.php" class="inline-block bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold px-2 py-1 rounded-full shadow transition-colors text-xs">
                Ver ranking completo &rarr;
              </a>
            </div>
          </div>
        </div>

        <?php if (empty($parceiro_vitoria)) { ?>

          <!-- Quadro de Honra (ou nem tanto) - Versão Futurista -->
          <div class="bg-[#0f172a] rounded-3xl border border-blue-600 p-8 shadow-xl mb-12">
            <h3 class="text-3xl font-black mb-10 text-blue-300 tracking-wide flex flex-col sm:flex-row items-start sm:items-center gap-1 sm:gap-3 uppercase">
              🏅 Quadro de Honra
              <span class="text-sm font-medium lowercase sm:ml-2 mt-1 sm:mt-0"> (ou nem tanto)</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

              <!-- Meu Pato -->
              <div class="bg-gradient-to-tr from-yellow-400 via-yellow-300 to-yellow-500 text-gray-900 rounded-2xl p-5 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition duration-300 relative">
                <div class="text-5xl font-bold absolute -top-5 -right-5 rotate-6 drop-shadow-md">🦆</div>
                <h4 class="text-xl font-extrabold mb-2">MEU PATO</h4>
                <p class="text-sm font-medium">🟡 Vitórias esmagadoras contra:</p>
                <p class="text-lg font-semibold mt-1"> ???? </p>
                <p class="text-sm mt-2">🔁 <strong> ?? jogos</strong> · ✅ <strong>?? vitórias</strong></p>
              </div>

              <!-- Meu Carrasco -->
              <div class="bg-gradient-to-tr from-red-600 via-red-500 to-pink-500 text-white rounded-2xl p-5 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition duration-300 relative">
                <div class="text-5xl font-bold absolute -top-5 -right-5 rotate-6 opacity-90 drop-shadow">💀</div>
                <h4 class="text-xl font-extrabold mb-2">MEU CARRASCO</h4>
                <p class="text-sm font-medium">🔴 Derrotas doloridas para:</p>
                <p class="text-lg font-semibold mt-1">????</p>
                <p class="text-sm mt-2">🔁 <strong>?? jogos</strong> · ❌ <strong>?? derrotas</strong></p>
              </div>

              <!-- Dupla Forte -->
              <div class="bg-gradient-to-tr from-emerald-500 via-lime-400 to-green-400 text-gray-900 rounded-2xl p-5 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition duration-300 relative">
                <div class="text-5xl font-bold absolute -top-5 -right-5 rotate-6 drop-shadow-lg">🤝</div>
                <h4 class="text-xl font-extrabold mb-2">DUPLA FORTE</h4>
                <p class="text-sm font-medium">🟢 Vitória em sintonia com:</p>
                <p class="text-lg font-semibold mt-1">????</p>
                <p class="text-sm mt-2">🤝 <strong>?? jogos</strong> · ✅ <strong>?? vitórias</strong></p>
              </div>

              <!-- Só Atrapalha -->
              <div class="bg-gradient-to-tr from-slate-500 via-slate-400 to-gray-300 text-gray-900 rounded-2xl p-5 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition duration-300 relative">
                <div class="text-5xl font-bold absolute -top-5 -right-5 rotate-6 drop-shadow-md opacity-80">🐢</div>
                <h4 class="text-xl font-extrabold mb-2">SÓ ATRAPALHA</h4>
                <p class="text-sm font-medium">⚫ Parceria que não rolou com:</p>
                <p class="text-lg font-semibold mt-1">????</p>
                <p class="text-sm mt-2">🤷 <strong>?? jogos</strong> · ❌ <strong>?? derrotas</strong></p>
              </div>

            </div>

            <div class="mt-10 text-center">
              <a href="ranking.php" class="inline-block bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-indigo-700 hover:to-blue-600 text-white font-bold px-8 py-3 rounded-full shadow-xl tracking-wider uppercase transition">
                Ver outras conquistas &rarr;
              </a>
            </div>
          </div>

        <?php } else { ?>

          <!-- Quadro de Honra (ou nem tanto) - Versão Futurista -->
          <div class="bg-[#0f172a] rounded-3xl border border-blue-600 p-8 shadow-xl mb-12">
            <h3 class="text-3xl font-black mb-10 text-blue-300 tracking-wide flex flex-col sm:flex-row items-start sm:items-center gap-1 sm:gap-3 uppercase">
              🏅 Quadro de Honra
              <span class="text-sm font-medium lowercase sm:ml-2 mt-1 sm:mt-0"> (ou nem tanto)</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

              <!-- Meu Pato -->
              <div class="bg-gradient-to-tr from-yellow-400 via-yellow-300 to-yellow-500 text-gray-900 rounded-2xl p-5 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition duration-300 relative">
                <div class="text-5xl font-bold absolute -top-5 -right-5 rotate-6 drop-shadow-md">🦆</div>
                <h4 class="text-xl font-extrabold mb-2">MEU PATO</h4>
                <p class="text-sm font-medium">🟡 Vitórias esmagadoras contra:</p>
                <p class="text-lg font-semibold mt-1"><?= $adversario_vitoria[0]['adversario_nome'] ?></p>
                <p class="text-sm mt-2">🔁 <strong><?= $adversario_vitoria[0]['partidas'] ?> jogos</strong> · ✅ <strong><?= $adversario_vitoria[0]['vitorias'] ?> vitórias</strong></p>
              </div>

              <!-- Meu Carrasco -->
              <div class="bg-gradient-to-tr from-red-600 via-red-500 to-pink-500 text-white rounded-2xl p-5 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition duration-300 relative">
                <div class="text-5xl font-bold absolute -top-5 -right-5 rotate-6 opacity-90 drop-shadow">💀</div>
                <h4 class="text-xl font-extrabold mb-2">MEU CARRASCO</h4>
                <p class="text-sm font-medium">🔴 Derrotas doloridas para:</p>
                <p class="text-lg font-semibold mt-1"><?= $adversario_derrota[0]['adversario_nome'] ?></p>
                <p class="text-sm mt-2">🔁 <strong><?= $adversario_derrota[0]['partidas'] ?> jogos</strong> · ❌ <strong><?= $adversario_derrota[0]['derrotas'] ?> derrotas</strong></p>
              </div>

              <!-- Dupla Forte -->
              <div class="bg-gradient-to-tr from-emerald-500 via-lime-400 to-green-400 text-gray-900 rounded-2xl p-5 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition duration-300 relative">
                <div class="text-5xl font-bold absolute -top-5 -right-5 rotate-6 drop-shadow-lg">🤝</div>
                <h4 class="text-xl font-extrabold mb-2">DUPLA FORTE</h4>
                <p class="text-sm font-medium">🟢 Vitória em sintonia com:</p>
                <p class="text-lg font-semibold mt-1"><?= $parceiro_vitoria[0]['parceiro_nome'] ?></p>
                <p class="text-sm mt-2">🤝 <strong><?= $parceiro_vitoria[0]['partidas'] ?> jogos</strong> · ✅ <strong><?= $parceiro_vitoria[0]['vitorias'] ?> vitórias</strong></p>
              </div>

              <!-- Só Atrapalha -->
              <div class="bg-gradient-to-tr from-slate-500 via-slate-400 to-gray-300 text-gray-900 rounded-2xl p-5 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition duration-300 relative">
                <div class="text-5xl font-bold absolute -top-5 -right-5 rotate-6 drop-shadow-md opacity-80">🐢</div>
                <h4 class="text-xl font-extrabold mb-2">SÓ ATRAPALHA</h4>
                <p class="text-sm font-medium">⚫ Parceria que não rolou com:</p>
                <p class="text-lg font-semibold mt-1"><?= $parceiro_derrota[0]['parceiro_nome'] ?></p>
                <p class="text-sm mt-2">🤷 <strong><?= $parceiro_derrota[0]['partidas'] ?> jogos</strong> · ❌ <strong><?= $parceiro_vitoria[0]['derrotas'] ?> derrotas</strong></p>
              </div>

            </div>

            <div class="mt-10 text-center">
              <a href="ranking.php" class="inline-block bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-indigo-700 hover:to-blue-600 text-white font-bold px-8 py-3 rounded-full shadow-xl tracking-wider uppercase transition">
                Ver outras conquistas &rarr;
              </a>
            </div>
          </div>

        <?php } ?>

      </section>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

  <script>
    const ctx = document.getElementById('graficoRating').getContext('2d');
    const graficoRating = new Chart(ctx, {
      type: 'line',
      data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
          label: 'Rating',
          data: <?= json_encode($dados) ?>,
          borderColor: '#2563EB',
          backgroundColor: 'rgba(37, 99, 235, 0.1)',
          tension: 0.4,
          fill: true,
          pointRadius: 6,
          pointHoverRadius: 8,
          pointBackgroundColor: '#1D4ED8',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
          datalabels: {
            anchor: 'end',
            align: 'top',
            color: '#1E40AF',
            font: {
              weight: 'bold'
            },
            formatter: function(value) {
              return value;
            }
          }
        },
        scales: {
          x: {
            ticks: {
              color: '#374151'
            },
            grid: {
              color: 'rgba(0,0,0,0.05)'
            }
          },
          y: {
            beginAtZero: false,
            ticks: {
              color: '#374151'
            },
            grid: {
              color: 'rgba(0,0,0,0.05)'
            }
          }
        }
      },
      plugins: [ChartDataLabels]
    });
  </script>

</body>

</html>