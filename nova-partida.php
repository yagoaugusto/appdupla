<?php require_once '#_global.php';
require_once 'system-classes/Funcoes.php' ?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php'; ?>

    <?php $listaJogadores = Usuario::listar_usuarios(); ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php'; ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-2 sm:p-6 bg-gradient-to-br from-blue-100 via-white to-red-100 min-h-screen flex flex-col items-center justify-start">
            <section class="w-full max-w-md bg-white/95 rounded-2xl shadow-xl border border-blue-200 mt-4 mb-6 px-3 py-6 flex flex-col items-center backdrop-blur-md">
                <div class="w-full text-center mb-6">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-pink-500 to-red-600 mb-2 tracking-tight drop-shadow-lg">Registrar Partida</h1>
                    <p class="text-xs sm:text-base text-gray-600 font-medium">Cada ponto é uma conquista. Compartilhe sua jornada!</p>
                </div>
                <form id="formPlacar" action="controller-partida/salvar-partida.php" method="POST" class="w-full space-y-6">
                    <fieldset class="border border-blue-300 rounded-xl p-3 bg-blue-50/80 shadow-inner">
                        <legend class="text-base font-semibold text-blue-700 px-2">Seu Time</legend>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-200 text-blue-700 text-base font-bold shadow">1</span>
                            <div class="flex-1">
                                <label class="block mb-0.5 text-xs font-medium text-gray-700">Você</label>
                                <input type="text" id="jogador1" class="input input-bordered w-full bg-gray-100 cursor-not-allowed text-xs force-white-bg" placeholder="Seu nome" autocomplete="off" value="<?= htmlspecialchars($_SESSION['DuplaUserNome']) . (!empty($_SESSION['DuplaUserApelido']) ? ' (' . htmlspecialchars($_SESSION['DuplaUserApelido']) . ')' : '') ?>" disabled>
                                <input type="hidden" id="jogador1_id" name="jogador1_id" value="<?= $_SESSION['DuplaUserId'] ?>">
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-200 text-blue-700 text-base font-bold shadow">2</span>
                            <div class="flex-1 relative">
                                <label class="block mb-0.5 text-xs font-medium text-gray-700">Seu parceiro</label>
                                <input required type="text" id="jogador2" class="input input-bordered w-full text-xs focus:ring-2 focus:ring-blue-400 bg-white force-white-bg" placeholder="Busque seu parceiro..." autocomplete="off">
                                <input type="hidden" id="jogador2_id" name="jogador2_id">
                                <div id="dropdown_jogadores2" class="absolute left-0 bg-white border rounded-md shadow-lg w-full hidden z-20"></div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="border border-red-300 rounded-xl p-3 bg-red-50/80 shadow-inner">
                        <legend class="text-base font-semibold text-red-700 px-2">Adversário</legend>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-200 text-red-700 text-base font-bold shadow">1</span>
                            <div class="flex-1 relative">
                                <label class="block mb-0.5 text-xs font-medium text-gray-700">Atleta 1</label>
                                <input required type="text" id="jogador3" class="input input-bordered w-full text-xs focus:ring-2 focus:ring-red-400 bg-white force-white-bg" placeholder="Busque o adversário..." autocomplete="off">
                                <input type="hidden" id="jogador3_id" name="jogador3_id">
                                <div id="dropdown_jogadores3" class="absolute left-0 bg-white border rounded-md shadow-lg w-full hidden z-20"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-200 text-red-700 text-base font-bold shadow">2</span>
                            <div class="flex-1 relative">
                                <label class="block mb-0.5 text-xs font-medium text-gray-700">Atleta 2</label>
                                <input required type="text" id="jogador4" class="input input-bordered w-full text-xs focus:ring-2 focus:ring-red-400 bg-white force-white-bg" placeholder="Busque o parceiro do adversário..." autocomplete="off">
                                <input type="hidden" id="jogador4_id" name="jogador4_id">
                                <div id="dropdown_jogadores4" class="absolute left-0 bg-white border rounded-md shadow-lg w-full hidden z-20"></div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="grid grid-cols-2 gap-3 mt-1">
                        <div class="bg-blue-100 rounded-lg p-3 shadow flex flex-col items-center">
                            <label class="block mb-1 text-xs font-semibold text-blue-700">Seu placar</label>
                            <input type="number" id="placarA" name="placar_a" class="input input-bordered w-24 text-center text-lg font-bold focus:ring-2 focus:ring-blue-400 bg-white force-white-bg" required min="0">
                        </div>
                        <div class="bg-red-100 rounded-lg p-3 shadow flex flex-col items-center">
                            <label class="block mb-1 text-xs font-semibold text-red-700">Placar adversário</label>
                            <input type="number" id="placarB" name="placar_b" class="input input-bordered w-24 text-center text-lg font-bold focus:ring-2 focus:ring-red-400 bg-white force-white-bg" required min="0">
                        </div>
                    </div>

                    <div class="flex flex-col items-center mt-4">
                        <button type="submit" class="btn w-full text-sm py-2 rounded-xl shadow-lg bg-gradient-to-r from-blue-600 via-pink-500 to-red-500 hover:from-blue-700 hover:to-red-600 transition-all font-bold tracking-wide text-white uppercase">
                            Registrar
                        </button>
                        <p class="mt-2 text-gray-500 text-[10px] text-center italic">Sua história está sendo escrita. Sinta orgulho de cada partida!</p>
                    </div>
                </form>

                <!-- Modal -->
                <div id="modalAlerta" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                    <div class="bg-white rounded-lg shadow-lg p-4 max-w-xs text-center">
                        <h2 class="text-lg font-bold mb-2" id="modalTitulo">Atenção</h2>
                        <p id="modalMensagem" class="text-gray-700 mb-4 text-sm">Mensagem do modal aqui.</p>
                        <button onclick="fecharModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-sm">
                            Ok
                        </button>
                    </div>
                </div>

                <!-- Modal de Loading Divertido -->
                <div id="modalLoading" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
                    <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center gap-3 max-w-xs text-center">
                        <div class="animate-spin rounded-full border-4 border-blue-400 border-t-transparent h-12 w-12 mb-2"></div>
                        <div class="font-bold text-blue-700 text-base" id="loadingMensagem">Validando sua partida...</div>
                        <div class="text-xs text-gray-500">Aguarde um instante 🏖️</div>
                    </div>
                </div>
            </section>
            <footer class="w-full max-w-md text-center mt-2 text-[10px] text-gray-400">
                Beach Tennis é mais do que um jogo. É sobre evolução, amizade e superação.
            </footer>
        </main>
    </div>

    <?php require_once '_footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const form = document.getElementById('formPlacar');
        const modal = document.getElementById('modalAlerta');
        const modalTitulo = document.getElementById('modalTitulo');
        const modalMensagem = document.getElementById('modalMensagem');
        const modalLoading = document.getElementById('modalLoading');
        const loadingMensagem = document.getElementById('loadingMensagem');

        const mensagensLoading = [
            "Estamos planejando a quadra...",
            "Fomos buscar a bolinha...",
            "Estamos tratando a raquete...",
            "Passando protetor solar...",
            "Tirando a areia do short...",
            "Aquecendo os motores...",
            "Conferindo a rede...",
            "Trocando a água do cooler...",
            "Limpando os óculos de sol...",
            "Ajeitando o chapéu de palha...",
            "Dando aquela esticada...",
            "Tirando selfie com a galera...",
            "Ajeitando a faixa do campeão...",
            "Tirando foto para o ranking...",
            "Aplaudindo a torcida...",
            "Tirando a selfie da vitória...",
            "Ajeitando o placar eletrônico...",
            "Tirando a areia da raquete...",
            "Dando aquela respirada funda...",
            "Fazendo dancinha da vitória..."
        ];

        function abrirModal(titulo, mensagem) {
            modalTitulo.textContent = titulo;
            modalMensagem.textContent = mensagem;
            modal.classList.remove('hidden');
        }

        function fecharModal() {
            modal.classList.add('hidden');
        }

        function mostrarLoadingAleatorio() {
            const msg = mensagensLoading[Math.floor(Math.random() * mensagensLoading.length)];
            loadingMensagem.textContent = msg;
            modalLoading.classList.remove('hidden');
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const a = parseInt(document.getElementById('placarA').value);
            const b = parseInt(document.getElementById('placarB').value);

            if (isNaN(a) || isNaN(b)) {
                abrirModal('Erro de preenchimento', 'Preencha os dois placares antes de enviar.');
                return;
            }

            if (a === b) {
                abrirModal('Placar inválido', 'Empates não são permitidos.');
                return;
            }

            const j1 = document.getElementById('jogador1_id').value;
            const j2 = document.getElementById('jogador2_id').value;
            const j3 = document.getElementById('jogador3_id').value;
            const j4 = document.getElementById('jogador4_id').value;

            if (!j2 || !j3 || !j4) {
                abrirModal('Jogadores Incompletos', 'Por favor, selecione todos os 4 jogadores para a partida. Digite o nome e clique na opção desejada.');
                return;
            }

            const jogadoresSelecionados = [j1, j2, j3, j4];
            const jogadoresUnicos = new Set(jogadoresSelecionados);

            if (jogadoresSelecionados.length !== jogadoresUnicos.size) {
                abrirModal('Jogadores Repetidos', 'Cada jogador só pode ser selecionado uma vez. Verifique os nomes e selecione novamente se necessário.');
                return;
            }

            const vencedor = Math.max(a, b);
            const permitidos = [4, 6, 7];

            if (!permitidos.includes(vencedor)) {
                abrirModal('Placar inválido', 'O vencedor só pode ter 4, 6 ou 7 pontos.');
                return;
            }

            // Mostra loading divertido e envia o formulário
            mostrarLoadingAleatorio();
            setTimeout(() => form.submit(), 900); // Pequeno delay para o loading aparecer
        });

        // --- Refactored Player Search ---
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

                // Get IDs of already selected players to exclude them from the search
                let excludeIds = [];
                $('input[name$="_id"]').each(function() {
                    if ($(this).val() && $(this).attr('id') !== hiddenId) {
                        excludeIds.push($(this).val());
                    }
                });

                $.ajax({
                    url: 'controller-usuario/ajax-jogadores.php',
                    method: 'GET',
                    data: {
                        q: query,
                        exclude: excludeIds // NOTE: Backend needs to handle this parameter
                    },
                    success: function(data) {
                        let jogadores = JSON.parse(data);
                        $dropdown.empty();

                        if (jogadores.length > 0) {
                            jogadores.forEach(function(jogador) {
                                let displayText = `${jogador.nome_completo} (${jogador.apelido}) - ${jogador.rating}`;
                                $dropdown.append(`<div class="p-2 hover:bg-gray-200 cursor-pointer text-xs" data-id="${jogador.identificador}" data-name="${jogador.nome_completo}">${displayText}</div>`);
                            });
                            $dropdown.show();
                        } else {
                            $dropdown.hide();
                        }
                    }
                });
            });

            $dropdown.on('click', 'div', function() {
                $input.val($(this).data('name')); // Use just the name for the input
                $hiddenInput.val($(this).data('id'));
                $dropdown.hide();
            });
        }

        $(document).ready(function() {
            initializePlayerSearch('jogador2', 'jogador2_id', 'dropdown_jogadores2');
            initializePlayerSearch('jogador3', 'jogador3_id', 'dropdown_jogadores3');
            initializePlayerSearch('jogador4', 'jogador4_id', 'dropdown_jogadores4');

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