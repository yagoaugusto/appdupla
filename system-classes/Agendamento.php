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
}