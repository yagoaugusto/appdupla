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
require_once 'conexao.php'; // Usa a conexão local que já inclui o #_global

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

    // --- 4.5. Obter e preparar dados do pagador ---
    $usuario_info = Usuario::getUsuarioInfoById($usuario_id);
    if (!$usuario_info) {
        throw new Exception('Informações do usuário pagador não encontradas.');
    }

    // Preparar dados do pagador
    $payer_name = $usuario_info['nome'] ?? '';
    $payer_surname = $usuario_info['sobrenome'] ?? '';
    $payer_email = $usuario_info['email'] ?? '';
    $payer_phone_raw = $usuario_info['telefone'] ?? '';
    $payer_cpf = preg_replace('/\D/', '', $usuario_info['cpf'] ?? ''); // Remove caracteres não numéricos

    // Validações mínimas para o Mercado Pago
    if (empty($payer_email) || !filter_var($payer_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('O e-mail do pagador é inválido.');
    }
    if (empty($payer_cpf) || strlen($payer_cpf) !== 11) {
        throw new Exception('O CPF do pagador é inválido ou não foi informado. É necessário para o pagamento.');
    }

    // --- Tratamento do Telefone ---
    // O telefone no banco está salvo com DDI (ex: 5598...).
    // A API do Mercado Pago espera o DDD e o número separadamente.
    // Esta lógica limpa e formata o número corretamente.
    $clean_phone = preg_replace('/\D/', '', $payer_phone_raw);

    // Se o número começar com o DDI 55 e for um número de celular (13 dígitos) ou fixo (12 dígitos),
    // removemos o DDI para ficar apenas com DDD + número.
    if (strlen($clean_phone) > 11 && strpos($clean_phone, '55') === 0) {
        $clean_phone = substr($clean_phone, 2);
    }

    // Agora, com o número limpo (formato DDD + número), separamos o código de área.
    // Ex: 98991668283 -> area_code: 98, number: 991668283
    $phone_data = ["area_code" => substr($clean_phone, 0, 2), "number" => substr($clean_phone, 2)];


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

    // Adiciona os dados do pagador à preferência
    $payer = new MercadoPago\Payer();
    $payer->name = $payer_name;
    $payer->surname = $payer_surname;
    $payer->email = $payer_email;
    $payer->phone = [
        "area_code" => $phone_data['area_code'],
        "number" => $phone_data['number']
    ];
    $payer->identification = ["type" => "CPF", "number" => $payer_cpf];

    $preference->payer = $payer;

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
    // Retorna a mensagem da exceção para o frontend, para que o usuário saiba o que aconteceu.
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}