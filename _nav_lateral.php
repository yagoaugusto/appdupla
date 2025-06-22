<aside id="sidebar" class="w-full sm:w-64 bg-white border-r border-gray-200 p-4 sm:block hidden fixed sm:relative z-50 shadow-lg">
  <nav class="flex flex-col space-y-2">
    <a href="principal.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'principal.php' ? 'bg-gray-100' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3z"/></svg>
      <span class="whitespace-nowrap">Início</span>
    </a>
    <a href="hist-partidas.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'hist-partidas.php' ? 'bg-gray-100' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
      <span class="whitespace-nowrap">Partidas</span>
    </a>
    <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.329 1.176l1.519 4.674c.3.921-.755 1.688-1.539 1.175l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.784.513-1.83-.254-1.539-1.175l1.519-4.674a1 1 0 00-.329-1.176l-3.976-2.888c-.784-.57-.381-1.81.588-1.81h4.915a1 1 0 00.95-.69l1.519-4.674z"/></svg>
      <span class="whitespace-nowrap">Conquistas</span>
    </a>
    <a href="parceiros.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'parceiros.php' ? 'bg-gray-100' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m9-10V11a3 3 0 11-6 0v-1a3 3 0 016 0z"/></svg>
      <span class="whitespace-nowrap">Parceiros</span>
    </a>
    <a href="ranking-geral.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'ranking-geral.php' ? 'bg-gray-100' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
      <span class="whitespace-nowrap">Ranking</span>
    </a>
    <a href="#" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
      <span class="whitespace-nowrap">Perfil</span>
    </a>
    <hr class="my-2">
    <a href="comofunciona.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-gray-100 transition text-gray-700 font-medium <?= basename($_SERVER['PHP_SELF']) == 'comofunciona.php' ? 'bg-gray-100' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
      <span class="whitespace-nowrap">Como Funciona</span>
    </a>
    <hr class="my-2">
    <a href="system-autenticacao/sair.php" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-red-100 transition text-red-600 font-semibold">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
      <span class="whitespace-nowrap">Sair</span>
    </a>
  </nav>
</aside>
