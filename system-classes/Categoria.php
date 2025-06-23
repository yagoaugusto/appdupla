<?php

class Categoria
{
    /**
     * Adiciona uma nova categoria a um torneio.
     *
     * @param int $torneio_id O ID do torneio.
     * @param string $titulo O título da categoria (ex: "Iniciante", "A", "Mista Pro").
     * @param string $genero O gênero da categoria ('masculino', 'feminino', 'mista').
     * @return int|false O ID da categoria recém-criada ou false em caso de falha.
     */
    public static function addCategory($torneio_id, $titulo, $genero)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("INSERT INTO torneio_categorias (torneio_id, titulo, genero) VALUES (?, ?, ?)");
            $stmt->execute([$torneio_id, $titulo, $genero]);
            return $conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao adicionar categoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca todas as categorias de um torneio específico.
     *
     * @param int $torneio_id O ID do torneio.
     * @return array Um array de arrays associativos com os dados das categorias.
     */
    public static function getCategoriesByTorneioId($torneio_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("SELECT * FROM torneio_categorias WHERE torneio_id = ? ORDER BY titulo ASC");
            $stmt->execute([$torneio_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar categorias do torneio: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca uma categoria específica pelo ID.
     *
     * @param int $categoria_id O ID da categoria.
     * @return array|false Um array associativo com os dados da categoria ou false se não encontrada.
     */
    public static function getCategoriaById($categoria_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("SELECT * FROM torneio_categorias WHERE id = ?");
            $stmt->execute([$categoria_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar categoria por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exclui uma categoria de um torneio.
     *
     * @param int $categoria_id O ID da categoria a ser excluída.
     * @param int $torneio_id O ID do torneio ao qual a categoria pertence (para segurança).
     * @return bool True se a categoria foi excluída com sucesso, false caso contrário.
     */
    public static function deleteCategory($categoria_id, $torneio_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("DELETE FROM torneio_categorias WHERE id = ? AND torneio_id = ?");
            $stmt->execute([$categoria_id, $torneio_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erro ao excluir categoria: " . $e->getMessage());
            return false;
        }
    }
}