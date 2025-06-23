<?php
session_start();
require_once '#_global.php';

// Define o cabeçalho para indicar que a resposta será JSON
header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['DuplaUserId'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inscricao_id = filter_input(INPUT_POST, 'inscricao_id', FILTER_VALIDATE_INT);
    $usuario_id = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
    $metodo_pagamento = filter_input(INPUT_POST, 'metodo_pagamento', FILTER_SANITIZE_STRING); // 'pix' ou 'cartao'

    // Validação básica
    if (!$inscricao_id || !$usuario_id || !in_array($metodo_pagamento, ['pix', 'cartao'])) {
        echo json_encode(['success' => false, 'message' => 'Dados de pagamento inválidos.']);
        exit;
    }

    // Verifica se o usuário logado é o mesmo que está tentando pagar
    if ($usuario_id !== $_SESSION['DuplaUserId']) {
        echo json_encode(['success' => false, 'message' => 'Erro de segurança: ID de usuário não corresponde.']);
        exit;
    }

    try {
        // 1. Obter detalhes do pagamento para o usuário e inscrição
        $pagamento_info = InscricaoTorneio::getPagamentoStatus($inscricao_id, $usuario_id);

        if (!$pagamento_info || $pagamento_info['status_pagamento'] === 'pago') {
            echo json_encode(['success' => false, 'message' => 'Pagamento já realizado ou informações não encontradas.']);
            exit;
        }

        $valor_a_pagar = $pagamento_info['valor'];

        // 2. Obter informações do usuário pagador
        $usuario_pagador = Usuario::getUsuarioInfoById($usuario_id);
        if (!$usuario_pagador) {
            echo json_encode(['success' => false, 'message' => 'Informações do usuário pagador não encontradas.']);
            exit;
        }

        // 3. Criar a preferência de pagamento no Mercado Pago
        $mp_url = "https://api.mercadopago.com/checkout/preferences";
        $mp_headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . MP_ACCESS_TOKEN
        ];

        $mp_data = [
            "items" => [
                [
                    "title" => "Inscrição Torneio - Jogador " . htmlspecialchars($usuario_pagador['nome']),
                    "quantity" => 1,
                    "unit_price" => (float) $valor_a_pagar,
                    "currency_id" => "BRL"
                ]
            ],
            "payer" => [
                "name" => $usuario_pagador['nome'],
                "surname" => $usuario_pagador['sobrenome'],
                "email" => $usuario_pagador['email'] ?? "email_nao_informado@example.com", // Use um email real se disponível
                "phone" => [ // Assumindo que o telefone está no formato DDI+DDD+Número (ex: 5511987654321)
                    "area_code" => substr($usuario_pagador['telefone'], 2, 2), // Pega o DDD (ex: 11 de 5511...)
                    "number" => substr($usuario_pagador['telefone'], 4) // Pega o número (ex: 987654321 de 5511987654321)
                ]
            ],
            "notification_url" => MP_NOTIFICATION_URL,
            "external_reference" => $inscricao_id . "-" . $usuario_id, // Referência para identificar o pagamento no seu sistema
            "back_urls" => [
                "success" => "http://localhost/APP%20DUPLA/torneio-inscrito.php?inscricao_id=" . $inscricao_id . "&payment_status=success",
                "pending" => "http://localhost/APP%20DUPLA/torneio-inscrito.php?inscricao_id=" . $inscricao_id . "&payment_status=pending",
                "failure" => "http://localhost/APP%20DUPLA/torneio-inscrito.php?inscricao_id=" . $inscricao_id . "&payment_status=failure"
            ],
            "auto_return" => "approved",
            "payment_methods" => [
                "excluded_payment_types" => [
                    ["id" => ($metodo_pagamento === 'pix' ? "credit_card" : "ticket")] // Exclui o outro método
                ],
                "installments" => 1 // Apenas 1 parcela para Pix/Cartão à vista
            ]
        ];

        $ch = curl_init($mp_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mp_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $mp_headers);
        $mp_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $mp_response_data = json_decode($mp_response, true);

        if ($http_code === 201 && isset($mp_response_data['init_point'])) {
            echo json_encode(['success' => true, 'checkout_url' => $mp_response_data['init_point']]);
        } else {
            error_log("Erro ao criar preferência MP: " . $mp_response);
            echo json_encode(['success' => false, 'message' => 'Erro ao iniciar pagamento. Tente novamente mais tarde.']);
        }

    } catch (Exception $e) {
        error_log("Erro no processo de pagamento: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ocorreu um erro inesperado ao processar o pagamento.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
}
?>