<?php
// Ativa a exibição de todos os erros para uma depuração clara
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Conexão Simples com a API da Asaas</h1>";

// Carrega todas as nossas configurações e classes
require_once '#_global.php';

try {
    echo "<p><strong>Status:</strong> A tentar fazer uma chamada de verificação...</p>";
    
    // 1. Chama o nosso novo método de teste simples
    $dados_conta = AsaasService::verificarConexao();
    
    echo "<p style='color: green; font-weight: bold;'>✅ SUCESSO! A conexão com a API da Asaas foi estabelecida com sucesso.</p>";
    
    echo "<h3>Dados da sua conta retornados pela API:</h3>";
    echo "<pre style='background-color: #f0f0f0; padding: 10px; border-radius: 5px; border: 1px solid #ccc;'>";
    // Exibe a resposta formatada
    print_r($dados_conta);
    echo "</pre>";

} catch (Exception $e) {
    // Se algo correr mal, o erro será apanhado e exibido aqui
    echo "<p style='color: red; font-weight: bold;'>❌ FALHA! Ocorreu um erro ao tentar conectar-se à Asaas.</p>";
    
    echo "<h3>Detalhes do Erro:</h3>";
    echo "<pre style='background-color: #ffebeb; padding: 10px; border-radius: 5px; border: 1px solid #ffbaba;'>";
    echo "<strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage());
    echo "</pre>";
}
?>