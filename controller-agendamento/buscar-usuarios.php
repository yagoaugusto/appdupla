<?php
session_start();
require_once '#_global.php'; // Ajuste o caminho conforme a estrutura de pastas

header('Content-Type: application/json');

// Verifica se o termo de busca foi enviado
$searchTerm = filter_input(INPUT_GET, 'search_term', FILTER_SANITIZE_STRING);

if (empty($searchTerm)) {
    echo json_encode(['success' => false, 'message' => 'Termo de busca vazio.']);
    exit;
}

try {
    $usuarios = Usuario::buscarUsuariosPorTermo($searchTerm);
    echo json_encode(['success' => true, 'users' => $usuarios]);
} catch (Exception $e) {
    error_log("Erro no controlador de busca de usuários: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
}

exit;
?>