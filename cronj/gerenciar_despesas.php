<?php
date_default_timezone_set('America/Sao_Paulo');

require_once dirname(__DIR__) . '#_global.php';
require_once dirname(__DIR__) . '../system-classes/Conexao.php';
require_once dirname(__DIR__) . '../system-classes/Despesa.php';
require_once dirname(__DIR__) . '../system-classes/Funcoes.php'; // Para enviar o WhatsApp

echo "=================================================\n";
echo "INICIANDO SCRIPT DE GESTÃO DE DESPESAS\n";
echo "=================================================\n\n";

// Tarefa 1: Atualizar Despesas Vencidas
try {
    $atualizadas = Despesa::scriptAtualizarDespesasVencidas();
    echo "[SUCESSO] Tarefa de verificação de vencidos concluída. Total atualizado: {$atualizadas}\n";
} catch (Exception $e) {
    echo "[ERRO] Falha ao atualizar despesas vencidas: " . $e->getMessage() . "\n";
}

// Tarefa 2: Enviar Alertas de Vencimentos Próximos
try {
    $alertas = Despesa::scriptBuscarDespesasParaAlertar(3); // Alerta para os próximos 3 dias
    if (empty($alertas)) {
        echo "[INFO] Nenhuma despesa próxima do vencimento para alertar.\n";
    } else {
        echo "[INFO] Encontradas despesas para alertar. Enviando mensagens...\n";
        foreach ($alertas as $telefone => $dados_gestor) {
            $nome_gestor = $dados_gestor['nome'];
            $mensagem = "Olá, {$nome_gestor}! 👋 Lembrete de contas a pagar da sua arena:\n\n";
            
            foreach ($dados_gestor['despesas'] as $despesa) {
                $valor_formatado = 'R$ ' . number_format($despesa['valor'], 2, ',', '.');
                $vencimento_formatado = date('d/m', strtotime($despesa['data_vencimento']));
                $mensagem .= "• *{$despesa['descricao']}* ({$valor_formatado}) - Vence em: *{$vencimento_formatado}*\n";
            }
            
            if (Funcoes::enviarMensagemUltramsg($telefone, $mensagem)) {
                echo "  - Alerta enviado para {$nome_gestor} no número {$telefone}\n";
            } else {
                echo "  - [FALHA] ao enviar alerta para {$nome_gestor}\n";
            }
        }
    }
} catch (Exception $e) {
    echo "[ERRO] Falha ao buscar despesas para alertar: " . $e->getMessage() . "\n";
}

echo "\n=================================================\n";
echo "SCRIPT FINALIZADO.\n";
echo "=================================================\n";