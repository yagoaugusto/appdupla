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
        <main class="flex-1 p-6 bg-gradient-to-br from-blue-100 via-white to-red-100 min-h-screen flex flex-col items-center justify-start">
            <section class="w-full max-w-xl bg-white/90 rounded-3xl shadow-2xl border border-blue-200 mt-8 mb-10 px-8 py-10 flex flex-col items-center backdrop-blur-md">
                <div class="w-full text-center mb-8">
                    <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-pink-500 to-red-600 mb-3 tracking-tight drop-shadow-lg">Registrar Partida</h1>
                    <p class="text-base text-gray-600 font-medium">Cada ponto é uma conquista. Compartilhe sua jornada no Beach Tennis e inspire outros atletas!</p>
                </div>
                <form action="controller-partida/salvar-partida.php" method="POST" class="w-full space-y-8">
                    <fieldset class="border-2 border-blue-300 rounded-2xl p-6 bg-blue-50/70 shadow-inner">
                        <legend class="text-lg font-semibold text-blue-700 px-3">Seu Time</legend>
                        <div class="flex items-center gap-4 mt-4">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-200 text-blue-700 text-lg font-bold shadow">1</span>
                            <div class="flex-1">
                                <label class="block mb-1 text-xs font-medium text-gray-700">Você</label>
                                <input type="text" id="jogador1" class="input input-bordered w-full bg-gray-100 cursor-not-allowed text-sm" placeholder="Seu nome" autocomplete="off" value="<?= $_SESSION['DuplaUserNome'] ?>" disabled>
                                <input type="hidden" id="jogador1_id" name="jogador1_id" value="<?= $_SESSION['DuplaUserId'] ?>">
                            </div>
                        </div>
                        <div class="flex items-center gap-4 mt-6">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-200 text-blue-700 text-lg font-bold shadow">2</span>
                            <div class="flex-1 relative">
                                <label class="block mb-1 text-xs font-medium text-gray-700">Seu parceiro</label>
                                <input type="text" id="jogador2" class="input input-bordered w-full text-sm focus:ring-2 focus:ring-blue-400" placeholder="Busque seu parceiro..." autocomplete="off">
                                <input type="hidden" id="jogador2_id" name="jogador2_id">
                                <div id="dropdown_jogadores2" class="absolute left-0 bg-white border rounded-md shadow-lg w-full hidden z-20"></div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="border-2 border-red-300 rounded-2xl p-6 bg-red-50/70 shadow-inner">
                        <legend class="text-lg font-semibold text-red-700 px-3">Time Adversário</legend>
                        <div class="flex items-center gap-4 mt-4">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-200 text-red-700 text-lg font-bold shadow">1</span>
                            <div class="flex-1 relative">
                                <label class="block mb-1 text-xs font-medium text-gray-700">Atleta 1</label>
                                <input type="text" id="jogador3" class="input input-bordered w-full text-sm focus:ring-2 focus:ring-red-400" placeholder="Busque o adversário..." autocomplete="off">
                                <input type="hidden" id="jogador3_id" name="jogador3_id">
                                <div id="dropdown_jogadores3" class="absolute left-0 bg-white border rounded-md shadow-lg w-full hidden z-20"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 mt-6">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-200 text-red-700 text-lg font-bold shadow">2</span>
                            <div class="flex-1 relative">
                                <label class="block mb-1 text-xs font-medium text-gray-700">Atleta 2</label>
                                <input type="text" id="jogador4" class="input input-bordered w-full text-sm focus:ring-2 focus:ring-red-400" placeholder="Busque o parceiro do adversário..." autocomplete="off">
                                <input type="hidden" id="jogador4_id" name="jogador4_id">
                                <div id="dropdown_jogadores4" class="absolute left-0 bg-white border rounded-md shadow-lg w-full hidden z-20"></div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="grid grid-cols-2 gap-6 mt-2">
                        <div class="bg-blue-100 rounded-xl p-6 shadow flex flex-col items-center">
                            <label class="block mb-2 text-sm font-semibold text-blue-700">Seu placar</label>
                            <input type="number" name="placar_a" class="input input-bordered w-24 text-center text-xl font-bold focus:ring-2 focus:ring-blue-400" required min="0">
                        </div>
                        <div class="bg-red-100 rounded-xl p-6 shadow flex flex-col items-center">
                            <label class="block mb-2 text-sm font-semibold text-red-700">Placar adversário</label>
                            <input type="number" name="placar_b" class="input input-bordered w-24 text-center text-xl font-bold focus:ring-2 focus:ring-red-400" required min="0">
                        </div>
                    </div>

                    <div class="flex flex-col items-center mt-8">
                        <button type="submit" class="btn w-full text-base py-3 rounded-2xl shadow-xl bg-gradient-to-r from-blue-600 via-pink-500 to-red-500 hover:from-blue-700 hover:to-red-600 transition-all font-bold tracking-wide text-white uppercase">
                            Registrar Partida
                        </button>
                        <p class="mt-4 text-gray-500 text-xs text-center italic">Sua história está sendo escrita. Sinta orgulho de cada partida registrada!</p>
                    </div>

                    <datalist id="jogadores">
                        <?php foreach ($listaJogadores as $jogador): ?>
                            <option value="<?= $jogador['nome'] ?>">
                        <?php endforeach; ?>
                    </datalist>
                </form>
            </section>
            <footer class="w-full max-w-xl text-center mt-4 text-xs text-gray-400">
                Beach Tennis é mais do que um jogo. É sobre evolução, amizade e superação. Compartilhe cada vitória!
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

</body>

</html>