<?php

class Turma
{
    /**
     * Busca todas as turmas de uma arena, incluindo o nome do professor e a contagem de alunos.
     */
    public static function getTurmasPorArena(int $arena_id)
    {
        $conn = Conexao::pegarConexao();
        // Query atualizada para contar os alunos ativos em cada turma
        $sql = "SELECT 
                    t.*, 
                    u.nome as professor_nome,
                    (SELECT COUNT(*) FROM turma_alunos ta WHERE ta.turma_id = t.id AND ta.status = 'ativo') as alunos_ativos
                FROM turmas t 
                JOIN usuario u ON t.professor_id = u.id 
                WHERE t.arena_id = ? AND t.status != 'arquivada'
                ORDER BY t.nome ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$arena_id]);
        $turmas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt_horarios = $conn->prepare("
            SELECT th.*, q.nome as quadra_nome 
            FROM turma_horarios th
            JOIN quadras q ON th.quadra_id = q.id
            WHERE th.turma_id = ?
        ");
        foreach ($turmas as $key => $turma) {
            $stmt_horarios->execute([$turma['id']]);
            $turmas[$key]['horarios'] = $stmt_horarios->fetchAll(PDO::FETCH_ASSOC);
        }

        return $turmas;
    }

    /**
     * NOVO: Busca os detalhes de uma única turma pelo seu ID.
     */
    public static function getTurmaById(int $turma_id)
    {
        $conn = Conexao::pegarConexao();
        $sql = "SELECT t.*, u.nome as professor_nome, u.sobrenome as professor_sobrenome
                FROM turmas t
                JOIN usuario u ON t.professor_id = u.id
                WHERE t.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$turma_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca todos os alunos matriculados em uma turma, incluindo um resumo financeiro de suas mensalidades.
     * @param int $turma_id
     * @return array
     */
    public static function getAlunosDaTurma(int $turma_id)
    {
        $conn = Conexao::pegarConexao();

        // 1. Busca a lista principal de alunos matriculados
        $sql_alunos = "SELECT 
                           u.id, u.nome, u.sobrenome, u.apelido, u.telefone,
                           ta.status, ta.data_matricula, ta.id as matricula_id
                       FROM turma_alunos ta
                       JOIN usuario u ON ta.aluno_id = u.id
                       WHERE ta.turma_id = ?
                       ORDER BY u.nome ASC";
        $stmt_alunos = $conn->prepare($sql_alunos);
        $stmt_alunos->execute([$turma_id]);
        $alunos = $stmt_alunos->fetchAll(PDO::FETCH_ASSOC);

        if (empty($alunos)) {
            return [];
        }

        // 2. Busca o resumo de todas as mensalidades de todos os alunos da turma de uma só vez
        $matriculas_ids = array_column($alunos, 'matricula_id');
        $placeholders = implode(',', array_fill(0, count($matriculas_ids), '?'));

        $sql_resumo = "SELECT matricula_id, status, COUNT(*) as total 
                       FROM mensalidades 
                       WHERE matricula_id IN ($placeholders) 
                       GROUP BY matricula_id, status";

        $stmt_resumo = $conn->prepare($sql_resumo);
        $stmt_resumo->execute($matriculas_ids);
        $resumos_db = $stmt_resumo->fetchAll(PDO::FETCH_ASSOC);

        // 3. Organiza os resumos num array para fácil acesso
        $resumos_por_matricula = [];
        foreach ($resumos_db as $resumo) {
            $resumos_por_matricula[$resumo['matricula_id']][$resumo['status']] = $resumo['total'];
        }

        // 4. Anexa o resumo financeiro a cada aluno
        foreach ($alunos as $key => $aluno) {
            $alunos[$key]['resumo_financeiro'] = [
                'paga' => $resumos_por_matricula[$aluno['matricula_id']]['paga'] ?? 0,
                'pendente' => $resumos_por_matricula[$aluno['matricula_id']]['pendente'] ?? 0,
                'vencida' => $resumos_por_matricula[$aluno['matricula_id']]['vencida'] ?? 0,
            ];
        }

        return $alunos;
    }


    /**
     * MÉTODO UNIFICADO (V2.1): Usa "named parameters" para robustez máxima.
     */
    public static function matricularAlunoComPlanoInicial(array $dados)
    {
        $conn = Conexao::pegarConexao();
        try {
            $conn->beginTransaction();

            // 1. VERIFICA SE A MATRÍCULA JÁ EXISTE
            $stmt_find = $conn->prepare("SELECT id, status FROM turma_alunos WHERE turma_id = :turma_id AND aluno_id = :aluno_id");
            $stmt_find->execute([':turma_id' => $dados['turma_id'], ':aluno_id' => $dados['aluno_id']]);
            $matricula_existente = $stmt_find->fetch(PDO::FETCH_ASSOC);

            if ($matricula_existente) {
                $matricula_id = $matricula_existente['id'];
                if ($matricula_existente['status'] == 'inativo') {
                    $stmt_update_status = $conn->prepare("UPDATE turma_alunos SET status = 'ativo' WHERE id = :id");
                    $stmt_update_status->execute([':id' => $matricula_id]);
                }
            } else {
                $stmt_matricula = $conn->prepare(
                    "INSERT INTO turma_alunos (turma_id, aluno_id, data_matricula, percentual_repasse, valor_mensalidade_acordado) 
                     VALUES (:turma_id, :aluno_id, NOW(), :percentual_repasse, :valor_acordado)"
                );
                $stmt_matricula->execute([
                    ':turma_id' => $dados['turma_id'],
                    ':aluno_id' => $dados['aluno_id'],
                    ':percentual_repasse' => $dados['percentual_repasse'],
                    ':valor_acordado' => $dados['valor_mensalidade_acordado']
                ]);
                $matricula_id = $conn->lastInsertId();
            }

            if (!$matricula_id) throw new Exception("Falha crítica ao obter o ID da matrícula.");

            // 2. Lógica para gerar as mensalidades pendentes
            $numero_meses = 1;
            if ($dados['plano'] == 'trimestral') $numero_meses = 3;
            if ($dados['plano'] == 'semestral') $numero_meses = 6;

            $data_base = new DateTime($dados['data_inicio_competencia'] . '-01');
            $dia_vencimento_base = date('d');

            for ($i = 0; $i < $numero_meses; $i++) {
                $competencia_atual_str = $data_base->format('Y-m-d');

                $stmt_check_mensalidade = $conn->prepare("SELECT id FROM mensalidades WHERE matricula_id = :matricula_id AND competencia = :competencia");
                $stmt_check_mensalidade->execute([':matricula_id' => $matricula_id, ':competencia' => $competencia_atual_str]);
                if ($stmt_check_mensalidade->fetch()) {
                    $data_base->modify('first day of next month');
                    continue;
                }

                $dias_no_mes_competencia = $data_base->format('t');
                $dia_vencimento = min($dia_vencimento_base, $dias_no_mes_competencia);
                $vencimento_str = $data_base->format('Y-m-') . str_pad($dia_vencimento, 2, '0', STR_PAD_LEFT);

                // =================================================================
                // AQUI ESTÁ A QUERY CORRIGIDA COM "NAMED PARAMETERS"
                // =================================================================
                $stmt_mensalidade = $conn->prepare(
                    "INSERT INTO mensalidades (matricula_id, aluno_id, turma_id, competencia, data_vencimento, valor, status)
                     VALUES (:matricula_id, :aluno_id, :turma_id, :competencia, :data_vencimento, :valor, 'pendente')"
                );
                $stmt_mensalidade->execute([
                    ':matricula_id' => $matricula_id,
                    ':aluno_id' => $dados['aluno_id'],
                    ':turma_id' => $dados['turma_id'],
                    ':competencia' => $competencia_atual_str,
                    ':data_vencimento' => $vencimento_str,
                    ':valor' => $dados['valor_mensalidade_acordado']
                ]);

                $data_base->modify('first day of next month');
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollBack();
            // Restaure esta linha para a versão de produção depois de confirmar a correção
            // error_log("Erro ao matricular aluno com plano inicial: " . $e->getMessage());
            // return false;
            throw new Exception("ERRO DETALHADO DO BANCO DE DADOS: " . $e->getMessage());
        }
    }


    /**
     * NOVO: Matricula um novo aluno em uma turma.
     */
    public static function matricularAluno(int $turma_id, int $aluno_id)
    {
        $conn = Conexao::pegarConexao();
        // O `IGNORE` impede um erro caso a matrícula já exista (devido ao UNIQUE KEY)
        $sql = "INSERT IGNORE INTO turma_alunos (turma_id, aluno_id, data_matricula) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$turma_id, $aluno_id, date('Y-m-d')]);
    }

    /**
     * NOVO: Remove a matrícula de um aluno de uma turma.
     */
    public static function removerMatricula(int $matricula_id)
    {
        $conn = Conexao::pegarConexao();
        $sql = "DELETE FROM turma_alunos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$matricula_id]);
    }

    /**
     * NOVO: Altera o status de uma matrícula (ativo/inativo).
     */
    public static function alterarStatusMatricula(int $matricula_id, string $novo_status)
    {
        $conn = Conexao::pegarConexao();
        // Validação para garantir que o status seja apenas 'ativo' ou 'inativo'
        if (!in_array($novo_status, ['ativo', 'inativo'])) {
            return false;
        }
        $sql = "UPDATE turma_alunos SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$novo_status, $matricula_id]);
    }

    /**
     * Cria uma nova turma e seus horários usando uma transação.
     * (Este método já existe e foi mantido)
     */
    public static function criarTurma(array $dados)
    {
        // ... (código existente da função criarTurma) ...
        $conn = Conexao::pegarConexao();
        try {
            $conn->beginTransaction();

            // 1. Inserir na tabela principal `turmas`
            $stmt_turma = $conn->prepare(
                "INSERT INTO turmas (arena_id, professor_id, nome, nivel, vagas_total, valor_mensalidade) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt_turma->execute([
                $dados['arena_id'],
                $dados['professor_id'],
                $dados['nome'],
                $dados['nivel'],
                $dados['vagas_total'],
                $dados['valor_mensalidade']
            ]);
            $turma_id = $conn->lastInsertId();

            // 2. Inserir os horários na tabela `turma_horarios`
            $stmt_horario = $conn->prepare(
                "INSERT INTO turma_horarios (turma_id, quadra_id, dia_semana, hora_inicio, hora_fim) 
                 VALUES (?, ?, ?, ?, ?)"
            );

            foreach ($dados['horarios'] as $horario) {

                if (!isset($horario['hora_inicio'])) {
                    continue;
                }

                $hora_inicio_str = trim($horario['hora_inicio']);
                if ($hora_inicio_str === '') {
                    continue;
                }

                if (!preg_match('/^\d{1,2}:\d{2}$/', $hora_inicio_str)) {
                    error_log("Formato de hora inválido detectado no formulário e ignorado: " . $hora_inicio_str);
                    continue;
                }

                $hora_fim_obj = new DateTime($hora_inicio_str);
                $hora_fim_obj->add(new DateInterval('PT1H'));
                $hora_fim_str = $hora_fim_obj->format('H:i:s');

                $stmt_horario->execute([
                    $turma_id,
                    $horario['quadra_id'],
                    $horario['dia_semana'],
                    $hora_inicio_str,
                    $hora_fim_str
                ]);
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Erro ao criar turma: " . $e->getMessage());
            return false;
        }
    }



    // ==========================================================
    // INÍCIO DOS NOVOS MÉTODOS FINANCEIROS
    // ==========================================================

    /**
     * Busca o histórico de mensalidades de uma turma, incluindo a data do pagamento.
     * @param int $turma_id
     * @return array
     */
    public static function getMensalidadesDaTurma(int $turma_id)
    {
        $conn = Conexao::pegarConexao();
        // ==========================================================
        // QUERY ATUALIZADA com JOIN para buscar a data do pagamento
        // ==========================================================
        $sql = "SELECT 
                    m.*,
                    u.nome as aluno_nome,
                    u.sobrenome as aluno_sobrenome,
                    pp.data_pagamento -- A nova coluna que estamos a buscar
                FROM mensalidades m
                JOIN usuario u ON m.aluno_id = u.id
                LEFT JOIN pagamentos_planos pp ON m.pagamento_id = pp.id -- O novo JOIN
                WHERE m.turma_id = ?
                ORDER BY m.competencia DESC, u.nome ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$turma_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * NOVO MÉTODO DE PAGAMENTO (V5.0): Regista um pagamento para mensalidades selecionadas.
     * @param array $dados Contém os IDs das mensalidades, valor total, forma de pagamento, etc.
     * @return bool
     */
    public static function registrarPagamentoSelecionado(array $dados)
    {
        $conn = Conexao::pegarConexao();
        try {
            $conn->beginTransaction();

            // 1. Regista a Transação Financeira principal (a entrada no caixa)
            $stmt_pagamento = $conn->prepare(
                "INSERT INTO pagamentos_planos (matricula_id, data_pagamento, valor_total_pago, forma_pagamento)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt_pagamento->execute([
                $dados['matricula_id'],
                $dados['data_pagamento'],
                $dados['valor_total_pago'],
                $dados['forma_pagamento']
            ]);
            $pagamento_id = $conn->lastInsertId();

            // 2. Itera sobre as mensalidades selecionadas para dar baixa
            foreach ($dados['mensalidades_ids'] as $mensalidade_id) {
                // Atualiza o status da mensalidade e vincula-a ao pagamento
                $stmt_mensalidade = $conn->prepare(
                    "UPDATE mensalidades SET status = 'paga', pagamento_id = ? WHERE id = ?"
                );
                $stmt_mensalidade->execute([$pagamento_id, $mensalidade_id]);

                // Busca os detalhes necessários para criar o repasse
                $stmt_detalhes = $conn->prepare(
                    "SELECT t.professor_id, m.valor, ta.percentual_repasse 
                     FROM mensalidades m
                     JOIN turmas t ON m.turma_id = t.id
                     JOIN turma_alunos ta ON m.matricula_id = ta.id
                     WHERE m.id = ?"
                );
                $stmt_detalhes->execute([$mensalidade_id]);
                $detalhes = $stmt_detalhes->fetch(PDO::FETCH_ASSOC);

                if ($detalhes) {
                    // Calcula o valor do repasse com base no percentual da matrícula
                    $valor_repasse = $detalhes['valor'] * ($detalhes['percentual_repasse'] / 100);

                    // Cria o registo de repasse pendente para o professor
                    if ($valor_repasse > 0) {
                        $stmt_repasse = $conn->prepare(
                            "INSERT INTO repasses_professores (mensalidade_id, professor_id, valor_repasse, status)
                             VALUES (?, ?, ?, 'pendente')"
                        );
                        $stmt_repasse->execute([$mensalidade_id, $detalhes['professor_id'], $valor_repasse]);
                    }
                }
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Erro ao registar pagamento selecionado (V5.0): " . $e->getMessage());
            // Para depuração, ative a linha abaixo
            // throw new Exception("Erro ao registar pagamento: " . $e->getMessage());
            return false;
        }
    }


    /**
     * Cria um registo de repasse pendente para um professor.
     * Esta função é chamada internamente pelo registrarPagamentoPlano.
     */
    private static function criarRepasseProfessor($conn, $mensalidade_id, $professor_id, $valor_base_mensalidade, $matricula_id) // <-- NOVO PARÂMETRO
    {
        // Busca o percentual específico desta matrícula
        $stmt_perc = $conn->prepare("SELECT percentual_repasse FROM turma_alunos WHERE id = ?");
        $stmt_perc->execute([$matricula_id]);
        $percentual = $stmt_perc->fetchColumn();

        if ($percentual === false || $percentual <= 0) {
            // Se não houver percentual definido, não cria repasse.
            return;
        }

        // A regra de negócio agora é dinâmica, baseada no que foi guardado.
        $valor_repasse = $valor_base_mensalidade * ($percentual / 100);

        $stmt = $conn->prepare(
            "INSERT INTO repasses_professores (mensalidade_id, professor_id, valor_repasse, status)
             VALUES (?, ?, ?, 'pendente')"
        );
        $stmt->execute([$mensalidade_id, $professor_id, $valor_repasse]);
    }

    /**
     * Função auxiliar para encontrar o ID de uma mensalidade que acabou de ser atualizada via ON DUPLICATE KEY.
     */
    private static function findMensalidadeId($conn, $matricula_id, $competencia)
    {
        $stmt = $conn->prepare("SELECT id FROM mensalidades WHERE matricula_id = ? AND competencia = ?");
        $stmt->execute([$matricula_id, $competencia]);
        return $stmt->fetchColumn();
    }


    /**
     * Script para ser executado via Cron Job para gerar mensalidades pendentes.
     * VERSÃO ROBUSTA (2.0): Usa o valor acordado na matrícula e named parameters.
     * @return void
     */
    public static function scriptGerarMensalidadesPendentes()
    {
        $conn = Conexao::pegarConexao();
        
        // Query ATUALIZADA para buscar o valor individual da matrícula, não o da turma.
        $sql_ativos = "SELECT 
                           ta.id as matricula_id, 
                           ta.aluno_id, 
                           ta.turma_id, 
                           ta.data_matricula, 
                           ta.valor_mensalidade_acordado -- <-- O valor correto
                       FROM turma_alunos ta
                       WHERE ta.status = 'ativo'";
        $stmt_ativos = $conn->query($sql_ativos);

        $hoje = new DateTime();
        $competencia_atual_str = $hoje->format('Y-m-01');

        $stmt_check = $conn->prepare("SELECT id FROM mensalidades WHERE matricula_id = :matricula_id AND competencia = :competencia");
        
        $stmt_insert = $conn->prepare(
            "INSERT INTO mensalidades (matricula_id, aluno_id, turma_id, competencia, data_vencimento, valor, status)
             VALUES (:matricula_id, :aluno_id, :turma_id, :competencia, :data_vencimento, :valor, 'pendente')"
        );

        while ($matricula = $stmt_ativos->fetch(PDO::FETCH_ASSOC)) {
            // Verifica se a mensalidade para a competência atual já existe
            $stmt_check->execute([
                ':matricula_id' => $matricula['matricula_id'],
                ':competencia' => $competencia_atual_str
            ]);
            if ($stmt_check->fetch()) {
                continue; // Já existe, pula para o próximo aluno
            }

            // Garante que não vai gerar cobrança para um mês anterior à matrícula do aluno
            $data_matricula = new DateTime($matricula['data_matricula']);
            if ($data_matricula->format('Y-m') > $hoje->format('Y-m')) {
                continue; // Aluno matriculado para começar no futuro, não gera cobrança ainda.
            }
            
            // Lógica de vencimento baseada no dia da matrícula
            $dia_vencimento_base = $data_matricula->format('d');
            $dias_no_mes_competencia = $hoje->format('t');
            $dia_vencimento = min($dia_vencimento_base, $dias_no_mes_competencia);
            $data_vencimento = $hoje->format('Y-m-') . str_pad($dia_vencimento, 2, '0', STR_PAD_LEFT);
            
            // Insere a nova mensalidade como pendente usando o valor acordado
            $stmt_insert->execute([
                ':matricula_id' => $matricula['matricula_id'],
                ':aluno_id' => $matricula['aluno_id'],
                ':turma_id' => $matricula['turma_id'],
                ':competencia' => $competencia_atual_str,
                ':data_vencimento' => $data_vencimento,
                ':valor' => $matricula['valor_mensalidade_acordado'] // <-- USA O VALOR CORRETO
            ]);
            echo "Gerada mensalidade pendente para matrícula ID: " . $matricula['matricula_id'] . "\n";
        }
    }
    

    /**
     * NOVO MÉTODO MESTRE: Recolhe e organiza todos os dados para o painel de gestão de alunos.
     * @param int $arena_id
     * @return array Um array estruturado com estatísticas e uma lista de turmas/alunos/mensalidades.
     */
    public static function getDadosGestaoAlunos(int $arena_id)
    {
        $conn = Conexao::pegarConexao();
        $resultado = [
            'stats' => [
                'total_alunos_ativos' => 0,
                'total_vagas_disponiveis' => 0,
                'total_vagas_ocupadas' => 0,
                'alunos_por_professor' => [],
                'mensalidades_pendentes' => 0,
                'mensalidades_vencidas' => 0
            ],
            'turmas' => []
        ];

        // --- 1. Calcular as Estatísticas (KPIs) ---
        // Alunos ativos e vagas
        $sql_stats1 = "SELECT 
                        COUNT(DISTINCT ta.aluno_id) as total_alunos,
                        SUM(t.vagas_total) as total_vagas
                     FROM turmas t
                     LEFT JOIN turma_alunos ta ON t.id = ta.turma_id AND ta.status = 'ativo'
                     WHERE t.arena_id = ? AND t.status = 'ativa'";
        $stmt_stats1 = $conn->prepare($sql_stats1);
        $stmt_stats1->execute([$arena_id]);
        $stats1 = $stmt_stats1->fetch(PDO::FETCH_ASSOC);
        $resultado['stats']['total_alunos_ativos'] = (int) $stats1['total_alunos'];
        $resultado['stats']['total_vagas_ocupadas'] = (int) $stats1['total_alunos'];
        $resultado['stats']['total_vagas_disponiveis'] = (int) $stats1['total_vagas'] - (int) $stats1['total_alunos'];

        // Alunos por professor
        $sql_stats2 = "SELECT u.nome as professor_nome, COUNT(DISTINCT ta.aluno_id) as qtd_alunos
                       FROM turmas t
                       JOIN usuario u ON t.professor_id = u.id
                       LEFT JOIN turma_alunos ta ON t.id = ta.turma_id AND ta.status = 'ativo'
                       WHERE t.arena_id = ? AND t.status = 'ativa'
                       GROUP BY t.professor_id
                       ORDER BY qtd_alunos DESC";
        $stmt_stats2 = $conn->prepare($sql_stats2);
        $stmt_stats2->execute([$arena_id]);
        $resultado['stats']['alunos_por_professor'] = $stmt_stats2->fetchAll(PDO::FETCH_ASSOC);

        // Mensalidades pendentes e vencidas
        $sql_stats3 = "SELECT status, COUNT(*) as total FROM mensalidades WHERE turma_id IN (SELECT id FROM turmas WHERE arena_id = ?) AND (status = 'pendente' OR status = 'vencida') GROUP BY status";
        $stmt_stats3 = $conn->prepare($sql_stats3);
        $stmt_stats3->execute([$arena_id]);
        while ($row = $stmt_stats3->fetch(PDO::FETCH_ASSOC)) {
            if ($row['status'] == 'pendente') $resultado['stats']['mensalidades_pendentes'] = $row['total'];
            if ($row['status'] == 'vencida') $resultado['stats']['mensalidades_vencidas'] = $row['total'];
        }

        // --- 2. Montar a Lista Estruturada ---
        // Primeiro, obtemos todos os dados de que precisamos em queries otimizadas
        $turmas_db = self::getTurmasPorArena($arena_id);
        $alunos_db = $conn->prepare("SELECT u.id, u.nome, u.sobrenome, ta.id as matricula_id, ta.turma_id FROM turma_alunos ta JOIN usuario u ON ta.aluno_id = u.id WHERE ta.turma_id IN (SELECT id FROM turmas WHERE arena_id = ?)");
        $alunos_db->execute([$arena_id]);

        $mensalidades_db = $conn->prepare("SELECT * FROM mensalidades WHERE turma_id IN (SELECT id FROM turmas WHERE arena_id = ?) ORDER BY competencia DESC");
        $mensalidades_db->execute([$arena_id]);

        // Organizamos as mensalidades por aluno para fácil acesso
        $mensalidades_por_aluno = [];
        foreach ($mensalidades_db->fetchAll(PDO::FETCH_ASSOC) as $m) {
            $mensalidades_por_aluno[$m['matricula_id']][] = $m;
        }

        // Organizamos os alunos por turma
        $alunos_por_turma = [];
        foreach ($alunos_db->fetchAll(PDO::FETCH_ASSOC) as $a) {
            // Para cada aluno, já anexamos as suas mensalidades
            $a['mensalidades'] = $mensalidades_por_aluno[$a['matricula_id']] ?? [];
            $alunos_por_turma[$a['turma_id']][] = $a;
        }

        // Finalmente, juntamos tudo no array de resultado
        foreach ($turmas_db as $turma) {
            $turma['alunos'] = $alunos_por_turma[$turma['id']] ?? [];
            $resultado['turmas'][] = $turma;
        }

        return $resultado;
    }

    /**
     * Sincroniza os horários das turmas com a agenda principal, bloqueando os horários.
     * VERSÃO FINAL CORRIGIDA: Corrige a comparação de dias da semana (Inglês vs Português).
     * @return void
     */
    public static function scriptSincronizarAgenda()
    {
        $conn = Conexao::pegarConexao();

        // --- 1. Preparação ---
        $stmt_insert = $conn->prepare(
            "INSERT INTO agenda_quadras (quadra_id, data, hora_inicio, hora_fim, status, turma_id)
             VALUES (?, ?, ?, ?, 'aula', ?)"
        );
        $sql_horarios = "SELECT th.id as turma_horario_id, th.turma_id, th.quadra_id, th.dia_semana, th.hora_inicio, th.hora_fim 
                         FROM turma_horarios th
                         JOIN turmas t ON th.turma_id = t.id
                         WHERE t.status = 'ativa'";
        $stmt_horarios = $conn->query($sql_horarios);

        // --- 2. Definição do Horizonte e do "Tradutor" de Dias ---
        $hoje = new DateTime();
        $data_limite = (new DateTime())->modify('+90 days');
        $intervalo = new DateInterval('P1D');

        // =================================================================
        // A CORREÇÃO ESTÁ AQUI: O nosso "dicionário" de dias da semana
        // =================================================================
        $dias_semana_map = [
            'monday'    => 'segunda',
            'tuesday'   => 'terca',
            'wednesday' => 'quarta',
            'thursday'  => 'quinta',
            'friday'    => 'sexta',
            'saturday'  => 'sabado',
            'sunday'    => 'domingo'
        ];
        // =================================================================

        echo "Iniciando sincronização de agenda para os próximos 90 dias...\n";

        while ($regra = $stmt_horarios->fetch(PDO::FETCH_ASSOC)) {
            $periodo_total = new DatePeriod($hoje, $intervalo, $data_limite);

            foreach ($periodo_total as $dia) {
                $dia_ingles = strtolower($dia->format('l'));
                $dia_portugues = $dias_semana_map[$dia_ingles]; // Traduzimos o dia

                // Agora a comparação funciona perfeitamente
                if ($dia_portugues == $regra['dia_semana']) {

                    $data_aula_str = $dia->format('Y-m-d');

                    $stmt_excecao = $conn->prepare("SELECT id FROM turma_horario_excecoes WHERE turma_horario_id = ? AND data_excecao = ?");
                    $stmt_excecao->execute([$regra['turma_horario_id'], $data_aula_str]);

                    if ($stmt_excecao->fetch()) {
                        echo "  - [IGNORADO] Aula da turma #" . $regra['turma_id'] . " em " . $data_aula_str . " foi cancelada.\n";
                        continue;
                    }

                    $stmt_check = $conn->prepare("SELECT id FROM agenda_quadras WHERE quadra_id = ? AND data = ? AND hora_inicio = ?");
                    $stmt_check->execute([$regra['quadra_id'], $data_aula_str, $regra['hora_inicio']]);

                    if (!$stmt_check->fetch()) {
                        $stmt_insert->execute([
                            $regra['quadra_id'],
                            $data_aula_str,
                            $regra['hora_inicio'],
                            $regra['hora_fim'],
                            $regra['turma_id']
                        ]);
                        echo "  - [CRIADO] Bloqueio para turma #" . $regra['turma_id'] . " em " . $data_aula_str . " às " . substr($regra['hora_inicio'], 0, 5) . "\n";
                    }
                }
            }
        }
        echo "Sincronização concluída.\n";
    }



    /**
     * NOVO MÉTODO MESTRE (VERSÃO 2.0): Recolhe e organiza os dados para o novo painel de relatórios.
     * @param int $arena_id
     * @param string $competencia No formato 'YYYY-MM'
     * @return array Um array estruturado com estatísticas e listas detalhadas.
     */
    public static function getDadosRelatorioTurmas(int $arena_id, string $competencia)
    {
        $conn = Conexao::pegarConexao();
        $resultado = [
            'stats' => [/* ... */],
            'professores' => [],
            'turmas' => []
        ];
        $competencia_sql = $competencia . '-01';

        // --- 1. Buscar todos os dados base de uma só vez ---
        $sql_base = "SELECT 
                        t.id as turma_id, t.nome as turma_nome, t.vagas_total,
                        p.id as professor_id, p.nome as professor_nome,
                        a.id as aluno_id, a.nome as aluno_nome, a.sobrenome as aluno_sobrenome,
                        m.status as mensalidade_status, m.valor as mensalidade_valor
                     FROM turmas t
                     JOIN usuario p ON t.professor_id = p.id
                     LEFT JOIN turma_alunos ta ON t.id = ta.turma_id AND ta.status = 'ativo'
                     LEFT JOIN usuario a ON ta.aluno_id = a.id
                     LEFT JOIN mensalidades m ON ta.id = m.matricula_id AND m.competencia = ?
                     WHERE t.arena_id = ? AND t.status = 'ativa'
                     ORDER BY p.nome, t.nome, a.nome";

        $stmt_base = $conn->prepare($sql_base);
        $stmt_base->execute([$competencia_sql, $arena_id]);
        $dados_brutos = $stmt_base->fetchAll(PDO::FETCH_ASSOC);

        // --- 2. Processar e estruturar os dados em PHP (muito mais rápido) ---
        $professores = [];
        $turmas = [];
        $kpis = [
            'faturamento_mes' => 0,
            'a_receber_mes' => 0,
            'vencido_total' => 0,
            'total_alunos_ativos' => 0,
            'total_vagas' => 0
        ];
        $alunos_contados = [];

        foreach ($dados_brutos as $row) {
            if (!$row['aluno_id']) continue; // Pula turmas sem alunos

            // Estrutura do Professor
            if (!isset($professores[$row['professor_id']])) {
                $professores[$row['professor_id']] = [
                    'nome' => $row['professor_nome'],
                    'faturamento' => 0,
                    'a_receber' => 0,
                    'vencido' => 0,
                    'alunos' => []
                ];
            }
            // Estrutura da Turma
            if (!isset($turmas[$row['turma_id']])) {
                $turmas[$row['turma_id']] = [
                    'nome' => $row['turma_nome'],
                    'professor_nome' => $row['professor_nome'],
                    'vagas_total' => $row['vagas_total'],
                    'faturamento' => 0,
                    'a_receber' => 0,
                    'vencido' => 0,
                    'alunos' => []
                ];
                $kpis['total_vagas'] += $row['vagas_total'];
            }

            // Estrutura do Aluno (com status financeiro)
            $aluno_key = $row['aluno_id'];
            if (!isset($turmas[$row['turma_id']]['alunos'][$aluno_key])) {
                $turmas[$row['turma_id']]['alunos'][$aluno_key] = ['nome_completo' => $row['aluno_nome'] . ' ' . $row['aluno_sobrenome'], 'status_financeiro' => $row['mensalidade_status'] ?? 'sem_cobranca'];
                if (!isset($professores[$row['professor_id']]['alunos'][$aluno_key])) {
                    $professores[$row['professor_id']]['alunos'][$aluno_key] = ['nome_completo' => $row['aluno_nome'] . ' ' . $row['aluno_sobrenome'], 'status_financeiro' => $row['mensalidade_status'] ?? 'sem_cobranca', 'turma_nome' => $row['turma_nome']];
                }
                if (!isset($alunos_contados[$aluno_key])) {
                    $kpis['total_alunos_ativos']++;
                    $alunos_contados[$aluno_key] = true;
                }
            }

            // Calcular finanças
            if ($row['mensalidade_status'] == 'paga') {
                $turmas[$row['turma_id']]['faturamento'] += $row['mensalidade_valor'];
                $professores[$row['professor_id']]['faturamento'] += $row['mensalidade_valor'];
                $kpis['faturamento_mes'] += $row['mensalidade_valor'];
            } elseif ($row['mensalidade_status'] == 'pendente') {
                $turmas[$row['turma_id']]['a_receber'] += $row['mensalidade_valor'];
                $professores[$row['professor_id']]['a_receber'] += $row['mensalidade_valor'];
                $kpis['a_receber_mes'] += $row['mensalidade_valor'];
            } elseif ($row['mensalidade_status'] == 'vencida') {
                $turmas[$row['turma_id']]['vencido'] += $row['mensalidade_valor'];
                $professores[$row['professor_id']]['vencido'] += $row['mensalidade_valor'];
                $kpis['vencido_total'] += $row['mensalidade_valor'];
            }
        }

        // --- 3. Calcular KPIs Finais ---
        $sql_novas_mat = "SELECT COUNT(*) FROM turma_alunos WHERE data_matricula LIKE ? AND turma_id IN (SELECT id FROM turmas WHERE arena_id = ?)";
        $stmt_novas_mat = $conn->prepare($sql_novas_mat);
        $stmt_novas_mat->execute([$competencia . '%', $arena_id]);
        $kpis['novas_matriculas_mes'] = $stmt_novas_mat->fetchColumn();
        if ($kpis['total_vagas'] > 0) {
            $kpis['taxa_ocupacao'] = round(($kpis['total_alunos_ativos'] / $kpis['total_vagas']) * 100);
        } else {
            $kpis['taxa_ocupacao'] = 0;
        }

        $resultado['stats'] = $kpis;
        $resultado['professores'] = array_values($professores);
        $resultado['turmas'] = array_values($turmas);

        return $resultado;
    }


    /**
     * NOVO: Busca todas as mensalidades com status 'pendente' ou 'vencida' de um aluno numa turma.
     * @param int $matricula_id
     * @return array
     */
    public static function getMensalidadesAbertasPorMatricula(int $matricula_id)
    {
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare(
            "SELECT * FROM mensalidades 
             WHERE matricula_id = ? AND (status = 'pendente' OR status = 'vencida')
             ORDER BY competencia ASC"
        );
        $stmt->execute([$matricula_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * NOVO: Regista o pagamento de múltiplos repasses para um professor.
     * @param array $repasses_ids Array com os IDs dos repasses a serem marcados como pagos.
     * @param string $data_pagamento Data em que o pagamento foi efetuado.
     * @return bool
     */
    public static function pagarRepasses(array $repasses_ids, string $data_pagamento)
    {
        if (empty($repasses_ids)) {
            return false;
        }

        $conn = Conexao::pegarConexao();
        try {
            $conn->beginTransaction();

            // Cria os placeholders (?) para a query IN de forma segura
            $placeholders = implode(',', array_fill(0, count($repasses_ids), '?'));

            $sql = "UPDATE repasses_professores 
                    SET status = 'pago', data_repasse = ? 
                    WHERE id IN ({$placeholders})";

            $stmt = $conn->prepare($sql);

            // Junta os parâmetros num único array para o execute()
            $params = array_merge([$data_pagamento], $repasses_ids);

            $stmt->execute($params);

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Erro ao pagar repasses: " . $e->getMessage());
            return false;
        }
    }




    /**
     * Busca todos os repasses pendentes, agora incluindo o nome da turma.
     */
    public static function getRepassesPendentes(int $arena_id, string $competencia)
    {
        $conn = Conexao::pegarConexao();
        // QUERY ATUALIZADA para incluir o nome da turma (t.nome)
        $sql = "SELECT 
                    r.id as repasse_id, r.valor_repasse,
                    p.id as professor_id, p.nome as professor_nome,
                    a.nome as aluno_nome, a.sobrenome as aluno_sobrenome,
                    m.competencia,
                    t.nome as turma_nome -- << NOVA INFORMAÇÃO
                FROM repasses_professores r
                JOIN usuario p ON r.professor_id = p.id
                JOIN mensalidades m ON r.mensalidade_id = m.id
                JOIN turmas t ON m.turma_id = t.id -- << NOVO JOIN
                JOIN turma_alunos ta ON m.matricula_id = ta.id
                JOIN usuario a ON ta.aluno_id = a.id
                WHERE r.status = 'pendente' 
                  AND m.turma_id IN (SELECT id FROM turmas WHERE arena_id = ?)
                  AND m.competencia = ?
                ORDER BY p.nome, t.nome, m.competencia";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$arena_id, $competencia . '-01']);
        $repasses_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // O restante da função para agrupar continua igual...
        $repasses_agrupados = [];
        foreach ($repasses_db as $repasse) {
            $prof_id = $repasse['professor_id'];
            if (!isset($repasses_agrupados[$prof_id])) {
                $repasses_agrupados[$prof_id] = ['professor_nome' => $repasse['professor_nome'], 'total_a_pagar' => 0, 'repasses' => []];
            }
            $repasses_agrupados[$prof_id]['repasses'][] = $repasse;
            $repasses_agrupados[$prof_id]['total_a_pagar'] += $repasse['valor_repasse'];
        }
        return $repasses_agrupados;
    }

    /**
     * Busca o histórico de repasses pagos, agora incluindo o nome da turma.
     */
    public static function getRepassesPagos(int $arena_id, string $competencia)
    {
        $conn = Conexao::pegarConexao();
        // QUERY ATUALIZADA para incluir o nome da turma (t.nome)
        $sql = "SELECT 
                    r.id as repasse_id, r.valor_repasse, r.data_repasse,
                    p.id as professor_id, p.nome as professor_nome,
                    a.nome as aluno_nome, a.sobrenome as aluno_sobrenome,
                    m.competencia,
                    t.nome as turma_nome -- << NOVA INFORMAÇÃO
                FROM repasses_professores r
                JOIN usuario p ON r.professor_id = p.id
                JOIN mensalidades m ON r.mensalidade_id = m.id
                JOIN turmas t ON m.turma_id = t.id -- << NOVO JOIN
                JOIN turma_alunos ta ON m.matricula_id = ta.id
                JOIN usuario a ON ta.aluno_id = a.id
                WHERE r.status = 'pago' 
                  AND m.turma_id IN (SELECT id FROM turmas WHERE arena_id = ?)
                  AND m.competencia = ?
                ORDER BY p.nome, r.data_repasse DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute([$arena_id, $competencia . '-01']);
        $repasses_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // O restante da função para agrupar continua igual...
        $repasses_agrupados = [];
        foreach ($repasses_db as $repasse) {
            $prof_id = $repasse['professor_id'];
            if (!isset($repasses_agrupados[$prof_id])) {
                $repasses_agrupados[$prof_id] = ['professor_nome' => $repasse['professor_nome'], 'total_pago' => 0, 'repasses' => []];
            }
            $repasses_agrupados[$prof_id]['repasses'][] = $repasse;
            $repasses_agrupados[$prof_id]['total_pago'] += $repasse['valor_repasse'];
        }
        return $repasses_agrupados;
    }
}
