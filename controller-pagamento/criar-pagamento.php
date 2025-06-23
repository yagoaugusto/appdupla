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
    // O ID do usuário pagador DEVE ser obtido da sessão para garantir a segurança.
    $usuario_id = $_SESSION['DuplaUserId'];

    $inscricao_id = filter_input(INPUT_POST, 'inscricao_id', FILTER_VALIDATE_INT);
    $metodo_pagamento = filter_input(INPUT_POST, 'metodo_pagamento', FILTER_SANITIZE_STRING); // 'pix' ou 'cartao'

    // Validação básica
    if (!$inscricao_id || !$usuario_id || !in_array($metodo_pagamento, ['pix', 'cartao'])) {
        echo json_encode(['success' => false, 'message' => 'Dados de pagamento inválidos.']);
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
                "email" => $usuario_pagador['email'] ?? "yagoacp@gmail.com", // Use um email real se disponível
                "phone" => (function($telefone_raw) {
                    $clean_phone = preg_replace('/\D/', '', $telefone_raw); // Remove todos os caracteres não numéricos

                    $area_code = '';
                    $number = '';

                    // Tenta extrair DDD e número de forma mais robusta
                    // Prioriza o formato brasileiro com DDI 55, senão assume DDD+Número
                    if (strlen($clean_phone) >= 10) { // Mínimo para DDD + número (ex: 1198765432)
                        if (substr($clean_phone, 0, 2) === '55' && strlen($clean_phone) >= 12) { // Se tiver DDI do Brasil (compatível com PHP < 8.0)
                            $area_code = substr($clean_phone, 2, 2); // Extrai DDD (ex: 11 de 5511...)
                            $number = substr($clean_phone, 4); // Extrai número (ex: 987654321 de 5511987654321)
                        } else { // Assume que é apenas DDD + número
                            $area_code = substr($clean_phone, 0, 2); // Extrai DDD
                            $number = substr($clean_phone, 2); // Extrai número
                        }
                    }
                    // Fallback para garantir que os campos não fiquem vazios, embora o ideal seja um número válido
                    return ["area_code" => $area_code ?: "00", "number" => $number ?: "000000000"];
                })($usuario_pagador['telefone']),
                "country_id" => "BR" // Explicitamente define o país do pagador como Brasil
            ],
            // A 'notification_url' (webhook) precisa ser uma URL pública para que o Mercado Pago possa acessá-la.
            // Em um ambiente de desenvolvimento local (localhost), esta URL não é acessível externamente.
            // Comentar esta linha para testes locais permite a criação da preferência de pagamento.
            // Para testar webhooks localmente, use uma ferramenta como o ngrok e descomente esta linha.
            // "notification_url" => MP_NOTIFICATION_URL,
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
            // Log do erro para o servidor
            error_log("Erro ao criar preferência MP. HTTP Code: " . $http_code . ", Response: " . $mp_response);

            // Prepara uma mensagem de erro detalhada para o frontend
            $error_details = json_decode($mp_response, true);
            $error_message = 'Erro ao iniciar pagamento.';

            if (isset($error_details['message'])) {
                $error_message = 'Erro do Mercado Pago: ' . $error_details['message'];
                if (isset($error_details['cause']) && is_array($error_details['cause']) && !empty($error_details['cause'])) {
                    // Pega a descrição da primeira causa do erro
                    $first_cause = reset($error_details['cause']);
                    if (isset($first_cause['description'])) {
                        $error_message .= ' Detalhe: ' . $first_cause['description'];
                    }
                }
            }
            echo json_encode(['success' => false, 'message' => $error_message]);
        }

    } catch (Exception $e) {
        error_log("Erro no processo de pagamento: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Ocorreu um erro inesperado: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
}
?>