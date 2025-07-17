<?php
session_start();
require_once '#_global.php';

// Verifica a permissão do utilizador
if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) {
    $_SESSION['mensagem'] = ['error', 'Acesso não autorizado.'];
    header('Location: ../principal.php');
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$arena_id = filter_input(INPUT_POST, 'arena_id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_GET, 'arena_id', FILTER_VALIDATE_INT);

// Define o redirecionamento com base na ação
$redirect_url = '../despesa_categorias.php' . ($arena_id ? '?arena_id=' . $arena_id : '');
if (in_array($action, ['criar_despesa', 'editar_despesa', 'apagar_despesa'])) {
    $redirect_url = '../gestao_despesas.php' . ($arena_id ? '?arena_id=' . $arena_id : '');
}

try {
    switch ($action) {
        case 'criar_categoria':
            $dados_categoria = [
                'arena_id' => $arena_id,
                'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING),
                'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING),
            ];
            if (empty($dados_categoria['nome'])) {
                throw new Exception("O nome da categoria é obrigatório.");
            }
            if (Despesa::criarCategoria($dados_categoria)) {
                $_SESSION['mensagem'] = ['success', 'Categoria criada com sucesso!'];
            } else {
                throw new Exception("Falha ao criar a categoria.");
            }
            break;

        case 'editar_categoria':
            $categoria_id = filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT);
            if (!$categoria_id) {
                throw new Exception("ID da categoria inválido.");
            }
            $dados_categoria = [
                'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING),
                'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING),
                'status' => filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING),
            ];
            if (empty($dados_categoria['nome']) || empty($dados_categoria['status'])) {
                throw new Exception("O nome e o status da categoria são obrigatórios.");
            }
            if (Despesa::editarCategoria($categoria_id, $dados_categoria)) {
                $_SESSION['mensagem'] = ['success', 'Categoria atualizada com sucesso!'];
            } else {
                throw new Exception("Falha ao atualizar a categoria.");
            }
            break;

        // --- Novos Cases de Lançamentos de Despesas ---
        case 'criar_despesa':
            $dados_despesa = [
                'arena_id' => $arena_id,
                'categoria_id' => filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT),
                'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING),
                'valor' => (float) str_replace(',', '.', str_replace('.', '', $_POST['valor'])),
                'data_vencimento' => filter_input(INPUT_POST, 'data_vencimento', FILTER_SANITIZE_STRING),
                'data_pagamento' => filter_input(INPUT_POST, 'data_pagamento', FILTER_SANITIZE_STRING),
                'observacoes' => filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_STRING),
            ];
            
            if (empty($dados_despesa['descricao']) || empty($dados_despesa['valor']) || empty($dados_despesa['data_vencimento'])) {
                throw new Exception("Descrição, Valor e Data de Vencimento são obrigatórios.");
            }

            if (Despesa::criarDespesa($dados_despesa)) {
                $_SESSION['mensagem'] = ['success', 'Despesa registada com sucesso!'];
            } else { 
                throw new Exception("Falha ao registar a despesa."); 
            }
            break;

        case 'editar_despesa':
            $dados_despesa = [
                'arena_id' => $arena_id,
                'categoria_id' => filter_input(INPUT_POST, 'categoria_id', FILTER_VALIDATE_INT),
                'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING),
                'valor' => (float) str_replace(',', '.', str_replace('.', '', $_POST['valor'])),
                'data_vencimento' => filter_input(INPUT_POST, 'data_vencimento', FILTER_SANITIZE_STRING),
                'data_pagamento' => filter_input(INPUT_POST, 'data_pagamento', FILTER_SANITIZE_STRING),
                'observacoes' => filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_STRING),
            ];
            // Validações...
            if (empty($dados_despesa['descricao']) || empty($dados_despesa['valor']) || empty($dados_despesa['data_vencimento'])) {
                throw new Exception("Descrição, Valor e Data de Vencimento são obrigatórios.");
            }

            if ($action == 'criar_despesa') {
                if (Despesa::criarDespesa($dados_despesa)) {
                    $_SESSION['mensagem'] = ['success', 'Despesa registada com sucesso!'];
                } else {
                    throw new Exception("Falha ao registar a despesa.");
                }
            } else {
                $despesa_id = filter_input(INPUT_POST, 'despesa_id', FILTER_VALIDATE_INT);
                if (!$despesa_id) throw new Exception("ID da despesa inválido.");
                if (Despesa::editarDespesa($despesa_id, $dados_despesa)) {
                    $_SESSION['mensagem'] = ['success', 'Despesa atualizada com sucesso!'];
                } else {
                    throw new Exception("Falha ao atualizar a despesa.");
                }
            }
            break;

        case 'apagar_despesa':
            $despesa_id = filter_input(INPUT_GET, 'despesa_id', FILTER_VALIDATE_INT);
            if (!$despesa_id) throw new Exception("ID da despesa inválido.");
            if (Despesa::apagarDespesa($despesa_id)) {
                $_SESSION['mensagem'] = ['success', 'Despesa apagada com sucesso.'];
            } else {
                throw new Exception("Falha ao apagar a despesa.");
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
