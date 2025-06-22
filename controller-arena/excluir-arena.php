<?php
session_start();
require_once '#_global.php'; // Ajusta o caminho para a raiz do projeto

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
    exit;
}

$arena_id = $_POST['arena_id'] ?? null;
$current_user_id = $_SESSION['DuplaUserId'] ?? null;

// Validação básica de entrada
if (empty($arena_id) || empty($current_user_id)) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos ou usuário não logado.']);
    exit;
}

try {
    // 1. Buscar informações da arena para verificação
    $arena_info = Arena::getArenaById($arena_id);
    if (!$arena_info) {
        echo json_encode(['success' => false, 'message' => 'Arena não encontrada.']);
        exit;
    }

    // 2. AUTORIZAÇÃO: Verificar se o usuário logado é o fundador da arena
    if ($arena_info['fundador'] != $current_user_id) {
        echo json_encode(['success' => false, 'message' => 'Ação não autorizada. Apenas o fundador pode excluir a arena.']);
        exit;
    }

    // 3. Executar a exclusão através do método da classe Arena
    $success = Arena::excluirArena($arena_id);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Arena excluída com sucesso!']);
    } else {
        // Este 'else' pode não ser alcançado se a função lançar exceção, mas é uma boa prática.
        echo json_encode(['success' => false, 'message' => 'Falha ao excluir a arena.']);
    }

} catch (PDOException $e) {
    error_log("Erro de banco de dados ao excluir arena: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor ao tentar excluir a arena.']);
}