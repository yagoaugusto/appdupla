<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<body class="bg-gray-100 min-h-screen text-gray-800">

  <?php require_once '_nav_superior.php'; ?>

  <div class="flex pt-16">
    <?php require_once '_nav_lateral.php'; ?>

    <main class="flex-1 flex flex-col min-h-screen p-6">
      <section class="bg-white rounded-xl shadow-md p-6 max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold mb-4 text-center text-green-600">Partida validada com sucesso! ⭐</h2>

        <!-- Resultado da partida -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-8">
          <div class="grid grid-cols-2 gap-2 mb-2">
            <div class="flex items-center gap-2">
              <span class="font-semibold">Davi Ballerini</span>
            </div>
            <div class="text-right font-bold text-green-600">6 ✔</div>

            <div class="flex items-center gap-2">
              <span class="font-semibold">Mateus Busnardo Buemo</span>
            </div>
            <div></div>
          </div>
          <hr class="my-2">
          <div class="grid grid-cols-2 gap-2">
            <div class="flex items-center gap-2">
              <span class="font-semibold">Vinicius Yohan Belusso</span>
            </div>
            <div class="text-right font-bold text-gray-500">0</div>

            <div class="flex items-center gap-2">
              <span class="font-semibold">Denzel West Sousa West</span>
            </div>
            <div></div>
          </div>
        </div>

        <!-- Rating -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
          <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <h3 class="font-semibold text-lg mb-2">Rating</h3>
            <p class="text-sm">Antes da partida:</p>
            <p class="text-xl font-bold">1475</p>
            <p class="text-sm mt-2">Depois da partida:</p>
            <p class="text-xl font-bold text-green-600">1490 <span class="text-sm">(+15)</span></p>
          </div>

          <!-- Posição no ranking -->
          <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <h3 class="font-semibold text-lg mb-2">Ranking</h3>
            <ul class="space-y-1 text-sm">
              <li>3º ⬆️ João Pedro - 1501</li>
              <li>4º ⬆️ <strong>Você</strong> - 1490</li>
              <li>5º ⬇️ Carla Lima - 1482</li>
              <li>6º ⬇️ Bruno Alves - 1460</li>
            </ul>
          </div>
        </div>

        <!-- Conquistas desbloqueadas -->
        <div class="p-4 rounded-lg bg-yellow-50 border border-yellow-200 mb-8">
          <h3 class="font-semibold text-lg mb-2 text-yellow-600">✨ Novas Conquistas Desbloqueadas</h3>
          <ul class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-2">
            <li class="bg-white border border-yellow-300 rounded-md p-3 shadow-sm text-center">
              🌟 Estreante - Primeira partida jogada
            </li>
            <li class="bg-white border border-yellow-300 rounded-md p-3 shadow-sm text-center">
              🔹 Constante - 10 partidas no sistema
            </li>
          </ul>
        </div>

        <!-- Conquistas recentes -->
        <div class="p-4 rounded-lg bg-blue-50 border border-blue-200">
          <h3 class="font-semibold text-lg mb-2 text-blue-600">🌟 Suas últimas Conquistas</h3>
          <ul class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-2">
            <li class="bg-white border border-blue-300 rounded-md p-3 shadow-sm text-center">
              🚀 Em ascensão - Ganhou 3 seguidas
            </li>
            <li class="bg-white border border-blue-300 rounded-md p-3 shadow-sm text-center">
              🔹 Engrenou - 20 partidas no sistema
            </li>
          </ul>
        </div>
      </section>
    </main>
  </div>

</body>
</html>
