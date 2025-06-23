<?php
session_start();
require_once '#_global.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['DuplaUserId'])) {
    header("Location: ../index.php"); // Redireciona para a página de login se não estiver logado
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['DuplaUserId'];
    // Coleta e sanitiza os dados do formulário
    $arena_id = filter_input(INPUT_POST, 'arena', FILTER_VALIDATE_INT);
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
    $sobre = filter_input(INPUT_POST, 'sobre', FILTER_SANITIZE_STRING);
    $inicio_inscricao = filter_input(INPUT_POST, 'inicio_inscricao', FILTER_SANITIZE_STRING);
    $fim_inscricao = filter_input(INPUT_POST, 'fim_inscricao', FILTER_SANITIZE_STRING);
    $inicio_torneio = filter_input(INPUT_POST, 'inicio_torneio', FILTER_SANITIZE_STRING);
    $fim_torneio = filter_input(INPUT_POST, 'fim_torneio', FILTER_SANITIZE_STRING);
    
    // Trata os valores monetários para o formato float do PHP
    $valor_primeira_insc_raw = filter_input(INPUT_POST, 'valor_primeira_insc', FILTER_SANITIZE_STRING);
    $valor_primeira_insc = (float) str_replace(',', '.', str_replace('.', '', $valor_primeira_insc_raw));

    $valor_segunda_insc_raw = filter_input(INPUT_POST, 'valor_segunda_insc', FILTER_SANITIZE_STRING);
    $valor_segunda_insc = (float) str_replace(',', '.', str_replace('.', '', $valor_segunda_insc_raw));


    // Validação básica dos dados (pode ser expandida conforme necessário)
    if (!$arena_id || !$titulo || !$inicio_inscricao || !$fim_inscricao || !$inicio_torneio || !$fim_torneio) {
        $_SESSION['mensagem'] = ["danger", "Por favor, preencha todos os campos obrigatórios."];
        header("Location: ../criar-torneio.php");
        exit;
    }

    try {
        // Prepara a query para inserção dos dados
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("INSERT INTO torneios (arena, responsavel_id, titulo, sobre, inicio_inscricao, fim_inscricao, inicio_torneio, fim_torneio, valor_primeira_insc, valor_segunda_insc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Executa a query com os parâmetros
        $stmt->execute([$arena_id, $usuario_id, $titulo, $sobre, $inicio_inscricao, $fim_inscricao, $inicio_torneio, $fim_torneio, $valor_primeira_insc, $valor_segunda_insc]);

        // Verifica se a inserção foi bem-sucedida
        if ($stmt->rowCount() > 0) {
            $torneio_id = $conn->lastInsertId();
            $_SESSION['mensagem'] = ["success", "Torneio criado com sucesso!"];
            // Redireciona para a página de gerenciamento do torneio recém-criado
            header("Location: ../gerenciar-torneio.php?id=" . $torneio_id);
            exit;
        } else {
            $_SESSION['mensagem'] = ["danger", "Erro ao criar o torneio. Tente novamente."];
        }
    } catch (PDOException $e) {
        // Em caso de erro, exibe a mensagem de erro (apenas para desenvolvimento, em produção use logs)
        if (DEBUG) {
            echo "Erro: " . $e->getMessage();
        }
        $_SESSION['mensagem'] = ["danger", "Erro ao criar o torneio: " . $e->getMessage()];
    }

    // Redireciona de volta para a página de criação em caso de falha
    header("Location: ../criar-torneio.php");
    exit;
} else {
    // Se a requisição não for POST, redireciona para a página de criação de torneio
    header("Location: ../criar-torneio.php");
    exit;
}
?>