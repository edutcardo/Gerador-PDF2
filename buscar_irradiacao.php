<?php
include 'conectar.php';

if (isset($_GET['cidade'])) {
    $cidade = $conn->real_escape_string($_GET['cidade']);
    
    // Use a coluna correta NOME_MUNICIPIO para a busca
    $sql = "SELECT IRRADIAÇÃO FROM cidadecoordenada WHERE NOME_MUNICIPIO = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cidade);
    $stmt->execute();
    $stmt->bind_result($irradiacao);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => true, 'irradiacao' => $irradiacao]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cidade não encontrada']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Parâmetro de cidade não fornecido']);
}

$conn->close();
?>