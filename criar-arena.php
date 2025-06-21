<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<style>
    /* Estilo para forçar o fundo branco em inputs, útil contra autofill de navegadores */
    .force-white-bg {
        background-color: #fff !important;
        color: #222 !important;
        -webkit-text-fill-color: #222 !important;
    }
</style>
<body class="bg-gray-100 min-h-screen text-gray-800">

  <!-- Navbar superior -->
  <?php require_once '_nav_superior.php'; ?>

  <div class="flex pt-16">
    <!-- Menu lateral -->
    <?php require_once '_nav_lateral.php'; ?>

    <!-- Conteúdo principal -->
    <main class="flex-1 p-4">
      <section class="max-w-md mx-auto w-full bg-white/95 rounded-2xl shadow-xl border border-blue-200 mt-4 mb-6 px-3 py-6 flex flex-col items-center backdrop-blur-md">
        
        <!-- Título da Página -->
        <div class="w-full text-center mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-pink-500 to-red-600 mb-2 tracking-tight drop-shadow-lg">
                Criar Nova Arena
            </h1>
            <p class="text-xs sm:text-base text-gray-600 font-medium">
                Dê vida ao seu espaço de jogo!
            </p>
        </div>

        <form id="formCriarArena" action="controller-arena/salvar-arena.php" method="POST" class="w-full space-y-6">
            <!-- Nome da Arena -->
            <div>
                <label for="nome_arena" class="block mb-1 text-sm font-medium text-gray-700">Nome da Arena</label>
                <input type="text" id="nome_arena" name="nome_arena" class="input input-bordered w-full text-sm focus:ring-2 focus:ring-blue-400 force-white-bg" placeholder="Ex: Arena Sol e Areia" required>
            </div>

            <!-- Lema da Arena -->
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Lema da Arena</label>
                <?php
                $mottos = [
                    "Onde a areia encontra a paixão.",
                    "Aqui, cada ponto é uma história.",
                    "Seu palco para o Beach Tennis.",
                    "A quadra que te espera.",
                    "Onde a diversão é levada a sério (só que não).",
                    "Prepare o smash, a areia é nossa!",
                    "Aqui o sol brilha mais forte (e a bolinha também).",
                    "Se a vida te der limões, jogue Beach Tennis aqui!",
                    "Nosso lema: menos trabalho, mais Beach Tennis.",
                    "Cuidado: paixão por Beach Tennis contagiosa!",
                    "Onde a rede é alta, mas a amizade é maior.",
                    "Venha suar e sorrir conosco!",
                    "A energia que você precisa, na areia que você ama.",
                    "Mais que um jogo, um estilo de vida.",
                    "Seu refúgio na areia."
                ];
                $random_motto = $mottos[array_rand($mottos)];
                ?>
                <div class="flex items-center gap-2">
                    <input type="text" id="lema_arena" name="lema_arena" class="input input-bordered w-full text-sm focus:ring-2 focus:ring-purple-400 force-white-bg mb-2" value="<?= htmlspecialchars($random_motto) ?>" readonly>
                </div>
                <button type="button" id="sortear_lema" class="btn w-full bg-purple-500 hover:bg-purple-600 text-white font-bold py-2 rounded-xl shadow transition">Sortear Lema</button>
            </div>

            <!-- Emblema da Arena (Emoji) -->
            <div>
                <label class="block mb-1 text-sm font-medium text-gray-700">Emblema da Arena</label>
                <?php
                $emojis = [
                    "🌴", "☀️", "🌊", "🏖️", "🏐", "🎾", "🏆", "🥇", "🌟", "🔥",
                    "🚀", "💪", "😎", "🥳", "🤩", "🎯", "💯", "✨", "⚡", "🌈",
                    "🏅", "🎉", "🌺", "🍹", "🏄‍♂️", "🏄‍♀️", "🕶️", "🌅", "🌇", "🌞"
                ];
                $random_emoji = $emojis[array_rand($emojis)];
                ?>
                <div class="flex items-center gap-2">
                    <input type="text" id="emblema_arena" name="emblema_arena" class="input input-bordered w-16 text-center text-2xl focus:ring-2 focus:ring-yellow-400 force-white-bg mb-2" value="<?= htmlspecialchars($random_emoji) ?>" readonly>
                </div>
                <button type="button" id="sortear_emblema" class="btn w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 rounded-xl shadow transition">Sortear Emblema</button>
            </div>

            <!-- Tipo de Arena (Pública/Privada) -->
            <div>
                <label class="block mb-2 text-sm font-medium text-gray-700">Visibilidade da Arena</label>
                <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200 shadow-sm">
                    <span class="text-gray-700 font-medium">Pública</span>
                    <input type="checkbox" id="tipo_arena" name="tipo_arena" class="toggle toggle-lg toggle-primary" checked />
                    <span class="text-gray-700 font-medium">Privada</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    <span id="public_desc" class="hidden">Qualquer um pode ver e se juntar.</span>
                    <span id="private_desc">Apenas membros convidados podem ver e acessar.</span>
                </p>
            </div>

            <!-- Botão de Criação -->
            <div class="flex flex-col items-center mt-4">
                <button type="submit" class="btn w-full text-sm py-2 rounded-xl shadow-lg bg-gradient-to-r from-blue-600 via-pink-500 to-red-500 hover:from-blue-700 hover:to-red-600 transition-all font-bold tracking-wide text-white uppercase">
                    Criar Arena
                </button>
            </div>
        </form>

      </section>
    </main>
  </div>

  <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
    DUPLA - Deu Game? Dá Ranking!
  </footer>

  <script>
    const mottos = <?= json_encode($mottos) ?>;
    const emojis = <?= json_encode($emojis) ?>;

    document.getElementById('sortear_lema').addEventListener('click', function() {
        const randomMotto = mottos[Math.floor(Math.random() * mottos.length)];
        document.getElementById('lema_arena').value = randomMotto;
    });

    document.getElementById('sortear_emblema').addEventListener('click', function() {
        const randomEmoji = emojis[Math.floor(Math.random() * emojis.length)];
        document.getElementById('emblema_arena').value = randomEmoji;
    });

    const tipoArenaToggle = document.getElementById('tipo_arena');
    const publicDesc = document.getElementById('public_desc');
    const privateDesc = document.getElementById('private_desc');

    // Estado inicial da descrição da visibilidade
    if (tipoArenaToggle.checked) {
        publicDesc.classList.add('hidden');
        privateDesc.classList.remove('hidden');
    } else {
        publicDesc.classList.remove('hidden');
        privateDesc.classList.add('hidden');
    }

    tipoArenaToggle.addEventListener('change', function() {
        if (this.checked) {
            publicDesc.classList.add('hidden');
            privateDesc.classList.remove('hidden');
        } else {
            publicDesc.classList.remove('hidden');
            privateDesc.classList.add('hidden');
        }
    });
  </script>

</body>
</html>