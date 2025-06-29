<?php
session_start();
require_once '#_global.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['mensagem'] = ['error', 'Método de requisição inválido.'];
    header('Location: ../agendamento-quadra.php');
    exit;
}

$quadra_id_form = filter_input(INPUT_POST, 'quadra_id_selecionada', FILTER_VALIDATE_INT);
$selected_slots_json = $_POST['selected_slots'] ?? '[]';
$status = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_STRING); // 'tipo' do form vira 'status' na tabela
$usuario_id_agendamento = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT); // Pode ser nulo
$observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_STRING);
$semana = filter_input(INPUT_POST, 'offset_semana', FILTER_VALIDATE_INT);

// Validação básica
if (!$quadra_id_form || empty($selected_slots_json) || !$status) {
    $_SESSION['mensagem'] = ['error', 'Dados obrigatórios do agendamento ausentes.'];
    header('Location: ../agendamento-quadra.php');
    exit;
}

$selected_slots = json_decode($selected_slots_json, true);
if (json_last_error() !== JSON_ERROR_NONE || empty($selected_slots)) {
    $_SESSION['mensagem'] = ['error', 'Formato de horários selecionados inválido.'];
    header('Location: ../agendamento-quadra.php?quadra_id=' . $quadra_id_form);
    exit;
}

$conn = Conexao::pegarConexao();

try {
    $conn->beginTransaction();

    $mapa_dias_semana = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];

    foreach ($selected_slots as $slot) {
        // Prioriza o quadra_id do slot (para visão diária), senão usa o do formulário (visão semanal)
        $quadra_id_para_slot = $slot['quadra_id'] ?? $quadra_id_form;

        if (!$quadra_id_para_slot) continue; // Pula se não houver ID de quadra

        // Busca informações da quadra para calcular o preço do slot individualmente
        $quadra_info = Quadras::getQuadraById($quadra_id_para_slot);
        if (!$quadra_info) continue; // Pula se a quadra não for encontrada

        $valor_base_quadra = (float)($quadra_info['valor_base'] ?? 0);

        $data_obj = new DateTime($slot['data']);
        $dia_semana_num = $data_obj->format('N') - 1; // 0 para segunda, 6 para domingo
        $dia_semana_key = $mapa_dias_semana[$dia_semana_num];

        // Valor adicional será ignorado, pois o valor vem do POST

        $valores_individuais = $_POST['valores_individuais'] ?? [];

        // Construa a chave com o mesmo formato do name no input
        $chave_valor = $slot['data'] . '_' . $slot['hora'];

        // Valor vindo do formulário
        $preco_bruto = $valores_individuais[$chave_valor] ?? '0';
        $preco_sanitizado = preg_replace('/[^\d,\.]/', '', $preco_bruto); // Remove tudo exceto dígitos, vírgulas e pontos
        $preco_formatado = str_replace('.', '', $preco_sanitizado);       // Remove pontos
        $preco_formatado = str_replace(',', '.', $preco_formatado);       // Converte vírgula decimal para ponto
        $preco_slot = floatval($preco_formatado);

        $hora_inicio_obj = new DateTime($slot['hora']);
        $hora_fim_obj = (clone $hora_inicio_obj)->modify('+1 hour');

        $sucesso = Agendamento::criarAgendamento(
            $quadra_id_para_slot,
            $slot['data'],
            $hora_inicio_obj->format('H:i:s'),
            $hora_fim_obj->format('H:i:s'),
            $status,
            $preco_slot,
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
if ($quadra_id_form) $params['quadra_id'] = $quadra_id_form;
// Adiciona o offset da semana ao redirecionamento, se existir e for um valor válido
if ($semana !== null && $semana !== false) {
    $params['semana'] = $semana;
}

$redirect_url .= '?' . http_build_query($params);

header('Location: ' . $redirect_url);
exit;