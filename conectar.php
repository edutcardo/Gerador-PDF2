<?php
// Configurações do banco de dados
$servername = "srv1781.hstgr.io";
$username = "u345670158_eduardotcardo2";
$password = "Rtz6ngqr@";
$dbname = "u345670158_irradiacao";

header('Content-Type: application/json');

// Conecta ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro na conexão com o banco de dados: ' . $conn->connect_error]));
}

// Consulta SQL
$sql = "SELECT * FROM nome_da_tabela"; // Substitua pelo nome correto da tabela
$result = $conn->query($sql);

if ($result) {
    $dados = [];
    while ($row = $result->fetch_assoc()) {
        $dados[] = $row;
    }
    echo json_encode(['success' => true, 'dados' => $dados]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro na consulta: ' . $conn->error]);
}

// Fecha a conexão
$conn->close();
?>
