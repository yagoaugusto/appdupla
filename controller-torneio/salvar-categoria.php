<?php
session_start();
require_once '#_global.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['DuplaUserId'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $torneio_id = filter_input(INPUT_POST, 'torneio_id', FILTER_VALIDATE_INT);
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
    $genero = filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_STRING);

    // Validação básica
    if (!$torneio_id || empty($titulo) || !in_array($genero, ['masculino', 'feminino', 'mista'])) {
        $_SESSION['mensagem'] = ["danger", "Dados inválidos para a categoria."];
        header("Location: ../gerenciar-torneio.php?id=" . $torneio_id);
        exit;
    }

    // Verifica se o usuário logado é o fundador do torneio (segurança)
    $torneio = Torneio::getTorneioById($torneio_id);
    if (!$torneio || $torneio['responsavel_id'] != $_SESSION['DuplaUserId']) {
        $_SESSION['mensagem'] = ["danger", "Você não tem permissão para adicionar categorias a este torneio."];
        header("Location: ../gerenciar-torneio.php?id=" . $torneio_id);
        exit;
    }

    $categoria_id = Categoria::addCategory($torneio_id, $titulo, $genero);

    if ($categoria_id) {
        $_SESSION['mensagem'] = ["success", "Categoria adicionada com sucesso!"];
    } else {
        $_SESSION['mensagem'] = ["danger", "Erro ao adicionar categoria. Tente novamente."];
    }

    header("Location: ../gerenciar-torneio.php?id=" . $torneio_id);
    exit;
} else {
    header("Location: ../principal.php"); // Redireciona se não for POST
    exit;
}