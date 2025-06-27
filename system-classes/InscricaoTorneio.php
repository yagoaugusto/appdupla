<?php

class InscricaoTorneio
{
    /**
     * Realiza a inscrição de uma dupla em um torneio para uma categoria específica.
     *
     * @param int $torneio_id O ID do torneio.
     * @param int $categoria_id O ID da categoria.
     * @param string $titulo_dupla O título/nome da dupla.
     * @param int $jogador1_id O ID do primeiro jogador (geralmente o usuário logado).
     * @param int $jogador2_id O ID do segundo jogador (o parceiro).
     * @return int|false O ID da inscrição recém-criada ou false em caso de falha (ex: dupla já inscrita).
     */
    public static function inscreverDupla($torneio_id, $categoria_id, $titulo_dupla, $jogador1_id, $jogador2_id)
    {
        try {
            $conn = Conexao::pegarConexao();

            // NOVO: Verifica se o jogador1 já está inscrito nesta categoria do torneio
            $stmt_check_j1 = $conn->prepare("SELECT id FROM torneio_inscricoes WHERE torneio_id = ? AND categoria_id = ? AND (jogador1_id = ? OR jogador2_id = ?)");
            $stmt_check_j1->execute([$torneio_id, $categoria_id, $jogador1_id, $jogador1_id]);
            if ($stmt_check_j1->fetch()) {
                error_log("Jogador1 (ID: $jogador1_id) já está inscrito na categoria $categoria_id do torneio $torneio_id.");
                return false; // Jogador1 já inscrito nesta categoria
            }

            // NOVO: Verifica se o jogador2 já está inscrito nesta categoria do torneio
            $stmt_check_j2 = $conn->prepare("SELECT id FROM torneio_inscricoes WHERE torneio_id = ? AND categoria_id = ? AND (jogador1_id = ? OR jogador2_id = ?)");
            $stmt_check_j2->execute([$torneio_id, $categoria_id, $jogador2_id, $jogador2_id]);
            if ($stmt_check_j2->fetch()) {
                error_log("Jogador2 (ID: $jogador2_id) já está inscrito na categoria $categoria_id do torneio $torneio_id.");
                return false; // Jogador2 já inscrito nesta categoria
            }

            $stmt = $conn->prepare("INSERT INTO torneio_inscricoes (torneio_id, categoria_id, titulo_dupla, jogador1_id, jogador2_id, data_inscricao, status) VALUES (?, ?, ?, ?, ?, NOW(), 'pendente')");
            $stmt->execute([$torneio_id, $categoria_id, $titulo_dupla, $jogador1_id, $jogador2_id]);
            return $conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao inscrever dupla no torneio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca os detalhes de uma inscrição específica, incluindo informações do torneio e categoria.
     *
     * @param int $inscricao_id O ID da inscrição.
     * @return array|false Um array associativo com os dados da inscrição ou false se não encontrada.
     */
    public static function getInscricaoById($inscricao_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("
                SELECT
                    ti.*,
                    t.titulo AS torneio_titulo,
                    t.arena AS torneio_arena_id,
                    tc.titulo AS categoria_titulo,
                    tc.genero AS categoria_genero
                FROM
                    torneio_inscricoes ti
                JOIN
                    torneios t ON ti.torneio_id = t.id
                JOIN
                    torneio_categorias tc ON ti.categoria_id = tc.id
                WHERE
                    ti.id = ?
            ");
            $stmt->execute([$inscricao_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar inscrição por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca o status de pagamento de um usuário para uma inscrição específica.
     *
     * @param int $inscricao_id O ID da inscrição.
     * @param int $usuario_id O ID do usuário.
     * @return array|false Um array associativo com o status e valor do pagamento ou false se não encontrado.
     */
    public static function getPagamentoStatus($inscricao_id, $usuario_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("SELECT status_pagamento, valor FROM torneio_pagamentos WHERE inscricao_id = ? AND usuario_id = ?");
            $stmt->execute([$inscricao_id, $usuario_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar status de pagamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca todas as duplas inscritas em um torneio, agrupadas por categoria.
     *
     * @param int $torneio_id O ID do torneio.
     * @return array Um array associativo onde a chave é o ID da categoria e o valor é um array com os detalhes da categoria e suas duplas.
     */
    public static function getDuplasInscritasByTorneio($torneio_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("
                SELECT
                    ti.id AS inscricao_id, ti.titulo_dupla, ti.categoria_id,
                    tc.titulo AS categoria_titulo, tc.genero AS categoria_genero,
                    u1.nome AS j1_nome, u1.apelido AS j1_apelido,
                    u2.nome AS j2_nome, u2.apelido AS j2_apelido
                FROM
                    torneio_inscricoes ti
                JOIN torneio_categorias tc ON ti.categoria_id = tc.id
                JOIN usuario u1 ON ti.jogador1_id = u1.id
                JOIN usuario u2 ON ti.jogador2_id = u2.id
                WHERE ti.torneio_id = ?
                ORDER BY tc.titulo ASC, ti.titulo_dupla ASC
            ");
            $stmt->execute([$torneio_id]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $grouped_duos = [];
            foreach ($results as $duo) {
                $category_key = $duo['categoria_id'];
                if (!isset($grouped_duos[$category_key])) {
                    $grouped_duos[$category_key] = [
                        'id' => $duo['categoria_id'],
                        'titulo' => $duo['categoria_titulo'],
                        'genero' => $duo['categoria_genero'],
                        'duplas' => []
                    ];
                }
                $grouped_duos[$category_key]['duplas'][] = $duo;
            }
            return $grouped_duos;
        } catch (PDOException $e) {
            error_log("Erro ao buscar duplas inscritas por torneio: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca todos os torneios em que um usuário está inscrito em alguma dupla.
     * Retorna uma única inscrição (a de menor ID) por torneio para fins de link.
     *
     * @param int $usuario_id O ID do usuário.
     * @return array Um array de torneios com detalhes e uma inscricao_id para link.
     */
    public static function getTorneiosInscritosByUserId($usuario_id, $limit = null)
    {
        try {
            $conn = Conexao::pegarConexao();

            $sql = "
            SELECT
                t.id AS torneio_id,
                t.titulo,
                t.inicio_torneio,
                t.fim_torneio,
                t.sobre,
                a.titulo AS arena_titulo,
                a.bandeira AS arena_bandeira,
                MIN(ti.id) AS inscricao_id_para_link
            FROM
                torneio_inscricoes ti
            JOIN
                torneios t ON ti.torneio_id = t.id
            JOIN
                arenas a ON t.arena = a.id
            WHERE
                ti.jogador1_id = ? OR ti.jogador2_id = ?
            GROUP BY
                t.id, t.titulo, t.inicio_torneio, t.fim_torneio, t.sobre, a.titulo, a.bandeira
            ORDER BY t.inicio_torneio DESC
        ";

            // Adiciona o LIMIT manualmente de forma segura
            if ($limit !== null) {
                $limit = (int)$limit;
                $sql .= " LIMIT $limit";
            }

            $stmt = $conn->prepare($sql);
            $stmt->execute([$usuario_id, $usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar torneios inscritos por usuário: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca todas as inscrições de um usuário em um torneio específico.
     *
     * @param int $torneio_id O ID do torneio.
     * @param int $usuario_id O ID do usuário.
     * @return array Um array de arrays associativos com os detalhes das inscrições.
     */
    public static function getInscricoesByTorneioAndUserId($torneio_id, $usuario_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("
                SELECT
                    ti.id AS inscricao_id,
                    ti.titulo_dupla,
                    ti.jogador1_id,
                    ti.jogador2_id,
                    tc.titulo AS categoria_titulo,
                    tc.genero AS categoria_genero,
                    u1.nome AS j1_nome,
                    u1.apelido AS j1_apelido,
                    u2.nome AS j2_nome,
                    u2.apelido AS j2_apelido
                FROM
                    torneio_inscricoes ti
                JOIN
                    torneio_categorias tc ON ti.categoria_id = tc.id
                JOIN
                    usuario u1 ON ti.jogador1_id = u1.id
                JOIN
                    usuario u2 ON ti.jogador2_id = u2.id
                WHERE
                    ti.torneio_id = ? AND (ti.jogador1_id = ? OR ti.jogador2_id = ?)
                ORDER BY tc.titulo ASC, ti.titulo_dupla ASC
            ");
            $stmt->execute([$torneio_id, $usuario_id, $usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar inscrições por torneio e usuário: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se um usuário já possui alguma inscrição em um torneio.
     *
     * @param int $torneio_id O ID do torneio.
     * @param int $usuario_id O ID do usuário.
     * @return bool True se o usuário já possui uma inscrição, false caso contrário.
     */
    public static function hasExistingRegistrationInTorneio($torneio_id, $usuario_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("SELECT COUNT(*) FROM torneio_inscricoes WHERE torneio_id = ? AND (jogador1_id = ? OR jogador2_id = ?)");
            $stmt->execute([$torneio_id, $usuario_id, $usuario_id]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar inscrição existente para usuário no torneio: " . $e->getMessage());
            return false; // Assume false em caso de erro para não bloquear novas inscrições indevidamente
        }
    }

    /**
     * Cria os registros de pagamento para os jogadores de uma dupla inscrita.
     *
     * @param int $inscricao_id O ID da inscrição recém-criada.
     * @param int $jogador1_id O ID do primeiro jogador.
     * @param int $jogador2_id O ID do segundo jogador.
     * @param float $valor_primeira_insc O valor da primeira inscrição (para o jogador 1).
     * @param float $valor_segunda_insc O valor da segunda inscrição (para o jogador 2).
     * @return bool True se os registros foram criados com sucesso, false caso contrário.
     */
    public static function criarRegistrosPagamento($inscricao_id, $jogador1_id, $jogador2_id, $valor_primeira_insc, $valor_segunda_insc)
    {
        try {
            $conn = Conexao::pegarConexao();
            $conn->beginTransaction();

            // Registro para o jogador 1
            $stmt1 = $conn->prepare("INSERT INTO torneio_pagamentos (inscricao_id, usuario_id, valor, status_pagamento) VALUES (?, ?, ?, 'pendente')");
            $stmt1->execute([$inscricao_id, $jogador1_id, $valor_primeira_insc]);

            // Registro para o jogador 2
            $stmt2 = $conn->prepare("INSERT INTO torneio_pagamentos (inscricao_id, usuario_id, valor, status_pagamento) VALUES (?, ?, ?, 'pendente')");
            $stmt2->execute([$inscricao_id, $jogador2_id, $valor_segunda_insc]);

            $conn->commit();
            return true;
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Erro ao criar registros de pagamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o status de pagamento de um registro específico.
     *
     * @param int $inscricao_id O ID da inscrição.
     * @param int $usuario_id O ID do usuário cujo pagamento será atualizado.
     * @param string $novo_status O novo status de pagamento ('pendente', 'pago', 'cancelado').
     * @param string|null $mp_payment_id O ID do pagamento do Mercado Pago (opcional).
     * @return bool True se a atualização foi bem-sucedida, false caso contrário.
     */
    public static function updatePagamentoStatus($inscricao_id, $usuario_id, $novo_status, $mp_payment_id = null)
    {
        try {
            $conn = Conexao::pegarConexao();
            $sql = "UPDATE torneio_pagamentos SET status_pagamento = ?, data_pagamento = NOW()";
            $params = [$novo_status];
            if ($mp_payment_id !== null) {
                $sql .= ", mp_payment_id = ?";
                $params[] = $mp_payment_id;
            }
            $sql .= " WHERE inscricao_id = ? AND usuario_id = ?";
            $params[] = $inscricao_id;
            $params[] = $usuario_id;
            $stmt = $conn->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar status de pagamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica se todos os pagamentos de uma inscrição foram confirmados.
     *
     * @param int $inscricao_id O ID da inscrição.
     * @return bool True se todos os pagamentos estiverem 'pago', false caso contrário.
     */
    public static function areAllPaymentsConfirmed($inscricao_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            // Conta quantos pagamentos para esta inscrição NÃO estão com status 'pago'.
            $stmt = $conn->prepare("SELECT COUNT(*) FROM torneio_pagamentos WHERE inscricao_id = ? AND status_pagamento != 'pago'");
            $stmt->execute([$inscricao_id]);
            // Se a contagem for 0, significa que todos estão pagos.
            return $stmt->fetchColumn() == 0;
        } catch (PDOException $e) {
            error_log("Erro ao verificar pagamentos da inscrição: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o status de uma inscrição na tabela torneio_inscricoes.
     *
     * @param int $inscricao_id O ID da inscrição.
     * @param string $novo_status O novo status ('pendente', 'confirmada', 'cancelada').
     * @return bool True se a atualização foi bem-sucedida, false caso contrário.
     */
    public static function updateInscricaoStatus($inscricao_id, $novo_status)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("UPDATE torneio_inscricoes SET status = ? WHERE id = ?");
            return $stmt->execute([$novo_status, $inscricao_id]);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar status da inscrição: " . $e->getMessage());
            return false;
        }
    }
}
