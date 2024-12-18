<?php
$servername = "srv1781.hstgr.io";
$username = "u345670158_eduardotcardo3";
$password = "Rtz6ngqr@";
$dbname = "u345670158_tarifa";

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão ao banco de dados: ' . $conn->connect_error]);
    exit();
}

// Verifica se a cidade foi informada
if (isset($_GET['cidade'])) {
    $cidade = $conn->real_escape_string($_GET['cidade']);

    // Consulta a tabela para buscar a concessionária pela cidade
    $sql = "SELECT Concessionárias FROM concessionaria WHERE cidade = '$cidade'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['success' => true, 'concessionaria' => $row['Concessionárias']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhuma concessionária encontrada para a cidade informada']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Cidade não informada']);
}

$conn->close();
?>