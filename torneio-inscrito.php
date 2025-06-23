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
            $inscricao_id = filter_input(INPUT_GET, 'inscricao_id', FILTER_VALIDATE_INT);
            $usuario_logado_id = $_SESSION['DuplaUserId'] ?? null;

            if (!$inscricao_id || !$usuario_logado_id) {
                $_SESSION['mensagem'] = ["danger", "Inscrição não encontrada ou usuário não logado."];
                header("Location: encontrar-torneio.php");
                exit;
            }

            $inscricao = InscricaoTorneio::getInscricaoById($inscricao_id);

            if (!$inscricao) {
                $_SESSION['mensagem'] = ["danger", "Detalhes da inscrição não encontrados."];
                header("Location: encontrar-torneio.php");
                exit;
            }

            // Obter detalhes dos jogadores da dupla
            $jogador1 = Usuario::getUsuarioInfoById($inscricao['jogador1_id']);
            $jogador2 = Usuario::getUsuarioInfoById($inscricao['jogador2_id']);

            // Obter status de pagamento do usuário logado
            $pagamento_status = InscricaoTorneio::getPagamentoStatus($inscricao_id, $usuario_logado_id);
            $status_pagamento_label = $pagamento_status['status_pagamento'] ?? 'desconhecido';
            $valor_pagamento = $pagamento_status['valor'] ?? 0;
            
            // Verifica se o pagamento já foi feito
            $pagamento_ja_feito = ($status_pagamento_label === 'pago');


            // Obter todas as duplas inscritas no mesmo torneio
            $duplas_do_torneio = InscricaoTorneio::getDuplasInscritasByTorneio($inscricao['torneio_id']);

            // Função auxiliar para formatar nome do jogador
            function formatar_nome_jogador($nome, $apelido) {
                return htmlspecialchars($nome) . (!empty($apelido) ? ' (' . htmlspecialchars($apelido) . ')' : '');
            }
            ?>

            <section class="max-w-xl mx-auto w-full bg-white rounded-2xl shadow-xl p-6 md:p-8">
                <div class="text-center mb-6">
                    <span class="text-5xl">🎉</span>
                    <h1 class="text-3xl font-bold text-gray-800 mt-2 mb-2">Inscrição Confirmada!</h1>
                    <p class="text-lg text-gray-600">Sua dupla foi inscrita no torneio.</p>
                    <p class="text-sm text-gray-500 mt-2">ID da Inscrição: <strong><?= htmlspecialchars($inscricao['id']) ?></strong></p>
                </div>

                <!-- Meus Detalhes da Dupla -->
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 mb-6">
                    <h2 class="text-xl font-bold text-blue-800 mb-3 flex items-center gap-2">
                        <span class="text-2xl">🤝</span> Minha Dupla
                    </h2>
                    <p class="text-lg font-semibold text-gray-800 mb-1">
                        <?= htmlspecialchars($inscricao['titulo_dupla']) ?>
                    </p>
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">Jogadores:</span>
                        <?= formatar_nome_jogador($jogador1['nome'], $jogador1['apelido']) ?> e
                        <?= formatar_nome_jogador($jogador2['nome'], $jogador2['apelido']) ?>
                    </p>
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">Categoria:</span>
                        <?= htmlspecialchars($inscricao['categoria_titulo']) ?> (<?= htmlspecialchars(ucfirst($inscricao['categoria_genero'])) ?>)
                    </p>
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">Torneio:</span>
                        <?= htmlspecialchars($inscricao['torneio_titulo']) ?>
                    </p>
                </div>

                <!-- Minha Situação de Pagamento -->
                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 mb-6">
                    <h2 class="text-xl font-bold text-yellow-800 mb-3 flex items-center gap-2">
                        <span class="text-2xl">💰</span> Meu Pagamento
                    </h2>
                    <p class="text-lg font-semibold text-gray-800">
                        Status: <span class="capitalize
                        <?php
                        if ($status_pagamento_label === 'pendente') echo 'text-orange-600';
                        elseif ($status_pagamento_label === 'pago') echo 'text-green-600';
                        else echo 'text-gray-600';
                        ?>
                        "><?= htmlspecialchars($status_pagamento_label) ?></span>
                    </p>
                    <p class="text-sm text-gray-700">
                        Valor: R$ <?= number_format($valor_pagamento, 2, ',', '.') ?>
                    </p>
                    <p class="text-xs text-gray-600 mt-2">
                        Em breve, você receberá instruções sobre como efetuar o pagamento.
                    </p>
                    <?php if (!$pagamento_ja_feito): ?>
                        <div class="mt-4 space-y-2">
                            <button id="btnPagarPix" class="btn btn-success w-full text-lg" data-inscricao-id="<?= $inscricao_id ?>" data-usuario-id="<?= $usuario_logado_id ?>" data-metodo="pix">
                                Pagar com Pix
                            </button>
                            <button id="btnPagarCartao" class="btn btn-info w-full text-lg" data-inscricao-id="<?= $inscricao_id ?>" data-usuario-id="<?= $usuario_logado_id ?>" data-metodo="cartao">
                                Pagar com Cartão
                            </button>
                        </div>
                    <?php else: ?>
                        <p class="text-green-600 font-semibold mt-4">Seu pagamento já foi confirmado!</p>
                    <?php endif; ?>
                </div>

                <!-- Duplas Inscritas (Agrupadas por Categoria) -->
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <span class="text-2xl">👥</span> Duplas Inscritas
                    </h2>
                    <?php if (empty($duplas_do_torneio)): ?>
                        <p class="text-gray-500 italic text-center">Nenhuma dupla inscrita ainda neste torneio.</p>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach ($duplas_do_torneio as $categoria_id => $categoria_data): ?>
                                <div class="collapse collapse-arrow bg-gray-50 rounded-lg border border-gray-200">
                                    <input type="checkbox" />
                                    <div class="collapse-title text-md font-semibold text-gray-700">
                                        <?= htmlspecialchars($categoria_data['titulo']) ?> (<?= htmlspecialchars(ucfirst($categoria_data['genero'])) ?>) - <?= count($categoria_data['duplas']) ?> Dupla(s)
                                    </div>
                                    <div class="collapse-content">
                                        <ul class="list-disc list-inside space-y-1 text-sm text-gray-700">
                                            <?php foreach ($categoria_data['duplas'] as $dupla): ?>
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

                <!-- Acompanhar Grupos do Torneio (Placeholder) -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6 text-center">
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Acompanhar Grupos do Torneio</h2>
                    <p class="text-gray-600">Funcionalidade em desenvolvimento. Em breve você poderá ver os grupos e chaves aqui!</p>
                </div>

                <!-- Jogos do Torneio (Placeholder) -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6 text-center">
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Jogos do Torneio</h2>
                    <p class="text-gray-600">Funcionalidade em desenvolvimento. Em breve você poderá ver o cronograma de jogos aqui!</p>
                </div>

                <a href="encontrar-torneio.php" class="btn btn-primary w-full text-lg mt-4">Voltar aos Torneios</a>
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
            $('.btn-success, .btn-info').on('click', function() {
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