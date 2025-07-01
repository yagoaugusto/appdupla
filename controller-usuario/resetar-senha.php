<?php
session_start();
require_once '../system-classes/config.php';
require_once '../system-classes/Conexao.php';
require_once '../system-classes/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

$token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
$nova_senha = $_POST['nova_senha'];
$confirmar_senha = $_POST['confirmar_senha'];

if (empty($token) || empty($nova_senha) || empty($confirmar_senha)) {
    $_SESSION['DuplaLogin'] = "Todos os campos são obrigatórios.";
    header("Location: ../resetar-senha.php?token=" . htmlspecialchars($token));
    exit;
}

if ($nova_senha !== $confirmar_senha) {
    $_SESSION['DuplaLogin'] = "As senhas não coincidem.";
    header("Location: ../resetar-senha.php?token=" . htmlspecialchars($token));
    exit;
}

// Validação de força da senha (opcional, mas recomendado)
if (strlen($nova_senha) < 6) {
    $_SESSION['DuplaLogin'] = "A senha deve ter no mínimo 6 caracteres.";
    header("Location: ../resetar-senha.php?token=" . htmlspecialchars($token));
    exit;
}

try {
    $hashed_token = hash('sha256', $token);
    $usuario_info = Usuario::getUsuarioByRecoveryToken($hashed_token);

    if (!$usuario_info) {
        $_SESSION['DuplaLogin'] = "Token de recuperação inválido ou expirado. Por favor, solicite uma nova recuperação de senha.";
        header("Location: ../index.php");
        exit;
    }

    $usuario_id = $usuario_info['id'];

    // Hash da nova senha
    $nova_senha_hash = password_hash($nova_senha, PASSWORD_BCRYPT);

    // Atualiza a senha e limpa o token
    Usuario::updateSenha($usuario_id, $nova_senha_hash);
    Usuario::clearRecoveryToken($usuario_id);

    $_SESSION['DuplaLogin'] = "Sua senha foi redefinida com sucesso! Você já pode fazer login com a nova senha.";
    header("Location: ../index.php");
    exit;

} catch (PDOException $e) {
    error_log("Erro ao redefinir senha: " . $e->getMessage());
    $_SESSION['DuplaLogin'] = "Ocorreu um erro ao redefinir sua senha. Por favor, tente novamente mais tarde.";
    header("Location: ../resetar-senha.php?token=" . htmlspecialchars($token));
    exit;
}
?>