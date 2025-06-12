<?php
require_once '#_global.php'; // ajuste conforme o nome do seu arquivo de conexão

$termo = $_GET['q'];

$resultado = Usuario::listar_usuarios_busca($termo);

if (!is_array($resultado)) {
    die("Erro: A função listar_usuarios_busca() não retornou um array.");
}

$dados = [];

foreach ($resultado as $row) {
    $dados[] = [
        'identificador' => $row['id'] ?? null,
        'nome_completo' => $row['nome'].' '.$row['sobrenome'] ?? '',
        'apelido' => $row['apelido'] ?? '',
        'rating' => $row['rating'] ?? '',
        'telefone' => $row['telefone'] ?? '',
        'cpf' => $row['cpf'] ?? '',
        'cidade' => $row['cidade'] ?? '',
        'empunhadura' => $row['empunhadura'] ?? ''
    ];
}

$json = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

echo $json;
?>