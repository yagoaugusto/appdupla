<?php
session_start(); // Iniciar a sessão para acessar $_SESSION
require_once '#_global.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recuperar e sanitizar dados POST
    $arena_id = $_POST['arena_id'] ?? null;
    $titulo = $_POST['titulo'] ?? '';
    $privacidade_raw = $_POST['privacidade'] ?? '';

    // 2. Determinar o valor da privacidade com base no estado do checkbox
    $privacidade = ($privacidade_raw === 'on') ? 'privada' : 'publica';

    // 3. Obter o ID do usuário logado para verificação de fundador
    $current_user_id = $_SESSION['DuplaUserId'] ?? null;

    // Validação básica: Verificar se os campos obrigatórios estão presentes e se o ID da arena é válido
    if (empty($arena_id) || empty($titulo) || $current_user_id === null) {
        header('Location: ../arena-page.php?id=' . $arena_id . '&error=missing_data');
        exit;
    }

    try {
        // Verificar se o usuário logado é o fundador da arena
        $arena_info = Arena::getArenaById($arena_id);
        if (!$arena_info || $arena_info['fundador'] != $current_user_id) {
            header('Location: ../arena-page.php?id=' . $arena_id . '&error=unauthorized');
            exit;
        }

        // Utiliza a classe Arena para atualizar os dados da arena no banco de dados
        Arena::updateArena($arena_id, $titulo, $privacidade);

        // Redireciona para a página de detalhes da arena com mensagem de sucesso
        header('Location: ../arena-page.php?id=' . $arena_id . '&success=arena_updated');
        exit;

    } catch (PDOException $e) {
        error_log("Erro de banco de dados ao editar arena: " . $e->getMessage());
        header('Location: ../arena-page.php?id=' . $arena_id . '&error=db_error');
        exit;
    }
} else {
    // Se o método da requisição não for POST, redirecionar para a página principal
    header('Location: ../principal.php');
    exit;
}
?>