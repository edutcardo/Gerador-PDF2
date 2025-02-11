<?php

// Configurações do banco de dados
$servername = "srv1781.hstgr.io";
$username = "u345670158_eduardotcardo3";
$password = "Rtz6ngqr@";
$dbname = "u345670158_tarifa";

// Obter o valor de inputConcessionaria a partir de uma requisição GET
$inputConcessionaria = isset($_GET['inputConcessionaria']) ? $_GET['inputConcessionaria'] : '';

if (!empty($inputConcessionaria)) {
    // Criar conexão
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar conexão
    if ($conn->connect_error) {
        die("Erro ao conectar: " . $conn->connect_error);
    }

    // Segurança contra SQL Injection
    $inputConcessionaria_safe = $conn->real_escape_string($inputConcessionaria);

    // Prepara a consulta SQL para buscar o valor "compensável"
    $sql = "SELECT compensavel FROM tarifas WHERE sigla='$inputConcessionaria_safe'";
    
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Se encontrar resultados, retorne o primeiro valor "compensável"
        $row = $result->fetch_assoc();
        echo $row['compensavel'];
    } else {
        // Retorna vazio se nenhum resultado foi encontrado
        echo "";
    }

    // Fechar conexão
    $conn->close();
} else {
    // Se a concessionária não for especificada, retorne vazio
    echo "";
}

?>