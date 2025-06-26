<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<body class="bg-gray-100 min-h-screen text-gray-800">

  <!-- Navbar superior -->
  <?php require_once '_nav_superior.php' ?>

  <div class="flex pt-16">
    <!-- Menu lateral -->
    <?php require_once '_nav_lateral.php' ?>

    <!-- Conteúdo principal -->
    <main class="flex-1 p-4 sm:p-6">
      <section class="max-w-7xl mx-auto w-full">
        <!-- Cabeçalho da Página -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Gerenciar Quadras</h1>
        </div>

        <?php
        // Exibir mensagens de sucesso ou erro da sessão
        if (isset($_SESSION['mensagem'])) {
            $tipo = $_SESSION['mensagem'][0];
            $texto = $_SESSION['mensagem'][1];
            $alert_class = ($tipo === 'success') ? 'alert-success' : 'alert-error';
            echo "<div class='alert {$alert_class} shadow-lg mb-5'><div><span>" . htmlspecialchars($texto) . "</span></div></div>";
            unset($_SESSION['mensagem']); // Limpa a mensagem após exibir
        }

        $usuario_id = $_SESSION['DuplaUserId'];
        $arenas = Quadras::getArenasDoGestor($usuario_id);
        ?>

        <?php if (empty($arenas)): ?>
          <div class="text-center bg-white p-8 rounded-xl shadow-md border border-gray-200">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma arena encontrada</h3>
            <p class="mt-1 text-sm text-gray-500">Você precisa gerenciar uma arena para poder adicionar quadras.</p>
            <div class="mt-6">
              <a href="criar-arena.php" class="btn btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Criar sua primeira Arena
              </a>
            </div>
          </div>
        <?php else: ?>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <?php foreach ($arenas as $arena): ?>
              <div class="card bg-base-100 shadow-lg border border-gray-200/80">
                <div class="card-body">
                  <h2 class="card-title text-xl font-bold flex items-center gap-3">
                    <span class="text-3xl"><?= htmlspecialchars($arena['bandeira']) ?></span>
                    <?= htmlspecialchars($arena['titulo']) ?>
                  </h2>
                  <p class="text-sm text-gray-500 -mt-2 mb-4">Gerencie as quadras desta arena.</p>
                  
                  <div class="space-y-2 mb-4">
                    <?php $quadras = Quadras::getQuadrasPorArena($arena['id']); ?>
                    <?php if (empty($quadras)): ?>
                      <p class="text-gray-500 italic text-sm p-3 bg-gray-50 rounded-lg">Nenhuma quadra cadastrada.</p>
                    <?php else: ?>
                      <?php foreach ($quadras as $q): ?>
                        <div class="bg-gray-50 p-3 rounded-lg flex justify-between items-center text-sm gap-2">
                          <div class="flex-1">
                            <span class="font-semibold text-gray-800"><?= htmlspecialchars($q['nome']) ?></span>
                            <div class="flex flex-wrap gap-2 mt-1">
                              <?php if ($q['beach_tennis']): ?><span class="badge badge-info badge-outline text-xs">Beach Tênis</span><?php endif; ?>
                              <?php if ($q['volei']): ?><span class="badge badge-success badge-outline text-xs">Vôlei</span><?php endif; ?>
                              <?php if ($q['futvolei']): ?><span class="badge badge-warning badge-outline text-xs">Futevôlei</span><?php endif; ?>
                            </div>
                          </div>
                          <button onclick='abrirModalEdicao(<?= htmlspecialchars(json_encode($q), ENT_QUOTES, 'UTF-8') ?>)' class="btn btn-xs btn-outline">Editar</button>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>

                  <div class="card-actions justify-end mt-4">
                    <button onclick="abrirModalCriacao(<?= $arena['id'] ?>, '<?= htmlspecialchars(addslashes($arena['titulo'])) ?>')" class="btn btn-primary">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                      Adicionar Quadra
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
      <br><br><br>
    </main>
  </div>

  <!-- Modal de Criação/Edição de Quadra (DaisyUI) -->
  <dialog id="modalQuadra" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
      <h3 class="font-bold text-lg" id="modalTitle">Adicionar Nova Quadra</h3>
      <form id="formQuadra" method="POST" action="controller-quadra/salvar-quadra.php" class="py-4 space-y-4">
        <input type="hidden" name="arena_id" id="modalArenaId">
        <input type="hidden" name="quadra_id" id="modalQuadraId">
        <div class="form-control">
          <label class="label"><span class="label-text">Nome da Quadra</span></label>
          <input type="text" id="modalNome" name="nome" placeholder="Ex: Quadra Central" class="input input-bordered w-full" required>
        </div>
        <div class="form-control">
          <label class="label"><span class="label-text">Valor Base por Hora (R$)</span></label>
          <input type="text" id="modalValorBase" name="valor_base" placeholder="Ex: 80,00" class="input input-bordered w-full" required>
        </div>
        <div class="form-control">
          <label class="label"><span class="label-text">Esportes Praticados</span></label>
          <div class="flex flex-wrap gap-x-6 gap-y-2">
            <label class="label cursor-pointer gap-2"><input type="checkbox" id="modalBeachTennis" name="beach_tennis" class="checkbox checkbox-primary" /><span>Beach Tennis</span></label>
            <label class="label cursor-pointer gap-2"><input type="checkbox" id="modalVolei" name="volei" class="checkbox checkbox-primary" /><span>Vôlei</span></label>
            <label class="label cursor-pointer gap-2"><input type="checkbox" id="modalFutvolei" name="futvolei" class="checkbox checkbox-primary" /><span>Futevôlei</span></label>
          </div>
        </div>
        <div class="modal-action">
          <button type="button" onclick="modalQuadra.close()" class="btn">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </div>
      </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
  </dialog>

  <script>
    const formQuadra = document.getElementById('formQuadra');
    const modalTitle = document.getElementById('modalTitle');
    const modalArenaId = document.getElementById('modalArenaId');
    const modalQuadraId = document.getElementById('modalQuadraId');
    const modalNome = document.getElementById('modalNome');
    const modalValorBase = document.getElementById('modalValorBase');
    const modalBeachTennis = document.getElementById('modalBeachTennis');
    const modalVolei = document.getElementById('modalVolei');
    const modalFutvolei = document.getElementById('modalFutvolei');

    function abrirModalCriacao(arenaId, arenaTitulo) {
      formQuadra.reset();
      formQuadra.action = 'controller-quadra/salvar-quadra.php';
      modalTitle.innerText = 'Nova Quadra para: ' + arenaTitulo;
      modalArenaId.value = arenaId;
      modalQuadraId.value = '';
      modalQuadra.showModal();
    }

    function abrirModalEdicao(quadra) {
      formQuadra.reset();
      formQuadra.action = 'controller-quadra/atualizar-quadra.php';
      modalTitle.innerText = 'Editar Quadra: ' + quadra.nome;
      modalArenaId.value = quadra.arena_id;
      modalQuadraId.value = quadra.id;
      modalNome.value = quadra.nome;
      modalValorBase.value = parseFloat(quadra.valor_base).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
      modalBeachTennis.checked = quadra.beach_tennis == 1;
      modalVolei.checked = quadra.volei == 1;
      modalFutvolei.checked = quadra.futvolei == 1;
      modalQuadra.showModal();
    }
  </script>

</body>
</html>