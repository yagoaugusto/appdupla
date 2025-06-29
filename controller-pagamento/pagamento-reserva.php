<?php
/**
 * Ponto de entrada para criação de uma preferência de pagamento de reserva no Mercado Pago.
 *
 * Este script recebe os dados do formulário de confirmação de agendamento,
 * valida as informações, cria uma reserva pendente no banco de dados,
 * e gera uma preferência de pagamento no Mercado Pago, retornando a URL
 * de checkout para o frontend.
 */

// Inclui o autoloader do Composer e as configurações globais (conexão, constantes, etc.)
require_once '../vendor/autoload.php';
require_once '#_global.php'; // Usa a conexão local que já inclui o #_global

session_start();

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json');

try {
    // --- 1. Validação dos Dados de Entrada ---
    $usuario_id = $_SESSION['DuplaUserId'] ?? null;
    $slots_json = $_POST['slots'] ?? null;
    $cupom_code = isset($_POST['cupom']) ? strtoupper(trim($_POST['cupom'])) : null;

    if (!$usuario_id || !$slots_json) {
        throw new Exception('Dados insuficientes para processar o pagamento.');
    }

    $slots = json_decode($slots_json, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($slots)) {
        throw new Exception('Formato de horários selecionados inválido.');
    }

    // --- 2. Configuração do SDK do Mercado Pago ---
    // A constante MP_ACCESS_TOKEN deve ser definida no seu arquivo de configuração.
    MercadoPago\SDK::setAccessToken(MP_ACCESS_TOKEN);

    // --- 3. Cálculo de Valores (Segurança no Backend) ---
    $valor_total = 0;
    foreach ($slots as $slot) {
        $valor_total += (float)$slot['preco'];
    }

    // Lógica de aplicação de cupom (exemplo)
    $desconto = 0;
    if ($cupom_code === 'DUPLA10') {
        $desconto = $valor_total * 0.10;
    }
    $valor_final = $valor_total - $desconto;

    // --- 4. Criação da Reserva Pendente no Banco de Dados ---
    $reserva_pendente_id = Agendamento::criarReservaPendente($usuario_id, $slots_json, $valor_final, $cupom_code);
    if (!$reserva_pendente_id) {
        throw new Exception('Ocorreu um erro ao registrar sua reserva. Tente novamente.');
    }

    // --- 5. Criação da Preferência de Pagamento no Mercado Pago ---
    $preference = new MercadoPago\Preference();
    $items = [];

    // Adiciona cada slot como um item detalhado na preferência
    foreach ($slots as $slot) {
        $item = new MercadoPago\Item();
        $item->title = "Reserva: " . $slot['quadra_nome'] . " - " . date('d/m/Y', strtotime($slot['data'])) . " às " . $slot['horario'];
        $item->quantity = 1;
        $item->unit_price = (float)$slot['preco'];
        $item->currency_id = "BRL";
        $items[] = $item;
    }

    // Adiciona o desconto como um item com valor negativo, se aplicável
    if ($desconto > 0) {
        $item_desconto = new MercadoPago\Item();
        $item_desconto->title = "Desconto Cupom " . htmlspecialchars($cupom_code);
        $item_desconto->quantity = 1;
        $item_desconto->unit_price = - (float)$desconto;
        $item_desconto->currency_id = "BRL";
        $items[] = $item_desconto;
    }

    $preference->items = $items;

    // --- 6. Configuração de URLs e Referências ---
    // A constante APP_BASE_URL deve ser definida no seu arquivo de configuração.
    $preference->back_urls = [
        "success" => APP_BASE_URL . "/notificacao-reserva.php?status=success&reserva_id=" . $reserva_pendente_id,
        "failure" => APP_BASE_URL . "/notificacao-reserva.php?status=failure&reserva_id=" . $reserva_pendente_id,
        "pending" => APP_BASE_URL . "/notificacao-reserva.php?status=pending&reserva_id=" . $reserva_pendente_id,
    ];
    $preference->auto_return = "approved";

    // URL do Webhook que o Mercado Pago irá notificar
    $preference->notification_url = APP_BASE_URL . "/controller-pagamento/webhook-reserva.php";

    // Vincula o pagamento à nossa reserva pendente no banco de dados
    $preference->external_reference = $reserva_pendente_id;

    // --- 7. Salvar Preferência e Retornar URL ---
    $preference->save();

    // Verifica se a URL de checkout foi gerada
    if (empty($preference->init_point)) {
        throw new Exception('Não foi possível obter a URL de pagamento do Mercado Pago.');
    }

    // Atualiza a reserva pendente com o ID da preferência para referência futura
    Agendamento::atualizarReservaPendenteComPreferenciaId($reserva_pendente_id, $preference->id);

    // Retorna a URL de checkout para o frontend
    echo json_encode(['status' => 'success', 'redirect_url' => $preference->init_point]);

} catch (Exception $e) {
    // Em caso de erro, loga a exceção e retorna uma mensagem de erro genérica
    error_log("Erro ao criar preferência de pagamento: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Não foi possível iniciar o pagamento. Por favor, tente novamente mais tarde.']);
}