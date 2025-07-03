<?php
session_start();
require_once '#_global.php';

header('Content-Type: application/json');

// Validações iniciais
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit;
}

$usuario_id = $_SESSION['DuplaUserId'] ?? null;
if (!$usuario_id) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

// Coleta e sanitiza os dados
$dados_avaliacao = [
    'reserva_id' => filter_input(INPUT_POST, 'reserva_id', FILTER_VALIDATE_INT),
    'usuario_id' => $usuario_id,
    'qualidade_quadra' => filter_input(INPUT_POST, 'qualidade_quadra', FILTER_VALIDATE_INT),
    'pontualidade_disponibilidade' => filter_input(INPUT_POST, 'pontualidade_disponibilidade', FILTER_VALIDATE_INT),
    'atendimento_suporte' => filter_input(INPUT_POST, 'atendimento_suporte', FILTER_VALIDATE_INT),
    'ambiente_arena' => filter_input(INPUT_POST, 'ambiente_arena', FILTER_VALIDATE_INT),
    'facilidade_reserva' => filter_input(INPUT_POST, 'facilidade_reserva', FILTER_VALIDATE_INT),
    'comentario' => filter_input(INPUT_POST, 'comentario', FILTER_SANITIZE_STRING)
];

if (!$dados_avaliacao['reserva_id']) {
    echo json_encode(['success' => false, 'message' => 'ID da reserva inválido.']);
    exit;
}

// Verifica se já não foi avaliado (segurança extra no backend)
if (Avaliacao::jaAvaliou($dados_avaliacao['reserva_id'], $usuario_id)) {
    echo json_encode(['success' => false, 'message' => 'Esta reserva já foi avaliada.']);
    exit;
}

// Tenta salvar no banco de dados
try {
    if (Avaliacao::salvar($dados_avaliacao)) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Falha ao salvar a avaliação no banco de dados.');
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ocorreu um erro interno. Tente novamente mais tarde.']);
}
?>