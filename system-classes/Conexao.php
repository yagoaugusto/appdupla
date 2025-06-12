<?php

class Conexao
{
    public static function pegarConexao()
    {
        $conexao = new PDO(
            DB_DRIVE . ':host=' . DB_HOSTNAME . ';dbname=' . DB_DATABASE . ';charset=utf8mb4', 
            DB_USERNAME, 
            DB_PASSWORD, 
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4")
        );
        
        $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexao;
    }
}
