<?php
session_start();
require_once '#_global.php';

header('Content-Type: application/json');

if (!isset($_SESSION['DuplaUserId'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

$usuario_id = $_SESSION['DuplaUserId'];

try {
    // 1. Tenta buscar a análise do cache
    $cached_analysis = Usuario::getAnaliseCache($usuario_id);

    if ($cached_analysis) {
        // Se encontrou um cache válido, retorna ele e encerra a execução.
        echo json_encode(['success' => true, 'analysis' => $cached_analysis]);
        exit;
    }

    // 1. Coleta os dados do jogador
    $stats_basicos = Usuario::posicao_usuario($usuario_id);
    $stats_partidas = Usuario::partidas_usuario($usuario_id);

    if (!$stats_basicos || !$stats_partidas) {
        echo json_encode(['success' => false, 'message' => 'Não foi possível obter os dados do jogador. Jogue algumas partidas primeiro!']);
        exit;
    }

    $rating = round($stats_basicos[0]['rating']);
    $rd = round($stats_basicos[0]['rd']);
    $vol = $stats_basicos[0]['vol'];
    $sexo = $stats_basicos[0]['sexo'] ?? 'Desconhecido';
    $posicao = $stats_basicos[0]['posicao'] ?? 'Desconhecida';
    $partidas_jogadas = $stats_partidas[0]['total_partidas'] ?? 0;
    $partidas_vencidas = $stats_partidas[0]['vitorias'] ?? 0;
    $partidas_perdidas = $partidas_jogadas - $partidas_vencidas;

    // 2. Monta o prompt para a IA
    $prompt = "Analise os seguintes dados de um jogador amador de Beach Tennis:
    - Rating (Pontuação de Habilidade): {$rating}
    - RD (Desvio de Rating - Incerteza da pontuação): {$rd}
    - Volatilidade (σ - Consistência do jogador): {$vol}
    - Total de Partidas Jogadas: {$partidas_jogadas}
    - Total de Vitórias: {$partidas_vencidas}
    - Total de Derrotas: {$partidas_perdidas}
    - Classificação no Ranking: {$posicao}
    - Sexo: {$sexo}

Sua resposta DEVE ser em formato HTML, usando tags <h3>, <p>, <ul> e <li>.
Não use as tags <html>, <head> ou <body> e não coloque a resposta dentro de um bloco de código markdown (```html).
A resposta deve ter EXATAMENTE as seguintes 3 seções:

<h3>📊 Análise Técnica</h3>
<p>Uma análise técnica, mas de fácil entendimento, sobre cada um dos parâmetros (Rating, RD e Volatilidade). Explique o que cada número significa para o nível de jogo atual do atleta.</p>

<h3>🚀 Resumo Desempenho</h3>
<p>Um parágrafo curto, divertido e motivacional que resume o perfil do jogador. Use analogias e um tom bem humorado, como se estivesse conversando com um amigo na praia.</p>

<h3>🔥 Dicas para Evoluir</h3>
<ul>
  <li>Dica 1: Uma dica específica para melhorar com base nos dados.</li>
  <li>Dica 2: Outra dica específica.</li>
  <li>Dica 3: Uma dica geral de mentalidade ou estratégia.</li>
</ul>";

    // 3. Chama a API do Google Gemini
    // 🚨 IMPORTANTE: Substitua o valor abaixo pela sua NOVA chave de API do Google AI Studio.
    $apiKey = 'AIzaSyBTBwXy-VIqWC5pH1p64BT19U3zZCY0_9M';
    $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

    // Esta verificação impede a execução se a chave de API for o valor padrão (placeholder).
    if ($apiKey != 'AIzaSyBTBwXy-VIqWC5pH1p64BT19U3zZCY0_9M') {
        echo json_encode(['success' => false, 'message' => 'A chave de API do Gemini não foi configurada neste arquivo.']);
        exit;
    }


    // O Gemini não tem um "system role" separado como o OpenAI, então combinamos as instruções.
    $system_prompt = 'Você é um analista de dados e técnico de Beach Tennis divertido e motivacional, especializado no sistema de ranking Glicko-2. Seu nome é "Coach Dupla".';
    $full_prompt = $system_prompt . "\n\n" . $prompt;

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $full_prompt]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 800, // Aumentei um pouco para garantir que a resposta não seja cortada
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    // A linha abaixo é para diagnóstico em ambiente local (XAMPP) e pode ser necessária se houver problemas de certificado SSL.
    // Em um servidor de produção com certificados corretos, esta linha deve ser removida.
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        throw new Exception("Erro de cURL: " . $curl_error);
    }

    if ($httpcode != 200) {
        $api_error_data = json_decode($response, true);
        // Tenta pegar a mensagem de erro específica da API do Google
        $error_message = $api_error_data['error']['message'] ?? $response;
        throw new Exception("Erro da API Gemini (HTTP {$httpcode}): " . $error_message);
    }

    $result = json_decode($response, true);
    $ai_content = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Não foi possível gerar a análise no momento.';

    // Limpa a resposta da IA, removendo o encapsulamento de markdown que ela às vezes adiciona.
    $ai_content = preg_replace('/^```(html)?\s*/i', '', $ai_content);
    $ai_content = preg_replace('/\s*```$/', '', $ai_content);
    $ai_content = trim($ai_content);

    // 4. Salva a nova análise no cache para uso futuro
    Usuario::setAnaliseCache($usuario_id, $ai_content);

    echo json_encode(['success' => true, 'analysis' => $ai_content]);
} catch (Exception $e) {
    error_log("Error in analise-jogador.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao processar a solicitação: ' . $e->getMessage()]);
}