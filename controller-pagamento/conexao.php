<?php

// Inclui o autoloader e a configuração global
require_once dirname(__DIR__) . '/#_global.php';

try {
    $conn = Conexao::pegarConexao();
} catch (Exception $e) {
    // Em caso de falha na conexão, encerra o script com uma mensagem de erro.
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}