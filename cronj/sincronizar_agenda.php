<?php
// Define o fuso horário para garantir consistência
date_default_timezone_set('America/Sao_Paulo');

// Caminho robusto para o ficheiro de configuração global
require_once dirname(__DIR__) . '/#_global.php';

// Inclusão manual das classes para garantir que o Cron Job as encontre
require_once dirname(__DIR__) . '/system-classes/Conexao.php';
require_once dirname(__DIR__) . '/system-classes/Turma.php';

echo "=================================================\n";
echo "INICIANDO SCRIPT DE SINCRONIZAÇÃO DA AGENDA\n";
echo "Data e Hora: " . date('Y-m-d H:i:s') . "\n";
echo "=================================================\n\n";

try {
    // Chama o método que faz todo o trabalho
    Turma::scriptSincronizarAgenda();
} catch (Exception $e) {
    $error_message = "ERRO FATAL NA SINCRONIZAÇÃO: " . $e->getMessage() . "\n";
    echo $error_message;
    error_log($error_message); // Guarda o erro no log do servidor
}

echo "\n=================================================\n";
echo "SCRIPT DE SINCRONIZAÇÃO FINALIZADO.\n";
echo "=================================================\n";