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
            <section class="max-w-6xl mx-auto w-full">
                <!-- Header Section -->
                <div class="text-center mb-8">
                    <span class="text-6xl mb-4 block animate-pulse">✨</span>
                    <h1 class="text-4xl font-extrabold text-gray-800 mb-2">Descubra seu Próximo Desafio</h1>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">Encontre torneios de Beach Tennis perto de você ou explore eventos incríveis. Sua próxima vitória espera!</p>
                </div>

                <!-- Search & Filter Section -->
                <div class="sticky top-0 z-10 bg-gray-100 py-4 -mx-4 px-4 sm:px-0 sm:-mx-0 rounded-b-2xl shadow-md mb-8">
                    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-inner">
                        <div class="mb-4">
                            <label for="buscaTorneio" class="sr-only">Buscar torneio</label>
                            <div class="relative">
                                <input type="text" id="buscaTorneio" placeholder="Buscar por nome do torneio ou arena..." class="input input-bordered w-full pr-10 text-lg force-white-bg">
                                <button id="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status do Torneio</label>
                            <div class="flex flex-wrap gap-2">
                                <input type="radio" name="statusFilterRadio" id="statusTodos" value="todos" class="radio hidden" checked />
                                <label for="statusTodos" class="btn btn-sm btn-outline btn-primary status-filter-btn" role="button">Todos</label>

                                <input type="radio" name="statusFilterRadio" id="statusAberto" value="aberto" class="radio hidden" />
                                <label for="statusAberto" class="btn btn-sm btn-outline btn-success status-filter-btn" role="button">Inscrições Abertas</label>

                                <input type="radio" name="statusFilterRadio" id="statusAndamento" value="andamento" class="radio hidden" />
                                <label for="statusAndamento" class="btn btn-sm btn-outline btn-warning status-filter-btn" role="button">Em Andamento</label>

                                <input type="radio" name="statusFilterRadio" id="statusFinalizado" value="finalizado" class="radio hidden" />
                                <label for="statusFinalizado" class="btn btn-sm btn-outline btn-error status-filter-btn" role="button">Finalizado</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tournament List Container -->
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
                            $status = get_torneio_status($torneio); // Re-using the helper function
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
                <div id="skeletonLoader" class="hidden grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                    <div class="flex flex-col gap-4 w-full bg-white p-4 rounded-2xl shadow-lg">
                        <div class="skeleton h-6 w-3/4"></div>
                        <div class="skeleton h-4 w-1/2"></div>
                        <div class="skeleton h-4 w-full"></div>
                        <div class="skeleton h-4 w-full"></div>
                    </div>
                    <?php endfor; ?>
                </div>
                <!-- No Tournaments Found Message -->
                <div id="noResultsMessage" class="hidden text-center py-10">
                    <span class="text-6xl block mb-4">😔</span>
                    <h3 class="text-2xl font-bold text-gray-700">Ops! Nenhum torneio encontrado.</h3>
                    <p class="text-gray-500 mt-2">Tente ajustar seus filtros ou buscar por outro nome.</p>
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
            const buscaTorneioInput = $('#buscaTorneio'); // Search input
            const statusFilterRadios = $('input[name="statusFilterRadio"]'); // Radio buttons for status filter
            const listaTorneiosDiv = $('#listaTorneios');
            const skeletonLoaderDiv = $('#skeletonLoader');
            const noResultsMessageDiv = $('#noResultsMessage'); // New element for no results message

            let searchTimeout = null;

            // Function to perform the search and update the list
            function performSearch() {
                const searchTerm = buscaTorneioInput.val();
                const statusFilter = statusFilterRadios.filter(':checked').val(); // Get selected radio value

                // Mostra o skeleton loader e esconde a lista atual
                listaTorneiosDiv.addClass('hidden');
                skeletonLoaderDiv.removeClass('hidden');

                $.ajax({
                    url: 'controller-torneio/search-torneios.php',
                    method: 'GET',
                    data: {
                        q: searchTerm, // Search term
                        status: statusFilter,
                        limit: 12 // Mantém o limite de 12 para a exibição inicial
                    },
                    dataType: 'json',
                    success: function(response) {
                        skeletonLoaderDiv.addClass('hidden'); // Esconde o skeleton
                        
                        if (response.success && response.torneios.length > 0) { // If tournaments are found
                            listaTorneiosDiv.removeClass('hidden'); // Show list
                            noResultsMessageDiv.addClass('hidden'); // Hide no results message
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
                        } else { // If no tournaments are found
                            listaTorneiosDiv.html(''); // Clear list
                            noResultsMessageDiv.removeClass('hidden'); // Show no results message
                        }
                    },
                    error: function() {
                        skeletonLoaderDiv.addClass('hidden');
                        listaTorneiosDiv.removeClass('hidden');
                        listaTorneiosDiv.html(''); // Clear list
                        noResultsMessageDiv.removeClass('hidden'); // Show no results message
                        noResultsMessageDiv.html('<span class="text-6xl block mb-4">⚠️</span><h3 class="text-2xl font-bold text-red-700">Erro ao carregar torneios.</h3><p class="text-red-500 mt-2">Ocorreu um problema na comunicação com o servidor. Tente novamente mais tarde.</p>');
                    }
                });
            }

            // Event listeners for search input
            buscaTorneioInput.on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 300); // Debounce de 300ms
                $('#clearSearch').toggleClass('hidden', $(this).val().length === 0); // Toggle clear button visibility
            });

            // Event listener for clear search button
            $('#clearSearch').on('click', function() {
                buscaTorneioInput.val('').trigger('input'); // Clear input and trigger search
            });

            // Event listeners for status filter radio buttons
            statusFilterRadios.on('change', function() {
                performSearch(); // Perform search immediately on filter change
                // Update active state for labels
                $('label.status-filter-btn').removeClass('btn-active'); // Remove from all
                $(`label[for="${$(this).attr('id')}"]`).addClass('btn-active'); // Add to the selected one
            });

            // Initial call to set active state for default checked radio on page load
            $('input[name="statusFilterRadio"]:checked').each(function() {
                $(`label[for="${$(this).attr('id')}"]`).addClass('btn-active');
            });

            // Initial search on page load
            performSearch();
        });
    </script>

</body>

</html>

</body>

</html>