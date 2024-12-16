<?php
header('Content-Type: application/json');

// Parâmetros de pesquisa
$potencia_gerador = isset($_GET['potencia-gerador']) ? $_GET['potencia-gerador'] : '';
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

// Criação da query SQL
$sql = "SELECT titulo, precoDoIntegrador, codProd, marca, fabricante, potenciaInversor, potenciaModulo, tensaoSaida, componentes, potenciaGerador 
        FROM produtos 
        WHERE potenciaGerador LIKE ?";
$params = ["%$potencia_gerador%"];

// Adiciona o filtro de estrutura se necessário
if (!empty($estrutura)) {
    $sql .= " AND estrutura = ?";
    $params[] = $estrutura; // Adiciona ao array de parâmetros
}

// Prepara o statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(['success' => false, 'message' => 'Erro na preparação da consulta: ' . $conn->error]));
}

// Prepara os tipos de parâmetros para o bind_param
$types = str_repeat("s", count($params));

// Faz o bind dos parâmetros
$stmt->bind_param($types, ...$params);

// Executa a consulta
$stmt->execute();
$result = $stmt->get_result();

// Verifica se encontrou algum resultado
$resultados = [];
while ($row = $result->fetch_assoc()) {
    $resultados[] = $row;
}

// Retorna o JSON
if (count($resultados) > 0) {
    echo json_encode(['success' => true, 'resultados' => $resultados]);
} else {
    echo json_encode(['success' => false, 'message' => 'Nenhum resultado encontrado']);
}

// Fecha a conexão
$stmt->close();
$conn->close();
?>
