<?php 
require_once '#_global.php'; 
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php';

// --- VERIFICAÇÃO DE PERFIL COMPLETO ---
// Garante que o usuário tenha preenchido os dados essenciais antes de usar o sistema.
$usuario_id_check = $_SESSION['DuplaUserId'];
$usuario_info_completo = Usuario::getUsuarioInfoById($usuario_id_check);

if (
    !$usuario_info_completo || // Garante que o usuário foi encontrado
    empty($usuario_info_completo['telefone']) ||
    empty($usuario_info_completo['cidade']) ||
    empty($usuario_info_completo['sexo']) ||
    empty($usuario_info_completo['empunhadura'])
) {
    // A mensagem de erro em perfil.php usa 'alert-error'
    $_SESSION['mensagem'] = ['error', 'Complete seu cadastro para seguir usando o Dupla.'];
    header("Location: perfil.php");
    exit;
}

$usuario_id = $_SESSION['DuplaUserId'];
$usuario = Usuario::posicao_usuario($usuario_id);

$partidas_usuario = Usuario::partidas_usuario($usuario_id);
$variacao_rating = Usuario::variacao_rating($usuario_id, 10);
$qtd_partida_pendente = Partida::qtd_partida_pendente($usuario_id);
$has_pending = ($qtd_partida_pendente[0]['quantidade'] ?? 0) > 0;

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
$dados = []; // Irá guardar os valores de rating

// --- Novas variáveis para os cards de informação ---
// Para Partidas | Vitórias
$total_partidas = $partidas_usuario[0]['total_partidas'] ?? 0;
$total_vitorias = $partidas_usuario[0]['vitorias'] ?? 0;
$percent_vitorias = ($total_partidas > 0) ? round(($total_vitorias / $total_partidas) * 100) : 0;

// Para Conquistas (assumindo 0 se não houver dados ou função implementada)
// Se você tiver uma função para buscar o total de conquistas, substitua '0' por ela. Ex: Usuario::total_conquistas($usuario_id)
$total_conquistas = 0; // Placeholder: substitua por uma função real que retorne o número de conquistas

// Para Variação (10d)
$variacao_valor = $variacao_rating[0]['variacao_rating'] ?? 0;
$variacao_formatada = round($variacao_valor, 1); // Arredonda para 1 casa decimal
$variacao_icon = '';
$variacao_color_class = 'text-gray-600'; // Cor padrão

$diffs = []; // Irá guardar a diferença do rating anterior
$previous_rating = null;

foreach ($hist_rating as $registro) {
  $labels[] = date('d M', strtotime($registro['data']));
  $current_rating = (int)$registro['rating_novo'];
  $dados[] = $current_rating;

  if ($previous_rating !== null) {
      $diffs[] = $current_rating - $previous_rating;
  } else {
      $diffs[] = 0; // Nenhuma diferença para o primeiro ponto
  }
  $previous_rating = $current_rating;
}

// Define o ícone e a cor da variação de rating
if ($variacao_valor > 0) {
    $variacao_icon = '⬆️';
    $variacao_color_class = 'text-green-600';
} elseif ($variacao_valor < 0) {
    $variacao_icon = '⬇️';
    $variacao_color_class = 'text-red-600';
}
?>

<body class="bg-gray-100 min-h-screen text-gray-800 text-sm sm:text-base" style="color-scheme: light;">

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
        <div class="mb-3 p-1">
          <div class="flex items-center gap-3 bg-gradient-to-r from-blue-600 via-purple-500 to-pink-500 text-white rounded-xl px-4 py-3 shadow-lg">
            <span class="text-2xl animate-bounce">👋</span>
            <span class="font-medium text-sm"><?= $mensagem_aleatoria ?></span>
          </div>
        </div>

        <!-- Botões de Ação -->
        <div class="grid grid-cols-5 gap-2 mb-3">
          <!-- Botão Registrar Partida (ocupa 3 colunas) -->
          <a href="nova-partida.php"
            class="col-span-3 px-4 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-extrabold rounded-xl shadow-md transition flex items-center gap-2 justify-center text-base transform hover:scale-105">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            <span>Registrar Partida</span>
          </a>
          
          <!-- Botão Validar Partidas (ocupa 2 colunas) -->
          <?php if ($has_pending): ?>
            <a href="validar-partidas.php"
              class="col-span-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-2 justify-center relative transform hover:scale-105">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              <span>Validar</span>
              <span class="absolute -top-2 -right-2 flex h-5 w-5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-5 w-5 bg-red-500 text-white text-xs items-center justify-center">
                  <?= $qtd_partida_pendente[0]['quantidade'] ?>
                </span>
              </span>
            </a>
          <?php else: ?>
            <a href="validar-partidas.php"
              class="col-span-2 px-4 py-3 bg-white hover:bg-gray-50 text-gray-600 font-bold rounded-xl shadow-md transition flex items-center gap-2 justify-center border border-gray-200">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              <span>Validar</span>
            </a>
          <?php endif; ?>
        </div>

        <!-- Cards de informações -->
        <div class="grid grid-cols-2 gap-3 mb-3">
          <!-- Card Rating -->
          <div class="bg-white rounded-xl shadow p-3 text-center flex flex-col items-center justify-center">
            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-100 text-blue-600 text-lg mb-1">⭐</div>
            <div class="text-xl font-bold text-blue-600"><?= $usuario[0]['rating'] ?></div>
            <div class="text-xs text-gray-500 mt-1">Rating Atual</div>
          </div>

          <!-- Card Partidas | Vitórias -->
          <div class="bg-white rounded-xl shadow p-3 text-center flex flex-col items-center justify-center">
            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 text-green-600 text-lg mb-1">🎾</div>
            <div class="text-xl font-bold text-green-600"><?= $total_partidas ?></div>
            <div class="text-xs text-gray-500 mt-1">Partidas Jogadas</div>
            <div class="text-sm text-gray-700 mt-1">Vitórias: <span class="font-semibold"><?= $total_vitorias ?> (<?= $percent_vitorias ?>%)</span></div>
          </div>

          <!-- Card Conquistas -->
          <!-- <div class="bg-white rounded-xl shadow p-3 text-center flex flex-col items-center justify-center">
            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-yellow-100 text-yellow-600 text-lg mb-1">🏆</div>
            <div class="text-xl font-bold text-yellow-600"><?= $total_conquistas ?></div>
            <div class="text-xs text-gray-500 mt-1">Conquistas Desbloqueadas</div>
          </div> -->

          <!-- Card Variação (10d) -->
          <!-- <div class="bg-white rounded-xl shadow p-3 text-center flex flex-col items-center justify-center">
            <div class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-100 text-purple-600 text-lg mb-1">📈</div>
            <div class="text-xl font-bold <?= $variacao_color_class ?>"><?= $variacao_formatada ?> <?= $variacao_icon ?></div>
            <div class="text-xs text-gray-500 mt-1">Variação de Rating (10d)</div>
          </div> -->
        </div>

        <!-- Gráfico e ranking -->
        <div class="grid grid-cols-1 gap-3 mb-3"> 
            <?php if (empty($hist_rating)): ?>
            <div class="bg-white rounded-xl shadow p-4 text-center">
                <h3 class="text-base font-semibold mb-3 flex items-center justify-center gap-2 text-gray-700">
                    <span class="text-xl">📊</span>
                    Histórico de Rating
                </h3>
                <p class="text-gray-600 italic">Você ainda não tem histórico de rating. Jogue sua primeira partida para começar a acompanhar sua evolução!</p>
            </div>
            <?php else: ?>
            <div class="bg-white rounded-xl shadow p-4">
              <h3 class="text-base font-semibold mb-3 flex items-center gap-2 text-gray-700">
                <span class="text-xl">📊</span>
                Histórico de Rating
              </h3>
              <canvas id="graficoRating" height="100"></canvas>
            </div>
            <?php endif; ?>
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
                  <div class="flex-1 font-semibold text-gray-800">
                    <span class="block"><?= htmlspecialchars($rank['nome']) ?></span>
                    <?php if (!empty($rank['apelido'])): ?>
                      <span class="text-xs text-gray-500 font-normal -mt-1 block">(<?= htmlspecialchars($rank['apelido']) ?>)</span>
                    <?php endif; ?>
                  </div>
                  <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-2 py-1 rounded-full">⭐ <?= htmlspecialchars($rank['rating']) ?></span>
                </li>
                <?php $p_sup = $p_sup + 1; endforeach; ?>

              <!-- Usuário em destaque -->
              <li class="flex items-center gap-3 rounded-lg px-4 py-2 shadow-lg border-l-4 border-blue-600 bg-blue-50 relative">
                <span class="text-lg font-bold text-blue-700 w-8 text-center"><?= $usuario[0]['posicao'] ?>º</span>
                <div class="flex-1 font-bold text-blue-800">
                  <span class="block"><?= htmlspecialchars($usuario[0]['nome']) ?></span>
                  <?php if (!empty($usuario[0]['apelido'])): ?>
                    <span class="text-xs text-blue-500 font-normal -mt-1 block">(<?= htmlspecialchars($usuario[0]['apelido']) ?>)</span>
                  <?php endif; ?>
                </div>
                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full">⭐ <?= htmlspecialchars($usuario[0]['rating']) ?></span>
              </li>

              <!-- Jogadores abaixo da posição do usuário -->
              <?php foreach ($ranking_inferior as $rank): ?>
                <li class="flex items-center gap-3 rounded-lg px-4 py-2 shadow-md border-l-4 border-gray-400 bg-white hover:bg-gray-50 transition-colors">
                  <span class="text-lg font-bold text-gray-600 w-8 text-center"><?= $p_inf ?>º</span>
                  <div class="flex-1 font-semibold text-gray-800">
                    <span class="block"><?= htmlspecialchars($rank['nome']) ?></span>
                    <?php if (!empty($rank['apelido'])): ?>
                      <span class="text-xs text-gray-500 font-normal -mt-1 block">(<?= htmlspecialchars($rank['apelido']) ?>)</span>
                    <?php endif; ?>
                  </div>
                  <span class="bg-gray-100 text-gray-700 text-xs font-bold px-2 py-1 rounded-full">⭐ <?= htmlspecialchars($rank['rating']) ?></span>
                </li>
                <?php $p_inf = $p_inf + 1; endforeach; ?>
            </ul>
            <div class="mt-4 text-center">
                <a href="ranking-geral.php" class="inline-block bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold px-3 py-1.5 rounded-full shadow-sm transition-colors text-xs">
                Ver ranking completo &rarr;
              </a>
            </div>
          </div>
        </div>

        <?php
        // Preparar dados para o Quadro de Honra, com fallback para estado vazio
        if (empty($parceiro_vitoria)) {
            $pato_nome = 'Ainda nenhum';
            $pato_partidas = 0;
            $pato_vitorias = 0;

            $carrasco_nome = 'Ninguém te para';
            $carrasco_partidas = 0;
            $carrasco_derrotas = 0;

            $dupla_forte_nome = 'Jogue mais';
            $dupla_forte_partidas = 0;
            $dupla_forte_vitorias = 0;

            $azarado_nome = 'Sempre na sorte';
            $azarado_partidas = 0;
            $azarado_derrotas = 0;
        } else {
            // Meu Pato (mais vitórias contra)
            $pato_nome = !empty($adversario_vitoria[0]) ? $adversario_vitoria[0]['adversario_nome'] : 'Ainda nenhum';
            $pato_partidas = !empty($adversario_vitoria[0]) ? $adversario_vitoria[0]['partidas'] : 0;
            $pato_vitorias = !empty($adversario_vitoria[0]) ? $adversario_vitoria[0]['vitorias'] : 0;

            // Meu Carrasco (mais derrotas para)
            $carrasco_nome = !empty($adversario_derrota[0]) ? $adversario_derrota[0]['adversario_nome'] : 'Ninguém te para';
            $carrasco_partidas = !empty($adversario_derrota[0]) ? $adversario_derrota[0]['partidas'] : 0;
            $carrasco_derrotas = !empty($adversario_derrota[0]) ? $adversario_derrota[0]['derrotas'] : 0;

            // Dupla Forte (mais vitórias com)
            $dupla_forte_nome = !empty($parceiro_vitoria[0]) ? $parceiro_vitoria[0]['parceiro_nome'] : 'Jogue mais';
            $dupla_forte_partidas = !empty($parceiro_vitoria[0]) ? $parceiro_vitoria[0]['partidas'] : 0;
            $dupla_forte_vitorias = !empty($parceiro_vitoria[0]) ? $parceiro_vitoria[0]['vitorias'] : 0;

            // Só Atrapalha (mais derrotas com)
            $azarado_nome = !empty($parceiro_derrota[0]) ? $parceiro_derrota[0]['parceiro_nome'] : 'Sempre na sorte';
            $azarado_partidas = !empty($parceiro_derrota[0]) ? $parceiro_derrota[0]['partidas'] : 0;
            $azarado_derrotas = !empty($parceiro_derrota[0]) ? $parceiro_derrota[0]['derrotas'] : 0;
        }
        ?>

        <!-- Quadro de Honra Redesenhado -->
        <div class="bg-white rounded-xl shadow p-4 mb-6">
            <h3 class="text-base font-semibold mb-3 flex items-center gap-2 text-gray-700">
                <span class="text-xl">🏅</span>
                Quadro de Honra <span class="text-gray-400 font-normal">(ou nem tanto)</span>
            </h3>
            <div class="grid grid-cols-2 gap-3">
                <!-- Dupla Forte -->
                <div class="bg-green-50 rounded-lg p-3 border-l-4 border-green-500 shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="font-bold text-xs text-green-800 uppercase tracking-wider">Dupla Forte</h4>
                        <span class="text-2xl">🤝</span>
                    </div>
                    <div class="text-sm font-semibold text-gray-800 truncate" title="<?= htmlspecialchars($dupla_forte_nome) ?>">
                        <?= htmlspecialchars($dupla_forte_nome) ?>
                        <?php if (!empty($parceiro_vitoria[0]['apelido'])): ?>
                            <span class="text-xs font-normal text-gray-500">(<?= htmlspecialchars($parceiro_vitoria[0]['apelido']) ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs text-gray-600 mt-1">
                        <span class="font-semibold"><?= htmlspecialchars($dupla_forte_vitorias) ?> vitórias</span> em <?= htmlspecialchars($dupla_forte_partidas) ?> jogos
                    </div>
                </div>
                <!-- Meu Pato -->
                <div class="bg-yellow-50 rounded-lg p-3 border-l-4 border-yellow-500 shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="font-bold text-xs text-yellow-800 uppercase tracking-wider">Meu Pato</h4>
                        <span class="text-2xl">🦆</span>
                    </div>
                    <div class="text-sm font-semibold text-gray-800 truncate" title="<?= htmlspecialchars($pato_nome) ?>">
                        <?= htmlspecialchars($pato_nome) ?>
                        <?php if (!empty($adversario_vitoria[0]['apelido'])): ?>
                            <span class="text-xs font-normal text-gray-500">(<?= htmlspecialchars($adversario_vitoria[0]['apelido']) ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs text-gray-600 mt-1">
                        <span class="font-semibold"><?= htmlspecialchars($pato_vitorias) ?> vitórias</span> em <?= htmlspecialchars($pato_partidas) ?> jogos
                    </div>
                </div>
                <!-- Meu Carrasco -->
                <div class="bg-red-50 rounded-lg p-3 border-l-4 border-red-500 shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="font-bold text-xs text-red-800 uppercase tracking-wider">Meu Carrasco</h4>
                        <span class="text-2xl">💀</span>
                    </div>
                    <div class="text-sm font-semibold text-gray-800 truncate" title="<?= htmlspecialchars($carrasco_nome) ?>">
                        <?= htmlspecialchars($carrasco_nome) ?>
                        <?php if (!empty($adversario_derrota[0]['apelido'])): ?>
                            <span class="text-xs font-normal text-gray-500">(<?= htmlspecialchars($adversario_derrota[0]['apelido']) ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs text-gray-600 mt-1">
                        <span class="font-semibold"><?= htmlspecialchars($carrasco_derrotas) ?> derrotas</span> em <?= htmlspecialchars($carrasco_partidas) ?> jogos
                    </div>
                </div>
                <!-- Só Atrapalha -->
                <div class="bg-gray-100 rounded-lg p-3 border-l-4 border-gray-400 shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <h4 class="font-bold text-xs text-gray-600 uppercase tracking-wider">Só Atrapalha</h4>
                        <span class="text-2xl">🐢</span>
                    </div>
                    <div class="text-sm font-semibold text-gray-800 truncate" title="<?= htmlspecialchars($azarado_nome) ?>">
                        <?= htmlspecialchars($azarado_nome) ?>
                        <?php if (!empty($parceiro_derrota[0]['apelido'])): ?>
                            <span class="text-xs font-normal text-gray-500">(<?= htmlspecialchars($parceiro_derrota[0]['apelido']) ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-xs text-gray-600 mt-1">
                        <span class="font-semibold"><?= htmlspecialchars($azarado_derrotas) ?> derrotas</span> em <?= htmlspecialchars($azarado_partidas) ?> jogos
                    </div>
                </div>
            </div>
            <div class="mt-4 text-center">
                <a href="#" class="inline-block bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold px-3 py-1.5 rounded-full shadow-sm transition-colors text-xs">
                    Ver todas as conquistas &rarr;
                </a>
            </div>
        </div>
        <br><br>
      </section>
    </main>
  </div>

    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>

  <!-- Barra de navegação inferior flutuante -->
  <div class="fixed bottom-4 left-1/2 -translate-x-1/2 z-40"> <!-- Alterado z-index para z-40 -->
      <div class="flex items-center gap-2 bg-white/80 backdrop-blur-md border border-gray-200/80 rounded-full shadow-xl px-3 py-2">
          <!-- Botão Mapa -->
          <a href="mapa-arenas.php" class="flex flex-col items-center justify-center text-gray-700 hover:text-green-600 transition-colors w-16 h-14 rounded-full hover:bg-green-50">
              <span class="text-2xl">🗺️</span>
              <span class="text-xs font-semibold">Mapa</span>
          </a>
          <!-- Botão Torneios (antigo Inscrições) -->
          <a href="meus-torneios.php" class="flex flex-col items-center justify-center text-gray-700 hover:text-purple-600 transition-colors w-16 h-14 rounded-full hover:bg-purple-50">
              <span class="text-2xl">🏆</span>
              <span class="text-xs font-semibold">Torneios</span>
          </a>
          <!-- Botão Análise IA -->
          <button id="btnGpt" class="flex flex-col items-center justify-center text-gray-700 hover:text-indigo-600 transition-colors w-16 h-14 rounded-full hover:bg-indigo-50">
              <span class="text-2xl">🤖</span>
              <span class="text-xs font-semibold">Análise IA</span>
          </button>
      </div>
  </div>

<!-- Modal de Análise IA -->
<div id="modalGpt" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-51 hidden"> <!-- Alterado z-index para z-51 -->
  <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md flex flex-col items-center gap-4 relative">
    <button id="fecharModalGpt" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
    <div class="text-2xl mb-2">🤖 <span class="font-extrabold text-indigo-700">Análise de Desempenho</span></div>
    <div id="gptContent" class="w-full text-left space-y-4 max-h-[60vh] overflow-y-auto p-2">
      <!-- Loading state -->
      <div id="gptLoading" class="text-center">
        <div class="animate-spin rounded-full border-4 border-indigo-400 border-t-transparent h-12 w-12 mx-auto mb-4"></div>
        <p class="font-semibold text-indigo-700">Analisando seus dados...</p>
        <p class="text-xs text-gray-500">Aguarde, nossa IA está preparando dicas personalizadas para você!</p>
      </div>
      <!-- Content will be injected here -->
    </div>
  </div>
</div>

<script>
    // Funções genéricas para modais
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('hidden');
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('hidden');
    }

    // --- LÓGICA PARA ANÁLISE DE IA ---
    const btnGpt = document.getElementById('btnGpt');
    const modalGpt = document.getElementById('modalGpt');
    const fecharModalGpt = document.getElementById('fecharModalGpt');
    const gptContent = document.getElementById('gptContent');
    const gptLoading = document.getElementById('gptLoading');

    if (btnGpt) {
        btnGpt.addEventListener('click', async () => {
            openModal('modalGpt');
            gptContent.innerHTML = ''; // Limpa conteúdo anterior
            gptLoading.style.display = 'block'; // Mostra o loading
            gptContent.appendChild(gptLoading);

            try {
                const response = await fetch(`controller-ia/analise-jogador.php?v=${new Date().getTime()}`);
                const data = await response.json();

                gptLoading.style.display = 'none'; // Esconde o loading

                if (data.success) {
                    gptContent.innerHTML = data.analysis;
                } else {
                    gptContent.innerHTML = `<p class="text-red-500 text-center font-semibold">${data.message || 'Ocorreu um erro.'}</p>`;
                }
            } catch (error) {
                console.error('Erro ao buscar análise da IA:', error);
                gptLoading.style.display = 'none';
                gptContent.innerHTML = `<p class="text-red-500 text-center font-semibold">Não foi possível conectar ao servidor. Verifique sua conexão.</p>`;
            }
        });
    }

    // Fechar modal da IA
    if (fecharModalGpt) fecharModalGpt.addEventListener('click', () => closeModal('modalGpt'));
    if (modalGpt) modalGpt.addEventListener('click', (e) => {
        if (e.target.id === 'modalGpt') closeModal('modalGpt');
    });
</script>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

  <script>
    const labels = <?= json_encode($labels) ?>;
    const dados = <?= json_encode($dados) ?>;
    const diffs = <?= json_encode($diffs) ?>;

    if (dados.length > 1) {
      const maxRating = Math.max(...dados);
      const minRating = Math.min(...dados);
      const maxIndex = dados.indexOf(maxRating);
      const minIndex = dados.indexOf(minRating);

      const ctx = document.getElementById('graficoRating').getContext('2d');

      // Criando o gradiente para o fundo
      const gradient = ctx.createLinearGradient(0, 0, 0, 120);
      gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
      gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

      const graficoRating = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Rating',
            data: dados,
            borderColor: 'rgba(59,130,246,1)',
            backgroundColor: gradient,
            tension: 0.4,
            fill: true,
            borderWidth: 2.5,
            pointRadius: function(context) {
              const idx = context.dataIndex;
              return idx === 0 || idx === dados.length - 1 || idx === minIndex || idx === maxIndex ? 4 : 0;
            },
            pointHoverRadius: 6,
            pointBackgroundColor: 'rgba(59,130,246,1)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgba(59,130,246,1)',
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
              display: function(context) {
                const idx = context.dataIndex;
                return idx === 0 || idx === dados.length - 1 || idx === minIndex || idx === maxIndex;
              },
              formatter: (value) => value,
              align: 'top',
              offset: 8,
              color: '#374151',
              font: {
                weight: 'bold',
                size: 11
              }
            },
            tooltip: {
              enabled: true,
              backgroundColor: '#1f2937',
              titleColor: '#f9fafb',
              bodyColor: '#f9fafb',
              borderColor: '#374151',
              borderWidth: 1,
              padding: 10,
              displayColors: false,
              callbacks: {
                title: (tooltipItems) => 'Data: ' + tooltipItems[0].label,
                label: function(context) {
                  const rating = context.parsed.y;
                  const diff = diffs[context.dataIndex];
                  let diffText = '';
                  if (diff > 0) {
                    diffText = ` (📈 +${diff})`;
                  } else if (diff < 0) {
                    diffText = ` (📉 ${diff})`;
                  }
                  return 'Rating: ' + rating + diffText;
                }
              }
            }
          },
          scales: {
            x: {
              ticks: {
                color: '#64748b',
                font: { size: 11, weight: 'bold' },
                maxRotation: 0,
                autoSkip: true,
                maxTicksLimit: 5 // Limita o número de labels para não poluir
              },
              grid: { display: false }
            },
            y: {
              beginAtZero: false,
              ticks: {
                color: '#64748b',
                font: { size: 11 },
                padding: 8,
              },
              grid: {
                color: 'rgba(100,116,139,0.1)',
                drawBorder: false,
              }
            }
          }
        }
      });
    }
  </script>
  <style>
    #graficoRating {
      max-height: 180px;
      min-height: 120px;
    }
  </style>

</body>

</html>