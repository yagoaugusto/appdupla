<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>

<body class="bg-gray-100 min-h-screen text-gray-800">

    <?php
    // Obtém o ID da arena da URL
    $arena_id = $_GET['id'] ?? null;

    if (!$arena_id) {
        // Redireciona se nenhum ID de arena for fornecido
        header('Location: principal.php'); // Ou para uma página genérica de listagem de arenas
        exit;
    }

    // Busca os detalhes da arena
    $arena = Arena::getArenaById($arena_id);

    $is_founder = ($_SESSION['DuplaUserId'] == $arena['fundador']);
    if (!$arena) {
        // Redireciona se a arena não for encontrada
        header('Location: principal.php'); // Ou para uma página 404
        exit;
    }

    // Busca os membros da arena
    $membros = Arena::getMembersByArenaId($arena_id); // Todos os membros
    $pending_members = Arena::getMembersByArenaId($arena_id, ['convidado', 'solicitado']); // Membros pendentes para o modal de convites

    // Verifica se o usuário logado é um membro (para o botão de "Sair")
    $is_member = false;
    foreach ($membros as $membro) {
        if ($membro['usuario_id'] == $_SESSION['DuplaUserId']) {
            $is_member = true;
            break;
        }
    }

    // Busca o ranking da arena (por simplicidade, usando o rating geral por enquanto)
    $ranking = Arena::getRankingByArenaId($arena_id);

    // Lida com mensagens de sucesso de redirecionamento (ex: após criar a arena)
    $success_message = '';
    if (isset($_GET['success']) && $_GET['success'] === 'arena_created') {
        $success_message = 'Arena criada com sucesso! Você é o fundador.';
    }

    ?>

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php'; ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php'; ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-4">
            <section class="max-w-4xl mx-auto w-full">

                <?php if ($success_message): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Sucesso!</strong>
                        <span class="block sm:inline"><?= htmlspecialchars($success_message) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($is_founder): ?>
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <button id="btnConvites" class="btn btn-primary w-full font-bold text-base">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Convites
                        </button>
                        <button id="btnEditarArena" class="btn btn-secondary w-full font-bold text-base">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar
                        </button>
                    </div>
                <?php endif; ?>


                <!-- Cabeçalho da Arena -->
                <div class="bg-white rounded-2xl shadow-xl p-6 mb-6 text-center border border-blue-200">
                    <div class="text-6xl mb-4"><?= htmlspecialchars($arena['bandeira']) ?></div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-pink-500 to-red-600 mb-2 drop-shadow-lg">
                        <?= htmlspecialchars($arena['titulo']) ?>
                    </h1>
                    <p class="text-sm sm:text-base text-gray-600 font-medium italic">"<?= htmlspecialchars($arena['lema']) ?>"</p>
                    <div class="mt-4 text-xs font-semibold text-gray-500">
                        Visibilidade: <span class="capitalize <?= $arena['privacidade'] === 'privada' ? 'text-red-500' : 'text-green-500' ?>"><?= htmlspecialchars($arena['privacidade']) ?></span>
                        <span class="mx-1">•</span>
                        Fundador: <span class="text-blue-600"><?= htmlspecialchars($arena['fundador_nome']) ?></span>
                    </div>
                </div>

                <!-- Seção de Membros -->
                <details class="collapse collapse-arrow bg-white rounded-2xl shadow-xl border border-gray-200 mb-6">
                    <summary class="collapse-title text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.125A7.5 7.5 0 0112 15.75a7.5 7.5 0 01-3 3.375m.75-12.75a3 3 0 114.5 0 3 3 0 01-4.5 0zM12 12.75a7.5 7.5 0 00-7.5 7.5h15a7.5 7.5 0 00-7.5-7.5z"></path>
                        </svg>
                        Membros da Arena (<?= count($membros) ?>)
                    </summary>
                    <div class="collapse-content">
                        <?php if (empty($membros)): ?>
                            <p class="text-gray-600 italic">Nenhum membro ainda. Convide seus amigos!</p>
                        <?php else: ?>
                            <ul class="space-y-2">
                                <?php foreach ($membros as $membro): ?>
                                    <li class="member-item flex items-center justify-between bg-gray-50 p-3 rounded-lg shadow-sm border border-gray-100">
                                        <div class="flex items-center gap-3">
                                            <span class="text-xl"><?= $membro['situacao'] === 'fundador' ? '👑' : '👤' ?></span>
                                            <span class="font-semibold text-gray-700"><?= htmlspecialchars($membro['nome']) ?></span>
                                            <?php if (!empty($membro['apelido'])): ?>
                                                <span class="text-sm text-gray-500">(<?= htmlspecialchars($membro['apelido']) ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-medium text-gray-500 capitalize"><?= htmlspecialchars($membro['situacao']) ?></span>
                                            <?php if ($is_founder && $membro['usuario_id'] != $_SESSION['DuplaUserId']): ?>
                                                <button class="btn btn-xs btn-outline btn-error" data-action="remove" data-usuario-id="<?= $membro['usuario_id'] ?>" data-arena-id="<?= $arena_id ?>">Remover</button>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </details>

                <!-- Seção de Ranking da Arena -->
                <details class="collapse collapse-arrow bg-white rounded-2xl shadow-xl border border-gray-200 mb-6">
                    <summary class="collapse-title text-xl font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.329 1.176l1.519 4.674c.3.921-.755 1.688-1.539 1.175l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.784.513-1.83-.254-1.539-1.175l1.519-4.674a1 1 0 00-.329-1.176l-3.976-2.888c-.784-.57-.381-1.81.588-1.81h4.915a1 1 0 00.95-.69l1.519-4.674z"></path>
                        </svg>
                        Ranking da Arena (<?= count($ranking) ?>)
                    </summary>
                    <div class="collapse-content">
                        <?php if (empty($ranking)): ?>
                            <p class="text-gray-600 italic">Nenhum jogador ranqueado nesta arena ainda.</p>
                        <?php else: ?>
                            <ul class="space-y-1">
                                <?php
                                $pos = 1;
                                foreach ($ranking as $jogador_rank):
                                    $is_current_user = ($jogador_rank['id'] == $_SESSION['DuplaUserId']);
                                    $li_classes = $is_current_user
                                        ? 'flex items-center gap-3 rounded-lg px-4 py-2 shadow-lg border-l-4 border-blue-600 bg-blue-50'
                                        : 'flex items-center gap-3 rounded-lg px-4 py-2 shadow-md border-l-4 border-gray-400 bg-white hover:bg-gray-50 transition-colors';
                                    $pos_classes = $is_current_user ? 'text-lg font-bold text-blue-700 w-8 text-center' : 'text-lg font-bold text-gray-600 w-8 text-center';
                                    $nome_classes = $is_current_user ? 'flex-1 font-bold text-blue-800' : 'flex-1 font-semibold text-gray-800';
                                    $rating_classes = $is_current_user ? 'bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full' : 'bg-gray-100 text-gray-700 text-xs font-bold px-2 py-1 rounded-full';
                                ?>
                                    <li class="<?= $li_classes ?>">
                                        <span class="<?= $pos_classes ?>"><?= $pos++ ?>º</span>
                                        <span class="<?= $nome_classes ?>">
                                            <?= htmlspecialchars($jogador_rank['nome']) ?>
                                            <?php if (!empty($jogador_rank['apelido'])): ?>
                                                <span class="text-xs text-gray-500 ml-1">(<?= htmlspecialchars($jogador_rank['apelido']) ?>)</span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="<?= $rating_classes ?>">⭐ <?= htmlspecialchars($jogador_rank['rating']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </details>

            </section>
        </main>
    </div>

    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>

    <!-- Modal de Convites -->
    <div id="modalConvites" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 max-w-full flex flex-col items-center gap-4 relative">
            <button id="fecharModalConvites" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
            <div class="text-2xl mb-2">✉️ <span class="font-extrabold text-blue-700">Convites da Arena</span></div>
            <p class="text-sm text-gray-600 text-center">Gerencie os convites e solicitações de adesão.</p>

            <?php if (empty($pending_members)): ?>
                <div class="text-center py-4">
                    <p class="text-gray-500 italic">Nenhum convite ou solicitação pendente.</p>
                </div>
            <?php else: ?>
                <ul class="w-full space-y-2">
                    <?php foreach ($pending_members as $member): ?>
                        <li class="member-item flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-700"><?= htmlspecialchars($member['nome']) ?></span>
                                <?php if (!empty($member['apelido'])): ?>
                                    <span class="text-sm text-gray-500">(<?= htmlspecialchars($member['apelido']) ?>)</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-gray-500 capitalize"><?= htmlspecialchars($member['situacao']) ?></span>
                                <!-- Botões de ação -->
                                <button class="btn btn-xs btn-success" data-action="accept" data-usuario-id="<?= htmlspecialchars($member['usuario_id']) ?>" data-arena-id="<?= htmlspecialchars($arena_id) ?>">✓</button>
                                <button class="btn btn-xs btn-error" data-action="reject" data-usuario-id="<?= htmlspecialchars($member['usuario_id']) ?>" data-arena-id="<?= htmlspecialchars($arena_id) ?>">✗</button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <hr class="my-4 w-full border-t border-gray-200">

            <div class="w-full">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Convidar Novo Membro</h3>
                <input type="text" id="searchInput" class="input input-bordered w-full text-sm focus:ring-2 focus:ring-blue-400 mb-2" placeholder="Buscar por nome ou apelido...">
                <div id="searchResults" class="w-full space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-2 bg-gray-50">
                    <p class="text-center text-gray-500 text-sm">Digite para buscar usuários...</p>
                </div>
                <button id="btnConvidarNovoMembro" class="mt-4 w-full btn bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 rounded-xl shadow transition hidden">
                    Convidar
                </button>
            </div> <!-- Fim da div .w-full -->
        </div>
    </div> <!-- Fim da div #modalConvites -->

    <!-- Modal de Editar Arena -->
    <div id="modalEditarArena" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 max-w-full flex flex-col items-center gap-4 relative">
            <button id="fecharModalEditarArena" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl font-bold">&times;</button>
            <div class="text-2xl mb-2">⚙️ <span class="font-extrabold text-blue-700">Editar Arena</span></div>
            <form id="formEditarArena" action="controller-arena/editar-arena.php" method="POST" class="w-full space-y-4">
                <input type="hidden" name="arena_id" value="<?= htmlspecialchars($arena['id']) ?>">

                <!-- Título da Arena -->
                <div>
                    <label for="edit_titulo" class="block mb-1 text-sm font-medium text-gray-700">Nome da Arena</label>
                    <input type="text" id="edit_titulo" name="titulo" class="input input-bordered w-full text-sm focus:ring-2 focus:ring-blue-400" value="<?= htmlspecialchars($arena['titulo']) ?>" required>
                </div>

                <!-- Visibilidade da Arena -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">Visibilidade da Arena</label>
                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200 shadow-sm">
                        <span class="text-gray-700 font-medium">Pública</span>
                        <input type="checkbox" id="edit_privacidade_toggle" name="privacidade" class="toggle toggle-lg toggle-primary" <?= $arena['privacidade'] === 'privada' ? 'checked' : '' ?> />
                        <span class="text-gray-700 font-medium">Privada</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        <span id="edit_public_desc" class="<?= $arena['privacidade'] === 'privada' ? 'hidden' : '' ?>">Qualquer um pode ver e se juntar.</span>
                        <span id="edit_private_desc" class="<?= $arena['privacidade'] === 'publica' ? 'hidden' : '' ?>">Apenas membros convidados podem ver e acessar.</span>
                    </p>
                </div>

                <button type="submit" class="mt-4 w-full btn bg-green-500 hover:bg-green-600 text-white font-bold py-2 rounded-xl shadow transition">
                    Salvar Alterações
                </button>
            </form>
        </div>
    </div>

    <script>
        // Funções para abrir/fechar modais
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.remove('hidden');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.add('hidden');
        }

        // Garante que todo o JS que manipula o DOM rode apenas quando a página estiver pronta.
        document.addEventListener('DOMContentLoaded', () => {

            <?php if ($is_founder): ?>
                // --- LÓGICA EXCLUSIVA DO FUNDADOR ---

                // Event Listeners para o Modal de Convites
                document.getElementById('btnConvites').addEventListener('click', () => openModal('modalConvites'));
                document.getElementById('fecharModalConvites').addEventListener('click', () => closeModal('modalConvites'));
                document.getElementById('modalConvites').addEventListener('click', (e) => {
                    if (e.target.id === 'modalConvites') closeModal('modalConvites');
                });

                // Event Listeners para o Modal de Editar Arena
                document.getElementById('btnEditarArena').addEventListener('click', () => openModal('modalEditarArena'));
                document.getElementById('fecharModalEditarArena').addEventListener('click', () => closeModal('modalEditarArena'));
                document.getElementById('modalEditarArena').addEventListener('click', (e) => {
                    if (e.target.id === 'modalEditarArena') closeModal('modalEditarArena');
                });

                // Lógica para o toggle de privacidade no modal de edição
                const editTipoArenaToggle = document.getElementById('edit_privacidade_toggle');
                const editPublicDesc = document.getElementById('edit_public_desc');
                const editPrivateDesc = document.getElementById('edit_private_desc');

                editTipoArenaToggle.addEventListener('change', function() {
                    if (this.checked) {
                        editPublicDesc.classList.add('hidden');
                        editPrivateDesc.classList.remove('hidden');
                    } else {
                        editPublicDesc.classList.remove('hidden');
                        editPrivateDesc.classList.add('hidden');
                    }
                });

                // Lógica para busca e convite de novos membros
                const searchInput = document.getElementById('searchInput');
                const searchResultsDiv = document.getElementById('searchResults');
                const arenaIdForSearch = '<?= htmlspecialchars($arena_id) ?>';
                let searchTimeout;

                searchInput.addEventListener('keyup', () => {
                    clearTimeout(searchTimeout);
                    const searchTerm = searchInput.value.trim();

                    if (searchTerm.length < 3) {
                        searchResultsDiv.innerHTML = '<p class="text-center text-gray-500 text-sm">Digite para buscar usuários...</p>';
                        return;
                    }

                    searchTimeout = setTimeout(async () => {
                        try {
                            const response = await fetch(`controller-arena/buscar-usuarios.php?arena_id=${arenaIdForSearch}&search_term=${encodeURIComponent(searchTerm)}`);
                            const data = await response.json();

                            if (data.success && data.users.length > 0) {
                                searchResultsDiv.innerHTML = '';
                                data.users.forEach(user => {
                                    const userHtml = `
                                <div class="member-item flex items-center justify-between bg-white p-2 rounded-lg border border-gray-100 shadow-sm">
                                    <span class="font-semibold text-gray-700">${user.nome} ${user.apelido ? `(${user.apelido})` : ''}</span>
                                    <button class="btn btn-xs btn-primary btn-invite" data-usuario-id="${user.id}" data-arena-id="${arenaIdForSearch}" data-action="invite">Convidar</button>
                                </div>`;
                                    searchResultsDiv.insertAdjacentHTML('beforeend', userHtml);
                                });
                            } else {
                                searchResultsDiv.innerHTML = '<p class="text-center text-gray-500 text-sm">Nenhum usuário encontrado.</p>';
                            }
                        } catch (error) {
                            console.error('Erro na busca de usuários:', error);
                            searchResultsDiv.innerHTML = '<p class="text-center text-red-500 text-sm">Erro ao buscar usuários.</p>';
                        }
                    }, 500);
                });
            <?php endif; ?>

            // --- LÓGICA GERAL PARA AÇÕES DE MEMBROS ---
            const currentUserId = '<?= $_SESSION['DuplaUserId'] ?>';

            document.addEventListener('click', async (e) => {
                const target = e.target;
                if (!target.dataset.action) return;

                const action = target.dataset.action;
                const usuarioId = target.dataset.usuarioId;
                const arenaId = target.dataset.arenaId;
                const listItem = target.closest('.member-item');

                if (action === 'remove' || action === 'reject') {
                    const confirmationMessage = action === 'remove' ? 'Tem certeza que deseja remover este membro?' : 'Tem certeza que deseja rejeitar?';
                    if (!confirm(confirmationMessage)) return;
                }

                try {
                    const response = await fetch('controller-arena/gerenciar-membro.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            arena_id: arenaId,
                            usuario_id: usuarioId,
                            action: action
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert(data.message);
                        if (action === 'remove' && usuarioId === currentUserId) {
                            window.location.href = 'arenas.php';
                        } else if (listItem) {
                            listItem.remove();
                        }
                    } else {
                        alert('Erro: ' + data.message);
                    }
                } catch (error) {
                    console.error('Erro na requisição AJAX:', error);
                    alert('Ocorreu um erro ao processar a solicitação.');
                }
            });
        });
    </script>

</body>

</html>