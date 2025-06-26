<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php
require_once '_head.php';

// --- LÓGICA DA PÁGINA ---

$usuario_id = $_SESSION['DuplaUserId'];
$arenas = Quadras::getArenasDoGestor($usuario_id);

// Filtros e Navegação
$arena_id_selecionada = filter_input(INPUT_GET, 'arena_id', FILTER_VALIDATE_INT);
$quadra_id_selecionada = filter_input(INPUT_GET, 'quadra_id', FILTER_VALIDATE_INT);
$offset_semana = filter_input(INPUT_GET, 'semana', FILTER_VALIDATE_INT) ?? 0;

// Popula o dropdown de quadras se uma arena foi selecionada
$quadras = [];
if ($arena_id_selecionada) {
  $quadras = Quadras::getQuadrasPorArena($arena_id_selecionada);
}

// --- CÁLCULO DA SEMANA ATUAL ---
$hoje = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
if ($offset_semana !== 0) {
  $hoje->modify(($offset_semana > 0 ? '+' : '') . ($offset_semana * 7) . ' days');
}
$dia_da_semana_num = $hoje->format('N'); // 1 (Segunda) a 7 (Domingo)
$inicio_semana = clone $hoje;
$inicio_semana->modify('-' . ($dia_da_semana_num - 1) . ' days');

$dias_da_semana_obj = [];
for ($i = 0; $i < 7; $i++) {
  $dia = clone $inicio_semana;
  $dia->modify("+$i days");
  $dias_da_semana_obj[] = $dia;
}

// --- BUSCA DE DADOS (COM DADOS MOCK PARA EXEMPLO) ---
$grade_horarios = [];
if ($quadra_id_selecionada) {
  // 1. Buscar horários de funcionamento da quadra
  $horarios_funcionamento = Quadras::getFuncionamentoQuadra($quadra_id_selecionada);

  // 2. Buscar agendamentos existentes para a semana
  $agendamentos = Agendamento::getAgendamentosPorQuadraNoPeriodo(
    $quadra_id_selecionada,
    $dias_da_semana_obj[0]->format('Y-m-d'),
    $dias_da_semana_obj[6]->format('Y-m-d')
  );

  // Monta a grade final para a view
  $mapa_dias_semana = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
  for ($h = 6; $h < 23; $h++) {
    $hora_str = sprintf("%02d:00", $h);
    foreach ($mapa_dias_semana as $dia_key) {
      $grade_horarios[$hora_str][$dia_key] = ['status' => 'fechado']; // Default para fechado
    }
  }
  foreach ($horarios_funcionamento as $hf) {
    $hora = substr($hf['hora_inicio'], 0, 5);
    $dia = $hf['dia_semana'];
    if (isset($grade_horarios[$hora][$dia])) {
      $grade_horarios[$hora][$dia] = ['status' => 'disponivel'];
    }
  }
  foreach ($agendamentos as $ag) {
    $hora = substr($ag['hora_inicio'], 0, 5);
    $data_ag = new DateTime($ag['data']);
    // Ajusta o dia da semana para o índice do array (0=segunda, 6=domingo)
    // DateTime::format('N') retorna 1 para segunda, 7 para domingo.
    // Subtraímos 1 para alinhar com o array $mapa_dias_semana.
    $dia_semana_num = ($data_ag->format('N') == 7) ? 6 : ($data_ag->format('N') - 1);
    $dia_key = $mapa_dias_semana[$dia_semana_num];
    if (isset($grade_horarios[$hora][$dia_key])) {
      $grade_horarios[$hora][$dia_key] = ['status' => 'agendado', 'tipo' => $ag['status'], 'details' => $ag];
    }
  }
}

// Mapeamento de tipos de agendamento para estilos CSS
$estilos_tipo = [
  'reservado' => 'bg-blue-200 border-blue-400 text-blue-800',
  'bloqueado' => 'bg-gray-300 border-gray-500 text-gray-800',
  'aula' => 'bg-yellow-200 border-yellow-400 text-yellow-800',
  'dayuse' => 'bg-purple-200 border-purple-400 text-purple-800',
  'manutencao' => 'bg-red-200 border-red-400 text-red-800',
];
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
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight mb-6">Agenda de Quadras</h1>

        <!-- Filtros -->
        <form method="GET" action="agendamento-quadra.php" class="bg-white p-4 rounded-xl shadow-md border border-gray-200 mb-6 grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
          <div class="form-control">
            <label class="label"><span class="label-text">Arena</span></label>
            <select name="arena_id" class="select select-bordered" onchange="this.form.submit()">
              <option value="">Selecione uma Arena</option>
              <?php foreach ($arenas as $arena): ?>
                <option value="<?= $arena['id'] ?>" <?= ($arena_id_selecionada == $arena['id']) ? 'selected' : '' ?>><?= htmlspecialchars($arena['titulo']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-control">
            <label class="label"><span class="label-text">Quadra</span></label>
            <select name="quadra_id" class="select select-bordered" onchange="this.form.submit()" <?= !$arena_id_selecionada ? 'disabled' : '' ?>>
              <option value="">Selecione uma Quadra</option>
              <?php foreach ($quadras as $quadra): ?>
                <option value="<?= $quadra['id'] ?>" <?= ($quadra_id_selecionada == $quadra['id']) ? 'selected' : '' ?>><?= htmlspecialchars($quadra['nome']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </form>

        <?php if ($quadra_id_selecionada): ?>
          <!-- Navegação da Semana e Legenda -->
          <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2">
              <a href="?arena_id=<?= $arena_id_selecionada ?>&quadra_id=<?= $quadra_id_selecionada ?>&semana=<?= $offset_semana - 1 ?>" class="btn btn-outline btn-sm">&larr; Anterior</a>
              <a href="?arena_id=<?= $arena_id_selecionada ?>&quadra_id=<?= $quadra_id_selecionada ?>&semana=0" class="btn btn-outline btn-sm">Hoje</a>
              <a href="?arena_id=<?= $arena_id_selecionada ?>&quadra_id=<?= $quadra_id_selecionada ?>&semana=<?= $offset_semana + 1 ?>" class="btn btn-outline btn-sm">Próxima &rarr;</a>
            </div>
            <div class="flex flex-wrap gap-2 text-xs">
              <?php foreach ($estilos_tipo as $tipo => $estilo): ?>
                <div class="flex items-center gap-1"><span class="w-3 h-3 rounded-full <?= explode(' ', $estilo)[0] ?>"></span><?= ucfirst($tipo) ?></div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Grade de Horários -->
          <div class="overflow-x-auto bg-white rounded-xl shadow-lg border border-gray-200">
            <table class="table table-fixed w-full text-center">
              <thead class="text-sm">
                <tr>
                  <th class="w-24">Horário</th>
                  <?php foreach ($dias_da_semana_obj as $dia): ?>
                    <th>
                      <?= ucfirst(strftime('%a', $dia->getTimestamp())) ?><br>
                      <span class="font-normal text-gray-500"><?= $dia->format('d/m') ?></span>
                    </th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php for ($h = 6; $h < 23; $h++):
                  $hora_atual = sprintf("%02d:00", $h);
                ?>
                  <tr>
                    <th class="text-xs sm:text-sm"><?= $hora_atual ?></th>
                    <?php foreach ($mapa_dias_semana as $i => $dia_key):
                      $slot = $grade_horarios[$hora_atual][$dia_key] ?? ['status' => 'fechado'];
                      $data_slot = $dias_da_semana_obj[$i]->format('Y-m-d');
                      $classes_slot = 'p-1 sm:p-2 border-t border-x text-xs h-20';
                      $conteudo_slot = '';

                      if ($slot['status'] === 'disponivel') {
                        $classes_slot .= ' bg-green-50 hover:bg-green-200 cursor-pointer transition-colors slot-disponivel select-none';
                        $conteudo_slot = '<span class="text-green-600 font-semibold">Disponível</span>';
                      } elseif ($slot['status'] === 'agendado') {
                        $tipo = $slot['tipo'];
                        $estilo = $estilos_tipo[$tipo] ?? 'bg-gray-200';
                        $classes_slot .= ' ' . $estilo . ' slot-agendado cursor-pointer';
                        $conteudo_slot = '<div class="font-bold">' . ucfirst($tipo) . '</div>';
                        $conteudo_slot .= '<div class="truncate text-xs">' . htmlspecialchars($slot['details']['cliente_nome'] ?? $slot['details']['observacoes'] ?? '') . '</div>';
                      } else { // Fechado
                        $classes_slot .= ' bg-gray-100';
                      }
                    ?>
                      <td class="<?= $classes_slot ?>" data-quadra-id="<?= $quadra_id_selecionada ?>" data-data="<?= $data_slot ?>" data-hora="<?= $hora_atual ?>"
                        data-agendamento-id="<?= $slot['details']['id'] ?? '' ?>">
                        <?= $conteudo_slot ?>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                <?php endfor; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="text-center bg-white p-8 rounded-xl shadow-md border border-gray-200">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Selecione uma arena e quadra</h3>
            <p class="mt-1 text-sm text-gray-500">Escolha uma arena e uma quadra nos filtros acima para visualizar a agenda.</p>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <!-- Barra Flutuante de Ações -->
  <div id="floatingActionBar" class="fixed bottom-4 left-1/2 -translate-x-1/2 z-20 hidden">
    <div class="bg-gray-800 text-white rounded-full shadow-lg p-2 flex items-center gap-4">
      <span id="selectedCount" class="font-bold text-sm px-3"></span>
      <button id="agendarSelecionadosBtn" class="btn btn-primary btn-sm rounded-full">Agendar Selecionados</button>
      <button id="limparSelecaoBtn" class="btn btn-ghost btn-sm rounded-full">Limpar</button>
    </div>
  </div>

  <!-- Modal de Agendamento -->
  <dialog id="modalAgendamento" class="modal">
    <div class="modal-box">
      <h3 id="modalTitle" class="font-bold text-lg">Novo Agendamento</h3>
      <form id="formAgendamento" method="POST" action="controller-agendamento/salvar-agendamento.php" class="py-4 space-y-4">
        <input type="hidden" name="arena_id" value="<?= htmlspecialchars($arena_id_selecionada) ?>">
        <input type="hidden" name="quadra_id_selecionada" value="<?= htmlspecialchars($quadra_id_selecionada) ?>">
        <input type="hidden" name="selected_slots" id="selectedSlotsInput">

        <div class="form-control">
          <label class="label"><span class="label-text">Tipo de Agendamento</span></label>
          <select name="tipo" class="select select-bordered" required>
            <option value="reservado">Reservado</option>
            <option value="aula">Aula</option>
            <option value="dayuse">Day Use</option>
            <option value="bloqueado">Bloqueio</option>
          </select>
        </div>
        <div class="form-control relative">
          <label class="label"><span class="label-text">Associar a um Usuário (Opcional)</span></label>
          <input type="text" id="searchUsuarioInput" placeholder="Busque por nome, apelido ou CPF" class="input input-bordered w-full">
          <input type="hidden" name="usuario_id" id="selectedUsuarioId">
          <div id="selectedUserNameDisplay" class="text-sm text-gray-600 mt-1"></div>
          <!-- Container para os resultados da busca, posicionado em relação a este div -->
          <div id="usuarioSearchResults" class="absolute top-full left-0 w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 max-h-48 overflow-y-auto hidden z-30">
            <!-- Resultados da busca serão inseridos aqui via JS -->
          </div>
        </div>
        <div class="form-control">
          <label class="label"><span class="label-text">Observações</span></label>
          <textarea name="observacoes" class="textarea textarea-bordered" placeholder="Ex: Pagamento pendente, evento especial, nome do cliente (se não for usuário), etc."></textarea>
        </div>
        <div class="modal-action">
          <button type="button" class="btn" onclick="modalAgendamento.close()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar Agendamentos</button>
        </div>
      </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
  </dialog>

  <!-- Modal de Visualização de Agendamento -->
  <dialog id="modalVerAgendamento" class="modal">
    <div class="modal-box">
      <h3 class="font-bold text-lg">Detalhes do Agendamento</h3>
      <div id="viewAgendamentoContent" class="py-4 space-y-2 text-sm">
        <!-- Conteúdo será injetado via JS -->
      </div>
      <div class="modal-action">
        <button type="button" class="btn btn-error">Cancelar Agendamento</button>
        <button type="button" class="btn" onclick="modalVerAgendamento.close()">Fechar</button>
      </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
  </dialog>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const agendaTableBody = document.querySelector('table tbody');
      const floatingBar = document.getElementById('floatingActionBar');
      const selectedCountSpan = document.getElementById('selectedCount');
      const agendarBtn = document.getElementById('agendarSelecionadosBtn');
      const limparBtn = document.getElementById('limparSelecaoBtn');
      const modalAgendamento = document.getElementById('modalAgendamento');
      const selectedSlotsInput = document.getElementById('selectedSlotsInput');
      const modalVerAgendamento = document.getElementById('modalVerAgendamento');
      const viewAgendamentoContent = document.getElementById('viewAgendamentoContent');

      let selectedSlots = new Set();

      function updateFloatingBar() {
        const count = selectedSlots.size;
        if (count > 0) {
          selectedCountSpan.textContent = `${count} horário${count > 1 ? 's' : ''} selecionado${count > 1 ? 's' : ''}`;
          floatingBar.classList.remove('hidden');
        } else {
          floatingBar.classList.add('hidden');
        }
      }

      function clearSelection() {
        document.querySelectorAll('.slot-selecionado').forEach(td => {
          td.classList.remove('slot-selecionado', 'bg-yellow-300', 'border-yellow-500');
        });
        selectedSlots.clear();
        updateFloatingBar();
      }

      agendaTableBody.addEventListener('click', (event) => {
        const td = event.target.closest('td');
        if (!td) return;

        if (td.classList.contains('slot-disponivel')) {
          const slotIdentifier = `${td.dataset.data}_${td.dataset.hora}`;
          if (selectedSlots.has(slotIdentifier)) {
            selectedSlots.delete(slotIdentifier);
            td.classList.remove('slot-selecionado', 'bg-yellow-300', 'border-yellow-500');
          } else {
            selectedSlots.add(slotIdentifier);
            td.classList.add('slot-selecionado', 'bg-yellow-300', 'border-yellow-500');
          }
          updateFloatingBar();
        } else if (td.classList.contains('slot-agendado')) {
          const agendamentoId = td.dataset.agendamentoId;
          if (agendamentoId) {
            // TODO: Fazer uma chamada AJAX para buscar os detalhes do agendamento
            // e popular o modal 'modalVerAgendamento'.
            viewAgendamentoContent.innerHTML = `<p>Carregando detalhes para o agendamento ID: ${agendamentoId}...</p>`;
            modalVerAgendamento.showModal();
          }
        }
      });

      limparBtn.addEventListener('click', clearSelection);

      agendarBtn.addEventListener('click', () => {
        if (selectedSlots.size === 0) {
          alert('Nenhum horário selecionado.');
          return;
        }
        const slotsData = Array.from(selectedSlots).map(id => {
          const [data, hora] = id.split('_');
          return {
            data,
            hora
          };
        });
        selectedSlotsInput.value = JSON.stringify(slotsData);
        modalAgendamento.showModal();
      });


     // --- Lógica de Busca de Usuários no Modal de Agendamento ---
      const searchUsuarioInput = document.getElementById('searchUsuarioInput');
      const selectedUsuarioIdInput = document.getElementById('selectedUsuarioId');
      const selectedUserNameDisplay = document.getElementById('selectedUserNameDisplay');
      const usuarioSearchResults = document.getElementById('usuarioSearchResults');
      let searchTimeout = null;

      searchUsuarioInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        const searchTerm = searchUsuarioInput.value.trim();

        if (searchTerm.length < 3) {
          usuarioSearchResults.classList.add('hidden');
          usuarioSearchResults.innerHTML = '';
          selectedUsuarioIdInput.value = ''; // Limpa o ID selecionado se a busca for apagada
          selectedUserNameDisplay.textContent = ''; // Limpa o nome exibido
          return;
        }

        searchTimeout = setTimeout(async () => {
          try {
            const response = await fetch(`controller-agendamento/buscar-usuarios.php?search_term=${encodeURIComponent(searchTerm)}`);
            const data = await response.json();

            usuarioSearchResults.innerHTML = ''; // Limpa resultados anteriores
            if (data.success && data.users.length > 0) {
              data.users.forEach(user => {
                const userDiv = document.createElement('div');
                userDiv.classList.add('p-2', 'cursor-pointer', 'hover:bg-gray-100', 'border-b', 'border-gray-100');

                const nameSpan = document.createElement('span');
                nameSpan.className = 'font-semibold';
                nameSpan.textContent = `${user.nome} ${user.apelido ? `(${user.apelido})` : ''}`;

                const infoSpan = document.createElement('span');
                infoSpan.className = 'text-xs text-gray-500 block';
                infoSpan.textContent = `${user.cpf ? `CPF: ${user.cpf}` : ''} ${user.telefone ? `Tel: ${user.telefone}` : ''}`;

                userDiv.appendChild(nameSpan);
                userDiv.appendChild(infoSpan);

                userDiv.dataset.userId = user.id;
                userDiv.dataset.userName = `${user.nome} ${user.apelido ? `(${user.apelido})` : ''}`;
                
                userDiv.addEventListener('click', () => {
                  selectedUsuarioIdInput.value = userDiv.dataset.userId;
                  selectedUserNameDisplay.textContent = `Usuário selecionado: ${userDiv.dataset.userName}`;
                  searchUsuarioInput.value = userDiv.dataset.userName; // Preenche o campo de busca com o nome selecionado
                  usuarioSearchResults.classList.add('hidden');
                });
                usuarioSearchResults.appendChild(userDiv);
              });
              usuarioSearchResults.classList.remove('hidden');
            } else {
              usuarioSearchResults.innerHTML = '<div class="p-2 text-gray-500">Nenhum usuário encontrado.</div>';
              usuarioSearchResults.classList.remove('hidden');
            }
          } catch (error) {
            console.error('Erro na busca de usuários:', error);
            usuarioSearchResults.innerHTML = '<div class="p-2 text-red-500">Erro ao buscar usuários.</div>';
            usuarioSearchResults.classList.remove('hidden');
          }
        }, 300); // Debounce de 300ms
      });

      // Esconde os resultados da busca quando o modal é fechado
      modalAgendamento.addEventListener('close', () => {
        usuarioSearchResults.classList.add('hidden');
      });  
      
    });
  </script>
</body>

</html>