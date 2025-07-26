<?php
session_start();
require_once '#_global.php';

// Verifica a permissão do utilizador
if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) {
    $_SESSION['mensagem'] = ['error', 'Acesso não autorizado.'];
    header('Location: ../principal.php');
    exit;
}

$action = $_POST['action'] ?? null;
$arena_id = filter_input(INPUT_POST, 'arena_id', FILTER_VALIDATE_INT);

// Redirecionamento padrão para a futura página de gestão de repasses
// Passamos os filtros de volta para que a página recarregue com a mesma visão
$redirect_url = '../gestao_repasses.php?' . http_build_query([
    'arena_id' => $arena_id,
    'competencia' => $_POST['competencia'] ?? date('Y-m')
]);

try {
    switch ($action) {
        case 'pagar_repasses':
            // Validação dos dados recebidos do formulário
            if (empty($_POST['repasses_ids']) || !is_array($_POST['repasses_ids'])) {
                throw new Exception("Nenhum repasse foi selecionado para pagamento.");
            }
            
            $repasses_ids = $_POST['repasses_ids'];
            $data_pagamento = filter_input(INPUT_POST, 'data_pagamento', FILTER_SANITIZE_STRING);

            if (empty($data_pagamento)) {
                throw new Exception("A data do pagamento é obrigatória.");
            }
            
            // Chama o método na classe Turma para processar o pagamento
            if (Turma::pagarRepasses($repasses_ids, $data_pagamento)) {
                $_SESSION['mensagem'] = ['success', 'Repasses marcados como pagos com sucesso!'];
            } else {
                throw new Exception("Ocorreu uma falha ao registar o pagamento dos repasses.");
            }
            break;

        default:
            throw new Exception("Ação desconhecida.");
    }
} catch (Exception $e) {
    $_SESSION['mensagem'] = ['error', 'Erro: ' . $e->getMessage()];
}

header('Location: ' . $redirect_url);
exit;