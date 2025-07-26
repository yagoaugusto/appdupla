<?php
// Define as páginas que pertencem a cada menu para mantê-los abertos
$paginas_torneio = ['criar-torneio.php', 'meus-torneios.php', 'encontrar-torneio.php', 'gerenciar-torneio.php'];
$is_pagina_torneio_ativa = in_array(basename($_SERVER['PHP_SELF']), $paginas_torneio);

$paginas_central_dupla = ['dupla.php', 'hist-partidas.php', 'ranking-geral.php', 'mvp.php']; // Adicionado hist-partidas e ranking-geral
$is_pagina_central_dupla_ativa = in_array(basename($_SERVER['PHP_SELF']), $paginas_central_dupla);

// Novas páginas para o menu Quadras
$paginas_quadras = ['criar-quadra.php', 'funcionamento-quadra.php', 'agendamento-quadra.php', 'relatorios-quadra.php'];
$is_pagina_quadras_ativa = in_array(basename($_SERVER['PHP_SELF']), $paginas_quadras);

// Novas páginas para o menu Loja
$paginas_loja = ['produtos.php', 'venda.php', 'relatorios.php', 'entradas.php'];
$is_pagina_loja_ativa = in_array(basename($_SERVER['PHP_SELF']), $paginas_loja);

// Novas páginas para o menu Turmas
$paginas_turma = ['turmas.php'];
$is_pagina_turma_ativa = in_array(basename($_SERVER['PHP_SELF']), $paginas_turma);

// Novas páginas para o menu Alunos
$paginas_alunos = ['cadastro-aluno.php', 'gestao_alunos.php', 'relatorio_turmas.php'];
$is_pagina_alunos_ativa = in_array(basename($_SERVER['PHP_SELF']), $paginas_alunos);

// Novas páginas para o menu Yago
$paginas_yago = ['usuarios-yago.php', 'arenas-yago.php'];
$is_pagina_yago_ativa = in_array(basename($_SERVER['PHP_SELF']), $paginas_yago);

// Novas páginas para o menu Despesas
$paginas_despesas = ['gestao_despesas.php', 'despesa_categorias.php'];
$is_pagina_despesas_ativa = in_array(basename($_SERVER['PHP_SELF']), $paginas_despesas);

$paginas_arena = ['criar-arena.php', 'arenas.php', 'arena-page.php', 'minhas-reservas.php'];
$is_pagina_arena_ativa = in_array(basename($_SERVER['PHP_SELF']), $paginas_arena);
?>
<!-- Sidebar Backdrop (for mobile) -->
<!-- This div will act as an overlay when the sidebar is open on small screens -->
<div id="sidebar-backdrop" class="fixed inset-0 top-16 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>
<!-- The sidebar itself, with height adjustments for large screens -->
<aside id="sidebar" class="w-64 bg-white border-r border-gray-200 p-4 fixed top-16 inset-y-0 left-0 transform -translate-x-full transition-transform duration-300 ease-in-out z-50 lg:translate-x-0 lg:fixed lg:top-16 lg:h-screen lg:-mt-16 lg:pt-16 overflow-y-auto">  <nav class="flex flex-col space-y-0.5">
    <a href="principal.php" class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition text-gray-700 text-sm font-medium <?= basename($_SERVER['PHP_SELF']) == 'principal.php' ? 'bg-gray-100' : '' ?>">
      <span class="text-lg">🏠</span>
      <span class="whitespace-nowrap">Início</span>
    </a>

    <!-- Menu Dropdown Central DUPLA -->
    <div class="collapse collapse-arrow">
      <input type="checkbox" <?= $is_pagina_central_dupla_ativa ? 'checked' : '' ?> />
      <div class="collapse-title flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition text-gray-700 text-sm font-medium">
        <span class="text-lg">🔥</span>
        <span class="whitespace-nowrap">DUPLA</span>
      </div>
      <div class="collapse-content !p-0">
        <ul class="menu menu-sm bg-base-100 rounded-box -mt-2 space-y-0.5">
          <li><a href="hist-partidas.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'hist-partidas.php' ? 'active' : '' ?>">Partidas</a></li>
          <li><a href="ranking-geral.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'ranking-geral.php' ? 'active' : '' ?>">Ranking</a></li>
          <li><a href="dupla.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'dupla.php' ? 'active' : '' ?>">Central DUPLA</a></li>
          <li><a href="mvp.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'mvp.php' ? 'active' : '' ?>">MVP Player</a></li>
          <li><a href="#" class="text-gray-700 px-3 py-1.5">Conquistas</a></li>
        </ul>
      </div>
    </div>

    <!-- Menu Dropdown Torneios -->
    <div class="collapse collapse-arrow">
      <input type="checkbox" <?= $is_pagina_torneio_ativa ? 'checked' : '' ?> /> 
      <div class="collapse-title flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition text-gray-700 text-sm font-medium">
        <span class="text-lg">🏆</span>
        <span class="whitespace-nowrap">Torneios</span>
      </div>
      <div class="collapse-content !p-0"> 
        <ul class="menu menu-sm bg-base-100 rounded-box -mt-2 space-y-0.5">
          <?php if (isset($_SESSION['DuplaUserTipo']) && in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])): ?>
            <li><a href="criar-torneio.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'criar-torneio.php' ? 'active' : '' ?>">Criar Torneio</a></li>
          <?php endif; ?>
          <li><a href="meus-torneios.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'meus-torneios.php' ? 'active' : '' ?>">Meus Torneios</a></li>
          <li><a href="encontrar-torneio.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'encontrar-torneio.php' ? 'active' : '' ?>">Encontrar Torneios</a></li>
        </ul>
      </div>
    </div>

    <!-- Menu Dropdown Arenas -->
    <div class="collapse collapse-arrow">
      <input type="checkbox" <?= $is_pagina_arena_ativa ? 'checked' : '' ?> /> 
      <div class="collapse-title flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition text-gray-700 text-sm font-medium">
        <span class="text-lg">🏟️</span>
        <span class="whitespace-nowrap">Arenas</span>
      </div>
      <div class="collapse-content !p-0"> 
        <ul class="menu menu-sm bg-base-100 rounded-box -mt-2 space-y-0.5">
          <li><a href="criar-arena.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'criar-arena.php' ? 'active' : '' ?>">Criar Arena</a></li>
          <li><a href="arenas.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'arenas.php' ? 'active' : '' ?>">Visitar Arenas</a></li>
          <li><a href="minhas-reservas.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'minhas-reservas.php' ? 'active' : '' ?>">Minhas Reservas</a></li>
        </ul>
      </div>
    </div>

    <!-- Menu Dropdown Sistema -->
    <div class="collapse collapse-arrow">
      <?php $paginas_sistema = ['parceiros.php', 'comofunciona.php', 'perfil.php', 'system-autenticacao/sair.php']; ?>
      <input type="checkbox" <?= in_array(basename($_SERVER['PHP_SELF']), $paginas_sistema) ? 'checked' : '' ?> />
      <div class="collapse-title flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition text-gray-700 text-sm font-medium">
        <span class="text-lg">⚙️</span>
        <span class="whitespace-nowrap">Sistema</span>
      </div>
      <div class="collapse-content !p-0">
        <ul class="menu menu-sm bg-base-100 rounded-box -mt-2 space-y-0.5">
          <li><a href="parceiros.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'parceiros.php' ? 'active' : '' ?>">Parceiros</a></li>
          <li><a href="comofunciona.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'comofunciona.php' ? 'active' : '' ?>">Como Funciona</a></li>
          <li><a href="perfil.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : '' ?>">Perfil</a></li>
          <li><a href="system-autenticacao/sair.php" class="text-red-600 px-3 py-1.5 hover:bg-red-100 transition text-sm font-semibold">Sair</a></li>
        </ul>
      </div>
    </div>

<hr>
<?php if (isset($_SESSION['DuplaUserTipo']) && ($_SESSION['DuplaUserTipo'] === 'gestor' || $_SESSION['DuplaUserTipo'] === 'super')): ?>
    <!-- Menu Dropdown Quadras -->
    <div class="collapse collapse-arrow">
      <input type="checkbox" <?= $is_pagina_quadras_ativa ? 'checked' : '' ?> /> 
      <div class="collapse-title flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition text-gray-700 text-sm font-medium">
        <span class="text-lg">🎾</span> <!-- Icone para Quadras -->
        <span class="whitespace-nowrap">Quadras</span>
      </div>
      <div class="collapse-content !p-0"> 
        <ul class="menu menu-sm bg-base-100 rounded-box -mt-2 space-y-0.5">
          <li><a href="criar-quadra.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'criar-quadra.php' ? 'active' : '' ?>">Criar Quadra</a></li>
          <li><a href="funcionamento-quadra.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'funcionamento-quadra.php' ? 'active' : '' ?>">Funcionamento</a></li>
          <li><a href="agendamento-quadra.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'agendamento-quadra.php' ? 'active' : '' ?>">Agendamento</a></li>
          <li><a href="relatorios-quadra.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'relatorios-quadra.php' ? 'active' : '' ?>">Relatórios</a></li>
        </ul>
      </div>
    </div>

    <!-- Menu Dropdown Turmas -->
    <div class="collapse collapse-arrow">
      <input type="checkbox" <?= $is_pagina_turma_ativa ? 'checked' : '' ?> /> 
      <div class="collapse-title flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition text-gray-700 text-sm font-medium">
        <span class="text-lg">🧑‍🎓</span> <!-- Icone para Turmas -->
        <span class="whitespace-nowrap">Turmas</span>
      </div>
      <div class="collapse-content !p-0"> 
        <ul class="menu menu-sm bg-base-100 rounded-box -mt-2 space-y-0.5">
          <li><a href="turmas.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'turmas.php' ? 'active' : '' ?>">Turmas</a></li>
          <li><a href="gestao_alunos.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'gestao_alunos.php' ? 'active' : '' ?>">Alunos</a></li>
          <li><a href="relatorio_turmas.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'relatorio_turmas.php' ? 'active' : '' ?>">Relatórios</a></li>
          <li><a href="gestao_repasses.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'gestao_repasses.php' ? 'active' : '' ?>">Repasses</a></li>
        </ul>
      </div>
    </div>
    <?php endif; ?>

    <!-- Menu Dropdown Loja -->
    <div class="collapse collapse-arrow">
      <?php $paginas_loja = ['cadastro.php', 'estoque.php', 'venda.php', 'relatorios.php']; ?>
      <input type="checkbox" <?= in_array(basename($_SERVER['PHP_SELF']), $paginas_loja) ? 'checked' : '' ?> />
      <div class="collapse-title flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition text-gray-700 text-sm font-medium">
        <span class="text-lg">🛒</span>
        <span class="whitespace-nowrap">Loja</span>
      </div>
      <div class="collapse-content !p-0">
        <ul class="menu menu-sm bg-base-100 rounded-box -mt-2 space-y-0.5">
          <li><a href="estoque.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'estoque.php' ? 'active' : '' ?>">Produtos</a></li>
          <li><a href="venda.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'venda.php' ? 'active' : '' ?>">Venda</a></li>
          <li><a href="entradas.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'entradas.php' ? 'active' : '' ?>">Entrada</a></li>
          <li><a href="relatorios.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'relatorios.php' ? 'active' : '' ?>">Relatórios</a></li>
        </ul>
      </div>
    </div>

    <!-- Menu Dropdown Fiscal -->
    <div class="collapse collapse-arrow">
      <?php $paginas_fiscal = ['notas-emitidas.php', 'relatorios.php']; ?>
      <input type="checkbox" <?= in_array(basename($_SERVER['PHP_SELF']), $paginas_fiscal) ? 'checked' : '' ?> />
      <div class="collapse-title flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition text-gray-700 text-sm font-medium">
        <span class="text-lg">📄</span>
        <span class="whitespace-nowrap">Fiscal</span>
      </div>
      <div class="collapse-content !p-0">
        <ul class="menu menu-sm bg-base-100 rounded-box -mt-2 space-y-0.5">
          <li><a href="notas-emitidas.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'notas-emitidas.php' ? 'active' : '' ?>">Notas Emitidas</a></li>
          <li><a href="relatorios.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'relatorios.php' ? 'active' : '' ?>">Relatórios</a></li>
        </ul>
      </div>
    </div>

    <!-- Menu Dropdown Despesas -->
    <div class="collapse collapse-arrow">
      <?php $paginas_despesas = ['gestao_despesas.php', 'despesa_categorias.php']; ?>
      <input type="checkbox" <?= in_array(basename($_SERVER['PHP_SELF']), $paginas_despesas) ? 'checked' : '' ?> />
      <div class="collapse-title flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition text-gray-700 text-sm font-medium">
        <span class="text-lg">💰</span>
        <span class="whitespace-nowrap">Despesas</span>
      </div>
      <div class="collapse-content !p-0">
        <ul class="menu menu-sm bg-base-100 rounded-box -mt-2 space-y-0.5">
          <li><a href="gestao_despesas.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'gestao_despesas.php' ? 'active' : '' ?>">Lançamentos</a></li>
          <li><a href="despesa_categorias.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'despesa_categorias.php' ? 'active' : '' ?>">Categorias</a></li>
        </ul>
      </div>
    </div>

    <?php if (isset($_SESSION['DuplaUserTipo']) && $_SESSION['DuplaUserTipo'] === 'super'): ?>
    <!-- Menu Dropdown Yago -->
    <div class="collapse collapse-arrow">
      <input type="checkbox" <?= $is_pagina_yago_ativa ? 'checked' : '' ?> /> 
      <div class="collapse-title flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition text-gray-700 text-sm font-medium">
        <span class="text-lg">👑</span> <!-- Icone para Yago -->
        <span class="whitespace-nowrap">Yago</span>
      </div>
      <div class="collapse-content !p-0"> 
        <ul class="menu menu-sm bg-base-100 rounded-box -mt-2 space-y-0.5">
          <li><a href="usuarios-yago.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'usuarios-yago.php' ? 'active' : '' ?>">Usuários</a></li>
          <li><a href="arenas-yago.php" class="text-gray-700 px-3 py-1.5 <?= basename($_SERVER['PHP_SELF']) == 'arenas-yago.php' ? 'active' : '' ?>">Arenas</a></li>
        </ul>
      </div>
    </div>

    <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br>
    <?php endif; ?>

  </nav>
</aside>

<script>
  // Função para alternar a visibilidade da sidebar em telas pequenas
  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');

    if (sidebar && backdrop) {
      sidebar.classList.toggle('-translate-x-full'); // Move a sidebar para dentro/fora da tela
      backdrop.classList.toggle('hidden'); // Mostra/esconde o overlay
    }
  }
</script>
