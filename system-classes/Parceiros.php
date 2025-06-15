<?php

class Parceiros
{

    public static function listar_parceiros()
    {
        $query =
            "SELECT * from parceiros";
        $conexao = Conexao::pegarConexao();
        $resultado = $conexao->query($query);
        $lista = $resultado->fetchAll();
        return $lista;
    }

    public static function parceiro_aleatorio()
    {
        $query =
            "SELECT * FROM parceiros
                ORDER BY RAND()
                LIMIT 2;";
        $conexao = Conexao::pegarConexao();
        $resultado = $conexao->query($query);
        $lista = $resultado->fetchAll();
        return $lista;
    }
}
