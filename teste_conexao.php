<?php

// Configurações do banco de dados
    $servername = "srv1781.hstgr.io";
    $username = "u345670158_eduardotcardo";
    $password = "Rtz6ngqr@";
    $dbname = "u345670158_apiedeltec";

    // Conecta ao banco de dados
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verifica a conexão
    if ($conn->connect_error) {
        die("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }

?>