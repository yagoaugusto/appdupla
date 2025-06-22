<?php require_once '#_global.php';?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>
<?php
$usuario_id = $_SESSION['DuplaUserId'];
$pending_arenas = Arena::getUserPendingArenas($usuario_id);
$my_arenas = Arena::getUserArenas($usuario_id);
$public_arenas = Arena::getArenas();

?>
<body class="bg-gray-100 min-h-screen text-gray-800">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php'; ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php'; ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-4">
            <section class="max-w-6xl mx-auto w-full">
                
                <!-- Título da Página -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-extrabold tracking-tight text-gray-800 flex items-center gap-2">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Arenas
                    </h1>
                    <a href="criar-arena.php" class="btn btn-primary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Criar Arena
                    </a>
                </div>

                <!-- Seção de Convites e Solicitações Pendentes -->
                <?php if (!empty($pending_arenas)) : ?>
                    <div class="mb-8">
                        <h2 class="text-lg font-bold text-gray-700 mb-3">📢 Convites e Solicitações</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($pending_arenas as $arena) : ?>
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-lg shadow-md p-4 flex justify-between items-center">
                                    <div>
                                        <div class="font-bold text-yellow-800"><?= htmlspecialchars($arena['titulo']) ?></div>
                                        <div class="text-sm text-yellow-700">
                                            <?php if ($arena['situacao'] == 'convidado') : ?>
                                                Você foi convidado para entrar.
                                            <?php else : ?>
                                                Sua solicitação está pendente.
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($arena['situacao'] == 'convidado') : ?>
                                        <div class="flex gap-2">
                                            <button class="btn btn-xs btn-success" data-action="accept" data-usuario-id="<?= $usuario_id ?>" data-arena-id="<?= $arena['id'] ?>">Aceitar</button>
                                            <button class="btn btn-xs btn-error" data-action="reject" data-usuario-id="<?= $usuario_id ?>" data-arena-id="<?= $arena['id'] ?>">Rejeitar</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Seção Minhas Arenas -->
                <div class="mb-8">
                    <h2 class="text-lg font-bold text-gray-700 mb-3">🏟️ Minhas Arenas</h2>
                    <?php if (empty($my_arenas)) : ?>
                        <p class="text-gray-500 italic">Você ainda não faz parte de nenhuma arena. Crie uma ou junte-se a uma arena pública abaixo!</p>
                    <?php else : ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($my_arenas as $arena) : ?>
                                <a href="arena-page.php?id=<?= $arena['id'] ?>" class="block bg-white rounded-xl shadow p-4 hover:shadow-lg hover:scale-105 transition-transform duration-200">
                                    <div class="flex items-center gap-4">
                                        <span class="text-4xl"><?= htmlspecialchars($arena['bandeira']) ?></span>
                                        <div class="flex-1">
                                            <h3 class="font-bold text-gray-800 truncate"><?= htmlspecialchars($arena['titulo']) ?></h3>
                                            <div class="flex items-center text-xs text-gray-500 gap-3 mt-1">
                                                <span>👥 <?= (int)$arena['member_count'] ?> Membros</span>
                                                <span>⭐ <?= round($arena['avg_rating'] ?? 1500) ?> Média</span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Seção Descobrir Novas Arenas -->
                <div>
                    <h2 class="text-lg font-bold text-gray-700 mb-3">🌍 Descobrir Novas Arenas</h2>
                    <div class="mb-4">
                        <input type="text" id="searchArenas" placeholder="Buscar arenas públicas pelo nome..." class="input input-bordered w-full max-w-lg">
                    </div>

                    <div id="publicArenasContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($public_arenas as $arena) : ?>
                            <a href="arena-page.php?id=<?= $arena['id'] ?>" class="arena-card block bg-white rounded-xl shadow p-4 hover:shadow-lg hover:scale-105 transition-transform duration-200">
                                <div class="flex items-center gap-4">
                                    <span class="text-4xl"><?= htmlspecialchars($arena['bandeira']) ?></span>
                                    <div class="flex-1">
                                        <h3 class="arena-title font-bold text-gray-800 truncate"><?= htmlspecialchars($arena['titulo']) ?></h3>
                                        <div class="flex items-center text-xs text-gray-500 gap-3 mt-1">
                                            <span>👥 <?= (int)$arena['member_count'] ?> Membros</span>
                                            <span>⭐ <?= round($arena['avg_rating'] ?? 1500) ?> Média</span>
                                            <span class="capitalize <?= $arena['privacidade'] === 'privada' ? 'text-red-500' : 'text-green-500' ?>"><?= htmlspecialchars($arena['privacidade']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </svg>
                </div>

            </section>
            <br><br><br>
        </main>
    </div>

    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Lógica para busca de arenas
            const searchInput = document.getElementById('searchArenas');
            const arenaCards = document.querySelectorAll('#publicArenasContainer .arena-card');

            searchInput.addEventListener('keyup', () => {
                const searchTerm = searchInput.value.toLowerCase();
                arenaCards.forEach(card => {
                    const title = card.querySelector('.arena-title').textContent.toLowerCase();
                    if (title.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });

            // Lógica para aceitar/rejeitar convites
            document.body.addEventListener('click', async (e) => {
                const target = e.target.closest('button[data-action]');
                if (!target) return;

                const action = target.dataset.action;
                const usuarioId = target.dataset.usuarioId;
                const arenaId = target.dataset.arenaId;

                try {
                    const response = await fetch('controller-arena/gerenciar-membro.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ arena_id: arenaId, usuario_id: usuarioId, action: action })
                    });
                    const data = await response.json();
                    alert(data.message);
                    if (data.success) {
                        window.location.reload();
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