<?php
require_once '#_global.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<?php require_once '_head.php'; ?>

<body class="bg-gray-100 min-h-screen text-gray-800" style="color-scheme: light;">

    <!-- Navbar superior -->
    <?php require_once '_nav_superior.php' ?>

    <div class="flex pt-16">
        <!-- Menu lateral -->
        <?php require_once '_nav_lateral.php' ?>

        <!-- Conteúdo principal -->
        <main class="flex-1 p-2 sm:p-4">
            <?php
            $usuario_logado_id = $_SESSION['DuplaUserId'] ?? null;

            if (!$usuario_logado_id) {
                $_SESSION['mensagem'] = ["danger", "Você precisa estar logado para acessar esta página."];
                header("Location: index.php");
                exit;
            }

            // Pega o ID da inscrição da URL
            $inscricao_id = filter_input(INPUT_GET, 'inscricao_id', FILTER_VALIDATE_INT);

            if (!$inscricao_id) {
                $_SESSION['mensagem'] = ["danger", "ID de inscrição inválido."];
                header("Location: meus-torneios.php"); // Redireciona para a lista de torneios do usuário
                exit;
            }

            // Busca os detalhes da inscrição principal (para obter o torneio_id)
            $inscricao_principal = InscricaoTorneio::getInscricaoById($inscricao_id);

            if (!$inscricao_principal) {
                $_SESSION['mensagem'] = ["danger", "Inscrição não encontrada."];
                header("Location: meus-torneios.php");
                exit;
            }

            $torneio_id = $inscricao_principal['torneio_id'];

            // Busca os detalhes do torneio
            $torneio = Torneio::getTorneioById($torneio_id);
            if (!$torneio) {
                $_SESSION['mensagem'] = ["danger", "Torneio não encontrado."];
                header("Location: encontrar-torneio.php");
                exit;
            }

            // Obter todas as inscrições do usuário logado neste torneio
            $todas_minhas_inscricoes = InscricaoTorneio::getInscricoesByTorneioAndUserId($torneio_id, $usuario_logado_id);

            // Obter todas as duplas inscritas no mesmo torneio (para a seção geral)
            $duplas_do_torneio = InscricaoTorneio::getDuplasInscritasByTorneio($torneio_id);

            // Função auxiliar para formatar nome do jogador
            function formatar_nome_jogador($nome, $apelido)
            {
                return htmlspecialchars($nome) . (!empty($apelido) ? ' (' . htmlspecialchars($apelido) . ')' : '');
            }
            // Função auxiliar para renderizar o badge de status de pagamento
            function render_payment_status_badge($status)
            {
                $color_class = $status === 'pago' ? 'text-green-600' : ($status === 'pendente' ? 'text-orange-600' : 'text-gray-600');
                return "<span class='capitalize font-semibold " . $color_class . "'>" . htmlspecialchars($status) . "</span>";
            }
            ?>

            <section class="max-w-4xl mx-auto w-full bg-white rounded-2xl shadow-xl p-6 md:p-8">
                <div class="text-center mb-6">
                    <span class="text-5xl">🎉</span>
                    <h1 class="text-3xl font-bold text-gray-800 mt-2 mb-2">Detalhes da Inscrição</h1>
                    <p class="text-sm text-gray-500 mt-2">Torneio: <strong><?= htmlspecialchars($torneio['titulo']) ?></strong></p>
                    <p class="text-sm text-gray-500">Arena: <strong><?= htmlspecialchars($torneio['arena_titulo']) ?></strong></p>
                </div>

                <!-- Minhas Inscrições neste Torneio -->
                <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="text-3xl">📝</span> Minhas Inscrições neste Torneio
                    </h2>
                    <?php if (empty($todas_minhas_inscricoes)) : ?>
                        <p class="text-gray-600 italic text-center py-4">Você não possui inscrições neste torneio.</p>
                    <?php else : ?>
                        <div class="space-y-4">
                            <?php foreach ($todas_minhas_inscricoes as $current_inscricao) : ?>
                                <?php
                                // Determine if this is the inscription highlighted by the URL
                                $is_highlighted_inscricao = ($current_inscricao['inscricao_id'] == $inscricao_id);

                                // Get player info for the current inscription in the loop
                                $current_jogador1 = Usuario::getUsuarioInfoById($current_inscricao['jogador1_id']);
                                $current_jogador2 = Usuario::getUsuarioInfoById($current_inscricao['jogador2_id']);

                                // Identify partner for the current inscription in the loop
                                $current_parceiro_id = ($usuario_logado_id == $current_jogador1['id']) ? $current_jogador2['id'] : $current_jogador1['id'];
                                $current_parceiro_info = ($usuario_logado_id == $current_jogador1['id']) ? $current_jogador2 : $current_jogador1;

                                // Get payment status for logged-in user for current inscription
                                $current_pagamento_status_usuario = InscricaoTorneio::getPagamentoStatus($current_inscricao['inscricao_id'], $usuario_logado_id);
                                $current_status_pagamento_usuario_label = $current_pagamento_status_usuario['status_pagamento'] ?? 'desconhecido';
                                $current_valor_pagamento_usuario = $current_pagamento_status_usuario['valor'] ?? 0;
                                $current_pagamento_usuario_feito = ($current_status_pagamento_usuario_label === 'pago');

                                // Get payment status for partner for current inscription
                                $current_pagamento_status_parceiro = InscricaoTorneio::getPagamentoStatus($current_inscricao['inscricao_id'], $current_parceiro_id);
                                $current_status_pagamento_parceiro_label = $current_pagamento_status_parceiro['status_pagamento'] ?? 'desconhecido';
                                ?>
                                <div class="collapse collapse-arrow bg-blue-50 rounded-lg border border-blue-200 shadow-sm">
                                    <input type="checkbox" <?= $is_highlighted_inscricao ? 'checked' : '' ?> />
                                    <div class="collapse-title text-xl font-semibold text-blue-800">
                                        <?= htmlspecialchars($current_inscricao['titulo_dupla']) ?>
                                        <span class="text-sm text-gray-600">(ID: #<?= htmlspecialchars($current_inscricao['inscricao_id']) ?>)</span>
                                    </div>
                                    <div class="collapse-content">
                                        <p class="text-sm text-gray-700 mb-1">
                                            <span class="font-medium">Jogadores:</span>
                                            <?= formatar_nome_jogador($current_jogador1['nome'], $current_jogador1['apelido']) ?> e
                                            <?= formatar_nome_jogador($current_jogador2['nome'], $current_jogador2['apelido']) ?>
                                        </p>
                                        <p class="text-sm text-gray-700 mb-3">
                                            <span class="font-medium">Categoria:</span>
                                            <?= htmlspecialchars($current_inscricao['categoria_titulo']) ?> (<?= htmlspecialchars(ucfirst($current_inscricao['categoria_genero'])) ?>)
                                        </p>

                                        <!-- Situação dos Pagamentos para esta inscrição -->
                                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 mt-4">
                                            <h3 class="text-lg font-bold text-yellow-800 mb-3 flex items-center gap-2">
                                                <span class="text-xl">💰</span> Situação dos Pagamentos
                                            </h3>
                                            <div class="space-y-3">
                                                <!-- Pagamento do Usuário Logado -->
                                                <div class="bg-white p-3 rounded-md shadow-sm">
                                                    <p class="font-semibold text-gray-800">
                                                        Seu Pagamento: <?= render_payment_status_badge($current_status_pagamento_usuario_label) ?>
                                                    </p>
                                                    <p class="text-sm text-gray-700">
                                                        Valor: R$ <?= number_format($current_valor_pagamento_usuario, 2, ',', '.') ?>
                                                    </p>
                                                    <?php if (!$current_pagamento_usuario_feito) : ?>
                                                        <div class="mt-3 space-y-2">
                                                            <button class="btn btn-success w-full btn-pagamento" data-inscricao-id="<?= $current_inscricao['inscricao_id'] ?>" data-usuario-id="<?= $usuario_logado_id ?>" data-metodo="pix">Pagar com Pix</button>
                                                            <button class="btn btn-info w-full btn-pagamento" data-inscricao-id="<?= $current_inscricao['inscricao_id'] ?>" data-usuario-id="<?= $usuario_logado_id ?>" data-metodo="cartao">Pagar com Cartão</button>
                                                        </div>
                                                    <?php else : ?>
                                                        <p class="text-green-600 font-semibold text-sm mt-2">Seu pagamento já foi confirmado!</p>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Pagamento do Parceiro -->
                                                <div class="bg-white p-3 rounded-md shadow-sm">
                                                    <p class="font-semibold text-gray-800">
                                                        Pagamento de <?= formatar_nome_jogador($current_parceiro_info['nome'], $current_parceiro_info['apelido']) ?>: <?= render_payment_status_badge($current_status_pagamento_parceiro_label) ?>
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        Seu parceiro será notificado para realizar o pagamento.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Todas as Duplas Inscritas no Torneio -->
                <div class="bg-white rounded-2xl shadow-xl p-6 md:p-8 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <span class="text-3xl">👥</span> Todas as Duplas Inscritas
                    </h2>
                    <?php if (empty($duplas_do_torneio)) : ?>
                        <p class="text-gray-600 italic text-center py-4">Nenhuma dupla inscrita ainda neste torneio.</p>
                    <?php else : ?>
                        <div class="space-y-3">
                            <?php foreach ($duplas_do_torneio as $categoria_id => $categoria_data) : ?>
                                <div class="collapse collapse-arrow bg-gray-50 rounded-lg border border-gray-200">
                                    <input type="checkbox" />
                                    <div class="collapse-title text-md font-semibold text-gray-700">
                                        <?= htmlspecialchars($categoria_data['titulo']) ?> (<?= htmlspecialchars(ucfirst($categoria_data['genero'])) ?>) - <?= count($categoria_data['duplas']) ?> Dupla(s)
                                    </div>
                                    <div class="collapse-content">
                                        <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                                            <?php foreach ($categoria_data['duplas'] as $dupla) : ?>
                                                <li>
                                                    <span class="font-medium"><?= htmlspecialchars($dupla['titulo_dupla']) ?>:</span>
                                                    <?= formatar_nome_jogador($dupla['j1_nome'], $dupla['j1_apelido']) ?> e
                                                    <?= formatar_nome_jogador($dupla['j2_nome'], $dupla['j2_apelido']) ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="inscrever-torneio.php?torneio_id=<?= htmlspecialchars($torneio_id) ?>" class="btn btn-secondary w-full text-lg">Inscrever em outra Categoria</a>
                    <a href="meus-torneios.php" class="btn btn-outline w-full text-lg">Voltar para Meus Torneios</a>
                </div>
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
            // Função para lidar com o clique nos botões de pagamento
            // Usamos delegação de evento porque os botões são gerados dinamicamente
            $(document).on('click', '.btn-pagamento', function() {
                const inscricaoId = $(this).data('inscricao-id');
                const usuarioId = $(this).data('usuario-id');
                const metodo = $(this).data('metodo');
                const btn = $(this);

                // Desabilita o botão e mostra um feedback
                btn.prop('disabled', true).text('Processando...');

                $.ajax({
                    url: 'controller-pagamento/criar-pagamento.php',
                    method: 'POST',
                    data: {
                        inscricao_id: inscricaoId,
                        usuario_id: usuarioId,
                        metodo_pagamento: metodo
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Redireciona o usuário para a URL de checkout do Mercado Pago
                            window.location.href = response.checkout_url;
                        } else {
                            alert('Erro ao iniciar pagamento: ' + response.message);
                            btn.prop('disabled', false).text(metodo === 'pix' ? 'Pagar com Pix' : 'Pagar com Cartão');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erro na requisição AJAX:', status, error, xhr.responseText);
                        alert('Erro de comunicação com o servidor. Tente novamente.');
                        btn.prop('disabled', false).text(metodo === 'pix' ? 'Pagar com Pix' : 'Pagar com Cartão');
                    }
                });
            });
        });
    </script>
</body>

</html>