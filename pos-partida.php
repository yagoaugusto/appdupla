<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>

<body class="bg-gradient-to-br from-gray-50 via-gray-100 to-gray-200 min-h-screen text-gray-800">

  <?php require_once '_nav_superior.php'; ?>

  <div class="flex pt-16">
    <?php require_once '_nav_lateral.php'; ?>

    <main class="flex-1 flex flex-col min-h-screen p-6">
      <section id="resultadoPartida" class="bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl p-8 max-w-3xl mx-auto border border-gray-200">
        <?php
        // Defina o resultado da partida (exemplo: 'win' ou 'lose')
        // Em produção, substitua por lógica real para determinar o resultado do usuário logado
        $resultado = 'win'; // ou 'lose'

        // Imagens de perfil para vitória e derrota
        $imagemVitoria = 'img/2.png'; // Substitua pelo caminho real da imagem de vitória
        $imagemDerrota = 'img/1.png'; // Substitua pelo caminho real da imagem de derrota

        // Mensagens divertidas para vitória
        $mensagensVitoria = [
          "Você esmagou a concorrência! 🏆",
          "Vitória épica! Parabéns, campeão!",
          "Ninguém te segura hoje! 🚀",
          "Você jogou como um mestre!",
          "A coroa é sua! 👑",
          "Hoje foi seu dia de brilhar!",
          "Você fez história nessa partida!",
          "O troféu já tem dono: você!",
          "Que aula de jogo! 👏",
          "Se fosse filme, seria blockbuster!",
          "Você é o terror dos adversários!",
          "Jogou fácil, venceu bonito!",
          "A vitória sorriu pra você!",
          "O placar não mente: você é fera!",
          "Que performance! Até o VAR aplaudiu!",
          "Jogo limpo, vitória suada e merecida 🥇",
          "Seu jogo foi arte pura 🎨🔥",
          "Você atropelou com estilo 🛣️💥",
          "Essa vitória foi cirúrgica 🧠💡",
          "Inspiração total! Os deuses do esporte aplaudiram ⚡",
          "Você dominou do início ao fim 🧱",
          "Que massacre tático 🎯",
          "O adversário ainda tá tentando entender o que aconteceu 😵",
          "Você está jogando em outro nível 🧬",
          "Colocou o adversário no bolso 🧥🎾",
          "Show de talento e atitude 👊🔥",
          "Vitória com sabor de lenda 🐐",
          "Você brilhou mais que o sol da praia ☀️🏖️",
          "O placar virou poesia com sua performance 📜✨",
          "Se fosse videogame, tava no modo lendário 🎮🏆"
        ];

        // Mensagens divertidas para derrota
        $mensagensDerrota = [
          "Não foi dessa vez, mas não desanime! 😉",
          "Acontece até com os melhores!",
          "Levanta a cabeça, campeão!",
          "Hoje não deu, mas amanhã tem mais!",
          "O importante é competir! 🏅",
          "Derrota é só um passo pra vitória!",
          "Você jogou bem, mas o jogo é assim!",
          "Faz parte do jogo, bora pra próxima!",
          "O placar não reflete seu talento!",
          "Até os campeões perdem às vezes!",
          "Hoje foi treino, amanhã é jogo!",
          "Perdeu, mas saiu gigante!",
          "A sorte não ajudou, mas a garra ficou!",
          "O aprendizado vale mais que o resultado!",
          "Derrota? Só se for de mentirinha!",
          "Perdeu, mas pelo menos o look tava em dia 😎",
          "Jogou com coragem... pena que sem pontaria 😅",
          "Hoje o jogo foi só pra manter a humildade 🥲",
          "O que vale é a resenha depois do jogo 🍻",
          "Foi quase… quase que você ganhou um game 😂",
          "Jogou muito… pro time adversário 🫣",
          "Deu show… mas só de drama mesmo 🎭",
          "Seu talento tá guardado… bem escondido 🤐",
          "Hoje o VAR nem quis ver, era muita vergonha 🤖",
          "Já pode pedir música no Fantástico: 3 derrotas seguidas 🎶",
          "Foi tipo Wi-Fi ruim: caiu toda hora 📶",
          "Fez tudo certo, menos ganhar 🙃",
          "Pelo menos a quadra tava bonita né 🏖️",
          "Jogar você jogou, vencer já é outra história 🤷‍♂️",
          "Serve de aquecimento pro próximo passeio 🤡"
        ];

        // Escolhe mensagem aleatória conforme resultado
        if ($resultado === 'win') {
          $imagemPerfil = $imagemVitoria;
          $mensagem = $mensagensVitoria[array_rand($mensagensVitoria)];
        } else {
          $imagemPerfil = $imagemDerrota;
          $mensagem = $mensagensDerrota[array_rand($mensagensDerrota)];
        }
        ?>

        <div class="flex flex-col items-center mb-6">
          <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-yellow-400 shadow-lg mb-3 bg-white flex items-center justify-center">
            <img src="<?= $imagemPerfil ?>" alt="Foto de perfil">
          </div>
          <h2 class="text-3xl font-extrabold mb-2 text-gray-800 text-center">
            <?php echo htmlspecialchars($mensagem); ?>
          </h2>
        </div>

        <!-- Resultado da partida -->
<div class="relative rounded-3xl overflow-hidden shadow-2xl border border-gray-300 bg-white p-8 mb-10 max-w-2xl mx-auto ring-1 ring-blue-100">
  
  <!-- Fundo decorativo com blur -->
  <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 opacity-30 blur-xl"></div>

  <!-- Título -->
  <div class="relative z-10 flex flex-col items-center mb-8">
    <h2 class="text-2xl sm:text-3xl font-extrabold text-blue-900 tracking-tight text-center drop-shadow-sm">
      Resultado da Partida
    </h2>
    <div class="w-16 h-1 bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 rounded-full mt-3"></div>
  </div>

  <!-- Dupla Vencedora -->
  <div class="relative z-10 bg-gradient-to-r from-green-200 via-green-100 to-white border-l-8 border-green-500 rounded-2xl p-6 mb-4 shadow-md">
    <div class="flex justify-between items-center">
      <div>
        <p class="text-lg font-bold text-green-800">🏆 Davi Ballerini</p>
        <p class="text-lg font-bold text-green-800">🏆 Mateus Busnardo Buemo</p>
      </div>
      <div class="text-right">
        <span class="text-5xl font-extrabold text-green-600">6</span>
        <span class="text-green-600 text-2xl align-middle ml-1">✔</span>
      </div>
    </div>
    <p class="mt-2 text-sm text-green-700 italic">Venceram com estilo!</p>
  </div>

  <!-- Dupla Derrotada -->
  <div class="relative z-10 bg-gradient-to-r from-red-100 via-white to-gray-100 border-l-8 border-red-400 rounded-2xl p-6 shadow-sm">
    <div class="flex justify-between items-center">
      <div>
        <p class="text-lg font-semibold text-red-800">😓 Vinicius Yohan Belusso</p>
        <p class="text-lg font-semibold text-red-800">😓 Denzel West Sousa West</p>
      </div>
      <div class="text-right">
        <span class="text-5xl font-extrabold text-gray-400">0</span>
      </div>
    </div>
    <p class="mt-2 text-sm text-red-600 italic">Derrota com dignidade</p>
  </div>

  <!-- Rodapé decorativo -->
  <div class="relative z-10 text-center mt-6">
    <span class="inline-block text-sm text-gray-500 italic">Compartilhe com seus amigos e desafie para a revanche! 🔁</span>
  </div>
</div>

        <!-- Status de validação -->
        <div class="mb-8">
          <h3 class="font-semibold mb-3 text-gray-700 text-lg">Status de Confirmação</h3>
          <ul class="grid grid-cols-2 gap-3 text-base">
            <li class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
              <span class="text-lg">✔</span> Davi Ballerini confirmou
            </li>
            <li class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
              <span class="text-lg">✔</span> Mateus Buemo confirmou
            </li>
            <li class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg shadow-sm">
              <span class="text-lg">⌛</span> Vinicius Belusso pendente
            </li>
            <li class="flex items-center gap-2 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg shadow-sm">
              <span class="text-lg">⌛</span> Denzel West pendente
            </li>
          </ul>
        </div>
        <hr>
        <!-- Botões de compartilhamento e início -->
        <div class="flex flex-col sm:flex-row justify-center items-center gap-6 mt-10">
          <div class="w-full sm:w-72 flex flex-col items-center">

            <p class="text-center text-base text-gray-500">
              <?php if ($resultado === 'win'): ?>
                Parabéns! Sua partida foi registrada.<br>
                Agora é só aguardar a confirmação dos outros jogadores.<br>
                Assim que tudo estiver validado, você será avisado no WhatsApp.
              <?php else: ?>
                Sua partida foi registrada.<br>
                Não desanime, continue jogando e tente novamente!<br>
                Assim que tudo estiver validado, você será avisado no WhatsApp.
              <?php endif; ?>
            </p>

          </div>
          <a href="principal.php"
            class="w-full sm:w-72 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white font-bold px-8 py-4 rounded-full shadow-lg transition-all duration-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M4 10v10a1 1 0 0 0 1 1h3m10-11v10a1 1 0 0 1-1 1h-3" />
            </svg>
            Início
          </a>

                      <p class="mb-3 text-center text-gray-600">
              Não esqueça de tirar um print desta tela e compartilhe o resultado nas suas redes sociais favoritas!
            </p>
        </div>
      </section>
    </main>
  </div>
</body>

</html>