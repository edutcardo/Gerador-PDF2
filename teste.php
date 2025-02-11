<?php

// Configurações do banco de dados
$servername = "srv1781.hstgr.io";
$username = "u345670158_eduardotcardo3";
$password = "Rtz6ngqr@";
$dbname = "u345670158_tarifa";

// Obter os valores de entrada da requisição GET
$inputConcessionaria = isset($_GET['inputConcessionaria']) ? $_GET['inputConcessionaria'] : '';
$usina = isset($_GET['usina']) ? $_GET['usina'] : '';

if (!empty($inputConcessionaria) && !empty($usina)) {
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
        $row = $result->fetch_assoc();
        $compensavel = $row['compensavel'];
    
        // Calcula de acordo com o tipo de usina
        switch($usina) {
            case '75kwSolo':
                $resultado = $compensavel * 12768;
                break;
            case '75kwTelhado':
                $resultado = $compensavel * 12804;
                break;
            case '300kwSolo':
                $resultado = $compensavel * 51072;
                break;
            case '300kwTelhado':
                $resultado = $compensavel * 51217;
                break;
            case '1mwSolo':
                $resultado = $compensavel * 170772;
                break;
            case '1mwTelhado':
                $resultado = $compensavel * 166458;
                break;
            default:
                $resultado = "Valor de usina desconhecido";
                break;
        }
    
        // Calcula o retorno anual
        if (is_numeric($resultado)) {
            $retornoVerdeAnual = $resultado * 12;
            
            $resultado_formatado = 'R$ ' . number_format($resultado, 2, ',', '.');
            $retornoVerdeAnual_formatado = 'R$ ' . number_format($retornoVerdeAnual, 2, ',', '.');
        } else {
            $resultado_formatado = $resultado;
            $retornoVerdeAnual_formatado = $resultado;
        }
        
        // Envie os resultados formatados como resposta, separados por algum delimitador, por exemplo "|"
        echo $resultado_formatado . "|" . $retornoVerdeAnual_formatado;
    
    } else {
        echo "";
    }
}  

?>