<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<body class="bg-gray-100 min-h-screen text-gray-800">

  <?php require_once '_nav_superior.php' ?>

  <div class="flex pt-16">
    <?php require_once '_nav_lateral.php' ?>

    <main class="flex-1 flex flex-col min-h-screen p-4">
      <section class="max-w-xl w-full mx-auto bg-white shadow-md rounded-xl p-6">
        <h2 class="text-2xl font-semibold mb-4 text-center">Editar Perfil</h2>
        <form action="salvar_perfil.php" method="POST" enctype="multipart/form-data" class="space-y-4">

          <!-- Foto de perfil -->
          <div class="text-center">
            <label for="foto" class="block mb-2 text-sm font-medium">Foto de Perfil</label>
            <input type="file" name="foto" id="foto" accept="image/*" class="mx-auto file:py-2 file:px-4 file:border-0 file:bg-blue-600 file:text-white file:rounded-full">
          </div>

          <!-- Nome -->
          <div>
            <label class="block text-sm font-medium mb-1">Nome Completo</label>
            <input type="text" name="nome" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 p-3" required>
          </div>

          <!-- Nickname -->
          <div>
            <label class="block text-sm font-medium mb-1">Nickname</label>
            <input type="text" name="nickname" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 p-3" required>
          </div>

          <!-- Arena principal -->
          <div>
            <label class="block text-sm font-medium mb-1">Arena Principal</label>
            <input type="text" name="arena" id="arena" list="arenas" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 p-3" placeholder="Digite sua arena...">
            <datalist id="arenas">
              <option value="Arena Beira-Mar">
              <option value="CT Beach Club">
              <option value="Arena Salvador Norte">
              <option value="Arena Rio Beach">
            </datalist>
          </div>

          <!-- Telefone -->
          <div>
            <label class="block text-sm font-medium mb-1">Telefone</label>
            <input type="text" name="telefone" value="<?php echo $telefone; ?>" class="w-full rounded-lg border-gray-300 bg-gray-100 cursor-not-allowed p-3" readonly>
          </div>

          <!-- Empunhadura -->
          <div>
            <label class="block text-sm font-medium mb-1">Empunhadura</label>
            <select name="empunhadura" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 p-3">
              <option value="destro">Destro</option>
              <option value="canhoto">Canhoto</option>
            </select>
          </div>

          <!-- Botão -->
          <div class="text-center mt-6">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-xl shadow-md">
              Salvar Alterações
            </button>
          </div>
        </form>
      </section>
    </main>
  </div>

</body>
</html>