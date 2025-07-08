<?php
session_start();
require_once '#_global.php';

// Verifica permissão
if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) {
    $_SESSION['mensagem'] = ['error', 'Acesso não autorizado.'];
    header('Location: ../principal.php');
    exit;
}

$arena_id = filter_input(INPUT_POST, 'arena_id', FILTER_VALIDATE_INT);

try {
    // Validações básicas
    if (!$arena_id) throw new Exception("ID da arena é inválido.");
    if (empty($_POST['itens_venda'])) throw new Exception("Nenhum item na venda.");

    $itens_venda = json_decode($_POST['itens_venda'], true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("Erro ao processar os itens da venda.");

    // Monta o array de dados para registrar a venda
    $dados_venda = [
        'arena_id' => $arena_id,
        'valor_total' => filter_input(INPUT_POST, 'valor_total', FILTER_VALIDATE_FLOAT),
        'forma_pagamento' => filter_input(INPUT_POST, 'forma_pagamento', FILTER_SANITIZE_STRING),
        'usuario_id' => filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT),
        'itens' => $itens_venda
    ];

    if (Lojinha::registrarVenda($dados_venda)) {
        $_SESSION['mensagem'] = ['success', 'Venda registrada com sucesso!'];
    } else {
        throw new Exception("Falha ao salvar a venda no banco de dados.");
    }

} catch (Exception $e) {
    $_SESSION['mensagem'] = ['error', 'Erro: ' . $e->getMessage()];
}

// Redireciona de volta para a página de vendas da arena
header('Location: ../venda.php' . ($arena_id ? '?arena_id=' . $arena_id : ''));
exit;