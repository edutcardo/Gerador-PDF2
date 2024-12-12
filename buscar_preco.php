<?php
header('Content-Type: application/json');

// Parâmetros de pesquisa
$potencia_gerador = isset($_GET['potencia-gerador']) ? $_GET['potencia-gerador'] : '';
$estrutura = isset($_GET['estrutura']) ? $_GET['estrutura'] : '';

// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apiedeltec";

// Conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro na conexão com o banco de dados']));
}

// Prepara a consulta SQL
$sql = "SELECT titulo, precoDoIntegrador, codProd, marca, fabricante, potenciaInversor, potenciaModulo, tensaoSaida, componentes FROM produtos WHERE potenciaGerador LIKE ?";
$params = ["%" . $potencia_gerador . "%"];  // Aqui criamos o array com o valor de busca da potência

// Adiciona o filtro de estrutura se necessário
if ($estrutura) {
    $sql .= " AND estrutura = ?";
    $params[] = $estrutura;  // Adiciona o valor de estrutura ao array
}

// Prepara o statement
$stmt = $conn->prepare($sql);

// Prepara os tipos de parâmetros para o bind_param
$types = str_repeat("s", count($params)); // cria uma string de tipos como "ss" para 2 parâmetros

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

if (count($resultados) > 0) {
    echo json_encode(['success' => true, 'resultados' => $resultados]);
} else {
    echo json_encode(['success' => false, 'message' => 'Nenhum resultado encontrado']);
}

// Fecha a conexão
$stmt->close();
$conn->close();
?>
