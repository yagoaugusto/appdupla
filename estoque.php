<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php
require_once '_head.php';

if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) {
    $_SESSION['mensagem'] = ["danger", "Você não tem permissão para acessar esta página."];
    header("Location: principal.php");
    exit;
}

$usuario_id = $_SESSION['DuplaUserId'];
$arenas_gestor = Quadras::getArenasDoGestor($usuario_id);
$arena_id_selecionada = filter_input(INPUT_GET, 'arena_id', FILTER_VALIDATE_INT);
$produtos = [];
if ($arena_id_selecionada) {
    // A função agora retorna 'estoque_calculado'
    $produtos = Lojinha::getProdutosPorArena($arena_id_selecionada);
}
?>

<body class="bg-gray-100 min-h-screen text-gray-800">
  <?php require_once '_nav_superior.php' ?>
  <div class="flex pt-16">
    <?php require_once '_nav_lateral.php' ?>
    <main class="flex-1 p-4 sm:p-6">
      <section class="max-w-7xl mx-auto w-full">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Estoque da Lojinha</h1>
        </div>

        <?php
        if (isset($_SESSION['mensagem'])) {
            [$tipo, $texto] = $_SESSION['mensagem'];
            $alert_class = ($tipo === 'success') ? 'alert-success' : 'alert-error';
            echo "<div class='alert {$alert_class} shadow-lg mb-5'><div><span>" . htmlspecialchars($texto) . "</span></div></div>";
            unset($_SESSION['mensagem']);
        }
        ?>

        <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 mb-6 flex flex-col md:flex-row gap-4">
          <form method="GET" action="estoque.php" class="flex-1">
            <div class="form-control">
              <label class="label"><span class="label-text">Selecione sua Arena</span></label>
              <select name="arena_id" class="select select-bordered" onchange="this.form.submit()">
                <option value="">Escolha uma Arena</option>
                <?php foreach ($arenas_gestor as $arena): ?>
                  <option value="<?= $arena['id'] ?>" <?= ($arena_id_selecionada == $arena['id']) ? 'selected' : '' ?>><?= htmlspecialchars($arena['titulo']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </form>
          <div class="flex-1">
              <div class="form-control">
                <label class="label"><span class="label-text">Buscar Produto</span></label>
                <input type="text" id="searchInput" placeholder="Digite para buscar..." class="input input-bordered w-full" <?= !$arena_id_selecionada ? 'disabled' : '' ?>>
              </div>
          </div>
        </div>

        <?php if ($arena_id_selecionada): ?>
          <div class="flex justify-end mb-4">
              <button class="btn btn-primary" onclick="abrirModalCriacao(<?= $arena_id_selecionada ?>)">
                  Adicionar Produto
              </button>
          </div>
          <div class="overflow-x-auto bg-white rounded-xl shadow-lg border border-gray-200">
            <table class="table w-full">
              <thead>
                <tr>
                  <th>Produto</th>
                  <th>Preço</th>
                  <th>Estoque Atual</th>
                  <th>Status</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody id="productsTableBody">
                <?php if (empty($produtos)): ?>
                  <tr><td colspan="5" class="text-center italic py-6">Nenhum produto cadastrado para esta arena.</td></tr>
                <?php else: ?>
                  <?php foreach ($produtos as $produto): ?>
                    <tr class="product-row" data-name="<?= strtolower(htmlspecialchars($produto['nome'])) ?>">
                      <td>
                        <div class="flex items-center gap-3">
                          <div class="avatar"><div class="mask mask-squircle w-12 h-12"><img src="img/produtos/<?= htmlspecialchars($produto['imagem'] ?: 'default.png') ?>" alt="Imagem do Produto" /></div></div>
                          <div>
                            <div class="font-bold"><?= htmlspecialchars($produto['nome']) ?></div>
                            <div class="text-sm opacity-50 truncate"><?= htmlspecialchars($produto['descricao']) ?></div>
                          </div>
                        </div>
                      </td>
                      <td class="font-mono">R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?></td>
                      <td><?= htmlspecialchars($produto['estoque_calculado']) ?> un.</td>
                      <td><span class="badge <?= $produto['status'] === 'ATIVO' ? 'badge-success' : 'badge-error' ?> text-white badge-sm"><?= htmlspecialchars($produto['status']) ?></span></td>
                      <td><button class="btn btn-ghost btn-xs" onclick='abrirModalEdicao(<?= htmlspecialchars(json_encode($produto), ENT_QUOTES, 'UTF-8') ?>)'>Editar</button></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <p id="noResultsMessage" class="text-center italic py-6 hidden">Nenhum produto encontrado com o termo buscado.</p>
        <?php else: ?>
          <div class="text-center bg-white p-8 rounded-xl shadow-md border border-gray-200">
              <h3 class="mt-2 text-sm font-medium text-gray-900">Selecione uma arena</h3>
              <p class="mt-1 text-sm text-gray-500">Escolha uma de suas arenas acima para ver e gerenciar o estoque de produtos.</p>
          </div>
        <?php endif; ?>
      </section>
      <br><br><br>
    </main>
  </div>

  <dialog id="modalProduto" class="modal">
    <div class="modal-box">
      <h3 class="font-bold text-lg" id="modalTitle">Adicionar Novo Produto</h3>
      <form id="formProduto" method="POST" action="controller-lojinha/controller-lojinha.php" enctype="multipart/form-data" class="py-4 space-y-4">
        <input type="hidden" name="action" id="modalAction">
        <input type="hidden" name="arena_id" id="modalArenaId">
        <input type="hidden" name="produto_id" id="modalProdutoId">
        
        <div class="form-control">
            <label class="label"><span class="label-text">Nome do Produto</span></label>
            <input type="text" name="nome" id="modalNome" class="input input-bordered w-full" required>
        </div>
        <div class="form-control">
            <label class="label"><span class="label-text">Descrição</span></label>
            <textarea name="descricao" id="modalDescricao" class="textarea textarea-bordered w-full"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="form-control">
                <label class="label"><span class="label-text">Preço de Venda (R$)</span></label>
                <input type="text" name="preco_venda" id="modalPreco" class="input input-bordered w-full" required>
            </div>
            <div class="form-control">
                <label class="label" id="labelEstoque"><span class="label-text">Estoque Inicial</span></label>
                <input type="number" name="estoque" id="modalEstoque" class="input input-bordered w-full" required>
            </div>
        </div>
        <div class="form-control">
            <label class="label"><span class="label-text">Status do Produto</span></label>
            <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200 shadow-sm">
                <span class="text-gray-700 font-medium">Inativo</span>
                <input type="checkbox" id="modalStatus" name="status" class="toggle toggle-lg toggle-success" />
                <span class="text-gray-700 font-medium">Ativo</span>
            </div>
        </div>
        <div class="form-control">
            <label class="label"><span class="label-text">Imagem do Produto (Opcional)</span></label>
            <input type="file" name="imagem" class="file-input file-input-bordered w-full" />
        </div>

        <div class="modal-action">
          <button type="button" class="btn" onclick="modalProduto.close()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
      </form>
    </div>
  </dialog>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
  <script>
    $(document).ready(function() {
        $('#modalPreco').mask('#.##0,00', {reverse: true});
        
        $('#searchInput').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            let visibleRows = 0;
            $('.product-row').each(function() {
                const productName = $(this).data('name');
                if (productName.includes(searchTerm)) {
                    $(this).show();
                    visibleRows++;
                } else {
                    $(this).hide();
                }
            });
            $('#noResultsMessage').toggle(visibleRows === 0);
        });
    });

    const modalEstoqueInput = document.getElementById('modalEstoque');
    const labelEstoque = document.getElementById('labelEstoque');
    const formProduto = document.getElementById('formProduto');
    const modalTitle = document.getElementById('modalTitle');
    const modalAction = document.getElementById('modalAction');
    const modalArenaId = document.getElementById('modalArenaId');
    const modalProdutoId = document.getElementById('modalProdutoId');
    const modalNome = document.getElementById('modalNome');
    const modalDescricao = document.getElementById('modalDescricao');
    const modalPreco = document.getElementById('modalPreco');
    const modalStatus = document.getElementById('modalStatus');

    function abrirModalCriacao(arenaId) {
        formProduto.reset();
        modalTitle.innerText = 'Adicionar Novo Produto';
        modalAction.value = 'criar';
        modalArenaId.value = arenaId;
        modalProdutoId.value = '';
        modalStatus.checked = true;
        
        // Habilita e ajusta para "Estoque Inicial"
        modalEstoqueInput.disabled = false;
        labelEstoque.innerText = 'Estoque Inicial';
        modalEstoqueInput.placeholder = 'Qtd. inicial';

        modalProduto.showModal();
    }

    function abrirModalEdicao(produto) {
        formProduto.reset();
        modalTitle.innerText = 'Editar Produto: ' + produto.nome;
        modalAction.value = 'editar';
        modalArenaId.value = produto.arena_id;
        modalProdutoId.value = produto.id;
        modalNome.value = produto.nome;
        modalDescricao.value = produto.descricao;
        modalPreco.value = parseFloat(produto.preco_venda).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
        modalStatus.checked = (produto.status === 'ATIVO');
        
        // Desabilita campo de estoque e mostra o valor calculado
        modalEstoqueInput.value = produto.estoque_calculado; // Mostra o estoque atual
        modalEstoqueInput.disabled = true;
        labelEstoque.innerText = 'Estoque Atual (Calculado)';
        
        modalProduto.showModal();
    }
  </script>

</body>
</html>