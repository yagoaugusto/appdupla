<?php
session_start();
require_once '../system-classes/config.php';
require_once '../system-classes/Conexao.php';
require_once '../system-classes/Usuario.php';
require_once '../vendor/autoload.php';          // PHPMailer via Composer
require_once 'mail-config.php';             // define SMTP_HOST, SMTP_* constants
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../recuperar-senha.php");
    exit;
}

$email_ou_telefone = filter_input(INPUT_POST, 'email_ou_telefone', FILTER_SANITIZE_STRING);

if (empty($email_ou_telefone)) {
    $_SESSION['DuplaLogin'] = "Por favor, informe seu e-mail ou telefone.";
    header("Location: ../recuperar-senha.php");
    exit;
}

try {
    $conn = Conexao::pegarConexao();

    // Tenta encontrar o usuário por e-mail ou telefone
    $stmt = $conn->prepare("SELECT id, nome, email, telefone FROM usuario WHERE email = :input OR telefone = :input_tel");
    $stmt->execute([
        ':input' => $email_ou_telefone,
        ':input_tel' => '55' . preg_replace('/\D/', '', $email_ou_telefone) // Formata telefone para busca
    ]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        // Para segurança, não informamos se o usuário não existe.
        // Apenas dizemos que as instruções foram enviadas (ou não).
        $_SESSION['DuplaLogin'] = "Se o seu e-mail ou telefone estiverem cadastrados, você receberá as instruções de recuperação em breve.";
        header("Location: ../index.php");
        exit;
    }

    $usuario_id = $usuario['id'];
    $nome_usuario = $usuario['nome'];
    $email_usuario = $usuario['email'];

    // VERIFICAÇÃO: Garante que o usuário encontrado tenha um e-mail válido.
    if (empty($email_usuario) || !filter_var($email_usuario, FILTER_VALIDATE_EMAIL)) {
        // O usuário foi encontrado (provavelmente pelo telefone), mas não tem um e-mail válido cadastrado.
        error_log("Tentativa de recuperação de senha para usuário ID $usuario_id sem e-mail válido.");
        $_SESSION['DuplaLogin'] = "Não foi possível enviar as instruções. A conta associada não possui um e-mail de recuperação válido. Por favor, entre em contato com o suporte.";
        header("Location: ../recuperar-senha.php");
        exit;
    }

    // Gera um token único e com validade (ex: 1 hora)
    $token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimais
    $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Salva o token no banco de dados
    Usuario::setRecoveryToken($usuario_id, $token, $expiracao);

    // Monta o link de recuperação
    $recovery_link = APP_BASE_URL . "/resetar-senha.php?token=" . $token;

    $assunto = "Recuperação de Senha - DUPLA";
    $mensagem_email = "Olá, " . htmlspecialchars($nome_usuario) . "!\n\n" .
                      "Recebemos uma solicitação de recuperação de senha para sua conta DUPLA.\n" .
                      "Para redefinir sua senha, clique no link abaixo:\n\n" .
                      $recovery_link . "\n\n" .
                      "Este link é válido por 1 hora. Se você não solicitou esta recuperação, por favor ignore.\n\n" .
                      "Atenciosamente,\nEquipe DUPLA";

    try {
        $mail = new PHPMailer(true);

        // Habilitar o debug SMTP se a constante DEBUG estiver ativa
        if (defined('DEBUG') && DEBUG) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Mostra a comunicação com o servidor
            echo '<pre>'; // Formata a saída para melhor leitura
        }

        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        $mail->CharSet    = 'UTF-8';
        $mail->setFrom('contato@appdupla.com', 'DUPLA');
        $mail->addAddress($email_usuario, $nome_usuario);
        $mail->Subject = $assunto;
        $mail->Body    = $mensagem_email;

        $mail->send();

        if (defined('DEBUG') && DEBUG) {
            echo 'E-mail enviado com sucesso (em modo debug).';
            echo '</pre>';
            // Em modo debug, paramos aqui para ver a saída.
            // Comente a linha abaixo para produção.
            // exit;
        }

    } catch (Exception $e) {
        error_log('Erro PHPMailer: ' . $mail->ErrorInfo);
        if (defined('DEBUG') && DEBUG) {
            die('</pre>Falha ao enviar e-mail. Erro do PHPMailer: ' . $mail->ErrorInfo);
        }
    }

    $_SESSION['DuplaLogin'] = "As instruções de recuperação de senha foram enviadas para o seu e-mail. Verifique sua caixa de entrada e spam.";
    header("Location: ../index.php");
    exit;

} catch (PDOException $e) {
    error_log("Erro ao solicitar recuperação de senha: " . $e->getMessage());
    $_SESSION['DuplaLogin'] = "Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente mais tarde.";
    header("Location: ../recuperar-senha.php");
    exit;
}
?>