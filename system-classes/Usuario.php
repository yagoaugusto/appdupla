<?php

class Usuario
{

  public function cadastrar_usuario($nome, $telefone, $senha, $cidade, $empunhadura, $cpf, $sobrenome, $apelido, $sexo)
  {
    $query =
      "INSERT INTO usuario(nome,sobrenome,apelido,sexo,telefone,senha,cidade,empunhadura,cpf) 
      values ('{$nome}','{$sobrenome}','{$apelido}','{$sexo}','{$telefone}','{$senha}',
      '{$cidade}','{$empunhadura}','{$cpf}')";
    $conexao = Conexao::pegarConexao();
    $stmt = $conexao->prepare($query);
    $stmt->execute();
  }

  public static function listar_usuarios_busca($nome)
  {
    $query =
      "SELECT * from usuario where nome like '%{$nome}%' or sobrenome like '%{$nome}%' order by nome";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }

  public static function listar_usuarios()
  {
    $query =
      "SELECT * from usuario order by nome";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }

  public static function partidas_usuario($id)
  {
    $query =
      "SELECT
  COUNT(*) AS total_partidas,
  SUM(
    (vencedor = 'A' AND (jogador1_id ={$id} OR jogador2_id ={$id})) OR
    (vencedor = 'B' AND (jogador3_id ={$id} OR jogador4_id ={$id}))
  ) AS vitorias,
  SUM(
    (vencedor = 'B' AND (jogador1_id ={$id} OR jogador2_id ={$id})) OR
    (vencedor = 'A' AND (jogador3_id ={$id} OR jogador4_id ={$id}))
  ) AS derrotas
FROM partidas
WHERE 
  jogador1_id ={$id} OR jogador2_id ={$id}
  OR jogador3_id ={$id} OR jogador4_id ={$id}";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }


  public static function listar_usuarios_por_rating()
  {
    $query =
      "SELECT * from usuario order by rating desc";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }

  public static function ranking_superior_tela_principal($id, $qtd)
  {
    $query =
      "SELECT id,apelido,nome,rating FROM usuario WHERE 
    rating>(SELECT rating FROM usuario WHERE id={$id}) ORDER BY rating ASC LIMIT {$qtd}";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }

  public static function ranking_inferior_tela_principal($id, $qtd)
  {
    $query =
      "SELECT id,apelido,nome,rating FROM usuario WHERE 
    rating<=(SELECT rating FROM usuario WHERE id={$id}) and usuario.id <> {$id} ORDER BY rating DESC LIMIT {$qtd}";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }

  public static function posicao_usuario($id)
  {
    $query =
      "WITH rankeados AS (
  SELECT id, nome, rating, apelido, rd, vol, sexo,
         RANK() OVER (ORDER BY rating DESC) AS posicao
  FROM usuario
),
total AS (
  SELECT COUNT(*) AS total FROM usuario
)
  SELECT 
    r.apelido,
    r.id,
    r.nome,
    r.rating,
    r.rd,
    r.vol AS vol,
    r.posicao,
    t.total,
  CASE
    WHEN t.total <= 1 THEN 0.00 -- Se houver 1 ou 0 usuários, o percentual abaixo é 0.
    ELSE ROUND(100.0 * (t.total - r.posicao) / (t.total - 1), 2)
  END AS percentual_abaixo
FROM rankeados r, total t
WHERE r.id = {$id}";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }

  public static function quadro_honra_parceiro_vitoria($id)
  {
    $query =
      "SELECT 
    u.nome AS parceiro_nome,
    u.apelido,
    parceiros.parceiro_id,
    COUNT(*) AS partidas,
    SUM(CASE 
        WHEN (vencedor = 'A' AND meu_time = 'A') OR (vencedor = 'B' AND meu_time = 'B') THEN 1
        ELSE 0
    END) AS vitorias,
    SUM(CASE 
        WHEN (vencedor = 'A' AND meu_time != 'A') OR (vencedor = 'B' AND meu_time != 'B') THEN 1
        ELSE 0
    END) AS derrotas
FROM (
    SELECT 
        CASE 
            WHEN jogador1_id = {$id} THEN jogador2_id
            WHEN jogador2_id = {$id} THEN jogador1_id
            WHEN jogador3_id = {$id} THEN jogador4_id
            WHEN jogador4_id = {$id} THEN jogador3_id
        END AS parceiro_id,
        CASE 
            WHEN jogador1_id = {$id} OR jogador2_id = {$id} THEN 'A'
            ELSE 'B'
        END AS meu_time,
        vencedor
    FROM partidas
    WHERE {$id} IN (jogador1_id, jogador2_id, jogador3_id, jogador4_id)
) AS parceiros
JOIN usuario u ON u.id = parceiros.parceiro_id
GROUP BY parceiros.parceiro_id, u.nome
ORDER BY vitorias DESC";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }



  public static function quadro_honra_parceiro_derrota($id)
  {
    $query =
      "SELECT 
    u.nome AS parceiro_nome,
    u.apelido,
    parceiros.parceiro_id,
    COUNT(*) AS partidas,
    SUM(CASE 
        WHEN (vencedor = 'A' AND meu_time = 'A') OR (vencedor = 'B' AND meu_time = 'B') THEN 1
        ELSE 0
    END) AS vitorias,
    SUM(CASE 
        WHEN (vencedor = 'A' AND meu_time != 'A') OR (vencedor = 'B' AND meu_time != 'B') THEN 1
        ELSE 0
    END) AS derrotas
FROM (
    SELECT 
        CASE 
            WHEN jogador1_id = {$id} THEN jogador2_id
            WHEN jogador2_id = {$id} THEN jogador1_id
            WHEN jogador3_id = {$id} THEN jogador4_id
            WHEN jogador4_id = {$id} THEN jogador3_id
        END AS parceiro_id,
        CASE 
            WHEN jogador1_id = {$id} OR jogador2_id = {$id} THEN 'A'
            ELSE 'B'
        END AS meu_time,
        vencedor
    FROM partidas
    WHERE {$id} IN (jogador1_id, jogador2_id, jogador3_id, jogador4_id)
) AS parceiros
JOIN usuario u ON u.id = parceiros.parceiro_id
GROUP BY parceiros.parceiro_id, u.nome
ORDER BY derrotas DESC";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }

  public static function quadro_honra_adversario_vitoria($id)
  {
    $query =
      "SELECT 
    u.nome AS adversario_nome,
    u.apelido,
    adversarios.adversario_id,
    COUNT(*) AS partidas,
    SUM(CASE 
        WHEN (vencedor = 'A' AND meu_time = 'A') OR (vencedor = 'B' AND meu_time = 'B') THEN 1
        ELSE 0
    END) AS vitorias,
    SUM(CASE 
        WHEN (vencedor = 'A' AND meu_time != 'A') OR (vencedor = 'B' AND meu_time != 'B') THEN 1
        ELSE 0
    END) AS derrotas
FROM (
    SELECT 
        CASE 
            WHEN jogador1_id = {$id} OR jogador2_id = {$id} THEN 'A'
            ELSE 'B'
        END AS meu_time,
        vencedor,
        -- Adversário 1
        CASE 
            WHEN jogador1_id = {$id} OR jogador2_id = {$id} THEN jogador3_id
            ELSE jogador1_id
        END AS adversario_id
    FROM partidas
    WHERE {$id} IN (jogador1_id, jogador2_id, jogador3_id, jogador4_id)

    UNION ALL

    SELECT 
        CASE 
            WHEN jogador1_id = {$id} OR jogador2_id = {$id} THEN 'A'
            ELSE 'B'
        END AS meu_time,
        vencedor,
        -- Adversário 2
        CASE 
            WHEN jogador1_id = {$id} OR jogador2_id = {$id} THEN jogador4_id
            ELSE jogador2_id
        END AS adversario_id
    FROM partidas
    WHERE {$id} IN (jogador1_id, jogador2_id, jogador3_id, jogador4_id)
) AS adversarios
JOIN usuario u ON u.id = adversarios.adversario_id
GROUP BY adversarios.adversario_id, u.nome
ORDER BY vitorias DESC";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }


  public static function quadro_honra_adversario_derrota($id)
  {
    $query =
      "SELECT 
    u.nome AS adversario_nome,
    u.apelido,
    adversarios.adversario_id,
    COUNT(*) AS partidas,
    SUM(CASE 
        WHEN (vencedor = 'A' AND meu_time = 'A') OR (vencedor = 'B' AND meu_time = 'B') THEN 1
        ELSE 0
    END) AS vitorias,
    SUM(CASE 
        WHEN (vencedor = 'A' AND meu_time != 'A') OR (vencedor = 'B' AND meu_time != 'B') THEN 1
        ELSE 0
    END) AS derrotas
FROM (
    SELECT 
        CASE 
            WHEN jogador1_id = {$id} OR jogador2_id = {$id} THEN 'A'
            ELSE 'B'
        END AS meu_time,
        vencedor,
        -- Adversário 1
        CASE 
            WHEN jogador1_id = {$id} OR jogador2_id = {$id} THEN jogador3_id
            ELSE jogador1_id
        END AS adversario_id
    FROM partidas
    WHERE {$id} IN (jogador1_id, jogador2_id, jogador3_id, jogador4_id)

    UNION ALL

    SELECT 
        CASE 
            WHEN jogador1_id = {$id} OR jogador2_id = {$id} THEN 'A'
            ELSE 'B'
        END AS meu_time,
        vencedor,
        -- Adversário 2
        CASE 
            WHEN jogador1_id = {$id} OR jogador2_id = {$id} THEN jogador4_id
            ELSE jogador2_id
        END AS adversario_id
    FROM partidas
    WHERE {$id} IN (jogador1_id, jogador2_id, jogador3_id, jogador4_id)
) AS adversarios
JOIN usuario u ON u.id = adversarios.adversario_id
GROUP BY adversarios.adversario_id, u.nome
ORDER BY derrotas DESC";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }

  public static function historico_rating($id)
  {
    $query =
      "SELECT * from historico_rating where jogador_id={$id} order by id desc limit 25";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }

  public static function variacao_rating($id, $dias)
  {
    $query =
      "SELECT 
  u.id,u.nome,
  hr_inicial.rating_novo AS rating_10_dias_atras,
  hr_final.rating_novo AS rating_atual,
  (hr_final.rating_novo - hr_inicial.rating_novo) AS variacao_rating
FROM usuario u
LEFT JOIN (
  SELECT rating_novo
  FROM historico_rating
  WHERE jogador_id ={$id}
    AND data >= CURDATE() - INTERVAL {$dias} DAY
  ORDER BY data ASC
  LIMIT 1
) AS hr_inicial ON u.id ={$id}
LEFT JOIN (
  SELECT rating_novo
  FROM historico_rating
  WHERE jogador_id ={$id}
  ORDER BY data DESC
  LIMIT 1
) AS hr_final ON u.id ={$id}
WHERE u.id ={$id}";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }

  /**
   * Busca uma análise de IA em cache para um usuário.
   * A análise é considerada válida se foi criada no mesmo dia.
   *
   * @param int $usuario_id O ID do usuário.
   * @return string|false Retorna a análise em cache se for válida, ou false.
   */
  public static function getAnaliseCache($usuario_id)
  {
      $conn = Conexao::pegarConexao();
      $stmt = $conn->prepare("SELECT analise_html FROM ia_analise_cache WHERE usuario_id = ? AND DATE(data_cache) = CURDATE()");
      $stmt->execute([$usuario_id]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result ? $result['analise_html'] : false;
  }

  /**
   * Salva ou atualiza a análise de IA de um usuário no cache.
   *
   * @param int $usuario_id O ID do usuário.
   * @param string $analise_html O conteúdo HTML da análise.
   * @return bool Retorna true em caso de sucesso.
   */
  public static function setAnaliseCache($usuario_id, $analise_html)
  {
      $conn = Conexao::pegarConexao();
      $stmt = $conn->prepare("INSERT INTO ia_analise_cache (usuario_id, analise_html) VALUES (?, ?) ON DUPLICATE KEY UPDATE analise_html = VALUES(analise_html), data_cache = NOW()");
      return $stmt->execute([$usuario_id, $analise_html]);
  }



    /**
     * Retorna a distribuição de ratings de todos os usuários para um histograma.
     * @param int $binSize O tamanho do intervalo para agrupar os ratings (ex: 100).
     * @return array Retorna um array com os intervalos de rating e a contagem de jogadores em cada um.
     */
    public static function getRatingDistribution($binSize = 100)
    {
        try {
            $conn = Conexao::pegarConexao();
            // A query agrupa os ratings em "bins" (intervalos) e conta quantos jogadores estão em cada um.
            $stmt = $conn->prepare(
                "SELECT
                    (FLOOR(rating / :binSize)) * :binSize AS rating_floor,
                    COUNT(*) AS player_count
                FROM usuario
                WHERE rating IS NOT NULL
                GROUP BY rating_floor
                ORDER BY rating_floor ASC"
            );
            $stmt->bindValue(':binSize', $binSize, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar distribuição de rating: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna o histórico do rating médio da comunidade nos últimos N dias.
     * @param int $days O número de dias para olhar para trás.
     * @return array Retorna um array com data e rating_medio.
     */
    public static function getCommunityAverageRatingHistory($days = 10)
    {
        try {
            $conn = Conexao::pegarConexao();
            // Esta query calcula a média do rating_novo para cada dia onde houve pelo menos uma partida.
            $stmt = $conn->prepare(
                "SELECT
                    DATE(data) AS dia,
                    AVG(rating_novo) AS rating_medio
                FROM historico_rating
                WHERE data >= CURDATE() - INTERVAL :days DAY
                GROUP BY dia
                ORDER BY dia ASC"
            );
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar histórico de rating médio da comunidade: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna o histórico de rating de um usuário específico nos últimos N dias.
     * @param int $usuario_id O ID do usuário.
     * @param int $days O número de dias para olhar para trás.
     * @return array Retorna um array com data e rating_novo.
     */
    public static function getUserRatingHistory($usuario_id, $days = 10)
    {
        try {
            $conn = Conexao::pegarConexao();
            // Pega o último rating de cada dia para o usuário
            $stmt = $conn->prepare(
                "SELECT t1.dia, t1.rating_novo
                 FROM (
                    SELECT DATE(data) AS dia, rating_novo, 
                           ROW_NUMBER() OVER(PARTITION BY DATE(data) ORDER BY data DESC) as rn
                    FROM historico_rating
                    WHERE jogador_id = :usuario_id AND data >= CURDATE() - INTERVAL :days DAY
                 ) t1
                 WHERE t1.rn = 1
                 ORDER BY t1.dia ASC"
            );
            $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar histórico de rating do usuário: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca o último rating de um usuário antes de uma data específica.
     * @param int $usuario_id O ID do usuário.
     * @param string $before_date A data limite no formato 'Y-m-d'.
     * @return array|false Retorna o registro do histórico ou false.
     */
    public static function getRatingBeforeDate($usuario_id, $before_date)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("SELECT rating_novo FROM historico_rating WHERE jogador_id = :usuario_id AND DATE(data) < :before_date ORDER BY data DESC LIMIT 1");
            $stmt->execute([':usuario_id' => $usuario_id, ':before_date' => $before_date]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar rating anterior do usuário: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca o parceiro mais frequente de um usuário.
     * @param int $id O ID do usuário.
     * @return array|false Retorna os dados do parceiro ou false se não houver.
     */
    public static function getMostFrequentPartner($id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare(
                "SELECT 
                    u.nome AS parceiro_nome,
                    u.apelido,
                    parceiros.parceiro_id,
                    COUNT(*) AS partidas,
                    SUM(CASE 
                        WHEN (parceiros.vencedor = 'A' AND parceiros.meu_time = 'A') OR (parceiros.vencedor = 'B' AND parceiros.meu_time = 'B') THEN 1
                        ELSE 0
                    END) AS vitorias
                FROM (
                    SELECT 
                        CASE 
                            WHEN jogador1_id = :id THEN jogador2_id
                            WHEN jogador2_id = :id THEN jogador1_id
                            WHEN jogador3_id = :id THEN jogador4_id
                            WHEN jogador4_id = :id THEN jogador3_id
                        END AS parceiro_id,
                        CASE 
                            WHEN jogador1_id = :id OR jogador2_id = :id THEN 'A'
                            ELSE 'B'
                        END AS meu_time,
                        vencedor
                    FROM partidas
                    WHERE :id IN (jogador1_id, jogador2_id, jogador3_id, jogador4_id)
                ) AS parceiros
                JOIN usuario u ON u.id = parceiros.parceiro_id
                WHERE parceiros.parceiro_id IS NOT NULL
                GROUP BY parceiros.parceiro_id, u.nome, u.apelido
                ORDER BY partidas DESC
                LIMIT 1"
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar parceiro mais frequente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca o rival mais frequente de um usuário.
     * @param int $id O ID do usuário.
     * @return array|false Retorna os dados do rival ou false se não houver.
     */
    public static function getMostFrequentRival($id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare(
                "SELECT 
                    u.nome AS rival_nome,
                    u.apelido,
                    adversarios.adversario_id,
                    COUNT(*) AS partidas,
                    SUM(CASE 
                        WHEN (adversarios.vencedor = 'A' AND adversarios.meu_time = 'A') OR (adversarios.vencedor = 'B' AND adversarios.meu_time = 'B') THEN 1
                        ELSE 0
                    END) AS vitorias
                FROM (
                    SELECT 
                        CASE WHEN jogador1_id = :id OR jogador2_id = :id THEN 'A' ELSE 'B' END AS meu_time,
                        vencedor,
                        CASE WHEN jogador1_id = :id OR jogador2_id = :id THEN jogador3_id ELSE jogador1_id END AS adversario_id
                    FROM partidas WHERE :id IN (jogador1_id, jogador2_id, jogador3_id, jogador4_id)
                    UNION ALL
                    SELECT 
                        CASE WHEN jogador1_id = :id OR jogador2_id = :id THEN 'A' ELSE 'B' END AS meu_time,
                        vencedor,
                        CASE WHEN jogador1_id = :id OR jogador2_id = :id THEN jogador4_id ELSE jogador2_id END AS adversario_id
                    FROM partidas WHERE :id IN (jogador1_id, jogador2_id, jogador3_id, jogador4_id)
                ) AS adversarios
                JOIN usuario u ON u.id = adversarios.adversario_id
                WHERE adversarios.adversario_id IS NOT NULL
                GROUP BY adversarios.adversario_id, u.nome, u.apelido
                ORDER BY partidas DESC
                LIMIT 1"
            );
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar rival mais frequente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca os usuários com o maior ganho de rating em um período.
     * Considera o rating atual e o rating mais recente antes do início do período.
     * Se o usuário não tinha rating antes do período, considera o rating inicial como 1500 (ou o primeiro registro).
     *
     * @param int $days O número de dias para considerar o ganho (ex: 7 para os últimos 7 dias).
     * @param int $limit O número de usuários a serem retornados (ex: top 5).
     * @return array Retorna um array de arrays associativos com os dados dos usuários e seu ganho de rating.
     */
    public static function getTopRatingGainers($days = 7, $limit = 5)
    {
        try {
            $conn = Conexao::pegarConexao();

            $stmt = $conn->prepare("
                SELECT
                    u.id,
                    u.nome,
                    u.sobrenome,
                    u.apelido,
                    u.rating AS current_rating,
                    (u.rating - COALESCE(
                        (SELECT hr_start.rating_novo
                         FROM historico_rating hr_start
                         WHERE hr_start.jogador_id = u.id
                           AND hr_start.data < CURDATE() - INTERVAL :days_ago_start DAY
                         ORDER BY hr_start.data DESC
                         LIMIT 1),
                        (SELECT hr_first.rating_novo
                         FROM historico_rating hr_first
                         WHERE hr_first.jogador_id = u.id
                         ORDER BY hr_first.data ASC
                         LIMIT 1),
                        1500 -- Default starting rating if no history found
                    )) AS rating_gain
                FROM
                    usuario u
                WHERE
                    u.rating IS NOT NULL
                ORDER BY
                    rating_gain DESC
                LIMIT :limit_val
            ");

            $stmt->bindValue(':days_ago_start', $days, PDO::PARAM_INT);
            $stmt->bindValue(':limit_val', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Erro ao buscar maiores ganhadores de rating: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca os usuários com as maiores sequências de vitórias ativas.
     * Uma sequência é ativa se a última partida do jogador foi uma vitória.
     *
     * @param int $limit O número de usuários a serem retornados (ex: top 5).
     * @return array Retorna um array de arrays associativos com os dados dos usuários e sua sequência de vitórias.
     */
    public static function getTopWinningStreaks($limit = 5)
    {
        try {
            $conn = Conexao::pegarConexao();

            // Esta query usa Common Table Expressions (CTEs) para:
            // 1. `player_matches`: "desempilhar" a tabela de partidas para ter uma linha por jogador por partida.
            // 2. `last_losses`: Encontrar a data da última derrota de cada jogador.
            // 3. `streaks`: Contar o número de vitórias de cada jogador desde sua última derrota.
            $stmt = $conn->prepare("
                WITH player_matches AS (
                    SELECT id as partida_id, data, jogador1_id as jogador_id, CASE WHEN vencedor = 'A' THEN 1 ELSE 0 END as is_win FROM partidas WHERE status = 'validada' AND jogador1_id IS NOT NULL
                    UNION ALL
                    SELECT id as partida_id, data, jogador2_id as jogador_id, CASE WHEN vencedor = 'A' THEN 1 ELSE 0 END as is_win FROM partidas WHERE status = 'validada' AND jogador2_id IS NOT NULL
                    UNION ALL
                    SELECT id as partida_id, data, jogador3_id as jogador_id, CASE WHEN vencedor = 'B' THEN 1 ELSE 0 END as is_win FROM partidas WHERE status = 'validada' AND jogador3_id IS NOT NULL
                    UNION ALL
                    SELECT id as partida_id, data, jogador4_id as jogador_id, CASE WHEN vencedor = 'B' THEN 1 ELSE 0 END as is_win FROM partidas WHERE status = 'validada' AND jogador4_id IS NOT NULL
                ),
                last_losses AS (
                    SELECT jogador_id, MAX(data) as last_loss_date FROM player_matches WHERE is_win = 0 GROUP BY jogador_id
                ),
                streaks AS (
                    SELECT pm.jogador_id, COUNT(pm.partida_id) as win_streak
                    FROM player_matches pm
                    LEFT JOIN last_losses ll ON pm.jogador_id = ll.jogador_id
                    WHERE pm.is_win = 1 AND (pm.data > ll.last_loss_date OR ll.last_loss_date IS NULL)
                    GROUP BY pm.jogador_id
                )
                SELECT u.id, u.nome, u.apelido, s.win_streak
                FROM streaks s JOIN usuario u ON s.jogador_id = u.id
                WHERE s.win_streak > 0 ORDER BY s.win_streak DESC, u.rating DESC LIMIT :limit_val
            ");

            $stmt->bindValue(':limit_val', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar maiores sequências de vitórias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca os usuários com as maiores sequências de derrotas ativas.
     * Uma sequência é ativa se a última partida do jogador foi uma derrota.
     *
     * @param int $limit O número de usuários a serem retornados (ex: top 5).
     * @return array Retorna um array de arrays associativos com os dados dos usuários e sua sequência de derrotas.
     */
    public static function getTopLosingStreaks($limit = 5)
    {
        try {
            $conn = Conexao::pegarConexao();

            // Esta query usa Common Table Expressions (CTEs) para:
            // 1. `player_matches`: "desempilhar" a tabela de partidas para ter uma linha por jogador por partida.
            // 2. `last_wins`: Encontrar a data da última vitória de cada jogador.
            // 3. `streaks`: Contar o número de derrotas de cada jogador desde sua última vitória.
            $stmt = $conn->prepare("
                WITH player_matches AS (
                    SELECT id as partida_id, data, jogador1_id as jogador_id, CASE WHEN vencedor = 'A' THEN 1 ELSE 0 END as is_win FROM partidas WHERE status = 'validada' AND jogador1_id IS NOT NULL
                    UNION ALL
                    SELECT id as partida_id, data, jogador2_id as jogador_id, CASE WHEN vencedor = 'A' THEN 1 ELSE 0 END as is_win FROM partidas WHERE status = 'validada' AND jogador2_id IS NOT NULL
                    UNION ALL
                    SELECT id as partida_id, data, jogador3_id as jogador_id, CASE WHEN vencedor = 'B' THEN 1 ELSE 0 END as is_win FROM partidas WHERE status = 'validada' AND jogador3_id IS NOT NULL
                    UNION ALL
                    SELECT id as partida_id, data, jogador4_id as jogador_id, CASE WHEN vencedor = 'B' THEN 1 ELSE 0 END as is_win FROM partidas WHERE status = 'validada' AND jogador4_id IS NOT NULL
                ),
                last_wins AS (
                    SELECT jogador_id, MAX(data) as last_win_date FROM player_matches WHERE is_win = 1 GROUP BY jogador_id
                ),
                streaks AS (
                    SELECT pm.jogador_id, COUNT(pm.partida_id) as loss_streak
                    FROM player_matches pm
                    LEFT JOIN last_wins lw ON pm.jogador_id = lw.jogador_id
                    WHERE pm.is_win = 0 AND (pm.data > lw.last_win_date OR lw.last_win_date IS NULL)
                    GROUP BY pm.jogador_id
                )
                SELECT u.id, u.nome, u.apelido, s.loss_streak
                FROM streaks s JOIN usuario u ON s.jogador_id = u.id
                WHERE s.loss_streak > 0 ORDER BY s.loss_streak DESC, u.rating ASC LIMIT :limit_val
            ");

            $stmt->bindValue(':limit_val', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar maiores sequências de derrotas: " . $e->getMessage());
            return [];
        }
    }
}
