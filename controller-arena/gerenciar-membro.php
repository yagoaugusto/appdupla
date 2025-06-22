<?php
session_start();
require_once '#_global.php';

header('Content-Type: application/json'); // Garante que a resposta será JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $arena_id = $_POST['arena_id'] ?? null;
    $usuario_id = $_POST['usuario_id'] ?? null;
    $action = $_POST['action'] ?? null; // 'accept', 'reject', 'invite', 'remove', 'request_join'

    $current_user_id = $_SESSION['DuplaUserId'] ?? null;

    // Validação básica: Verificar se os dados obrigatórios estão presentes e se o usuário está logado
    if (empty($arena_id) || empty($usuario_id) || empty($action) || $current_user_id === null) {
        echo json_encode(['success' => false, 'message' => 'Dados incompletos ou usuário não logado.']);
        exit;
    }

    try {
        // Buscar informações da arena
        $arena_info = Arena::getArenaById($arena_id);
        if (!$arena_info) {
            echo json_encode(['success' => false, 'message' => 'Arena não encontrada.']);
            exit;
        }

        $is_founder = ($arena_info['fundador'] == $current_user_id);
        $is_self_action = ($usuario_id == $current_user_id);
        $success = false;
        $message = '';

        switch ($action) {
            case 'accept':
                // O fundador pode aceitar solicitações de outros.
                // O próprio usuário pode aceitar um convite.
                if ($is_founder || $is_self_action) {
                    $success = Arena::updateMemberStatus($arena_id, $usuario_id, 'membro');
                    $message = $is_self_action ? 'Bem-vindo à arena!' : 'Membro aceito com sucesso.';
                } else {
                    echo json_encode(['success' => false, 'message' => 'Ação não autorizada.']);
                    exit;
                }
                break;

            case 'reject':
                // O fundador pode rejeitar solicitações de outros.
                // O próprio usuário pode rejeitar um convite.
                if ($is_founder || $is_self_action) {
                    $success = Arena::removeMember($arena_id, $usuario_id);
                    $message = $is_self_action ? 'Convite rejeitado.' : 'Solicitação rejeitada com sucesso.';
                } else {
                    echo json_encode(['success' => false, 'message' => 'Ação não autorizada.']);
                    exit;
                }
                break;

            case 'invite':
                // Apenas o fundador pode convidar.
                if ($is_founder) {
                    $success = Arena::adicionarMembro($arena_id, $usuario_id, 'convidado');
                    $message = 'Convite enviado com sucesso!';
                } else {
                    echo json_encode(['success' => false, 'message' => 'Ação não autorizada. Apenas o fundador pode convidar.']);
                    exit;
                }
                break;

            case 'request_join':
                // Qualquer usuário logado pode solicitar entrada em uma arena PÚBLICA.
                // A ação deve ser sobre o próprio ID do usuário.
                if ($is_self_action && $arena_info['privacidade'] === 'publica') {
                    // Verificação extra no backend para garantir que o usuário já não é membro ou tem uma solicitação.
                    $membros = Arena::getMembersByArenaId($arena_id, ['membro', 'fundador', 'convidado', 'solicitado']);
                    $is_already_member = false;
                    foreach ($membros as $membro) {
                        if ($membro['usuario_id'] == $current_user_id) {
                            $is_already_member = true;
                            break;
                        }
                    }
                    if ($is_already_member) {
                        echo json_encode(['success' => false, 'message' => 'Você já é membro ou possui uma solicitação pendente para esta arena.']);
                        exit;
                    }

                    $success = Arena::adicionarMembro($arena_id, $usuario_id, 'solicitado');
                    $message = 'Sua solicitação para entrar na arena foi enviada!';
                } else {
                    $message = $arena_info['privacidade'] !== 'publica' ? 'Você não pode solicitar entrada em uma arena privada.' : 'Ação não permitida.';
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit;
                }
                break;

            case 'remove':
                // O fundador pode remover um membro (que não seja ele mesmo).
                // O próprio usuário pode sair da arena (se não for o fundador).
                $is_founder_removing_member = $is_founder && !$is_self_action;
                $is_member_leaving = !$is_founder && $is_self_action;

                if ($is_founder_removing_member || $is_member_leaving) {
                    $success = Arena::removeMember($arena_id, $usuario_id);
                    $message = $is_member_leaving ? 'Você saiu da arena.' : 'Membro removido com sucesso.';
                } else {
                    $message = ($is_founder && $is_self_action)
                        ? 'O fundador não pode sair da arena. Para isso, a arena deve ser excluída.'
                        : 'Ação não permitida.';
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit;
                }
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
                exit;
        }

        if ($success) {
            echo json_encode(['success' => true, 'message' => $message]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falha ao executar a ação no banco de dados.']);
        }
    } catch (PDOException $e) {
        error_log("Erro de banco de dados ao gerenciar membro da arena: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
}
?>