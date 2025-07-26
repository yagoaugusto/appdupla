<?php

class AsaasService
{
    private static $apiKey;
    private static $baseUrl;

    /**
     * Inicializa o serviço com as credenciais.
     */
    private static function init()
    {
        self::$apiKey = ASAAS_API_KEY;
        // ==========================================================
        // CORREÇÃO 1: O endereço base da API foi corrigido.
        // ==========================================================
        self::$baseUrl = (ASAAS_MODE == 'production') 
            ? 'https://api.asaas.com/v3' 
            : 'https://sandbox.asaas.com/api/v3';
    }

    /**
     * Método privado para executar as requisições para a API.
     */
    private static function request($method, $endpoint, $data = [])
    {
        self::init();

        $url = self::$baseUrl . $endpoint;
$headers = [
            'Content-Type: application/json',
            'access_token: ' . self::$apiKey,
            'User-Agent: DuplaApp' // Adicionamos o cabeçalho User-Agent obrigatório
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded_response = json_decode($response, true);

        if ($http_code < 200 || $http_code >= 300) {
            $error_message = "Erro na API Asaas (HTTP {$http_code}): " . ($decoded_response['errors'][0]['description'] ?? $response);
            error_log($error_message);
            throw new Exception($error_message);
        }

        return $decoded_response;
    }

    /**
     * Faz uma chamada simples à API para verificar a autenticação e conexão.
     */
    public static function verificarConexao()
    {
        // ==========================================================
        // CORREÇÃO 2: O endpoint de verificação foi corrigido.
        // ==========================================================
        return self::request('GET', '/myAccount');
    }

    // ... (outros métodos como buscarCliente, criarCliente, etc.) ...
}