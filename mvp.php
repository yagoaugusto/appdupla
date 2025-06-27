<?php
require_once '#_global.php';

// --- LÓGICA DA PÁGINA ---

// Filtros
// Define o período padrão como o mês atual
$data_inicio_padrao = new DateTime('first day of this month');
$data_fim_padrao = new DateTime('last day of this month');

$data_inicio = $_GET['data_inicio'] ?? $data_inicio_padrao->format('Y-m-d');
$data_fim = $_GET['data_fim'] ?? $data_fim_padrao->format('Y-m-d');
$arena_id = filter_input(INPUT_GET, 'arena_id', FILTER_VALIDATE_INT);
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;
$limit = 50;

// Busca de dados
$arenas = Arena::getArenas(); // Busca todas as arenas para o filtro
$ranking_data = Ranking::getMvpRanking($data_inicio, $data_fim, $arena_id, $page, $limit);
$mvp_list = $ranking_data['data'];
$total_players = $ranking_data['total'];
$total_pages = ceil($total_players / $limit);

$usuario_logado_id = $_SESSION['DuplaUserId'] ?? null;
$posicao_inicial = ($page - 1) * $limit + 1;

$icones_podio = ['🥇', '🥈', '🥉'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>

<body class="bg-gray-100 min-h-screen text-gray-800">
    <!-- Navs -->
    <?php require_once '_nav_superior.php'; ?>
    <div class="flex pt-16">
        <?php require_once '_nav_lateral.php'; ?>
        <!-- Main content -->
        <main class="flex-1 p-4 sm:p-6">
            <section class="max-w-7xl mx-auto w-full">
                <!-- Header -->
                <div class="flex items-center gap-4 mb-6">
                    <span class="text-4xl">🔥</span>
                    <div class="flex-1">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">MVP - Most Valuable Player</h1>
                        <p class="text-sm text-gray-500">Ranking de jogadores pela média de rating ganho por partida.</p>
                    </div>
                </div>

                <!-- Filters -->
                <form method="GET" action="mvp.php" class="bg-white p-4 rounded-xl shadow-md border border-gray-200 mb-6 flex flex-col md:flex-row gap-4 md:items-end justify-between">
                    <!-- Date Range Filter - Adjusted for better mobile wrapping -->
                    <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                        <div class="form-control">
                            <label class="label-text text-xs pb-1 font-semibold">De:</label>
                            <input type="date" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" class="input input-bordered input-sm w-full md:w-auto">
                        </div>
                        <div class="form-control">
                            <label class="label-text text-xs pb-1 font-semibold">Até:</label>
                            <input type="date" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" class="input input-bordered input-sm w-full md:w-auto">
                        </div>
                    </div>

                    <!-- Arena Filter and Submit Button -->
                    <div class="flex flex-col sm:flex-row sm:items-end gap-2 w-full md:w-auto">
                        <div class="form-control w-full flex-grow">
                            <label class="label-text text-xs pb-1 font-semibold">Arena:</label>
                            <select name="arena_id" class="select select-bordered select-sm">
                                <option value="">Todas as Arenas</option>
                                <?php foreach ($arenas as $arena) : ?>
                                    <option value="<?= $arena['id'] ?>" <?= $arena_id == $arena['id'] ? 'selected' : '' ?>><?= htmlspecialchars($arena['titulo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                    </div>
                </form>
                <!-- Ranking Table -->
                <div class="overflow-x-auto bg-white rounded-xl shadow-lg border border-gray-200 hidden md:block">
                    <table class="table w-full table-zebra">
                        <thead>
                            <tr class="text-sm">
                                <th class="w-12 text-center min-w-[50px]">#</th>
                                <th class="min-w-[150px]">Jogador</th>
                                <th class="text-center min-w-[80px]">Partidas</th>
                                <th class="text-center min-w-[100px]">Rating Ganho</th>
                                <th class="text-center min-w-[120px]">Média / Partida</th>
                                <th class="text-center min-w-[80px]">Rating Atual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mvp_list)) : ?>
                                <tr>
                                    <td colspan="6" class="text-center italic py-6">Nenhum jogador encontrado para os filtros selecionados.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($mvp_list as $index => $jogador) :
                                    $posicao_atual = $posicao_inicial + $index;
                                    $is_logged_in_user = ($jogador['usuario_id'] == $usuario_logado_id);
                                    $row_class = $is_logged_in_user ? 'bg-blue-50 font-bold' : '';

                                    // Lógica para Rating Ganho
                                    $total_rating_ganho_val = (float)$jogador['total_rating_ganho'];
                                    $total_rating_ganho_class = $total_rating_ganho_val < 0 ? 'text-red-600' : 'text-green-600';
                                    $total_rating_ganho_display = number_format($total_rating_ganho_val, 1, ',', '.');
                                    if ($total_rating_ganho_val > 0) {
                                        $total_rating_ganho_display = '+' . $total_rating_ganho_display;
                                    }

                                    // Lógica para Média / Partida
                                    $media_rating_val = (float)$jogador['media_rating_por_partida'];
                                    $media_rating_class = $media_rating_val < 0 ? 'text-red-600' : 'text-blue-600';
                                    $media_rating_display = number_format($media_rating_val, 2, ',', '.');
                                    if ($media_rating_val > 0) {
                                        $media_rating_display = '+' . $media_rating_display;
                                    }
                                ?>
                                    <tr class="<?= $row_class ?>">
                                        <th class="text-center">
                                            <?php if ($page == 1 && $posicao_atual <= 3) : ?>
                                                <span class="text-xl sm:text-2xl"><?= $icones_podio[$posicao_atual - 1] ?></span>
                                            <?php else : ?>
                                                <?= $posicao_atual ?>
                                            <?php endif; ?>
                                        </th> <!-- Posição -->
                                        <td>
                                            <div class="font-semibold text-gray-800"><?= htmlspecialchars($jogador['nome']) ?></div>
                                            <?php if (!empty($jogador['apelido'])) : ?>
                                                <div class="text-xs opacity-70">(<?= htmlspecialchars($jogador['apelido']) ?>)</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= $jogador['total_partidas'] ?></td>
                                        <td class="text-center font-semibold <?= $total_rating_ganho_class ?>">
                                            <?= $total_rating_ganho_display ?>
                                        </td>
                                        <td class="text-center font-bold <?= $media_rating_class ?>">
                                            <?= $media_rating_display ?>
                                        </td>
                                        <td class="text-center font-mono"><?= round($jogador['rating']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Ranking Cards for Mobile - Refactored with Collapse -->
                <div class="md:hidden space-y-4">
                    <?php if (empty($mvp_list)) : ?>
                        <div class="text-center italic py-6 bg-white rounded-xl shadow-lg border border-gray-200">Nenhum jogador encontrado para os filtros selecionados.</div>
                    <?php else : ?>
                        <?php foreach ($mvp_list as $index => $jogador) :
                            $posicao_atual = $posicao_inicial + $index;
                            $is_logged_in_user = ($jogador['usuario_id'] == $usuario_logado_id);
                            $card_class = $is_logged_in_user ? 'bg-blue-50 border-blue-400' : 'bg-white border-gray-200';

                            // Lógica para Rating Ganho
                            $total_rating_ganho_val = (float)$jogador['total_rating_ganho'];
                            $total_rating_ganho_class = $total_rating_ganho_val < 0 ? 'text-red-600' : 'text-green-600';
                            $total_rating_ganho_display = number_format($total_rating_ganho_val, 1, ',', '.');
                            if ($total_rating_ganho_val > 0) {
                                $total_rating_ganho_display = '+' . $total_rating_ganho_display;
                            }

                            // Lógica para Média / Partida
                            $media_rating_val = (float)$jogador['media_rating_por_partida'];
                            $media_rating_class = $media_rating_val < 0 ? 'text-red-600' : 'text-blue-600';
                            $media_rating_display = number_format($media_rating_val, 2, ',', '.');
                            if ($media_rating_val > 0) {
                                $media_rating_display = '+' . $media_rating_display;
                            }
                        ?>
                            <div class="collapse collapse-arrow rounded-xl shadow-lg border-l-4 <?= $card_class ?>">
                                <input type="checkbox" />
                                <div class="collapse-title text-base font-semibold flex items-center gap-2 p-3">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0
                                        <?php if ($page == 1 && $posicao_atual <= 3) : ?>
                                            bg-yellow-500 text-xl
                                        <?php else : ?>
                                            bg-gray-500
                                        <?php endif; ?>">
                                        <?php if ($page == 1 && $posicao_atual <= 3) : ?>
                                            <?= $icones_podio[$posicao_atual - 1] ?>
                                        <?php else : ?>
                                            <?= $posicao_atual ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 overflow-hidden">
                                        <div class="font-bold text-gray-800 truncate"><?= htmlspecialchars($jogador['nome']) ?><?php if (!empty($jogador['apelido'])) : ?> <span class="text-xs font-normal text-gray-500">(<?= htmlspecialchars($jogador['apelido']) ?>)</span><?php endif; ?></div>
                                        <div class="text-sm text-gray-600">Média: <span class="font-bold <?= $media_rating_class ?>"><?= $media_rating_display ?></span></div>
                                    </div>

                                </div>
                                <div class="collapse-content bg-white p-3 pt-0 border-t border-gray-100">
                                    <div class="grid grid-cols-3 gap-y-1 gap-x-4 text-sm">
                                        <div><span class="text-gray-600">Partidas:</span> <span class="font-semibold"><?= $jogador['total_partidas'] ?></span></div>
                                        <div><span class="text-gray-600">Rating:</span> <span class="font-semibold"><?= $jogador['rating'] ?></span></div>
                                        <div><span class="text-gray-600">Rating Ganho:</span> <span class="font-semibold <?= $total_rating_ganho_class ?>"><?= $total_rating_ganho_display ?></span></div>
                                    </div>
                                </div>
                            </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1) : ?>
                    <div class="mt-6 flex justify-center">
                        <div class="join">
                            <?php
                            // Lógica para exibir um número limitado de páginas
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);

                            if ($page > 1) {
                                $params = $_GET;
                                $params['page'] = $page - 1;
                                echo '<a href="?' . http_build_query($params) . '" class="join-item btn btn-sm">«</a>';
                            }

                            if ($start_page > 1) {
                                $params = $_GET;
                                $params['page'] = 1;
                                echo '<a href="?' . http_build_query($params) . '" class="join-item btn btn-sm">1</a>';
                                if ($start_page > 2) {
                                    echo '<button class="join-item btn btn-sm btn-disabled">...</button>';
                                }
                            }

                            for ($i = $start_page; $i <= $end_page; $i++) {
                                $params = $_GET;
                                $params['page'] = $i;
                                $is_active = ($i == $page) ? 'btn-active' : '';
                                echo '<a href="?' . http_build_query($params) . '" class="join-item btn btn-sm ' . $is_active . '">' . $i . '</a>';
                            }

                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<button class="join-item btn btn-sm btn-disabled">...</button>';
                                }
                                $params = $_GET;
                                $params['page'] = $total_pages;
                                echo '<a href="?' . http_build_query($params) . '" class="join-item btn btn-sm">' . $total_pages . '</a>';
                            }

                            if ($page < $total_pages) {
                                $params = $_GET;
                                $params['page'] = $page + 1;
                                echo '<a href="?' . http_build_query($params) . '" class="join-item btn btn-sm">»</a>';
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>

            </section>
        </main>
    </div>
</body>

</html>