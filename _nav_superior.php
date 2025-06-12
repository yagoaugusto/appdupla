  <div class="navbar bg-white shadow-md px-4 fixed w-full z-10">
    <div class="flex-1">
      <button class="btn btn-ghost lg:hidden" onclick="document.getElementById('sidebar').classList.toggle('hidden')">
        ☰
      </button>
      <a class="text-xl font-bold">DUPLA</a>
    </div>
    <div class="flex-none hidden lg:block">
      <a href="#" class="btn btn-ghost"><?= $_SESSION['DuplaUserNome'] ?></a>
      <a href="system-autenticacao/sair.php" class="btn btn-ghost">Sair</a>
    </div>
  </div>