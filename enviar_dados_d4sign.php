<?php
// Obtém o JSON enviado via POST
$post_data = file_get_contents('php://input');

// Decodifica o JSON para um objeto PHP
$form_data = json_decode($post_data, true);

$url = 'https://secure.d4sign.com.br/api/v1/documents/47c0cf16-5590-4054-8c97-0d00cf7283d8/makedocumentbytemplateword?tokenAPI=live_52449bbd01229efbbeaaf4ce6ee511df175bef8707b57ff35604039dc7714c48&cryptKey=live_crypt_rwJd9DYWs28IYUI1bPhhWDnVtJg5yeb8';

// Configurações do cabeçalho cURL
$options = [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($form_data)
];

// Inicializa a sessão cURL
$curl = curl_init();

// Define as opções para a sessão cURL
curl_setopt_array($curl, $options);

// Executa a requisição e obtém a resposta
$response = curl_exec($curl);

// Fecha a sessão cURL
curl_close($curl);

// Retorna a resposta para o frontend
header('Content-Type: application/json');
echo $response;
?>