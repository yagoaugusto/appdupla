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

            // Definir variáveis com base no status do pagamento
            $icon_char = '❓';
            $title = 'Status Desconhecido';
            $message = 'Não foi possível determinar o status do seu pagamento. Por favor, verifique os detalhes da sua inscrição.';
            $card_bg_class = 'bg-white';
            $card_border_class = 'border-gray-300';
            $title_text_class = 'text-gray-800';
            $icon_bg_class = 'bg-gray-200';
            $icon_text_class = 'text-gray-600';
            $button_class = 'btn-primary';
            $button_text = 'Voltar para Minha Inscrição';

            switch ($payment_status) {
                case 'success':
                    $icon_char = '🎉';
                    $title = 'Pagamento Aprovado!';
                    $message = 'Seu pagamento foi recebido com sucesso. O status da sua inscrição será atualizado em breve. Você já pode retornar para a página da sua inscrição.';
                    $card_bg_class = 'bg-green-50';
                    $card_border_class = 'border-green-400';
                    $title_text_class = 'text-green-800';
                    $icon_bg_class = 'bg-green-200';
                    $icon_text_class = 'text-green-700';
                    $button_class = 'btn-success';
                    break;
                case 'pending':
                    $icon_char = '⏳';
                    $title = 'Pagamento Pendente';
                    $message = 'Seu pagamento está sendo processado. Assim que for aprovado, o status da sua inscrição será atualizado. Você pode acompanhar pelo site do Mercado Pago ou aguardar a confirmação.';
                    $card_bg_class = 'bg-yellow-50';
                    $card_border_class = 'border-yellow-400';
                    $title_text_class = 'text-yellow-800';
                    $icon_bg_class = 'bg-yellow-200';
                    $icon_text_class = 'text-yellow-700';
                    $button_class = 'btn-warning';
                    break;
                case 'failure':
                    $icon_char = '❌';
                    $title = 'Falha no Pagamento';
                    $message = 'Houve um problema ao processar seu pagamento. Por favor, tente novamente ou utilize outro método de pagamento na página da sua inscrição.';
                    $card_bg_class = 'bg-red-50';
                    $card_border_class = 'border-red-400';
                    $title_text_class = 'text-red-800';
                    $icon_bg_class = 'bg-red-200';
                    $icon_text_class = 'text-red-700';
                    $button_class = 'btn-error';
                    break;
            }
            ?>

            <section class="max-w-md mx-auto w-full rounded-2xl shadow-xl p-6 md:p-8 transition-all duration-300
                <?= htmlspecialchars($card_bg_class) ?> border <?= htmlspecialchars($card_border_class) ?>">
                <div class="text-center">
                    <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4
                        <?= htmlspecialchars($icon_bg_class) ?> <?= htmlspecialchars($icon_text_class) ?> text-4xl font-bold">
                        <?= $icon_char ?>
                    </div>
                    <h1 class="text-3xl font-bold <?= htmlspecialchars($title_text_class) ?> mt-4 mb-3"><?= htmlspecialchars($title) ?></h1>
                    <p class="text-lg text-gray-700 mb-8">
                        <?= htmlspecialchars($message) ?>
                    </p>
                    <a href="torneio-inscrito.php?inscricao_id=<?= htmlspecialchars($inscricao_id) ?>" class="btn <?= htmlspecialchars($button_class) ?> w-full text-lg">
                        <?= htmlspecialchars($button_text) ?>
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