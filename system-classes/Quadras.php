<?php


class Quadras {
    /**
     * Retorna todas as quadras de uma arena específica.
     *
     * @param int $arena_id O ID da arena.
     * @return array Lista de quadras como arrays associativos.
     */
    public static function getQuadrasPorArena($arena_id) {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("SELECT * FROM quadras WHERE arena_id = ?");
            $stmt->execute([$arena_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar quadras da arena: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna todas as arenas onde o usuário é fundador.
     *
     * @param int $usuario_id O ID do usuário.
     * @return array Lista de arenas como arrays associativos.
     */
    public static function getArenasDoGestor($usuario_id) {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("SELECT a.* FROM arenas a
                JOIN arena_membros am ON am.arena_id = a.id
                WHERE am.usuario_id = ? AND am.situacao = 'fundador'");
            $stmt->execute([$usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar arenas do gestor: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cria uma nova quadra vinculada a uma arena.
     *
     * @param int $arena_id ID da arena.
     * @param string $nome Nome da quadra.
     * @param float $valor_base Valor base da quadra.
     * @param int $beach_tennis 1 se aceita beach tennis, 0 se não.
     * @param int $volei 1 se aceita vôlei, 0 se não.
     * @param int $futvolei 1 se aceita futevôlei, 0 se não.
     * @return bool True se cadastrou com sucesso, false se falhou.
     */
    public static function criarQuadra($arena_id, $nome, $valor_base, $beach_tennis, $volei, $futvolei) {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("INSERT INTO quadras (arena_id, nome, valor_base, beach_tennis, volei, futvolei) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([
                $arena_id,
                $nome,
                $valor_base,
                $beach_tennis,
                $volei,
                $futvolei
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao criar quadra: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza os dados de uma quadra existente.
     *
     * @param int $quadra_id ID da quadra a ser atualizada.
     * @param string $nome Novo nome da quadra.
     * @param float $valor_base Novo valor base.
     * @param int $beach_tennis Novo status para beach tennis.
     * @param int $volei Novo status para vôlei.
     * @param int $futvolei Novo status para futevôlei.
     * @return bool True se a atualização foi bem-sucedida, false caso contrário.
     */
    public static function updateQuadra($quadra_id, $nome, $valor_base, $beach_tennis, $volei, $futvolei) {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare(
                "UPDATE quadras SET 
                    nome = ?, 
                    valor_base = ?, 
                    beach_tennis = ?, 
                    volei = ?, 
                    futvolei = ? 
                WHERE id = ?"
            );
            $stmt->execute([$nome, $valor_base, $beach_tennis, $volei, $futvolei, $quadra_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar quadra: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca uma quadra pelo ID.
     *
     * @param int $quadra_id O ID da quadra.
     * @return array|false Um array associativo com os dados da quadra, ou false se não encontrada.
     */
    public static function getQuadraById($quadra_id) {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("SELECT * FROM quadras WHERE id = ?");
            $stmt->execute([$quadra_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar quadra por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna todos os horários de funcionamento de uma quadra específica.
     *
     * @param int $quadra_id O ID da quadra.
     * @return array Lista de horários de funcionamento como arrays associativos.
     */
    public static function getFuncionamentoQuadra($quadra_id) {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("SELECT dia_semana, hora_inicio, hora_fim, intervalo, valor_adicional FROM quadras_funcionamento WHERE quadra_id = ? ORDER BY dia_semana, hora_inicio");
            $stmt->execute([$quadra_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar funcionamento da quadra: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Limpa os horários de funcionamento de uma quadra para um dia específico.
     *
     * Se $dia_semana for null, limpa todos os horários de funcionamento da quadra.
     *
     * @param int $quadra_id O ID da quadra.
     * @param string|null $dia_semana O dia da semana (ex: 'segunda', 'terca') ou null para todos os dias.
     * @return bool True em caso de sucesso, false caso contrário.
     */
    public static function clearFuncionamentoQuadra($quadra_id, $dia_semana = null) {
        try {
            $conn = Conexao::pegarConexao();
            $sql = "DELETE FROM quadras_funcionamento WHERE quadra_id = ?";
            $params = [$quadra_id];
            if ($dia_semana !== null) {
                $sql .= " AND dia_semana = ?";
                $params[] = $dia_semana;
            }
            $stmt = $conn->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao limpar funcionamento da quadra: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Adiciona um novo horário de funcionamento para uma quadra.
     *
     * @param int $quadra_id ID da quadra.
     * @param string $dia_semana Dia da semana.
     * @param string $hora_inicio Hora de início (HH:MM:SS).
     * @param string $hora_fim Hora de fim (HH:MM:SS).
     * @param int $intervalo Intervalo em minutos (padrão: 60).
     * @param float $valor_adicional Valor adicional para este slot (padrão: 0).
     * @return bool True em caso de sucesso, false caso contrário.
     */
    public static function addFuncionamentoQuadra($quadra_id, $dia_semana, $hora_inicio, $hora_fim, $intervalo = 60, $valor_adicional = 0) {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("INSERT INTO quadras_funcionamento (quadra_id, dia_semana, hora_inicio, hora_fim, intervalo, valor_adicional) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$quadra_id, $dia_semana, $hora_inicio, $hora_fim, $intervalo, $valor_adicional]);
        } catch (PDOException $e) {
            error_log("Erro ao adicionar funcionamento da quadra: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca o valor adicional para um slot de horário específico.
     *
     * @param int $quadra_id O ID da quadra.
     * @param string $dia_semana O dia da semana (ex: 'segunda').
     * @param string $hora_inicio A hora de início (ex: '08:00').
     * @return float O valor adicional, ou 0 se não encontrado.
     */
    public static function getValorAdicionalPorSlot($quadra_id, $dia_semana, $hora_inicio) {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("SELECT valor_adicional FROM quadras_funcionamento WHERE quadra_id = ? AND dia_semana = ? AND hora_inicio = ?");
            $stmt->execute([$quadra_id, $dia_semana, $hora_inicio . ':00']);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (float)$result['valor_adicional'] : 0.0;
        } catch (PDOException $e) {
            error_log("Erro ao buscar valor adicional do slot: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Busca os horários de funcionamento para múltiplas quadras em um dia específico.
     *
     * @param array $quadra_ids Array com os IDs das quadras.
     * @param string $dia_semana O dia da semana (ex: 'segunda').
     * @return array Lista de horários de funcionamento.
     */
    public static function getFuncionamentoMultiplasQuadrasPorDia(array $quadra_ids, string $dia_semana) {
        if (empty($quadra_ids)) {
            return [];
        }
        try {
            $conn = Conexao::pegarConexao();
            $placeholders = implode(',', array_fill(0, count($quadra_ids), '?'));
            $sql = "SELECT quadra_id, hora_inicio FROM quadras_funcionamento WHERE quadra_id IN ($placeholders) AND dia_semana = ?";
            $params = array_merge($quadra_ids, [$dia_semana]);
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar funcionamento de múltiplas quadras: " . $e->getMessage());
            return [];
        }
    }

     /**
     * Busca os slots de horários disponíveis para uma quadra em um dia específico.
     *
     * @param int $quadra_id O ID da quadra.
     * @param string $data A data no formato YYYY-MM-DD.
     * @return array Um array de slots de horários disponíveis (HH:mm).
     */
    public static function getSlotsDisponiveis($quadra_id, $data) {
        try {
            $conn = Conexao::pegarConexao();

            // 1. Buscar horários de funcionamento da quadra para o dia da semana
            $dia_semana = date('l', strtotime($data)); // Retorna o dia da semana em inglês (e.g., Monday)
            $dias_semana_map = [
                'Monday' => 'segunda',
                'Tuesday' => 'terca',
                'Wednesday' => 'quarta',
                'Thursday' => 'quinta',
                'Friday' => 'sexta',
                'Saturday' => 'sabado',
                'Sunday' => 'domingo'
            ];
            $dia_semana_pt = $dias_semana_map[$dia_semana] ?? null;

            if (!$dia_semana_pt) {
                return []; // Dia de semana inválido
            }

            $stmt_funcionamento = $conn->prepare("SELECT hora_inicio, hora_fim, intervalo FROM quadras_funcionamento WHERE quadra_id = ? AND dia_semana = ?");
            $stmt_funcionamento->execute([$quadra_id, $dia_semana_pt]);
            $funcionamento = $stmt_funcionamento->fetchAll(PDO::FETCH_ASSOC);

            if (empty($funcionamento)) {
                return []; // Quadra sem horários de funcionamento para este dia
            }

            // 2. Gerar todos os slots possíveis com base no funcionamento
            $slots_possiveis = [];
            foreach ($funcionamento as $f) {
                $hora_inicio = strtotime($f['hora_inicio']);
                $hora_fim = strtotime($f['hora_fim']);
                $intervalo = $f['intervalo'];

                while ($hora_inicio < $hora_fim) {
                    $slot_inicio = date('H:i', $hora_inicio);
                    $slots_possiveis[] = $slot_inicio;
                    $hora_inicio = strtotime("+" . $intervalo . " minutes", $hora_inicio);
                }
            }

            // 3. Buscar reservas existentes para a quadra na data
            // MODIFICADO: Formata a hora para 'HH:MM' diretamente na consulta SQL
            $stmt_reservas = $conn->prepare("SELECT DATE_FORMAT(hora_inicio, '%H:%i') as hora_inicio FROM agenda_quadras WHERE quadra_id = ? AND data = ?");
            $stmt_reservas->execute([$quadra_id, $data]);
            $reservas = $stmt_reservas->fetchAll(PDO::FETCH_COLUMN); // Apenas as horas de início reservadas

            // 4. Determinar slots disponíveis (removendo reservas dos slots possíveis)
            // AGORA FUNCIONA: A comparação será entre strings no mesmo formato (ex: '08:00' vs '08:00')
            $slots_disponiveis = array_diff($slots_possiveis, $reservas);

            return $slots_disponiveis;

        } catch (PDOException $e) {
            error_log("Erro ao buscar slots disponíveis: " . $e->getMessage());
            return [];
        }
    }

public static function getSlotsReservados($quadra_id, $data) {
    try {
        $conn = Conexao::pegarConexao();
        // MODIFICADO: Formata a hora para 'HH:MM' para consistência
        $stmt = $conn->prepare("SELECT DATE_FORMAT(hora_inicio, '%H:%i') as hora_inicio FROM agenda_quadras WHERE quadra_id = ? AND data = ?");
        $stmt->execute([$quadra_id, $data]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN); // Apenas horas reservadas
    } catch (PDOException $e) {
        error_log("Erro ao buscar slots reservados: " . $e->getMessage());
        return [];
    }
}


}