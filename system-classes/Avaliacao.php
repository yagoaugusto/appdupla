<?php
class Avaliacao {

    /**
     * Verifica se um usuário já avaliou uma reserva específica.
     * @param int $reserva_id O ID da reserva.
     * @param int $usuario_id O ID do usuário.
     * @return bool True se já existe uma avaliação, false caso contrário.
     */
    public static function jaAvaliou($reserva_id, $usuario_id) {
        try {
            $conn = Conexao::pegarConexao();
            $stmt = $conn->prepare("SELECT id FROM avaliacoes_reservas WHERE reserva_id = ? AND usuario_id = ?");
            $stmt->execute([$reserva_id, $usuario_id]);
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            error_log("Erro ao verificar avaliação: " . $e->getMessage());
            return false;
        }
    }

// ... dentro da classe Avaliacao

public static function salvar(array $dados) {
    try {
        $conn = Conexao::pegarConexao();
        $sql = "INSERT INTO avaliacoes_reservas (reserva_id, usuario_id, qualidade_quadra, pontualidade_disponibilidade, atendimento_suporte, ambiente_arena, facilidade_reserva, comentario) 
                VALUES (:reserva_id, :usuario_id, :qualidade_quadra, :pontualidade_disponibilidade, :atendimento_suporte, :ambiente_arena, :facilidade_reserva, :comentario)";
        
        $stmt = $conn->prepare($sql);
        
        // **MUDANÇA AQUI**: Removemos a divisão por 2. Os valores agora são salvos como inteiros (1-5).
        return $stmt->execute([
            ':reserva_id' => $dados['reserva_id'],
            ':usuario_id' => $dados['usuario_id'],
            ':qualidade_quadra' => $dados['qualidade_quadra'] ?? 0,
            ':pontualidade_disponibilidade' => $dados['pontualidade_disponibilidade'] ?? 0,
            ':atendimento_suporte' => $dados['atendimento_suporte'] ?? 0,
            ':ambiente_arena' => $dados['ambiente_arena'] ?? 0,
            ':facilidade_reserva' => $dados['facilidade_reserva'] ?? 0,
            ':comentario' => $dados['comentario']
        ]);
    } catch (Exception $e) {
        error_log("Erro ao salvar avaliação: " . $e->getMessage());
        return false;
    }
}
}
?>