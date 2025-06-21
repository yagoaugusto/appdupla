<?php
session_start(); // Iniciar a sessão para acessar $_SESSION
require_once '#_global.php'; // Assumindo que este arquivo inclui system-classes/config.php e gerencia spl_autoload_register
// session_start() é gerenciado por _head.php, que é incluído por #_global.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recuperar e sanitizar dados POST
    // Usando o operador de coalescência nula para evitar avisos de índice indefinido
    $titulo = $_POST['nome_arena'] ?? '';
    $lema = $_POST['lema_arena'] ?? '';
    $bandeira = $_POST['emblema_arena'] ?? '';
    $privacidade_raw = $_POST['tipo_arena'] ?? '';

    // 2. Determinar o valor da privacidade com base no estado do checkbox
    // Se o checkbox estiver marcado, seu valor é 'on'; caso contrário, não está definido.
    $privacidade = ($privacidade_raw === 'on') ? 'privada' : 'publica';

    // 3. Obter o ID do fundador da sessão
    // Garantir que o usuário esteja logado
    $fundador_id = $_SESSION['DuplaUserId'] ?? null;

    // Validação básica: Verificar se os campos obrigatórios estão presentes
    if (empty($titulo) || $fundador_id === null) {
        // Redirecionar de volta para o formulário com uma mensagem de erro
        // Você pode querer armazenar uma mensagem de erro mais específica em $_SESSION
        header('Location: ../criar-arena.php?error=missing_data');
        exit;
    }

    try {
        // 1. Utiliza a classe Arena para criar a arena no banco de dados e obter o ID
        $arena_id = Arena::criarArena($titulo, $lema, $bandeira, $privacidade, $fundador_id);

        // 2. Adiciona o fundador como membro da arena com a situação 'fundador'
        if ($arena_id) { // Garante que a arena foi criada com sucesso
            Arena::adicionarMembro($arena_id, $fundador_id, 'fundador');
        }

        // 3. Redireciona para a página de detalhes da arena recém-criada
        header('Location: ../arena-page.php?id=' . $arena_id . '&success=arena_created');
        exit;

    } catch (PDOException $e) {
        // Lidar com erros de banco de dados de forma elegante
        // Em uma aplicação real, você registraria $e->getMessage() e mostraria um erro genérico ao usuário
        error_log("Erro de banco de dados ao criar arena: " . $e->getMessage()); // Registrar o erro para depuração
        header('Location: ../criar-arena.php?error=db_error'); // Redirecionar com um erro genérico
        exit;
    }
} else {
    // Se o método da requisição não for POST, redirecionar para o formulário de criação de arena
    header('Location: ../criar-arena.php');
    exit;
}

?>