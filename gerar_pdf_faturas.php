<?php
    require_once('vendor/autoload.php');

    $authToken = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiOTFlMzU1NmYzMTAxMzE1NzBlNmI0OWM0ZWQwZDhiZjUzNWUiLCJpYXQiOjE3NDA1NzM4MzEsImV4cCI6MTc0MDYxNDM5OH0.Y-pjHKdnM9BWv3J7JVLWtEN64afCrtFayBoMy0J60UU";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nuAnoMes = $_POST['nuAnoMes'];

        $instalacaoInput = $_POST['nuInstalacao'];
        if (is_array($instalacaoInput)) {
            $nuInstalacoes = $instalacaoInput;
        } else {
            if (strpos($instalacaoInput, ',') !== false) {
                $nuInstalacoes = array_map('trim', explode(',', $instalacaoInput));
            } else {
                $nuInstalacoes = [$instalacaoInput];
            }
        }

        $allInvoices = [];

        foreach ($nuInstalacoes as $instalacao) {
            $url = "https://api.powerrev.com.br:3401/invoice?nuAnoMes=" . urlencode($nuAnoMes) . "&nuInstalacao=" . urlencode($instalacao);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'accept: application/json',
                'Authorization: Bearer ' . $authToken
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'Erro ao buscar faturas para a instalação ' . htmlspecialchars($instalacao) . ': ' . curl_error($ch) . '<br>';
                curl_close($ch);
                continue;
            }
            curl_close($ch);

            $invoices = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "Erro ao decodificar a resposta JSON para a instalação " . htmlspecialchars($instalacao) . ".<br>";
                continue;
            }

            $allInvoices = array_merge($allInvoices, $invoices);
        }

        echo "Foram encontradas " . count($allInvoices) . " faturas.<br><br>";

        foreach ($allInvoices as $invoice) {
            if (!isset($invoice['idFaturaConsumo'])) {
                continue;
            }
            $idInvoice = $invoice['idFaturaConsumo'];
            $downloadUrl = "https://api.powerrev.com.br:3401/billing/invoice/preview/" . urlencode($idInvoice);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $downloadUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'accept: application/json',
                'Authorization: Bearer ' . $authToken
            ]);
            $pdfData = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'Erro ao baixar o PDF da fatura ' . htmlspecialchars($idInvoice) . ': ' . curl_error($ch) . '<br>';
                curl_close($ch);
                continue;
            }
            curl_close($ch);

            if (isset($invoice['downloadArquivo']['noArquivo'])) {
                $fileName = $invoice['downloadArquivo']['noArquivo'];
            } else {
                $fileName = "invoice_" . $idInvoice . ".pdf";
            }

            if (file_put_contents($fileName, $pdfData) !== false) {
                echo "PDF da fatura " . htmlspecialchars($idInvoice) . " salvo como " . htmlspecialchars($fileName) . ".<br>";
            } else {
                echo "Erro ao salvar o PDF da fatura " . htmlspecialchars($idInvoice) . ".<br>";
            }
        }
    } else {
        echo '<form method="post" action="">
                <label for="nuAnoMes">Ano/Mês:</label>
                <input type="text" id="nuAnoMes" name="nuAnoMes" required>
                <br>
                <label for="nuInstalacao">Instalação (se múltiplas, separe por vírgula):</label>
                <input type="text" id="nuInstalacao" name="nuInstalacao" required>
                <br>
                <input type="submit" value="Enviar">
            </form>';
    }
?>
