<?php

class Agendamento
{
    /**
     * Cria um registro de reserva pendente no banco de dados.
     * @param int $usuario_id
     * @param string $slots_json
     * @param float $valor_total
     * @param string|null $cupom
     * @return string|false O ID da reserva pendente inserida ou false em caso de erro.
     */
    public static function criarReservaPendente($usuario_id, $slots_json, $valor_total, $cupom = null)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare(
                "INSERT INTO reservas_pendentes (usuario_id, slots_json, valor_total, cupom_usado) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$usuario_id, $slots_json, $valor_total, $cupom]);
            return $conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erro ao criar reserva pendente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza uma reserva pendente com o ID da preferência de pagamento.
     * @param int $reserva_id
     * @param string $preference_id
     * @return bool
     */
    public static function atualizarReservaPendenteComPreferenciaId($reserva_id, $preference_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("UPDATE reservas_pendentes SET payment_preference_id = ? WHERE id = ?");
            return $stmt->execute([$preference_id, $reserva_id]);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar ID de preferência da reserva: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca uma reserva pendente pelo seu ID.
     * @param int $id
     * @return array|false
     */
    public static function getReservaPendentePorId($id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("SELECT * FROM reservas_pendentes WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar reserva pendente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Insere um agendamento final na tabela `agenda_quadras`.
     * @return bool
     */
    public static function inserirAgendamento($quadra_id, $usuario_id, $data, $hora_inicio, $hora_fim, $status, $preco, $pagamento_id, $observacoes = '')
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare(
                "INSERT INTO agenda_quadras (quadra_id, usuario_id, data, hora_inicio, hora_fim, status, preco, pagamento_id, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            return $stmt->execute([$quadra_id, $usuario_id, $data, $hora_inicio, $hora_fim, $status, $preco, $pagamento_id, $observacoes]);
        } catch (PDOException $e) {
            error_log("Erro ao inserir agendamento final: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca todas as reservas de um usuário.
     * @param int $usuario_id
     * @return array
     */
    public static function getReservasByUsuarioId($usuario_id)
    {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare(
                "SELECT
                    aq.*,
                    q.nome as quadra_nome,
                    a.titulo as arena_titulo,
                    a.bandeira as arena_bandeira
                FROM
                    agenda_quadras aq
                JOIN
                    quadras q ON aq.quadra_id = q.id
                JOIN
                    arenas a ON q.arena_id = a.id
                WHERE
                    aq.usuario_id = ? AND aq.status = 'reservado'
                ORDER BY
                    aq.data DESC,
                    aq.id DESC"
            );
            $stmt->execute([$usuario_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar reservas do usuário: " . $e->getMessage());
            return [];
        }
    }


/**Add commentMore actions
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

    /**
     * Cancela (exclui) um agendamento específico pelo seu ID.
     *
     * @param int $agendamento_id O ID do agendamento a ser cancelado.
     * @return bool True se a exclusão foi bem-sucedida, false caso contrário.
     */
    public static function cancelarAgendamento($agendamento_id) {

        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("DELETE FROM agenda_quadras WHERE id = ?");
            $stmt->execute([$agendamento_id]);
            return $stmt->rowCount() > 0; // Retorna true se uma linha foi afetada

        } catch (PDOException $e) {
            error_log("Erro ao cancelar agendamento: " . $e->getMessage());
            return false;
        }
    }
}