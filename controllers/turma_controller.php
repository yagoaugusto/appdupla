<?php

session_start();
require_once '#_global.php';

// Verifica a permissão do usuário
if (!isset($_SESSION['DuplaUserTipo']) || !in_array($_SESSION['DuplaUserTipo'], ['gestor', 'super'])) {
    $_SESSION['mensagem'] = ['error', 'Acesso não autorizado.'];
    header('Location: ../principal.php');
    exit;
}

// Determina a ação e os IDs recebidos
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$arena_id = filter_input(INPUT_POST, 'arena_id', FILTER_VALIDATE_INT);
$turma_id = filter_input(INPUT_POST, 'turma_id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_GET, 'turma_id', FILTER_VALIDATE_INT);

// =================================================================
// LÓGICA DE REDIRECIONAMENTO INTELIGENTE (A CORREÇÃO)
// =================================================================
$redirect_url = '../turmas.php' . ($arena_id ? '?arena_id=' . $arena_id : '');

if ($action === 'matricular_aluno_com_plano') {
    // APÓS MATRICULAR, VAI PARA O FINANCEIRO para já registar o pagamento.
    $redirect_url = '../turma_financeiro.php?id=' . $turma_id;
} elseif ($action === 'registrar_pagamento_plano') {
    // Após registar um pagamento, continua no financeiro.
    $redirect_url = '../turma_financeiro.php?id=' . $turma_id;
} elseif (in_array($action, ['remover_matricula', 'alterar_status_matricula'])) {
    // Ações de gestão de alunos voltam para a página de detalhes.
    $redirect_url = '../turma_detalhes.php?id=' . $turma_id;
}

try {
    switch ($action) {
        case 'criar':
            // ... (código existente para criar turma, mantido como está)
            $horarios_formatados = [];
            $total_horarios = count($_POST['horarios']['dia_semana']);
            for ($i = 0; $i < $total_horarios; $i++) {
                $horarios_formatados[] = [
                    'dia_semana' => $_POST['horarios']['dia_semana'][$i],
                    'hora_inicio' => $_POST['horarios']['hora_inicio'][$i],
                    'quadra_id' => $_POST['horarios']['quadra_id'][$i]
                ];
            }
            $dados_turma = [
                'arena_id' => $arena_id,
                'professor_id' => filter_input(INPUT_POST, 'professor_id', FILTER_VALIDATE_INT),
                'nome' => filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING),
                'nivel' => filter_input(INPUT_POST, 'nivel', FILTER_SANITIZE_STRING),
                'vagas_total' => filter_input(INPUT_POST, 'vagas_total', FILTER_VALIDATE_INT),
                'valor_mensalidade' => (float) str_replace(',', '.', str_replace('.', '', $_POST['valor_mensalidade'])),
                'horarios' => $horarios_formatados
            ];
            if (Turma::criarTurma($dados_turma)) {
                $_SESSION['mensagem'] = ['success', 'Turma e horários criados com sucesso!'];
                // Redireciona para a lista principal de turmas
                $redirect_url = '../turmas.php?arena_id=' . $arena_id;
            } else {
                throw new Exception("Ocorreu uma falha ao salvar a turma no banco de dados.");
            }
            break;

        // ==========================================================
        // INÍCIO DAS NOVAS AÇÕES
        // ==========================================================

        // =================================================================
        // AÇÃO DE MATRÍCULA ATUALIZADA
        // =================================================================
        case 'matricular_aluno_com_plano':
            // Validação dos dados recebidos do novo modal de matrícula
            if (!$turma_id) throw new Exception("ID da turma inválido.");
            $aluno_id = filter_input(INPUT_POST, 'aluno_id', FILTER_VALIDATE_INT);
            if (!$aluno_id) throw new Exception("Nenhum aluno foi selecionado.");
            
            // Busca o valor da mensalidade da turma para passar para a função
            $turma_info = Turma::getTurmaById($turma_id);
            if (!$turma_info) throw new Exception("Turma não encontrada.");

            $dados_matricula = [
                'turma_id' => $turma_id,
                'aluno_id' => $aluno_id,
                'plano' => filter_input(INPUT_POST, 'plano_inicial', FILTER_SANITIZE_STRING),
                'data_inicio_competencia' => filter_input(INPUT_POST, 'data_inicio_competencia', FILTER_SANITIZE_STRING),
                'valor_mensalidade' => $turma_info['valor_mensalidade']
            ];

            if (Turma::matricularAlunoComPlanoInicial($dados_matricula)) {
                $_SESSION['mensagem'] = ['success', 'Aluno matriculado e primeira cobrança gerada com sucesso!'];
            } else {
                throw new Exception("Falha ao matricular aluno. Verifique se ele já não está na turma com uma cobrança para o mesmo período.");
            }
            break;

        case 'remover_matricula':
            $matricula_id = filter_input(INPUT_GET, 'matricula_id', FILTER_VALIDATE_INT);
            if (!$matricula_id) throw new Exception("ID da matrícula inválido.");

            if (Turma::removerMatricula($matricula_id)) {
                $_SESSION['mensagem'] = ['success', 'Aluno removido da turma com sucesso.'];
            } else {
                throw new Exception("Falha ao remover aluno.");
            }
            break;

        case 'alterar_status_matricula':
            $matricula_id = filter_input(INPUT_GET, 'matricula_id', FILTER_VALIDATE_INT);
            $novo_status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING);
            if (!$matricula_id || !$novo_status) throw new Exception("Dados inválidos para alterar status.");

            if (Turma::alterarStatusMatricula($matricula_id, $novo_status)) {
                $_SESSION['mensagem'] = ['success', 'Status do aluno alterado com sucesso!'];
            } else {
                throw new Exception("Falha ao alterar status do aluno.");
            }
            break;

        // ==========================================================
        // INÍCIO DA NOVA AÇÃO FINANCEIRA
        // ==========================================================
        case 'registrar_pagamento_plano':

            echo "<h1>Depuração do Pagamento</h1>";
            try {
                echo "<strong>Passo 1: Ação recebida com sucesso.</strong><br>";

                $turma_id = $_POST['turma_id'];
                echo "<strong>Passo 2:</strong> turma_id = " . htmlspecialchars($turma_id) . "<br>";

                $matricula_id = $_POST['matricula_id'];
                echo "<strong>Passo 3:</strong> matricula_id = " . htmlspecialchars($matricula_id) . "<br>";

                $aluno_selecionado_data = $_POST['aluno_selecionado_data'];
                echo "<strong>Passo 4:</strong> aluno_selecionado_data = " . htmlspecialchars($aluno_selecionado_data) . "<br>";

                $aluno_info = json_decode($aluno_selecionado_data, true);
                echo "<strong>Passo 5:</strong> Dados do aluno descodificados com sucesso.<br>";

                echo "<strong>Passo 6:</strong> Chaves internas do JSON validadas.<br>";

                echo "<strong>Passo 7:</strong> A tentar executar a função `Turma::registrarPagamentoPlano`...<br>";

                $dados_pagamento = [
                    'matricula_id' => $matricula_id,
                    'aluno_id' => $aluno_info['aluno_id'],
                    'turma_id' => $turma_id,
                    'data_matricula' => $aluno_info['data_matricula'],
                    'valor_mensal' => (float) str_replace(',', '.', str_replace('.', '', $_POST['valor_pago'])),
                    'plano' => filter_input(INPUT_POST, 'plano', FILTER_SANITIZE_STRING),
                    'forma_pagamento' => filter_input(INPUT_POST, 'forma_pagamento', FILTER_SANITIZE_STRING),
                    'data_inicio_plano' => filter_input(INPUT_POST, 'data_inicio_plano', FILTER_SANITIZE_STRING)
                ];

                $sucesso = Turma::registrarPagamentoPlano($dados_pagamento);

                if ($sucesso) {
                    echo "<h2>SUCESSO! A função foi executada e retornou 'true'.</h2>";
                } else {
                    echo "<h2>FALHA. A função foi executada mas retornou 'false'. Verifique os logs de erro do PHP ou a lógica dentro da função `registrarPagamentoPlano`.</h2>";
                }
            } catch (Exception $e) {
                // Agora, o erro lançado pela classe Turma será apanhado aqui
                echo "<h2>ERRO DETETADO!</h2>";
                echo "<p>A operação falhou com a seguinte mensagem do banco de dados:</p>";
                echo "<pre style='background-color: #ffebeb; border: 1px solid #ffbaba; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($e->getMessage()) . "</pre>";
            }

        default:
            throw new Exception("Ação desconhecida ou inválida.");
    }
} catch (Exception $e) {
    // Captura qualquer erro e o prepara para ser exibido na tela
    $_SESSION['mensagem'] = ['error', 'Erro: ' . $e->getMessage()];
}

// Redireciona para a URL apropriada no final da execução
header('Location: ' . $redirect_url);
exit;
