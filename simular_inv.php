<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simule eventualmente o cálculo
// do precoFinal aquí para efeitos
// Somente aula
$precoFinal = 1000;

// Envolvimento JSON para n PR completa
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['simular']) && $_POST['simular'] === "true") {
    header('Content-Type: application/json');

    // Echo apenas JSON
    echo json_encode([
        'success' => isset($precoFinal) && is_numeric($precoFinal),
        'precoFinal' => $precoFinal
    ]);
    exit;
}

?>