<?php
require_once '#_global.php';

// Helper function to format player names
function format_player_name($nome, $apelido) {
    $nome_html = htmlspecialchars($nome);
    if (!empty($apelido)) {
        $nome_html .= ' <span class="text-xs text-gray-500">(' . htmlspecialchars($apelido) . ')</span>';
    }
    return $nome_html;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php
require_once '_head.php';

// --- LÓGICA DA PÁGINA ---
$usuario_id = $_SESSION['DuplaUserId'];
$usuario = Usuario::posicao_usuario($usuario_id);
$user_rating = $usuario[0]['rating'] ?? 1500;

// --- LÓGICA PARA DUELOS MAIS JOGADOS ---
$parceiro_frequente = Usuario::getMostFrequentPartner($usuario_id);
$rival_frequente = Usuario::getMostFrequentRival($usuario_id);

$parceiro_vitorias = $parceiro_frequente['vitorias'] ?? 0;
$parceiro_partidas = $parceiro_frequente['partidas'] ?? 0;
$parceiro_percentual = ($parceiro_partidas > 0) ? round(($parceiro_vitorias / $parceiro_partidas) * 100) : 0;

$rival_vitorias = $rival_frequente['vitorias'] ?? 0;
$rival_partidas = $rival_frequente['partidas'] ?? 0;
$rival_percentual = ($rival_partidas > 0) ? round(($rival_vitorias / $rival_partidas) * 100) : 0;


// Busca a distribuição de ratings da comunidade
$rating_distribution = Usuario::getRatingDistribution(100); // Agrupa em faixas de 100 pontos

// --- LÓGICA PARA GRÁFICOS DE EVOLUÇÃO ---
$days_to_show = 10;
$community_history = Usuario::getCommunityAverageRatingHistory($days_to_show);
$user_history = Usuario::getUserRatingHistory($usuario_id, $days_to_show);

// Converte os resultados em mapas para fácil acesso por data
$community_map = array_column($community_history, 'rating_medio', 'dia');
$user_map = array_column($user_history, 'rating_novo', 'dia');

// Prepara dados para os gráficos
$combined_chart_labels = [];
$combined_community_data = [];
$combined_user_data = [];

// Pega o rating do usuário *antes* do período para preencher o início do gráfico combinado
$initial_rating_record = Usuario::getRatingBeforeDate($usuario_id, date('Y-m-d', strtotime("-$days_to_show days")));
$last_user_rating = $initial_rating_record ? (int)$initial_rating_record['rating_novo'] : (int)$user_rating;

for ($i = $days_to_show - 1; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $label = date('d/m', strtotime($date));
    
    $combined_chart_labels[] = $label;
    $combined_community_data[] = isset($community_map[$date]) ? round($community_map[$date]) : null;
    if (isset($user_map[$date])) {
        $last_user_rating = round($user_map[$date]);
    }
    $combined_user_data[] = $last_user_rating;
}

// Prepara os dados para o gráfico
$chart_labels = [];
$chart_data = [];
$chart_colors = [];

$user_rating_floor = floor($user_rating / 100) * 100;

foreach ($rating_distribution as $bin) {
    $floor = (int)$bin['rating_floor'];
    $ceiling = $floor + 99;
    $chart_labels[] = "{$floor} - {$ceiling}";
    $chart_data[] = (int)$bin['player_count'];

    // Destaca a faixa de rating do usuário
    if ($floor == $user_rating_floor) {
        $chart_colors[] = 'rgba(239, 68, 68, 0.8)'; // Cor de destaque (vermelho)
    } else {
        $chart_colors[] = 'rgba(59, 130, 246, 0.7)'; // Cor padrão (azul)
    }
}

// --- LÓGICA PARA RATING GAIN CHART ---
$top_gainers = Usuario::getTopRatingGainers(7, 5); // Top 5 ganhadores nos últimos 7 dias

$gainer_chart_labels = [];
$gainer_chart_data = [];
$gainer_chart_colors = [];

foreach ($top_gainers as $gainer) {
    // Para o gráfico, usamos apenas o apelido ou nome para não poluir o eixo X
    $chart_name = !empty($gainer['nome']) ? $gainer['nome'].' '.$gainer['sobrenome'] : explode(' ', $gainer['nome'])[0];
    if (strlen($chart_name) > 10) {
        $chart_name = substr($chart_name, 0, 10) . '...';
    }
    $gainer_chart_labels[] = $chart_name;
    $gainer_chart_data[] = round($gainer['rating_gain'],1);
    $gainer_chart_colors[] = $gainer['rating_gain'] > 0 ? 'rgba(34, 197, 94, 0.8)' : 'rgba(107, 114, 128, 0.7)'; // Tailwind green-500, gray-500
}


// --- LÓGICA PARA RATING GAIN CHART ---
$top_losers = Usuario::getTopRatinglosers(7, 5); // Top 5 ganhadores nos últimos 7 dias

$loser_chart_labels = [];
$loser_chart_data = [];
$loser_chart_colors = [];

foreach ($top_losers as $loser) {
    // Para o gráfico, usamos apenas o apelido ou nome para não poluir o eixo X
    $chart_name = !empty($loser['nome']) ? $loser['nome'].' '.$loser['sobrenome'] : explode(' ', $loser['nome'])[0];
    if (strlen($chart_name) > 10) {
        $chart_name = substr($chart_name, 0, 10) . '...';
    }
    $loser_chart_labels[] = $chart_name;
    $loser_chart_data[] = round($loser['rating_gain'],1);
    $loser_chart_colors[] = $loser['rating_gain'] > 0 ? 'rgb(220, 87, 87)' : 'rgba(218, 89, 89, 0.7)'; // Tailwind green-500, gray-500
}


// --- LÓGICA PARA MAIORES STREAKS ---
$top_streaks = Usuario::getTopWinningStreaks(5);

// --- LÓGICA PARA MAIORES SEQUÊNCIAS DE DERROTAS ---
$top_losers = Usuario::getTopLosingStreaks(5);
?>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php'; ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php'; ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-2 sm:p-4">
            <div class="max-w-4xl mx-auto w-full">
                <!-- Cabeçalho -->
                <div class="text-center mb-6">
                    <h1 class="text-3xl font-extrabold text-gray-800">Central DUPLA</h1>
                    <p class="text-sm text-gray-600">Suas estatísticas e o pulso da comunidade.</p>
                </div>

                <!-- Accordion para organizar os blocos -->
                <div class="space-y-3">

                    <!-- Bloco: Sua Jornada -->
                    <div class="collapse collapse-arrow bg-white rounded-2xl shadow-xl border border-gray-200">
                        <input type="checkbox" checked />
                        <div class="collapse-title text-lg font-bold flex items-center gap-2">⚔️ Sua Jornada</div>
                        <div class="collapse-content bg-gray-50/50">
                            <div class="p-2 space-y-4">
                                <!-- Percentil -->
                                <div>
                                    <h3 class="font-semibold text-gray-700 mb-1">Seu Posicionamento</h3>
                                    <?php if (($usuario[0]['percentual_abaixo'] ?? 0) >= 100): ?>
                                        <p class="text-md font-semibold text-amber-600 mb-2">Parabéns! Você é o líder do ranking! 👑</p>
                                        <progress class="progress progress-warning w-full" value="100" max="100"></progress>
                                        <p class="text-xs text-gray-500 mt-1">Continue jogando para defender sua posição!</p>
                                    <?php else: ?>
                                        <p class="text-md font-semibold text-gray-700 mb-2">Você está no top <span class="text-blue-600"><?= round(100 - ($usuario[0]['percentual_abaixo'] ?? 0), 1) ?>%</span> da comunidade!</p>
                                        <progress class="progress progress-primary w-full" value="<?= round(($usuario[0]['percentual_abaixo'] ?? 0)) ?>" max="100"></progress>
                                    <?php endif; ?>
                                </div>
                                <!-- Gráfico de Evolução -->
                                <div>
                                    <h3 class="font-semibold text-gray-700 mb-1">Sua Evolução vs. Comunidade</h3>
                                    <div class="h-56 relative"><canvas id="comparisonChart"></canvas></div>
                                    <div class="flex justify-center gap-4 mt-2 text-xs font-semibold">
                                        <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500"></span>Seu Rating</div>
                                        <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-purple-500"></span>Média</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bloco: Análise da Comunidade -->
                    <div class="collapse collapse-arrow bg-white rounded-2xl shadow-xl border border-gray-200">
                        <input type="checkbox" />
                        <div class="collapse-title text-lg font-bold flex items-center gap-2">🌡️ Análise da Comunidade</div>
                        <div class="collapse-content bg-gray-50/50">
                            <div class="p-2 space-y-4">
                                <!-- Termômetro -->
                                <div>
                                    <h3 class="font-semibold text-gray-700 mb-1">Distribuição de Rating</h3>
                                    <div class="h-64 relative"><canvas id="ratingHistogram"></canvas></div>
                                    <p class="text-xs text-gray-500 text-center mt-1">Sua faixa de rating está destacada em vermelho.</p>
                                </div>
                                <!-- Rating Gainers -->
                                <div>
                                    <h3 class="font-semibold text-gray-700 mb-1">Quem Mais Subiu (7 dias)</h3>
                                    <div class="h-64 relative">
                                        <?php if (!empty($gainer_chart_data)): ?><canvas id="ratingGainChart"></canvas>
                                        <?php else: ?><p class="text-center text-gray-500 italic pt-10">Sem dados de ganho de rating.</p><?php endif; ?>
                                    </div>
                                </div>
                                <!-- Rating Losers -->
                                <div>
                                    <h3 class="font-semibold text-gray-700 mb-1">Quem Mais Caiu (7 dias)</h3>
                                    <div class="h-64 relative">
                                        <?php if (!empty($loser_chart_data)): ?><canvas id="ratingLoserChart"></canvas>
                                        <?php else: ?><p class="text-center text-gray-500 italic pt-10">Sem dados de perda de rating.</p><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bloco: Leaderboards -->
                    <div class="collapse collapse-arrow bg-white rounded-2xl shadow-xl border border-gray-200">
                        <input type="checkbox" />
                        <div class="collapse-title text-lg font-bold flex items-center gap-2">🔥 Leaderboards</div>
                        <div class="collapse-content bg-gray-50/50">
                            <div class="p-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Streaks de Vitória -->
                                <div>
                                    <h3 class="font-semibold text-gray-700 mb-2 text-center">Sequências de Vitórias</h3>
                                    <div class="space-y-2">
                                        <?php if (!empty($top_streaks)): foreach ($top_streaks as $player): ?>
                                            <div class="flex items-center justify-between bg-orange-50 p-2 rounded-lg border-l-4 border-orange-400">
                                                <div class="font-semibold text-sm text-gray-800 truncate" title="<?= htmlspecialchars($player['nome']) ?>"><?= format_player_name($player['nome'], $player['apelido']) ?></div>
                                                <div class="flex items-center gap-1 font-bold text-orange-600 text-sm"><span><?= htmlspecialchars($player['win_streak']) ?></span><span class="text-md">🔥</span></div>
                                            </div>
                                        <?php endforeach; else: ?><p class="text-center text-xs text-gray-500 italic py-2">Ninguém em sequência de vitórias.</p><?php endif; ?>
                                    </div>
                                </div>
                                <!-- Streaks de Derrota -->
                                <div>
                                    <h3 class="font-semibold text-gray-700 mb-2 text-center">Sequência do Azarão</h3>
                                    <div class="space-y-2">
                                        <?php if (!empty($top_losers)): foreach ($top_losers as $player): ?>
                                            <div class="flex items-center justify-between bg-blue-50 p-2 rounded-lg border-l-4 border-blue-400">
                                                <div class="font-semibold text-sm text-gray-800 truncate" title="<?= htmlspecialchars($player['nome']) ?>"><?= format_player_name($player['nome'], $player['apelido']) ?></div>
                                                <div class="flex items-center gap-1 font-bold text-blue-600 text-sm"><span><?= htmlspecialchars($player['loss_streak']) ?></span><span class="text-md">🥲</span></div>
                                            </div>
                                        <?php endforeach; else: ?><p class="text-center text-xs text-gray-500 italic py-2">Ninguém em maré de azar.</p><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bloco: Duelos -->
                    <div class="collapse collapse-arrow bg-white rounded-2xl shadow-xl border border-gray-200">
                        <input type="checkbox" />
                        <div class="collapse-title text-lg font-bold flex items-center gap-2">🤝 Duelos Mais Jogados</div>
                        <div class="collapse-content bg-gray-50/50">
                            <div class="p-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-green-50 p-3 rounded-lg border-l-4 border-green-500 text-center">
                                    <h3 class="font-bold text-md text-green-800 mb-1">Parceria de Ouro 🥇</h3>
                                    <?php if ($parceiro_frequente): ?>
                                        <p class="text-lg font-bold text-gray-800 truncate" title="<?= htmlspecialchars($parceiro_frequente['parceiro_nome']) ?>"><?= format_player_name($parceiro_frequente['parceiro_nome'], $parceiro_frequente['apelido']) ?></p>
                                        <p class="text-xs text-gray-600">Jogaram juntos <strong class="text-gray-700"><?= $parceiro_partidas ?></strong> vezes com <strong class="text-green-600"><?= $parceiro_percentual ?>%</strong> de vitórias</p>
                                    <?php else: ?><p class="text-gray-500 text-xs italic py-2">Jogue mais para descobrir.</p><?php endif; ?>
                                </div>
                                <div class="bg-red-50 p-3 rounded-lg border-l-4 border-red-500 text-center">
                                    <h3 class="font-bold text-md text-red-800 mb-1">Rivalidade Clássica 🔥</h3>
                                    <?php if ($rival_frequente): ?>
                                        <p class="text-lg font-bold text-gray-800 truncate" title="<?= htmlspecialchars($rival_frequente['rival_nome']) ?>"><?= format_player_name($rival_frequente['rival_nome'], $rival_frequente['apelido']) ?></p>
                                        <p class="text-xs text-gray-600">Enfrentou <strong class="text-gray-700"><?= $rival_partidas ?></strong> vezes com <strong class="text-green-600"><?= $rival_percentual ?>%</strong> de vitórias contra</p>
                                    <?php else: ?><p class="text-gray-500 text-xs italic py-2">Enfrente mais adversários.</p><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <br><br><br>
        </main>
    </div>

    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('ratingHistogram').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chart_labels) ?>,
                    datasets: [{
                        label: 'Nº de Jogadores',
                        data: <?= json_encode($chart_data) ?>,
                        backgroundColor: <?= json_encode($chart_colors) ?>,
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 5,
                        hoverBackgroundColor: 'rgba(239, 68, 68, 1.0)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, title: { display: true, text: 'Nº de Jogadores', font: {size: 10} } }, x: { title: { display: true, text: 'Faixas de Rating', font: {size: 10} } } }
                }
            });

            const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
            new Chart(comparisonCtx, {
                type: 'line',
                data: {
                    labels: <?= json_encode($combined_chart_labels) ?>,
                    datasets: [{
                        label: 'Seu Rating',
                        data: <?= json_encode($combined_user_data) ?>,
                        borderColor: 'rgba(239, 68, 68, 1)', // Vermelho
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2.5,
                        pointRadius: 0,
                        pointHoverRadius: 5,
                    }, {
                        label: 'Média da Comunidade',
                        data: <?= json_encode($combined_community_data) ?>,
                        borderColor: 'rgba(168, 85, 247, 1)', // Roxo
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        fill: false,
                        tension: 0.4,
                        borderWidth: 2,
                        borderDash: [5, 5], // Linha tracejada
                        pointRadius: 0,
                        pointHoverRadius: 5,
                        spanGaps: true, // Conecta pontos com dados nulos
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { mode: 'index', intersect: false },
                    },
                    scales: { y: { beginAtZero: false, title: { display: true, text: 'Rating', font: {size: 10} } }, x: { title: { display: false } } }
                }
            });

            <?php if (!empty($gainer_chart_data)): ?>
            const gainCtx = document.getElementById('ratingGainChart').getContext('2d');
            new Chart(gainCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($gainer_chart_labels) ?>,
                    datasets: [{
                        label: 'Ganho de Rating',
                        data: <?= json_encode($gainer_chart_data) ?>,
                        backgroundColor: <?= json_encode($gainer_chart_colors) ?>,
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1,
                        borderRadius: 5,
                        hoverBackgroundColor: 'rgba(34, 197, 94, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Ganho: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: { y: { beginAtZero: true, title: { display: true, text: 'Ganho de Rating', font: {size: 10} } }, x: { ticks: { font: {size: 10} } } }
                }
            });
            <?php endif; ?>



            <?php if (!empty($loser_chart_data)): ?>
            const loserCtx = document.getElementById('ratingLoserChart').getContext('2d');
            new Chart(loserCtx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($loser_chart_labels) ?>,
                    datasets: [{
                        label: 'Perda de Rating',
                        data: <?= json_encode($loser_chart_data) ?>,
                        backgroundColor: <?= json_encode($loser_chart_colors) ?>,
                        borderColor: 'rgb(217, 66, 66)',
                        borderWidth: 1,
                        borderRadius: 5,
                        hoverBackgroundColor: 'rgb(219, 85, 85)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Perda: ' + context.parsed.y;
                                }
                            }
                        }
                    },
                    scales: { y: { beginAtZero: true, title: { display: true, text: 'Perda de Rating', font: {size: 10} } }, x: { ticks: { font: {size: 10} } } }
                }
            });
            <?php endif; ?>
  
        });
    </script>

</body>
</html>