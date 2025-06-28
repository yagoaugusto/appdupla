<?php

class Partida
{

    public static function info_partida($token)
    {
        $query =
            "SELECT
            jogador1_id,
            jogador2_id,
            jogador3_id,
            jogador4_id,
            j1.nome as nomej1,
            j2.nome as nomej2,
            j3.nome as nomej3,
            j4.nome as nomej4,
            j1.apelido as apelidoj1,
            j2.apelido as apelidoj2,
            j3.apelido as apelidoj3,
            j4.apelido as apelidoj4,
            placar_a,
            placar_b,
            vencedor,
            status,
            validado_jogador1,
            validado_jogador2,
            validado_jogador3,
            validado_jogador4,
            rejeitado_jogador1,
            rejeitado_jogador2,
            rejeitado_jogador3,
            rejeitado_jogador4,
            data
            from partidas 
            join usuario as j1 on j1.id=jogador1_id
            join usuario as j2 on j2.id=jogador2_id
            join usuario as j3 on j3.id=jogador3_id
            join usuario as j4 on j4.id=jogador4_id
            where token_validacao = '{$token}'";
        $conexao = Conexao::pegarConexao();
        $resultado = $conexao->query($query);
        $lista = $resultado->fetchAll();
        return $lista;
    }

    public function validar_partida($partida, $coluna_validado)
    {
        $query =
            "UPDATE partidas set {$coluna_validado} = 1 where token_validacao = '{$partida}'";
        $conexao = Conexao::pegarConexao();
        $stmt = $conexao->prepare($query);
        $stmt->execute();
    }

    public function att_status_partida($partida, $status)
    {
        $query =
            "UPDATE partidas set status = '{$status}',
            validado_jogador1 = 1,
            validado_jogador2 = 1,
            validado_jogador3 = 1,
            validado_jogador4 = 1
            where token_validacao = '{$partida}'";
        $conexao = Conexao::pegarConexao();
        $stmt = $conexao->prepare($query);
        $stmt->execute();
    }

    public static function partidas_jogador($jogador)
    {
        $query =
            "SELECT partidas.id,partidas.data,token_validacao,placar_a,placar_b,status,vencedor,
                jogador1_id,jogador2_id,jogador3_id,jogador4_id,
                h1.rating_anterior as anterior_h1,h1.rating_novo,
                h2.rating_anterior as anterior_h2,h2.rating_novo,
                h3.rating_anterior as anterior_h3,h3.rating_novo,
                h4.rating_anterior as anterior_h4,h4.rating_novo,
                (h1.rating_novo-h1.rating_anterior) as diff_h1,
                (h2.rating_novo-h2.rating_anterior) as diff_h2,
                (h3.rating_novo-h3.rating_anterior) as diff_h3,
                (h4.rating_novo-h4.rating_anterior) as diff_h4,
                j1.nome as nomej1,
                j2.nome as nomej2,
                j3.nome as nomej3,
                j4.nome as nomej4,
                j1.sobrenome as sobrenomej1,
                j2.sobrenome as sobrenomej2,
                j3.sobrenome as sobrenomej3,
                j4.sobrenome as sobrenomej4,
                j1.apelido as apelidoj1,
                j2.apelido as apelidoj2,
                j3.apelido as apelidoj3,
                j4.apelido as apelidoj4
                FROM partidas
                join usuario j1 on j1.id=jogador1_id 
                join usuario j2 on j2.id=jogador2_id
                join usuario j3 on j3.id=jogador3_id
                join usuario j4 on j4.id=jogador4_id
                left join historico_rating h1 on h1.jogador_id = jogador1_id and h1.partida_token = partidas.token_validacao
                left join historico_rating h2 on h2.jogador_id = jogador2_id and h2.partida_token = partidas.token_validacao
                left join historico_rating h3 on h3.jogador_id = jogador3_id and h3.partida_token = partidas.token_validacao
                left join historico_rating h4 on h4.jogador_id = jogador4_id and h4.partida_token = partidas.token_validacao
                WHERE '{$jogador}' IN (jogador1_id, jogador2_id, jogador3_id, jogador4_id)
                ORDER BY data DESC";
        $conexao = Conexao::pegarConexao();
        $resultado = $conexao->query($query);
        $lista = $resultado->fetchAll();
        return $lista;
    }

    public static function partidas_pendente_jogador($jogador)
    {
        $query =
            "SELECT 
partidas.id,data,token_validacao,placar_a,placar_b,status,vencedor,
                jogador1_id,jogador2_id,jogador3_id,jogador4_id,
                j1.nome as nomej1,
                j2.nome as nomej2,
                j3.nome as nomej3,
                j4.nome as nomej4,
                j1.sobrenome as sobrenomej1,
                j2.sobrenome as sobrenomej2,
                j3.sobrenome as sobrenomej3,
                j4.sobrenome as sobrenomej4,
                j1.apelido as apelidoj1,
                j2.apelido as apelidoj2,
                j3.apelido as apelidoj3,
                j4.apelido as apelidoj4
FROM partidas
                join usuario j1 on j1.id=jogador1_id 
                join usuario j2 on j2.id=jogador2_id
                join usuario j3 on j3.id=jogador3_id
                join usuario j4 on j4.id=jogador4_id
WHERE 
    (
        (jogador1_id ='{$jogador}' AND validado_jogador1 = 0) OR
        (jogador2_id ='{$jogador}' AND validado_jogador2 = 0) OR
        (jogador3_id ='{$jogador}' AND validado_jogador3 = 0) OR
        (jogador4_id ='{$jogador}' AND validado_jogador4 = 0)
    )
AND status = 'pendente'  -- ou o status que representa partidas não finalizadas
ORDER BY data DESC";
        $conexao = Conexao::pegarConexao();
        $resultado = $conexao->query($query);
        $lista = $resultado->fetchAll();
        return $lista;
    }

    public static function qtd_partida_pendente($jogador)
    {
        $query =
            "SELECT count(1) as quantidade
FROM partidas
                join usuario j1 on j1.id=jogador1_id 
                join usuario j2 on j2.id=jogador2_id
                join usuario j3 on j3.id=jogador3_id
                join usuario j4 on j4.id=jogador4_id
WHERE 
    (
        (jogador1_id ='{$jogador}' AND validado_jogador1 = 0) OR
        (jogador2_id ='{$jogador}' AND validado_jogador2 = 0) OR
        (jogador3_id ='{$jogador}' AND validado_jogador3 = 0) OR
        (jogador4_id ='{$jogador}' AND validado_jogador4 = 0)
    )
AND status = 'pendente'  -- ou o status que representa partidas não finalizadas
ORDER BY data DESC";
        $conexao = Conexao::pegarConexao();
        $resultado = $conexao->query($query);
        $lista = $resultado->fetchAll();
        return $lista;
    }
}
