<?php
session_start();
require_once '#_global.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['DuplaUserId'])) {
    header("Location: ../../index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $torneio_id = filter_input(INPUT_POST, 'torneio_id', FILTER_VALIDATE_INT);

    // Verifica se o usuário logado é o fundador do torneio (segurança)
    $torneio = Torneio::getTorneioById($torneio_id);
    if (!$torneio || $torneio['responsavel_id'] != $_SESSION['DuplaUserId']) {
        $_SESSION['mensagem'] = ["danger", "Você não tem permissão para editar este torneio."];
        header("Location: ../gerenciar-torneio.php?id=" . $torneio_id);
        exit;
    }

    // Coleta e sanitiza os dados do formulário
    $dados_torneio = [
        'titulo' => filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING),
        'sobre' => filter_input(INPUT_POST, 'sobre', FILTER_SANITIZE_STRING),
        'inicio_inscricao' => filter_input(INPUT_POST, 'inicio_inscricao', FILTER_SANITIZE_STRING),
        'fim_inscricao' => filter_input(INPUT_POST, 'fim_inscricao', FILTER_SANITIZE_STRING),
        'inicio_torneio' => filter_input(INPUT_POST, 'inicio_torneio', FILTER_SANITIZE_STRING),
        'fim_torneio' => filter_input(INPUT_POST, 'fim_torneio', FILTER_SANITIZE_STRING),
    ];

    // Trata os valores monetários para o formato float do PHP
    $valor_primeira_insc_raw = filter_input(INPUT_POST, 'valor_primeira_insc', FILTER_SANITIZE_STRING);
    $dados_torneio['valor_primeira_insc'] = (float) str_replace(',', '.', str_replace('.', '', $valor_primeira_insc_raw));

    $valor_segunda_insc_raw = filter_input(INPUT_POST, 'valor_segunda_insc', FILTER_SANITIZE_STRING);
    $dados_torneio['valor_segunda_insc'] = (float) str_replace(',', '.', str_replace('.', '', $valor_segunda_insc_raw));

    // Validação básica
    if (empty($dados_torneio['titulo']) || empty($dados_torneio['inicio_inscricao'])) {
        $_SESSION['mensagem'] = ["danger", "Por favor, preencha todos os campos obrigatórios."];
        header("Location: ../gerenciar-torneio.php?id=" . $torneio_id);
        exit;
    }

    header("Location: ../gerenciar-torneio.php?id=" . $torneio_id);
    exit;

} else {
    // Se a requisição não for POST, redireciona para a página principal
    header("Location: ../principal.php");
    exit;
}