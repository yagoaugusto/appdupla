<?php
session_start();
require_once '#_global.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../criar-quadra.php');
    exit;
}

$arena_id = $_POST['arena_id'] ?? null;
$nome = $_POST['nome'] ?? null;
$valor_base = $_POST['valor_base'] ?? null;

// Checkboxes: se não marcados, não são enviados. O valor padrão é 0.
$beach_tennis = isset($_POST['beach_tennis']) ? 1 : 0;
$volei = isset($_POST['volei']) ? 1 : 0;
$futvolei = isset($_POST['futvolei']) ? 1 : 0;

if (empty($arena_id) || empty($nome) || $valor_base === null) {
    $_SESSION['mensagem'] = ['error', 'Todos os campos são obrigatórios.'];
    header('Location: ../criar-quadra.php');
    exit;
}

// Converte valor para o formato do banco de dados (ex: 1.500,50 -> 1500.50)
$valor_base = str_replace('.', '', $valor_base);
$valor_base = str_replace(',', '.', $valor_base);

$success = Quadras::criarQuadra($arena_id, $nome, (float)$valor_base, $beach_tennis, $volei, $futvolei);

if ($success) {
    $_SESSION['mensagem'] = ['success', 'Quadra criada com sucesso!'];
} else {
    $_SESSION['mensagem'] = ['error', 'Ocorreu um erro ao criar a quadra. Tente novamente.'];
}

header('Location: ../criar-quadra.php');
exit;
?>