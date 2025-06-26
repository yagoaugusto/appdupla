<?php
session_start();
require_once '#_global.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensagem'] = ['error', 'Método de requisição inválido.'];
    header('Location: ../agendamento-quadra.php');
    exit;
}

$quadra_id = filter_input(INPUT_POST, 'quadra_id_selecionada', FILTER_VALIDATE_INT);
$selected_slots_json = $_POST['selected_slots'] ?? '[]';
$status = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING); // 'tipo' do form vira 'status' na tabela
$usuario_id_agendamento = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT); // Pode ser nulo
$observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_STRING);

// Validação básica
if (!$quadra_id || empty($selected_slots_json) || !$status) {
    $_SESSION['mensagem'] = ['error', 'Dados obrigatórios do agendamento ausentes.'];
    header('Location: ../agendamento-quadra.php');
    exit;
}

$selected_slots = json_decode($selected_slots_json, true);
if (json_last_error() !== JSON_ERROR_NONE || empty($selected_slots)) {
    $_SESSION['mensagem'] = ['error', 'Formato de horários selecionados inválido.'];
    header('Location: ../agendamento-quadra.php?quadra_id=' . $quadra_id);
    exit;
}

$conn = Conexao::pegarConexao();

try {
    $conn->beginTransaction();

    $quadra_info = Quadras::getQuadraById($quadra_id);
    $valor_base_quadra = (float)($quadra_info['valor_base'] ?? 0);

    $mapa_dias_semana = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];

    foreach ($selected_slots as $slot) {
        $data_obj = new DateTime($slot['data']);
        $dia_semana_num = $data_obj->format('N') - 1; // 0 para segunda, 6 para domingo
        $dia_semana_key = $mapa_dias_semana[$dia_semana_num];

        $valor_adicional = Quadras::getValorAdicionalPorSlot($quadra_id, $dia_semana_key, $slot['hora']);
        $preco_final = $valor_base_quadra + $valor_adicional;

        $hora_inicio_obj = new DateTime($slot['hora']);
        $hora_fim_obj = (clone $hora_inicio_obj)->modify('+1 hour');

        $sucesso = Agendamento::criarAgendamento(
            $quadra_id,
            $slot['data'],
            $hora_inicio_obj->format('H:i:s'),
            $hora_fim_obj->format('H:i:s'),
            $status,
            $preco_final,
            $usuario_id_agendamento,
            $observacoes
        );

        if (!$sucesso) {
            throw new Exception("Falha ao agendar o horário das " . $slot['hora'] . " em " . $slot['data']);
        }
    }

    $conn->commit();
    $_SESSION['mensagem'] = ['success', 'Agendamento(s) salvo(s) com sucesso!'];

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Erro ao salvar agendamento: " . $e->getMessage());
    $_SESSION['mensagem'] = ['error', 'Ocorreu um erro inesperado ao salvar o agendamento.'];
}

// Redireciona de volta para a página de agendamento, mantendo os filtros de arena e quadra
$redirect_url = '../agendamento-quadra.php';
$arena_id = filter_input(INPUT_POST, 'arena_id', FILTER_VALIDATE_INT);

$params = [];
if ($arena_id) $params['arena_id'] = $arena_id;
if ($quadra_id) $params['quadra_id'] = $quadra_id;

$redirect_url .= '?' . http_build_query($params);

header('Location: ' . $redirect_url);
exit;