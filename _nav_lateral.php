<aside id="sidebar" class="w-full sm:w-64 bg-white border-r border-gray-200 p-4 sm:block hidden fixed sm:relative z-50">
  <nav class="flex flex-col space-y-2">
    <a href="principal.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'principal.php' ? 'bg-gray-100' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3z"/></svg>
      <span class="whitespace-nowrap">Início</span>
    </a>
    <a href="hist-partidas.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'hist-partidas.php' ? 'bg-gray-100' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
      <span class="whitespace-nowrap">Partidas</span>
    </a>
    <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
      <span class="whitespace-nowrap">Conquistas</span>
    </a>
    <a href="parceiros.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'parceiros.php' ? 'bg-gray-100' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m9-10V11a3 3 0 11-6 0v-1a3 3 0 016 0z"/></svg>
      <span class="whitespace-nowrap">Parceiros</span>
    </a>
    <a href="ranking-geral.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'ranking-geral.php' ? 'bg-gray-100' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4m8 0a5 5 0 015 5v1H3v-1a5 5 0 015-5z"/></svg>
      <span class="whitespace-nowrap">Ranking</span>
    </a>
    <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
      <span class="whitespace-nowrap">Perfil</span>
    </a>
    <hr class="my-2">
    <a href="comofunciona.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'comofunciona.php' ? 'bg-gray-100' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 1.076-2.48 1.076-4C9.304 2.135 10.398 1 12 1s2.696 1.135 2.696 4c0 1.52-.527 2.835-1.076 4m-1.048 9a9 9 0 01-9 9h.213l2.631-.827c.112-.035.21-.05.31-.05 1.79 0 3.144 1.047 3.634 2.35l.467 1.118c.076.183.145.37.195.562a10 10 0 009.894-10H17.5a9 9 0 01-.03-.824l.044-.125a.5.5 0 01.197-.25h2.159a11 11 0 00-11 11z"/></svg>
      <span class="whitespace-nowrap">Como funciona</span>
    </a>
    <hr class="my-2">
    <a href="system-autenticacao/sair.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-red-100 transition text-red-600 font-semibold">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      <span class="whitespace-nowrap">Sair</span>
    </a>
  </nav>
</aside>