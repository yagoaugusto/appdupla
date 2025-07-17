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

$turma_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$turma_id) {
    $_SESSION['mensagem'] = ['error', 'ID da turma inválido.'];
    header("Location: turmas.php");
    exit;
}

$turma = Turma::getTurmaById($turma_id);
$alunos = Turma::getAlunosDaTurma($turma_id);

if (!$turma) {
    $_SESSION['mensagem'] = ['error', 'Turma não encontrada.'];
    header("Location: turmas.php");
    exit;
}
?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container {
        z-index: 9999 !important;
    }

    .select2-selection--single {
        height: 3rem !important;
        border-radius: 0.5rem !important;
        border: 1px solid #e5e7eb !important;
    }

    .select2-selection__rendered {
        line-height: 3rem !important;
        padding-left: 1rem !important;
    }

    .select2-selection__arrow {
        height: calc(3rem - 2px) !important;
    }
</style>

<body class="bg-gray-100 flex flex-col min-h-screen">
    <?php require_once '_nav_superior.php'; ?>

    <div class="flex flex-1 pt-16">
        <?php require_once '_nav_lateral.php'; ?>

        <main class="flex-1 p-4 sm:p-6">
            <section class="max-w-7xl mx-auto">

                <div class="flex items-center justify-between mb-6">
                    <a href="turmas.php?arena_id=<?= $turma['arena_id'] ?>" class="btn btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Voltar para Turmas
                    </a>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 mb-6">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight"><?= htmlspecialchars($turma['nome']) ?></h1>
                    <p class="text-gray-500 mt-1">Nível: <?= htmlspecialchars($turma['nivel']) ?></p>
                    <div class="stats stats-vertical lg:stats-horizontal shadow mt-4 w-full">
                        <div class="stat">
                            <div class="stat-title">Professor</div>
                            <div class="stat-value text-secondary"><?= htmlspecialchars($turma['professor_nome'] . ' ' . $turma['professor_sobrenome']) ?></div>
                        </div>
                        <div class="stat">
                            <div class="stat-title">Alunos Ativos</div>
                            <div class="stat-value"><?= count(array_filter($alunos, fn($a) => $a['status'] == 'ativo')) ?> / <?= $turma['vagas_total'] ?></div>
                        </div>
                        <div class="stat">
                            <div class="stat-title">Mensalidade</div>
                            <div class="stat-value">R$ <?= number_format($turma['valor_mensalidade'], 2, ',', '.') ?></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-4 sm:p-6 rounded-xl shadow-md border border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Alunos Matriculados</h2>
                        <div class="flex gap-2">
                            <a href="turma_financeiro.php?id=<?= $turma_id ?>" class="btn btn-outline">Ver Financeiro</a>
                            <button class="btn btn-primary" onclick="modalMatricular.showModal()">Matricular Aluno</button>
                        </div>
                    </div>

                    <?php // Bloco para exibir mensagens de sucesso/erro 
                    ?>
                    <?php if (isset($_SESSION['mensagem'])): list($tipo, $texto) = $_SESSION['mensagem']; ?>
                        <div class="alert <?= $tipo === 'success' ? 'alert-success' : 'alert-error' ?> shadow-lg mb-5">
                            <div><span><?= htmlspecialchars($texto) ?></span></div>
                        </div>
                    <?php unset($_SESSION['mensagem']);
                    endif; ?>

                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th>Aluno</th>
                                    <th>Data da Matrícula</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alunos as $aluno): ?>
                                    <tr>
                                        <td>
                                            <div class="font-bold"><?= htmlspecialchars($aluno['nome'] . ' ' . $aluno['sobrenome']) ?></div>
                                            <div class="text-sm opacity-60"><?= htmlspecialchars($aluno['apelido'] ?: 'N/A') ?></div>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($aluno['data_matricula'])) ?></td>
                                        <td>
                                            <div class="badge <?= $aluno['status'] == 'ativo' ? 'badge-success' : 'badge-ghost' ?>">
                                                <?= ucfirst($aluno['status']) ?>
                                            </div>
                                        </td>
                                        <td class="flex gap-2">
                                            <?php if ($aluno['status'] == 'ativo'): ?>
                                                <a href="controllers/turma_controller.php?action=alterar_status_matricula&matricula_id=<?= $aluno['matricula_id'] ?>&status=inativo&turma_id=<?= $turma_id ?>" class="btn btn-xs btn-outline btn-warning">Inativar</a>
                                            <?php else: ?>
                                                <a href="controllers/turma_controller.php?action=alterar_status_matricula&matricula_id=<?= $aluno['matricula_id'] ?>&status=ativo&turma_id=<?= $turma_id ?>" class="btn btn-xs btn-outline btn-success">Ativar</a>
                                            <?php endif; ?>
                                            <a onclick="confirmarRemocao(<?= $aluno['matricula_id'] ?>, '<?= htmlspecialchars(addslashes($aluno['nome'])) ?>')" class="btn btn-xs btn-outline btn-error">Remover</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <dialog id="modalMatricular" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Matricular Aluno e Gerar Cobrança</h3>
            <form method="POST" action="controllers/turma_controller.php" class="py-4 space-y-4">
                <input type="hidden" name="action" value="matricular_aluno_com_plano">
                <input type="hidden" name="turma_id" value="<?= $turma_id ?>">

                <div>
                    <label class="label"><span class="label-text">1. Selecione o Aluno</span></label>
                    <select name="aluno_id" class="select2-aluno w-full" required></select>
                </div>
                <div>
                    <label class="label"><span class="label-text">2. Defina o Plano Inicial</span></label>
                    <select name="plano_inicial" class="select select-bordered w-full">
                        <option value="mensal" selected>Mensal (Gera 1 cobrança)</option>
                        <option value="trimestral">Trimestral (Gera 3 cobranças)</option>
                        <option value="semestral">Semestral (Gera 6 cobranças)</option>
                    </select>
                </div>
                <div>
                    <label class="label">
                        <span class="label-text">3. Início da Competência</span>
                        <span class="label-text-alt">Mude apenas para matrículas antigas</span>
                    </label>
                    <select name="data_inicio_competencia" class="select select-bordered w-full">
                        <?php
                        // Cria um seletor de meses, desde 6 meses atrás até 3 meses no futuro.
                        $data_atual = new DateTime();
                        $data_atual->modify('first day of this month');

                        // Mapeia os meses para português
                        $meses_pt = [
                            '01' => 'Janeiro',
                            '02' => 'Fevereiro',
                            '03' => 'Março',
                            '04' => 'Abril',
                            '05' => 'Maio',
                            '06' => 'Junho',
                            '07' => 'Julho',
                            '08' => 'Agosto',
                            '09' => 'Setembro',
                            '10' => 'Outubro',
                            '11' => 'Novembro',
                            '12' => 'Dezembro'
                        ];

                        // Gera as opções do select
                        for ($i = -6; $i <= 3; $i++) {
                            $data_opcao = clone $data_atual;
                            $data_opcao->modify("$i month");

                            $valor = $data_opcao->format('Y-m');
                            $mes_num = $data_opcao->format('m');
                            $ano = $data_opcao->format('Y');
                            $texto = $meses_pt[$mes_num] . ' / ' . $ano;

                            // Deixa o mês atual pré-selecionado
                            $selecionado = ($i == 0) ? 'selected' : '';

                            echo "<option value=\"$valor\" $selecionado>$texto</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn" onclick="modalMatricular.close()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Matrícula</button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog id="modalRemover" class="modal">
    </dialog>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Correções para o Select2 funcionar dentro do modal
            $(document).on('focusin', function(e) {
                if ($(e.target).closest(".select2-container").length) e.stopImmediatePropagation();
            });

            $('.select2-aluno').select2({
                dropdownParent: $('#modalMatricular'),
                placeholder: 'Busque por nome, apelido ou CPF',
                ajax: {
                    url: 'controller-usuario/ajax-jogadores.php',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    id: item.identificador,
                                    text: item.nome_completo
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
        });

        function confirmarRemocao(matriculaId, nomeAluno) {
            // ... (código da função de confirmação mantido como está) ...
        }
    </script>
</body>

</html>