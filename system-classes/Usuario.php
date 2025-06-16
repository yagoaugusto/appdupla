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
    rating>(SELECT rating FROM usuario WHERE id={$id}) and usuario.id <> {$id} ORDER BY rating ASC LIMIT {$qtd}";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }

  public static function ranking_inferior_tela_principal($id, $qtd)
  {
    $query =
      "SELECT id,apelido,nome,rating FROM usuario WHERE 
    rating<=(SELECT rating FROM usuario WHERE id={$id}) ORDER BY rating DESC LIMIT {$qtd}";
    $conexao = Conexao::pegarConexao();
    $resultado = $conexao->query($query);
    $lista = $resultado->fetchAll();
    return $lista;
  }

  public static function posicao_usuario($id)
  {
    $query =
      "WITH rankeados AS (
  SELECT id, nome, rating, apelido,
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
    r.posicao,
    t.total,
  ROUND({$id}0 * (t.total - r.posicao) / (t.total - 1), 2) AS percentual_abaixo
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
}
