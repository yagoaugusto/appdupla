<?php

class Ranking
{
    /**
     * Busca o ranking de MVP (Most Valuable Player) baseado na média de rating ganho por partida.
     *
     * @param string $period O período do filtro ('hoje', 'semana', 'mes', 'sempre').
     * @param int|null $arena_id O ID da arena para filtrar, ou null para todas.
     * @param int $page A página atual para paginação.
     * @param int $limit O número de registros por página.
     * @return array Um array contendo 'data' com os resultados e 'total' com o número total de registros.
     */
    public static function getMvpRanking($period, $arena_id = null, $page = 1, $limit = 50)
    {
        $conn = Conexao::pegarConexao();
        $offset = ($page - 1) * $limit;

        // NOTA: Esta query assume que a tabela `historico_rating` possui uma coluna `partida_id`
        // e que a tabela `partidas` possui uma coluna `quadra_id` que se relaciona com a tabela `quadras`.
        // Se a estrutura for diferente, o JOIN para o filtro de arena precisará ser ajustado.

        $base_sql = "
            FROM historico_rating hr
            JOIN usuario u ON hr.jogador_id = u.id
        ";
        $join_sql = "";
        $where_clauses = [];
        $params = [];

        // Filtro de Arena
        if ($arena_id) {
            $join_sql = "
                JOIN partidas p ON hr.partida_id = p.id
                JOIN quadras q ON p.quadra_id = q.id
            ";
            $where_clauses[] = "q.arena_id = ?";
            $params[] = $arena_id;
        }

        // Filtro de Período
        switch ($period) {
            case 'hoje':
                $where_clauses[] = "DATE(hr.data) = CURDATE()";
                break;
            case 'semana':
                $where_clauses[] = "hr.data >= CURDATE() - INTERVAL 6 DAY";
                break;
            case 'mes':
                $where_clauses[] = "YEAR(hr.data) = YEAR(CURDATE()) AND MONTH(hr.data) = MONTH(CURDATE())";
                break;
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
                SUM(hr.rating_ganho) as total_rating_ganho,
                (SUM(hr.rating_ganho) / COUNT(hr.id)) as media_rating_por_partida
            " . $base_sql . $join_sql . $where_sql . "
            GROUP BY u.id, u.nome, u.apelido, u.rating
            HAVING total_partidas > 0
            ORDER BY media_rating_por_partida DESC, total_partidas DESC
            LIMIT ? OFFSET ?
        ";
        $data_params = array_merge($params, [$limit, $offset]);

        // Query para a contagem total de jogadores (para paginação)
        $count_sql = "
            SELECT COUNT(DISTINCT hr.jogador_id)
            " . $base_sql . $join_sql . $where_sql;

        try {
            $stmt_data = $conn->prepare($data_sql);
            // PDO bindValue/bindParam starts at 1
            for ($i = 0; $i < count($data_params); $i++) {
                $stmt_data->bindValue($i + 1, $data_params[$i]);
            }
            $stmt_data->execute();
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