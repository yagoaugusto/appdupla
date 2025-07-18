<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php
require_once '_head.php';

// --- LÓGICA DA PÁGINA ---
if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) { /* ... */
}

$arenas_gestor = Quadras::getArenasDoGestor($_SESSION['DuplaUserId']);
$arena_id_selecionada = filter_input(INPUT_GET, 'arena_id', FILTER_VALIDATE_INT);

// Filtros
$filtros = [
    'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING),
    'categoria_id' => filter_input(INPUT_GET, 'categoria_id', FILTER_VALIDATE_INT),
    'competencia' => filter_input(INPUT_GET, 'competencia') ?: date('Y-m'),
];

$despesas = [];
$categorias = [];
$stats = ['pago_mes' => 0, 'a_pagar_total' => 0, 'vencido_total' => 0];

if ($arena_id_selecionada) {
    $despesas = Despesa::getDespesasPorArena($arena_id_selecionada, $filtros);
    $categorias = Despesa::getCategoriasPorArena($arena_id_selecionada);
    $stats = Despesa::getStatsDespesas($arena_id_selecionada, $filtros['competencia']);
}
?>

<body class="bg-gray-100 flex flex-col min-h-screen">

    <?php require_once '_nav_superior.php'; ?>
    <div class="flex flex-1 pt-16">
        <?php require_once '_nav_lateral.php'; ?>
        <main class="flex-1 p-4 sm:p-6">
            <section class="max-w-7xl mx-auto space-y-6">
                <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Gestão de Despesas</h1>

                <div class="bg-white p-4 rounded-xl shadow-md border">
                    <form method="GET" action="gestao_despesas.php"><label class="label"><span class="label-text">Selecione a Arena</span></label><select name="arena_id" class="select select-bordered" required onchange="this.form.submit()">
                            <option value="">Escolha...</option><?php foreach ($arenas_gestor as $a): ?><option value="<?= $a['id'] ?>" <?= ($arena_id_selecionada == $a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['titulo']) ?></option><?php endforeach; ?>
                        </select></form>
                </div>

                <?php if ($arena_id_selecionada): ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="stat bg-white rounded-xl shadow border">
                            <div class="stat-title">Pago (Mês)</div>
                            <div class="stat-value text-success">R$<?= number_format($stats['pago_mes'], 2, ',', '.') ?></div>
                        </div>
                        <div class="stat bg-white rounded-xl shadow border">
                            <div class="stat-title">A Pagar (Total)</div>
                            <div class="stat-value text-warning">R$<?= number_format($stats['a_pagar_total'], 2, ',', '.') ?></div>
                        </div>
                        <div class="stat bg-white rounded-xl shadow border">
                            <div class="stat-title">Vencido (Total)</div>
                            <div class="stat-value text-error">R$<?= number_format($stats['vencido_total'], 2, ',', '.') ?></div>
                        </div>
                    </div>

                    <div class="bg-white p-4 rounded-xl shadow-md border">
                        <form method="GET" action="gestao_despesas.php" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <input type="hidden" name="arena_id" value="<?= $arena_id_selecionada ?>">
                            <div class="form-control"><label class="label"><span class="label-text">Competência</span></label><input type="month" name="competencia" class="input input-bordered w-full" value="<?= $filtros['competencia'] ?>"></div>
                            <div class="form-control"><label class="label"><span class="label-text">Categoria</span></label><select name="categoria_id" class="select select-bordered w-full">
                                    <option value="">Todas</option><?php foreach ($categorias as $c): ?><option value="<?= $c['id'] ?>" <?= ($filtros['categoria_id'] == $c['id'] ? 'selected' : '') ?>><?= $c['nome'] ?></option><?php endforeach; ?>
                                </select></div>
                            <div class="form-control"><label class="label"><span class="label-text">Status</span></label><select name="status" class="select select-bordered w-full">
                                    <option value="">Todos</option>
                                    <option value="paga" <?= ($filtros['status'] == 'paga' ? 'selected' : '') ?>>Paga</option>
                                    <option value="pendente" <?= ($filtros['status'] == 'pendente' ? 'selected' : '') ?>>Pendente</option>
                                    <option value="vencida" <?= ($filtros['status'] == 'vencida' ? 'selected' : '') ?>>Vencida</option>
                                </select></div>
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </form>
                    </div>

                    <div class="bg-white p-4 sm:p-6 rounded-xl shadow-md border">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold">Lançamentos</h2>
                            <button class="btn btn-primary" onclick="abrirModal()">Adicionar Despesa</button>
                        </div>
                        <?php if (isset($_SESSION['mensagem'])): list($tipo, $texto) = $_SESSION['mensagem']; ?><div class="alert <?= $tipo === 'success' ? 'alert-success' : 'alert-error' ?> shadow-lg mb-5">
                                <div><span><?= htmlspecialchars($texto) ?></span></div>
                            </div><?php unset($_SESSION['mensagem']);
                                endif; ?>
                        <div class="overflow-x-auto">
                            <table class="table w-full">
                                <thead>
                                    <tr>
                                        <th>Descrição</th>
                                        <th>Categoria</th>
                                        <th>Vencimento</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($despesas as $d): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($d['descricao']) ?></td>
                                            <td>
                                                <div class="badge badge-neutral"><?= htmlspecialchars($d['categoria_nome']) ?></div>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($d['data_vencimento'])) ?></td>
                                            <td class="font-mono">R$<?= number_format($d['valor'], 2, ',', '.') ?></td>
                                            <td><span class="badge <?= $d['status'] == 'paga' ? 'badge-success' : ($d['status'] == 'pendente' ? 'badge-warning' : 'badge-error') ?>"><?= ucfirst($d['status']) ?></span></td>
                                            <td class="flex gap-1">
                                                <?php if ($d['status'] != 'paga'): ?>
                                                    <button class="btn btn-xs btn-success" onclick="abrirModalPagamento(<?= $d['id'] ?>)">Pagar</button>
                                                <?php endif; ?>
                                                <button class="btn btn-xs btn-outline" onclick='abrirModal(<?= htmlspecialchars(json_encode($d), ENT_QUOTES, "UTF-8") ?>)'>Editar</button>
                                                <a href="controllers/despesa_controller.php?action=apagar_despesa&despesa_id=<?= $d['id'] ?>&arena_id=<?= $arena_id_selecionada ?>" onclick="return confirm('Tem a certeza?')" class="btn btn-xs btn-ghost text-error">✕</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <dialog id="modalPagarDespesa" class="modal">
        <div class="modal-box w-11/12 max-w-xs">
            <h3 class="font-bold text-lg">Registar Pagamento</h3>
            <form id="formPagarDespesa" method="POST" action="controllers/despesa_controller.php" class="py-4">
                <input type="hidden" name="action" value="registrar_pagamento_despesa">
                <input type="hidden" id="pagarDespesaId" name="despesa_id">
                <input type="hidden" name="arena_id" value="<?= $arena_id_selecionada ?>">
                <div class="form-control">
                    <label class="label"><span class="label-text">Data do Pagamento</span></label>
                    <input type="date" name="data_pagamento" class="input input-bordered w-full" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn" onclick="modalPagarDespesa.close()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar</button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog id="modalDespesa" class="modal">
        <div class="modal-box">
            <h3 id="modalTitle" class="font-bold text-lg">Adicionar Despesa</h3>
            <form id="formDespesa" method="POST" action="controllers/despesa_controller.php" class="py-4 space-y-4"><input type="hidden" id="modalAction" name="action"><input type="hidden" id="modalDespesaId" name="despesa_id"><input type="hidden" name="arena_id" value="<?= $arena_id_selecionada ?>">
                <div class="form-control"><label class="label"><span class="label-text">Descrição</span></label><input type="text" id="modalDescricao" name="descricao" class="input input-bordered w-full" required></div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control"><label class="label"><span class="label-text">Categoria</span></label><select id="modalCategoria" name="categoria_id" class="select select-bordered w-full" required>
                            <option disabled selected>Selecione</option><?php foreach ($categorias as $c): ?><option value="<?= $c['id'] ?>"><?= $c['nome'] ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="form-control"><label class="label"><span class="label-text">Valor (R$)</span></label><input type="text" id="modalValor" name="valor" class="input input-bordered w-full" required></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-control"><label class="label"><span class="label-text">Data de Vencimento</span></label><input type="date" id="modalVencimento" name="data_vencimento" class="input input-bordered w-full" required></div>
                    <div class="form-control"><label class="label"><span class="label-text">Data de Pagamento (se já paga)</span></label><input type="date" id="modalPagamento" name="data_pagamento" class="input input-bordered w-full"></div>
                </div>
                <div class="form-control"><label class="label"><span class="label-text">Observações</span></label><textarea id="modalObservacoes" name="observacoes" class="textarea textarea-bordered"></textarea></div>
                <div class="modal-action"><button type="button" class="btn" onclick="modalDespesa.close()">Cancelar</button><button type="submit" class="btn btn-primary">Salvar</button></div>
            </form>
        </div>
    </dialog>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        function abrirModal(d = null) {
            const form = document.getElementById('formDespesa');
            form.reset();
            if (d) {
                document.getElementById('modalTitle').innerText = 'Editar Despesa';
                document.getElementById('modalAction').value = 'editar_despesa';
                document.getElementById('modalDespesaId').value = d.id;
                document.getElementById('modalDescricao').value = d.descricao;
                document.getElementById('modalCategoria').value = d.categoria_id;
                document.getElementById('modalValor').value = parseFloat(d.valor).toLocaleString('pt-BR', {
                    minimumFractionDigits: 2
                });
                document.getElementById('modalVencimento').value = d.data_vencimento;
                document.getElementById('modalPagamento').value = d.data_pagamento;
                document.getElementById('modalObservacoes').value = d.observacoes;
            } else {
                document.getElementById('modalTitle').innerText = 'Adicionar Despesa';
                document.getElementById('modalAction').value = 'criar_despesa';
                document.getElementById('modalDespesaId').value = '';
            }
            $('#modalValor').mask('#.##0,00', {
                reverse: true
            });
            modalDespesa.showModal();
        }

        // NOVA FUNÇÃO para o modal de pagamento
        function abrirModalPagamento(despesa_id) {
            document.getElementById('pagarDespesaId').value = despesa_id;
            modalPagarDespesa.showModal();
        }
    </script>
</body>

</html>