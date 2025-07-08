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
    // Validações
    $produto_id = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);
    $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

    if (!$arena_id || !$produto_id || !$quantidade || $quantidade <= 0) {
        throw new Exception("Dados inválidos. Verifique o produto e a quantidade.");
    }
    
    // Converte valor monetário (custo) para o formato do banco
    $custo_unitario = $_POST['custo_unitario'] ?? '0';
    $custo_unitario = str_replace('.', '', $custo_unitario);
    $custo_unitario = str_replace(',', '.', $custo_unitario);

    $dados_entrada = [
        'produto_id' => $produto_id,
        'quantidade' => $quantidade,
        'custo_unitario' => (float)$custo_unitario,
        'motivo' => filter_input(INPUT_POST, 'motivo', FILTER_SANITIZE_STRING)
    ];

    if (Lojinha::registrarEntrada($dados_entrada)) {
        $_SESSION['mensagem'] = ['success', 'Entrada de estoque registrada com sucesso!'];
    } else {
        throw new Exception("Falha ao registrar a entrada no banco de dados.");
    }

} catch (Exception $e) {
    $_SESSION['mensagem'] = ['error', 'Erro: ' . $e->getMessage()];
}

// Redireciona de volta para a página de entradas da arena
header('Location: ../entradas.php' . ($arena_id ? '?arena_id=' . $arena_id : ''));
exit;