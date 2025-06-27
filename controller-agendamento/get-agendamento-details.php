<?php
session_start();
require_once '#_global.php';

header('Content-Type: application/json');

$agendamento_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$agendamento_id) {
    echo json_encode(['success' => false, 'message' => 'ID do agendamento inválido.']);
    exit;
}

try {
    $agendamento = Agendamento::getAgendamentoById($agendamento_id);

    if ($agendamento) {
        echo json_encode(['success' => true, 'data' => $agendamento]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Agendamento não encontrado.']);
    }
} catch (Exception $e) {
    error_log("Erro ao buscar detalhes do agendamento: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
}

exit;
?>