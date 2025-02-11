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
                $precoFinalBack = 298920.95;
                $resultado = $compensavel * 12768;
                break;
            case '75kwTelhado':
                $precoFinalBack = 264798.28;
                $resultado = $compensavel * 12804;
                break;
            case '300kwSolo':
                $precoFinalBack = 1270002.81;
                $resultado = $compensavel * 51072;
                break;
            case '300kwTelhado':
                $precoFinalBack = 1043683.57;
                $resultado = $compensavel * 51217;
                break;
            case '1mwSolo':
                $precoFinalBack = 4891468.81;
                $resultado = $compensavel * 170772;
                break;
            case '1mwTelhado':
                $precoFinalBack = 4390447.08;
                $resultado = $compensavel * 166458;
                break;
            default:
                $precoFinalBack = "Não foi possível calcular";
                $resultado = "Valor de usina desconhecido";
                break;
        }
    
        // Calcula o retorno anual
        if (is_numeric($resultado)) {
            $retornoVerdeAnual = $resultado * 12;
            $rentabilidadeVerde = ($resultado) / $precoFinalBack;
            
            $resultado_formatado = 'R$ ' . number_format($resultado, 2, ',', '.');
            $retornoVerdeAnual_formatado = 'R$ ' . number_format($retornoVerdeAnual, 2, ',', '.');
            $precoFinalBack_formatado = 'R$ ' . number_format($precoFinalBack, 2, ',', '.');
            $rentabilidadeVerde_formatado = number_format($rentabilidadeVerde * 100, 2, ',', '.') . '%';

        } else {
            $resultado_formatado = $resultado;
            $retornoVerdeAnual_formatado = $resultado;
            $precoFinalBack_formatado = $precoFinalBack;
        }


        
        // Envie os resultados formatados como resposta, separados por algum delimitador, por exemplo "|"
        echo $resultado_formatado . "|" . $retornoVerdeAnual_formatado . "|" . $precoFinalBack_formatado . "|" . $rentabilidadeVerde_formatado;
    
    } else {
        echo "";
    }
}  

?>