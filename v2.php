<?php require_once '#_global.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>

<?php
$partida = $_GET['p']; // Obtém o token da partida via GET
$usuario = $_GET['j']; // Obtém o ID do usuário via GET
$parceiro = Parceiros::parceiro_aleatorio();

$info_p = Partida::info_partida($partida);
if (empty($info_p)) {
  header('Location: principal.php');
  exit;
}

$jogadores = [
  [
    'id' => $info_p[0]['jogador1_id'],
    'nome' => $info_p[0]['nomej1'],
    'apelido' => $info_p[0]['apelidoj1'],
    'time' => 'A',
    'validado' => $info_p[0]['validado_jogador1'],
    'rejeitado' => $info_p[0]['rejeitado_jogador1']
  ],
  [
    'id' => $info_p[0]['jogador2_id'],
    'nome' => $info_p[0]['nomej2'],
    'apelido' => $info_p[0]['apelidoj2'],
    'time' => 'A',
    'validado' => $info_p[0]['validado_jogador2'],
    'rejeitado' => $info_p[0]['rejeitado_jogador2']
  ],
  [
    'id' => $info_p[0]['jogador3_id'],
    'nome' => $info_p[0]['nomej3'],
    'apelido' => $info_p[0]['apelidoj3'],
    'time' => 'B',
    'validado' => $info_p[0]['validado_jogador3'],
    'rejeitado' => $info_p[0]['rejeitado_jogador3']
  ],
  [
    'id' => $info_p[0]['jogador4_id'],
    'nome' => $info_p[0]['nomej4'],
    'apelido' => $info_p[0]['apelidoj4'],
    'time' => 'B',
    'validado' => $info_p[0]['validado_jogador4'],
    'rejeitado' => $info_p[0]['rejeitado_jogador4']
  ]
];



// Verifica em qual time o usuário está
if ($usuario == $info_p[0]['jogador1_id'] || $usuario == $info_p[0]['jogador2_id']) {
  $time_usuario = 'A';
} elseif ($usuario == $info_p[0]['jogador3_id'] || $usuario == $info_p[0]['jogador4_id']) {
  $time_usuario = 'B';
} else {
  $time_usuario = null; // não está em nenhum time (erro ou usuário não relacionado à partida)
}

if ($info_p[0]['vencedor'] == $time_usuario) {
  $resultado = 'win'; // ou 'lose'
} else {
  $resultado = 'lose'; // ou 'lose'
}

?>

<body class="bg-gradient-to-br from-gray-50 via-gray-100 to-gray-200 min-h-screen text-gray-800">

  <div class="flex">

    <main class="flex-1 flex flex-col min-h-screen p-6">
      <section id="resultadoPartida" class="bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl p-8 max-w-3xl mx-auto border border-gray-200">
        <?php
        // Defina o resultado da partida (exemplo: 'win' ou 'lose')
        // Em produção, substitua por lógica real para determinar o resultado do usuário logado


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



        <?php
        // Busca o jogador correspondente ao usuário
        $jogador_usuario = null;
        $coluna_validado = null;
        foreach ($jogadores as $idx => $jogador) {
          if ($jogador['id'] == $usuario) {
            $jogador_usuario = $jogador;
            // O índice do array +1 corresponde ao número do jogador (1 a 4)
            $coluna_validado = 'validado_jogador' . ($idx + 1);
            break;
          }
        }
        ?>

        <?php if ($jogador_usuario && !$jogador_usuario['validado'] && !$jogador_usuario['rejeitado']): ?>
          <form action="controller-partida/validar-partida2.php" method="post" class="flex flex-col items-center mb-4 w-full">
            <input type="hidden" name="partida" value="<?= htmlspecialchars($partida) ?>">
            <input type="hidden" name="usuario" value="<?= htmlspecialchars($usuario) ?>">
            <input type="hidden" name="coluna_validado" value="<?= htmlspecialchars($coluna_validado) ?>">
            <button type="submit" name="acao" value="validar"
              class="w-full max-w-xs sm:max-w-sm md:max-w-md bg-gradient-to-r from-green-500 via-green-600 to-green-700 hover:from-green-600 hover:to-green-800 text-white font-extrabold py-2 px-4 rounded-full shadow-lg transition-all duration-150 text-base tracking-wide border-2 border-green-700 outline-none focus:ring-4 focus:ring-green-300 flex items-center justify-center gap-2"
              style="min-width:0;">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
              Clique para validar partida
            </button>
          </form>
        <?php endif; ?>

        <div class="flex flex-col items-center mb-6">
          <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-yellow-400 shadow-lg mb-3 bg-white flex items-center justify-center">
            <img src="<?= $imagemPerfil ?>" alt="Foto de perfil">
          </div>
          <div class="w-full flex flex-col items-center">
            <div class="bg-gradient-to-r from-blue-100 via-purple-100 to-pink-100 rounded-xl px-4 py-2 shadow-md border border-blue-200 max-w-xs">
              <p class="text-base sm:text-sm font-semibold text-gray-700 text-center leading-snug tracking-tight">
                <?= htmlspecialchars($mensagem); ?>
              </p>
            </div>
          </div>
        </div>

        <!-- Resultado da partida -->
        <div class="relative rounded-2xl overflow-hidden shadow-xl border border-gray-200 bg-white p-4 sm:p-6 mb-8 max-w-md w-full mx-auto ring-1 ring-blue-100">

          <!-- Fundo decorativo com blur -->
          <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 opacity-30 blur-xl"></div>

          <!-- Título -->
          <div class="relative z-10 flex flex-col items-center mb-4">
            <h2 class="text-lg sm:text-xl font-extrabold text-blue-900 tracking-tight text-center drop-shadow-sm">
              Resultado da Partida
            </h2>
            <div class="w-12 h-1 bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 rounded-full mt-2"></div>
          </div>

          <?php
          // CASO O TIME DO USUARIO SEJA O VENCEDOR
          if ($resultado == 'win') {
            // SE O USUARIO GANHOU E É O TIME A
            if ($time_usuario == 'A') {
          ?>
              <!-- Dupla Vencedora -->
              <div class="relative z-10 bg-gradient-to-r from-green-200 via-green-100 to-white border-l-4 border-green-500 rounded-xl p-3 mb-2 shadow">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-base font-bold text-green-800 flex items-center gap-1">🏆 <?= htmlspecialchars($info_p[0]['nomej1']) ?>
                      <?php if(!empty($info_p[0]['apelidoj1'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj1']) ?>)</span><?php endif; ?>
                    </p>
                    <p class="text-base font-bold text-green-800 flex items-center gap-1">🏆 <?= htmlspecialchars($info_p[0]['nomej2']) ?>
                      <?php if(!empty($info_p[0]['apelidoj2'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj2']) ?>)</span><?php endif; ?>
                    </p>
                  </div>
                  <div class="text-right">
                    <span class="text-3xl font-extrabold text-green-600"><?= $info_p[0]['placar_a'] ?></span>
                    <span class="text-yellow-500 text-lg align-middle ml-1" title="Medalha de Ouro">🥇</span>
                  </div>
                </div>
                <p class="mt-1 text-xs text-green-700 italic">Venceram com estilo!</p>
              </div>

              <!-- Dupla Derrotada -->
              <div class="relative z-10 bg-gradient-to-r from-red-100 via-white to-gray-100 border-l-4 border-red-400 rounded-xl p-3 shadow">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-base font-semibold text-red-800 flex items-center gap-1">😓 <?= htmlspecialchars($info_p[0]['nomej3']) ?>
                      <?php if(!empty($info_p[0]['apelidoj3'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj3']) ?>)</span><?php endif; ?>
                    </p>
                    <p class="text-base font-semibold text-red-800 flex items-center gap-1">😓 <?= htmlspecialchars($info_p[0]['nomej4']) ?>
                      <?php if(!empty($info_p[0]['apelidoj4'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj4']) ?>)</span><?php endif; ?>
                    </p>
                  </div>
                  <div class="text-right">
                    <span class="text-3xl font-extrabold text-gray-400"><?= $info_p[0]['placar_b'] ?></span>
                    <span class="text-gray-400 text-lg align-middle ml-1" title="Medalha de Prata">🥈</span>
                  </div>
                </div>
                <p class="mt-1 text-xs text-red-600 italic">Derrota com dignidade</p>
              </div>
            <?php
              //SE O USUARIO GANHOU E É O TIME B
            } else {
            ?>
              <!-- Dupla Vencedora -->
              <div class="relative z-10 bg-gradient-to-r from-green-200 via-green-100 to-white border-l-4 border-green-500 rounded-xl p-3 mb-2 shadow">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-base font-bold text-green-800 flex items-center gap-1">🏆 <?= htmlspecialchars($info_p[0]['nomej3']) ?>
                      <?php if(!empty($info_p[0]['apelidoj3'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj3']) ?>)</span><?php endif; ?>
                    </p>
                    <p class="text-base font-bold text-green-800 flex items-center gap-1">🏆 <?= htmlspecialchars($info_p[0]['nomej4']) ?>
                      <?php if(!empty($info_p[0]['apelidoj4'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj4']) ?>)</span><?php endif; ?>
                    </p>
                  </div>
                  <div class="text-right">
                    <span class="text-3xl font-extrabold text-green-600"><?= $info_p[0]['placar_b'] ?></span>
                    <span class="text-yellow-500 text-lg align-middle ml-1" title="Medalha de Ouro">🥇</span>
                  </div>
                </div>
                <p class="mt-1 text-xs text-green-700 italic">Venceram com estilo!</p>
              </div>

              <!-- Dupla Derrotada -->
              <div class="relative z-10 bg-gradient-to-r from-red-100 via-white to-gray-100 border-l-4 border-red-400 rounded-xl p-3 shadow">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-base font-semibold text-red-800 flex items-center gap-1">😓 <?= htmlspecialchars($info_p[0]['nomej1']) ?>
                      <?php if(!empty($info_p[0]['apelidoj1'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj1']) ?>)</span><?php endif; ?>
                    </p>
                    <p class="text-base font-semibold text-red-800 flex items-center gap-1">😓 <?= htmlspecialchars($info_p[0]['nomej2']) ?>
                      <?php if(!empty($info_p[0]['apelidoj2'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj2']) ?>)</span><?php endif; ?>
                    </p>
                  </div>
                  <div class="text-right">
                    <span class="text-3xl font-extrabold text-gray-400"><?= $info_p[0]['placar_a'] ?></span>
                    <span class="text-gray-400 text-lg align-middle ml-1" title="Medalha de Prata">🥈</span>
                  </div>
                </div>
                <p class="mt-1 text-xs text-red-600 italic">Derrota com dignidade</p>
              </div>
            <?php
            }
            ?>

            <?php
            // CASO O TIME DO USUARIO SEJA O PERDEDOR
          } else {
            // SE O USUARIO PERDEU E É O TIME A
            if ($time_usuario == 'A') {
            ?>
              <!-- Dupla Vencedora -->
              <div class="relative z-10 bg-gradient-to-r from-green-200 via-green-100 to-white border-l-4 border-green-500 rounded-xl p-3 mb-2 shadow">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-base font-bold text-green-800 flex items-center gap-1">🏆 <?= htmlspecialchars($info_p[0]['nomej3']) ?>
                      <?php if(!empty($info_p[0]['apelidoj3'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj3']) ?>)</span><?php endif; ?>
                    </p>
                    <p class="text-base font-bold text-green-800 flex items-center gap-1">🏆 <?= htmlspecialchars($info_p[0]['nomej4']) ?>
                      <?php if(!empty($info_p[0]['apelidoj4'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj4']) ?>)</span><?php endif; ?>
                    </p>
                  </div>
                  <div class="text-right">
                    <span class="text-3xl font-extrabold text-green-600"><?= $info_p[0]['placar_b'] ?></span>
                    <span class="text-yellow-500 text-lg align-middle ml-1" title="Medalha de Ouro">🥇</span>
                  </div>
                </div>
                <p class="mt-1 text-xs text-green-700 italic">Venceram com estilo!</p>
              </div>

              <!-- Dupla Derrotada -->
              <div class="relative z-10 bg-gradient-to-r from-red-100 via-white to-gray-100 border-l-4 border-red-400 rounded-xl p-3 shadow">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-base font-semibold text-red-800 flex items-center gap-1">😓 <?= htmlspecialchars($info_p[0]['nomej1']) ?>
                      <?php if(!empty($info_p[0]['apelidoj1'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj1']) ?>)</span><?php endif; ?>
                    </p>
                    <p class="text-base font-semibold text-red-800 flex items-center gap-1">😓 <?= htmlspecialchars($info_p[0]['nomej2']) ?>
                      <?php if(!empty($info_p[0]['apelidoj2'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj2']) ?>)</span><?php endif; ?>
                    </p>
                  </div>
                  <div class="text-right">
                    <span class="text-3xl font-extrabold text-gray-400"><?= $info_p[0]['placar_a'] ?></span>
                    <span class="text-gray-400 text-lg align-middle ml-1" title="Medalha de Prata">🥈</span>
                  </div>
                </div>
                <p class="mt-1 text-xs text-red-600 italic">Derrota com dignidade</p>
              </div>
            <?php
              // SE O USUARIO PERDEU E É O TIME B
            } else {
            ?>
              <!-- Dupla Vencedora -->
              <div class="relative z-10 bg-gradient-to-r from-green-200 via-green-100 to-white border-l-4 border-green-500 rounded-xl p-3 mb-2 shadow">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-base font-bold text-green-800 flex items-center gap-1">🏆 <?= htmlspecialchars($info_p[0]['nomej1']) ?>
                      <?php if(!empty($info_p[0]['apelidoj1'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj1']) ?>)</span><?php endif; ?>
                    </p>
                    <p class="text-base font-bold text-green-800 flex items-center gap-1">🏆 <?= htmlspecialchars($info_p[0]['nomej2']) ?>
                      <?php if(!empty($info_p[0]['apelidoj2'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj2']) ?>)</span><?php endif; ?>
                    </p>
                  </div>
                  <div class="text-right">
                    <span class="text-3xl font-extrabold text-green-600"><?= $info_p[0]['placar_a'] ?></span>
                    <span class="text-yellow-500 text-lg align-middle ml-1" title="Medalha de Ouro">🥇</span>
                  </div>
                </div>
                <p class="mt-1 text-xs text-green-700 italic">Venceram com estilo!</p>
              </div>

              <!-- Dupla Derrotada -->
              <div class="relative z-10 bg-gradient-to-r from-red-100 via-white to-gray-100 border-l-4 border-red-400 rounded-xl p-3 shadow">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-base font-semibold text-red-800 flex items-center gap-1">😓 <?= htmlspecialchars($info_p[0]['nomej3']) ?>
                      <?php if(!empty($info_p[0]['apelidoj3'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj3']) ?>)</span><?php endif; ?>
                    </p>
                    <p class="text-base font-semibold text-red-800 flex items-center gap-1">😓 <?= htmlspecialchars($info_p[0]['nomej4']) ?>
                      <?php if(!empty($info_p[0]['apelidoj4'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($info_p[0]['apelidoj4']) ?>)</span><?php endif; ?>
                    </p>
                  </div>
                  <div class="text-right">
                    <span class="text-3xl font-extrabold text-gray-400"><?= $info_p[0]['placar_b'] ?></span>
                    <span class="text-gray-400 text-lg align-middle ml-1" title="Medalha de Prata">🥈</span>
                  </div>
                </div>
                <p class="mt-1 text-xs text-red-600 italic">Derrota com dignidade</p>
              </div>
          <?php
            }
          }
          ?>
          <!-- Rodapé decorativo -->
          <div class="relative z-10 text-center mt-3">
            <span class="inline-block text-xs text-gray-500 italic">Compartilhe com seus amigos e desafie para a revanche! 🔁 @app.dupla</span>
          </div>
          <!-- Logo discreta no topo direito -->
          <div class="flex justify-center items-center gap-6 mt-2">
            <!-- <div class="flex flex-col items-center">
              <img src="img/dupla.png" alt="Logo Dupla" class="h-16 w-auto max-h-16 aspect-square object-contain" loading="lazy" style="max-width:4rem;">
            </div> -->
            <div class="flex flex-col items-center">
              <img src="img/<?= $parceiro[0]['imagem'] ?>" alt="Logo Dupla" class="h-16 w-auto max-h-16 aspect-square object-contain rounded-full border-2 border-gray-300" loading="lazy" style="max-width:4rem;">
            </div>
            <div class="flex flex-col items-center">
              <img src="img/<?= $parceiro[1]['imagem'] ?>" alt="Logo Dupla" class="h-16 w-auto max-h-16 aspect-square object-contain rounded-full border-2 border-gray-300" loading="lazy" style="max-width:4rem;">
            </div>
          </div>
        </div>

        <!-- Status de validação -->
        <div class="mb-6">
          <h3 class="font-semibold mb-4 text-gray-900 text-lg flex items-center gap-2 justify-center bg-gradient-to-r from-blue-100 via-purple-100 to-pink-100 rounded-lg py-2 px-3 shadow border-b-2 border-blue-400 tracking-wide">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-500/90 text-white shadow mr-1">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
            </span>
            Status dos Jogadores
          </h3>
          <div class="grid grid-cols-1 gap-2">
            <?php foreach ($jogadores as $jogador): ?>
              <?php if ($jogador['validado'] == true) { ?>
                <div class="flex items-center gap-2 bg-green-100 border-l-4 border-green-500 px-3 py-2 rounded-lg shadow-sm">
                  <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-green-500 text-white text-lg font-bold shadow">
                    ✔
                  </span>
                  <div>
                    <span class="block font-semibold text-green-800 text-sm"><?= htmlspecialchars($jogador['nome']) ?>
                      <?php if(!empty($jogador['apelido'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($jogador['apelido']) ?>)</span><?php endif; ?>
                    </span>
                    <span class="text-xs text-green-700">Confirmado</span>
                  </div>
                </div>
              <?php } elseif ($jogador['rejeitado'] == true) { ?>
                <div class="flex items-center gap-2 bg-red-100 border-l-4 border-red-500 px-3 py-2 rounded-lg shadow-sm">
                  <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-red-500 text-white text-lg font-bold shadow">
                    ✖
                  </span>
                  <div>
                    <span class="block font-semibold text-red-800 text-sm"><?= htmlspecialchars($jogador['nome']) ?>
                      <?php if(!empty($jogador['apelido'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($jogador['apelido']) ?>)</span><?php endif; ?>
                    </span>
                    <span class="text-xs text-red-700">Rejeitado</span>
                  </div>
                </div>
              <?php } else { ?>
                <div class="flex items-center gap-2 bg-yellow-100 border-l-4 border-yellow-400 px-3 py-2 rounded-lg shadow-sm">
                  <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-yellow-400 text-white text-lg font-bold shadow">
                    ⌛
                  </span>
                  <div>
                    <span class="block font-semibold text-yellow-800 text-sm"><?= htmlspecialchars($jogador['nome']) ?>
                      <?php if(!empty($jogador['apelido'])): ?><span class="text-xs font-normal text-gray-600">(<?= htmlspecialchars($jogador['apelido']) ?>)</span><?php endif; ?>
                    </span>
                    <span class="text-xs text-yellow-700">Pendente</span>
                  </div>
                </div>
              <?php } ?>
            <?php endforeach; ?>
          </div>
        </div>
        <hr class="my-4">
        <!-- Botões de compartilhamento e início -->
        <div class="flex flex-col gap-4 mt-6 items-center">
          <div class="w-full flex flex-col items-center">
            <p class="text-center text-sm text-gray-500 leading-snug mb-2">
              <?php if ($resultado === 'win'): ?>
                Parabéns! Sua partida foi registrada.<br>
                Aguarde a confirmação dos outros jogadores.<br>
                Você será avisado no WhatsApp.
              <?php else: ?>
                Sua partida foi registrada.<br>
                Não desanime, tente novamente!<br>
                Você será avisado no WhatsApp.
              <?php endif; ?>
            </p>
          </div>

          <a href="hist-partidas.php"
            class="w-full max-w-xs inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white font-bold px-6 py-3 rounded-full shadow-lg transition-all duration-200 text-base">
            Minhas Partidas
          </a>

          <p class="text-center text-xs text-gray-600 mt-2">
            Tire um print desta tela e compartilhe o resultado nas suas redes sociais!
          </p>
        </div>
      </section>
    </main>
  </div>
</body>

</html>