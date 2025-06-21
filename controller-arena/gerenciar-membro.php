<?php
session_start();
require_once '#_global.php';

header('Content-Type: application/json'); // Garante que a resposta será JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $arena_id = $_POST['arena_id'] ?? null;
    $usuario_id = $_POST['usuario_id'] ?? null;
    $action = $_POST['action'] ?? null; // 'accept' ou 'reject'

    $current_user_id = $_SESSION['DuplaUserId'] ?? null;

    // Validação básica: Verificar se os dados obrigatórios estão presentes e se o usuário está logado
    if (empty($arena_id) || empty($usuario_id) || empty($action) || $current_user_id === null) {
        echo json_encode(['success' => false, 'message' => 'Dados incompletos ou usuário não logado.']);
        exit;
    }

    try {
        // Verificar se o usuário logado é o fundador da arena (segurança)
        $arena_info = Arena::getArenaById($arena_id);
        if (!$arena_info) {
            echo json_encode(['success' => false, 'message' => 'Arena não encontrada.']);
            exit;
        }

        $success = false;
        if ($action === 'accept' || $action === 'reject' || $action === 'invite') {
            // Para estas ações, o usuário DEVE ser o fundador
            if ($arena_info['fundador'] != $current_user_id) {
                echo json_encode(['success' => false, 'message' => 'Ação não autorizada. Apenas o fundador pode gerenciar convites.']);
                exit;
            }

            if ($action === 'accept') {
                $success = Arena::updateMemberStatus($arena_id, $usuario_id, 'membro');
            } elseif ($action === 'reject') {
                // Rejeitar um convite/solicitação é o mesmo que remover o registro pendente.
                $success = Arena::removeMember($arena_id, $usuario_id);
            } elseif ($action === 'invite') {
                $success = Arena::adicionarMembro($arena_id, $usuario_id, 'convidado');
            }
        } elseif ($action === 'remove') {
            // Lógica para remover um membro ou para um membro sair
            $is_founder_removing_member = ($arena_info['fundador'] == $current_user_id && $usuario_id != $current_user_id);
            $is_member_leaving = ($usuario_id == $current_user_id); // Qualquer membro pode sair

            if ($is_founder_removing_member || $is_member_leaving) {
                $success = Arena::removeMember($arena_id, $usuario_id);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ação não permitida.']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
            exit;
        }

        if ($success) {
            $message = 'Ação executada com sucesso.';
            if ($action === 'remove' && isset($is_member_leaving) && $is_member_leaving) {
                $message = 'Você saiu da arena.';
            } elseif ($action === 'invite') {
                $message = 'Convite enviado com sucesso!';
            }
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falha ao atualizar o status do membro.']);
        }
    } catch (PDOException $e) {
        error_log("Erro de banco de dados ao gerenciar membro da arena: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
}
?>