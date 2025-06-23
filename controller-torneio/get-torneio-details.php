<?php
session_start(); // Inicia a sessão para acesso a variáveis de sessão, se necessário
require_once '#_global.php'; // Inclui o arquivo global para carregar classes e configurações

// Define o cabeçalho para indicar que a resposta será JSON
header('Content-Type: application/json');

// Verifica se o ID do torneio foi fornecido na requisição GET
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do torneio não fornecido.']);
    exit;
}

$torneio_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Valida se o ID do torneio é um inteiro válido
if (!$torneio_id) {
    echo json_encode(['success' => false, 'message' => 'ID do torneio inválido.']);
    exit;
}

try {
    // Busca os detalhes do torneio usando a classe Torneio
    $torneio = Torneio::getTorneioById($torneio_id);

    // Se o torneio não for encontrado, retorna um erro
    if (!$torneio) {
        echo json_encode(['success' => false, 'message' => 'Torneio não encontrado.']);
        exit;
    }

    // Busca as categorias associadas a este torneio usando a classe Categoria
    $categorias = Categoria::getCategoriesByTorneioId($torneio_id);

    // Retorna os dados do torneio e suas categorias em formato JSON
    echo json_encode([
        'success' => true,
        'data' => [
            'torneio' => $torneio,
            'categorias' => $categorias
        ]
    ]);

} catch (PDOException $e) {
    // Em caso de erro no banco de dados, registra o erro e retorna uma mensagem genérica
    error_log("Erro ao buscar detalhes do torneio: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor ao buscar detalhes do torneio.']);
}
?>