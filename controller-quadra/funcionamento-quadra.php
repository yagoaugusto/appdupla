<?php
session_start();
require_once '#_global.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../criar-quadra.php'); // Redireciona para a página de criação de quadra se não for POST
    exit;
}

$quadra_id = $_POST['quadra_id'] ?? null;
$horarios_json = $_POST['horarios'] ?? '[]';

if (empty($quadra_id)) {
    $_SESSION['mensagem'] = ['error', 'ID da quadra não especificado.'];
    header('Location: ../funcionamento-quadra.php'); // Ou para uma página de erro
    exit;
}

$horarios_selecionados = json_decode($horarios_json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    $_SESSION['mensagem'] = ['error', 'Dados de horários inválidos.'];
    header('Location: ../funcionamento-quadra.php?quadra_id=' . htmlspecialchars($quadra_id));
    exit;
}

try {
    $dias_processados = [];
    foreach ($horarios_selecionados as $slot) {
        if (!in_array($slot['dia'], $dias_processados)) {
            $dias_processados[] = $slot['dia'];
        }
    }

    // Para cada dia que teve alguma seleção (ou deseleção), limpamos os horários existentes
    // e depois inserimos os novos. Isso garante que a deseleção funcione.
    foreach ($dias_processados as $dia) {
        Quadras::clearFuncionamentoQuadra($quadra_id, $dia);
    }

    // Insere os novos horários selecionados
    foreach ($horarios_selecionados as $slot) {
        Quadras::addFuncionamentoQuadra($quadra_id, $slot['dia'], $slot['inicio'], $slot['fim'], 60, $slot['valor_adicional']);
    }

    $_SESSION['mensagem'] = ['success', 'Horários de funcionamento atualizados com sucesso!'];
    header('Location: ../funcionamento-quadra.php?quadra_id=' . htmlspecialchars($quadra_id));
    exit;
} catch (Exception $e) {
    error_log("Erro ao salvar funcionamento da quadra: " . $e->getMessage());
    $_SESSION['mensagem'] = ['error', 'Ocorreu um erro ao salvar os horários.'];
    header('Location: ../funcionamento-quadra.php?quadra_id=' . htmlspecialchars($quadra_id));
    exit;
}
?>