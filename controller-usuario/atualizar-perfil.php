<?php
session_start();
require_once '#_global.php';

// 1. Verificação de Segurança e Requisição
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../perfil.php");
    exit;
}

$usuario_id = $_SESSION['DuplaUserId'] ?? null;
if (!$usuario_id) {
    $_SESSION['mensagem'] = ['error', 'Sessão inválida. Por favor, faça login novamente.'];
    header("Location: ../index.php");
    exit;
}

// 2. Coleta e Sanitização dos Dados do Formulário
$dados_perfil = [
    'nome'        => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING),
    'sobrenome'   => filter_input(INPUT_POST, 'sobrenome', FILTER_SANITIZE_STRING),
    'apelido'     => filter_input(INPUT_POST, 'apelido', FILTER_SANITIZE_STRING),
    'sexo'        => filter_input(INPUT_POST, 'sexo', FILTER_SANITIZE_STRING),
    'email'       => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
    'telefone'    => '55' . preg_replace('/\D/', '', filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING)),
    'cpf'         => preg_replace('/\D/', '', filter_input(INPUT_POST, 'cpf', FILTER_SANITIZE_STRING)),
    'cidade'      => filter_input(INPUT_POST, 'cidade', FILTER_SANITIZE_STRING),
    'empunhadura' => filter_input(INPUT_POST, 'empunhadura', FILTER_SANITIZE_STRING)
];

// 3. Validação dos Dados
if (empty($dados_perfil['nome']) || empty($dados_perfil['sobrenome']) || empty($dados_perfil['email'])) {
    $_SESSION['mensagem'] = ['error', 'Nome, sobrenome e e-mail são campos obrigatórios.'];
    header("Location: ../perfil.php");
    exit;
}

if ($dados_perfil['email'] === false) {
    $_SESSION['mensagem'] = ['error', 'O formato do e-mail é inválido.'];
    header("Location: ../perfil.php");
    exit;
}

// Validação do apelido
if (empty($dados_perfil['apelido'])) {
    $_SESSION['mensagem'] = ['error', 'O apelido é obrigatório.'];
    header("Location: ../perfil.php");
    exit;
}

// Validação do sexo
$sexos_permitidos = ['M','F'];
if (!in_array($dados_perfil['sexo'], $sexos_permitidos)) {
    $dados_perfil['sexo'] = 'M';
}

if (!empty($dados_perfil['cpf']) && strlen($dados_perfil['cpf']) !== 11) {
    $_SESSION['mensagem'] = ['error', 'O CPF, se preenchido, deve conter 11 dígitos.'];
    header("Location: ../perfil.php");
    exit;
}

if (!in_array($dados_perfil['empunhadura'], ['destro', 'canhoto'])) {
    $dados_perfil['empunhadura'] = 'destro'; // Valor padrão se for inválido
}

// 3.1. Verifica se e-mail ou telefone já pertencem a OUTRO usuário
try {
    $conn = Conexao::pegarConexao();
    $dup = $conn->prepare("
        SELECT id
          FROM usuario
         WHERE (email = :email OR telefone = :telefone)
           AND id <> :meu_id
         LIMIT 1
    ");
    $dup->execute([
        ':email'    => $dados_perfil['email'],
        ':telefone' => $dados_perfil['telefone'],
        ':meu_id'   => $usuario_id
    ]);

    if ($dup->rowCount() > 0) {
        $_SESSION['mensagem'] = ['error', 'E-mail ou telefone já está em uso por outra conta.'];
        header("Location: ../perfil.php");
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro ao checar duplicidade (usuario {$usuario_id}): " . $e->getMessage());
    $_SESSION['mensagem'] = ['error', 'Erro ao validar dados. Tente novamente.'];
    header("Location: ../perfil.php");
    exit;
}

try {
    // 4. Atualização no Banco de Dados
    $sucesso = Usuario::updateUsuarioInfo($usuario_id, $dados_perfil);

    if ($sucesso) {
        // 5. Atualização da Sessão para refletir as mudanças imediatamente
        $_SESSION['DuplaUserNome'] = $dados_perfil['nome'];
        $_SESSION['DuplaUserTelefone'] = $dados_perfil['telefone'];
        $_SESSION['DuplaUserCidade'] = $dados_perfil['cidade'];
        $_SESSION['DuplaUserEmpunhadura'] = $dados_perfil['empunhadura'];
        $_SESSION['DuplaUserApelido']  = $dados_perfil['apelido'];
        $_SESSION['DuplaUserSexo']     = $dados_perfil['sexo'];

        $_SESSION['mensagem'] = ['success', 'Perfil atualizado com sucesso!'];
    } else {
        $_SESSION['mensagem'] = ['error', 'Não foi possível atualizar o perfil. Verifique se o e-mail já não está em uso por outra conta.'];
    }
} catch (Exception $e) {
    error_log("Erro ao atualizar perfil: " . $e->getMessage());
    $_SESSION['mensagem'] = ['error', 'Ocorreu um erro inesperado. Por favor, contate o suporte.'];
}

// 6. Redirecionamento de volta para a página de perfil
header("Location: ../perfil.php");
exit;