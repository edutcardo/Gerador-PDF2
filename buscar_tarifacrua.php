<?php
header('Content-Type: application/json');
include 'db_connection.php';

if (isset($_GET['sigla'])) {
    $sigla = $conn->real_escape_string($_GET['sigla']);

    // Atualize para refletir a capitalização correta do nome da coluna
    $sql = "SELECT tarifacrua FROM tarifas WHERE Sigla = '$sigla'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['success' => true, 'tarifacrua' => $row['tarifacrua']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhum registro encontrado para a sigla fornecida.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Parâmetro sigla não fornecido.']);
}

$conn->close();
?>