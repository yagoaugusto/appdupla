<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<?php
require_once '_head.php';
// --- LÓGICA DA PÁGINA ---
if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) {
    // ... (redirecionamento de permissão)
}
$usuario_id = $_SESSION['DuplaUserId'];
$arenas_gestor = Quadras::getArenasDoGestor($usuario_id);
$arena_id_selecionada = filter_input(INPUT_GET, 'arena_id', FILTER_VALIDATE_INT);

$turmas = [];
$quadras_da_arena = [];
if ($arena_id_selecionada) {
    $turmas = Turma::getTurmasPorArena($arena_id_selecionada);
    // Precisamos de uma função para pegar as quadras da arena para o formulário
    $quadras_da_arena = Quadras::getQuadrasPorArena($arena_id_selecionada); // Supondo que esta função exista
}
?>

<style>
    /* --- Correção de Estilo (Aparência) --- */
    /* Deixa o campo Select2 com a mesma altura dos inputs do DaisyUI */
    .select2-selection--single {
        height: 3rem !important;
        /* 48px, altura padrão do input-bordered */
        border-radius: 0.5rem !important;
        /* 8px, borda padrão */
        border: 1px solid #e5e7eb !important;
        /* Cor da borda padrão */
    }

    /* Alinha o texto selecionado verticalmente */
    .select2-selection__rendered {
        line-height: 3rem !important;
        padding-left: 1rem !important;
    }

    /* Alinha a seta do dropdown verticalmente */
    .select2-selection__arrow {
        height: calc(3rem - 2px) !important;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


<body class="bg-gray-100 flex flex-col min-h-screen">
    <?php require_once '_nav_superior.php' ?>
    <div class="flex pt-16">
        <?php require_once '_nav_lateral.php' ?>
        <main class="flex-1 p-6">
            <section class="max-w-7xl mx-auto">
                <h1 class="text-3xl font-extrabold text-gray-800 mb-6">Gestão de Turmas</h1>

                <?php
                if (isset($_SESSION['mensagem'])) {
                    [$tipo, $texto] = $_SESSION['mensagem'];
                    // Adapta a cor do alerta com base no tipo da mensagem
                    $alert_class = ($tipo === 'success') ? 'alert-success' : 'alert-error';
                    echo "<div class='alert {$alert_class} shadow-lg mb-5'><div><svg xmlns='http://www.w3.org/2000/svg' class='stroke-current flex-shrink-0 h-6 w-6' fill='none' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg><span>" . htmlspecialchars($texto) . "</span></div></div>";
                    // Limpa a mensagem para não a mostrar novamente
                    unset($_SESSION['mensagem']);
                }
                ?>

                <div class="bg-white p-4 rounded-xl shadow-md border mb-6">
                    <form method="GET" action="turmas.php">
                        <label class="label"><span class="label-text">Selecione a Arena</span></label>
                        <select name="arena_id" class="select select-bordered" onchange="this.form.submit()">
                            <option value="">Escolha uma Arena</option>
                            <?php foreach ($arenas_gestor as $arena): ?>
                                <option value="<?= $arena['id'] ?>" <?= ($arena_id_selecionada == $arena['id']) ? 'selected' : '' ?>><?= htmlspecialchars($arena['titulo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <?php if ($arena_id_selecionada): ?>
                    <div class="flex justify-end mb-4">
                        <button class="btn btn-primary" onclick="modalTurma.showModal()">Criar Nova Turma</button>
                    </div>

                    <div class="overflow-x-auto bg-white rounded-xl shadow-lg border">
                        <table class="table w-full">
                            <thead>
                                <tr>
                                    <th>Turma</th>
                                    <th>Professor</th>
                                    <th>Vagas</th>
                                    <th>Mensalidade</th>
                                    <th>Horários</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($turmas as $turma): ?>
                                    <tr>
                                        <td>
                                            <div class="font-bold"><?= htmlspecialchars($turma['nome']) ?></div>
                                            <div class="text-sm opacity-60"><?= htmlspecialchars($turma['nivel']) ?></div>
                                        </td>
                                        <td><?= htmlspecialchars($turma['professor_nome']) ?></td>
                                        <td><?= $turma['alunos_ativos'] ?> / <?= $turma['vagas_total'] ?></td>
                                        <td>R$ <?= number_format($turma['valor_mensalidade'], 2, ',', '.') ?></td>
                                        <td>
                                            <?php foreach ($turma['horarios'] as $h): ?>
                                                <div class="badge badge-outline badge-sm mb-1">
                                                    <?= ucfirst($h['dia_semana']) ?> às <?= substr($h['hora_inicio'], 0, 5) ?> (<?= $h['quadra_nome'] ?>)
                                                </div>
                                            <?php endforeach; ?>
                                        </td>
                                        <td>
                                            <a href="turma_detalhes.php?id=<?= $turma['id'] ?>" class="btn btn-xs btn-outline">
                                                Gerir Alunos
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </section>
        </main>
    </div>

    <dialog id="modalTurma" class="modal">
        <div class="modal-box w-11/12 max-w-2xl">
            <h3 class="font-bold text-lg">Criar Nova Turma</h3>
            <form id="formTurma" method="POST" action="controllers/turma_controller.php" class="py-4 space-y-4">
                <input type="hidden" name="action" value="criar">
                <input type="hidden" name="arena_id" value="<?= $arena_id_selecionada ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control"><label class="label"><span class="label-text">Nome da Turma</span></label><input type="text" name="nome" class="input input-bordered w-full" required></div>
                    <div class="form-control"><label class="label"><span class="label-text">Nível</span></label><input type="text" name="nivel" class="input input-bordered w-full" placeholder="Ex: Iniciante, Mista B"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-control"><label class="label"><span class="label-text">Professor</span></label><select name="professor_id" class="input input-bordered select2-prof  w-full" required></select></div>
                    <div class="form-control"><label class="label"><span class="label-text">Vagas</span></label><input type="number" name="vagas_total" class="input input-bordered w-full" required></div>
                    <div class="form-control"><label class="label"><span class="label-text">Valor Mensalidade</span></label><input type="text" name="valor_mensalidade" id="valorMensalidade" class="input input-bordered w-full" required></div>
                </div>

                <div>
                    <h4 class="font-bold mt-6 mb-2">Horários da Turma</h4>
                    <div id="horariosContainer" class="space-y-2">
                    </div>
                    <button type="button" id="addHorarioBtn" class="btn btn-sm btn-outline btn-success mt-2">Adicionar Horário</button>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn" onclick="modalTurma.close()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Turma</button>
                </div>
            </form>
        </div>
    </dialog>

    <div id="horarioTemplate" class="hidden">
        <div class="grid grid-cols-12 gap-2 items-center horario-row">
            <div class="col-span-4"><select name="horarios[dia_semana][]" class="select select-bordered select-sm w-full">
                    <option>segunda</option>
                    <option>terca</option>
                    <option>quarta</option>
                    <option>quinta</option>
                    <option>sexta</option>
                    <option>sabado</option>
                    <option>domingo</option>
                </select></div>
            <div class="col-span-3"><select name="horarios[hora_inicio][]" class="select select-bordered select-sm w-full"><?php for ($h = 6; $h <= 22; $h++) {
                                                                                                                                echo "<option value='{$h}:00'>{$h}:00</option>";
                                                                                                                            } ?></select></div>
            <div class="col-span-4"><select name="horarios[quadra_id][]" class="select select-bordered select-sm w-full"><?php foreach ($quadras_da_arena as $q) {
                                                                                                                                echo "<option value='{$q['id']}'>{$q['nome']}</option>";
                                                                                                                            } ?></select></div>
            <div class="col-span-1"><button type="button" class="btn btn-xs btn-circle btn-error remove-horario-btn">✕</button></div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#valorMensalidade').mask('#.##0,00', {
                reverse: true
            });

            // =================================================================
            // CORREÇÃO ADICIONAL PARA O PROBLEMA DE CLIQUE/FOCO
            // Este código impede que o modal "brigue" com o Select2 pelo foco.
            $(document).on('focusin', function(e) {
                if ($(e.target).closest(".select2-container").length) {
                    e.stopImmediatePropagation();
                }
            });
            // =================================================================

            $('.select2-prof').select2({
                dropdownParent: $('#modalTurma'),
                placeholder: 'Busque por nome ou apelido',
                language: "pt-BR",
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
                                    text: item.nome_completo + ' (' + (item.apelido || 'N/A') + ')'
                                };
                            })
                        };
                    },
                    cache: true
                }
            });

            // Adicionar novo horário
            $('#addHorarioBtn').on('click', function() {
                var newHorario = $('#horarioTemplate').contents().clone();
                $('#horariosContainer').append(newHorario);
            });

            // Remover horário
            $('#horariosContainer').on('click', '.remove-horario-btn', function() {
                $(this).closest('.horario-row').remove();
            });
        });
    </script>


</body>

</html>