<?php
header('Content-Type: application/json');

if (isset($_GET['sigla'])) {
    $sigla = $_GET['sigla'];

    // Conectar ao banco de dados
    $servername = "srv1781.hstgr.io";
    $username = "u345670158_eduardotcardo3";
    $password = "Rtz6ngqr@";
    $dbname = "u345670158_tarifa";

    try {
        $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco de dados']);
        exit;
    }

    // Consulta ao banco de dados
    $stmt = $pdo->prepare("SELECT compensavel, fiob, tarifacrua FROM tarifas WHERE Sigla = :sigla");
    $stmt->bindParam(':sigla', $sigla);
    if ($stmt->execute()) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            echo json_encode([
                'success' => true,
                'compensavel' => $result['compensavel'],
                'fiob' => $result['fiob'],
                'tarifacrua' => $result['tarifacrua']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Nenhum resultado encontrado']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro na consulta']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Sigla não foi informada']);
}
?>