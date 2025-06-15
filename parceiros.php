<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<body class="bg-gray-100 min-h-screen text-gray-800">

<?php require_once '_nav_superior.php'; ?>
<div class="flex pt-16">
  <?php require_once '_nav_lateral.php'; ?>
  <?php
  $parceiro =  Parceiros::listar_parceiros();
  ?>
  <main class="flex-1 p-4">
    <h1 class="text-2xl sm:text-3xl font-black mb-6 tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-pink-500 to-yellow-400 flex items-center gap-3 drop-shadow-lg">
      <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-pink-400 shadow-lg mr-2">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M12 8v4l3 3M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </span>
      Parceiros Oficiais
    </h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <!-- Exemplo de cartão de parceiro -->
      <?php foreach ($parceiro as $p): ?>
      <div class="bg-white rounded-2xl shadow-xl flex flex-col items-center p-4 border-t-4 border-blue-500 relative overflow-hidden">
        <div class="absolute right-2 top-2 bg-gradient-to-r from-blue-500 to-pink-500 text-white px-2 py-0.5 rounded-full text-xs font-bold shadow">PARCEIRO</div>
        <img src="img/<?= $p['imagem'] ?>" alt="Logo do Parceiro" class="w-20 h-20 rounded-full shadow-lg border-4 border-blue-100 mb-2">
        <div class="text-lg font-bold text-blue-700 mb-1"><?= $p['titulo'] ?></div>
        <div class="text-xs text-gray-600 text-center mb-2 px-2"><?= $p['descricao'] ?></div>
        <div class="flex gap-2 mt-2">
          <a href="<?= $p['instagram'] ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 bg-gradient-to-r from-pink-500 to-yellow-400 text-white rounded-full text-xs font-semibold shadow hover:scale-105 transition">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm0 1.5A4.25 4.25 0 0 0 3.5 7.75v8.5A4.25 4.25 0 0 0 7.75 20.5h8.5A4.25 4.25 0 0 0 20.5 16.25v-8.5A4.25 4.25 0 0 0 16.25 3.5zm4.25 2.25a5.25 5.25 0 1 1 0 10.5a5.25 5.25 0 0 1 0-10.5zm0 1.5a3.75 3.75 0 1 0 0 7.5a3.75 3.75 0 0 0 0-7.5zm5.25 1.25a1 1 0 1 1-2 0a1 1 0 0 1 2 0z"/></svg>
            Instagram
          </a>
          <a href="<?= $p['instagram'] ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 bg-gradient-to-r from-blue-500 to-green-400 text-white rounded-full text-xs font-semibold shadow hover:scale-105 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7-7 7M5 5v14"/></svg>
            Visitar
          </a>
        </div>
      </div>
      <?php endforeach; ?>
      <!-- Repita o bloco acima para outros parceiros -->
    </div>
  </main>
</div>
<footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
    DUPLA - Deu Game? Dá Ranking!
</footer>
</body>
</html>