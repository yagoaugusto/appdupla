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

            <div class="space-y-4">

                <!-- Partida 1 -->
                <?php foreach ($partidas as $p): ?>
                    <?php
                    $time_a = [$p['jogador1_id'], $p['jogador2_id']];
                    $time_b = [$p['jogador3_id'], $p['jogador4_id']];
                    $time_usuario = in_array($jogador, $time_a) ? 'A' : 'B';
                    $vencedor = $p['vencedor'];
                    $venceu = ($time_usuario == $vencedor);
                    $classe_resultado = $venceu ? 'green' : 'red';
                    $classe_resultado_adv = !$venceu ? 'green' : 'red';
                    $data = date('d M Y', strtotime($p['data']));
                    $cor_resultado = $venceu ? '#DFF2BF' : '#FFBABA';
                    $status = $p['status'];
                    ?>
                    <div class="relative bg-white rounded-xl shadow p-3 border border-gray-100 flex flex-col items-center" style="background-color:<?= $cor_resultado ?>;">
                        <!-- Tarja de Resultado Centralizada -->
                        <div class="absolute left-1/2 -top-3 -translate-x-1/2 px-4 py-1 rounded-full bg-<?= $classe_resultado ?>-600 text-white text-xs font-bold shadow z-10 border-2 border-white flex items-center gap-1">
                            <?= $venceu ? 'Vitória' : 'Derrota' ?>
                        </div>
                        <!-- Times e Placar -->
                        <div class="flex items-center gap-4 w-full justify-center mt-4">
                            <!-- Time A -->
                            <div class="flex flex-col items-end flex-1">
                                <span class="font-semibold text-gray-800 truncate"><?= $p['nomej1'] ?></span>
                                <?php if ($p['status'] === 'validada'): ?>
                                    <div class="flex gap-1 mt-0.5">
                                        <span class="text-xs bg-gray-100 text-gray-600 rounded px-1.5 py-0.5 font-mono"><?= round($p['diff_h1'],1) ?></span>
                                    </div>
                                <?php endif; ?>
                                <span class="font-semibold text-gray-800 truncate mt-2"><?= $p['nomej2'] ?></span>
                                <?php if ($p['status'] === 'validada'): ?>
                                    <div class="flex gap-1 mt-0.5">
                                        <span class="text-xs bg-gray-100 text-gray-600 rounded px-1.5 py-0.5 font-mono"><?= round($p['diff_h2'],1) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="text-base font-bold text-<?= ($time_usuario == 'A' ? $classe_resultado : $classe_resultado_adv) ?>-700 bg-<?= ($time_usuario == 'A' ? $classe_resultado : $classe_resultado_adv) ?>-100 rounded px-2 py-1 shadow"><?= $p['placar_a'] ?></span>
                            <!-- VS Circle -->
                            <span class="mx-2 flex items-center justify-center rounded-full bg-gray-200 text-gray-700 font-bold text-sm w-8 h-8 shadow-inner border border-gray-300">
                                VS
                            </span>
                            <span class="text-base font-bold text-<?= ($time_usuario == 'B' ? $classe_resultado : $classe_resultado_adv) ?>-700 bg-<?= ($time_usuario == 'B' ? $classe_resultado : $classe_resultado_adv) ?>-100 rounded px-2 py-1 shadow"><?= $p['placar_b'] ?></span>
                            <!-- Time B -->
                            <div class="flex flex-col items-start flex-1">
                                <span class="font-semibold text-gray-800 truncate"><?= $p['nomej3'] ?></span>
                                <?php if ($p['status'] === 'validada'): ?>
                                    <div class="flex gap-1 mt-0.5">
                                        <span class="text-xs bg-gray-100 text-gray-600 rounded px-1.5 py-0.5 font-mono"><?= round($p['diff_h3'],1) ?></span>
                                    </div>
                                <?php endif; ?>
                                <span class="font-semibold text-gray-800 truncate mt-2"><?= $p['nomej4'] ?></span>
                                <?php if ($p['status'] === 'validada'): ?>
                                    <div class="flex gap-1 mt-0.5">
                                        <span class="text-xs bg-gray-100 text-gray-600 rounded px-1.5 py-0.5 font-mono"><?= round($p['diff_h4'],1) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Data e ID Centralizados e Discretos -->
                        <div class="mt-2 text-xs text-gray-400 text-center w-full">
                            <span><?= $data ?></span>
                            <span class="mx-1">•</span>
                            <span class="font-semibold text-gray-500">#<?= $p['id'] ?></span>
                        </div>
                        <!-- Rodapé do cartão: Botão e Status -->
                        <?php
                        $status = $p['status'];
                        if ($status === 'pendente') {
                            $status_label = '⏳ Pendente';
                            $status_color = 'yellow';
                        } else {
                            $status_label = '✅ Finalizada';
                            $status_color = 'blue';
                        }
                        ?>
                        <div class="flex items-center justify-between w-full mt-4 pt-2 border-t border-gray-200 gap-2">
                            <a href="pos-partida.php?j=<?= urlencode($jogador) ?>&p=<?= urlencode($p['token_validacao']) ?>"
                                class="flex items-center gap-1 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded-full px-3 py-1 shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-300"
                                title="Ver detalhes da partida">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0c0 5-9 9-9 9s-9-4-9-9a9 9 0 0118 0z" />
                                </svg>
                                Ver partida
                            </a>
                            <span class="flex items-center gap-1 bg-<?= $status_color ?>-100 text-<?= $status_color ?>-700 text-xs font-semibold rounded-full px-3 py-1 border border-<?= $status_color ?>-200 shadow">
                                <?= $status_label ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <br><br>

            </div>
        </main>
    </div>
    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>
</body>

</html>