<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
// Recupera o JSON enviado pelo JavaScript
$post_data = file_get_contents('php://input');

// Decodifica o JSON para uma matriz PHP
$data = json_decode($post_data, true);

// Verifica se o JSON foi decodificado corretamente
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400); // Resposta de erro Bad Request
    echo json_encode(["error" => "Invalid JSON data"]);
    exit;
}

// URL da API da D4sign
$url = 'https://secure.d4sign.com.br/api/v1/documents/88b7ed6f-368d-4801-85d1-56f94b75f7cb/makedocumentbytemplateword?tokenAPI=live_52449bbd01229efbbeaaf4ce6ee511df175bef8707b57ff35604039dc7714c48&cryptKey=live_crypt_rwJd9DYWs28IYUI1bPhhWDnVtJg5yeb8';

$options = [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data)
];

// Inicializa a sessão cURL
$curl = curl_init();

// Define as opções para a sessão cURL
curl_setopt_array($curl, $options);

// Executa a requisição e armazena a resposta
$response = curl_exec($curl);

// Verifica se ocorreu algum erro na execução do cURL
if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    curl_close($curl);
    // Envia resposta indicando erro ao cliente
    http_response_code(500);
    echo json_encode(["error" => "cURL error: $error_msg"]);
    exit;
}

// Fecha a sessão cURL
curl_close($curl);

// Retorna a resposta da API D4sign para o cliente
header('Content-Type: application/json');
echo $response;
?>