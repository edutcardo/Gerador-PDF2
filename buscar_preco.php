<?php
header('Content-Type: application/json');

// Parâmetro de pesquisa
$potencia_gerador = isset($_GET['potencia-gerador']) ? trim($_GET['potencia-gerador']) : '';

// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apiedeltec";

// Conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro na conexão com o banco de dados: ' . $conn->connect_error]));
}

// Verifica se o parâmetro de pesquisa não está vazio
if (empty($potencia_gerador)) {
    echo json_encode(['success' => false, 'message' => 'Parâmetro de pesquisa vazio']);
    exit;
}

// Prepara a consulta SQL
$stmt = $conn->prepare("SELECT palavrasChave, precoDoIntegrador FROM produtos WHERE potenciaGerador LIKE ? LIMIT 10");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Erro na preparação da consulta: ' . $conn->error]);
    exit;
}

$searchPattern = '%' . $potencia_gerador . '%';
$stmt->bind_param("s", $searchPattern);

// Executa a consulta
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Erro na execução da consulta: ' . $stmt->error]);
    exit;
}

$result = $stmt->get_result();

// Verifica se encontrou algum resultado
$resultados = [];
while ($row = $result->fetch_assoc()) {
    $resultados[] = $row;
}

if (count($resultados) > 0) {
    echo json_encode(['success' => true, 'resultados' => $resultados]);
} else {
    echo json_encode(['success' => false, 'message' => 'Nenhum resultado encontrado']);
}

// Fecha a conexão
$stmt->close();
$conn->close();
?>