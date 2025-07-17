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
$arenas_gestor = Quadras::getArenasDoGestor($_SESSION['DuplaUserId']);
$arena_id_selecionada = filter_input(INPUT_GET, 'arena_id', FILTER_VALIDATE_INT);
$categorias = [];
if ($arena_id_selecionada) {
    $categorias = Despesa::getCategoriasPorArena($arena_id_selecionada);
}
?>

<body class="bg-gray-100 flex flex-col min-h-screen">

  <?php require_once '_nav_superior.php'; ?>
  <div class="flex flex-1 pt-16">
    <?php require_once '_nav_lateral.php'; ?>
    <main class="flex-1 p-4 sm:p-6">
      <section class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Categorias de Despesas</h1>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-md border mb-6">
            <form method="GET" action="despesa_categorias.php">
                <label class="label"><span class="label-text">Selecione a Arena para gerir as categorias</span></label>
                <select name="arena_id" class="select select-bordered" required onchange="this.form.submit()">
                    <option value="">Escolha uma Arena...</option>
                    <?php foreach ($arenas_gestor as $arena): ?>
                    <option value="<?= $arena['id'] ?>" <?= ($arena_id_selecionada == $arena['id']) ? 'selected' : '' ?>><?= htmlspecialchars($arena['titulo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if ($arena_id_selecionada): ?>
            <div class="flex justify-end mb-4">
                <button class="btn btn-primary" onclick="abrirModal()">Adicionar Nova Categoria</button>
            </div>
            
            <?php // Bloco para exibir mensagens de sucesso/erro ?>
            <?php if (isset($_SESSION['mensagem'])): list($tipo, $texto) = $_SESSION['mensagem']; ?>
                <div class="alert <?= $tipo === 'success' ? 'alert-success' : 'alert-error' ?> shadow-lg mb-5"><div><span><?= htmlspecialchars($texto) ?></span></div></div>
            <?php unset($_SESSION['mensagem']); endif; ?>

            <div class="bg-white p-4 sm:p-6 rounded-xl shadow-md border">
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead><tr><th>Nome</th><th>Descrição</th><th>Status</th><th>Ações</th></tr></thead>
                        <tbody>
                            <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td class="font-bold"><?= htmlspecialchars($cat['nome']) ?></td>
                                <td><?= htmlspecialchars($cat['descricao']) ?></td>
                                <td><span class="badge <?= $cat['status'] == 'ativo' ? 'badge-success' : 'badge-ghost' ?>"><?= ucfirst($cat['status']) ?></span></td>
                                <td><button class="btn btn-xs btn-outline" onclick='abrirModal(<?= htmlspecialchars(json_encode($cat), ENT_QUOTES, 'UTF-8') ?>)'>Editar</button></td>
                            </tr>
                            <?php endforeach; ?>
                             <?php if (empty($categorias)): ?>
                                <tr><td colspan="4" class="text-center italic py-6">Nenhuma categoria criada para esta arena.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

      </section>
    </main>
  </div>

  <dialog id="modalCategoria" class="modal">
        <div class="modal-box">
            <h3 id="modalTitle" class="font-bold text-lg">Adicionar Nova Categoria</h3>
            <form id="formCategoria" method="POST" action="controllers/despesa_controller.php" class="py-4 space-y-4">
                <input type="hidden" id="modalAction" name="action">
                <input type="hidden" id="modalCategoriaId" name="categoria_id">
                <input type="hidden" name="arena_id" value="<?= $arena_id_selecionada ?>">

                <div class="form-control">
                    <label class="label"><span class="label-text">Nome da Categoria</span></label>
                    <input type="text" id="modalNome" name="nome" class="input input-bordered w-full" required>
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">Descrição (Opcional)</span></label>
                    <input type="text" id="modalDescricao" name="descricao" class="input input-bordered w-full">
                </div>
                <div id="statusWrapper" class="form-control hidden">
                    <label class="label"><span class="label-text">Status</span></label>
                    <select id="modalStatus" name="status" class="select select-bordered w-full">
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn" onclick="modalCategoria.close()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        const modal = document.getElementById('modalCategoria');
        const modalTitle = document.getElementById('modalTitle');
        const modalAction = document.getElementById('modalAction');
        const form = document.getElementById('formCategoria');
        
        function abrirModal(data = null) {
            form.reset();
            if (data) {
                // Modo Edição
                modalTitle.innerText = 'Editar Categoria';
                modalAction.value = 'editar_categoria';
                document.getElementById('modalCategoriaId').value = data.id;
                document.getElementById('modalNome').value = data.nome;
                document.getElementById('modalDescricao').value = data.descricao;
                document.getElementById('modalStatus').value = data.status;
                document.getElementById('statusWrapper').classList.remove('hidden');
            } else {
                // Modo Criação
                modalTitle.innerText = 'Adicionar Nova Categoria';
                modalAction.value = 'criar_categoria';
                document.getElementById('modalCategoriaId').value = '';
                document.getElementById('statusWrapper').classList.add('hidden');
            }
            modal.showModal();
        }
    </script>
</body>
</html>