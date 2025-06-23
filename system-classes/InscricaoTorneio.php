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

            // Verifica se a dupla já está inscrita nesta categoria do torneio
            // Considera ambas as ordens dos jogadores para evitar duplicidade
            $stmt_check = $conn->prepare("SELECT id FROM torneio_inscricoes WHERE torneio_id = ? AND categoria_id = ? AND ((jogador1_id = ? AND jogador2_id = ?) OR (jogador1_id = ? AND jogador2_id = ?))");
            $stmt_check->execute([$torneio_id, $categoria_id, $jogador1_id, $jogador2_id, $jogador2_id, $jogador1_id]);
            if ($stmt_check->fetch()) {
                error_log("Tentativa de inscrição duplicada para torneio_id: $torneio_id, categoria_id: $categoria_id, dupla: $jogador1_id-$jogador2_id");
                return false; // Dupla já inscrita
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
     * @return bool True se a atualização foi bem-sucedida, false caso contrário.
     */
    public static function updatePagamentoStatus($inscricao_id, $usuario_id, $novo_status)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("UPDATE torneio_pagamentos SET status_pagamento = ?, data_pagamento = NOW() WHERE inscricao_id = ? AND usuario_id = ?");
            $stmt->execute([$novo_status, $inscricao_id, $usuario_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao atualizar status de pagamento: " . $e->getMessage());
            return false;
        }
    }
}
?>