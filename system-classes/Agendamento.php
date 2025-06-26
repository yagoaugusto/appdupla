<?php

class Agendamento {
    /**
     * Cria um novo agendamento para uma quadra.
     *
     * @param int $quadra_id O ID da quadra.
     * @param string $data A data do agendamento (formato YYYY-MM-DD).
     * @param string $hora_inicio A hora de início do agendamento (formato HH:MM:SS).
     * @param string $hora_fim A hora de fim do agendamento (formato HH:MM:SS).
     * @param string $status O tipo de agendamento (reservado, bloqueado, aula, dayuse).
     * @param float $preco O preço calculado para o agendamento.
     * @param int|null $usuario_id O ID do usuário associado (opcional).
     * @param string|null $cliente_nome O nome do cliente (opcional).
     * @param string|null $observacoes Observações adicionais (opcional).
     * @return bool True em caso de sucesso, false caso contrário.
     */
    public static function criarAgendamento($quadra_id, $data, $hora_inicio, $hora_fim, $status, $preco, $usuario_id = null, $observacoes = null) {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("
                INSERT INTO agenda_quadras (quadra_id, data, hora_inicio, hora_fim, status, preco, usuario_id, observacoes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([$quadra_id, $data, $hora_inicio, $hora_fim, $status, $preco, $usuario_id, $observacoes]);
        } catch (PDOException $e) {
            error_log("Erro ao criar agendamento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca agendamentos para uma quadra em um período específico.
     *
     * @param int $quadra_id O ID da quadra.
     * @param string $data_inicio A data de início do período (formato YYYY-MM-DD).
     * @param string $data_fim A data de fim do período (formato YYYY-MM-DD).
     * @return array Lista de agendamentos como arrays associativos.
     */
    public static function getAgendamentosPorQuadraNoPeriodo($quadra_id, $data_inicio, $data_fim) {
        try {
            $conn = Conexao::pegarConexao();
            // Seleciona também o nome do usuário, se houver
            $sql = "SELECT a.*, u.nome as cliente_nome FROM agenda_quadras a 
                    LEFT JOIN usuario u ON a.usuario_id = u.id
                    WHERE a.quadra_id = ? AND a.data BETWEEN ? AND ?
                    ORDER BY a.data, a.hora_inicio";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$quadra_id, $data_inicio, $data_fim]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar agendamentos por quadra e período: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca os detalhes de um agendamento específico pelo seu ID.
     *
     * @param int $agendamento_id O ID do agendamento.
     * @return array|false Um array associativo com os dados do agendamento ou false se não encontrado.
     */
    public static function getAgendamentoById($agendamento_id) {
        try {
            $conn = Conexao::pegarConexao();
            $sql = "SELECT a.*, u.nome as cliente_nome, u.telefone as cliente_telefone 
                    FROM agenda_quadras a
                    LEFT JOIN usuario u ON a.usuario_id = u.id
                    WHERE a.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$agendamento_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar agendamento por ID: " . $e->getMessage());
            return false;
        }
    }
}