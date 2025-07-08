<?php

class Lojinha
{
    /**
     * Busca todos os produtos de uma arena, já com o estoque calculado.
     * O estoque é: estoque_inicial + somatório de entradas - somatório de saídas.
     * @param int $arena_id O ID da arena.
     * @return array Lista de produtos com estoque calculado.
     */
    public static function getProdutosPorArena($arena_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            // Query que calcula o estoque dinamicamente
            $stmt = $conn->prepare("
                SELECT
                    p.*,
                    (p.estoque + COALESCE(entradas.total_entradas, 0) - COALESCE(saidas.total_saidas, 0)) AS estoque_calculado
                FROM
                    lojinha_produtos p
                LEFT JOIN
                    (SELECT produto_id, SUM(quantidade) AS total_entradas FROM lojinha_entradas GROUP BY produto_id) AS entradas
                    ON p.id = entradas.produto_id
                LEFT JOIN
                    (SELECT produto_id, SUM(quantidade) AS total_saidas FROM lojinha_vendas_itens GROUP BY produto_id) AS saidas
                    ON p.id = saidas.produto_id
                WHERE
                    p.arena_id = ?
                ORDER BY
                    p.nome ASC
            ");
            $stmt->execute([$arena_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar produtos da arena com estoque calculado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca um produto específico pelo seu ID.
     * @param int $produto_id O ID do produto.
     * @return array|false Dados do produto ou false se não encontrado.
     */
    public static function getProdutoById($produto_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("SELECT * FROM lojinha_produtos WHERE id = ?");
            $stmt->execute([$produto_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar produto por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cria um novo produto no banco de dados.
     *
     * @param array $dados Dados do produto.
     * @return bool True se bem-sucedido, false caso contrário.
     */
    public static function criarProduto(array $dados)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare(
                "INSERT INTO lojinha_produtos (arena_id, nome, descricao, preco_venda, estoque, status, imagem) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            return $stmt->execute([
                $dados['arena_id'],
                $dados['nome'],
                $dados['descricao'],
                $dados['preco_venda'],
                $dados['estoque'], // Salva como estoque inicial
                $dados['status'],
                $dados['imagem']
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao criar produto: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Edita um produto existente no banco de dados.
     *
     * @param int $produto_id ID do produto.
     * @param array $dados Dados a serem atualizados.
     * @return bool True se bem-sucedido, false caso contrário.
     */
    public static function editarProduto(int $produto_id, array $dados)
    {
        try {
            $conn = Conexao::pegarConexao();
            
            $set_parts = [];
            $params = [];
            foreach ($dados as $key => $value) {
                // Impede a atualização direta do campo 'estoque'
                if ($key === 'estoque') {
                    continue;
                }
                $set_parts[] = "`$key` = ?";
                $params[] = $value;
            }

            if (empty($set_parts)) {
                return true; 
            }

            $params[] = $produto_id;

            $sql = "UPDATE lojinha_produtos SET " . implode(', ', $set_parts) . " WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao editar produto: " . $e->getMessage());
            return false;
        }
    }


/**
     * Busca todos os produtos ATIVOS de uma arena, já com o estoque calculado.
     * @param int $arena_id O ID da arena.
     * @return array Lista de produtos.
     */
    public static function getProdutosAtivosPorArena($arena_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("
                SELECT
                    p.*,
                    (p.estoque + COALESCE(entradas.total_entradas, 0) - COALESCE(saidas.total_saidas, 0)) AS estoque_calculado
                FROM lojinha_produtos p
                LEFT JOIN (SELECT produto_id, SUM(quantidade) AS total_entradas FROM lojinha_entradas GROUP BY produto_id) AS entradas ON p.id = entradas.produto_id
                LEFT JOIN (SELECT produto_id, SUM(quantidade) AS total_saidas FROM lojinha_vendas_itens GROUP BY produto_id) AS saidas ON p.id = saidas.produto_id
                WHERE p.arena_id = ? AND p.status = 'ATIVO'
                ORDER BY p.nome ASC
            ");
            $stmt->execute([$arena_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar produtos ativos da arena: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registra uma nova venda e seus itens no banco de dados.
     * @param array $dados Os dados da venda.
     * @return bool True se bem-sucedido, false em caso de falha.
     */
    public static function registrarVenda(array $dados)
    {
        $conn = Conexao::pegarConexao();
        try {
            $conn->beginTransaction();

            // 1. Inserir na tabela principal de vendas
            $stmt_venda = $conn->prepare(
                "INSERT INTO lojinha_vendas (arena_id, valor_total, forma_pagamento, usuario_id) 
                 VALUES (?, ?, ?, ?)"
            );
            $stmt_venda->execute([
                $dados['arena_id'],
                $dados['valor_total'],
                $dados['forma_pagamento'],
                $dados['usuario_id'] ?: null // Permite usuário nulo
            ]);
            $venda_id = $conn->lastInsertId();

            // 2. Inserir cada item da venda
            $stmt_item = $conn->prepare(
                "INSERT INTO lojinha_vendas_itens (venda_id, produto_id, quantidade, preco_unitario)
                 VALUES (?, ?, ?, ?)"
            );
            foreach ($dados['itens'] as $item) {
                $stmt_item->execute([
                    $venda_id,
                    $item['id'],
                    $item['quantity'],
                    $item['preco_venda']
                ]);
            }

            // 3. Confirmar a transação
            $conn->commit();
            return true;

        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Erro ao registrar venda: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Compila dados de vendas para um relatório dentro de um período.
     *
     * @param int $arena_id
     * @param string $data_inicio Formato 'YYYY-MM-DD'
     * @param string $data_fim Formato 'YYYY-MM-DD'
     * @return array Dados compilados para o relatório.
     */
    public static function getRelatorioVendas(int $arena_id, string $data_inicio, string $data_fim)
    {
        $conn = Conexao::pegarConexao();
        $resultado = [
            'resumo_produtos' => [],
            'resumo_pagamentos' => ['dinheiro' => 0, 'pix' => 0, 'cartao' => 0, 'cortesia' => 0],
            'total_geral' => 0,
            'lista_vendas' => []
        ];

        try {
            // Adiciona a hora final do dia para incluir todas as vendas do período
            $data_fim_ajustada = $data_fim . ' 23:59:59';

            // 1. Obter a lista de todas as vendas e seus itens de uma só vez
            $sql_vendas = "
                SELECT
                    v.id,
                    v.data,
                    v.valor_total,
                    v.forma_pagamento,
                    u.nome AS cliente_nome,
                    u.sobrenome AS cliente_sobrenome,
                    vi.quantidade,
                    vi.preco_unitario,
                    p.nome AS produto_nome
                FROM lojinha_vendas v
                JOIN lojinha_vendas_itens vi ON v.id = vi.venda_id
                JOIN lojinha_produtos p ON vi.produto_id = p.id
                LEFT JOIN usuario u ON v.usuario_id = u.id
                WHERE v.arena_id = ? AND v.data BETWEEN ? AND ?
                ORDER BY v.data DESC, v.id DESC
            ";
            $stmt_vendas = $conn->prepare($sql_vendas);
            $stmt_vendas->execute([$arena_id, $data_inicio, $data_fim_ajustada]);
            $vendas_raw = $stmt_vendas->fetchAll(PDO::FETCH_ASSOC);

            // 2. Processar os dados brutos para preencher os resumos e a lista de vendas
            $vendas_agrupadas = [];
            $resumo_produtos_temp = [];

            foreach ($vendas_raw as $row) {
                $venda_id = $row['id'];

                // Agrupa os itens na lista de vendas
                if (!isset($vendas_agrupadas[$venda_id])) {
                    $vendas_agrupadas[$venda_id] = [
                        'id' => $venda_id,
                        'data' => $row['data'],
                        'valor_total' => $row['valor_total'],
                        'forma_pagamento' => $row['forma_pagamento'],
                        'cliente' => trim($row['cliente_nome'] . ' ' . $row['cliente_sobrenome']) ?: 'Não identificado',
                        'itens' => []
                    ];
                }
                $vendas_agrupadas[$venda_id]['itens'][] = [
                    'nome' => $row['produto_nome'],
                    'quantidade' => $row['quantidade'],
                    'preco_unitario' => $row['preco_unitario']
                ];

                // Calcula o resumo de produtos
                $produto_nome = $row['produto_nome'];
                if (!isset($resumo_produtos_temp[$produto_nome])) {
                    $resumo_produtos_temp[$produto_nome] = ['quantidade' => 0, 'valor' => 0];
                }
                $resumo_produtos_temp[$produto_nome]['quantidade'] += $row['quantidade'];
                $resumo_produtos_temp[$produto_nome]['valor'] += $row['quantidade'] * $row['preco_unitario'];
            }
            $resultado['lista_vendas'] = array_values($vendas_agrupadas);
            $resultado['resumo_produtos'] = $resumo_produtos_temp;

            // 3. Obter o resumo de pagamentos (forma mais eficiente)
            $sql_pagamentos = "
                SELECT forma_pagamento, SUM(valor_total) as total 
                FROM lojinha_vendas 
                WHERE arena_id = ? AND data BETWEEN ? AND ? 
                GROUP BY forma_pagamento
            ";
            $stmt_pagamentos = $conn->prepare($sql_pagamentos);
            $stmt_pagamentos->execute([$arena_id, $data_inicio, $data_fim_ajustada]);
            
            $total_geral = 0;
            while($row = $stmt_pagamentos->fetch(PDO::FETCH_ASSOC)){
                 $resultado['resumo_pagamentos'][$row['forma_pagamento']] = $row['total'];
                 $total_geral += $row['total'];
            }
            $resultado['total_geral'] = $total_geral;
            
            return $resultado;

        } catch (PDOException $e) {
            error_log("Erro ao gerar relatório de vendas: " . $e->getMessage());
            // Retorna a estrutura vazia em caso de erro
            return $resultado; 
        }
    }

        /**
     * Registra uma nova entrada de estoque para um produto.
     *
     * @param array $dados Dados da entrada.
     * @return bool True se bem-sucedido, false em caso de falha.
     */
    public static function registrarEntrada(array $dados)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare(
                "INSERT INTO lojinha_entradas (produto_id, quantidade, custo_unitario, motivo) 
                 VALUES (?, ?, ?, ?)"
            );
            return $stmt->execute([
                $dados['produto_id'],
                $dados['quantidade'],
                $dados['custo_unitario'] ?: null, // Permite custo nulo
                $dados['motivo']
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao registrar entrada de estoque: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca o histórico de entradas de estoque de uma arena.
     *
     * @param int $arena_id O ID da arena.
     * @param int $limit O número de registros a serem retornados.
     * @return array Lista com o histórico de entradas.
     */
    public static function getHistoricoEntradas(int $arena_id, int $limit = 50)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("
                SELECT
                    le.data,
                    le.quantidade,
                    le.custo_unitario,
                    le.motivo,
                    lp.nome AS produto_nome
                FROM lojinha_entradas le
                JOIN lojinha_produtos lp ON le.produto_id = lp.id
                WHERE lp.arena_id = ?
                ORDER BY le.data DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, $arena_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar histórico de entradas: " . $e->getMessage());
            return [];
        }
    }

    

}