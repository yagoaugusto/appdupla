<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php
require_once '_head.php';

// Lógica da página para obter os dados
if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) { header("Location: principal.php"); exit; }
$arenas_gestor = Quadras::getArenasDoGestor($_SESSION['DuplaUserId']);
$arena_id_selecionada = filter_input(INPUT_GET, 'arena_id', FILTER_VALIDATE_INT);
$competencia_selecionada = filter_input(INPUT_GET, 'competencia') ?: date('Y-m');

$dados_relatorio = null;
if ($arena_id_selecionada) {
    $dados_relatorio = Turma::getDadosRelatorioTurmas($arena_id_selecionada, $competencia_selecionada);
}
?>

<body class="bg-gray-100 flex flex-col min-h-screen">

  <?php require_once '_nav_superior.php'; ?>
  <div class="flex flex-1 pt-16">
    <?php require_once '_nav_lateral.php'; ?>
    <main class="flex-1 p-4 sm:p-6">
      <section class="max-w-7xl mx-auto space-y-8">
        <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">Relatórios de Turmas</h1>

        <div class="bg-white p-4 rounded-xl shadow-md border">
          <form method="GET" action="relatorio_turmas.php" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="form-control">
                <label class="label"><span class="label-text">Arena</span></label>
                <select name="arena_id" class="select select-bordered" required onchange="this.form.submit()"><option value="">Selecione...</option><?php foreach ($arenas_gestor as $a): ?><option value="<?= $a['id'] ?>" <?= ($arena_id_selecionada == $a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['titulo']) ?></option><?php endforeach; ?></select>
            </div>
            <div class="form-control">
                <label class="label"><span class="label-text">Competência</span></label>
                <select name="competencia" class="select select-bordered" onchange="this.form.submit()"><?php $data_ref = new DateTime(); for ($i=-12; $i<=1; $i++){ $d = (clone $data_ref)->modify("$i month"); $v = $d->format('Y-m'); $t = ucfirst(strftime('%B / %Y', $d->getTimestamp())); echo "<option value=\"$v\" ".($competencia_selecionada==$v ? 'selected' : '').">$t</option>"; } ?></select>
            </div>
          </form>
        </div>

        <?php if ($dados_relatorio): $stats = $dados_relatorio['stats']; ?>
            <div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="stat bg-white rounded-xl shadow border"><div class="stat-title">Faturamento (Mês)</div><div class="stat-value text-success">R$<?= number_format($stats['faturamento_mes'], 2, ',', '.') ?></div></div>
                    <div class="stat bg-white rounded-xl shadow border"><div class="stat-title">A Receber (Mês)</div><div class="stat-value text-warning">R$<?= number_format($stats['a_receber_mes'], 2, ',', '.') ?></div></div>
                    <div class="stat bg-white rounded-xl shadow border"><div class="stat-title">Total Vencido</div><div class="stat-value text-error">R$<?= number_format($stats['vencido_total'], 2, ',', '.') ?></div></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="stat bg-white rounded-xl shadow border"><div class="stat-title">Alunos Ativos</div><div class="stat-value text-info"><?= $stats['total_alunos_ativos'] ?></div></div>
                    <div class="stat bg-white rounded-xl shadow border"><div class="stat-title">Novas Matrículas</div><div class="stat-value"><?= $stats['novas_matriculas_mes'] ?></div></div>
                    <div class="stat bg-white rounded-xl shadow border"><div class="stat-title">Ocupação das Vagas</div><div class="stat-value"><?= $stats['taxa_ocupacao'] ?>%</div></div>
                </div>
            </div>

            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-md border">
                <h2 class="text-xl font-bold mb-4">Análise por Professor</h2>
                <div class="space-y-3">
                <?php foreach ($dados_relatorio['professores'] as $prof): ?>
                    <div class="collapse collapse-plus bg-base-200 rounded-lg">
                        <input type="checkbox" /> 
                        <div class="collapse-title text-xl font-medium"><?= htmlspecialchars($prof['nome']) ?></div>
                        <div class="collapse-content">
                            <div class="stats shadow w-full mb-4">
                                <div class="stat"><div class="stat-title">Faturamento</div><div class="stat-value text-xs text-success">R$<?= number_format($prof['faturamento'], 2, ',', '.') ?></div></div>
                                <div class="stat"><div class="stat-title">A Receber</div><div class="stat-value text-xs text-warning">R$<?= number_format($prof['a_receber'], 2, ',', '.') ?></div></div>
                                <div class="stat"><div class="stat-title">Vencido</div><div class="stat-value text-xs text-error">R$<?= number_format($prof['vencido'], 2, ',', '.') ?></div></div>
                            </div>
                            <h4 class="font-semibold mb-2">Alunos:</h4>
                            <ul class="menu bg-base-100 w-full rounded-box">
                                <?php $turma_atual = ''; foreach ($prof['alunos'] as $aluno): ?>
                                    <?php if ($turma_atual != $aluno['turma_nome']): $turma_atual = $aluno['turma_nome']; ?>
                                        <li class="menu-title"><span><?= htmlspecialchars($turma_atual) ?></span></li>
                                    <?php endif; ?>
                                    <li><a><?= htmlspecialchars($aluno['nome_completo']) ?> <span class="badge <?= $aluno['status_financeiro'] == 'paga' ? 'badge-success' : ($aluno['status_financeiro'] == 'pendente' ? 'badge-warning' : 'badge-error') ?>"><?= $aluno['status_financeiro'] ?></span></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>

        <?php elseif ($arena_id_selecionada): ?>
            <p>Nenhum dado encontrado para a competência selecionada.</p>
        <?php else: ?>
            <p>Selecione uma arena para começar.</p>
        <?php endif; ?>
      </section>
    </main>
  </div>
</body>
</html>