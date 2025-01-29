<?php
header('Content-Type: application/json; charset=utf-8');

// Configurações de conexão com o banco de dados
$dbConfig = [
    'host' => 'srv1781.hstgr.io',
    'dbname' => 'u345670158_propostainv',
    'user' => 'u345670158_eduardotcardo4',
    'pass' => 'Rtz6ngqr@',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_EMULATE_PREPARES => false
    ]
];

try {
    // Validar o parâmetro de entrada
    $usina = filter_input(INPUT_GET, 'usina', FILTER_SANITIZE_STRING);
    if (!$usina) {
        throw new Exception('Parâmetro usina não fornecido ou inválido');
    }

    // Estabelece conexão segura com o banco
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $dbConfig['options']);

    // Query para obter o registro mais recente para a usina selecionada
    $sql = "
        SELECT 
            preco_final,
            retorno_verde,
            media_liquido,
            rentabilidade_verde
        FROM resultados_calculados 
        WHERE usina = :usina 
        ORDER BY data_calculo DESC 
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usina' => $usina]);
    $dados = $stmt->fetch();

    if ($dados) {
        // Processa os valores e calcula o retorno anualizado
        $dados['retorno_verde_12'] = $dados['retorno_verde'] * 12;
        
        // Retorna a resposta em JSON
        echo json_encode([
            'success' => true,
            'dados' => $dados
        ]);
    } else {
        throw new Exception('Nenhum registro encontrado para a usina selecionada');
    }

} catch (PDOException $e) {
    error_log("Erro de banco de dados: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao acessar o banco de dados'
    ]);
} catch (Exception $e) {
    error_log("Erro geral: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>