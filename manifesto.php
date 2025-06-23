<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<body class="bg-white text-gray-800 font-sans leading-relaxed" style="color-scheme: light;">

  <!-- Navbar superior -->
  <?php require_once '_nav_superior.php'; ?>

  <main class="pt-16">
    <section class="bg-gradient-to-r from-yellow-300 to-pink-400 text-white text-center py-16 px-4">
      <h1 class="text-5xl font-extrabold mb-4">🏆 Bem-vindo ao Manifesto DUPLA</h1>
      <p class="text-xl max-w-2xl mx-auto">Mais que um jogo, o DUPLA é um movimento. Aqui, cada saque vale história. Cada ponto é parte de algo maior. E você, já faz parte?</p>
    </section>

    <section class="max-w-4xl mx-auto py-12 px-6">
      <h2 class="text-3xl font-bold text-center mb-6">🎯 Nosso propósito</h2>
      <p class="mb-6 text-lg">
        O DUPLA nasceu da paixão pelo Beach Tennis e da vontade de transformar cada partida em algo que vá além da quadra. Criamos um sistema que valoriza o esforço, a dedicação, a superação e — principalmente — a diversão com os amigos. Aqui, jogando ou assistindo, você participa de algo real, com estatísticas, conquistas e evolução contínua.
      </p>
    </section>

    <section class="bg-gray-100 py-12 px-6">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-center mb-8">📊 Como funciona o nosso ranking?</h2>
        
        <div class="mb-6">
          <h3 class="text-xl font-semibold mb-2">👥 Tudo começa com as duplas</h3>
          <p>Você joga com um parceiro, e enfrentam outra dupla. O sistema calcula a média de rating das duplas para definir o favoritismo.</p>
        </div>

        <div class="mb-6">
          <h3 class="text-xl font-semibold mb-2">💡 Venceu? Ganha pontos. Perdeu? Depende...</h3>
          <p>O nosso algoritmo, inspirado no <strong>Glicko-2</strong>, recompensa <strong>vitórias inesperadas</strong>. Se você derrota uma dupla muito mais forte, ganha muitos pontos. Mas se perde para uma muito mais fraca, a penalidade é maior. Isso cria justiça e emoção!</p>
        </div>

        <div class="mb-6">
          <h3 class="text-xl font-semibold mb-2">🔐 Fator de confiança</h3>
          <p>O sistema entende quando o seu ranking ainda é instável (poucas partidas) e vai ajustando a precisão com o tempo. Quanto mais você joga, mais fiel é sua pontuação.</p>
        </div>

        <div class="mb-6">
          <h3 class="text-xl font-semibold mb-2">📈 Fórmula (para os curiosos)</h3>
          <pre class="bg-white text-sm p-4 rounded shadow overflow-x-auto">
Novo rating = rating atual + fator de ajuste * (resultado real - resultado esperado)
          </pre>
          <p class="text-sm text-gray-600">Essa é a base do algoritmo. Mas o Glicko-2 leva em conta também o desvio padrão e o tempo entre partidas.</p>
        </div>
      </div>
    </section>

    <section class="max-w-4xl mx-auto py-12 px-6">
      <h2 class="text-3xl font-bold text-center mb-6">🔥 Por que participar?</h2>
      <ul class="list-disc pl-6 text-lg space-y-2">
        <li>✅ Seu esforço será reconhecido, até em derrotas dignas</li>
        <li>✅ Evolução visível no seu perfil</li>
        <li>✅ Conquistas desbloqueáveis e títulos únicos</li>
        <li>✅ Participe de rankings locais, por cidade, por nível e muito mais</li>
        <li>✅ Convide seus amigos e veja como você se compara a eles</li>
      </ul>
    </section>

    <section class="bg-yellow-200 text-center py-10 px-4">
      <h2 class="text-3xl font-bold mb-4">🚀 Bora jogar?</h2>
      <p class="text-lg mb-6">O DUPLA está só começando. Junte-se a nós e construa sua história no ranking do Beach Tennis.</p>
      <a href="registrar_partida.php" class="bg-pink-600 text-white px-6 py-3 rounded-full text-lg font-semibold hover:bg-pink-700 transition">Começar uma nova partida</a>
    </section>

    <!-- Footer -->
    <?php require_once '_footer.php'; ?>
  </main>

</body>
</html>