<?php

class Ranking
{
    /**
     * Busca o ranking de MVP (Most Valuable Player) com base na média de rating ganho por partida.
     *
     * @param string|null $data_inicio A data de início do período (formato YYYY-MM-DD).
     * @param string|null $data_fim A data de fim do período (formato YYYY-MM-DD).
     * @param int|null $arena_id O ID da arena para filtrar, ou null para todas.
     * @param int $page A página atual para paginação.
     * @param int $limit O número de registros por página.
     * @return array Um array contendo 'data' com os resultados e 'total' com o número total de registros.
     */
    public static function getMvpRanking($data_inicio = null, $data_fim = null, $arena_id = null, $page = 1, $limit = 50)
    {
        $conn = Conexao::pegarConexao();
        $offset = ($page - 1) * $limit;

        $base_sql = "
            FROM historico_rating hr
            JOIN usuario u ON hr.jogador_id = u.id
        ";
        $join_sql  = "";            // será adicionado se filtrar arena
        $params = [];
        $where_clauses = [];

        // Filtro de Arena – acrescenta JOIN em partidas apenas quando necessário
        if ($arena_id) {
            $where_clauses[] = "u.id in (SELECT usuario_id from arena_membros where arena_membros.situacao in ('fundador', 'membro') and arena_id = ?)";
            $params[] = $arena_id;
        }

        // Filtro de Período
        if (!empty($data_inicio) && !empty($data_fim)) {
            $where_clauses[] = "DATE(hr.data) BETWEEN ? AND ?";
            $params[] = $data_inicio;
            $params[] = $data_fim;
        } elseif (!empty($data_inicio)) {
            $where_clauses[] = "DATE(hr.data) >= ?";
            $params[] = $data_inicio;
        } elseif (!empty($data_fim)) {
            $where_clauses[] = "DATE(hr.data) <= ?";
            $params[] = $data_fim;
        }

        $where_sql = "";
        if (!empty($where_clauses)) {
            $where_sql = " WHERE " . implode(" AND ", $where_clauses);
        }

        // Query para os dados com paginação
        $data_sql = "
            SELECT
                u.id as usuario_id, u.nome, u.apelido, u.rating,
                COUNT(hr.id) as total_partidas,
                SUM(hr.rating_novo - hr.rating_anterior) as total_rating_ganho,
                (SUM(hr.rating_novo - hr.rating_anterior) / COUNT(hr.id)) as media_rating_por_partida
            " . $base_sql . $join_sql . $where_sql . "
            GROUP BY u.id, u.nome, u.apelido, u.rating
            HAVING total_partidas > 0 AND total_rating_ganho IS NOT NULL
            ORDER BY media_rating_por_partida DESC, total_partidas DESC
            LIMIT " . intval($limit) . " OFFSET " . intval($offset) . "
        ";
        $data_params = $params;   // apenas arena_id e datas

        // Query para a contagem total de jogadores (para paginação)
        $count_sql = "
            SELECT COUNT(*) FROM (
                SELECT 1
                " . $base_sql . $join_sql . $where_sql . "
                GROUP BY hr.jogador_id
                HAVING COUNT(hr.id) > 0 AND SUM(hr.rating_novo - hr.rating_anterior) IS NOT NULL
            ) as subquery
        ";

        try {
            $stmt_data = $conn->prepare($data_sql);
            $stmt_data->execute($data_params);
            $results = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

            $stmt_count = $conn->prepare($count_sql);
            $stmt_count->execute($params);
            $total_records = $stmt_count->fetchColumn();

            return ['data' => $results, 'total' => $total_records];
        } catch (PDOException $e) {
            error_log("Erro ao buscar ranking MVP: " . $e->getMessage());
            return ['data' => [], 'total' => 0];
        }
    }
}