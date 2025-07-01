<?php

session_start();
require_once '#_global.php';

$nome = $_POST['nome'];
$telefone = '55' . $_POST['telefone'];
$empunhadura = $_POST['empunhadura'];
// Gera senha de 4 dígitos (ex.: 0427) para o usuário
$senha_plain = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
// Hash seguro para armazenar no banco
$senha_hash  = password_hash($senha_plain, PASSWORD_BCRYPT);
$cidade = $_POST['cidade'];
$cpf = isset($_POST['cpf']) ? $_POST['cpf'] : null;
$sobrenome = $_POST['sobrenome'];
$apelido = $_POST['apelido'];
$sexo = $_POST['sexo'];
$email = $_POST['email'];

$novo_telefone = preg_replace('/\D/', '', $telefone);
$novo_cpf = $cpf ? preg_replace('/\D/', '', $cpf) : null;

/* --- Verificar existência de CPF ou telefone antes de cadastrar --- */
$conexao = Conexao::pegarConexao();
$sql = "SELECT id FROM usuario WHERE telefone = :tel OR email = :email";
$params = [
  ':tel' => $novo_telefone,
  ':email' => $email
];
if ($novo_cpf) {
  $sql .= " OR cpf = :cpf";
  $params[':cpf'] = $novo_cpf;
}
$sql .= " LIMIT 1";
$dupCheck = $conexao->prepare($sql);
$dupCheck->execute($params);
if ($dupCheck->rowCount() > 0) {
  $_SESSION['DuplaLogin'] = "Usuário já cadastrado. Utilize o login ou recupere a senha.";
  $_SESSION['DuplaLoginTipo'] = 'error';
  header("Location: ../index.php");
  exit;
}

$usuario = new Usuario();
$cadastrar = $usuario->cadastrar_usuario($nome, $novo_telefone, $senha_hash, $cidade, $empunhadura, $novo_cpf, $sobrenome, $apelido, $sexo, $email);
$_SESSION['DuplaLogin'] = "Bem vindo ao Dupla. Você receberá um WhatsApp com sua senha de acesso em breve!";
$_SESSION['DuplaLoginTipo'] = 'success';

$mensagem = "Olá, $nome! 👋\n\n" .
  "Seja bem-vindo ao DUPLA, o sistema de ranking de Beach Tennis mais divertido do Brasil! 🎾🔥\n" .
  "Seu cadastro foi realizado com sucesso. Aqui está sua senha de acesso:\n\n" .
  "🔐 Senha: $senha_plain\n\n" .
  "Acesse o sistema em https://beta.appdupla.com e comece a jogar, pontuar e desbloquear conquistas!\n\n" .
  "Deu game? Dá Ranking! 🏆\n— Equipe DUPLA";

$params = array(
  'token' => 'vtts75qh13n0jdc7',
  'to' => $telefone,
  'body' => $mensagem
);
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.ultramsg.com/instance124122/messages/chat",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_SSL_VERIFYHOST => 0,
  CURLOPT_SSL_VERIFYPEER => 0,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => http_build_query($params),
  CURLOPT_HTTPHEADER => array(
    "content-type: application/x-www-form-urlencoded"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}

header('Location:../index.php');
