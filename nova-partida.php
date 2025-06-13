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
                                <input type="text" id="jogador1" class="input input-bordered w-full bg-gray-100 cursor-not-allowed text-xs" placeholder="Seu nome" autocomplete="off" value="<?= $_SESSION['DuplaUserNome'] ?>" disabled>
                                <input type="hidden" id="jogador1_id" name="jogador1_id" value="<?= $_SESSION['DuplaUserId'] ?>">
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-200 text-blue-700 text-base font-bold shadow">2</span>
                            <div class="flex-1 relative">
                                <label class="block mb-0.5 text-xs font-medium text-gray-700">Seu parceiro</label>
                                <input required type="text" id="jogador2" class="input input-bordered w-full text-xs focus:ring-2 focus:ring-blue-400" placeholder="Busque seu parceiro..." autocomplete="off">
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
                                <input required type="text" id="jogador3" class="input input-bordered w-full text-xs focus:ring-2 focus:ring-red-400" placeholder="Busque o adversário..." autocomplete="off">
                                <input type="hidden" id="jogador3_id" name="jogador3_id">
                                <div id="dropdown_jogadores3" class="absolute left-0 bg-white border rounded-md shadow-lg w-full hidden z-20"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-200 text-red-700 text-base font-bold shadow">2</span>
                            <div class="flex-1 relative">
                                <label class="block mb-0.5 text-xs font-medium text-gray-700">Atleta 2</label>
                                <input required type="text" id="jogador4" class="input input-bordered w-full text-xs focus:ring-2 focus:ring-red-400" placeholder="Busque o parceiro do adversário..." autocomplete="off">
                                <input type="hidden" id="jogador4_id" name="jogador4_id">
                                <div id="dropdown_jogadores4" class="absolute left-0 bg-white border rounded-md shadow-lg w-full hidden z-20"></div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="grid grid-cols-2 gap-3 mt-1">
                        <div class="bg-blue-100 rounded-lg p-3 shadow flex flex-col items-center">
                            <label class="block mb-1 text-xs font-semibold text-blue-700">Seu placar</label>
                            <input type="number" id="placarA" name="placar_a" class="input input-bordered w-24 text-center text-lg font-bold focus:ring-2 focus:ring-blue-400" required min="0">
                        </div>
                        <div class="bg-red-100 rounded-lg p-3 shadow flex flex-col items-center">
                            <label class="block mb-1 text-xs font-semibold text-red-700">Placar adversário</label>
                            <input type="number" id="placarB" name="placar_b" class="input input-bordered w-24 text-center text-lg font-bold focus:ring-2 focus:ring-red-400" required min="0">
                        </div>
                    </div>

                    <div class="flex flex-col items-center mt-4">
                        <button type="submit" class="btn w-full text-sm py-2 rounded-xl shadow-lg bg-gradient-to-r from-blue-600 via-pink-500 to-red-500 hover:from-blue-700 hover:to-red-600 transition-all font-bold tracking-wide text-white uppercase">
                            Registrar
                        </button>
                        <p class="mt-2 text-gray-500 text-[10px] text-center italic">Sua história está sendo escrita. Sinta orgulho de cada partida!</p>
                    </div>

                    <datalist id="jogadores">
                        <?php foreach ($listaJogadores as $jogador): ?>
                            <option value="<?= $jogador['nome'] ?>">
                        <?php endforeach; ?>
                    </datalist>
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
            </section>
            <footer class="w-full max-w-md text-center mt-2 text-[10px] text-gray-400">
                Beach Tennis é mais do que um jogo. É sobre evolução, amizade e superação.
            </footer>
        </main>
    </div>

    <?php require_once '_footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // JOGADOR 1

        $(document).ready(function() {
            $('#jogador1').on('input', function() {
                let query = $(this).val();
                if (query.length < 2) {
                    $('#dropdown_jogadores1').hide();
                    return;
                }

                $.ajax({
                    url: 'controller-usuario/ajax-jogadores.php',
                    method: 'GET',
                    data: {
                        q: query
                    },
                    success: function(data) {
                        let jogadores = JSON.parse(data);
                        let dropdown = $('#dropdown_jogadores1');
                        dropdown.empty();

                        jogadores.forEach(function(jogador) {
                            dropdown.append(`<div class="p-2 hover:bg-gray-200 cursor-pointer" data-id="${jogador.identificador}">${jogador.nome_completo} ${jogador.apelido} ${jogador.rating}</div>`);
                        });

                        dropdown.show();
                    }
                });
            });

            // Seleciona um jogador e preenche os campos
            $(document).on('click', '#dropdown_jogadores1 div', function() {
                $('#jogador1').val($(this).text());
                $('#jogador1_id').val($(this).data('id'));
                $('#dropdown_jogadores1').hide();
            });

            // Esconde o dropdown quando clicar fora
            $(document).on('click', function(event) {
                if (!$(event.target).closest('#dropdown_jogadores1, #jogador1').length) {
                    $('#dropdown_jogadores1').hide();
                }
            });
        });



        // JOGADOR 2
        $(document).ready(function() {
            $('#jogador2').on('input', function() {
                let query = $(this).val();
                if (query.length < 2) {
                    $('#dropdown_jogadores2').hide();
                    return;
                }

                $.ajax({
                    url: 'controller-usuario/ajax-jogadores.php',
                    method: 'GET',
                    data: {
                        q: query
                    },
                    success: function(data) {
                        let jogadores = JSON.parse(data);
                        let dropdown = $('#dropdown_jogadores2');
                        dropdown.empty();

                        jogadores.forEach(function(jogador) {
                            dropdown.append(`<div class="p-2 hover:bg-gray-200 cursor-pointer" data-id="${jogador.identificador}">${jogador.nome_completo} ${jogador.apelido} ${jogador.rating}</div>`);
                        });

                        dropdown.show();
                    }
                });
            });

            // Seleciona um jogador e preenche os campos
            $(document).on('click', '#dropdown_jogadores2 div', function() {
                $('#jogador2').val($(this).text());
                $('#jogador2_id').val($(this).data('id'));
                $('#dropdown_jogadores2').hide();
            });

            // Esconde o dropdown quando clicar fora
            $(document).on('click', function(event) {
                if (!$(event.target).closest('#dropdown_jogadores2, #jogador2').length) {
                    $('#dropdown_jogadores2').hide();
                }
            });
        });



        // JOGADOR 3
        $(document).ready(function() {
            $('#jogador3').on('input', function() {
                let query = $(this).val();
                if (query.length < 2) {
                    $('#dropdown_jogadores3').hide();
                    return;
                }

                $.ajax({
                    url: 'controller-usuario/ajax-jogadores.php',
                    method: 'GET',
                    data: {
                        q: query
                    },
                    success: function(data) {
                        let jogadores = JSON.parse(data);
                        let dropdown = $('#dropdown_jogadores3');
                        dropdown.empty();

                        jogadores.forEach(function(jogador) {
                            dropdown.append(`<div class="p-2 hover:bg-gray-200 cursor-pointer" data-id="${jogador.identificador}">${jogador.nome_completo} ${jogador.apelido} ${jogador.rating}</div>`);
                        });

                        dropdown.show();
                    }
                });
            });

            // Seleciona um jogador e preenche os campos
            $(document).on('click', '#dropdown_jogadores3 div', function() {
                $('#jogador3').val($(this).text());
                $('#jogador3_id').val($(this).data('id'));
                $('#dropdown_jogadores3').hide();
            });

            // Esconde o dropdown quando clicar fora
            $(document).on('click', function(event) {
                if (!$(event.target).closest('#dropdown_jogadores3, #jogador3').length) {
                    $('#dropdown_jogadores3').hide();
                }
            });
        });




        // JOGADOR 2
        $(document).ready(function() {
            $('#jogador2').on('input', function() {
                let query = $(this).val();
                if (query.length < 2) {
                    $('#dropdown_jogadores2').hide();
                    return;
                }

                $.ajax({
                    url: 'controller-usuario/ajax-jogadores.php',
                    method: 'GET',
                    data: {
                        q: query
                    },
                    success: function(data) {
                        let jogadores = JSON.parse(data);
                        let dropdown = $('#dropdown_jogadores2');
                        dropdown.empty();

                        jogadores.forEach(function(jogador) {
                            dropdown.append(`<div class="p-2 hover:bg-gray-200 cursor-pointer" data-id="${jogador.identificador}">${jogador.nome_completo} ${jogador.apelido} ${jogador.rating}</div>`);
                        });

                        dropdown.show();
                    }
                });
            });

            // Seleciona um jogador e preenche os campos
            $(document).on('click', '#dropdown_jogadores2 div', function() {
                $('#jogador2').val($(this).text());
                $('#jogador2_id').val($(this).data('id'));
                $('#dropdown_jogadores2').hide();
            });

            // Esconde o dropdown quando clicar fora
            $(document).on('click', function(event) {
                if (!$(event.target).closest('#dropdown_jogadores2, #jogador2').length) {
                    $('#dropdown_jogadores2').hide();
                }
            });
        });



        // JOGADOR 4
        $(document).ready(function() {
            $('#jogador4').on('input', function() {
                let query = $(this).val();
                if (query.length < 2) {
                    $('#dropdown_jogadores4').hide();
                    return;
                }

                $.ajax({
                    url: 'controller-usuario/ajax-jogadores.php',
                    method: 'GET',
                    data: {
                        q: query
                    },
                    success: function(data) {
                        let jogadores = JSON.parse(data);
                        let dropdown = $('#dropdown_jogadores4');
                        dropdown.empty();

                        jogadores.forEach(function(jogador) {
                            dropdown.append(`<div class="p-2 hover:bg-gray-200 cursor-pointer" data-id="${jogador.identificador}">${jogador.nome_completo} ${jogador.apelido} ${jogador.rating}</div>`);
                        });

                        dropdown.show();
                    }
                });
            });

            // Seleciona um jogador e preenche os campos
            $(document).on('click', '#dropdown_jogadores4 div', function() {
                $('#jogador4').val($(this).text());
                $('#jogador4_id').val($(this).data('id'));
                $('#dropdown_jogadores4').hide();
            });

            // Esconde o dropdown quando clicar fora
            $(document).on('click', function(event) {
                if (!$(event.target).closest('#dropdown_jogadores4, #jogador4').length) {
                    $('#dropdown_jogadores4').hide();
                }
            });
        });
    </script>

    <script>
        const form = document.getElementById('formPlacar');
        const modal = document.getElementById('modalAlerta');
        const modalTitulo = document.getElementById('modalTitulo');
        const modalMensagem = document.getElementById('modalMensagem');

        function abrirModal(titulo, mensagem) {
            modalTitulo.textContent = titulo;
            modalMensagem.textContent = mensagem;
            modal.classList.remove('hidden');
        }

        function fecharModal() {
            modal.classList.add('hidden');
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

            const vencedor = Math.max(a, b);
            const permitidos = [4, 6, 7];

            if (!permitidos.includes(vencedor)) {
                abrirModal('Placar inválido', 'O vencedor só pode ter 4, 6 ou 7 pontos.');
                return;
            }

            // Se tudo estiver certo, envie o formulário
            form.submit();
        });
    </script>

</body>

</html>