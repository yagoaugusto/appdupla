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
            <section class="max-w-6xl mx-auto w-full bg-white rounded-2xl shadow-xl p-6 md:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <span class="text-3xl">🔍</span>
                    <h1 class="text-2xl font-bold text-gray-800">Encontrar Torneios</h1>
                </div>

                <p class="text-gray-600 mb-4">Explore os torneios mais recentes ou use a busca para encontrar um específico.</p>

                <!-- Barra de Busca e Filtros -->
                <div class="mb-8 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label for="buscaTorneio" class="block text-sm font-medium text-gray-700 mb-1">Buscar por nome</label>
                            <input type="text" id="buscaTorneio" placeholder="Nome do torneio ou arena..." class="input input-bordered w-full">
                        </div>
                        <div>
                            <label for="filtroStatus" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="filtroStatus" class="select select-bordered w-full">
                                <option value="todos">Todos</option>
                                <option value="aberto">Inscrições Abertas</option>
                                <option value="andamento">Em Andamento</option>
                                <option value="finalizado">Finalizado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Container dos Torneios -->
                <div id="listaTorneios" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    // Função auxiliar para determinar o status do torneio
                    function get_torneio_status($torneio) {
                        $agora = new DateTime();
                        $inicio_insc = new DateTime($torneio['inicio_inscricao']);
                        $fim_insc = new DateTime($torneio['fim_inscricao']);
                        $inicio_torneio = new DateTime($torneio['inicio_torneio']);
                        $fim_torneio = new DateTime($torneio['fim_torneio']);

                        if ($agora >= $inicio_torneio && $agora <= $fim_torneio) return ['label' => 'Em Andamento', 'color' => 'badge-warning'];
                        if ($agora >= $inicio_insc && $agora <= $fim_insc) return ['label' => 'Inscrições Abertas', 'color' => 'badge-success'];
                        if ($agora > $fim_torneio) return ['label' => 'Finalizado', 'color' => 'badge-error'];
                        if ($agora < $inicio_insc) return ['label' => 'Em Breve', 'color' => 'badge-info'];
                        return ['label' => 'Fechado', 'color' => 'badge-ghost'];
                    }

                    $recent_torneios = Torneio::getRecentTorneios(12); // Limite de 12 torneios
                    if (empty($recent_torneios)) :
                    ?>
                        <p class="col-span-full text-center text-gray-500 italic">Nenhum torneio encontrado no momento.</p>
                    <?php
                    else :
                        foreach ($recent_torneios as $torneio) :
                            $status = get_torneio_status($torneio);
                    ?>
                            <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-shadow duration-300 cursor-pointer card-torneio" data-torneio-id="<?= htmlspecialchars($torneio['id']) ?>">
                                <div class="card-body p-4">
                                    <h2 class="card-title text-lg mb-1 truncate"><?= htmlspecialchars($torneio['titulo']) ?></h2>
                                    <div class="badge <?= $status['color'] ?> badge-sm text-white mb-2"><?= $status['label'] ?></div>
                                    <p class="text-sm text-gray-600 mb-2">Arena: <span class="font-semibold"><?= htmlspecialchars($torneio['arena_titulo']) ?></span></p>
                                    <div class="text-xs text-gray-500 space-y-1">
                                        <p>Início: <?= date('d/m/Y H:i', strtotime($torneio['inicio_torneio'])) ?></p>
                                        <p>Fim: <?= date('d/m/Y H:i', strtotime($torneio['fim_torneio'])) ?></p>
                                    </div>
                                </div>
                            </div>
                    <?php
                        endforeach;
                    endif;
                    ?>
                </div>
                <!-- Skeleton Loader (para feedback de busca) -->
                <div id="skeletonLoader" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <div class="flex flex-col gap-4 w-full bg-white p-4 rounded-2xl shadow-lg">
                        <div class="skeleton h-6 w-3/4"></div>
                        <div class="skeleton h-4 w-1/2"></div>
                        <div class="skeleton h-4 w-full"></div>
                        <div class="skeleton h-4 w-full"></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </section>
            <br><br><br>
        </main>
    </div>

    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>

    <!-- Modal de Detalhes do Torneio -->
    <div id="modalTorneioDetalhes" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col relative max-h-[90vh]">
            <!-- Cabeçalho Fixo -->
            <div class="p-6 border-b border-gray-200 flex-shrink-0">
                <button id="fecharModalTorneioDetalhes" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 text-3xl font-bold">&times;</button>
                <h2 id="modalTorneioTitulo" class="text-2xl font-bold text-gray-800 pr-8"></h2>
                <p id="modalTorneioArena" class="text-sm text-gray-500 mt-1"></p>
                <a id="btnInscreverTorneio" href="#" class="btn btn-primary w-full mt-4">Inscrever-se</a>
            </div>

            <!-- Conteúdo Rolável -->
            <div class="p-6 overflow-y-auto space-y-6">
                <div>
                    <h3 class="font-bold text-lg mb-2 flex items-center gap-2"><span class="text-xl">ℹ️</span> Sobre o Torneio</h3>
                    <p id="modalTorneioSobre" class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg border"></p>
                </div>
                <!-- Seção para Cronograma -->
                <div>
                    <h3 class="font-bold text-lg mb-2 flex items-center gap-2"><span class="text-xl">🗓️</span> Cronograma</h3>
                    <div class="space-y-2 text-sm">
                        <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                            <p class="font-semibold text-blue-800">Início das Inscrições:</p>
                            <p id="modalInicioInscricao" class="font-mono text-blue-700"></p>
                        </div>
                        <div class="bg-blue-50 p-3 rounded-lg border border-blue-200">
                            <p class="font-semibold text-blue-800">Fim das Inscrições:</p>
                            <p id="modalFimInscricao" class="font-mono text-blue-700"></p>
                        </div>
                        <div class="bg-green-50 p-3 rounded-lg border border-green-200 mt-2">
                            <p class="font-semibold text-green-800">Início do Torneio:</p>
                            <p id="modalInicioTorneio" class="font-mono text-green-700"></p>
                        </div>
                        <div class="bg-green-50 p-3 rounded-lg border border-green-200">
                            <p class="font-semibold text-green-800">Fim do Torneio:</p>
                            <p id="modalFimTorneio" class="font-mono text-green-700"></p>
                        </div>
                    </div>
                </div>
                <!-- Seção para Valores de Inscrição -->
                <div>
                    <h3 class="font-bold text-lg mb-2 flex items-center gap-2"><span class="text-xl">💰</span> Valores de Inscrição</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border">
                            <span class="font-semibold text-gray-700">1ª Inscrição:</span>
                            <span id="modalValorPrimeiraInsc" class="font-bold text-green-600"></span>
                        </div>
                        <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border">
                            <span class="font-semibold text-gray-700">2ª Inscrição:</span>
                            <span id="modalValorSegundaInsc" class="font-bold text-green-600"></span>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-2 flex items-center gap-2"><span class="text-xl">🏷️</span> Categorias</h3>
                    <div id="modalTorneioCategorias" class="space-y-2">
                        <!-- Categorias serão carregadas aqui via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const modal = $('#modalTorneioDetalhes');
            const fecharModalBtn = $('#fecharModalTorneioDetalhes');
            const modalTitulo = $('#modalTorneioTitulo');
            const modalArena = $('#modalTorneioArena');
            const modalSobre = $('#modalTorneioSobre');
            const modalInicioInscricao = $('#modalInicioInscricao');
            const modalFimInscricao = $('#modalFimInscricao');
            const modalInicioTorneio = $('#modalInicioTorneio');
            const modalFimTorneio = $('#modalFimTorneio');
            const modalCategorias = $('#modalTorneioCategorias');
            const btnInscreverTorneio = $('#btnInscreverTorneio');

            $('#listaTorneios').on('click', '.card-torneio', function() {
                const torneioId = $(this).data('torneio-id');
                
                // Limpa o conteúdo anterior do modal
                modalTitulo.text('Carregando...');
                modalArena.text('');
                modalInicioInscricao.text('');
                modalFimInscricao.text('');
                modalInicioTorneio.text('');
                modalFimTorneio.text('');
                modalSobre.text('Carregando...');
                modalCategorias.html('<p class="text-gray-500 italic">Carregando categorias...</p>');
                btnInscreverTorneio.attr('href', '#'); // Reset href
                modal.removeClass('hidden');

                // Faz a requisição AJAX para buscar os detalhes do torneio
                $.ajax({
                    url: 'controller-torneio/get-torneio-details.php',
                    method: 'GET',
                    data: { id: torneioId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            const torneio = response.data.torneio;
                            const categorias = response.data.categorias;

                            modalTitulo.text(torneio.titulo);
                            modalArena.text('Arena: ' + torneio.arena_titulo);
                            modalSobre.html(torneio.sobre ? torneio.sobre.replace(/\n/g, '<br>') : 'Nenhuma descrição fornecida.');
                            
                            // Preenche os novos campos de data
                            modalInicioInscricao.text(new Date(torneio.inicio_inscricao).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }));
                            modalFimInscricao.text(new Date(torneio.fim_inscricao).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }));
                            modalInicioTorneio.text(new Date(torneio.inicio_torneio).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }));
                            modalFimTorneio.text(new Date(torneio.fim_torneio).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }));

                            // Preenche os novos campos de valor
                            $('#modalValorPrimeiraInsc').text('R$ ' + parseFloat(torneio.valor_primeira_insc).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                            $('#modalValorSegundaInsc').text('R$ ' + parseFloat(torneio.valor_segunda_insc).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                            // Define o link de inscrição
                            btnInscreverTorneio.attr('href', `inscrever-torneio.php?torneio_id=${torneio.id}`);
                            
                            if (categorias.length > 0) {
                                let categoriasHtml = '';
                                categorias.forEach(cat => {
                                    categoriasHtml += `<div class="flex items-center justify-between bg-white p-2 rounded-lg border border-gray-200 shadow-sm">
                                                        <span class="font-semibold text-gray-700">${cat.titulo}</span>
                                                        <span class="badge badge-outline badge-sm capitalize">${cat.genero}</span>
                                                    </div>`;
                                });
                                modalCategorias.html(categoriasHtml);
                            } else {
                                modalCategorias.html('<p class="text-gray-500 italic">Nenhuma categoria cadastrada para este torneio.</p>');
                            }
                        } else {
                            modalTitulo.text('Erro');
                            modalSobre.text(response.message || 'Não foi possível carregar os detalhes do torneio.');
                            modalCategorias.html('');
                        }
                    },
                    error: function() {
                        modalTitulo.text('Erro de Conexão');
                        modalSobre.text('Não foi possível carregar os detalhes do torneio. Verifique sua conexão.');
                        modalCategorias.html('');
                    }
                });
            });

            fecharModalBtn.on('click', function() {
                modal.addClass('hidden');
            });

            // Fecha o modal ao clicar fora dele
            modal.on('click', function(e) {
                if ($(e.target).is(modal)) {
                    modal.addClass('hidden');
                }
            });

            // Lógica para busca e filtro de torneios
            const buscaTorneioInput = $('#buscaTorneio');
            const filtroStatusSelect = $('#filtroStatus');
            const listaTorneiosDiv = $('#listaTorneios');
            const skeletonLoaderDiv = $('#skeletonLoader');

            let searchTimeout = null;

            function performSearch() {
                const searchTerm = buscaTorneioInput.val();
                const statusFilter = filtroStatusSelect.val();

                // Mostra o skeleton loader e esconde a lista atual
                listaTorneiosDiv.addClass('hidden');
                skeletonLoaderDiv.removeClass('hidden');

                $.ajax({
                    url: 'controller-torneio/search-torneios.php',
                    method: 'GET',
                    data: {
                        q: searchTerm,
                        status: statusFilter,
                        limit: 12 // Mantém o limite de 12 para a exibição inicial
                    },
                    dataType: 'json',
                    success: function(response) {
                        skeletonLoaderDiv.addClass('hidden'); // Esconde o skeleton
                        listaTorneiosDiv.removeClass('hidden'); // Mostra a lista

                        if (response.success && response.torneios.length > 0) {
                            let torneiosHtml = '';
                            response.torneios.forEach(torneio => {
                                // Reutiliza a lógica de status do PHP para o JS
                                const agora = new Date();
                                const inicio_insc = new Date(torneio.inicio_inscricao);
                                const fim_insc = new Date(torneio.fim_inscricao);
                                const inicio_torneio = new Date(torneio.inicio_torneio);
                                const fim_torneio = new Date(torneio.fim_torneio);

                                let statusLabel = 'Fechado';
                                let statusColor = 'badge-ghost';

                                if (agora >= inicio_torneio && agora <= fim_torneio) { statusLabel = 'Em Andamento'; statusColor = 'badge-warning'; }
                                else if (agora >= inicio_insc && agora <= fim_insc) { statusLabel = 'Inscrições Abertas'; statusColor = 'badge-success'; }
                                else if (agora > fim_torneio) { statusLabel = 'Finalizado'; statusColor = 'badge-error'; }
                                else if (agora < inicio_insc) { statusLabel = 'Em Breve'; statusColor = 'badge-info'; }

                                torneiosHtml += `
                                    <div class="card bg-base-100 shadow-lg hover:shadow-xl transition-shadow duration-300 cursor-pointer card-torneio" data-torneio-id="${torneio.id}">
                                        <div class="card-body p-4">
                                            <h2 class="card-title text-lg mb-1 truncate">${torneio.titulo}</h2>
                                            <div class="badge ${statusColor} badge-sm text-white mb-2">${statusLabel}</div>
                                            <p class="text-sm text-gray-600 mb-2">Arena: <span class="font-semibold">${torneio.arena_titulo}</span></p>
                                            <div class="text-xs text-gray-500 space-y-1">
                                                <p>Início: ${new Date(torneio.inicio_torneio).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                                                <p>Fim: ${new Date(torneio.fim_torneio).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                                            </div>
                                        </div>
                                    </div>`;
                            });
                            listaTorneiosDiv.html(torneiosHtml);
                        } else {
                            listaTorneiosDiv.html('<p class="col-span-full text-center text-gray-500 italic">Nenhum torneio encontrado com os critérios de busca.</p>');
                        }
                    },
                    error: function() {
                        skeletonLoaderDiv.addClass('hidden');
                        listaTorneiosDiv.removeClass('hidden');
                        listaTorneiosDiv.html('<p class="col-span-full text-center text-red-500 italic">Erro ao carregar torneios. Tente novamente.</p>');
                    }
                });
            }

            // Event listeners para busca e filtro
            $('#buscaTorneio, #filtroStatus').on('input change', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 300); // Debounce de 300ms
            });
        });
    </script>

</body>

</html>