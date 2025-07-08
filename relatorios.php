<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php
require_once '_head.php';

// --- LÓGICA DA PÁGINA ---
if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) {
    $_SESSION['mensagem'] = ["danger", "Você não tem permissão para acessar esta página."];
    header("Location: principal.php");
    exit;
}

$usuario_id = $_SESSION['DuplaUserId'];
$arenas_gestor = Quadras::getArenasDoGestor($usuario_id);

// Define o período padrão (mês atual)
$arena_id_selecionada = filter_input(INPUT_GET, 'arena_id', FILTER_VALIDATE_INT);
$data_inicio = filter_input(INPUT_GET, 'data_inicio') ?: date('Y-m-01');
$data_fim = filter_input(INPUT_GET, 'data_fim') ?: date('Y-m-t');

$relatorio = null;
if ($arena_id_selecionada) {
    $relatorio = Lojinha::getRelatorioVendas($arena_id_selecionada, $data_inicio, $data_fim);
}
?>

<body class="bg-gray-100 min-h-screen text-gray-800" x-data="{ openVendaId: null }">

  <?php require_once '_nav_superior.php' ?>
  <div class="flex pt-16">
    <?php require_once '_nav_lateral.php' ?>
    <main class="flex-1 p-4 sm:p-6">
      <section class="max-w-7xl mx-auto w-full">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Relatório de Vendas</h1>
        </div>
        
        <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 mb-6">
          <form method="GET" action="relatorios.php" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="form-control md:col-span-2">
              <label class="label"><span class="label-text">Selecione a Arena</span></label>
              <select name="arena_id" class="select select-bordered" required>
                <option value="">Escolha uma Arena</option>
                <?php foreach ($arenas_gestor as $arena): ?>
                  <option value="<?= $arena['id'] ?>" <?= ($arena_id_selecionada == $arena['id']) ? 'selected' : '' ?>><?= htmlspecialchars($arena['titulo']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-control">
              <label class="label"><span class="label-text">Data Início</span></label>
              <input type="date" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" class="input input-bordered w-full">
            </div>
            <div class="form-control">
              <label class="label"><span class="label-text">Data Fim</span></label>
              <input type="date" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" class="input input-bordered w-full">
            </div>
            <button type="submit" class="btn btn-primary w-full md:w-auto">Gerar Relatório</button>
          </form>
        </div>

        <?php if ($relatorio): ?>
            <h2 class="text-xl font-bold mb-4">Resumo do Período <span class="text-base font-normal text-gray-500">(<?= date('d/m/Y', strtotime($data_inicio)) ?> a <?= date('d/m/Y', strtotime($data_fim)) ?>)</span></h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
                <div class="stat bg-primary text-white rounded-xl shadow p-4"><div class="stat-title text-white mb-1">Total Geral</div><div class="stat-value">R$ <?= number_format($relatorio['total_geral'], 2, ',', '.') ?></div></div>
                <div class="stat bg-white border border-gray-200 rounded-xl shadow p-4"><div class="stat-title mb-1">Dinheiro</div><div class="stat-value">R$ <?= number_format($relatorio['resumo_pagamentos']['dinheiro'] ?? 0, 2, ',', '.') ?></div></div>
                <div class="stat bg-white border border-gray-200 rounded-xl shadow p-4"><div class="stat-title mb-1">PIX</div><div class="stat-value">R$ <?= number_format($relatorio['resumo_pagamentos']['pix'] ?? 0, 2, ',', '.') ?></div></div>
                <div class="stat bg-white border border-gray-200 rounded-xl shadow p-4"><div class="stat-title mb-1">Cartão</div><div class="stat-value">R$ <?= number_format($relatorio['resumo_pagamentos']['cartao'] ?? 0, 2, ',', '.') ?></div></div>
                <div class="stat bg-white border border-gray-200 rounded-xl shadow p-4"><div class="stat-title mb-1">Cortesia</div><div class="stat-value">R$ <?= number_format($relatorio['resumo_pagamentos']['cortesia'] ?? 0, 2, ',', '.') ?></div></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <h2 class="text-xl font-bold mb-4">Itens Vendidos</h2>
                    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 max-h-[60vh] overflow-y-auto">
                        <table class="table table-sm w-full">
                            <thead><tr><th>Produto</th><th>Qtd.</th><th>Valor</th></tr></thead>
                            <tbody>
                                <?php foreach ($relatorio['resumo_produtos'] as $nome => $dados): ?>
                                <tr>
                                    <td><?= htmlspecialchars($nome) ?></td>
                                    <td><?= $dados['quantidade'] ?></td>
                                    <td class="font-mono">R$ <?= number_format($dados['valor'], 2, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="lg:col-span-2">
                    <h2 class="text-xl font-bold mb-4">Detalhes das Vendas</h2>
                    <div class="bg-white rounded-xl shadow-md border border-gray-200">
                      <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead><tr><th>ID</th><th>Data</th><th>Cliente</th><th>Valor</th><th>Pag.</th><th>Ações</th></tr></thead>
                            <tbody>
                                <?php foreach ($relatorio['lista_vendas'] as $venda): ?>
                                <tr class="cursor-pointer hover" @click="openVendaId = (openVendaId === <?= $venda['id'] ?> ? null : <?= $venda['id'] ?>)">
                                    <td>#<?= $venda['id'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($venda['data'])) ?></td>
                                    <td><?= htmlspecialchars($venda['cliente']) ?></td>
                                    <td class="font-mono">R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></td>
                                    <td><span class="badge badge-ghost badge-sm"><?= htmlspecialchars($venda['forma_pagamento']) ?></span></td>
                                    <td class="flex gap-1">
                                      <button class="btn btn-xs btn-outline" onclick="event.stopPropagation(); alert('Função para emitir NF será implementada.')">Emitir NF</button>
                                    </td>
                                </tr>
                                <tr x-show="openVendaId === <?= $venda['id'] ?>" x-cloak>
                                    <td colspan="6" class="p-0">
                                        <div class="bg-gray-50 p-4">
                                            <h4 class="font-bold mb-2">Itens da Venda #<?= $venda['id'] ?>:</h4>
                                            <ul class="list-disc list-inside text-sm">
                                            <?php foreach ($venda['itens'] as $item): ?>
                                                <li>
                                                  <?= $item['quantidade'] ?>x <?= htmlspecialchars($item['nome']) ?> 
                                                  (R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?> un.)
                                                </li>
                                            <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($relatorio['lista_vendas'])): ?>
                                  <tr><td colspan="6" class="text-center italic py-6">Nenhuma venda encontrada para o período selecionado.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                      </div>
                    </div>
                </div>
            </div>

        <?php elseif ($arena_id_selecionada): ?>
            <div class="text-center bg-white p-8 rounded-xl shadow-md border border-gray-200">
                <h3 class="mt-2 text-lg font-medium text-gray-900">Nenhum resultado</h3>
                <p class="mt-1 text-sm text-gray-500">Não foram encontradas vendas para a arena e período selecionados.</p>
            </div>
        <?php else: ?>
            <div class="text-center bg-white p-8 rounded-xl shadow-md border border-gray-200">
                <h3 class="mt-2 text-lg font-medium text-gray-900">Selecione os filtros</h3>
                <p class="mt-1 text-sm text-gray-500">Escolha uma arena e um período para gerar o relatório de vendas.</p>
            </div>
        <?php endif; ?>

      </section>
      <br><br><br>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>