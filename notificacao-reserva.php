<?php require_once '#_global.php'; ?>
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
        <main class="flex-1 p-4 sm:p-6 flex items-center justify-center">
            <?php
            $status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);
            $reserva_id = filter_input(INPUT_GET, 'reserva_id', FILTER_VALIDATE_INT);

            if (!$status || !$reserva_id) {
                // Fallback para parâmetros inválidos
                $status = 'failure';
            }

            // Valores padrão
            $icon_char = '❓';
            $title = 'Status Desconhecido';
            $message = 'Não foi possível determinar o status do seu pagamento. Por favor, verifique a seção "Minhas Reservas" para mais detalhes.';
            $card_bg_class = 'bg-white';
            $card_border_class = 'border-gray-300';
            $title_text_class = 'text-gray-800';
            $icon_bg_class = 'bg-gray-200';
            $icon_text_class = 'text-gray-600';
            $button_class = 'btn-primary';
            $button_text = 'Ver Minhas Reservas';

            switch ($status) {
                case 'success':
                    $icon_char = '🎉';
                    $title = 'Reserva Confirmada!';
                    $message = 'Seu pagamento foi aprovado e sua quadra está garantida. Nos vemos em quadra!';
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
                    $message = 'Seu pagamento está sendo processado. Assim que for aprovado, sua reserva será confirmada. Você pode acompanhar o status em "Minhas Reservas".';
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
                    $message = 'Houve um problema ao processar seu pagamento. Nenhuma cobrança foi feita. Por favor, tente novamente ou utilize outro método.';
                    $card_bg_class = 'bg-red-50';
                    $card_border_class = 'border-red-400';
                    $title_text_class = 'text-red-800';
                    $icon_bg_class = 'bg-red-200';
                    $icon_text_class = 'text-red-700';
                    $button_class = 'btn-error';
                    $button_text = 'Tentar Novamente';
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
                    <div class="flex flex-col gap-3">
                        <a href="minhas-reservas.php" class="btn <?= htmlspecialchars($button_class) ?> w-full text-lg">
                            <?= htmlspecialchars($button_text) ?>
                        </a>
                        <a href="principal.php" class="btn btn-ghost w-full">Voltar para o Início</a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <?php require_once '_footer.php'; ?>

</body>
</html>