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
     * NOVO: Busca todos os alunos matriculados em uma turma.
     */
    public static function getAlunosDaTurma(int $turma_id)
    {
        $conn = Conexao::pegarConexao();
        $sql = "SELECT 
                    u.id, u.nome, u.sobrenome, u.apelido, u.telefone,
                    ta.status, ta.data_matricula, ta.id as matricula_id
                FROM turma_alunos ta
                JOIN usuario u ON ta.aluno_id = u.id
                WHERE ta.turma_id = ?
                ORDER BY u.nome ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$turma_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * NOVO MÉTODO UNIFICADO: Matricula um aluno e já gera a sua primeira cobrança
     * (mensal, trimestral, etc.) com status 'pendente'.
     * @param array $dados Contém turma_id, aluno_id, plano, data_inicio, etc.
     * @return bool
     */
    public static function matricularAlunoComPlanoInicial(array $dados)
    {
        $conn = Conexao::pegarConexao();
        try {
            $conn->beginTransaction();

            // 1. Cria o registo da matrícula na tabela turma_alunos
            $stmt_matricula = $conn->prepare(
                "INSERT INTO turma_alunos (turma_id, aluno_id, data_matricula) VALUES (?, ?, NOW())"
            );
            $stmt_matricula->execute([$dados['turma_id'], $dados['aluno_id']]);
            $matricula_id = $conn->lastInsertId();

            if (!$matricula_id) {
                // Se o aluno já estiver matriculado, a inserção pode falhar devido à chave única.
                // Neste caso, encontramos a matrícula existente.
                $stmt_find = $conn->prepare("SELECT id FROM turma_alunos WHERE turma_id = ? AND aluno_id = ?");
                $stmt_find->execute([$dados['turma_id'], $dados['aluno_id']]);
                $matricula_id = $stmt_find->fetchColumn();
                if (!$matricula_id) throw new Exception("Não foi possível criar ou encontrar a matrícula do aluno.");
            }

            // 2. Determina o número de mensalidades a gerar com base no plano
            $numero_meses = 1;
            if ($dados['plano'] == 'trimestral') $numero_meses = 3;
            if ($dados['plano'] == 'semestral') $numero_meses = 6;

            // 3. Prepara-se para inserir as mensalidades pendentes
            $stmt_mensalidade = $conn->prepare(
                "INSERT INTO mensalidades (matricula_id, aluno_id, turma_id, competencia, data_vencimento, valor, status)
                 VALUES (?, ?, ?, ?, ?, ?, 'pendente')"
            );

            // Define a data de início para a primeira cobrança
            $data_base = new DateTime($dados['data_inicio_competencia'] . '-01');
            $dia_vencimento_base = date('d'); // Vencimento no dia da matrícula

            for ($i = 0; $i < $numero_meses; $i++) {
                $competencia_atual_str = $data_base->format('Y-m-d');

                // Lógica de vencimento inteligente
                $dias_no_mes_competencia = $data_base->format('t');
                $dia_vencimento = min($dia_vencimento_base, $dias_no_mes_competencia);
                $vencimento_str = $data_base->format('Y-m-') . str_pad($dia_vencimento, 2, '0', STR_PAD_LEFT);

                $stmt_mensalidade->execute([
                    $matricula_id,
                    $dados['aluno_id'],
                    $dados['turma_id'],
                    $competencia_atual_str,
                    $vencimento_str,
                    $dados['valor_mensalidade']
                ]);

                // Avança a data base para o próximo mês
                $data_base->modify('first day of next month');
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Erro ao matricular aluno com plano inicial: " . $e->getMessage());
            return false;
        }
    }

    /**
     * MÉTODO ANTIGO - Pode ser removido ou mantido para referência.
     * Nós não o usaremos mais no novo fluxo.
     */
    // public static function matricularAluno(int $turma_id, int $aluno_id) { ... }



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
     * NOVO: Busca o histórico de mensalidades de uma turma.
     * @param int $turma_id
     * @return array
     */
    public static function getMensalidadesDaTurma(int $turma_id)
    {
        $conn = Conexao::pegarConexao();
        $sql = "SELECT 
                    m.*,
                    u.nome as aluno_nome,
                    u.sobrenome as aluno_sobrenome
                FROM mensalidades m
                JOIN usuario u ON m.aluno_id = u.id
                WHERE m.turma_id = ?
                ORDER BY m.competencia DESC, u.nome ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$turma_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Regista um pagamento de plano (mensal, trimestral, etc.) para um aluno.
     * VERSÃO FINAL: Usa INSERT...ON DUPLICATE KEY UPDATE para ser atómico e seguro.
     * @param array $dados - Contém todos os dados do pagamento.
     * @return bool
     */
    public static function registrarPagamentoPlano(array $dados)
    {
        $conn = Conexao::pegarConexao();
        try {
            $conn->beginTransaction();

            $numero_meses = 1;
            if ($dados['plano'] == 'trimestral') $numero_meses = 3;
            if ($dados['plano'] == 'semestral') $numero_meses = 6;

            $plano_pagamento_id = time();

            // Lógica para determinar a data de início da primeira competência (mantida)
            if (!empty($dados['data_inicio_plano'])) {
                $data_base = new DateTime($dados['data_inicio_plano'] . '-01');
            } else {
                $stmt_last = $conn->prepare(
                    "SELECT MAX(competencia) as ultima_competencia FROM mensalidades WHERE matricula_id = ? AND (status = 'paga' OR status = 'pendente' OR status = 'vencida')"
                );
                $stmt_last->execute([$dados['matricula_id']]);
                $ultima_competencia = $stmt_last->fetchColumn();

                if ($ultima_competencia) {
                    $data_base = new DateTime($ultima_competencia);
                    $data_base->modify('first day of next month');
                } else {
                    $data_base = new DateTime($dados['data_matricula']);
                    $data_base->modify('first day of this month');
                }
            }

            // =================================================================
            // A NOVA LÓGICA COM A QUERY INTELIGENTE
            // =================================================================
            $stmt_upsert = $conn->prepare(
                "INSERT INTO mensalidades 
                    (matricula_id, aluno_id, turma_id, competencia, data_vencimento, valor, status, data_pagamento, forma_pagamento, plano_pagamento_id)
                 VALUES 
                    (?, ?, ?, ?, ?, ?, 'paga', NOW(), ?, ?)
                 ON DUPLICATE KEY UPDATE
                    status = 'paga', 
                    data_pagamento = NOW(), 
                    forma_pagamento = VALUES(forma_pagamento), 
                    plano_pagamento_id = VALUES(plano_pagamento_id),
                    valor = VALUES(valor)"
            );

            $valor_por_mes = $dados['valor_mensal'] / $numero_meses;
            $dia_vencimento_base = date('d', strtotime($dados['data_matricula']));

            for ($i = 0; $i < $numero_meses; $i++) {
                $competencia_atual_str = $data_base->format('Y-m-d');

                $dias_no_mes_competencia = $data_base->format('t');
                $dia_vencimento = min($dia_vencimento_base, $dias_no_mes_competencia);
                $vencimento_str = $data_base->format('Y-m-') . str_pad($dia_vencimento, 2, '0', STR_PAD_LEFT);

                $stmt_upsert->execute([
                    $dados['matricula_id'],
                    $dados['aluno_id'],
                    $dados['turma_id'],
                    $competencia_atual_str,
                    $vencimento_str,
                    $valor_por_mes,
                    $dados['forma_pagamento'],
                    $plano_pagamento_id
                ]);

                $data_base->modify('first day of next month');
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Erro ao registar pagamento de plano: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Script para ser executado via Cron Job para gerar mensalidades pendentes.
     * VERSÃO ATUALIZADA: Vencimento baseado no dia da matrícula.
     * @return void
     */
    public static function scriptGerarMensalidadesPendentes()
    {
        $conn = Conexao::pegarConexao();
        $sql_ativos = "SELECT ta.id, ta.aluno_id, ta.turma_id, ta.data_matricula, t.valor_mensalidade
                       FROM turma_alunos ta
                       JOIN turmas t ON ta.turma_id = t.id
                       WHERE ta.status = 'ativo'";
        $stmt_ativos = $conn->query($sql_ativos);

        $hoje = new DateTime();
        $competencia_atual_str = $hoje->format('Y-m-01');

        $stmt_check = $conn->prepare("SELECT id FROM mensalidades WHERE matricula_id = ? AND competencia = ?");
        $stmt_insert = $conn->prepare(
            "INSERT INTO mensalidades (matricula_id, aluno_id, turma_id, competencia, data_vencimento, valor)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        while ($matricula = $stmt_ativos->fetch(PDO::FETCH_ASSOC)) {
            $stmt_check->execute([$matricula['id'], $competencia_atual_str]);
            if ($stmt_check->fetch()) {
                continue;
            }

            if (new DateTime($matricula['data_matricula']) > new DateTime($competencia_atual_str)) {
                continue;
            }

            // === AQUI ESTÁ A NOVA LÓGICA DE VENCIMENTO (também para o Cron) ===
            $dia_vencimento_base = date('d', strtotime($matricula['data_matricula']));
            $dias_no_mes_competencia = $hoje->format('t');
            $dia_vencimento = min($dia_vencimento_base, $dias_no_mes_competencia);
            $data_vencimento = $hoje->format('Y-m-') . str_pad($dia_vencimento, 2, '0', STR_PAD_LEFT);
            // ===================================================================

            $stmt_insert->execute([
                $matricula['id'],
                $matricula['aluno_id'],
                $matricula['turma_id'],
                $competencia_atual_str,
                $data_vencimento, // Usa a nova data calculada
                $matricula['valor_mensalidade']
            ]);
            echo "Gerada mensalidade para matrícula ID: " . $matricula['id'] . "\n";
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
        while($row = $stmt_stats3->fetch(PDO::FETCH_ASSOC)) {
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
        foreach($mensalidades_db->fetchAll(PDO::FETCH_ASSOC) as $m) {
            $mensalidades_por_aluno[$m['matricula_id']][] = $m;
        }

        // Organizamos os alunos por turma
        $alunos_por_turma = [];
        foreach($alunos_db->fetchAll(PDO::FETCH_ASSOC) as $a) {
            // Para cada aluno, já anexamos as suas mensalidades
            $a['mensalidades'] = $mensalidades_por_aluno[$a['matricula_id']] ?? [];
            $alunos_por_turma[$a['turma_id']][] = $a;
        }
        
        // Finalmente, juntamos tudo no array de resultado
        foreach($turmas_db as $turma) {
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
            'faturamento_mes' => 0, 'a_receber_mes' => 0, 'vencido_total' => 0,
            'total_alunos_ativos' => 0, 'total_vagas' => 0
        ];
        $alunos_contados = [];

        foreach ($dados_brutos as $row) {
            if (!$row['aluno_id']) continue; // Pula turmas sem alunos

            // Estrutura do Professor
            if (!isset($professores[$row['professor_id']])) {
                $professores[$row['professor_id']] = [
                    'nome' => $row['professor_nome'], 'faturamento' => 0, 'a_receber' => 0, 'vencido' => 0, 'alunos' => []
                ];
            }
            // Estrutura da Turma
            if (!isset($turmas[$row['turma_id']])) {
                $turmas[$row['turma_id']] = [
                    'nome' => $row['turma_nome'], 'professor_nome' => $row['professor_nome'], 'vagas_total' => $row['vagas_total'],
                    'faturamento' => 0, 'a_receber' => 0, 'vencido' => 0, 'alunos' => []
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
        $stmt_novas_mat->execute([$competencia.'%', $arena_id]);
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

    
}
