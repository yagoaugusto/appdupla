<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php'; ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php'; ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-2 sm:p-4">
            <?php
            // Verifica se o usuário está logado
            if (!isset($_SESSION['DuplaUserId'])) {
                header("Location: index.php");
                exit;
            }
            $usuario_id = $_SESSION['DuplaUserId'];

            // Pega o ID do torneio da URL
            $torneio_id = filter_input(INPUT_GET, 'torneio_id', FILTER_VALIDATE_INT);

            if (!$torneio_id) {
                $_SESSION['mensagem'] = ["danger", "ID do torneio inválido."];
                header("Location: encontrar-torneio.php");
                exit;
            }

            $torneio = Torneio::getTorneioById($torneio_id);
            if (!$torneio) {
                $_SESSION['mensagem'] = ["danger", "Torneio não encontrado."];
                header("Location: encontrar-torneio.php");
                exit;
            }

            $categorias = Categoria::getCategoriesByTorneioId($torneio_id);
            
            $nomes_duplas = [
                "Reis da Areia", "Rainhas da Praia", "Smash na Causa", "Dupla Dinâmica", "Pé na Areia",
                "Sol e Saque", "Vibe Praiana", "Guerreiros do Sol", "Lob na Medida", "Areia nos Pés",
                "Fúria da Praia", "Tubarões da Rede", "Parceria de Ouro", "Saque e Voleio", "Beach Bums",
                "Tornado de Areia", "Esquadrão do Lob", "Os Imbatíveis", "Deu Match", "Ponto Certo",
                "Raquetes em Chamas", "Amigos do Saque", "Bola Fora? Nunca!", "Time da Virada", "Zero Bala",
                "Os Gancheiros", "Mestres do Voleio", "Praia, Sol e Ponto", "Dupla de Respeito", "Feras do Beach",
                "Só Jogaço", "Areia e Adrenalina", "Saque Viagem", "Os Estrategistas", "Vitamina D-Team",
                "Linha de Fundo", "Smashers", "Os Bloqueadores", "Dupla Implacável", "Pura Vibe",
                "Os Craques da Areia", "Time Sem Erro", "Bola de Fogo", "Guerreiras da Areia", "Os Vencedores",
                "Pé Quente", "Saque Monstro", "Dupla de Elite", "Tropa da Areia", "Os Incansáveis",
                "Smash & Cia", "Areia no Sangue", "Os Precisos", "Dupla Fatal", "Time da Praia",
                "Os Invictos", "Raquetada Certa", "Dupla Show", "Os Lendários", "Time do Barulho",
                "Os Artistas da Areia", "Dupla de Titãs", "Os Gladiadores", "Time do Sol", "Os Fenômenos",
                "Raquetes de Aço", "Dupla de Gigantes", "Os Conquistadores", "Time da Brisa", "Os Imparáveis",
                "Smash Total", "Areia e Glória", "Os Perfeitos", "Dupla de Campeões", "Time da Areia Quente",
                "Os Destemidos", "Raquetada Atômica", "Dupla de Feras", "Os Supremos", "Time do Mar","Tempestade de Areia", 
                "Saque Quente", "Dupla do Barulho", "Reis do Lob", "Areia no Pulmão", "Sol & Smash", "Os Mandachuvas da Praia", 
                "Dupla Nervosa", "Força do Saque", "Giro de Raquete", "Chama na Areia", "Ponto Quente", "Os Zicas da Rede", 
                "Combo de Smash", "Ritmo de Praia", "Tsunami de Ponto", "Areia Hardcore", "Impacto na Rede", "Dupla do Verão", 
                "Os Donos da Quadra", "Tempinho de Vitória", "Joga e Arrebenta", "Desce a Raquetada", "Fúria do Saque", 
                "Caiu na Rede, é Ponto", "Os Surfistas da Bola", "Equipe Treme Terra", "Dupla Animal", "Beach Power", "Ponto Rápido", 
                "Sem Recuo", "Explosão de Ponto", "Quarteto em Dupla", "Solta o Braço", "Dom da Rede", "Raquete Elétrica", "Dupla do Solzão", 
                "Beach Ninjas", "Areia Selvagem", "Tropa do Lob", "Dupla Estilo Livre", "Feras do Smash", "Chama no Voleio", "Duelos na Areia", 
                "Equipe Ponta Firme", "Os Rebatedores", "Praia no Sangue", "Dupla Caçadora de Ponto", "Esquadrão Sol & Areia", "Smash de Luxo"
            ];
            $nome_aleatorio = $nomes_duplas[array_rand($nomes_duplas)];
            ?>

            <section class="max-w-md mx-auto w-full bg-white rounded-2xl shadow-xl p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-3xl">📝</span>
                    <h1 class="text-2xl font-bold text-gray-800">Inscrever-se no Torneio</h1>
                </div>

                <div class="mb-4">
                    <p class="text-lg font-semibold text-gray-700"><?= htmlspecialchars($torneio['titulo']) ?></p>
                    <p class="text-sm text-gray-500">Arena: <?= htmlspecialchars($torneio['arena_titulo']) ?></p>
                </div>

                <!-- Exibição de mensagens de feedback -->
                <?php if (isset($_SESSION['mensagem'])) : ?>
                    <?php
                    $tipo_alerta = $_SESSION['mensagem'][0]; // 'success' ou 'danger'
                    $texto_alerta = $_SESSION['mensagem'][1];
                    $cor_alerta = ($tipo_alerta === 'success')
                        ? 'bg-green-100 border-green-400 text-green-700'
                        : 'bg-red-100 border-red-400 text-red-700';
                    ?>
                    <div class="border px-4 py-3 rounded-lg relative mb-4 <?= $cor_alerta ?>" role="alert">
                        <span class="block sm:inline"><?= htmlspecialchars($texto_alerta) ?></span>
                    </div>
                    <?php unset($_SESSION['mensagem']); ?>
                <?php endif; ?>

                <form action="controller-torneio/inscrever-dupla.php" method="POST" class="space-y-4">
                    <input type="hidden" name="torneio_id" value="<?= htmlspecialchars($torneio_id) ?>">
                    <input type="hidden" name="jogador1_id" value="<?= htmlspecialchars($usuario_id) ?>">

                    <!-- Escolher Categoria -->
                    <div class="form-control w-full">
                        <label class="label" for="categoria_id">
                            <span class="label-text font-semibold">Escolha a Categoria</span>
                        </label>
                        <select name="categoria_id" id="categoria_id" class="select select-bordered w-full" required>
                            <option value="" disabled selected>Selecione uma categoria</option>
                            <?php if (empty($categorias)) : ?>
                                <option disabled>Nenhuma categoria disponível para este torneio.</option>
                            <?php else : ?>
                                <?php foreach ($categorias as $categoria) : ?>
                                    <option value="<?= htmlspecialchars($categoria['id']) ?>"><?= htmlspecialchars($categoria['titulo']) ?> (<?= htmlspecialchars(ucfirst($categoria['genero'])) ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Título da Dupla -->
                    <div class="form-control w-full">
                        <label class="label" for="titulo_dupla">
                            <span class="label-text font-semibold">Título da Dupla</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="titulo_dupla" id="titulo_dupla" class="input input-bordered w-full" value="<?= htmlspecialchars($nome_aleatorio) ?>" readonly required>
                            <button type="button" id="sortear_nome_dupla" class="btn btn-ghost text-2xl" title="Sortear novo nome">🎲</button>
                        </div>
                    </div>

                    <!-- Selecionar Parceiro -->
                    <div class="form-control w-full">
                        <label class="label" for="jogador2">
                            <span class="label-text font-semibold">Selecione seu Parceiro</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="jogador2" class="input input-bordered w-full" placeholder="Busque seu parceiro..." autocomplete="off" required>
                            <input type="hidden" id="jogador2_id" name="jogador2_id">
                            <div id="dropdown_jogadores2" class="absolute left-0 bg-white border rounded-md shadow-lg w-full hidden z-20"></div>
                        </div>
                    </div>

                    <!-- Botão de Inscrição -->
                    <div class="mt-6">
                        <button type="submit" class="btn btn-primary w-full text-lg" <?= empty($categorias) ? 'disabled' : '' ?>>
                            Confirmar Inscrição
                        </button>
                    </div>
                </form>
            </section>
            <br><br><br>
        </main>
    </div>

    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function initializePlayerSearch(inputId, hiddenId, dropdownId) {
                const $input = $(`#${inputId}`);
                const $hiddenInput = $(`#${hiddenId}`);
                const $dropdown = $(`#${dropdownId}`);

                $input.on('input', function() {
                    let query = $(this).val();
                    $hiddenInput.val(''); // Clear hidden ID on new input

                    if (query.length < 2) {
                        $dropdown.hide();
                        return;
                    }

                    const excludeIds = [$('#jogador1_id').val()]; // Exclui o próprio usuário

                    $.ajax({
                        url: 'controller-usuario/ajax-jogadores.php',
                        method: 'GET',
                        data: { q: query, exclude: excludeIds.join(',') },
                        success: function(data) {
                            let jogadores = JSON.parse(data);
                            $dropdown.empty();

                            if (jogadores.length > 0) {
                                jogadores.forEach(function(jogador) {
                                    let displayText = `${jogador.nome_completo} (${jogador.apelido}) - Rating: ${jogador.rating}`;
                                    $dropdown.append(`<div class="p-2 hover:bg-gray-200 cursor-pointer text-sm" data-id="${jogador.identificador}" data-name="${jogador.nome_completo}">${displayText}</div>`);
                                });
                                $dropdown.show();
                            } else {
                                $dropdown.hide();
                            }
                        }
                    });
                });

                $dropdown.on('click', 'div', function() {
                    $input.val($(this).data('name'));
                    $hiddenInput.val($(this).data('id'));
                    $dropdown.hide();
                });
            }

            initializePlayerSearch('jogador2', 'jogador2_id', 'dropdown_jogadores2');

            // Sorteio de nome de dupla
            const nomesDuplas = <?= json_encode($nomes_duplas) ?>;
            const tituloDuplaInput = $('#titulo_dupla');
            const sortearNomeBtn = $('#sortear_nome_dupla');

            sortearNomeBtn.on('click', function() {
                const nomeAleatorio = nomesDuplas[Math.floor(Math.random() * nomesDuplas.length)];
                tituloDuplaInput.val(nomeAleatorio);
            });

            // Hide dropdowns when clicking outside
            $(document).on('click', function(event) {
                if (!$(event.target).closest('.relative').length) {
                    $('.absolute.bg-white').hide();
                }
            });
        });
    </script>

</body>

</html>