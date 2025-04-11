<?php
// --- INÍCIO: Tratamento CORS Preflight (OPTIONS) ---
// Verifica se o método da requisição é OPTIONS
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Define os cabeçalhos CORS necessários para a resposta OPTIONS
    // Permite qualquer origem (por segurança, considere substituir '*' por 'http://localhost:3000' em dev e seu domínio real em produção)
    header("Access-Control-Allow-Origin: *");
    // Métodos permitidos para a URL real (o seu script usa POST)
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    // Cabeçalhos que o navegador pode enviar na requisição real (Content-Type é comum para JSON POST)
    header("Access-Control-Allow-Headers: Content-Type");
    // Opcional: Cachear a resposta preflight por 1 dia (em segundos)
    header("Access-Control-Max-Age: 86400");
    // Define o status HTTP como 204 No Content (resposta padrão de sucesso para OPTIONS)
    http_response_code(204);
    // Interrompe a execução do script PHP aqui, pois só queremos enviar os cabeçalhos para OPTIONS
    header("Content-Type: application/json");

    exit(0);
}

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
    $input_str = $input ?? ''; // Garante que é string ou vazio
    return htmlspecialchars(strip_tags(trim($input_str)));
}
// --- MODIFICADO: Leitura dinâmica dos dados ---
$name_document = !empty($data['name_document']) ? sanitize_input($data['name_document']) : 'Documento Padrão';
// Pega o ID do template enviado pelo frontend
$received_template_id = $data['template_id'] ?? null; // <<< LÊ template_id enviado pelo Vue
// Pega os dados do template enviados pelo frontend
$received_template_data = $data['template_data'] ?? []; // <<< LÊ template_data enviado pelo Vue
// --- Fim da leitura dinâmica ---

// Validação se o ID do template foi recebido
if (empty($received_template_id)) {
    http_response_code(400);
    echo json_encode(["error" => "ID do template não fornecido na requisição."]);
    exit;
}

error_log("[D4Sign Dynamic] Template ID Recebido: " . $received_template_id);

// error_log("[D4Sign Dynamic] Template Data Recebido: " . print_r($received_template_data, true)); // Log opcional
// --- MODIFICADO: Monta o Payload para a D4Sign DINAMICAMENTE ---
//     A API da D4Sign espera que o ID seja a CHAVE aqui dentro de 'templates'
$payload = [
    "name_document" => $name_document,
    "templates" => [
        $received_template_id => $received_template_data // <<< USA O ID RECEBIDO COMO CHAVE
    ],
];
// --- Fim da montagem dinâmica ---
error_log("[D4Sign Dynamic] Payload Enviado (makedocument): " . json_encode($payload));

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
// Bloco CORRIGIDO para substituir o anterior

$curl_error = curl_error($curl); // Captura erro do cURL ANTES de fechar
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE); // Captura status HTTP da resposta da D4Sign
curl_close($curl); // Fecha o cURL aqui

// *** LOG: Resposta Bruta e Status ***
error_log("[D4Sign Debug] Resposta Bruta D4Sign (makedocument): " . $response);
error_log("[D4Sign Debug] Status HTTP D4Sign (makedocument): " . $http_status);
if ($curl_error) {
    error_log("[D4Sign Debug] Erro cURL (makedocument): " . $curl_error);
    // Se houve erro de comunicação cURL, sai
    http_response_code(500);
    echo json_encode(["error" => "Erro de comunicação cURL com API D4Sign", "details" => $curl_error]);
    exit;
}

// Tenta decodificar a resposta JSON recebida da D4Sign
$response_data = json_decode($response, true);

// *** LOG: Dados Decodificados ***
error_log("[D4Sign Debug] Decoded Response Data (\$response_data): " . print_r($response_data, true));

// *** LÓGICA DE VERIFICAÇÃO CORRIGIDA ***
$is_http_error = ($http_status >= 400); // Verifica se D4Sign retornou erro HTTP
$uuid_value = $response_data['uuid'] ?? null; // Pega o valor do UUID ou null
$is_uuid_not_set_or_null = ($uuid_value === null); // Verifica se é null ou não definido
$is_uuid_empty_string = ($uuid_value === ''); // Verifica se é string vazia

// *** LOG: Verificação das Condições ***
error_log("[D4Sign Debug] Check Conditions: HTTP Error? " . ($is_http_error ? 'YES' : 'NO') .
          " | UUID Null/Not Set? " . ($is_uuid_not_set_or_null ? 'YES' : 'NO') .
          " | UUID Empty String? " . ($is_uuid_empty_string ? 'YES' : 'NO'));

// Condição if corrigida: Verifica erro HTTP OU se UUID está efetivamente ausente/inválido
if ($is_http_error || $is_uuid_not_set_or_null || $is_uuid_empty_string) {
    http_response_code(500); // Erro interno do nosso script, pois não conseguimos o UUID
    error_log("[D4Sign Debug] Falha ao obter UUID. Status D4Sign: $http_status. Resposta D4Sign: " . $response);
    // Retorna detalhes para o frontend
    echo json_encode([
        "error" => "Falha ao criar documento na D4Sign (UUID não obtido ou inválido). Verifique os logs do servidor PHP.",
        "d4sign_status" => $http_status,
        "d4sign_response" => $response_data ?? $response // Envia resposta D4Sign completa se possível
        ]);
    exit; // Para a execução
}

// Se chegou aqui, a chamada foi OK e o UUID é válido
$uuid = $uuid_value; // Usa o valor que já validamos
error_log("[D4Sign Dynamic] UUID Obtido com sucesso: " . $uuid); // Log de sucesso

// *** FIM DO BLOCO CORRIGIDO ***


error_log("[D4Sign Dynamic] UUID Obtido: " . $uuid);


// --- MODIFICADO: Adicionar primeiro signatário ---
$signersData1 = [
    'signers' => [
        [
            'email' => 'engeletazevedo@gmail.com',
            'act' => '1',
            // ... outros campos ...
            // Busca o telefone DENTRO dos dados recebidos ($received_template_data)
            'whatsapp_number' => '+55' . sanitize_input($received_template_data['telefone'] ?? ''), // <<< USA $received_template_data
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