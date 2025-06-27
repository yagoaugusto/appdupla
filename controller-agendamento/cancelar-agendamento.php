<?php
session_start();
require_once '#_global.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$agendamento_id = $input['agendamento_id'] ?? null;

if (!filter_var($agendamento_id, FILTER_VALIDATE_INT)) {
    echo json_encode(['success' => false, 'message' => 'ID do agendamento inválido.']);
    exit;
}

// TODO: Adicionar verificação de permissão (ex: o gestor pode cancelar?)

try {
    $sucesso = Agendamento::cancelarAgendamento($agendamento_id);

    if ($sucesso) {
        echo json_encode(['success' => true, 'message' => 'Agendamento cancelado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Agendamento não encontrado ou já foi cancelado.']);
    }
} catch (Exception $e) {
    error_log("Erro ao cancelar agendamento: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
}

exit;
?>