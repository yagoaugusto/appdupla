<?php

class Arena
{
    /**
     * Cria uma nova arena no banco de dados.
     *
     * @param string $titulo O nome da arena.
     * @param string $lema O lema ou slogan da arena.
     * @param string $bandeira O emoji que representa a arena.
     * @param string $privacidade A visibilidade da arena ('publica' ou 'privada').
     * @param int $fundador_id O ID do usuário fundador da arena.
     * @return bool Retorna true em caso de sucesso.
     * @throws PDOException Se ocorrer um erro durante a operação no banco de dados.
     */
    public static function criarArena($titulo, $lema, $bandeira, $privacidade, $fundador_id)
    {
        $conn = Conexao::pegarConexao();

        $stmt = $conn->prepare("INSERT INTO arenas (titulo, lema, bandeira, privacidade, fundador) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$titulo, $lema, $bandeira, $privacidade, $fundador_id]);
        return $conn->lastInsertId();
    }

    /**
     * Adiciona um membro à arena.
     *
     * Se o membro já existe (baseado em arena_id e usuario_id), atualiza sua situação.
     * Caso contrário, insere um novo registro.
     *
     * @param int $arena_id O ID da arena.
     * @param int $usuario_id O ID do usuário a ser adicionado.
     * @param string $situacao A situação do membro ('convidado', 'solicitado', 'membro', 'fundador').
     * @return bool Retorna true em caso de sucesso.
     * @throws PDOException Se ocorrer um erro durante a operação no banco de dados.
     */
    public static function adicionarMembro($arena_id, $usuario_id, $situacao = 'convidado')
    {
        $conn = Conexao::pegarConexao();

        $stmt = $conn->prepare("
            INSERT INTO arena_membros (arena_id, usuario_id, situacao, data_entrada)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                situacao = VALUES(situacao),
                data_entrada = NOW()
        ");
        return $stmt->execute([$arena_id, $usuario_id, $situacao]);
    }

    /**
     * Retorna os detalhes de uma arena pelo seu ID.
     *
     * @param int $arena_id O ID da arena.
     * @return array|false Retorna um array associativo com os detalhes da arena ou false se não encontrada.
     * @throws PDOException Se ocorrer um erro durante a operação no banco de dados.
     */
    public static function getArenaById($arena_id)
    {
        $conn = Conexao::pegarConexao();

        $stmt = $conn->prepare("SELECT a.*, u.nome AS fundador_nome, u.apelido AS fundador_apelido FROM arenas a JOIN usuario u ON a.fundador = u.id WHERE a.id = ?");
        $stmt->execute([$arena_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna a lista de membros de uma arena.
     *
     * @param int $arena_id O ID da arena.
     * @param array $situacoes Opcional. Um array de situações para filtrar os membros (ex: ['convidado', 'solicitado']).
     * @return array Retorna um array de arrays associativos com os detalhes dos membros.
     * @throws PDOException Se ocorrer um erro durante a operação no banco de dados.
     */
    public static function getMembersByArenaId($arena_id, array $situacoes = [])
    {
        $conn = Conexao::pegarConexao();

        $sql = "
            SELECT am.usuario_id, am.situacao, u.nome, u.apelido
            FROM arena_membros am
            JOIN usuario u ON am.usuario_id = u.id
            WHERE am.arena_id = ?
        ";
        $params = [$arena_id];

        if (!empty($situacoes)) {
            $placeholders = implode(',', array_fill(0, count($situacoes), '?'));
            $sql .= " AND am.situacao IN ($placeholders)";
            $params = array_merge($params, $situacoes);
        }

        $sql .= " ORDER BY am.situacao DESC, u.nome ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna o ranking dos jogadores que são membros de uma arena, baseado no rating geral.
     *
     * @param int $arena_id O ID da arena.
     * @return array Retorna um array de arrays associativos com o ranking dos jogadores.
     * @throws PDOException Se ocorrer um erro durante a operação no banco de dados.
     */
    public static function getRankingByArenaId($arena_id)
    {
        $conn = Conexao::pegarConexao();

        // Este ranking é baseado no rating geral do usuário, não em um rating específico da arena.
        // Se um rating específico da arena for necessário, uma nova tabela/lógica seria exigida.
        $stmt = $conn->prepare("
            SELECT u.id, u.nome, u.apelido, u.rating
            FROM arena_membros am
            JOIN usuario u ON am.usuario_id = u.id
            WHERE am.arena_id = ? AND am.situacao IN ('membro', 'fundador')
            ORDER BY u.rating DESC
        ");
        $stmt->execute([$arena_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atualiza os dados de uma arena existente.
     *
     * @param int $arena_id O ID da arena a ser atualizada.
     * @param string $titulo O novo título da arena.
     * @param string $privacidade A nova visibilidade da arena ('publica' ou 'privada').
     * @return bool Retorna true em caso de sucesso.
     * @throws PDOException Se ocorrer um erro durante a operação no banco de dados.
     */
    public static function updateArena($arena_id, $titulo, $privacidade)
    {
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("UPDATE arenas SET titulo = ?, privacidade = ? WHERE id = ?");
        return $stmt->execute([$titulo, $privacidade, $arena_id]);
    }

    /**
     * Atualiza a situação de um membro na arena.
     *
     * @param int $arena_id O ID da arena.
     * @param int $usuario_id O ID do usuário membro.
     * @param string $nova_situacao A nova situação do membro ('membro', 'rejeitado', etc.).
     * @return bool Retorna true em caso de sucesso.
     * @throws PDOException Se ocorrer um erro durante a operação no banco de dados.
     */
    public static function updateMemberStatus($arena_id, $usuario_id, $nova_situacao)
    {
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("UPDATE arena_membros SET situacao = ? WHERE arena_id = ? AND usuario_id = ?");
        return $stmt->execute([$nova_situacao, $arena_id, $usuario_id]);
    }

    /**
     * Remove um membro de uma arena.
     *
     * @param int $arena_id O ID da arena.
     * @param int $usuario_id O ID do usuário a ser removido.
     * @return bool Retorna true em caso de sucesso.
     */
    public static function removeMember($arena_id, $usuario_id)
    {
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("DELETE FROM arena_membros WHERE arena_id = ? AND usuario_id = ?");
        return $stmt->execute([$arena_id, $usuario_id]);
    }

    /**
     * Busca usuários que não são membros de uma arena específica.
     *
     * @param int $arena_id O ID da arena.
     * @param string $search_term O termo de busca para nome ou apelido do usuário.
     * @param int $limit Opcional. Limite de resultados.
     * @return array Retorna um array de arrays associativos com os detalhes dos usuários.
     * @throws PDOException Se ocorrer um erro durante a operação no banco de dados.
     */
    public static function searchNonMembers($arena_id, $search_term, $limit = 10)
    {
        $conn = Conexao::pegarConexao();
        $search_term = '%' . $search_term . '%';

        $stmt = $conn->prepare("
            SELECT u.id, u.nome, u.apelido
            FROM usuario u
            WHERE u.id NOT IN (SELECT am.usuario_id FROM arena_membros am WHERE am.arena_id = ?)
            AND (u.nome LIKE ? OR u.apelido LIKE ?)
            ORDER BY u.nome ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, $arena_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $search_term, PDO::PARAM_STR);
        $stmt->bindValue(3, $search_term, PDO::PARAM_STR);
        $stmt->bindValue(4, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca todas as arenas públicas com estatísticas de membros e rating.
     *
     * @return array Retorna uma lista de arenas públicas.
     */
    public static function getArenas()
    {
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("
            SELECT 
                a.*, 
                COUNT(am.usuario_id) as member_count, 
                AVG(u.rating) as avg_rating
            FROM arenas a
            LEFT JOIN arena_membros am ON a.id = am.arena_id AND am.situacao IN ('membro', 'fundador')
            LEFT JOIN usuario u ON am.usuario_id = u.id
            GROUP BY a.id
            ORDER BY member_count DESC, a.titulo ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca as arenas das quais um usuário é membro ('membro' ou 'fundador').
     *
     * @param int $user_id O ID do usuário.
     * @return array Retorna uma lista de arenas do usuário.
     */
    public static function getUserArenas($user_id)
    {
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("
            SELECT 
                a.*,
                (SELECT COUNT(*) FROM arena_membros WHERE arena_id = a.id AND situacao IN ('membro', 'fundador')) as member_count,
                (SELECT AVG(u.rating) FROM arena_membros am JOIN usuario u ON am.usuario_id = u.id WHERE am.arena_id = a.id AND am.situacao IN ('membro', 'fundador')) as avg_rating
            FROM arenas a
            JOIN arena_membros am_user ON a.id = am_user.arena_id
            WHERE am_user.usuario_id = ? AND am_user.situacao IN ('membro', 'fundador')
            GROUP BY a.id
            ORDER BY a.titulo ASC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca as arenas onde um usuário tem um convite ou solicitação pendente.
     *
     * @param int $user_id O ID do usuário.
     * @return array Retorna uma lista de arenas pendentes.
     */
    public static function getUserPendingArenas($user_id)
    {
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("
            SELECT a.*, am.situacao
            FROM arenas a
            JOIN arena_membros am ON a.id = am.arena_id
            WHERE am.usuario_id = ? AND am.situacao IN ('convidado', 'solicitado')
            ORDER BY am.situacao DESC, a.titulo ASC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}