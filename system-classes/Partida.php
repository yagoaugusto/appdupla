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
}
