<?php

class Torneio
{
    /**
     * Busca os detalhes de um torneio específico, juntando informações da arena e do fundador.
     * @param int $torneio_id O ID do torneio a ser buscado.
     * @return array|false Retorna um array com os dados do torneio ou false se não for encontrado.
     */
    public static function getTorneioById($torneio_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare(
                "SELECT t.*, a.titulo AS arena_titulo
                 FROM torneios t
                 JOIN arenas a ON t.arena = a.id
                 WHERE t.id = :torneio_id"
            );
            $stmt->bindParam(':torneio_id', $torneio_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar torneio por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca os torneios mais recentes.
     * @param int $limit O número máximo de torneios a serem retornados.
     * @return array Retorna um array de arrays associativos com os dados dos torneios.
     */
    public static function getRecentTorneios($limit = 20)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare(
                "SELECT t.id, t.titulo, t.sobre, t.inicio_inscricao, t.fim_inscricao, t.inicio_torneio, t.fim_torneio, a.titulo AS arena_titulo
                 FROM torneios t
                 JOIN arenas a ON t.arena = a.id
                 ORDER BY t.criado_em DESC LIMIT :limit"
            );
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar torneios recentes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca torneios com base em um termo de busca e filtro de status.
     * @param string $searchTerm Termo para buscar no título do torneio ou da arena.
     * @param string $statusFilter Filtro de status ('todos', 'aberto', 'andamento', 'finalizado').
     * @param int $limit O número máximo de torneios a serem retornados.
     * @return array Retorna um array de arrays associativos com os dados dos torneios.
     */
    public static function searchTorneios($searchTerm = '', $statusFilter = 'todos', $limit = 20)
    {
        try {
            $conn = Conexao::pegarConexao();
            $sql = "
                SELECT t.id, t.titulo, t.sobre, t.inicio_inscricao, t.fim_inscricao, t.inicio_torneio, t.fim_torneio, a.titulo AS arena_titulo
                FROM torneios t
                JOIN arenas a ON t.arena = a.id
                WHERE 1=1
            ";
            $params = [];

            if (!empty($searchTerm)) {
                $sql .= " AND (t.titulo LIKE :searchTerm OR a.titulo LIKE :searchTerm)";
                $params[':searchTerm'] = '%' . $searchTerm . '%';
            }

            $agora = new DateTime();
            $agora_str = $agora->format('Y-m-d H:i:s');

            if ($statusFilter === 'aberto') {
                $sql .= " AND t.inicio_inscricao <= :agora AND t.fim_inscricao >= :agora";
                $params[':agora'] = $agora_str;
            } elseif ($statusFilter === 'andamento') {
                $sql .= " AND t.inicio_torneio <= :agora AND t.fim_torneio >= :agora";
                $params[':agora'] = $agora_str;
            } elseif ($statusFilter === 'finalizado') {
                $sql .= " AND t.fim_torneio < :agora";
                $params[':agora'] = $agora_str;
            }

            $stmt = $conn->prepare($sql . " ORDER BY t.criado_em DESC LIMIT :limit");
            foreach ($params as $key => &$val) { $stmt->bindParam($key, $val); }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar torneios: " . $e->getMessage());
            return [];
        }
    }
}