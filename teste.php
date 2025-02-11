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

        // Formata o resultado para o formato de moeda brasileira
        if (is_numeric($resultado)) {
            $resultado_formatado = 'R$ ' . number_format($resultado, 2, ',', '.');
        } else {
            $resultado_formatado = $resultado;
        }
        
        // Envie o resultado formatado como resposta
        echo $resultado_formatado;
        
    } else {
        // Retorna vazio se nenhum resultado foi encontrado
        echo "";
    }

    // Fechar conexão
    $conn->close();
} else {
    // Se parâmetros não forem especificados corretamente, retorne vazio
    echo "";
}

?>