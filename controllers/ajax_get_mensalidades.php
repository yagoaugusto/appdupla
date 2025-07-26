<?php
// Define o cabeçalho para indicar que a resposta é em formato JSON
header('Content-Type: application/json');

require_once '#_global.php';

// Medida de segurança básica para garantir que apenas utilizadores logados possam aceder
session_start();
if (!isset($_SESSION['DuplaUserId'])) {
    echo json_encode(['error' => 'Acesso não autorizado']);
    exit;
}

$matricula_id = filter_input(INPUT_GET, 'matricula_id', FILTER_VALIDATE_INT);

if (!$matricula_id) {
    echo json_encode(['error' => 'ID da matrícula inválido']);
    exit;
}

try {
    // Chama o nosso novo método na classe Turma
    $mensalidades_abertas = Turma::getMensalidadesAbertasPorMatricula($matricula_id);
    // Envia os dados de volta como JSON
    echo json_encode($mensalidades_abertas);
} catch (Exception $e) {
    // Em caso de erro, envia uma mensagem de erro JSON
    echo json_encode(['error' => 'Falha ao buscar mensalidades: ' . $e->getMessage()]);
}