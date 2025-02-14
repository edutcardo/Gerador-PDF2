<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Recupera o JSON enviado pelo JavaScript
$post_data = file_get_contents('php://input');
$data = json_decode($post_data, true);

// Verifica se o JSON foi decodificado corretamente
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON data"]);
    exit;
}

// Função para sanitizar entrada de dados
function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Valida e sana as variáveis recebidas
$name_document = !empty($data['name_document']) ? sanitize_input($data['name_document']) : null;
$template_data = $data['templates']['MTY0MzI1'] ?? [];

$required_fields = ['nome', 'cpf', 'logradouro', 'nr', 'cep', 'cidade', 'telefone', 'totalconsumo'];

// Validação básica dos campos
foreach ($required_fields as $field) {
    if (empty($template_data[$field])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing field: $field"]);
        exit;
    }
    // Sanitiza os valores
    $template_data[$field] = sanitize_input($template_data[$field]);
}

// Prepara os dados para envio usando a API da D4sign
$payload = [
    "name_document" => $name_document,
    "templates" => [
        "MTY0MzI1" => [
            "nome" => $template_data['nome'],
            "cpf" => $template_data['cpf'],
            "logradouro" => $template_data['logradouro'],
            "nr" => $template_data['nr'],
            "cep" => $template_data['cep'],
            "cidade" => $template_data['cidade'],
            "kwp" => "teste",  // Substitua conforme necessário
            "qtdmodulos" => $template_data['qtdmodulos'] ?? "1",
            "kwhmes" => "teste",
            "_" => "teste",
            "uc" => "teste",
            "cpf_cnpj" => "teste",
            "consumo" => "teste",
            "data" => "teste",
            "id" => "teste",
            "nomeficha" => "teste",
            "cpfficha" => "teste",
            "logradourouc" => "teste",
            "nruc" => "teste",
            "cepuc" => "teste",
            "cidadeuc" => sanitize_input($template_data['cidade']),
            "telefone" => $template_data['telefone'],
            "responsável" => "teste",
            "uc2" => "teste",
            "logradouro2" => "teste",
            "consumo2" => "teste",
            "uc3" => "teste",
            "logradouro3" => "teste",
            "consumo3" => "teste",
            "uc4" => "teste",
            "logradouro4" => "teste",
            "consumo4" => "teste",
            "uc5" => "teste",
            "logradouro5" => "teste",
            "consumo5" => "teste",
            "uc6" => "teste",
            "logradouro6" => "teste",
            "consumo6" => "teste",
            "uc7" => "teste",
            "logradouro7" => "teste",
            "consumo7" => "teste",
            "totalconsumo" => $template_data['totalconsumo']
        ]
    ]
];

// URL da API da D4sign
$url = 'https://secure.d4sign.com.br/api/v1/documents/88b7ed6f-368d-4801-85d1-56f94b75f7cb/makedocumentbytemplateword?tokenAPI=live_52449bbd01229efbbeaaf4ce6ee511df175bef8707b57ff35604039dc7714c48&cryptKey=live_crypt_rwJd9DYWs28IYUI1bPhhWDnVtJg5yeb8';

$options = [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload)
];

// Inicializa a sessão cURL
$curl = curl_init();
curl_setopt_array($curl, $options);

// Executa a requisição e armazena a resposta
$response = curl_exec($curl);

// Verifica se ocorreu algum erro na execução do cURL
if (curl_errno($curl)) {
    $error_msg = curl_error($curl);
    curl_close($curl);
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