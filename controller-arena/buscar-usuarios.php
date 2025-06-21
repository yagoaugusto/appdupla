<?php
session_start();
require_once '#_global.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $arena_id = $_GET['arena_id'] ?? null;
    $search_term = $_GET['search_term'] ?? '';

    $current_user_id = $_SESSION['DuplaUserId'] ?? null;

    if (empty($arena_id) || empty($search_term) || $current_user_id === null) {
        echo json_encode(['success' => false, 'message' => 'Dados incompletos ou usuário não logado.']);
        exit;
    }

    try {
        // Opcional: Verificar se o usuário logado é o fundador da arena para permitir a busca
        // $arena_info = Arena::getArenaById($arena_id);
        // if (!$arena_info || $arena_info['fundador'] != $current_user_id) {
        //     echo json_encode(['success' => false, 'message' => 'Não autorizado a buscar usuários para esta arena.']);
        //     exit;
        // }

        $users = Arena::searchNonMembers($arena_id, $search_term);
        echo json_encode(['success' => true, 'users' => $users]);
    } catch (PDOException $e) {
        error_log("Erro de banco de dados ao buscar usuários: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erro interno do servidor.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
}
?>