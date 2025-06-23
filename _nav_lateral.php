<?php
// Define as páginas que pertencem a cada menu para mantê-los abertos
$paginas_torneio = ['criar-torneio.php', 'meu-torneio.php', 'encontrar-torneio.php', 'gerenciar-torneio.php'];
$is_pagina_torneio_ativa = in_array(basename($_SERVER['PHP_SELF']), $paginas_torneio);

$paginas_arena = ['criar-arena.php', 'arenas.php', 'arena-page.php'];
$is_pagina_arena_ativa = in_array(basename($_SERVER['PHP_SELF']), $paginas_arena);
?>

<aside id="sidebar" class="w-full sm:w-64 bg-white border-r border-gray-200 p-4 sm:block hidden fixed sm:relative z-50 shadow-lg">
  <nav class="flex flex-col space-y-1">
    <a href="principal.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'principal.php' ? 'bg-gray-100' : '' ?>">
      <span class="text-xl">🏠</span>
      <span class="whitespace-nowrap">Início</span>
    </a>
    <a href="hist-partidas.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'hist-partidas.php' ? 'bg-gray-100' : '' ?>">
      <span class="text-xl">📝</span>
      <span class="whitespace-nowrap">Partidas</span>
    </a>

    <!-- Menu Dropdown Torneios -->
    <div class="collapse collapse-arrow">
      <input type="checkbox" <?= $is_pagina_torneio_ativa ? 'checked' : '' ?> /> 
      <div class="collapse-title flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium">
        <span class="text-xl">🏆</span>
        <span class="whitespace-nowrap">Torneios</span>
      </div>
      <div class="collapse-content !p-0"> 
        <ul class="menu menu-sm bg-base-100 rounded-box -mt-2">
          <li><a href="criar-torneio.php" class="<?= basename($_SERVER['PHP_SELF']) == 'criar-torneio.php' ? 'active' : '' ?>">Criar Torneio</a></li>
          <li><a href="#" class="<?= basename($_SERVER['PHP_SELF']) == '#' ? 'active' : '' ?>">Meus Torneios</a></li>
          <li><a href="encontrar-torneio.php" class="<?= basename($_SERVER['PHP_SELF']) == 'encontrar-torneio.php' ? 'active' : '' ?>">Encontrar Torneios</a></li>
        </ul>
      </div>
    </div>

    <!-- Menu Dropdown Arenas -->
    <div class="collapse collapse-arrow">
      <input type="checkbox" <?= $is_pagina_arena_ativa ? 'checked' : '' ?> /> 
      <div class="collapse-title flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium">
        <span class="text-xl">🏟️</span>
        <span class="whitespace-nowrap">Arenas</span>
      </div>
      <div class="collapse-content !p-0"> 
        <ul class="menu menu-sm bg-base-100 rounded-box -mt-2">
          <li><a href="criar-arena.php" class="<?= basename($_SERVER['PHP_SELF']) == 'criar-arena.php' ? 'active' : '' ?>">Criar Arena</a></li>
          <li><a href="arenas.php" class="<?= basename($_SERVER['PHP_SELF']) == 'arenas.php' ? 'active' : '' ?>">Visitar Arenas</a></li>
        </ul>
      </div>
    </div>

    <a href="parceiros.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'parceiros.php' ? 'bg-gray-100' : '' ?>">
      <span class="text-xl">🤝</span>
      <span class="whitespace-nowrap">Parceiros</span>
    </a>
    <a href="ranking-geral.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'ranking-geral.php' ? 'bg-gray-100' : '' ?>">
      <span class="text-xl">📊</span>
      <span class="whitespace-nowrap">Ranking</span>
    </a>
    <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium">
      <span class="text-xl">🌟</span>
      <span class="whitespace-nowrap">Conquistas</span>
    </a>
    <hr class="my-2">
    <a href="comofunciona.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'comofunciona.php' ? 'bg-gray-100' : '' ?>">
      <span class="text-xl">❓</span>
      <span class="whitespace-nowrap">Como Funciona</span>
    </a>
    <a href="perfil.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'bg-gray-100' : '' ?>">
      <span class="text-xl">👤</span>
      <span class="whitespace-nowrap">Perfil</span>
    </a>
    <a href="system-autenticacao/sair.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-red-100 transition text-red-600 font-semibold">
      <span class="text-xl">🚪</span>
      <span class="whitespace-nowrap">Sair</span>
    </a>
  </nav>
</aside>
