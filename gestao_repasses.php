<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php
require_once '_head.php';

// --- LÓGICA DA PÁGINA ---
if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) {
    header("Location: principal.php");
    exit;
}

$arenas_gestor = Quadras::getArenasDoGestor($_SESSION['DuplaUserId']);
$arena_id_selecionada = filter_input(INPUT_GET, 'arena_id', FILTER_VALIDATE_INT);
$competencia_selecionada = filter_input(INPUT_GET, 'competencia') ?: date('Y-m');

$repasses_pendentes = [];
$repasses_pagos = [];
if ($arena_id_selecionada) {
    $repasses_pendentes = Turma::getRepassesPendentes($arena_id_selecionada, $competencia_selecionada);
    $repasses_pagos = Turma::getRepassesPagos($arena_id_selecionada, $competencia_selecionada);
}
?>

<body class="bg-gray-100 flex flex-col min-h-screen" x-data="{ activeTab: 'pendentes' }">

    <?php require_once '_nav_superior.php'; ?>
    <div class="flex flex-1 pt-16">
        <?php require_once '_nav_lateral.php'; ?>
        <main class="flex-1 p-4 sm:p-6">
            <section class="max-w-7xl mx-auto space-y-6">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Gestão de Repasses a Professores</h1>

                <div class="bg-white p-4 rounded-xl shadow-md border">
                    <form method="GET" action="gestao_repasses.php" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="form-control">
                            <label class="label"><span class="label-text">Arena</span></label>
                            <select name="arena_id" class="select select-bordered" required onchange="this.form.submit()">
                                <option value="">Selecione...</option><?php foreach ($arenas_gestor as $a) : ?><option value="<?= $a['id'] ?>" <?= ($arena_id_selecionada == $a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['titulo']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Competência do Repasse</span></label>
                            <select name="competencia" class="select select-bordered" onchange="this.form.submit()"><?php $data_ref = new DateTime();
                                                                                                                    for ($i = -6; $i <= 6; $i++) {
                                                                                                                        $d = (clone $data_ref)->modify("$i month");
                                                                                                                        $v = $d->format('Y-m');
                                                                                                                        $t = ucfirst(strftime('%B / %Y', $d->getTimestamp()));
                                                                                                                        echo "<option value=\"$v\" " . ($competencia_selecionada == $v ? 'selected' : '') . ">$t</option>";
                                                                                                                    } ?></select>
                        </div>
                    </form>
                </div>

                <?php if (isset($_SESSION['mensagem'])) : list($tipo, $texto) = $_SESSION['mensagem']; ?>
                    <div class="alert <?= $tipo === 'success' ? 'alert-success' : 'alert-error' ?> shadow-lg">
                        <div><span><?= htmlspecialchars($texto) ?></span></div>
                    </div>
                <?php unset($_SESSION['mensagem']);
                endif; ?>

                <?php if ($arena_id_selecionada) : ?>
                    <div class="tabs tabs-boxed">
                        <a class="tab" :class="{'tab-active': activeTab === 'pendentes'}" @click.prevent="activeTab = 'pendentes'">
                            Pendentes <div class="badge ml-2"><?= count($repasses_pendentes) ?></div>
                        </a>
                        <a class="tab" :class="{'tab-active': activeTab === 'pagos'}" @click.prevent="activeTab = 'pagos'">
                            Histórico de Pagos
                        </a>
                    </div>

                    <div x-show="activeTab === 'pendentes'" class="space-y-4">
                        <?php if (empty($repasses_pendentes)) : ?>
                            <div class="text-center bg-white p-8 rounded-xl shadow-md border">
                                <p class="text-gray-500">Nenhum repasse pendente para a competência selecionada.</p>
                            </div>
                        <?php else : ?>
                            <?php foreach ($repasses_pendentes as $prof_id => $dados_prof) : ?>
                                <div class="bg-white p-4 sm:p-6 rounded-xl shadow-md border" x-data="{ selectedRepasses: [], selectAll: false }">
                                    <form method="POST" action="controllers/repasse_controller.php">
                                        <input type="hidden" name="action" value="pagar_repasses"><input type="hidden" name="arena_id" value="<?= $arena_id_selecionada ?>"><input type="hidden" name="competencia" value="<?= $competencia_selecionada ?>">
                                        <div class="flex justify-between items-center mb-4">
                                            <h2 class="text-xl font-bold"><?= htmlspecialchars($dados_prof['professor_nome']) ?></h2>
                                            <div class="text-right">
                                                <div class="text-gray-500">Total a Pagar</div>
                                                <div class="font-extrabold text-lg text-success">R$ <?= number_format($dados_prof['total_a_pagar'], 2, ',', '.') ?></div>
                                            </div>
                                        </div>
                                        <div class="overflow-x-auto">
                                            <table class="table w-full">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" class="checkbox" @click="selectAll = !selectAll; selectedRepasses = selectAll ? <?= htmlspecialchars(json_encode(array_column($dados_prof['repasses'], 'repasse_id'))) ?> : []"></th>
                                                        <th>Aluno</th>
                                                        <th>Turma</th>
                                                        <th>Competência</th>
                                                        <th class="text-right">Valor do Repasse</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($dados_prof['repasses'] as $repasse) : ?>
                                                        <tr>
                                                            <td><input type="checkbox" class="checkbox" name="repasses_ids[]" value="<?= $repasse['repasse_id'] ?>" x-model="selectedRepasses"></td>
                                                            <td><?= htmlspecialchars($repasse['aluno_nome'] . ' ' . $repasse['aluno_sobrenome']) ?></td>
                                                            <td>
                                                                <div class="badge badge-ghost"><?= htmlspecialchars($repasse['turma_nome']) ?></div>
                                                            </td>
                                                            <td><?= date('m/Y', strtotime($repasse['competencia'])) ?></td>
                                                            <td class="text-right font-mono">R$ <?= number_format($repasse['valor_repasse'], 2, ',', '.') ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="flex justify-end items-center mt-4 gap-4" x-show="selectedRepasses.length > 0" x-cloak>
                                            <div class="font-bold"><span x-text="selectedRepasses.length"></span> repasse(s) selecionado(s)</div>
                                            <button type="button" class="btn btn-primary" onclick="document.getElementById('modalPagar_<?= $prof_id ?>').showModal()">Pagar Selecionados</button>
                                        </div>
                                        <dialog id="modalPagar_<?= $prof_id ?>" class="modal">
                                            <div class="modal-box">
                                                <h3 class="font-bold text-lg">Confirmar Pagamento</h3>
                                                <p class="py-4">Confirme a data para registar o pagamento dos <strong x-text="selectedRepasses.length"></strong> repasses selecionados.</p>
                                                <div class="form-control"><label class="label"><span class="label-text">Data do Pagamento</span></label><input type="date" name="data_pagamento" class="input input-bordered w-full" value="<?= date('Y-m-d') ?>" required></div>
                                                <div class="modal-action"><button type="button" class="btn" onclick="document.getElementById('modalPagar_<?= $prof_id ?>').close()">Cancelar</button><button type="submit" class="btn btn-primary">Confirmar Pagamento</button></div>
                                            </div>
                                        </dialog>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div x-show="activeTab === 'pagos'" x-cloak class="space-y-4">
                        <?php if (empty($repasses_pagos)) : ?>
                            <div class="text-center bg-white p-8 rounded-xl shadow-md border">
                                <p class="text-gray-500">Nenhum repasse pago encontrado para a competência selecionada.</p>
                            </div>
                        <?php else : ?>
                            <?php foreach ($repasses_pagos as $prof_id => $dados_prof) : ?>
                                <div class="bg-white p-4 sm:p-6 rounded-xl shadow-md border">
                                    <div class="flex justify-between items-center mb-4">
                                        <h2 class="text-xl font-bold"><?= htmlspecialchars($dados_prof['professor_nome']) ?></h2>
                                        <div class="text-right">
                                            <div class="text-gray-500">Total Pago na Competência</div>
                                            <div class="font-extrabold text-lg text-gray-800">R$ <?= number_format($dados_prof['total_pago'], 2, ',', '.') ?></div>
                                        </div>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="table table-sm w-full">
                                            <thead>
                                                <tr>
                                                    <th>Aluno</th>
                                                    <th>Turma</th>
                                                    <th>Competência</th>
                                                    <th class="text-right">Valor do Repasse</th>
                                                    <th class="text-right">Data do Pagamento</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($dados_prof['repasses'] as $repasse) : ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($repasse['aluno_nome'] . ' ' . $repasse['aluno_sobrenome']) ?></td>
                                                        <td>
                                                            <div class="badge badge-ghost"><?= htmlspecialchars($repasse['turma_nome']) ?></div>
                                                        </td>
                                                        <td><?= date('m/Y', strtotime($repasse['competencia'])) ?></td>
                                                        <td class="text-right font-mono">R$ <?= number_format($repasse['valor_repasse'], 2, ',', '.') ?></td>
                                                        <td class="text-right font-mono"><?= date('d/m/Y', strtotime($repasse['data_repasse'])) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>

</html>