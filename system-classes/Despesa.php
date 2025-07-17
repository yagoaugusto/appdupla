<?php

class Despesa
{
    /**
     * Busca todas as categorias de despesa de uma arena.
     * @param int $arena_id
     * @return array
     */
    public static function getCategoriasPorArena(int $arena_id)
    {
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("SELECT * FROM despesa_categorias WHERE arena_id = ? ORDER BY nome ASC");
        $stmt->execute([$arena_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca os dados de uma categoria específica pelo seu ID.
     * @param int $categoria_id
     * @return mixed
     */
    public static function getCategoriaById(int $categoria_id)
    {
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("SELECT * FROM despesa_categorias WHERE id = ?");
        $stmt->execute([$categoria_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cria uma nova categoria de despesa.
     * @param array $dados
     * @return bool
     */
    public static function criarCategoria(array $dados)
    {
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("INSERT INTO despesa_categorias (arena_id, nome, descricao) VALUES (?, ?, ?)");
        return $stmt->execute([
            $dados['arena_id'],
            $dados['nome'],
            $dados['descricao']
        ]);
    }

    /**
     * Edita uma categoria de despesa existente.
     * @param int $categoria_id
     * @param array $dados
     * @return bool
     */
    public static function editarCategoria(int $categoria_id, array $dados)
    {
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("UPDATE despesa_categorias SET nome = ?, descricao = ?, status = ? WHERE id = ?");
        return $stmt->execute([
            $dados['nome'],
            $dados['descricao'],
            $dados['status'],
            $categoria_id
        ]);
    }

    // Futuramente, aqui entrarão os métodos para gerir as despesas (lançamentos).
// ==========================================================
    // INÍCIO DOS NOVOS MÉTODOS DE LANÇAMENTOS DE DESPESAS
    // ==========================================================

    /**
     * Busca as despesas de uma arena, com opção de filtros.
     * @param int $arena_id
     * @param array $filtros (ex: ['status' => 'pendente', 'categoria_id' => 5])
     * @return array
     */
    public static function getDespesasPorArena(int $arena_id, array $filtros = [])
    {
        $conn = Conexao::pegarConexao();
        $sql = "SELECT d.*, dc.nome as categoria_nome 
                FROM despesas d 
                JOIN despesa_categorias dc ON d.categoria_id = dc.id 
                WHERE d.arena_id = ?";
        
        $params = [$arena_id];

        if (!empty($filtros['status'])) {
            $sql .= " AND d.status = ?";
            $params[] = $filtros['status'];
        }
        if (!empty($filtros['categoria_id'])) {
            $sql .= " AND d.categoria_id = ?";
            $params[] = $filtros['categoria_id'];
        }
        if (!empty($filtros['competencia'])) {
            $sql .= " AND YEAR(d.data_vencimento) = ? AND MONTH(d.data_vencimento) = ?";
            $data_competencia = explode('-', $filtros['competencia']);
            $params[] = $data_competencia[0]; // Ano
            $params[] = $data_competencia[1]; // Mês
        }

        $sql .= " ORDER BY d.data_vencimento DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca uma despesa específica pelo seu ID.
     * @param int $despesa_id
     * @return mixed
     */
    public static function getDespesaById(int $despesa_id)
    {
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("SELECT * FROM despesas WHERE id = ?");
        $stmt->execute([$despesa_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria um novo lançamento de despesa.
     * @param array $dados
     * @return bool
     */
    public static function criarDespesa(array $dados)
    {
        $conn = Conexao::pegarConexao();
        $status = empty($dados['data_pagamento']) ? 'pendente' : 'paga';
        $sql = "INSERT INTO despesas (arena_id, categoria_id, descricao, valor, data_vencimento, data_pagamento, status, observacoes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            $dados['arena_id'],
            $dados['categoria_id'],
            $dados['descricao'],
            $dados['valor'],
            $dados['data_vencimento'],
            empty($dados['data_pagamento']) ? null : $dados['data_pagamento'],
            $status,
            $dados['observacoes']
        ]);
    }
    
    /**
     * Edita um lançamento de despesa existente.
     * @param int $despesa_id
     * @param array $dados
     * @return bool
     */
    public static function editarDespesa(int $despesa_id, array $dados)
    {
        $conn = Conexao::pegarConexao();
        $status = empty($dados['data_pagamento']) ? 'pendente' : 'paga';
        $sql = "UPDATE despesas SET categoria_id = ?, descricao = ?, valor = ?, data_vencimento = ?, data_pagamento = ?, status = ?, observacoes = ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            $dados['categoria_id'],
            $dados['descricao'],
            $dados['valor'],
            $dados['data_vencimento'],
            empty($dados['data_pagamento']) ? null : $dados['data_pagamento'],
            $status,
            $dados['observacoes'],
            $despesa_id
        ]);
    }
    
    /**
     * Apaga um lançamento de despesa.
     * @param int $despesa_id
     * @return bool
     */
    public static function apagarDespesa(int $despesa_id)
    {
        $conn = Conexao::pegarConexao();
        $stmt = $conn->prepare("DELETE FROM despesas WHERE id = ?");
        return $stmt->execute([$despesa_id]);
    }

    /**
     * Obtém os KPIs (estatísticas) das despesas para um determinado mês/arena.
     * @param int $arena_id
     * @param string $competencia (formato 'YYYY-MM')
     * @return array
     */
    public static function getStatsDespesas(int $arena_id, string $competencia)
    {
        $conn = Conexao::pegarConexao();
        $stats = ['pago_mes' => 0, 'a_pagar_total' => 0, 'vencido_total' => 0];
        
        $sql = "SELECT 
                    (SELECT SUM(valor) FROM despesas WHERE arena_id = :arena_id AND status = 'paga' AND data_pagamento LIKE :competencia_like) as pago_mes,
                    (SELECT SUM(valor) FROM despesas WHERE arena_id = :arena_id AND status = 'pendente') as a_pagar_total,
                    (SELECT SUM(valor) FROM despesas WHERE arena_id = :arena_id AND status = 'vencida') as vencido_total";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([':arena_id' => $arena_id, ':competencia_like' => $competencia . '%']);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $stats['pago_mes'] = (float) $result['pago_mes'];
            $stats['a_pagar_total'] = (float) $result['a_pagar_total'];
            $stats['vencido_total'] = (float) $result['vencido_total'];
        }
        return $stats;
    }
    
}