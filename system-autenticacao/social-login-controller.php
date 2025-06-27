<?php
session_start();
require_once '#_global.php';

// Para o Google, é recomendado usar a biblioteca oficial. Instale com: composer require google/apiclient:^2.0
// require_once 'vendor/autoload.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['token']) || !isset($input['provider'])) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$token = $input['token'];
$provider = $input['provider'];

try {
    $payload = null;
    $user_info = [];

    if ($provider === 'google') {
        // --- VERIFICAÇÃO DO TOKEN DO GOOGLE ---
        // Em um ambiente de produção, use a biblioteca oficial do Google para mais segurança.
        // Por simplicidade, aqui decodificamos o token (NÃO FAÇA ISSO EM PRODUÇÃO SEM VERIFICAR A ASSINATURA)
        // A forma correta é usar:
        // $client = new Google_Client(['client_id' => 'SEU_CLIENT_ID.apps.googleusercontent.com']);
        // $payload = $client->verifyIdToken($token);

        // Decodificação simplificada para exemplo (requer verificação de assinatura em produção)
        $parts = explode('.', $token);
        if (count($parts) !== 3) throw new Exception("Token do Google inválido.");
        $payload = json_decode(base64_decode(str_replace(['-','_'], ['+','/'], $parts[1])), true);

        if (!$payload || !isset($payload['sub']) || !isset($payload['email'])) {
            throw new Exception("Payload do Google inválido.");
        }

        $user_info = [
            'social_id' => $payload['sub'],
            'email' => $payload['email'],
            'nome' => $payload['given_name'] ?? '',
            'sobrenome' => $payload['family_name'] ?? '',
        ];
    } elseif ($provider === 'apple') {
        // A verificação da Apple é mais complexa e envolve chaves públicas.
        // Aqui seria o local para implementar a lógica de verificação do token da Apple.
        throw new Exception("Login com a Apple ainda não implementado no backend.");
    } else {
        throw new Exception("Provedor de login desconhecido.");
    }

    // --- LÓGICA DE USUÁRIO ---
    $conn = Conexao::pegarConexao();

    // 1. Tenta encontrar o usuário pelo ID social
    $stmt = $conn->prepare("SELECT * FROM usuario WHERE {$provider}_id = ?");
    $stmt->execute([$user_info['social_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Se não encontrou, tenta encontrar pelo e-mail (para vincular contas existentes)
    if (!$usuario) {
        $stmt = $conn->prepare("SELECT * FROM usuario WHERE email = ?");
        $stmt->execute([$user_info['email']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Se encontrou por e-mail, atualiza o ID social para vincular a conta
        if ($usuario) {
            $stmt_update = $conn->prepare("UPDATE usuario SET {$provider}_id = ? WHERE id = ?");
            $stmt_update->execute([$user_info['social_id'], $usuario['id']]);
        }
    }

    // 3. Se ainda não encontrou, cria um novo usuário
    if (!$usuario) {
        $stmt_insert = $conn->prepare(
            "INSERT INTO usuario (nome, sobrenome, email, {$provider}_id, data_cadastro, rating) VALUES (?, ?, ?, ?, NOW(), 1500)"
        );
        $stmt_insert->execute([$user_info['nome'], $user_info['sobrenome'], $user_info['email'], $user_info['social_id']]);
        $new_user_id = $conn->lastInsertId();

        // Busca o usuário recém-criado para obter todos os dados
        $stmt = $conn->prepare("SELECT * FROM usuario WHERE id = ?");
        $stmt->execute([$new_user_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // --- CRIA A SESSÃO DE LOGIN ---
    $_SESSION['DuplaUserId'] = $usuario['id'];
    $_SESSION['DuplaUserNome'] = $usuario['nome'];
    $_SESSION['DuplaUserApelido'] = $usuario['apelido'];
    $_SESSION['DuplaUserTelefone'] = $usuario['telefone'];
    $_SESSION['DuplaUserCidade'] = $usuario['cidade'];
    $_SESSION['DuplaUserTipo'] = $usuario['tipo'];

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log("Erro no login social: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>