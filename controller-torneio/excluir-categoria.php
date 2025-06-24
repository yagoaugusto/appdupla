<?php
session_start();
require_once '#_global.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Verifica se o usuário está logado
if (!isset($_SESSION['DuplaUserId'])) {
    $response['message'] = "Usuário não autenticado.";
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
    $torneio_id = filter_input(INPUT_POST, 'torneio_id', FILTER_VALIDATE_INT);

    if (!$categoria_id || !$torneio_id) {
        $response['message'] = "Dados inválidos para exclusão da categoria.";
        echo json_encode($response);
        exit;
    }

    // Verifica se o usuário logado é o fundador do torneio (segurança)
    $torneio = Torneio::getTorneioById($torneio_id);
    if (!$torneio || $torneio['responsavel_id'] != $_SESSION['DuplaUserId']) {
        $response['message'] = "Você não tem permissão para excluir categorias deste torneio.";
        echo json_encode($response);
        exit;
    }

    // VERIFICAÇÃO CRÍTICA: Se existem inscrições para esta categoria
    if (Categoria::hasRegistrations($categoria_id)) {
        $response['message'] = 'Não é possível excluir a categoria, pois já existem duplas inscritas nela.';
        echo json_encode($response);
        exit;
    }

    if (Categoria::deleteCategory($categoria_id, $torneio_id)) {
        $response['success'] = true;
        $response['message'] = "Categoria excluída com sucesso!";
    } else {
        $response['message'] = "Erro ao excluir categoria. Categoria não encontrada ou não pertence ao torneio.";
    }

    echo json_encode($response);
    exit;
} else {
    $response['message'] = "Método de requisição inválido.";
    echo json_encode($response);
    exit;
}