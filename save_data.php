<?php
if (isset($_POST['execute'])) {
    // URL da API para obter o token
    $url = "https://api.edeltecsolar.com.br/api-access/token";

    // Dados de autenticação
    $data = [
        "apiKey" => "b54360a4-b6cd-4e14-b294-ad3bbe80007b",
        "secret" => "KHNax/9Lz2lrqwtGZ14AqBjHF9m/l94CPQ84mF/ouXA="
    ];

    // Inicializa a sessão cURL para obter o token
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Executa a requisição
    $response = curl_exec($ch);
    $err = curl_error($ch);

    // Fecha a sessão cURL
    curl_close($ch);

    if ($response === false) {
        die('Erro ao obter token: ' . $err);
    }

    $jwtToken = $response;

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

    // Limpa a tabela antes de inserir novos dados
    $truncateSql = "TRUNCATE TABLE produtos";
    if ($conn->query($truncateSql) === TRUE) {
        echo "Tabela limpa com sucesso!<br>";
    } else {
        die("Erro ao limpar tabela: " . $conn->error);
    }

    // Lista de estruturas para requisições
    $estruturas = ["S/ESTRUTURA", "COLONIAL", "FIBROMADEIRA", "FIBROMETAL", "METALICO"];

    foreach ($estruturas as $estrutura) {
        $url = "https://api.edeltecsolar.com.br/produtos/integration?limit=5000&fabricante=DEYE&estrutura=" . urlencode($estrutura);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $jwtToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            echo 'Erro ao obter produtos para estrutura ' . $estrutura . ': ' . $err . '<br>';
            continue;
        }

        $produtos = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'Erro ao decodificar JSON de produtos para estrutura ' . $estrutura . ': ' . json_last_error_msg() . '<br>';
            continue;
        }

        if (!isset($produtos['items'])) {
            echo 'Nenhum produto encontrado para estrutura ' . $estrutura . '.<br>';
            continue;
        }

        foreach ($produtos['items'] as $produto) {
            $codProd = isset($produto['codProd']) ? $produto['codProd'] : '';
            $titulo = isset($produto['titulo']) ? $produto['titulo'] : '';
            $marca = isset($produto['marca']) ? $produto['marca'] : '';
            $fabricante = isset($produto['fabricante']) ? $produto['fabricante'] : '';
            $potenciaInversor = isset($produto['potenciaInversor']) ? $produto['potenciaInversor'] : 0;
            $potenciaModulo = isset($produto['potenciaModulo']) ? $produto['potenciaModulo'] : 0;
            $tensaoSaida = isset($produto['tensaoSaida']) ? $produto['tensaoSaida'] : 0;
            $componentes = isset($produto['componentes']) ? json_encode($produto['componentes']) : '';
            $palavrasChave = isset($produto['palavrasChave']) ? $produto['palavrasChave'] : '';
            $precoDoIntegrador = isset($produto['precoDoIntegrador']) ? $produto['precoDoIntegrador'] : 0;
            $potenciaGerador = isset($produto['potenciaGerador']) ? $produto['potenciaGerador'] : 0;
            $estruturaAtual = isset($produto['estrutura']) ? $produto['estrutura'] : '';

            $stmt = $conn->prepare("INSERT INTO produtos (codProd, titulo, marca, fabricante, potenciaInversor, potenciaModulo, tensaoSaida, componentes, palavrasChave, precoDoIntegrador, potenciaGerador, estrutura) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssdddsddds", $codProd, $titulo, $marca, $fabricante, $potenciaInversor, $potenciaModulo, $tensaoSaida, $componentes, $palavrasChave, $precoDoIntegrador, $potenciaGerador, $estruturaAtual);

            if ($stmt->execute() === TRUE) {
                echo "Produto inserido com sucesso para estrutura $estrutura!<br>";
            } else {
                echo "Erro ao inserir produto para estrutura $estrutura: " . $stmt->error . "<br>";
            }

            $stmt->close();
        }
    }

    // Nova solicitação para produtos sem estrutura especificada
    $url = "https://api.edeltecsolar.com.br/produtos/integration?limit=5000";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $jwtToken,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $err = curl_error($ch);

    curl_close($ch);

    if ($response === false) {
        echo 'Erro ao obter produtos sem estrutura: ' . $err . '<br>';
    } else {
        $produtos = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'Erro ao decodificar JSON de produtos sem estrutura: ' . json_last_error_msg() . '<br>';
        } else {
            if (!isset($produtos['items'])) {
                echo 'Nenhum produto encontrado sem estrutura.<br>';
            } else {
                foreach ($produtos['items'] as $produto) {
                    $codProd = isset($produto['codProd']) ? $produto['codProd'] : '';
                    $titulo = isset($produto['titulo']) ? $produto['titulo'] : '';
                    $marca = isset($produto['marca']) ? $produto['marca'] : '';
                    $fabricante = isset($produto['fabricante']) ? $produto['fabricante'] : '';
                    $potenciaInversor = isset($produto['potenciaInversor']) ? $produto['potenciaInversor'] : 0;
                    $potenciaModulo = isset($produto['potenciaModulo']) ? $produto['potenciaModulo'] : 0;
                    $tensaoSaida = isset($produto['tensaoSaida']) ? $produto['tensaoSaida'] : 0;
                    $componentes = isset($produto['componentes']) ? json_encode($produto['componentes']) : '';
                    $palavrasChave = isset($produto['palavrasChave']) ? $produto['palavrasChave'] : '';
                    $precoDoIntegrador = isset($produto['precoDoIntegrador']) ? $produto['precoDoIntegrador'] : 0;
                    $potenciaGerador = isset($produto['potenciaGerador']) ? $produto['potenciaGerador'] : 0;
                    $estruturaAtual = isset($produto['estrutura']) ? $produto['estrutura'] : '';

                    $stmt = $conn->prepare("INSERT INTO produtos (codProd, titulo, marca, fabricante, potenciaInversor, potenciaModulo, tensaoSaida, componentes, palavrasChave, precoDoIntegrador, potenciaGerador, estrutura) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssdddsddds", $codProd, $titulo, $marca, $fabricante, $potenciaInversor, $potenciaModulo, $tensaoSaida, $componentes, $palavrasChave, $precoDoIntegrador, $potenciaGerador, $estruturaAtual);

                    if ($stmt->execute() === TRUE) {
                        echo "Produto inserido com sucesso sem estrutura!<br>";
                    } else {
                        echo "Erro ao inserir produto sem estrutura: " . $stmt->error . "<br>";
                    }

                    $stmt->close();
                }
            }
        }
    }

    $conn->close();
}
?>