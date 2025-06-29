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

        <div id="horariosSelecionados" class="fixed top-16 left-1/2 transform -translate-x-1/2 bg-white border border-blue-200 rounded-xl shadow-lg p-3 text-sm text-center z-50 hidden">
            <span class="font-semibold text-gray-800">Horários Selecionados:</span>
            <div id="listaHorarios" class="mt-2 text-blue-600 font-mono"></div>
            <div class="mt-3 flex justify-center gap-3">
                <button onclick="limparHorarios()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-1 px-3 rounded">
                    Limpar Seleção
                </button>
                <button onclick="confirmarReserva()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 px-3 rounded">
                    Confirmar Reserva
                </button>
            </div>
        </div>

        <main class="flex-1 py-8 px-4 items-center">
            <section class="max-w-4xl mx-auto w-full md:w-11/12 lg:w-4/5 bg-white/95 rounded-2xl shadow-xl border border-blue-200 mt-4 mb-6 px-4 py-6 flex flex-col backdrop-blur-md">

                <!-- Título da Página -->
                <div class="w-full text-center mb-8">
                    <!-- Ícone -->
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-blue-100 to-pink-100 mb-4 shadow-md">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <!-- Título Principal -->
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-pink-500 to-red-600 mb-2 tracking-tight drop-shadow-lg">
                        Reserve sua quadra na <?= htmlspecialchars($arena['titulo']) ?>
                    </h1>
                    <!-- Subtítulo -->
                    <p class="text-sm sm:text-base text-gray-600 font-medium">
                        Seu próximo play está mais perto do que você imagina. Escolha seu horário e garanta a diversão!
                    </p>
                </div>

                <div class="bg-gray-50 p-4 rounded-md mb-6">
                    <p class="text-gray-700">
                        <span class="font-semibold text-lg"><?= htmlspecialchars($arena['titulo']) ?></span> -
                        <span class="text-blue-500 font-medium"><?= date('d/m/Y', strtotime($data)) ?></span>
                    </p>
                </div>

                <!-- Botão "Ver Horários" (agora abre o modal) -->
                <button onclick="openModal()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-6">
                    Verificar Horários
                </button>

                <!-- Modal para selecionar a data -->
                <?php include '_modal_selecionar_data.php'; ?>

                <?php if (!empty($quadras_info)): ?>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Horários por Quadra</h2>
                    <?php foreach ($quadras_info as $quadra): ?>
                        <div class="collapse collapse-arrow bg-white rounded-lg shadow mb-4">
                            <input type="checkbox" class="peer" />
                            <div class="collapse-title text-xl font-medium text-gray-700 hover:bg-gray-50 p-4">
                                <div class="mb-2">
                                    <?= htmlspecialchars($quadra['nome']) ?>
                                </div>
                                <div class="flex flex-wrap gap-1">
                                    <?php for ($i = 0; $i < $quadra['disponiveis']; $i++): ?>
                                        <span class="h-4 w-4 rounded-full bg-green-400"></span>
                                    <?php endfor; ?>
                                    <?php for ($i = 0; $i < count($quadra['reservados']); $i++): ?>
                                        <span class="h-4 w-4 rounded-full bg-red-400"></span>
                                    <?php endfor; ?>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">
                                    (<?= $quadra['disponiveis'] ?> disponíveis, <?= count($quadra['reservados']) ?> reservados)
                                </div>
                            </div>
                            <div class="collapse-content">
                                <?php if (!empty($quadra['horarios'])): ?>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 p-4">
                                        <?php
                                        $todos_horarios = array_unique(array_merge($quadra['horarios'], $quadra['reservados']));
                                        sort($todos_horarios);
                                        ?>
                                        <?php foreach ($todos_horarios as $horario): ?>
                                            <?php
                                            $isReservado = in_array($horario, $quadra['reservados']);
                                            $bgColor = $isReservado ? 'bg-gray-400' : 'bg-green-500 hover:bg-green-700';
                                            $horaFim = date('H:i', strtotime($horario) + 3600);
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
                                            ?>
                                            <button
                                                class="<?= $bgColor ?> text-white font-bold py-2 px-3 rounded text-sm <?= $isReservado ? 'cursor-not-allowed opacity-70' : '' ?>"
                                                data-key="<?= $quadra['id'] ?>_<?= $horario ?>"
                                                data-quadra-nome="<?= htmlspecialchars($quadra['nome']) ?>"
                                                data-preco="<?= $valorBase + $valorAdicional ?>"
                                                onclick="<?= $isReservado ? 'event.preventDefault();' : "toggleHorario('{$quadra['id']}', '{$horario}')" ?>"
                                                <?= $isReservado ? 'disabled' : '' ?>>
                                                <?= $horario ?> às <?= $horaFim ?><br><span class="text-xs font-normal">R$ <?= $valorTotal ?></span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-gray-500 p-4">Nenhum horário disponível.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
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

        const horariosSelecionados = [];

        function toggleHorario(quadraId, horario) {
            const button = document.querySelector(`button[data-key="${quadraId}_${horario}"]`);
            const quadraNome = button.dataset.quadraNome;
            const preco = parseFloat(button.dataset.preco);
            const key = `${quadraId}_${horario}`;
            const displayKey = `${quadraNome}_${horario}`;
            const index = horariosSelecionados.findIndex(h => h.key === displayKey);

            if (index > -1) {
                horariosSelecionados.splice(index, 1);
                button?.classList.remove('bg-blue-700');
                button?.classList.add('bg-green-500');
            } else {
                horariosSelecionados.push({
                    key: displayKey,
                    preco
                });
                button?.classList.remove('bg-green-500');
                button?.classList.add('bg-blue-700');
            }

            updateResumoHorarios();
        }

        function updateResumoHorarios() {
            const container = document.getElementById('horariosSelecionados');
            const lista = document.getElementById('listaHorarios');

            if (horariosSelecionados.length === 0) {
                container.classList.add('hidden');
                lista.innerHTML = '';
                return;
            }

            container.classList.remove('hidden');
            const total = horariosSelecionados.reduce((sum, h) => sum + h.preco, 0);
            const items = horariosSelecionados.map(h => h.key.replace('_', ' às ')).join(', ');
            lista.innerHTML = `${items} <br><span class="inline-block mt-2 px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Total: R$ ${total.toFixed(2).replace('.', ',')}</span>`;
        }

        function limparHorarios() {
            horariosSelecionados.forEach(horarioObj => {
                const displayKey = horarioObj.key;
                const parts = displayKey.split('_');
                if (parts.length >= 2) {
                    const quadraNome = parts[0];
                    const horario = parts.slice(1).join('_');
                    const buttons = document.querySelectorAll(`button[data-quadra-nome="${quadraNome}"]`);
                    buttons.forEach(btn => {
                        if (btn.textContent.includes(horario)) {
                            if (!btn.disabled) {
                                btn.classList.remove('bg-blue-700');
                                btn.classList.add('bg-green-500');
                            }
                        }
                    });
                }
            });
            horariosSelecionados.length = 0;
            updateResumoHorarios();
        }

        function confirmarReserva() {
            if (horariosSelecionados.length === 0) {
                alert("Nenhum horário selecionado.");
                return;
            }

            const detalhes = horariosSelecionados.map(h => h.key.replace('_', ' às ')).join(', ');
            alert("Reserva confirmada para: " + detalhes);
            // Aqui você pode redirecionar ou enviar via AJAX para a API
        }
    </script>
    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>
</body>

</html>