<?php
session_start();
require_once '#_global.php';

if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) {
    $_SESSION['mensagem'] = ['error', 'Acesso não autorizado.'];
    header('Location: ../principal.php');
    exit;
}

$action = $_POST['action'] ?? null;
$arena_id = filter_input(INPUT_POST, 'arena_id', FILTER_VALIDATE_INT);
$produto_id = filter_input(INPUT_POST, 'produto_id', FILTER_VALIDATE_INT);

function tratarUploadImagem($file_input_name, $produto_id = null)
{
    if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] == UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$file_input_name];

    if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) {
        throw new Exception("Arquivo não foi recebido corretamente pelo servidor.");
    }

    if ($file['error'] !== UPLOAD_ERR_OK) throw new Exception("Erro no upload do arquivo.");
    if ($file['size'] > 2 * 1024 * 1024) throw new Exception("O arquivo é muito grande (máximo 2MB).");
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) throw new Exception("Formato de arquivo inválido. Apenas JPG, PNG, GIF e WebP são permitidos.");
    
    $target_dir = realpath(__DIR__ . '/../img/produtos');
    if ($target_dir === false || !is_dir($target_dir) || !is_writable($target_dir)) {
        throw new Exception("Diretório de destino não existe ou não possui permissão de escrita.");
    }
    $target_dir .= '/';

    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('prod_', true) . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    error_log("Upload de: " . $file['tmp_name']);
    error_log("Salvar em: " . $target_file);
    error_log("Permissões do diretório: " . substr(sprintf('%o', fileperms($target_dir)), -4));

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $new_filename;
    } else {
        throw new Exception("Falha ao mover o arquivo enviado.");
    }
}

try {
    $dados_produto = [
        'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING),
        'descricao' => filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING),
        'status' => isset($_POST['status']) ? 'ATIVO' : 'INATIVO'
    ];
    $preco_venda = str_replace('.', '', $_POST['preco_venda']);
    $preco_venda = str_replace(',', '.', $preco_venda);
    $dados_produto['preco_venda'] = (float)$preco_venda;

    switch ($action) {
        case 'criar':
            $dados_produto['arena_id'] = $arena_id;
            $dados_produto['estoque'] = filter_input(INPUT_POST, 'estoque', FILTER_VALIDATE_INT); // Apenas na criação
            $dados_produto['imagem'] = tratarUploadImagem('imagem') ?: 'default.png';

            if (Lojinha::criarProduto($dados_produto)) {
                $_SESSION['mensagem'] = ['success', 'Produto adicionado com sucesso!'];
            } else {
                throw new Exception("Não foi possível adicionar o produto.");
            }
            break;

        case 'editar':
            if (!$produto_id) throw new Exception("ID do produto inválido.");

            // O campo 'estoque' é intencionalmente ignorado aqui, pois não deve ser atualizado diretamente.
            $nova_imagem = tratarUploadImagem('imagem', $produto_id);
            if ($nova_imagem !== null) {
                $dados_produto['imagem'] = $nova_imagem;
            }

            if (Lojinha::editarProduto($produto_id, $dados_produto)) {
                $_SESSION['mensagem'] = ['success', 'Produto atualizado com sucesso!'];
            } else {
                throw new Exception("Não foi possível atualizar o produto.");
            }
            break;

        default:
            throw new Exception("Ação desconhecida.");
    }
} catch (Exception $e) {
    $_SESSION['mensagem'] = ['error', 'Erro: ' . $e->getMessage()];
}

header('Location: ../estoque.php' . ($arena_id ? '?arena_id=' . $arena_id : ''));
exit;