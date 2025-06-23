<?php
require_once '#_global.php';

// Log para depuração (remova em produção)
file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - Recebido: " . file_get_contents('php://input') . "\n", FILE_APPEND);

// Recebe o corpo da requisição (JSON)
$input = file_get_contents('php://input');
$event = json_decode($input, true);

// Verifica se é uma notificação válida do Mercado Pago
if (!isset($event['type']) || !isset($event['data']['id'])) {
    http_response_code(400); // Bad Request
    exit;
}

// Para notificações de pagamento, o tipo é 'payment' e o ID é o ID do pagamento
if ($event['type'] === 'payment') {
    $payment_id = $event['data']['id'];

    // Consulta a API do Mercado Pago para obter detalhes completos do pagamento
    $mp_url = "https://api.mercadopago.com/v1/payments/" . $payment_id;
    $mp_headers = [
        "Authorization: Bearer " . MP_ACCESS_TOKEN
    ];

    $ch = curl_init($mp_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $mp_headers);
    $mp_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $payment_details = json_decode($mp_response, true);

    if ($http_code === 200 && isset($payment_details['status'])) {
        $status = $payment_details['status']; // approved, pending, rejected, cancelled, refunded, charged_back
        $external_reference = $payment_details['external_reference']; // Nossa referência: inscricao_id-usuario_id

        list($inscricao_id, $usuario_id) = explode('-', $external_reference);

        // Mapeia status do Mercado Pago para seu ENUM no DB
        $new_status = 'pendente';
        if ($status === 'approved') {
            $new_status = 'pago';
        } elseif ($status === 'rejected' || $status === 'cancelled') {
            $new_status = 'cancelado';
        }

        // Atualiza o status do pagamento no seu banco de dados
        InscricaoTorneio::updatePagamentoStatus($inscricao_id, $usuario_id, $new_status);

        file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - Pagamento ID: $payment_id, Status: $status, Nova Status DB: $new_status, Inscricao: $inscricao_id, Usuario: $usuario_id\n", FILE_APPEND);
    }
}

http_response_code(200); // Sempre retorne 200 OK para o Mercado Pago
?>