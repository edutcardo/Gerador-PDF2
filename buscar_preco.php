<?php
header('Content-Type: application/json');

// Parâmetros de pesquisa
$potencia_gerador = isset($_GET['potencia-gerador']) ? floatval($_GET['potencia-gerador']) : null;
$estrutura = isset($_GET['estrutura']) ? $_GET['estrutura'] : '';

// Defina a margem de tolerância
$tolerancia = 0.5;

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

// Criação da query SQL com intervalo de tolerância
$sql = "SELECT titulo, precoDoIntegrador, codProd, marca, fabricante, potenciaInversor, potenciaModulo, tensaoSaida, componentes, potenciaGerador 
        FROM produtos 
        WHERE potenciaGerador BETWEEN ? AND ?";

// Adiciona o filtro de estrutura se necessário
if (!empty($estrutura)) {
    $sql .= " AND estrutura = ?";
}

// Prepara o statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(['success' => false, 'message' => 'Erro na preparação da consulta: ' . $conn->error]));
}

// Calcula os limites inferior e superior para a busca
$limite_inferior = $potencia_gerador - $tolerancia;
$limite_superior = $potencia_gerador + $tolerancia;

// Vincula os parâmetros de forma correta
if (!empty($estrutura)) {
    $stmt->bind_param("dds", $limite_inferior, $limite_superior, $estrutura);
} else {
    $stmt->bind_param("dd", $limite_inferior, $limite_superior);
}

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