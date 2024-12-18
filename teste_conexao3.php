<?php
$servername = "srv1781.hstgr.io";
$username = "u345670158_eduardotcardo3";
$password = "Rtz6ngqr@";
$dbname = "u345670158_tarifa";

// Tenta conectar ao banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão ao banco de dados: ' . $conn->connect_error]);
    exit();
} else {
    echo json_encode(['success' => true, 'message' => 'Conexão com o banco de dados bem-sucedida']);
}

$conn->close();
?>