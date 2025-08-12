<?php
// Certifique-se de que o autoload do TCPDF está correto
require_once('vendor/autoload.php');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// --- CONFIGURAÇÕES DE DEBUG E ERRO ---
ini_set('display_errors', 0);
ini_set('log_errors', 1);
// Opcional: mude o nome do log para refletir o novo script
ini_set('error_log', __DIR__ . '/gerar_pdf_manutencao_errors.log');

error_log("==================================================");
error_log("INÍCIO DO SCRIPT (MANUTENÇÃO SIMPLIFICADO): " . date("Y-m-d H:i:s"));
error_log("MÉTODO: " . $_SERVER['REQUEST_METHOD']);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("DADOS _POST: " . print_r($_POST, true));
}
error_log("==================================================");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("PDF: Ação de geração de PDF detectada.");

    // DADOS BÁSICOS DO CLIENTE
    $nome = isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : 'N/A';
    $endereco = isset($_POST['endereco']) ? htmlspecialchars($_POST['endereco']) : 'N/A';
    $cidade_input = isset($_POST['cidade']) ? htmlspecialchars($_POST['cidade']) : 'N/A';

    // SERVIÇOS SELECIONADOS (recebidos como JSON string)
    $servicos_json = isset($_POST['servicos_selecionados']) ? $_POST['servicos_selecionados'] : '[]';
    $servicos_selecionados = json_decode($servicos_json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("PDF: ERRO ao decodificar JSON de serviços: " . json_last_error_msg());
        $servicos_selecionados = [];
    }

    $custo_total = 0;
    foreach ($servicos_selecionados as $servico) {
        if (isset($servico['custo']) && is_numeric($servico['custo'])) {
            $custo_total += floatval($servico['custo']);
        }
    }
    error_log("PDF: Custo TOTAL da proposta calculado: R$ " . $custo_total);

    $dataAtual = date('d/m/Y');

    // --- CRIAÇÃO DO PDF ---
    error_log("PDF: Iniciando criação do objeto TCPDF.");
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Canal Verde');
    $pdf->SetTitle('Proposta de Serviços');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0, true);
    $pdf->SetAutoPageBreak(FALSE, 0);

    // Adiciona as páginas de imagem de fundo
    $imagens_fundo = ['PL1.png', 'PL4.png'];
    foreach ($imagens_fundo as $img_nome) {
        $pdf->AddPage();
        $imgPath = __DIR__ . '/' . $img_nome;
        if (file_exists($imgPath)) {
            $pdf->Image($imgPath, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
        } else {
            error_log("PDF: ERRO - Imagem de fundo '{$img_nome}' NÃO encontrada em " . __DIR__);
            $pdf->SetFillColor(255, 200, 200); $pdf->Rect(0, 0, 210, 297, 'F');
            $pdf->SetTextColor(255, 0, 0); $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Text(10, 10, "Erro: Imagem '{$img_nome}' nao encontrada!");
        }
    }

    // --- CONTEÚDO DA ÚLTIMA PÁGINA (PL4.png) ---
    $pdf->setPage($pdf->getNumPages());
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetXY(175, 280);
    $pdf->Cell(0, 10, $dataAtual, 0, 0, 'R');

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);
    $line_height = 7;
    $current_y_pdf = 71;

    $pdf->SetXY(18.4, $current_y_pdf); $pdf->MultiCell(170, $line_height, "Cliente: " . $nome, 0, 'L');
    $current_y_pdf += $line_height;
    $pdf->SetXY(18.4, $current_y_pdf); $pdf->MultiCell(170, $line_height, "Endereço: " . $endereco, 0, 'L');
    $current_y_pdf += $line_height;
    $pdf->SetXY(18.4, $current_y_pdf); $pdf->MultiCell(170, $line_height, "Cidade: " . $cidade_input, 0, 'L');
    $current_y_pdf += $line_height + 5;

    $pdf->SetFont('helvetica', 'BU', 14);
    $pdf->SetXY(18.4, $current_y_pdf); $pdf->MultiCell(170, $line_height, "Serviços Contratados:", 0, 'L');
    $current_y_pdf += $line_height + 2;

    $pdf->SetFont('helvetica', '', 12.5);
    if (empty($servicos_selecionados)) {
        $pdf->SetXY(18.4, $current_y_pdf);
        $pdf->MultiCell(170, $line_height, "Nenhum serviço especificado.", 0, 'L');
        $current_y_pdf += $line_height;
    } else {
        foreach ($servicos_selecionados as $servico) {
            $nome_servico = isset($servico['nome']) ? $servico['nome'] : 'Serviço';
            $custo_servico = isset($servico['custo']) ? floatval($servico['custo']) : 0;
            $texto_servico = $nome_servico . ": R$ " . number_format($custo_servico, 2, ',', '.');
            
            $pdf->SetXY(18.4, $current_y_pdf);
            $pdf->MultiCell(170, $line_height, $texto_servico, 0, 'L');
            $current_y_pdf += $line_height;
        }
    }

    $current_y_pdf += 5;

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetXY(18.4, $current_y_pdf);
    $pdf->MultiCell(170, $line_height + 2, "VALOR TOTAL DA PROPOSTA: R$ " . number_format($custo_total, 2, ',', '.'), 0, 'L');

    $nomeArquivoLimpo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nome);
    $nomeFinalPDF = "Proposta_Servicos_{$nomeArquivoLimpo}.pdf";

    error_log("PDF: Preparando para enviar o PDF: {$nomeFinalPDF}");
    $pdf->Output($nomeFinalPDF, 'I'); // 'I' para inline, 'D' para download
    exit;

} else {
    error_log("Recebida requisição " . $_SERVER['REQUEST_METHOD'] . ". Apenas POST é permitido.");
    http_response_code(405);
    echo "Método não permitido.";
}
?>