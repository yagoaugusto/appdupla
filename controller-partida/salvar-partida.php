<?php
require_once 'conexao.php';

$token_partida = bin2hex(random_bytes(16));
// Recebe os dados via POST
$j1_id = $_POST['jogador1_id'];
$j2_id = $_POST['jogador2_id'];
$j3_id = $_POST['jogador3_id'];
$j4_id = $_POST['jogador4_id'];
$placara = $_POST['placar_a'];
$placarb = $_POST['placar_b'];

$vencedor = ($placara > $placarb) ? 'A' : 'B';
$validado_jogador1 = true;

// Salva a partida na tabela partidas
$partida = $conn->prepare("INSERT INTO partidas (jogador1_id, jogador2_id, 
jogador3_id, jogador4_id, vencedor, token_validacao, placar_a, placar_b, validado_jogador1) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$partida->execute([$j1_id, $j2_id, $j3_id, $j4_id, $vencedor, $token_partida, $placara, $placarb, $validado_jogador1]);

//ENVIAR MENSAGEM PARA OS JOGADORES DA PARTIDA 
$stmt = $conn->prepare("SELECT * FROM usuario WHERE id IN (?, ?, ?)");
$stmt->execute([$j2_id, $j3_id, $j4_id]);
$jogadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Enviar mensagem para cada jogador
foreach ($jogadores as $jogador) {
    // Acesse os dados: $jogador['nome'], $jogador['rating'], etc.

    $nome = $jogador['nome'].' '.$jogador['apelido']; // Nome do jogador
    $token = $token_partida; // Token único da partida
    $jogador_id = $jogador['id']; // ID do jogador
    $telefone = $jogador['telefone']; // Telefone do jogador

    $mensagem = "🎾 *Olá, {$nome}!* \n";
    $mensagem .= "Uma nova partida foi registrada no *DUPLA* com você como participante.\n\n";
    $mensagem .= "Por favor, valide o resultado clicando no link abaixo:\n";
    $mensagem .= "🔗 *[VALIDAR PARTIDA]*\n";
    $mensagem .= "https://beta.appdupla.com/pos-partida.php?p={$token}&j={$jogador_id}\n\n";
    $mensagem .= "💡 _A validação é importante para manter o ranking justo e atualizado._\n";
    $mensagem .= "Obrigado por fazer parte da comunidade *DUPLA*!";

    
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
}

header("Location: ../pos-partida.php?p=".$token_partida."&j=".$j1_id);
