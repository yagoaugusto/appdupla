<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<body class="bg-gradient-to-br from-blue-100 via-white to-purple-100 min-h-screen text-gray-800">

  <!-- Navbar superior -->
  <?php require_once '_nav_superior.php' ?>

  <div class="flex pt-16">
    <!-- Menu lateral -->
    <?php require_once '_nav_lateral.php' ?>

    <!-- Conteúdo principal -->
    <main class="flex-1 flex flex-col min-h-screen">
      <section class="flex-grow p-6 md:p-12">
        <div class="max-w-3xl mx-auto bg-white/90 rounded-3xl shadow-2xl p-8 md:p-12 border border-blue-100">
          <div class="flex items-center gap-4 mb-6">
            <img src="https://img.icons8.com/color/96/leaderboard.png" alt="Ranking" class="w-16 h-16">
            <h1 class="text-4xl font-extrabold text-blue-700 drop-shadow-lg">Como funciona o ranking no DUPLA?</h1>
          </div>

          <p class="text-gray-700 text-lg mb-8">
            O DUPLA utiliza o <span class="font-bold text-blue-600">Glicko-2</span>, um sistema moderno que avalia seu desempenho de forma <span class="font-bold text-purple-700">justa</span>, <span class="font-bold text-green-700">inteligente</span> e <span class="font-bold text-yellow-600">dinâmica</span>.
          </p>

          <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div class="bg-blue-50 border-l-8 border-blue-400 p-6 rounded-xl shadow">
              <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">🎯 O que é o <span class="text-blue-700">Glicko-2</span>?</h2>
              <p class="text-gray-800">É um sistema matemático que calcula seu nível de habilidade com base nos seus resultados e nos adversários que você enfrenta.</p>
            </div>
            <div class="bg-green-50 border-l-8 border-green-400 p-6 rounded-xl shadow">
              <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">📈 Por que é melhor?</h2>
              <ul class="list-disc list-inside space-y-1 text-gray-800">
                <li>Considera a força dos adversários</li>
                <li>Avalia o número de partidas jogadas</li>
                <li>Premia vitórias surpreendentes</li>
                <li>Reduz confiança no rating de quem joga pouco</li>
              </ul>
            </div>
          </div>

          <div class="bg-yellow-50 border-l-8 border-yellow-400 p-6 rounded-xl shadow mb-8">
            <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">🤔 Exemplo prático</h2>
            <div class="flex flex-col md:flex-row items-center gap-4">
              <div class="flex-1">
                <p class="mb-2">Imagine:</p>
                <ul class="list-disc list-inside text-gray-800">
                  <li><span class="font-bold text-blue-700">Você</span> tem <span class="font-bold">1300 pontos</span></li>
                  <li>Vence uma dupla com <span class="font-bold text-green-700">1700 pontos</span> ➔ <span class="font-bold text-green-700">Seu ranking sobe bastante!</span></li>
                  <li>Perde para uma dupla com <span class="font-bold text-red-700">1100 pontos</span> ➔ <span class="font-bold text-red-700">Seu ranking cai mais do que o normal.</span></li>
                </ul>
              </div>
              <img src="https://img.icons8.com/color/96/trophy.png" alt="Troféu" class="w-20 h-20">
            </div>
          </div>

          <div class="bg-purple-50 border-l-8 border-purple-400 p-6 rounded-xl shadow mb-8">
            <h2 class="text-xl font-semibold mb-2 flex items-center gap-2">🚀 Vantagens para você</h2>
            <ul class="list-disc list-inside space-y-1 text-gray-800">
              <li><span class="font-bold text-purple-700">Ranking mais justo</span></li>
              <li>Mais motivação para jogar e evoluir</li>
              <li>Transparência nas partidas e resultados</li>
              <li>Combate jogos arranjados ou registros falsos</li>
            </ul>
          </div>

          <div class="text-center mt-8">
            <a href="ranking.php" class="inline-block bg-blue-700 hover:bg-blue-800 text-white font-bold py-3 px-8 rounded-full shadow-lg text-lg transition-all duration-200">
              Ver Ranking Atual
            </a>
          </div>
        </div>
      </section>

      <!-- Footer -->
      <?php require_once '_footer.php' ?>
    </main>
  </div>

</body>
</html>