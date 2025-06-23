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
        <main class="flex-1 p-2 sm:p-4 flex items-center justify-center">
            <?php
            $inscricao_id = filter_input(INPUT_GET, 'inscricao_id', FILTER_VALIDATE_INT);
            $payment_status = filter_input(INPUT_GET, 'payment_status', FILTER_SANITIZE_STRING);

            if (!$inscricao_id) {
                // Se não houver ID de inscrição, não podemos fazer muito. Redireciona para a página principal de torneios.
                $_SESSION['mensagem'] = ["danger", "Informações da inscrição não encontradas."];
                header("Location: encontrar-torneio.php");
                exit;
            }

            // Definir conteúdo com base no status do pagamento
            $icon = '❓';
            $title = 'Status Desconhecido';
            $message = 'Não foi possível determinar o status do seu pagamento. Por favor, verifique os detalhes da sua inscrição.';
            $card_class = 'bg-gray-50 border-gray-200';
            $title_class = 'text-gray-800';

            switch ($payment_status) {
                case 'success':
                    $icon = '🎉';
                    $title = 'Pagamento Aprovado!';
                    $message = 'Seu pagamento foi recebido com sucesso. O status da sua inscrição será atualizado em breve. Você já pode retornar para a página da sua inscrição.';
                    $card_class = 'bg-green-50 border-green-200';
                    $title_class = 'text-green-800';
                    break;
                case 'pending':
                    $icon = '⏳';
                    $title = 'Pagamento Pendente';
                    $message = 'Seu pagamento está sendo processado. Assim que for aprovado, o status da sua inscrição será atualizado. Você pode acompanhar pelo site do Mercado Pago ou aguardar a confirmação.';
                    $card_class = 'bg-yellow-50 border-yellow-200';
                    $title_class = 'text-yellow-800';
                    break;
                case 'failure':
                    $icon = '❌';
                    $title = 'Falha no Pagamento';
                    $message = 'Houve um problema ao processar seu pagamento. Por favor, tente novamente ou utilize outro método de pagamento na página da sua inscrição.';
                    $card_class = 'bg-red-50 border-red-200';
                    $title_class = 'text-red-800';
                    break;
            }
            ?>

            <section class="max-w-lg mx-auto w-full bg-white rounded-2xl shadow-xl p-6 md:p-8 <?= htmlspecialchars($card_class) ?> border">
                <div class="text-center">
                    <span class="text-6xl"><?= $icon ?></span>
                    <h1 class="text-3xl font-bold <?= htmlspecialchars($title_class) ?> mt-4 mb-3"><?= htmlspecialchars($title) ?></h1>
                    <p class="text-lg text-gray-600 mb-8">
                        <?= htmlspecialchars($message) ?>
                    </p>
                    <a href="torneio-inscrito.php?inscricao_id=<?= htmlspecialchars($inscricao_id) ?>" class="btn btn-primary w-full text-lg">
                        Voltar para Minha Inscrição
                    </a>
                </div>
            </section>
        </main>
    </div>

    <footer class="w-full bg-white border-t border-gray-200 py-4 text-center fixed bottom-0 left-0 z-50">
        DUPLA - Deu Game? Dá Ranking!
    </footer>

</body>
</html>