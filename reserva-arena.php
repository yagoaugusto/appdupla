<?php
// Inclui o arquivo de configuração global (onde a conexão com o banco está definida)
require_once '#_global.php';

// Recebendo data e arena via GET
$data = $_GET['data'] ?? date('Y-m-d');
$arena_id = isset($_GET['arena']) ? $_GET['arena'] : null;
$arena_nome = isset($_GET['arena_nome']) ? $_GET['arena_nome'] : 'Arena';

// Validação básica
if (!$arena_id || !is_numeric($arena_id)) {
    // Redirecionar ou mostrar uma mensagem de erro se o ID da arena for inválido
    echo "<p class='text-red-500'>ID de arena inválido.</p>";
    exit;
}

// Recupera os dados da arena do banco de dados
$arena = Arena::getArenaById($arena_id);  // Assumindo que você tem um método estático 'getArena' na classe Arena

if (!$arena) {
    echo "<p class='text-red-500'>Arena não encontrada.</p>";
    exit;
}

//  Recuperar as quadras da arena
$quadras = Quadras::getQuadrasPorArena($arena_id);

// Buscar slots disponíveis e reservados para cada quadra
$quadras_info = [];
if ($quadras) {
    foreach ($quadras as $quadra) {
        $slots_disponiveis = Quadras::getSlotsDisponiveis($quadra['id'], $data);
        // Supondo que você tenha um método para buscar slots reservados
        $slots_reservados = Quadras::getSlotsReservados($quadra['id'], $data);

        $quadras_info[] = [
            'id' => $quadra['id'],
            'nome' => $quadra['nome'],
            'preco' => $quadra['valor_base'] ?? 0,
            'disponiveis' => count($slots_disponiveis),
            'reservados' => $slots_reservados,
            'horarios' => $slots_disponiveis // Opcional: se precisar listar os horários no collapse
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once '_head.php'; ?>
    <!-- Adicione a folha de estilo do Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>

<body class="bg-gray-100 min-h-screen text-gray-800 font-sans">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php' ?>

    <div class="flex pt-16">

        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php' ?>

        <main class="flex-1 py-8 px-4 items-center">
            <!-- Barra de Ações Fixa no Topo -->
            <div id="floatingActionBar" class="sticky top-16 z-30 bg-gray-800 text-white shadow-lg p-3 mb-6 hidden rounded-lg border border-gray-700">
                <div class="max-w-4xl mx-auto flex justify-between items-center">
                    <div class="flex items-center gap-4">
                        <span id="selectedCount" class="font-bold"></span>
                        <span id="totalPrice" class="font-semibold text-green-400"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="limparHorarios()" class="btn btn-ghost btn-sm hover:bg-gray-700">Limpar</button>
                        <button onclick="confirmarReserva()" class="btn btn-success btn-sm">Agendar</button>
                    </div>
                </div>
            </div>
            
            <section class="max-w-4xl mx-auto w-full md:w-11/12 lg:w-4/5 bg-white/95 rounded-2xl shadow-xl border border-blue-200 mt-4 mb-6 px-4 py-6 flex flex-col backdrop-blur-md">

                <!-- Título da Página -->
                <div class="w-full text-center mb-8">
                    <!-- Ícone -->
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-blue-100 to-pink-100 mb-4 shadow-md">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-pink-500 to-red-600 mb-2 tracking-tight drop-shadow-lg">
                        Reserve sua quadra na <?= htmlspecialchars($arena['titulo']) ?>
                    </h2>
                    <!-- Destaque da Data -->
                    <p class="text-lg font-semibold text-blue-600 bg-blue-100 inline-block px-4 py-1 rounded-full mb-3">
                        🗓️ <?= date('d/m/Y', strtotime($data)) ?>
                    </p>
                    <!-- Subtítulo -->
                    <p class="text-sm sm:text-base text-gray-600 font-medium">
                        Seu próximo play vai ser divertido!
                    </p>
                </div>

                <!-- Novo seletor de data interativo -->
                <button onclick="openModal()" class="w-full bg-white p-4 rounded-lg shadow-md border border-gray-200 hover:bg-gray-50 transition-colors flex items-center justify-between text-left mb-6">
                    <div>
                        <span class="text-xs text-gray-500">Data selecionada</span>
                        <p class="text-lg font-bold text-blue-600"><?= date('d/m/Y', strtotime($data)) ?></p>
                    </div>
                    <div class="flex items-center gap-2 text-blue-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span class="text-sm font-semibold">Alterar</span>
                    </div>
                </button>

                <!-- Modal de seleção de data (agora neste arquivo) -->
                <div id="dateModal" class="fixed z-50 inset-0 overflow-y-auto hidden">
                    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeModal()">
                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>
                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Selecionar Nova Data</h3>
                                        <div class="mt-2"><input type="date" id="modalDate" name="modalDate" class="input input-bordered w-full"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" onclick="selectDate()" class="btn btn-primary w-full sm:w-auto">Selecionar</button>
                                <button type="button" onclick="closeModal()" class="btn btn-ghost mt-3 w-full sm:mt-0 sm:w-auto">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($quadras_info)): ?>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="text-3xl">🕒</span>
                        Escolha sua Quadra e Horário
                    </h2>
                    <div class="space-y-4">
                        <?php foreach ($quadras_info as $quadra): ?>
                            <div class="collapse collapse-arrow bg-gray-50 rounded-lg border border-gray-200">
                                <input type="checkbox" class="peer" />
                                <div class="collapse-title text-lg font-semibold text-gray-800 flex items-center justify-between">
                                    <span><?= htmlspecialchars($quadra['nome']) ?></span>
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="badge badge-success badge-outline"><?= $quadra['disponiveis'] ?> disponíveis</span>
                                        <span class="badge badge-error badge-outline"><?= count($quadra['reservados']) ?> reservados</span>
                                    </div>
                                </div>
                                <div class="collapse-content bg-white">
                                <?php if (!empty($quadra['horarios'])): ?>
                                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3 p-4">
                                        <?php
                                        $todos_horarios = array_unique(array_merge($quadra['horarios'], $quadra['reservados']));
                                        sort($todos_horarios);
                                        ?>
                                        <?php foreach ($todos_horarios as $horario): ?>
                                            <?php
                                            $is_reservado = in_array($horario, $quadra['reservados']);
                                            $button_classes = $is_reservado
                                                ? 'bg-gray-200 text-gray-400 line-through cursor-not-allowed'
                                                : 'slot-disponivel bg-green-100 text-green-800 hover:bg-green-200 cursor-pointer';
                                            $diaSemanaIngles = date('l', strtotime($data));
                                            $dias_semana_map = [
                                                'Monday' => 'segunda',
                                                'Tuesday' => 'terca',
                                                'Wednesday' => 'quarta',
                                                'Thursday' => 'quinta',
                                                'Friday' => 'sexta',
                                                'Saturday' => 'sabado',
                                                'Sunday' => 'domingo'
                                            ];
                                            $diaSemanaPt = $dias_semana_map[$diaSemanaIngles] ?? null;

                                            $valorAdicional = Quadras::getValorAdicionalPorSlot($quadra['id'], $diaSemanaPt, $horario);
                                            $valorBase = floatval($quadra['preco'] ?? 0);
                                            $valorTotal = number_format($valorBase + $valorAdicional, 2, ',', '.');
                                            $horaFim = date('H:i', strtotime($horario . ' +1 hour'));
                                            ?>
                                            <button
                                                class="p-2 rounded-lg text-center font-semibold transition-all duration-200 flex flex-col items-center justify-center h-20 <?= $button_classes ?>"
                                                data-key="<?= $quadra['id'] ?>_<?= $horario ?>"
                                                data-quadra-nome="<?= htmlspecialchars($quadra['nome']) ?>"
                                                data-preco="<?= $valorBase + $valorAdicional ?>"
                                                onclick="<?= $is_reservado ? 'event.preventDefault();' : "toggleHorario(this)" ?>"
                                                <?= $is_reservado ? 'disabled' : '' ?>>
                                                <span class="text-sm font-bold"><?= $horario ?> - <?= $horaFim ?></span>
                                                <span class="text-xs font-normal mt-1">R$ <?= $valorTotal ?></span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-gray-500 p-4">Nenhum horário disponível.</p>
                                <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">Nenhuma quadra encontrada para esta arena.</p>
                <?php endif; ?>
            </section> <br><br>
        </main>
    </div>

    <script>
        // Função para simular a reserva (substitua pela sua lógica real)
        function reservar(quadra_id, horario) {
            alert(`Horário ${horario} reservado para a quadra ${quadra_id} em <?php echo date('d/m/Y', strtotime($data)); ?>! (Simulação)`);
            // Aqui você faria a chamada para o banco de dados ou API para registrar a reserva.
        }

        function openModal() {
            document.getElementById('dateModal').classList.remove('hidden');
            // Opcional: definir a data atual no input do modal 
            // document.getElementById('modalDate').valueAsDate = new Date('<?php //echo $data; 
                                                                            ?>'); 
        }

        function closeModal() {
            document.getElementById('dateModal').classList.add('hidden');
        }

        function selectDate() {
            const selectedDate = document.getElementById('modalDate').value;
            if (selectedDate) {
                window.location.href = `reserva-arena.php?data=${selectedDate}&arena=<?= $arena_id ?>&arena_nome=<?= urlencode($arena_nome) ?>`;
            } else {
                alert("Por favor, selecione uma data.");
            }
        }

        const horariosSelecionados = new Map(); // Usando um Map para facilitar o acesso e a remoção

        function toggleHorario(button) {
            const key = button.dataset.key;
            const preco = parseFloat(button.dataset.preco);

            if (horariosSelecionados.has(key)) {
                horariosSelecionados.delete(key);
                button.classList.remove('slot-selecionado', 'bg-blue-600', 'text-white');
                button.classList.add('slot-disponivel', 'bg-green-100', 'text-green-800');
            } else {
                horariosSelecionados.set(key, { preco });
                button.classList.remove('slot-disponivel', 'bg-green-100', 'text-green-800');
                button.classList.add('slot-selecionado', 'bg-blue-600', 'text-white');
            }

            updateResumoHorarios();
        }

        function updateResumoHorarios() {
            const bar = document.getElementById('floatingActionBar');
            const countSpan = document.getElementById('selectedCount');
            const priceSpan = document.getElementById('totalPrice');
            const count = horariosSelecionados.size;

            if (count === 0) {
                bar.classList.add('hidden');
                return;
            }

            bar.classList.remove('hidden');
            countSpan.textContent = `${count} horário${count > 1 ? 's' : ''} selecionado${count > 1 ? 's' : ''}`;

            let total = 0;
            for (let item of horariosSelecionados.values()) {
                total += item.preco;
            }
            priceSpan.textContent = `Total: R$ ${total.toFixed(2).replace('.', ',')}`;
        }

        function limparHorarios() {
            document.querySelectorAll('.slot-selecionado').forEach(button => {
                button.classList.remove('slot-selecionado', 'bg-blue-600', 'text-white');
                button.classList.add('slot-disponivel', 'bg-green-100', 'text-green-800');
            });
            horariosSelecionados.clear();
            updateResumoHorarios();
        }

        function confirmarReserva() {
            if (horariosSelecionados.size === 0) {
                alert("Nenhum horário selecionado.");
                return;
            }

            const detalhes = Array.from(horariosSelecionados.keys()).join(', ');
            alert("Reserva confirmada para: " + detalhes);
            // Aqui você pode redirecionar ou enviar via AJAX para a API
        }
    </script>
    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>
</body>

</html>