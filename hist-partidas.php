<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<?php
$jogador = $_SESSION['DuplaUserId'];
$partidas = Partida::partidas_jogador($jogador);
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
                Histórico de Partidas
            </h1>

            <?php
            // Função auxiliar para renderizar o badge de variação de rating
            function render_diff_badge($diff) {
                if ($diff === null) return '';
                $diff_val = round($diff, 1);
                $color_class = $diff_val >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                $icon = $diff_val >= 0 ? '▲' : '▼';
                $sign = $diff_val > 0 ? '+' : '';
                return "<span class='text-xs {$color_class} rounded px-1.5 py-0.5 font-mono'>{$icon} {$sign}{$diff_val}</span>";
            }
            ?>

            <?php if (empty($partidas)): ?>
                <div class="text-center bg-white rounded-xl shadow p-8 mt-6">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mt-4">Nenhuma partida encontrada</h3>
                    <p class="text-gray-500 mt-2">Parece que você ainda não registrou nenhuma partida. Que tal começar agora?</p>
                    <a href="nova-partida.php" class="mt-6 inline-block bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-full transition-transform transform hover:scale-105">
                        Registrar Nova Partida
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($partidas as $p): ?>
                        <?php
                        // --- Lógica para determinar o estado e estilo do card ---
                        $time_a = [$p['jogador1_id'], $p['jogador2_id']];
                        $time_usuario = in_array($jogador, $time_a) ? 'A' : 'B';
                        $venceu = ($p['vencedor'] == $time_usuario);
                        $data = date('d M Y', strtotime($p['data']));

                        // --- Variáveis de Estilo ---
                        $card_border_class = $venceu ? 'border-green-500' : 'border-red-500';
                        $tag_bg_class = $venceu ? 'bg-green-600' : 'bg-red-600';
                        $tag_text = $venceu ? 'Vitória' : 'Derrota';

                        if ($p['status'] === 'pendente') {
                            $status_label = '⏳ Pendente';
                            $status_classes = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                        } else {
                            $status_label = '✅ Finalizada';
                            $status_classes = 'bg-blue-100 text-blue-800 border-blue-200';
                        }
                        ?>
                        <div class="relative bg-white rounded-xl shadow p-4 border-l-4 <?= htmlspecialchars($card_border_class) ?> flex flex-col items-center transition hover:shadow-md">
                            <!-- Tarja de Resultado -->
                            <div class="absolute -top-3 left-4 px-3 py-1 rounded-full <?= htmlspecialchars($tag_bg_class) ?> text-white text-xs font-bold shadow z-10 border-2 border-white flex items-center gap-1">
                                <?= htmlspecialchars($tag_text) ?>
                            </div>
                            <!-- Link do cartão -->
                            <a href="pos-partida.php?j=<?= urlencode($jogador) ?>&p=<?= urlencode($p['token_validacao']) ?>" class="absolute inset-0 z-10" title="Ver detalhes da partida" style="border-radius: 0.75rem;"></a>
                            
                            <!-- Times e Placar -->
                            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-2 w-full mt-4 relative z-20">
                                <!-- Time A -->
                                <div class="flex flex-col items-end text-right space-y-2">
                                    <!-- Jogador 1 -->
                                    <div>
                                        <div class="truncate">
                                            <span class="font-semibold text-sm text-gray-800 block"><?= htmlspecialchars($p['nomej1']) ?></span>
                                            <?php if (!empty($p['apelidoj1'])): ?><span class="text-xs text-gray-500 block -mt-1">(<?= htmlspecialchars($p['apelidoj1']) ?>)</span><?php endif; ?>
                                        </div>
                                        <div class="mt-1"><?= $p['status'] === 'validada' ? render_diff_badge($p['diff_h1']) : '' ?></div>
                                    </div>
                                    <!-- Jogador 2 -->
                                    <div>
                                        <div class="truncate">
                                            <span class="font-semibold text-sm text-gray-800 block"><?= htmlspecialchars($p['nomej2']) ?></span>
                                            <?php if (!empty($p['apelidoj2'])): ?><span class="text-xs text-gray-500 block -mt-1">(<?= htmlspecialchars($p['apelidoj2']) ?>)</span><?php endif; ?>
                                        </div>
                                        <div class="mt-1"><?= $p['status'] === 'validada' ? render_diff_badge($p['diff_h2']) : '' ?></div>
                                    </div>
                                </div>

                                <!-- Placar -->
                                <div class="flex items-center gap-2 text-center">
                                    <span class="text-xl font-bold text-gray-700 bg-gray-100 rounded-md px-2 py-1 shadow-inner w-10 text-center"><?= htmlspecialchars($p['placar_a']) ?></span>
                                    <span class="text-gray-400 font-bold text-xs">VS</span>
                                    <span class="text-xl font-bold text-gray-700 bg-gray-100 rounded-md px-2 py-1 shadow-inner w-10 text-center"><?= htmlspecialchars($p['placar_b']) ?></span>
                                </div>

                                <!-- Time B -->
                                <div class="flex flex-col items-start text-left space-y-2">
                                    <!-- Jogador 3 -->
                                    <div>
                                        <div class="truncate">
                                            <span class="font-semibold text-sm text-gray-800 block"><?= htmlspecialchars($p['nomej3']) ?></span>
                                            <?php if (!empty($p['apelidoj3'])): ?><span class="text-xs text-gray-500 block -mt-1">(<?= htmlspecialchars($p['apelidoj3']) ?>)</span><?php endif; ?>
                                        </div>
                                        <div class="mt-1"><?= $p['status'] === 'validada' ? render_diff_badge($p['diff_h3']) : '' ?></div>
                                    </div>
                                    <!-- Jogador 4 -->
                                    <div>
                                        <div class="truncate">
                                            <span class="font-semibold text-sm text-gray-800 block"><?= htmlspecialchars($p['nomej4']) ?></span>
                                            <?php if (!empty($p['apelidoj4'])): ?><span class="text-xs text-gray-500 block -mt-1">(<?= htmlspecialchars($p['apelidoj4']) ?>)</span><?php endif; ?>
                                        </div>
                                        <div class="mt-1"><?= $p['status'] === 'validada' ? render_diff_badge($p['diff_h4']) : '' ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rodapé do cartão: Status e Data -->
                            <div class="flex items-center justify-between w-full mt-4 pt-3 border-t border-gray-200/80 gap-2">
                                <div class="text-xs text-gray-500 flex items-center gap-2">
                                    <span><?= htmlspecialchars($data) ?></span>
                                    <?php if (!empty($p['data'])): ?>
                                        <span class="mx-1">•</span>
                                        <span><?= date('H:i', strtotime($p['data'])) ?></span>
                                    <?php endif; ?>
                                    <span class="mx-1">•</span>
                                    <span class="font-semibold">#<?= htmlspecialchars($p['id']) ?></span>
                                </div>
                                <span class="flex items-center gap-1 text-xs font-semibold rounded-full px-3 py-1 border shadow-sm <?= htmlspecialchars($status_classes) ?>">
                                    <?= htmlspecialchars($status_label) ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <br><br>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>
</body>

</html>