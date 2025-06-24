<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>

<body class="bg-gray-100 min-h-screen text-gray-800" style="color-scheme: light;">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php'; ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php'; ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-2 sm:p-4">
            <?php
            $usuario_id = $_SESSION['DuplaUserId'] ?? null;

            if (!$usuario_id) {
                // Redireciona se o usuário não estiver logado
                $_SESSION['mensagem'] = ["danger", "Você precisa estar logado para acessar esta página."];
                header("Location: index.php");
                exit;
            }

            // Busca os torneios em que o usuário está inscrito
            $torneios_inscritos = InscricaoTorneio::getTorneiosInscritosByUserId($usuario_id, 10); // Limite de 10

            // Busca os torneios organizados pelo usuário
            $torneios_organizados = Torneio::getTorneiosByFundadorId($usuario_id, 10); // Limite de 10
            ?>

            <section class="max-w-4xl mx-auto w-full">
                <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Meus Torneios</h1>

                <!-- Mensagens de feedback -->
                <?php
                if (isset($_SESSION['mensagem'])) {
                    $tipo = $_SESSION['mensagem'][0];
                    $texto = $_SESSION['mensagem'][1];
                    $alert_class = ($tipo === 'success') ? 'alert-success' : 'alert-error';
                    echo "<div class='alert {$alert_class} shadow-lg mb-4'><div><span>" . htmlspecialchars($texto) . "</span></div></div>";
                    unset($_SESSION['mensagem']); // Limpa a mensagem após exibir
                }
                ?>

                <!-- Torneios Inscritos -->
                <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="text-3xl">📝</span> Torneios que Estou Inscrito
                    </h2>
                    <?php if (empty($torneios_inscritos)): ?>
                        <p class="text-gray-600 italic text-center py-4">Você ainda não se inscreveu em nenhum torneio. Que tal <a href="encontrar-torneio.php" class="link link-primary">encontrar um agora</a>?</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($torneios_inscritos as $torneio): ?>
                                <div class="collapse collapse-arrow bg-green-50 rounded-lg border border-green-200 shadow-sm">
                                    <input type="checkbox" />
                                    <div class="collapse-title text-xl font-semibold text-green-800">
                                        <?= htmlspecialchars($torneio['titulo']) ?> <span class="text-sm text-gray-600">(ID: #<?= htmlspecialchars($torneio['torneio_id']) ?>)</span>
                                    </div>
                                    <div class="collapse-content">
                                        <p class="text-sm text-gray-700 mb-1">
                                            <span class="font-medium">Arena:</span> <?= htmlspecialchars($torneio['arena_bandeira'] . ' ' . $torneio['arena_titulo']) ?>
                                        </p>
                                        <p class="text-sm text-gray-700 mb-1">
                                            <span class="font-medium">Período:</span> <?= date('d/m/Y', strtotime($torneio['inicio_torneio'])) ?> a <?= date('d/m/Y', strtotime($torneio['fim_torneio'])) ?>
                                        </p>
                                        <p class="text-sm text-gray-700 mb-3">
                                            <span class="font-medium">Sobre:</span> <?= htmlspecialchars(substr($torneio['sobre'], 0, 100)) ?><?= strlen($torneio['sobre']) > 100 ? '...' : '' ?>
                                        </p>
                                        <a href="torneio-inscrito.php?inscricao_id=<?= htmlspecialchars($torneio['inscricao_id_para_link']) ?>" class="btn btn-sm btn-success">Ver Minha Inscrição</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Torneios Organizados -->
                <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="text-3xl">👑</span> Torneios que Organizo
                    </h2>
                    <?php if (empty($torneios_organizados)): ?>
                        <p class="text-gray-600 italic text-center py-4">Você ainda não organizou nenhum torneio. Que tal <a href="criar-torneio.php" class="link link-primary">criar um agora</a>?</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($torneios_organizados as $torneio): ?>                                <div class="collapse collapse-arrow bg-blue-50 rounded-lg border border-blue-200 shadow-sm">
                                    <input type="checkbox" />
                                    <div class="collapse-title text-xl font-semibold text-blue-800">
                                        <?= htmlspecialchars($torneio['titulo']) ?> <span class="text-sm text-gray-600">(ID: #<?= htmlspecialchars($torneio['id']) ?>)</span>
                                    </div>
                                    <div class="collapse-content">
                                    <p class="text-sm text-gray-700 mb-1">
                                        <span class="font-medium">Arena:</span> <?= htmlspecialchars($torneio['arena_bandeira'] ?? '') ?> <?= htmlspecialchars($torneio['arena_titulo'] ?? 'N/A') ?>
                                    </p>
                                    <p class="text-sm text-gray-700 mb-1">
                                        <span class="font-medium">Período:</span> <?= date('d/m/Y', strtotime($torneio['inicio_torneio'])) ?> a <?= date('d/m/Y', strtotime($torneio['fim_torneio'])) ?>
                                    </p>
                                    <p class="text-sm text-gray-700 mb-3">
                                        <span class="font-medium">Sobre:</span> <?= htmlspecialchars(substr($torneio['sobre'], 0, 100)) ?><?= strlen($torneio['sobre']) > 100 ? '...' : '' ?>
                                    </p>
                                    <a href="gerenciar-torneio.php?id=<?= htmlspecialchars($torneio['id']) ?>" class="btn btn-sm btn-primary">Gerenciar Torneio</a>
                                </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            <br><br><br>
        </main>
    </div>

    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>

</body>

</html>