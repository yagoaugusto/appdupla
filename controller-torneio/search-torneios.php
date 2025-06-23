<?php
session_start();
require_once '#_global.php';

// Define o cabeçalho para indicar que a resposta será JSON
header('Content-Type: application/json');

// Coleta e sanitiza os parâmetros de busca
$searchTerm = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
$statusFilter = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);
$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 12; // Limite padrão de 12 torneios

try {
    // Chama o método de busca da classe Torneio
    $torneios = Torneio::searchTorneios($searchTerm, $statusFilter, $limit);
    echo json_encode(['success' => true, 'torneios' => $torneios]);
} catch (Exception $e) {
    // Em caso de erro, registra e retorna uma mensagem genérica
    error_log("Erro ao processar busca de torneios: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor ao buscar torneios.']);
}
?>