<?php
require_once 'conexao.php';
require_once 'Glicko2.php';

// Recebe os dados via POST

$j1_id = $_POST['jogador1_id'];
$j2_id = $_POST['jogador2_id'];
$j3_id = $_POST['jogador3_id'];
$j4_id = $_POST['jogador4_id'];
$placara = $_POST['placar_a'];
$placarb = $_POST['placar_b'];

// Validação simples
if ($placara == $placarb) {
    echo "Empate não é permitido.";
    exit();
}
$vencedor = ($placara > $placarb) ? 'A' : 'B';

// Função para buscar jogador
function getJogador($conn, $id) {
    $stmt = $conn->prepare("SELECT id, nome, rating, rd, vol FROM usuario WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Carrega jogadores
$j1 = getJogador($conn, $j1_id);
$j2 = getJogador($conn, $j2_id);
$j3 = getJogador($conn, $j3_id);
$j4 = getJogador($conn, $j4_id);

// Verificação de existência dos jogadores
if (!$j1 || !$j2 || !$j3 || !$j4) {
    echo "Erro: Um ou mais jogadores não foram encontrados no banco de dados.";
    exit();
}

// Instancia Glicko2
$glicko = new Glicko2();

// Cria perfis dos jogadores
$players = [
    $glicko->createPlayer($j1['rating'], $j1['rd'], $j1['vol']),
    $glicko->createPlayer($j2['rating'], $j2['rd'], $j2['vol']),
    $glicko->createPlayer($j3['rating'], $j3['rd'], $j3['vol']),
    $glicko->createPlayer($j4['rating'], $j4['rd'], $j4['vol']),
];

// Adiciona os resultados (dupla A: j1, j2 | dupla B: j3, j4)
$glicko->addResult($players[0], $players[2], $vencedor === 'A' ? 1 : 0);
$glicko->addResult($players[0], $players[3], $vencedor === 'A' ? 1 : 0);
$glicko->addResult($players[1], $players[2], $vencedor === 'A' ? 1 : 0);
$glicko->addResult($players[1], $players[3], $vencedor === 'A' ? 1 : 0);

$glicko->addResult($players[2], $players[0], $vencedor === 'B' ? 1 : 0);
$glicko->addResult($players[2], $players[1], $vencedor === 'B' ? 1 : 0);
$glicko->addResult($players[3], $players[0], $vencedor === 'B' ? 1 : 0);
$glicko->addResult($players[3], $players[1], $vencedor === 'B' ? 1 : 0);

// Atualiza os ratings
$glicko->updateRatings($players);

// Atualiza os dados no banco e salva histórico
$update = $conn->prepare("UPDATE usuario SET rating = ?, rd = ?, vol = ? WHERE id = ?");
$historico = $conn->prepare("INSERT INTO historico_rating (jogador_id, rating_anterior, rating_novo) VALUES (?, ?, ?)");

// Jogador 1
$historico->execute([$j1['id'], $j1['rating'], $players[0]->getRating()]);
$update->execute([$players[0]->getRating(), $players[0]->getRd(), $players[0]->getVolatility(), $j1['id']]);
// Jogador 2
$historico->execute([$j2['id'], $j2['rating'], $players[1]->getRating()]);
$update->execute([$players[1]->getRating(), $players[1]->getRd(), $players[1]->getVolatility(), $j2['id']]);
// Jogador 3
$historico->execute([$j3['id'], $j3['rating'], $players[2]->getRating()]);
$update->execute([$players[2]->getRating(), $players[2]->getRd(), $players[2]->getVolatility(), $j3['id']]);
// Jogador 4
$historico->execute([$j4['id'], $j4['rating'], $players[3]->getRating()]);
$update->execute([$players[3]->getRating(), $players[3]->getRd(), $players[3]->getVolatility(), $j4['id']]);

// Salva a partida na tabela partidas
$partida = $conn->prepare("INSERT INTO partidas (jogador1_id, jogador2_id, jogador3_id, jogador4_id, vencedor) VALUES (?, ?, ?, ?, ?)");
$partida->execute([$j1['id'], $j2['id'], $j3['id'], $j4['id'], $vencedor]);

header("Location: ../pos-partida.php");
?>