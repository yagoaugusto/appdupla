<?php
session_start();
require_once 'conexao.php'; // Arquivo de conexão mysqli
require_once '../vendor/autoload.php'; // Autoload do Composer para a biblioteca do Google

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$token = $input['token'] ?? null;

if (!$token) {
    echo json_encode(['success' => false, 'message' => 'Token não fornecido.']);
    exit;
}

// --- Verificação do Token do Google ---
// 🚨 SUBSTITUA PELO SEU CLIENT ID DO GOOGLE, O MESMO USADO NO FRONTEND
$google_client_id = "718722463767-kadfm0scdru0blvhkfd61mdij55rgo6b.apps.googleusercontent.com";
$client = new Google_Client(['client_id' => $google_client_id]);
$payload = $client->verifyIdToken($token);

if ($payload) {
    $email = $payload['email'];
    $nome = $payload['given_name'] ?? 'Jogador';
    $sobrenome = $payload['family_name'] ?? 'Dupla';
    $google_id = $payload['sub'];

    // --- Lógica de Usuário: Encontrar ou Criar ---
    $email_esc = mysqli_real_escape_string($conn, $email);
    $query = "SELECT * FROM usuario WHERE email = '{$email_esc}' LIMIT 1";
    $result = mysqli_query($conn, $query);
    $usuario = mysqli_fetch_assoc($result);

    if (!$usuario) {
        // Usuário não existe, vamos criar um novo
        $nome_esc = mysqli_real_escape_string($conn, $nome);
        $sobrenome_esc = mysqli_real_escape_string($conn, $sobrenome);
        // Para um novo usuário, o apelido pode ser o primeiro nome
        $apelido_esc = mysqli_real_escape_string($conn, $nome);

        $insert_query = "INSERT INTO usuario (nome, sobrenome, email, apelido, google_id, data_cadastro) VALUES ('{$nome_esc}', '{$sobrenome_esc}', '{$email_esc}', '{$apelido_esc}', '{$google_id}', NOW())";

        if (mysqli_query($conn, $insert_query)) {
            $user_id = mysqli_insert_id($conn);
            $query = "SELECT * FROM usuario WHERE id = {$user_id} LIMIT 1";
            $result = mysqli_query($conn, $query);
            $usuario = mysqli_fetch_assoc($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao criar novo usuário.']);
            exit;
        }
    }

    // --- Login bem-sucedido ---
    if ($usuario) {
        // 1. Definir as variáveis de sessão
        $_SESSION['DuplaUserId'] = $usuario['id'];
        $_SESSION['DuplaUserNome'] = $usuario['nome'];
        $_SESSION['DuplaUserApelido'] = $usuario['apelido'];
        $_SESSION['DuplaUserTelefone'] = $usuario['telefone'];
        $_SESSION['DuplaUserCidade'] = $usuario['cidade'];
        $_SESSION['DuplaUserEmpunhadura'] = $usuario['empunhadura'];
        $_SESSION['DuplaUserTipo'] = $usuario['tipo'];

        // 2. Criar e salvar o token de "manter logado" (SEMPRE para login social)
        $login_token = bin2hex(random_bytes(32));
        $update_token_query = "UPDATE usuario SET token_login = '{$login_token}' WHERE id = {$usuario['id']}";
        mysqli_query($conn, $update_token_query);

        // 3. Definir o cookie no navegador do usuário
        // O cookie expira em 30 dias (86400 segundos * 30)
        setcookie('DuplaLoginToken', $login_token, time() + (86400 * 30), "/");

        // 4. Retornar sucesso para o frontend
        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Não foi possível encontrar ou criar o usuário.']);
        exit;
    }

} else {
    // Token inválido
    echo json_encode(['success' => false, 'message' => 'Token do Google inválido.']);
    exit;
}

?>