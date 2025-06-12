<?php

session_start ();
require_once '#_global.php';

$nome = $_POST['nome'];
$telefone = '55'.$_POST['telefone'];
$empunhadura = $_POST['empunhadura'];
$senha = rand(1000, 9999);
$cidade = $_POST['cidade'];
$cpf = $_POST['cpf'];
$sobrenome = $_POST['sobrenome'];
$apelido = $_POST['apelido'];
$sexo = $_POST['sexo'];

$novo_telefone = preg_replace('/\D/', '', $telefone);
$novo_cpf = preg_replace('/\D/', '', $cpf);


$usuario = new Usuario();
$cadastrar = $usuario->cadastrar_usuario($nome, $novo_telefone, $senha, $cidade, $empunhadura, $novo_cpf, $sobrenome, $apelido, $sexo);

$mensagem = "Olá, $nome! 👋\n\n" .
            "Seja bem-vindo ao DUPLA, o sistema de ranking de Beach Tennis mais divertido do Brasil! 🎾🔥\n" .
            "Seu cadastro foi realizado com sucesso. Aqui está sua senha de acesso:\n\n" .
            "🔐 Senha: $senha\n\n" .
            "Acesse o sistema e comece a jogar, pontuar e desbloquear conquistas!\n\n" .
            "Deu game? Dá Ranking! 🏆\n— Equipe DUPLA";

$params=array(
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

