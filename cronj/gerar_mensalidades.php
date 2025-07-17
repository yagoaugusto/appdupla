<?php
// Define o fuso horário para garantir que a data seja sempre consistente
date_default_timezone_set('America/Sao_Paulo');

// Inclui o ficheiro de configuração global
require_once dirname(__DIR__) . '/#_global.php';

// =================================================================
// CORREÇÃO DIRETA E DEFINITIVA
// Incluímos a classe Conexao manualmente ANTES de a usarmos.
// Isto resolve o problema de o autoloader não a encontrar no contexto do Cron Job.
// =================================================================
require_once dirname(__DIR__) . '/system-classes/Conexao.php';


echo "=================================================\n";
echo "INICIANDO SCRIPT DE GESTÃO DE MENSALIDADES\n";
echo "Data e Hora: " . date('Y-m-d H:i:s') . "\n";
echo "=================================================\n\n";

// --- TAREFA 1: Gerar Novas Mensalidades Pendentes ---
try {
    echo "--- TAREFA 1: Verificando novas mensalidades para gerar...\n";
    // Agora a classe 'Conexao' já é conhecida e esta linha funcionará.
    $conn = Conexao::pegarConexao();
    
    // Incluímos também a classe Turma, por segurança.
    require_once dirname(__DIR__) . '/system-classes/Turma.php';

    Turma::scriptGerarMensalidadesPendentes();
    
    echo "Tarefa de geração de mensalidades concluída com sucesso.\n\n";

} catch (Exception $e) {
    $error_message = "ERRO na Tarefa 1 (Geração): " . $e->getMessage() . "\n";
    echo $error_message;
    error_log($error_message);
}


// --- TAREFA 2: Atualizar Mensalidades Vencidas ---
try {
    echo "--- TAREFA 2: Verificando mensalidades vencidas...\n";
    $conn = Conexao::pegarConexao();

    $sql_vencidas = "UPDATE mensalidades 
                     SET status = 'vencida' 
                     WHERE status = 'pendente' AND data_vencimento < CURDATE()";
    
    $stmt_vencidas = $conn->prepare($sql_vencidas);
    $stmt_vencidas->execute();
    
    $total_atualizadas = $stmt_vencidas->rowCount();
    
    echo "Total de mensalidades atualizadas para 'vencida': " . $total_atualizadas . "\n";
    echo "Tarefa de atualização de vencidas concluída com sucesso.\n\n";

} catch (Exception $e) {
    $error_message = "ERRO na Tarefa 2 (Vencidas): " . $e->getMessage() . "\n";
    echo $error_message;
    error_log($error_message);
}

echo "=================================================\n";
echo "SCRIPT FINALIZADO.\n";
echo "=================================================\n";