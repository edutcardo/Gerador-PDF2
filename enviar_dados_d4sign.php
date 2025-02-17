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

// Preparar dados do documento
$name_document = !empty($data['name_document']) ? sanitize_input($data['name_document']) : 'Documento Padrão';
$template_data = $data['templates']['MTY0MzI1'] ?? [];

$payload = [
    "name_document" => $name_document,
    "templates" => ["MTY0MzI1" => $template_data],
];

// URL e credenciais da primeira API
$url_create_document = "https://secure.d4sign.com.br/api/v1/documents/88b7ed6f-368d-4801-85d1-56f94b75f7cb/makedocumentbytemplateword";
$tokenAPI = "live_52449bbd01229efbbeaaf4ce6ee511df175bef8707b57ff35604039dc7714c48";
$cryptKey = "live_crypt_rwJd9DYWs28IYUI1bPhhWDnVtJg5yeb8";

$options = [
    CURLOPT_URL => "$url_create_document?tokenAPI=$tokenAPI&cryptKey=$cryptKey",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
];

$curl = curl_init();
curl_setopt_array($curl, $options);
$response = curl_exec($curl);

if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    curl_close($curl);
    http_response_code(500);
    echo json_encode(["error" => "Erro ao criar documento via API", "details" => $error_msg]);
    exit;
}
curl_close($curl);

$response_data = json_decode($response, true);

if (!isset($response_data['uuid'])) {
    http_response_code(500);
    echo json_encode(["error" => "UUID não retornado pela API"]);
    exit;
}

// Adicionar primeiro signatário
$uuid = $response_data['uuid'];
$signersData1 = [
    'signers' => [
        [
            'email' => 'engeletazevedo@gmail.com',
            'act' => '1',
            'foreign' => '0',
            'certificadoicpbr' => '0',
            'assinatura_presencial' => '0',
            'docauthandselfie' => '0',
            'whatsapp_number' => '+55' . sanitize_input($template_data['telefone']),
        ]
    ]
];

$url_add_signer1 = "https://secure.d4sign.com.br/api/v1/documents/$uuid/createlist?tokenAPI=$tokenAPI&cryptKey=$cryptKey";

$options = [
    CURLOPT_URL => $url_add_signer1,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($signersData1),
];

$curl = curl_init();
curl_setopt_array($curl, $options);
$responseSigners1 = curl_exec($curl);

if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    curl_close($curl);
    http_response_code(500);
    echo json_encode(["error" => "Erro ao adicionar primeiro signatário via API", "details" => $error_msg]);
    exit;
}
curl_close($curl);

// Adicionar segundo signatário (Arthur)
$signersData2 = [
    'signers' => [
        [
            'email' => 'arthur@canalverdeenergia.com.br',
            'act' => '1',
            'foreign' => '0',
            'certificadoicpbr' => '0',
            'assinatura_presencial' => '0',
            'docauthandselfie' => '0'
        ]
    ]
];

$url_add_signer2 = "https://secure.d4sign.com.br/api/v1/documents/$uuid/createlist?tokenAPI=$tokenAPI&cryptKey=$cryptKey";

$options = [
    CURLOPT_URL => $url_add_signer2,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($signersData2),
];

$curl = curl_init();
curl_setopt_array($curl, $options);
$responseSigners2 = curl_exec($curl);

if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    curl_close($curl);
    http_response_code(500);
    echo json_encode(["error" => "Erro ao adicionar signatário Arthur via API", "details" => $error_msg]);
    exit;
}
curl_close($curl);

// Enviar documento aos signatários
$sendToSignersData = [
    "message" => "Segue o contrato:",
    "skip_email" => "0",
    "workflow" => "0",
];

$url_send_document = "https://secure.d4sign.com.br/api/v1/documents/$uuid/sendtosigner?tokenAPI=$tokenAPI&cryptKey=$cryptKey";

$options = [
    CURLOPT_URL => $url_send_document,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($sendToSignersData),
];

$curl = curl_init();
curl_setopt_array($curl, $options);
$responseSend = curl_exec($curl);

if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    curl_close($curl);
    http_response_code(500);
    echo json_encode(["error" => "Erro ao enviar documento aos signatários via API", "details" => $error_msg]);
    exit;
}
curl_close($curl);

// Retorna resultado completo
echo json_encode([
    "uuid" => $uuid,
    "results" => [
        "primeiroSignatário" => json_decode($responseSigners1),
        "segundoSignatário" => json_decode($responseSigners2),
        "envioDocumento" => json_decode($responseSend),
    ]
]);
?>