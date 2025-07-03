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
                $reserva_confirmada_com_sucesso = false; // Flag para controlar o envio da mensagem

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
                    $reserva_confirmada_com_sucesso = true; // Confirma que tudo deu certo
                    
                    // Responde ao Mercado Pago que a notificação foi recebida com sucesso
                    http_response_code(200);

                } catch (Exception $e) {
                    $conn->rollBack();
                    error_log("Webhook Erro (transação): " . $e->getMessage());
                    http_response_code(500); // Erro interno do servidor
                }

                // --- INÍCIO DA NOVA LÓGICA DE NOTIFICAÇÃO ---
                if ($reserva_confirmada_com_sucesso) {
                    // 1. Buscar dados do usuário (nome e telefone)
                    $usuario = Usuario::getUsuarioInfoById($reserva['usuario_id']);

                    if ($usuario && !empty($usuario['telefone'])) {
                        $nome_usuario = $usuario['nome'];
                        $telefone_usuario = $usuario['telefone'];
                        
                        // 2. Construir a mensagem de confirmação
                        $slots_info = json_decode($reserva['slots_json'], true);
                        $detalhes_reserva = "";
                        foreach($slots_info as $slot) {
                            $data_formatada = date('d/m/Y', strtotime($slot['data']));
                            $detalhes_reserva .= "✅ Quadra: *{$slot['quadra_nome']}*\n";
                            $detalhes_reserva .= "🗓️ Dia: {$data_formatada}\n";
                            $detalhes_reserva .= "⏰ Hora: {$slot['horario']}\n\n";
                        }

                        // Versão mais humanizada e completa
                        $mensagem = "E aí, *{$nome_usuario}*! Tudo pronto para o jogo?\n\n";
                        $mensagem .= "Sua reserva está confirmadíssima! Já estamos preparando a quadra e deixando tudo no ponto para receber vocês.\n\n";
                        $mensagem .= "Agora é só avisar a turma e aquecer!\n\n";
                        $mensagem .= "Ah, uma dica importante: não esqueça de levar sua garrafinha com água para manter a hidratação em dia. 💧\n\n";
                        $mensagem .= "Confira os detalhes do seu agendamento:\n";       
                        $mensagem .= $detalhes_reserva;
                        $mensagem .= "Nos vemos na quadra. Tenham um ótimo jogo!\n\n";
                        $mensagem .= "Deu game? Dá Ranking! 🏆\n— Equipe DUPLA";      

                        // 3. Enviar a mensagem via UltraMSG
                        $params = array(
                            'token' => 'vtts75qh13n0jdc7', // Seu token da UltraMSG
                            'to' => $telefone_usuario,
                            'body' => $mensagem
                        );
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => "https://api.ultramsg.com/instance124122/messages/chat",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_SSL_VERIFYHOST => 0,
                            CURLOPT_SSL_VERIFYPEER => 0,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS => http_build_query($params),
                            CURLOPT_HTTPHEADER => array(
                                "content-type: application/x-www-form-urlencoded"
                            ),
                        ));

                        $response = curl_exec($curl);
                        $err = curl_error($curl);
                        curl_close($curl);

                        // Log para depuração
                        if ($err) {
                            error_log("Erro ao enviar WhatsApp de confirmação (cURL): " . $err);
                        } else {
                            error_log("WhatsApp de confirmação enviado para {$telefone_usuario}. Resposta: " . $response);
                        }
                    }
                }
                // --- FIM DA NOVA LÓGICA DE NOTIFICAÇÃO ---
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
?>