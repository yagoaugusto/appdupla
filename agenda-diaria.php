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
$data_selecionada_str = filter_input(INPUT_GET, 'data', FILTER_SANITIZE_STRING);

$data_selecionada = new DateTime($data_selecionada_str ?? 'now', new DateTimeZone('America/Sao_Paulo'));

// --- BUSCA DE DADOS ---
$quadras_da_arena = [];
$grade_horarios_diaria = [];
if ($arena_id_selecionada) {
    $quadras_da_arena = Quadras::getQuadrasPorArena($arena_id_selecionada);

    if (!empty($quadras_da_arena)) {
        $ids_quadras = array_column($quadras_da_arena, 'id');

        // Determina o dia da semana para a consulta de funcionamento
        $mapa_dias_semana = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
        $dia_semana_num = $data_selecionada->format('N') - 1; // 0 para Segunda, 6 para Domingo
        $dia_semana_key = $mapa_dias_semana[$dia_semana_num];

        // Busca os horários de funcionamento de todas as quadras para o dia selecionado
        $horarios_funcionamento = Quadras::getFuncionamentoMultiplasQuadrasPorDia($ids_quadras, $dia_semana_key);
        $horarios_funcionamento_map = [];
        foreach ($horarios_funcionamento as $hf) {
            $horarios_funcionamento_map[$hf['quadra_id']][substr($hf['hora_inicio'], 0, 5)] = true;
        }

        $ids_quadras = array_column($quadras_da_arena, 'id');
        $placeholders = implode(',', array_fill(0, count($ids_quadras), '?'));

        // Busca todos os agendamentos de todas as quadras da arena para o dia selecionado
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("SELECT * FROM agenda_quadras WHERE quadra_id IN ($placeholders) AND data = ?");
        $params = array_merge($ids_quadras, [$data_selecionada->format('Y-m-d')]);
        $stmt->execute($params);
        $agendamentos_do_dia = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Organiza os agendamentos por quadra e hora
        $agendamentos_map = [];
        foreach ($agendamentos_do_dia as $ag) {
            $hora_key = substr($ag['hora_inicio'], 0, 5);
            $agendamentos_map[$ag['quadra_id']][$hora_key] = $ag;
        }

        // Monta a grade final
        for ($h = 6; $h < 23; $h++) {
            $hora_str = sprintf("%02d:00", $h);
            foreach ($quadras_da_arena as $quadra) {
                $quadra_id = $quadra['id'];
                if (isset($agendamentos_map[$quadra_id][$hora_str])) {
                    $ag = $agendamentos_map[$quadra_id][$hora_str];
                    $grade_horarios_diaria[$hora_str][$quadra_id] = ['status' => 'agendado', 'tipo' => $ag['status'], 'details' => $ag];
                } else {
                    if (isset($horarios_funcionamento_map[$quadra_id][$hora_str])) {
                        $grade_horarios_diaria[$hora_str][$quadra_id] = ['status' => 'disponivel'];
                    } else {
                        $grade_horarios_diaria[$hora_str][$quadra_id] = ['status' => 'fechado'];
                    }
                }
            }
        }
    }
}

// Mapeamento de tipos de agendamento para estilos CSS
$estilos_tipo = [
    'reservado' => 'bg-blue-200 border-blue-400 text-blue-800',
    'bloqueado' => 'bg-gray-300 border-gray-500 text-gray-800',
    'aula' => 'bg-yellow-200 border-yellow-400 text-yellow-800',
    'dayuse' => 'bg-purple-200 border-purple-400 text-purple-800',
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
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight mb-6">Agenda Diária por Arena</h1>

        <!-- Filtros -->
        <form method="GET" action="agenda-diaria.php" class="bg-white p-4 rounded-xl shadow-md border border-gray-200 mb-6 grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
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
            <label class="label"><span class="label-text">Data</span></label>
            <input type="date" name="data" class="input input-bordered" value="<?= $data_selecionada->format('Y-m-d') ?>" onchange="this.form.submit()">
          </div>
        </form>

        <?php if ($arena_id_selecionada): ?>
          <!-- Navegação do Dia e Legenda -->
          <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex items-center gap-2">
              <a href="?arena_id=<?= $arena_id_selecionada ?>&data=<?= (clone $data_selecionada)->modify('-1 day')->format('Y-m-d') ?>" class="btn btn-outline btn-sm">&larr; Anterior</a>
              <a href="?arena_id=<?= $arena_id_selecionada ?>&data=<?= (new DateTime())->format('Y-m-d') ?>" class="btn btn-outline btn-sm">Hoje</a>
              <a href="?arena_id=<?= $arena_id_selecionada ?>&data=<?= (clone $data_selecionada)->modify('+1 day')->format('Y-m-d') ?>" class="btn btn-outline btn-sm">Próximo &rarr;</a>
            </div>
            <a href="agendamento-quadra.php?arena_id=<?= $arena_id_selecionada ?>" class="btn btn-sm btn-outline">Ver Visão Semanal &rarr;</a>
          </div>

          <!-- Grade de Horários -->
          <div class="overflow-x-auto bg-white rounded-xl shadow-lg border border-gray-200">
            <table class="table table-fixed w-full text-center">
              <thead class="text-sm">
                <tr>
                  <th class="w-24">Horário</th>
                  <?php foreach ($quadras_da_arena as $quadra): ?>
                    <th><?= htmlspecialchars($quadra['nome']) ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php for ($h = 6; $h < 23; $h++):
                  $hora_atual = sprintf("%02d:00", $h);
                ?>
                  <tr>
                    <th class="text-xs sm:text-sm"><?= $hora_atual ?></th>
                    <?php foreach ($quadras_da_arena as $quadra):
                      $quadra_id = $quadra['id'];
                      $slot = $grade_horarios_diaria[$hora_atual][$quadra_id] ?? ['status' => 'fechado'];
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
                      <td class="<?= $classes_slot ?>" data-quadra-id="<?= $quadra_id ?>" data-data="<?= $data_selecionada->format('Y-m-d') ?>" data-hora="<?= $hora_atual ?>"
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
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Selecione uma arena</h3>
            <p class="mt-1 text-sm text-gray-500">Escolha uma arena no filtro acima para visualizar a agenda diária.</p>
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
        <!-- O quadra_id_selecionada será definido dinamicamente no JS para cada slot -->
        <input type="hidden" name="quadra_id_selecionada" id="modalQuadraIdInput">
        <input type="hidden" name="origin" value="agenda-diaria">
        <input type="hidden" name="data_selecionada" value="<?= $data_selecionada->format('Y-m-d') ?>">
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
          <input type="text" id="searchUsuarioInput" placeholder="Busque por nome, apelido ou CPF" class="input input-bordered w-full" required>
          <input type="hidden" name="usuario_id" id="selectedUsuarioId">
          <div id="selectedUserNameDisplay" class="text-sm text-gray-600 mt-1"></div>
          <div id="usuarioSearchResults" class="absolute top-full left-0 w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1 max-h-48 overflow-y-auto hidden z-30">
            <!-- Resultados da busca serão inseridos aqui via JS -->
          </div>
        </div>
        <div class="form-control">
          <label class="label"><span class="label-text">Observações</span></label>
          <textarea name="observacoes" class="textarea textarea-bordered" placeholder="Ex: Pagamento pendente, evento especial, nome do cliente (se não for usuário), etc." required></textarea>
        </div>
        <div id="valoresIndividuaisContainer" class="max-h-48 overflow-y-auto space-y-2 border-t pt-4 mt-2">
          <!-- Campos de valor por slot serão adicionados aqui via JS -->
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
        <button type="button" id="btnCancelarAgendamento" class="btn btn-error" onclick="handleCancelClick(this)">Cancelar Agendamento</button>
        <button type="button" class="btn" onclick="modalVerAgendamento.close()">Fechar</button>
      </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
  </dialog>

  <!-- Modal de Confirmação de Cancelamento -->
  <dialog id="modalConfirmacaoCancelamento" class="modal">
    <div class="modal-box">
      <h3 class="font-bold text-lg text-red-600">Cancelar Agendamento</h3>
      <p class="py-4">Tem certeza que deseja cancelar este agendamento? Esta ação não pode ser desfeita.</p>
      <div class="modal-action">
        <button id="btnConfirmarCancelamento" class="btn btn-error">Sim, Cancelar</button>
        <button class="btn" onclick="document.getElementById('modalConfirmacaoCancelamento').close()">Fechar</button>
      </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
  </dialog>

  <script>
    // Função global para lidar com o clique no botão de cancelar.
    function handleCancelClick(buttonElement) {
      const agendamentoId = buttonElement.dataset.agendamentoId;
      if (!agendamentoId) {
        alert('Erro: Não foi possível identificar o agendamento para cancelamento.');
        return;
      }

      const modalConfirm = document.getElementById('modalConfirmacaoCancelamento');
      const btnConfirmar = document.getElementById('btnConfirmarCancelamento');
      btnConfirmar.dataset.agendamentoId = agendamentoId;

      modalConfirm.showModal();
    }

    document.addEventListener('DOMContentLoaded', () => {
      // Listener para confirmação de cancelamento
      document.getElementById('btnConfirmarCancelamento').addEventListener('click', async function () {
        const agendamentoId = this.dataset.agendamentoId;
        const modalConfirm = document.getElementById('modalConfirmacaoCancelamento');

        try {
          const response = await fetch('controller-agendamento/cancelar-agendamento.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ agendamento_id: agendamentoId })
          });

          if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`O servidor respondeu com um erro: ${response.status}. Detalhes: ${errorText}`);
          }

          const result = await response.json();

          if (result.success) {
            alert(result.message);
            modalConfirm.close();
            document.getElementById('modalVerAgendamento').close();
            window.location.reload();
          } else {
            alert('Erro: ' + result.message);
          }
        } catch (error) {
          console.error('Erro ao cancelar agendamento:', error);
          alert('Ocorreu um erro de comunicação ao tentar cancelar. Verifique o console para mais detalhes.');
        }
      });

      const agendaTableBody = document.querySelector('table tbody');
      const floatingBar = document.getElementById('floatingActionBar');
      const selectedCountSpan = document.getElementById('selectedCount');
      const agendarBtn = document.getElementById('agendarSelecionadosBtn');
      const limparBtn = document.getElementById('limparSelecaoBtn');
      const modalAgendamento = document.getElementById('modalAgendamento');
      const selectedSlotsInput = document.getElementById('selectedSlotsInput');
      const modalVerAgendamento = document.getElementById('modalVerAgendamento');
      const viewAgendamentoContent = document.getElementById('viewAgendamentoContent');
      const btnCancelarAgendamento = document.getElementById('btnCancelarAgendamento');
      const modalQuadraIdInput = document.getElementById('modalQuadraIdInput'); // Novo input para quadra_id no modal

      let selectedSlots = new Set(); // Armazena identificadores únicos: "quadraId_data_hora"

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

        const quadraId = td.dataset.quadraId;
        const data = td.dataset.data;
        const hora = td.dataset.hora;
        const agendamentoId = td.dataset.agendamentoId;

        if (td.classList.contains('slot-disponivel')) {
          const slotIdentifier = `${quadraId}_${data}_${hora}`;
          if (selectedSlots.has(slotIdentifier)) {
            selectedSlots.delete(slotIdentifier);
            td.classList.remove('slot-selecionado', 'bg-yellow-300', 'border-yellow-500');
          } else {
            selectedSlots.add(slotIdentifier);
            td.classList.add('slot-selecionado', 'bg-yellow-300', 'border-yellow-500');
          }
          updateFloatingBar();
        } else if (td.classList.contains('slot-agendado')) {
          if (agendamentoId) {
            viewAgendamentoContent.innerHTML = `<div class="text-center"><span class="loading loading-spinner loading-lg"></span><p class="mt-2">Carregando detalhes...</p></div>`;
            modalVerAgendamento.showModal();

            btnCancelarAgendamento.dataset.agendamentoId = agendamentoId;

            fetch(`controller-agendamento/get-agendamento-details.php?id=${agendamentoId}`)
              .then(response => response.json())
              .then(result => {
                if (result.success) {
                  const agendamento = result.data;

                  const dataFormatada = new Date(agendamento.data + 'T00:00:00').toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric' });
                  const horaInicio = agendamento.hora_inicio.substring(0, 5);
                  const horaFim = agendamento.hora_fim.substring(0, 5);
                  const precoFormatado = parseFloat(agendamento.preco).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

                  let clienteInfo = '';
                  if (agendamento.cliente_nome) {
                    clienteInfo = `<div class="flex justify-between"><strong>Cliente:</strong> <span>${agendamento.cliente_nome}</span></div>`;
                    if (agendamento.cliente_telefone) {
                      clienteInfo += `<div class="flex justify-between"><strong>Telefone:</strong> <span>${agendamento.cliente_telefone}</span></div>`;
                    }
                  }

                  const observacoesInfo = agendamento.observacoes
                    ? `<div class="pt-2 mt-2 border-t"><strong>Observações:</strong><p class="text-gray-600 bg-gray-50 p-2 rounded-md mt-1">${agendamento.observacoes.replace(/\n/g, '<br>')}</p></div>`
                    : '';

                  viewAgendamentoContent.innerHTML = `
                    <div class="space-y-1">
                      <div class="flex justify-between"><strong>Status:</strong> <span class="badge badge-outline capitalize">${agendamento.status}</span></div>
                      <div class="flex justify-between"><strong>Data:</strong> <span>${dataFormatada}</span></div>
                      <div class="flex justify-between"><strong>Horário:</strong> <span>${horaInicio} - ${horaFim}</span></div>
                      <div class="flex justify-between"><strong>Preço:</strong> <span>${precoFormatado}</span></div>
                      ${clienteInfo}
                    </div>
                    ${observacoesInfo}
                  `;
                } else {
                  viewAgendamentoContent.innerHTML = `<p class="text-red-500 text-center">${result.message}</p>`;
                }
              })
              .catch(error => {
                console.error('Erro ao buscar detalhes do agendamento:', error);
                viewAgendamentoContent.innerHTML = `<p class="text-red-500 text-center">Erro de comunicação. Tente novamente.</p>`;
              });
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
          const [quadraId, data, hora] = id.split('_');
          return { quadraId, data, hora };
        });

        // O controller `salvar-agendamento.php` usa o `quadra_id_selecionada` como fallback.
        modalQuadraIdInput.value = slotsData[0].quadraId;
        selectedSlotsInput.value = JSON.stringify(slotsData.map(s => ({ data: s.data, hora: s.hora, quadra_id: s.quadraId })));

        // Adiciona campos individuais de valor
        const valoresContainer = document.getElementById('valoresIndividuaisContainer');
        valoresContainer.innerHTML = ''; // Limpa antes de adicionar novos

        const quadrasDaArena = <?= json_encode(array_column($quadras_da_arena, 'nome', 'id')) ?>;

        slotsData.forEach((slot, index) => {
          const wrapper = document.createElement('div');
          wrapper.classList.add('form-control');

          const label = document.createElement('label');
          label.classList.add('label');

          const quadraNome = quadrasDaArena[slot.quadraId] || `Quadra ID ${slot.quadraId}`;
          label.innerHTML = `<span class="label-text">${quadraNome} - ${slot.hora}</span>`;

          const campo = document.createElement('input');
          campo.type = 'text';
          // A chave deve ser única, combinando quadra, data e hora.
          campo.name = `valores_individuais[${slot.quadraId}_${slot.data}_${slot.hora}]`;
          campo.classList.add('input', 'input-bordered', 'moeda', 'w-full');
          campo.setAttribute('data-slot', `${slot.quadraId}_${slot.data}_${slot.hora}`);
          campo.placeholder = `Valor para ${slot.hora}`;
          campo.required = true;

          wrapper.appendChild(label);
          wrapper.appendChild(campo);
          valoresContainer.appendChild(wrapper);
        });

        modalAgendamento.showModal();
        preencherValoresIndividuaisDiaria();
      });

      // Função para preencher automaticamente os valores individuais dos slots selecionados na visão diária
      async function preencherValoresIndividuaisDiaria() {
        for (let slotId of selectedSlots) {
          const [quadraId, data, hora] = slotId.split('_');
          try {
            const response = await fetch(`controller-agendamento/get-valor-slot.php?quadra_id=${quadraId}&data=${encodeURIComponent(data)}&hora=${encodeURIComponent(hora)}`);
            const json = await response.json();
            if (json.success) {
              // A chave do input é quadraId_data_hora
              const input = document.querySelector(`[name="valores_individuais[${quadraId}_${data}_${hora}]"]`);
              if (input) {
                input.value = parseFloat(json.total).toLocaleString('pt-BR', {
                  style: 'currency',
                  currency: 'BRL'
                });
              }
            }
          } catch (err) {
            console.warn('Erro ao preencher valor individual do slot:', err);
          }
        }
      }

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
          selectedUsuarioIdInput.value = '';
          selectedUserNameDisplay.textContent = '';
          return;
        }

        searchTimeout = setTimeout(async () => {
          try {
            const response = await fetch(`controller-agendamento/buscar-usuarios.php?search_term=${encodeURIComponent(searchTerm)}`);
            const data = await response.json();

            usuarioSearchResults.innerHTML = '';
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
                  searchUsuarioInput.value = userDiv.dataset.userName;
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
        }, 300);
      });

      modalAgendamento.addEventListener('close', () => {
        usuarioSearchResults.classList.add('hidden');
      });

      // Máscara para campo de moeda
      document.addEventListener('input', function(e) {
        if (e.target.classList.contains('moeda')) {
          let v = e.target.value.replace(/\D/g, '');
          v = (parseInt(v, 10) / 100).toFixed(2) + '';
          v = v.replace('.', ',');
          v = v.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
          e.target.value = 'R$ ' + v;
        }
      });
    });
  </script>
</body>
</html>