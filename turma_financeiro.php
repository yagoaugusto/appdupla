<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php
require_once '_head.php';

// --- LÓGICA DA PÁGINA ---
if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) { /* ... (verificação de permissão) */ }

$turma_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$turma_id) { /* ... (redirecionamento de erro) */ }

// Busca os dados da turma, dos alunos e do histórico financeiro
$turma = Turma::getTurmaById($turma_id);
$alunos_ativos = array_filter(Turma::getAlunosDaTurma($turma_id), fn($a) => $a['status'] == 'ativo');
$mensalidades = Turma::getMensalidadesDaTurma($turma_id);

if (!$turma) { /* ... (redirecionamento se a turma não for encontrada) */ }
?>

<body class="bg-gray-100 flex flex-col min-h-screen">
    <?php require_once '_nav_superior.php'; ?>
    <div class="flex flex-1 pt-16">
        <?php require_once '_nav_lateral.php'; ?>
        <main class="flex-1 p-4 sm:p-6">
            <section class="max-w-7xl mx-auto">

                <div class="flex items-center justify-between mb-6">
                    <div>
                        <a href="turmas.php?arena_id=<?= $turma['arena_id'] ?>" class="text-sm text-gray-500 hover:underline">Turmas</a>
                        <span class="text-sm text-gray-500 mx-2">/</span>
                        <a href="turma_detalhes.php?id=<?= $turma_id ?>" class="text-sm text-gray-500 hover:underline">Detalhes da Turma</a>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Financeiro: <?= htmlspecialchars($turma['nome']) ?></h1>
                    <button class="btn btn-primary" onclick="modalPagar.showModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        Registar Pagamento
                    </button>
                </div>
                
                <?php // Bloco para exibir mensagens de sucesso/erro ?>

                <div class="bg-white p-4 sm:p-6 rounded-xl shadow-md border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead><tr><th>Aluno</th><th>Competência</th><th>Vencimento</th><th>Valor</th><th>Status</th><th>Data Pag.</th></tr></thead>
                            <tbody>
                                <?php foreach ($mensalidades as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['aluno_nome'] . ' ' . $m['aluno_sobrenome']) ?></td>
                                    <td><?= date('m/Y', strtotime($m['competencia'])) ?></td>
                                    <td><?= date('d/m/Y', strtotime($m['data_vencimento'])) ?></td>
                                    <td class="font-mono">R$ <?= number_format($m['valor'], 2, ',', '.') ?></td>
                                    <td>
                                        <span class="badge 
                                            <?= $m['status'] == 'paga' ? 'badge-success' : '' ?>
                                            <?= $m['status'] == 'pendente' ? 'badge-warning' : '' ?>
                                            <?= $m['status'] == 'vencida' ? 'badge-error' : '' ?>
                                        "><?= ucfirst($m['status']) ?></span>
                                    </td>
                                    <td><?= $m['data_pagamento'] ? date('d/m/Y H:i', strtotime($m['data_pagamento'])) : '-' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </section>
        </main>
    </div>

    <dialog id="modalPagar" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Registar Pagamento de Plano</h3>
            <form method="POST" action="controllers/turma_controller.php">
                <input type="hidden" name="action" value="registrar_pagamento_plano">
                <input type="hidden" name="turma_id" value="<?= $turma_id ?>">
                <input type="hidden" id="aluno_selecionado_data" name="aluno_selecionado_data">

                <div class="form-control py-4 space-y-4">
                    <div>
                        <label class="label"><span class="label-text">Aluno</span></label>
                        <select id="alunoSelect" name="matricula_id" class="select select-bordered w-full" required>
                            <option disabled selected value="">Selecione um aluno</option>
                            <?php foreach ($alunos_ativos as $aluno): ?>
                                <option value="<?= $aluno['matricula_id'] ?>" 
                                        data-info='<?= htmlspecialchars(json_encode(["aluno_id" => $aluno['id'], "data_matricula" => $aluno['data_matricula']]), ENT_QUOTES, 'UTF-8') ?>'>
                                    <?= htmlspecialchars($aluno['nome'] . ' ' . $aluno['sobrenome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="label">
                            <span class="label-text">Início da Competência do Plano</span>
                            <span class="label-text-alt">Mude apenas para pagamentos antigos</span>
                        </label>
                        <input type="month" 
                               name="data_inicio_plano" 
                               class="input input-bordered w-full" 
                               value="<?= date('Y-m') ?>">
                    </div>
                    <div>
                        <label class="label"><span class="label-text">Plano</span></label>
                        <select id="planoSelect" name="plano" class="select select-bordered w-full">
                            <option value="mensal">Mensal (1 mês)</option>
                            <option value="trimestral">Trimestral (3 meses)</option>
                            <option value="semestral">Semestral (6 meses)</option>
                        </select>
                    </div>
                    <div>
                        <label class="label"><span class="label-text">Valor Total a Pagar</span></label>
                        <input type="text" id="valorPago" name="valor_pago" class="input input-bordered w-full font-bold text-lg" value="<?= number_format($turma['valor_mensalidade'], 2, ',', '.') ?>">
                    </div>
                    <div>
                        <label class="label"><span class="label-text">Forma de Pagamento</span></label>
                        <select name="forma_pagamento" class="select select-bordered w-full">
                            <option value="pix">PIX</option>
                            <option value="dinheiro">Dinheiro</option>
                            <option value="credito">Cartão de Crédito</option>
                            <option value="debito">Cartão de Débito</option>
                            <option value="cortesia">Cortesia</option>
                        </select>
                    </div>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn" onclick="modalPagar.close()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Pagamento</button>
                </div>
            </form>
        </div>
    </dialog>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function() {
            const valorBase = <?= $turma['valor_mensalidade'] ?>;
            const valorInput = $('#valorPago');
            valorInput.mask('#.##0,00', {reverse: true});

            // Atualiza o valor total a pagar quando o plano muda
            $('#planoSelect').on('change', function() {
                let multiplicador = 1;
                if (this.value === 'trimestral') multiplicador = 3;
                if (this.value === 'semestral') multiplicador = 6;
                
                const valorTotal = valorBase * multiplicador;
                valorInput.val(valorTotal.toFixed(2).replace('.', ',')).trigger('input');
            });

            // Guarda os dados do aluno num campo escondido quando ele é selecionado
            $('#alunoSelect').on('change', function() {
                const selectedData = $(this).find('option:selected').data('info');
                $('#aluno_selecionado_data').val(JSON.stringify(selectedData));
            });
        });
    </script>

</body>
</html>