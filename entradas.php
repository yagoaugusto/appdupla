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

$arena_id_selecionada = filter_input(INPUT_GET, 'arena_id', FILTER_VALIDATE_INT);
$produtos = [];
$historico = [];
if ($arena_id_selecionada) {
    // Usamos getProdutosPorArena pois podemos querer dar entrada em um produto inativo
    $produtos = Lojinha::getProdutosPorArena($arena_id_selecionada); 
    $historico = Lojinha::getHistoricoEntradas($arena_id_selecionada);
}
?>

<body class="bg-gray-100 min-h-screen text-gray-800">

  <?php require_once '_nav_superior.php' ?>
  <div class="flex pt-16">
    <?php require_once '_nav_lateral.php' ?>
    <main class="flex-1 p-4 sm:p-6">
      <section class="max-w-7xl mx-auto w-full">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-800 tracking-tight">Entrada de Estoque</h1>
        </div>
        
        <?php
        if (isset($_SESSION['mensagem'])) {
            [$tipo, $texto] = $_SESSION['mensagem'];
            $alert_class = ($tipo === 'success') ? 'alert-success' : 'alert-error';
            echo "<div class='alert {$alert_class} shadow-lg mb-5'><div><span>" . htmlspecialchars($texto) . "</span></div></div>";
            unset($_SESSION['mensagem']);
        }
        ?>

        <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200 mb-6">
          <form method="GET" action="entradas.php">
            <div class="form-control">
              <label class="label"><span class="label-text">Selecione a Arena</span></label>
              <select name="arena_id" class="select select-bordered" onchange="this.form.submit()">
                <option value="">Escolha uma Arena</option>
                <?php foreach ($arenas_gestor as $arena): ?>
                  <option value="<?= $arena['id'] ?>" <?= ($arena_id_selecionada == $arena['id']) ? 'selected' : '' ?>><?= htmlspecialchars($arena['titulo']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </form>
        </div>

        <?php if ($arena_id_selecionada): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200 sticky top-20">
                        <h2 class="text-xl font-bold mb-4">Registrar Nova Entrada</h2>
                        <form method="POST" action="controller-lojinha/entrada_controller.php" class="space-y-4">
                            <input type="hidden" name="arena_id" value="<?= $arena_id_selecionada ?>">
                            
                            <div class="form-control">
                                <label class="label"><span class="label-text">Produto</span></label>
                                <select name="produto_id" class="select select-bordered" required>
                                    <option value="" disabled selected>Selecione um produto</option>
                                    <?php foreach ($produtos as $produto): ?>
                                    <option value="<?= $produto['id'] ?>"><?= htmlspecialchars($produto['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Quantidade</span></label>
                                    <input type="number" name="quantidade" placeholder="Ex: 10" class="input input-bordered w-full" required min="1">
                                </div>
                                <div class="form-control">
                                    <label class="label"><span class="label-text">Custo Unitário (R$)</span></label>
                                    <input type="text" name="custo_unitario" id="custoUnitario" placeholder="Opcional" class="input input-bordered w-full">
                                </div>
                            </div>
                            
                            <div class="form-control">
                                <label class="label"><span class="label-text">Motivo / Observação</span></label>
                                <textarea name="motivo" class="textarea textarea-bordered" placeholder="Ex: Compra semanal, ajuste de inventário..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary w-full mt-6">Registrar Entrada</button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-200">
                        <h2 class="text-xl font-bold mb-4 px-2">Histórico de Entradas Recentes</h2>
                        <div class="overflow-x-auto max-h-[70vh]">
                            <table class="table w-full">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Custo Unit.</th>
                                        <th>Motivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($historico)): ?>
                                        <tr><td colspan="5" class="text-center italic py-6">Nenhuma entrada registrada para esta arena.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($historico as $entrada): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($entrada['data'])) ?></td>
                                            <td><?= htmlspecialchars($entrada['produto_nome']) ?></td>
                                            <td class="font-bold text-success">+<?= htmlspecialchars($entrada['quantidade']) ?></td>
                                            <td class="font-mono">R$ <?= number_format($entrada['custo_unitario'] ?? 0, 2, ',', '.') ?></td>
                                            <td><?= htmlspecialchars($entrada['motivo']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="text-center bg-white p-8 rounded-xl shadow-md border border-gray-200">
                <h3 class="mt-2 text-lg font-medium text-gray-900">Comece selecionando uma arena</h3>
                <p class="mt-1 text-sm text-gray-500">Escolha uma de suas arenas no menu acima para gerenciar as entradas de estoque.</p>
            </div>
        <?php endif; ?>
      </section>
      <br><br><br>
    </main>
  </div>
  
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
  <script>
    $(document).ready(function(){
        $('#custoUnitario').mask('#.##0,00', {reverse: true});
    });
  </script>

</body>
</html>