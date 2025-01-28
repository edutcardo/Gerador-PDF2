<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['simular']) && $_POST['simular'] === "true") {
    header('Content-Type: application/json');

    // Recebe variáveis do formulário
    $nome = $_POST['nome'] ?? "";
    $endereco = $_POST['endereco'] ?? "";
    $numeroDeFases = $_POST['numeroDeFases'] ?? 1;
    $precoKit = $_POST['precoKit'] ?? null;

    // Logs para revisar o que é recebido
    error_log("Nome recebido: $nome");
    error_log("Endereço recebido: $endereco");
    error_log("Número de Fases: $numeroDeFases");
    error_log("PrecoKit recebido: $precoKit");

    // Verificação e cálculo
    if ($precoKit !== null && is_numeric($precoKit)) {
        $precoFinal = $precoKit * intval($numeroDeFases);
        
        echo json_encode([
            'success' => true,
            'precoFinal' => $numeroDeFases
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "Preço do Kit inválido ou não recebido."
        ]);
    }
    exit;
}
?>