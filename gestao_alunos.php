<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php
require_once '_head.php';

// --- LÓGICA DA PÁGINA ---
if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) {
    header("Location: principal.php"); exit;
}

$usuario_id = $_SESSION['DuplaUserId'];
$arenas_gestor = Quadras::getArenasDoGestor($usuario_id);
$arena_id_selecionada = filter_input(INPUT_GET, 'arena_id', FILTER_VALIDATE_INT);

$dados_gestao = null;
if ($arena_id_selecionada) {
    $dados_gestao = Turma::getDadosGestaoAlunos($arena_id_selecionada);
}
?>

<body class="bg-gray-100 flex flex-col min-h-screen" x-data="pageData()">

  <?php require_once '_nav_superior.php'; ?>
  <div class="flex flex-1 pt-16">
    <?php require_once '_nav_lateral.php'; ?>
    <main class="flex-1 p-4 sm:p-6">
      <section class="max-w-7xl mx-auto">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight mb-6">Painel de Gestão de Alunos</h1>

        <div class="bg-white p-4 rounded-xl shadow-md border mb-6">
            <form method="GET" action="gestao_alunos.php">
                <label class="label"><span class="label-text">Selecione a Arena</span></label>
                <select name="arena_id" class="select select-bordered" onchange="this.form.submit()">
                    <option value="">Escolha uma Arena</option>
                    <?php foreach ($arenas_gestor as $arena): ?>
                    <option value="<?= $arena['id'] ?>" <?= ($arena_id_selecionada == $arena['id']) ? 'selected' : '' ?>><?= htmlspecialchars($arena['titulo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if ($dados_gestao): ?>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="stat bg-white rounded-xl shadow border cursor-pointer hover:bg-gray-100" @click="setFilter(null)">
                    <div class="stat-title">Alunos Ativos</div>
                    <div class="stat-value text-primary"><?= $dados_gestao['stats']['total_alunos_ativos'] ?></div>
                </div>
                <div class="stat bg-white rounded-xl shadow border cursor-pointer hover:bg-gray-100" @click="setFilter(null)">
                    <div class="stat-title">Vagas Disponíveis</div>
                    <div class="stat-value"><?= $dados_gestao['stats']['total_vagas_disponiveis'] ?></div>
                </div>
                <div class="stat bg-white rounded-xl shadow border cursor-pointer hover:bg-gray-100" :class="{'bg-yellow-100 border-yellow-300': statusFilter === 'pendente'}" @click="setFilter('pendente')">
                    <div class="stat-title">Mensal. Pendentes</div>
                    <div class="stat-value text-warning"><?= $dados_gestao['stats']['mensalidades_pendentes'] ?></div>
                </div>
                <div class="stat bg-white rounded-xl shadow border cursor-pointer hover:bg-gray-100" :class="{'bg-red-100 border-red-300': statusFilter === 'vencida'}" @click="setFilter('vencida')">
                    <div class="stat-title">Mensal. Vencidas</div>
                    <div class="stat-value text-error"><?= $dados_gestao['stats']['mensalidades_vencidas'] ?></div>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-md border">
                <div class="flex flex-col md:flex-row gap-4 mb-4">
                    <input type="text" x-model="searchTerm" placeholder="Buscar aluno por nome..." class="input input-bordered w-full md:flex-1">
                    <button class="btn btn-ghost" x-show="statusFilter" @click="setFilter(null)">Limpar Filtro de Status</button>
                </div>

                <div class="space-y-2">
                    <template x-for="turma in filteredTurmas" :key="turma.id">
                        <div class="collapse collapse-plus bg-base-200 rounded-lg" :class="{'collapse-open': statusFilter}">
                            <input type="checkbox" /> 
                            <div class="collapse-title text-xl font-medium">
                                <span x-text="turma.nome"></span>
                                <span class="text-sm font-normal text-gray-500">(<span x-text="turma.alunos.length"></span> alunos)</span>
                            </div>
                            <div class="collapse-content">
                                <div class="space-y-1 pl-4">
                                    <template x-for="aluno in turma.alunos" :key="aluno.id">
                                        <div class="collapse collapse-arrow bg-base-100 rounded-md" :class="{'collapse-open': statusFilter}">
                                            <input type="checkbox" /> 
                                            <div class="collapse-title font-medium">
                                                <span x-text="`${aluno.nome} ${aluno.sobrenome}`"></span>
                                            </div>
                                            <div class="collapse-content">
                                                <div class="flex justify-end mb-2">
                                                    <a :href="`turma_financeiro.php?id=${turma.id}`" class="btn btn-xs btn-outline btn-primary">Registar Pagamento</a>
                                                </div>
                                                <table class="table table-sm w-full">
                                                    <thead><tr><th>Competência</th><th>Status</th><th>Valor</th></tr></thead>
                                                    <tbody>
                                                        <template x-for="mensalidade in aluno.mensalidades" :key="mensalidade.id">
                                                            <tr x-show="!statusFilter || mensalidade.status === statusFilter">
                                                                <td x-text="new Date(mensalidade.competencia + 'T00:00:00').toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' })"></td>
                                                                <td><span class="badge" :class="{'badge-success': mensalidade.status == 'paga', 'badge-warning': mensalidade.status == 'pendente', 'badge-error': mensalidade.status == 'vencida'}" x-text="mensalidade.status"></span></td>
                                                                <td class="font-mono" x-text="parseFloat(mensalidade.valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })"></td>
                                                            </tr>
                                                        </template>
                                                        <tr x-show="aluno.mensalidades.length === 0"><td colspan="3" class="text-center italic">Nenhuma mensalidade gerada.</td></tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </template>
                                    <p x-show="turma.alunos.length === 0" class="italic text-center py-2">Nenhum aluno nesta turma.</p>
                                </div>
                            </div>
                        </div>
                    </template>
                    <p x-show="filteredTurmas.length === 0" class="italic text-center py-4">Nenhum aluno ou turma encontrados com os termos da busca/filtro.</p>
                </div>
            </div>
        <?php endif; ?>
      </section>
    </main>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script>
      function pageData() {
          return {
              searchTerm: '',
              statusFilter: null, // NOVO: Guarda o status do filtro ('pendente', 'vencida' ou null)
              turmas: <?= json_encode($dados_gestao['turmas'] ?? []) ?>,
              
              // NOVO: Função para definir o filtro de status
              setFilter(status) {
                  // Se clicarmos no mesmo filtro outra vez, ele é limpo.
                  this.statusFilter = this.statusFilter === status ? null : status;
              },
              
              get filteredTurmas() {
                  let turmasFiltradas = this.turmas;
                  
                  // 1. Filtro por status da mensalidade
                  if (this.statusFilter) {
                      turmasFiltradas = turmasFiltradas.map(turma => {
                          const alunosComStatus = turma.alunos.filter(aluno => 
                              aluno.mensalidades.some(m => m.status === this.statusFilter)
                          );
                          return { ...turma, alunos: alunosComStatus };
                      }).filter(turma => turma.alunos.length > 0);
                  }

                  // 2. Filtro por nome do aluno (aplicado sobre o resultado do primeiro filtro)
                  if (this.searchTerm.trim() !== '') {
                      turmasFiltradas = turmasFiltradas.map(turma => {
                          const alunosPorNome = turma.alunos.filter(aluno =>
                              `${aluno.nome} ${aluno.sobrenome}`.toLowerCase().includes(this.searchTerm.toLowerCase())
                          );
                          return { ...turma, alunos: alunosPorNome };
                      }).filter(turma => turma.alunos.length > 0);
                  }
                  
                  return turmasFiltradas;
              }
          }
      }
  </script>

</body>
</html>