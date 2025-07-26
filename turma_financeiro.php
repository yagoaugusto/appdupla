<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php
require_once '_head.php';

// --- LÓGICA DA PÁGINA ---
if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) { header("Location: principal.php"); exit; }

$turma_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$turma_id) { $_SESSION['mensagem'] = ['error', 'ID da turma inválido.']; header("Location: turmas.php"); exit; }

$turma = Turma::getTurmaById($turma_id);
if (!$turma) { $_SESSION['mensagem'] = ['error', 'Turma não encontrada.']; header("Location: turmas.php"); exit; }

$alunos_ativos = array_filter(Turma::getAlunosDaTurma($turma_id), fn($a) => $a['status'] == 'ativo');
?>

<body class="bg-gray-100 flex flex-col min-h-screen" x-data="paymentManager()">

  <?php require_once '_nav_superior.php'; ?>
  <div class="flex flex-1 pt-16">
    <?php require_once '_nav_lateral.php'; ?>
    <main class="flex-1 p-4 sm:p-6">
      <section class="max-w-4xl mx-auto space-y-6">

        <div>
            <div class="text-sm breadcrumbs"><ul><li><a href="turmas.php?arena_id=<?= $turma['arena_id'] ?>">Turmas</a></li><li><a href="turma_detalhes.php?id=<?= $turma_id ?>">Detalhes</a></li><li>Financeiro</li></ul></div>
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight mt-2">Assistente de Pagamento: <?= htmlspecialchars($turma['nome']) ?></h1>
        </div>
        
        <?php if (isset($_SESSION['mensagem'])): list($tipo, $texto) = $_SESSION['mensagem']; ?>
            <div class="alert <?= $tipo === 'success' ? 'alert-success' : 'alert-error' ?> shadow-lg"><div><span><?= htmlspecialchars($texto) ?></span></div></div>
        <?php unset($_SESSION['mensagem']); endif; ?>

        <form method="POST" action="controllers/turma_controller.php">
            <input type="hidden" name="action" value="registrar_pagamento_selecionado">
            <input type="hidden" name="turma_id" value="<?= $turma_id ?>">
            <input type="hidden" name="matricula_id" x-model="selectedMatriculaId">

            <div class="bg-white p-6 rounded-xl shadow-md border space-y-6">
                <div>
                    <label class="label"><span class="label-text text-lg font-bold">1. Selecione o Aluno</span></label>
                    <select id="alunoSelect" class="select select-bordered w-full" @change="fetchMensalidades($event.target.value)">
                        <option disabled selected value="">Selecione um aluno para começar...</option>
                        <?php foreach ($alunos_ativos as $aluno): ?>
                            <option value="<?= $aluno['matricula_id'] ?>"><?= htmlspecialchars($aluno['nome'] . ' ' . $aluno['sobrenome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div x-show="mensalidades.length > 0" x-cloak>
                    <label class="label"><span class="label-text text-lg font-bold">2. Marque as mensalidades a serem pagas</span></label>
                    <div class="border rounded-lg p-4 space-y-2">
                        <template x-for="mensalidade in mensalidades" :key="mensalidade.id">
                            <label class="flex items-center p-3 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" class="checkbox checkbox-primary" name="mensalidades_selecionadas[]" :value="mensalidade.id" x-model="selectedMensalidadesIds">
                                <span class="flex-1 ml-4 font-semibold" x-text="formatCompetencia(mensalidade.competencia)"></span>
                                <span class="font-mono mr-4" x-text="formatCurrency(mensalidade.valor)"></span>
                                <span class="badge" :class="{'badge-warning': mensalidade.status === 'pendente', 'badge-error': mensalidade.status === 'vencida'}" x-text="mensalidade.status"></span>
                            </label>
                        </template>
                    </div>
                </div>
                <div x-show="loading" class="text-center">Carregando mensalidades...</div>
                <div x-show="!loading && hasFetched && mensalidades.length === 0" class="text-center italic text-success p-4">Este aluno está com todas as mensalidades em dia!</div>

                <div x-show="selectedMensalidadesIds.length > 0" x-cloak>
                    <label class="label"><span class="label-text text-lg font-bold">3. Confirme os detalhes do pagamento</span></label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="form-control">
                            <label class="label"><span class="label-text">Valor Total Pago (R$)</span></label>
                            <input type="text" id="valorTotalPago" name="valor_total_pago" x-model="totalPago" class="input input-bordered font-bold" required>
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Forma de Pagamento</span></label>
                            <select name="forma_pagamento" class="select select-bordered w-full" required><option>PIX</option><option>Dinheiro</option><option>Crédito</option><option>Débito</option></select>
                        </div>
                        <div class="form-control">
                            <label class="label"><span class="label-text">Data do Pagamento</span></label>
                            <input type="date" name="data_pagamento" class="input input-bordered w-full" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="pt-4 text-right">
                    <button type="submit" class="btn btn-primary btn-lg" :disabled="selectedMensalidadesIds.length === 0">Confirmar Pagamento</button>
                </div>
            </div>
        </form>
      </section>
    </main>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
  <script>
    function paymentManager() {
      return {
        loading: false,
        hasFetched: false,
        selectedMatriculaId: null,
        mensalidades: [],
        selectedMensalidadesIds: [],
        totalPago: '0,00',

        // Função chamada quando um aluno é selecionado
        fetchMensalidades(matriculaId) {
          if (!matriculaId) return;
          this.loading = true;
          this.hasFetched = true;
          this.selectedMatriculaId = matriculaId;
          this.resetPayment();

          $.getJSON(`controllers/ajax_get_mensalidades.php?matricula_id=${matriculaId}`)
            .done(data => {
              if (data.error) {
                  alert(data.error);
                  this.mensalidades = [];
              } else {
                  this.mensalidades = data;
              }
            })
            .fail(() => alert('Ocorreu um erro ao buscar as mensalidades.'))
            .always(() => this.loading = false);
        },

        // Função para resetar os campos quando o aluno muda
        resetPayment() {
            this.selectedMensalidadesIds = [];
            this.totalPago = '0,00';
        },
        
        // Formatação de valores e datas
        formatCurrency(value) {
            return parseFloat(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        },
        formatCompetencia(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            return date.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
        },

        // Observador para atualizar o valor total quando os checkboxes mudam
        init() {
            $('#valorTotalPago').mask('#.##0,00', {reverse: true});
            
            this.$watch('selectedMensalidadesIds', () => {
                let total = 0;
                this.selectedMensalidadesIds.forEach(id => {
                    const mensalidade = this.mensalidades.find(m => m.id == id);
                    if (mensalidade) total += parseFloat(mensalidade.valor);
                });
                // Atualiza o valor no input formatado
                $('#valorTotalPago').val(total.toFixed(2).replace('.', ',')).trigger('input');
                this.totalPago = $('#valorTotalPago').val();
            });
        }
      }
    }
  </script>
</body>
</html>