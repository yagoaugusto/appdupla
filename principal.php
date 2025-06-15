<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php';

$usuario_id = $_SESSION['DuplaUserId'];
$usuario = Usuario::posicao_usuario($usuario_id);

$partidas_usuario = Usuario::partidas_usuario($usuario_id);
$variacao_rating = Usuario::variacao_rating($usuario_id, 10);
$qtd_partida_pendente = Partida::qtd_partida_pendente($usuario_id);

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
        <div class="mb-3">
          <div class="flex items-center gap-3 bg-blue-600 text-white rounded-xl px-4 py-2 shadow">
            <span class="text-xl">👋</span>
            <span class="font-medium"><?= $mensagem_aleatoria ?></span>
          </div>
        </div>

        <!-- Botão Nova Partida estilizado -->
        <div class="mb-2 flex gap-2">
          <!-- Botão Nova Partida -->
          <a href="nova-partida.php"
            class="flex-1 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow transition flex items-center gap-2 justify-center">
            Registrar Partida
            <span class="text-xl">➕</span>
          </a>
          <!-- Botão Validar Partidas -->
          <a href="validar-partidas.php"
            class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow transition flex items-center gap-2 justify-center">
            Validar Partidas
            <span class="ml-2 bg-white text-blue-700 font-bold px-2 py-0.5 rounded-full text-xs">
              <?= $qtd_partida_pendente[0]['quantidade'] ?>
            </span>
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
            <div class="bg-white rounded-xl shadow p-3 flex flex-col gap-2">
              <div class="flex items-center gap-2 mb-1">
              <span class="text-xl">📊</span>
              <span class="font-semibold text-blue-700 text-base tracking-wide drop-shadow-sm">Histórico de Rating</span>
              </div>
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
                <?php $p_sup = $p_sup + 1; endforeach; ?>

              <!-- Usuário em destaque -->
              <li class="flex items-center gap-3 rounded-lg px-4 py-2 shadow-sm border-l-8 border-blue-700 bg-blue-200">
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
                <?php $p_inf = $p_inf + 1; endforeach; ?>
            </ul>
            <div class="mt-2 text-center">
              <a href="ranking-geral.php" class="inline-block bg-yellow-400 hover:bg-yellow-500 text-blue-900 font-bold px-2 py-1 rounded-full shadow transition-colors text-xs">
                Ver ranking completo &rarr;
              </a>
            </div>
          </div>
        </div>

        
        <?php if (empty($parceiro_vitoria)) { ?>

<!-- Quadro de Honra (ou nem tanto) - Versão Futurista -->
          <div class="bg-[#0f172a] rounded-2xl border border-blue-600 p-4 shadow-lg mb-6">
            <h3 class="text-lg font-black mb-4 text-blue-300 tracking-wide flex items-center gap-2 uppercase">
              🏅 Quadro de Honra
              <span class="text-xs font-medium lowercase">(ou nem tanto) | Você ainda não tem nenhuma partida para exibir.</span>
            </h3>
            <div class="grid grid-cols-2 gap-2">
              <!-- Meu Pato -->
              <div class="bg-yellow-300 text-gray-900 rounded-xl p-2 shadow flex flex-col items-start relative min-h-[90px]">
                <div class="text-2xl font-bold absolute top-1 right-2">🦆</div>
                <div class="text-xs font-bold mb-1">MEU PATO</div>
                <div class="text-xs">🟡 Vitórias contra:</div>
                <div class="text-sm font-semibold truncate">???</div>
                <div class="text-xs mt-1">🔁 <b>???</b> · ✅ <b>??? Vitórias</b></div>
              </div>
              <!-- Meu Carrasco -->
              <div class="bg-red-500 text-white rounded-xl p-2 shadow flex flex-col items-start relative min-h-[90px]">
                <div class="text-2xl font-bold absolute top-1 right-2">💀</div>
                <div class="text-xs font-bold mb-1">MEU CARRASCO</div>
                <div class="text-xs">🔴 Derrotas para:</div>
                <div class="text-sm font-semibold truncate">???</div>
                <div class="text-xs mt-1">🔁 <b>???</b> · ❌ <b>??? Derrotas</b></div>
              </div>
              <!-- Dupla Forte -->
              <div class="bg-lime-300 text-gray-900 rounded-xl p-2 shadow flex flex-col items-start relative min-h-[90px]">
                <div class="text-2xl font-bold absolute top-1 right-2">🤝</div>
                <div class="text-xs font-bold mb-1">DUPLA FORTE</div>
                <div class="text-xs">🟢 Com:</div>
                <div class="text-sm font-semibold truncate">???</div>
                <div class="text-xs mt-1">🤝 <b>???</b> · ✅ <b>??? Vitórias</b></div>
              </div>
              <!-- Só Atrapalha -->
              <div class="bg-slate-300 text-gray-900 rounded-xl p-2 shadow flex flex-col items-start relative min-h-[90px]">
                <div class="text-2xl font-bold absolute top-1 right-2">🐢</div>
                <div class="text-xs font-bold mb-1">SÓ ATRAPALHA</div>
                <div class="text-xs">⚫ Com:</div>
                <div class="text-sm font-semibold truncate">???</div>
                <div class="text-xs mt-1">🤷 <b>???</b> · ❌ <b>??? Derrotas</b></div>
              </div>
            </div>
            <div class="mt-4 text-center">
              <a href="#" class="inline-block bg-blue-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-full shadow text-xs uppercase transition">
                Ver conquistas &rarr;
              </a>
            </div>
          </div>

        <?php } else { ?>

          <!-- Quadro de Honra (ou nem tanto) - Versão Futurista -->
          <div class="bg-[#0f172a] rounded-2xl border border-blue-600 p-4 shadow-lg mb-6">
            <h3 class="text-lg font-black mb-4 text-blue-300 tracking-wide flex items-center gap-2 uppercase">
              🏅 Quadro de Honra
              <span class="text-xs font-medium lowercase">(ou nem tanto)</span>
            </h3>
            <div class="grid grid-cols-2 gap-2">
              <!-- Meu Pato -->
              <div class="bg-yellow-300 text-gray-900 rounded-xl p-2 shadow flex flex-col items-start relative min-h-[90px]">
                <div class="text-2xl font-bold absolute top-1 right-2">🦆</div>
                <div class="text-xs font-bold mb-1">MEU PATO</div>
                <div class="text-xs">🟡 Vitórias contra:</div>
                <div class="text-sm font-semibold truncate"><?= $adversario_vitoria[0]['adversario_nome'] ?></div>
                <div class="text-xs mt-1">🔁 <b><?= $adversario_vitoria[0]['partidas'] ?></b> · ✅ <b><?= $adversario_vitoria[0]['vitorias'] ?> Vitórias</b></div>
              </div>
              <!-- Meu Carrasco -->
              <div class="bg-red-500 text-white rounded-xl p-2 shadow flex flex-col items-start relative min-h-[90px]">
                <div class="text-2xl font-bold absolute top-1 right-2">💀</div>
                <div class="text-xs font-bold mb-1">MEU CARRASCO</div>
                <div class="text-xs">🔴 Derrotas para:</div>
                <div class="text-sm font-semibold truncate"><?= $adversario_derrota[0]['adversario_nome'] ?></div>
                <div class="text-xs mt-1">🔁 <b><?= $adversario_derrota[0]['partidas'] ?></b> · ❌ <b><?= $adversario_derrota[0]['derrotas'] ?> Derrotas</b></div>
              </div>
              <!-- Dupla Forte -->
              <div class="bg-lime-300 text-gray-900 rounded-xl p-2 shadow flex flex-col items-start relative min-h-[90px]">
                <div class="text-2xl font-bold absolute top-1 right-2">🤝</div>
                <div class="text-xs font-bold mb-1">DUPLA FORTE</div>
                <div class="text-xs">🟢 Com:</div>
                <div class="text-sm font-semibold truncate"><?= $parceiro_vitoria[0]['parceiro_nome'] ?></div>
                <div class="text-xs mt-1">🤝 <b><?= $parceiro_vitoria[0]['partidas'] ?></b> · ✅ <b><?= $parceiro_vitoria[0]['vitorias'] ?> Vitórias</b></div>
              </div>
              <!-- Só Atrapalha -->
              <div class="bg-slate-300 text-gray-900 rounded-xl p-2 shadow flex flex-col items-start relative min-h-[90px]">
                <div class="text-2xl font-bold absolute top-1 right-2">🐢</div>
                <div class="text-xs font-bold mb-1">SÓ ATRAPALHA</div>
                <div class="text-xs">⚫ Com:</div>
                <div class="text-sm font-semibold truncate"><?= $parceiro_derrota[0]['parceiro_nome'] ?></div>
                <div class="text-xs mt-1">🤷 <b><?= $parceiro_derrota[0]['partidas'] ?></b> · ❌ <b><?= $parceiro_derrota[0]['derrotas'] ?> Derrotas</b></div>
              </div>
            </div>
            <div class="mt-4 text-center">
              <a href="#" class="inline-block bg-blue-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-full shadow text-xs uppercase transition">
                Ver conquistas &rarr;
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
    const labels = <?= json_encode($labels) ?>;
    const dados = <?= json_encode($dados) ?>;

    // Função para mostrar apenas o primeiro, meio e último label e rating
    function customXTicks(value, index, ticks) {
      if (index === 0) return labels[0];
      if (index === Math.floor((labels.length - 1) / 2)) return labels[Math.floor((labels.length - 1) / 2)];
      if (index === labels.length - 1) return labels[labels.length - 1];
      return '';
    }

    // Função para mostrar apenas o primeiro, meio e último valor no gráfico
    function customPointLabels(context) {
      const idx = context.dataIndex;
      if (idx === 0 || idx === Math.floor((dados.length - 1) / 2) || idx === dados.length - 1) {
        return dados[idx];
      }
      return '';
    }

    const ctx = document.getElementById('graficoRating').getContext('2d');
    const graficoRating = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Rating',
          data: dados,
          borderColor: 'rgba(59,130,246,1)',
          backgroundColor: 'rgba(59,130,246,0.08)',
          tension: 0.5,
          fill: true,
          pointRadius: 0,
          pointHoverRadius: 0,
          borderWidth: 3,
          pointBackgroundColor: 'rgba(59,130,246,1)',
          pointBorderColor: '#fff',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          datalabels: {
            display: false
          },
          tooltip: {
            enabled: true,
            callbacks: {
              // Mostra a pontuação ao passar o mouse
              label: function(context) {
                return 'Rating: ' + context.parsed.y;
              }
            }
          }
        },
        scales: {
          x: {
            ticks: {
              color: '#64748b',
              font: {
                size: 12,
                weight: 'bold'
              },
              callback: customXTicks,
              maxRotation: 0,
              minRotation: 0,
              autoSkip: false,
            },
            grid: {
              color: 'rgba(100,116,139,0.07)',
              drawBorder: false,
            }
          },
          y: {
            beginAtZero: false,
            ticks: {
              color: '#64748b',
              font: {
                size: 12
              },
              padding: 6,
            },
            grid: {
              color: 'rgba(100,116,139,0.07)',
              drawBorder: false,
            }
          }
        },
        onClick: (e, elements) => {
          if (elements.length > 0) {
            const idx = elements[0].index;
            const label = labels[idx];
            const valor = dados[idx];
            alert('Data: ' + label + '\nRating: ' + valor);
          }
        }
      }
    });
  </script>
  <style>
    #graficoRating {
      max-height: 150px;
      min-height: 80px;
    }
  </style>

</body>

</html>