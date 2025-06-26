<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<?php
// Verifica se o quadra_id foi passado na URL
$quadra_id = $_GET['quadra_id'] ?? null;

?>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php' ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php' ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-4 sm:p-6">
            <section class="max-w-7xl mx-auto w-full">
                <?php if ($quadra_id): ?>
                    <?php
                    // Busca informações da quadra
                    $quadra_info = Quadras::getQuadraById($quadra_id);
                    if (!$quadra_info) {
                        $_SESSION['mensagem'] = ['error', 'Quadra não encontrada.'];
                        header('Location: funcionamento-quadra.php'); // Volta para a seleção
                        exit;
                    }
                    // Busca os horários de funcionamento existentes para esta quadra
                    $horarios_existentes = Quadras::getFuncionamentoQuadra($quadra_id);
                    $slots_existentes = [];
                    foreach ($horarios_existentes as $slot) {
                        $slots_existentes[$slot['dia_semana'] . '_' . substr($slot['hora_inicio'], 0, 5)] = $slot;
                    }
                    // Define os dias da semana aqui para que estejam disponíveis antes do thead
                    $dias_semana = [
                        'segunda' => 'Seg',
                        'terca' => 'Ter',
                        'quarta' => 'Qua',
                        'quinta' => 'Qui',
                        'sexta' => 'Sex',
                        'sabado' => 'Sáb',
                        'domingo' => 'Dom'];
                    ?>
                    <!-- Cabeçalho da Página -->
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">
                            Horários de Funcionamento
                        </h1>
                        <a href="funcionamento-quadra.php" class="btn btn-sm btn-outline">&larr; Voltar para seleção</a>
                    </div>

                    <?php
                    // Exibir mensagens de sucesso ou erro da sessão
                    if (isset($_SESSION['mensagem'])) {
                        $tipo = $_SESSION['mensagem'][0];
                        $texto = $_SESSION['mensagem'][1];
                        $alert_class = ($tipo === 'success') ? 'alert-success' : 'alert-error';
                        echo "<div class='alert {$alert_class} shadow-lg mb-5'><div><span>" . htmlspecialchars($texto) . "</span></div></div>";
                        unset($_SESSION['mensagem']); // Limpa a mensagem após exibir
                    }
                    ?>

                    <div class="bg-white rounded-xl shadow p-4 mb-6 border border-gray-200">
                        <h2 class="text-xl font-bold text-gray-700 mb-3">
                            Quadra: <span class="text-blue-600"><?= htmlspecialchars($quadra_info['nome']) ?></span>
                        </h2>
                        <p class="text-gray-600 text-sm mb-4">
                            Selecione os horários em que esta quadra estará disponível para agendamento.
                            Cada seleção representa um slot de 1 hora.
                        </p>

                        <form method="POST" action="controller-quadra/funcionamento-quadra.php" onsubmit="return prepararEnvio()" class="space-y-4">
                            <input type="hidden" name="quadra_id" value="<?= htmlspecialchars($quadra_id) ?>">
                            <input type="hidden" name="horarios" id="horariosInput">

                            <div class="overflow-x-auto w-full">
                                <table class="table table-zebra w-full text-center">
                                    <thead>
                                        <tr>
                                            <th class="w-20">Horário</th>
                                            <th>Seg</th>
                                            <th>Ter</th>
                                            <th>Qua</th>
                                            <th>Qui</th>
                                            <th>Sex</th>
                                            <th>Sáb</th>
                                            <th>Dom</th>
                                        </tr>
                                        <tr>
                                            <th></th> <!-- Empty for "Horário" column -->
                                            <?php foreach (array_keys($dias_semana) as $dia_key): ?>
                                                <th class="text-center">
                                                    <button type="button" class="btn btn-xs btn-outline btn-error clear-day-btn" data-day="<?= $dia_key ?>">
                                                        Limpar
                                                    </button>
                                                </th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $dias_semana = [
                                            'segunda' => 'Seg',
                                            'terca' => 'Ter',
                                            'quarta' => 'Qua',
                                            'quinta' => 'Qui',
                                            'sexta' => 'Sex',
                                            'sabado' => 'Sáb',
                                            'domingo' => 'Dom'
                                        ];
                                        for ($h = 6; $h < 23; $h++) { // Horários de 06:00 a 22:00
                                            $inicio = sprintf("%02d:00", $h);
                                            $fim = sprintf("%02d:00", $h + 1);
                                            echo "<tr>";
                                            echo "<th class='text-xs sm:text-sm font-semibold text-gray-700'>{$inicio} - {$fim}</th>";
                                            foreach (array_keys($dias_semana) as $dia_key) {
                                                $id = "{$dia_key}_{$inicio}";
                                                $slot_existente = $slots_existentes[$dia_key . '_' . $inicio] ?? null;
                                                $is_checked = $slot_existente ? 'checked' : '';
                                                $valor_adicional = $slot_existente ? (float)$slot_existente['valor_adicional'] : 0;
                                                echo "<td class='p-1'>";
                                                echo "<div class='flex flex-col items-center justify-center'>";
                                                echo "<label class='cursor-pointer'><input type='checkbox' class='checkbox checkbox-xs sm:checkbox-sm checkbox-primary func-checkbox' id='{$id}' data-dia='{$dia_key}' data-inicio='{$inicio}' data-fim='{$fim}' data-valor-adicional='{$valor_adicional}' {$is_checked}></label>";
                                                // Span para exibir o valor adicional
                                                echo "<span id='valor-{$id}' class='text-xs text-green-600 font-mono mt-1 " . ($valor_adicional > 0 ? '' : 'hidden') . "'>+R$" . number_format($valor_adicional, 2, ',', '.') . "</span>";
                                                echo "</div>";
                                                echo "</td>";
                                            }
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button type="submit" class="btn btn-primary">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                    </svg>
                                    Salvar Horários
                                </button>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <?php
                    // Se nenhum quadra_id for fornecido, exibe a tela de seleção.
                    $usuario_id = $_SESSION['DuplaUserId'];
                    $arenas = Quadras::getArenasDoGestor($usuario_id);
                    ?>
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">
                            Configurar Funcionamento
                        </h1>
                    </div>

                    <div class="bg-white rounded-xl shadow p-4 mb-6 border border-gray-200">
                        <h2 class="text-xl font-bold text-gray-700 mb-3">Selecione a Quadra</h2>
                        <p class="text-gray-600 text-sm mb-4">
                            Escolha uma de suas quadras abaixo para definir os horários de funcionamento.
                        </p>

                        <?php if (empty($arenas)): ?>
                            <p class="text-gray-500 italic">Você não gerencia nenhuma arena. <a href="criar-arena.php" class="link link-primary">Crie uma arena</a> e adicione quadras primeiro.</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($arenas as $arena): ?>
                                    <details class="collapse collapse-arrow bg-gray-50 border border-gray-200 rounded-lg">
                                        <summary class="collapse-title text-lg font-medium">
                                            <span class="text-2xl mr-2"><?= htmlspecialchars($arena['bandeira']) ?></span>
                                            <?= htmlspecialchars($arena['titulo']) ?>
                                        </summary>
                                        <div class="collapse-content">
                                            <?php $quadras = Quadras::getQuadrasPorArena($arena['id']); ?>
                                            <?php if (empty($quadras)): ?>
                                                <p class="text-sm text-gray-500 italic px-4 py-2">Nenhuma quadra encontrada nesta arena. <a href="criar-quadra.php" class="link link-primary">Adicionar quadra</a>.</p>
                                            <?php else: ?>
                                                <ul class="menu bg-base-100 p-2 rounded-box">
                                                    <?php foreach ($quadras as $quadra): ?>
                                                        <li>
                                                            <a href="funcionamento-quadra.php?quadra_id=<?= $quadra['id'] ?>" class="flex justify-between items-center">
                                                                <span><?= htmlspecialchars($quadra['nome']) ?></span>
                                                                <span class="text-xs text-gray-400">Configurar &rarr;</span>
                                                            </a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </div>
                                    </details>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

    

    <!-- Nova seção para configurar preços especiais -->
    <div class="bg-white rounded-xl shadow p-4 mb-6 border border-gray-200">
        <h2 class="text-xl font-bold text-gray-700 mb-3">Configurar Preços Especiais</h2>
        <p class="text-gray-600 text-sm mb-4">
            Defina um valor adicional para horários específicos. Este valor será somado ao valor base da quadra.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-control">
                <label class="label"><span class="label-text">Dias da Semana</span></label>
                <select id="specialPriceDays" multiple class="select select-bordered w-full">
                    <option value="segunda">Segunda-feira</option>
                    <option value="terca">Terça-feira</option>
                    <option value="quarta">Quarta-feira</option>
                    <option value="quinta">Quinta-feira</option>
                    <option value="sexta">Sexta-feira</option>
                    <option value="sabado">Sábado</option>
                    <option value="domingo">Domingo</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label"><span class="label-text">Hora Início</span></label>
                    <select id="specialPriceStart" class="select select-bordered w-full">
                        <?php for ($h = 6; $h < 23; $h++): ?>
                            <option value="<?= sprintf("%02d:00", $h) ?>"><?= sprintf("%02d:00", $h) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Hora Fim</span></label>
                    <select id="specialPriceEnd" class="select select-bordered w-full">
                        <?php for ($h = 7; $h < 24; $h++): ?>
                            <option value="<?= sprintf("%02d:00", $h) ?>"><?= sprintf("%02d:00", $h) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-control mt-4">
            <label class="label"><span class="label-text">Valor Adicional (R$)</span></label>
            <input type="text" id="specialPriceValue" placeholder="Ex: 10,00" class="input input-bordered w-full">
        </div>
        <div class="mt-6 flex justify-end">
            <button type="button" id="applySpecialPrice" class="btn btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Aplicar Valor Adicional
            </button>
        </div>
        </form>
        </section>

        <br><br><br>
        </main>
    </div>

    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>

    <script>
        function prepararEnvio() {
            const selecionados = [];
            document.querySelectorAll('.func-checkbox:checked').forEach(input => {
                selecionados.push({
                    dia: input.dataset.dia,
                    inicio: input.dataset.inicio,
                    fim: input.dataset.fim,
                    valor_adicional: parseFloat(input.dataset.valorAdicional) || 0 // Pega o valor adicional
                });
            });
            document.getElementById('horariosInput').value = JSON.stringify(selecionados);
            return true;
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const applyButton = document.getElementById('applySpecialPrice');
            const specialPriceDays = document.getElementById('specialPriceDays');
            const specialPriceStart = document.getElementById('specialPriceStart');
            const specialPriceEnd = document.getElementById('specialPriceEnd');
            const specialPriceValueInput = document.getElementById('specialPriceValue');

            // Aplica máscara de moeda ao campo de valor adicional
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.mask !== 'undefined') {
                $('#specialPriceValue').mask('000.000.000.000.000,00', {
                    reverse: true
                });
            }

            applyButton.addEventListener('click', () => {
                const selectedDays = Array.from(specialPriceDays.options)
                    .filter(option => option.selected)
                    .map(option => option.value);
                const startTime = specialPriceStart.value;
                const endTime = specialPriceEnd.value;
                let additionalValue = specialPriceValueInput.value;

                if (selectedDays.length === 0) {
                    alert('Por favor, selecione pelo menos um dia da semana.');
                    return;
                }
                if (!startTime || !endTime) {
                    alert('Por favor, selecione o intervalo de horários.');
                    return;
                }
                if (parseInt(startTime.split(':')[0]) >= parseInt(endTime.split(':')[0])) {
                    alert('A hora de início deve ser anterior à hora de fim.');
                    return;
                }

                // Converte para float para armazenamento e comparação
                additionalValue = parseFloat(additionalValue.replace('.', '').replace(',', '.')) || 0;

                // Itera sobre todos os checkboxes e aplica o valor
                document.querySelectorAll('.func-checkbox').forEach(input => {
                    const dia = input.dataset.dia;
                    const inicio = input.dataset.inicio;
                    const horaInicioNum = parseInt(inicio.split(':')[0]);
                    const valorSpan = document.getElementById(`valor-${input.id}`);

                    if (selectedDays.includes(dia) && horaInicioNum >= parseInt(startTime.split(':')[0]) && horaInicioNum < parseInt(endTime.split(':')[0])) {
                        input.dataset.valorAdicional = additionalValue;
                        // Atualiza o span com o valor adicional
                        if (additionalValue > 0 && valorSpan) {
                            valorSpan.textContent = `+R$${additionalValue.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`;
                            valorSpan.classList.remove('hidden');
                        } else if (valorSpan) {
                            valorSpan.classList.add('hidden');
                        }
                    }
                });

                alert('Valor adicional aplicado aos horários selecionados!');
            });

            // Função para limpar horários de um dia específico
            document.querySelectorAll('.clear-day-btn').forEach(button => {
                button.addEventListener('click', (event) => {
                    const dayToClear = event.target.dataset.day;
                    document.querySelectorAll(`.func-checkbox[data-dia="${dayToClear}"]`).forEach(input => {
                        input.checked = false;
                        input.dataset.valorAdicional = 0; // Reset additional value
                        const valorSpan = document.getElementById(`valor-${input.id}`);
                        if (valorSpan) {
                            valorSpan.classList.add('hidden'); // Hide the value span
                        }
                    });
                });
            });
        });
    </script>

</body>

</html>