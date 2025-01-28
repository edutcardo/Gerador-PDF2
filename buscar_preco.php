<?php
header('Content-Type: application/json');

// Parâmetros de pesquisa
$potencia = isset($_GET['potencia']) ? floatval($_GET['potencia']) : null;
$estrutura = isset($_GET['estrutura']) ? $_GET['estrutura'] : '';

// Configurações do banco de dados
$servername = "srv1781.hstgr.io";
$username = "u345670158_eduardotcardo";
$password = "Rtz6ngqr@";
$dbname = "u345670158_apiedeltec";

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro na conexão com o banco de dados: ' . $conn->connect_error]));
}

// Define a query base
$sql = "SELECT titulo, precoDoIntegrador, codProd, marca, fabricante, potenciaInversor, potenciaModulo, tensaoSaida, componentes, potenciaGerador 
        FROM produtos";

// Modifica a lógica com base na estrutura selecionada
if (strtoupper($estrutura) === 'SOLO') {
    // Apenas filtra por inversores
    $sql .= " WHERE titulo REGEXP '[[:<:]]75kw[[:>:]]' LIMIT 200";
    $stmt = $conn->prepare($sql);
} else {
    // Define margem de tolerância e prepara 'where' pela potencia verbal
    $tolerancia = 0.5;
    $limite_inferior = $potencia - $tolerancia;
    $limite_superior = $potencia + $tolerancia;

    $sql .= " WHERE potenciaGerador BETWEEN ? AND ?";
    
    if (!empty($estrutura)) {
        $sql .= " AND estrutura = ?";
    }

    // Prepara declaração, passando critérios de limitação aplicáveis
    $stmt = $conn->prepare($sql);
    if (!empty($estrutura)) {
        $stmt->bind_param("dds", $limite_inferior, $limite_superior, $estrutura);
    } else {
        $stmt->bind_param("dd", $limite_inferior, $limite_superior);
    }
}

// Verifica se o statement foi preparado corretamente
if (!$stmt) {
    die(json_encode(['success' => false, 'message' => 'Erro na preparação da consulta: ' . $conn->error]));
}

// Executa a query
$stmt->execute();
$result = $stmt->get_result();

// Obtem e estruturando resultados
$resultados = [];
while ($row = $result->fetch_assoc()) {
    $resultados[] = $row;
}

// Envia resultados como JSON
if (count($resultados) > 0) {
    echo json_encode(['success' => true, 'resultados' => $resultados]);
} else {
    echo json_encode(['success' => false, 'message' => 'Nenhum resultado encontrado']);
}

// Conclui e libera recursos do banco de dados
$stmt->close();
$conn->close();
?>