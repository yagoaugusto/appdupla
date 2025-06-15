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
            "UPDATE partidas set status = '{$status}' where token_validacao = '{$partida}'";
        $conexao = Conexao::pegarConexao();
        $stmt = $conexao->prepare($query);
        $stmt->execute();
    }

    public static function partidas_jogador($jogador)
    {
        $query =
            "SELECT partidas.id,data,token_validacao,placar_a,placar_b,status,vencedor,
                jogador1_id,jogador2_id,jogador3_id,jogador4_id,
                j1.nome as nomej1,
                j2.nome as nomej2,
                j3.nome as nomej3,
                j4.nome as nomej4
                FROM partidas
                join usuario j1 on j1.id=jogador1_id 
                join usuario j2 on j2.id=jogador2_id
                join usuario j3 on j3.id=jogador3_id
                join usuario j4 on j4.id=jogador4_id
                WHERE '{$jogador}' IN (jogador1_id, jogador2_id, jogador3_id, jogador4_id)
                ORDER BY data DESC";
        $conexao = Conexao::pegarConexao();
        $resultado = $conexao->query($query);
        $lista = $resultado->fetchAll();
        return $lista;
    }
}
