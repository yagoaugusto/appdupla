<?php
require_once '#_global.php';
require_once '../vendor/autoload.php';

// Configura o Access Token do Mercado Pago
MercadoPago\SDK::setAccessToken(MP_ACCESS_TOKEN);

// Pega a notificação enviada pelo Mercado Pago
$json_notification = file_get_contents('php://input');
$notification = json_decode($json_notification, true);

if (isset($notification['type']) && $notification['type'] === 'payment') {
    $payment_id = $notification['data']['id'];

    try {
        // Busca os detalhes completos do pagamento
        $payment = MercadoPago\Payment::find_by_id($payment_id);

        if ($payment && $payment->status == 'approved') {
            $reserva_pendente_id = $payment->external_reference;

            // Busca a reserva pendente em nosso banco
            $reserva = Agendamento::getReservaPendentePorId($reserva_pendente_id);

            // Verifica se a reserva existe e ainda está pendente para evitar processamento duplicado
            if ($reserva && $reserva['status'] === 'pendente') {
                
                $conn = Conexao::pegarConexao();
                $conn->beginTransaction();

                try {
                    $slots = json_decode($reserva['slots_json'], true);
                    $usuario_id = $reserva['usuario_id'];

                    foreach ($slots as $slot) {
                        $hora_fim = date('H:i:s', strtotime($slot['horario'] . ' +1 hour'));
                        
                        Agendamento::inserirAgendamento(
                            $slot['quadra_id'],
                            $usuario_id,
                            $slot['data'],
                            $slot['horario'],
                            $hora_fim,
                            'reservado', // Status do agendamento final
                            $slot['preco'],
                            $payment->id, // ID do pagamento do MP
                            'Pagamento via Mercado Pago'
                        );
                    }

                    // Atualiza o status da reserva pendente para 'aprovado'
                    $stmt = $conn->prepare("UPDATE reservas_pendentes SET status = 'aprovado' WHERE id = ?");
                    $stmt->execute([$reserva_pendente_id]);

                    $conn->commit();
                    
                    // Responde ao Mercado Pago que a notificação foi recebida com sucesso
                    http_response_code(200);

                } catch (Exception $e) {
                    $conn->rollBack();
                    error_log("Webhook Erro (transação): " . $e->getMessage());
                    http_response_code(500); // Erro interno do servidor
                }
            }
        }
    } catch (Exception $e) {
        error_log("Webhook Erro (API MP): " . $e->getMessage());
        http_response_code(500);
    }
} else {
    // Se não for uma notificação de pagamento, apenas confirma o recebimento.
    http_response_code(200);
}

exit(); // Termina o script