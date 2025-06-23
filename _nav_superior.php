  <div class="navbar bg-white shadow-md px-4 fixed w-full z-10">
    <div class="flex-1">
      <button class="btn btn-ghost lg:hidden" onclick="toggleSidebar()">
        ☰
      </button>
      <a class="text-xl font-bold">DUPLA</a>
    </div>
    <div class="flex-none hidden lg:block">
      <a href="perfil.php" class="btn btn-ghost">
        <?= htmlspecialchars($_SESSION['DuplaUserNome']) ?>
        <?php if (!empty($_SESSION['DuplaUserApelido'])): ?>
            <span class="text-xs font-normal text-gray-500">(<?= htmlspecialchars($_SESSION['DuplaUserApelido']) ?>)</span>
        <?php endif; ?>
      </a>
      <a href="system-autenticacao/sair.php" class="btn btn-ghost">Sair</a>
    </div>
  </div>