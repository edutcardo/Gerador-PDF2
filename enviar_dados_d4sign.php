<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$post_data = file_get_contents('php://input');
$data = json_decode($post_data, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON data"]);
    exit;
}

function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Prepara os dados para criar o documento
$name_document = !empty($data['name_document']) ? sanitize_input($data['name_document']) : null;
$template_data = $data['templates']['MTY0MzI1'] ?? [];

$payload = [
    "name_document" => $name_document,
    "templates" => [
        "MTY0MzI1" => $template_data
    ]
];

// URL para criar o documento
$url_create_document = 'https://secure.d4sign.com.br/api/v1/documents/88b7ed6f-368d-4801-85d1-56f94b75f7cb/makedocumentbytemplateword';
$tokenAPI = "live_52449bbd01229efbbeaaf4ce6ee511df175bef8707b57ff35604039dc7714c48";
$cryptKey = "live_crypt_rwJd9DYWs28IYUI1bPhhWDnVtJg5yeb8";

$options = [
    CURLOPT_URL => "$url_create_document?tokenAPI=$tokenAPI&cryptKey=$cryptKey",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload)
];

$curl = curl_init();
curl_setopt_array($curl, $options);
$response = curl_exec($curl);

if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    curl_close($curl);
    http_response_code(500);
    echo json_encode(["error" => "cURL error: $error_msg"]);
    exit;
}

curl_close($curl);
$response_data = json_decode($response, true);

if (!isset($response_data['uuid'])) {
    http_response_code(500);
    echo json_encode(["error" => "UUID não encontrado na resposta da API D4sign"]);
    exit;
}

// O documento foi criado, agora adicionamos o signatário
$uuid = $response_data['uuid'];

// Dados do signatário
$signersData = [
    'signers' => [
        [
            'email' => 'engeletazevedo@gmail.com',
            'act' => '1',
            'foreign' => '0',
            'certificadoicpbr' => '0',
            'assinatura_presencial' => '0',
            'docauthandselfie' => '0',
            'whatsapp_number' => '+5544997539777'
        ]
    ]
];

// URL para adicionar o signatário
$url_add_signer = "https://secure.d4sign.com.br/api/v1/documents/$uuid/createlist?tokenAPI=$tokenAPI&cryptKey=$cryptKey";

$options = [
    CURLOPT_URL => $url_add_signer,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($signersData)
];

$curl = curl_init();
curl_setopt_array($curl, $options);
$responseSigners = curl_exec($curl);

if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    curl_close($curl);
    http_response_code(500);
    echo json_encode(["error" => "Erro na API de adicionar signatário", "details" => $error_msg]);
    exit;
}

curl_close($curl);

// Retorna o resultado da operação
echo json_encode(["uuid" => $uuid, "signersResult" => json_decode($responseSigners)]);
?>