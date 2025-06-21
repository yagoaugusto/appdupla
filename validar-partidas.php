<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<?php
$jogador = $_SESSION['DuplaUserId'];
$partidas = Partida::partidas_pendente_jogador($jogador);
?>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <?php require_once '_nav_superior.php'; ?>
    <div class="flex pt-16">
        <?php require_once '_nav_lateral.php'; ?>

        <main class="flex-1 p-4">
            <h1 class="text-2xl font-extrabold mb-6 tracking-tight text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Partidas aguardando validação
            </h1>

            <div class="space-y-4">

                <?php if (empty($partidas)): ?>
                    <div class="text-center bg-white rounded-xl shadow p-8 mt-6">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700 mt-4">Nenhuma partida pendente</h3>
                        <p class="text-gray-500 mt-2">Você não tem partidas aguardando validação no momento. Que tal registrar uma nova?</p>
                        <a href="nova-partida.php" class="mt-6 inline-block bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-full transition-transform transform hover:scale-105">
                            Registrar Nova Partida
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($partidas as $p): ?>
                        <?php
                        $time_a_ids = [$p['jogador1_id'], $p['jogador2_id']];
                        $time_b_ids = [$p['jogador3_id'], $p['jogador4_id']];

                        $is_user_in_team_a = in_array($jogador, $time_a_ids);
                        $is_user_in_team_b = in_array($jogador, $time_b_ids);

                        // Determine which team the current user is on
                        $user_team = null;
                        if ($is_user_in_team_a) {
                            $user_team = 'A';
                        } elseif ($is_user_in_team_b) {
                            $user_team = 'B';
                        }

                        $venceu = ($user_team == $p['vencedor']);

                        $card_border_class = $venceu ? 'border-green-500' : 'border-red-500';
                        $tag_bg_class = $venceu ? 'bg-green-600' : 'bg-red-600';
                        $tag_text = $venceu ? 'Vitória' : 'Derrota';
                        $tag_icon = $venceu ? '✅' : '❌';

                        $placar_time_a_class = $p['vencedor'] == 'A' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100';
                        $placar_time_b_class = $p['vencedor'] == 'B' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100';

                        $data_formatada = date('d M Y', strtotime($p['data']));
                        ?>
                        <div class="relative bg-white rounded-xl shadow p-4 border-l-8 <?= htmlspecialchars($card_border_class) ?> flex flex-col items-center transition hover:shadow-md">
                            <!-- Tarja de Resultado -->
                            <div class="absolute left-1/2 -top-3 -translate-x-1/2 px-3 py-1 rounded-full <?= htmlspecialchars($tag_bg_class) ?> text-white text-xs font-bold shadow z-10 border-2 border-white flex items-center gap-1">
                                <?= htmlspecialchars($tag_icon) ?> <?= htmlspecialchars($tag_text) ?>
                            </div>

                            <!-- Times e Placar -->
                            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 w-full mt-4">
                                <!-- Time A -->
                                <div class="flex flex-col items-end text-right space-y-1">
                                    <div class="flex items-center gap-1">
                                        <span class="font-semibold text-sm text-gray-800 truncate"><?= htmlspecialchars($p['nomej1']) ?></span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="font-semibold text-sm text-gray-800 truncate"><?= htmlspecialchars($p['nomej2']) ?></span>
                                    </div>
                                </div>

                                <!-- Placar -->
                                <div class="flex items-center gap-2 text-center">
                                    <span class="text-xl font-bold rounded-md px-2 py-1 shadow-inner w-10 text-center <?= htmlspecialchars($placar_time_a_class) ?>"><?= htmlspecialchars($p['placar_a']) ?></span>
                                    <span class="text-gray-400 font-bold text-xs">VS</span>
                                    <span class="text-xl font-bold rounded-md px-2 py-1 shadow-inner w-10 text-center <?= htmlspecialchars($placar_time_b_class) ?>"><?= htmlspecialchars($p['placar_b']) ?></span>
                                </div>

                                <!-- Time B -->
                                <div class="flex flex-col items-start text-left space-y-1">
                                    <div class="flex items-center gap-1">
                                        <span class="font-semibold text-sm text-gray-800 truncate"><?= htmlspecialchars($p['nomej3']) ?></span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="font-semibold text-sm text-gray-800 truncate"><?= htmlspecialchars($p['nomej4']) ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Data e ID -->
                            <div class="flex items-center justify-between w-full mt-4 pt-3 border-t border-gray-200/80">
                                <div class="text-xs text-gray-500 flex items-center gap-1">
                                    <span><?= htmlspecialchars($data_formatada) ?></span>
                                    <span class="mx-0.5">•</span>
                                    <span class="font-semibold">#<?= htmlspecialchars($p['id']) ?></span>
                                </div>
                                <!-- Botão VALIDAR -->
                                <form method="GET" action="v2.php">
                                    <input type="hidden" name="p" value="<?= htmlspecialchars($p['token_validacao']) ?>">
                                    <input type="hidden" name="j" value="<?= htmlspecialchars($jogador) ?>">
                                    <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1.5 px-4 rounded-full shadow-md transition-colors text-xs uppercase tracking-wide">
                                        Validar Partida
                                    </button>
                                </form>
                            </div>
                        </div>
                <?php endforeach; ?> 
                <?php endif; ?>
                <br><br>

            </div>
        </main>
    </div>
    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>
</body>
</html>