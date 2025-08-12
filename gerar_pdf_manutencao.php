<?php
// Certifique-se de que o autoload do TCPDF está correto
require_once('vendor/autoload.php');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// --- CONFIGURAÇÕES DE DEBUG E ERRO ---
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/gerar_pdf_proposta_errors.log');

// =================================================================//
//          --- OPÇÕES DE CONFIGURAÇÃO DO LAYOUT ---
// =================================================================//

$usar_imagem_fundo = true;
$caminho_imagem_fundo = 'PL1.png'; // Imagem de fundo padrão

// =================================================================//

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- ETAPA 1: LÓGICA DE DADOS DINÂMICOS ---
    $nome = isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : 'N/A';
    $endereco = isset($_POST['endereco']) ? htmlspecialchars($_POST['endereco']) : 'N/A';
    $cidade_input = isset($_POST['cidade']) ? htmlspecialchars($_POST['cidade']) : 'N/A';
    $usina = isset($_POST['usina']) ? htmlspecialchars($_POST['usina']) : 'Não especificada';

    $servicos_json = isset($_POST['servicos_selecionados']) ? $_POST['servicos_selecionados'] : '[]';
    $servicos_selecionados = json_decode($servicos_json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("PDF ERRO: Falha ao decodificar JSON de serviços: " . json_last_error_msg());
        $servicos_selecionados = [];
    }

    $custo_total = 0;
    foreach ($servicos_selecionados as $servico) {
        if (isset($servico['custo']) && is_numeric($servico['custo'])) {
            $custo_total += floatval($servico['custo']);
        }
    }

    $dataAtual = date('d/m/Y');

    // --- ETAPA 2: CRIAÇÃO DO PDF COM O ESTILO ---
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
    
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Canal Verde');
    $pdf->SetTitle('Proposta de Serviços - ' . $nome);
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    $pdf->AddPage();

    // --- LÓGICA PARA IMAGEM DE FUNDO ---
    $desenhar_elementos = !$usar_imagem_fundo;

    if ($usar_imagem_fundo) {
        $imgPath = $caminho_imagem_fundo;
        if (file_exists($imgPath)) {
            $bMargin = $pdf->getBreakMargin();
            $auto_page_break = $pdf->getAutoPageBreak();
            $pdf->SetAutoPageBreak(false, 0);
            $pdf->Image($imgPath, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
            $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
            $pdf->setPageMark();
        } else {
            error_log("PDF ERRO: Imagem de fundo NÃO encontrada: " . $imgPath);
            $desenhar_elementos = true;
        }
    }
    

    // --- TÍTULO DA PROPOSTA ---
    $pdf->SetY(44);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Proposta de Serviços de Manutenção', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'Data: ' . $dataAtual, 0, 1, 'C');
    
    if ($desenhar_elementos) {
        $pdf->Line(15, $pdf->GetY() + 3, 195, $pdf->GetY() + 3);
    }
    $pdf->SetY($pdf->GetY() + 8);

    // --- SEÇÃO DE DADOS DO CLIENTE ---
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(230, 245, 230);
    $pdf->Cell(0, 8, 'Cliente', 0, 1, 'L', $desenhar_elementos);
    
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Ln(2);
    $pdf->SetX(20); $pdf->Cell(20, 7, 'Nome:', 0, 0, 'L'); $pdf->MultiCell(0, 7, $nome, 0, 'L');
    $pdf->SetX(20); $pdf->Cell(20, 7, 'Endereço:', 0, 0, 'L'); $pdf->MultiCell(0, 7, $endereco, 0, 'L');
    $pdf->SetX(20); $pdf->Cell(20, 7, 'Cidade:', 0, 0, 'L'); $pdf->MultiCell(0, 7, $cidade_input, 0, 'L');
    $pdf->SetX(20); $pdf->Cell(20, 7, 'Usina:', 0, 0, 'L'); $pdf->MultiCell(0, 7, $usina, 0, 'L');
    $pdf->Ln(5);

    // --- SEÇÃO VALORES DOS SERVIÇOS ---
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 30, 'Valores dos Serviços', 0, 1, 'C', $desenhar_elementos);
    $pdf->Ln(2);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(200, 220, 200);
    
    $pdf->Cell(130, 7, 'Tipo de Serviço', 1, 0, 'C', $desenhar_elementos);
    $pdf->Cell(50, 7, 'Valor', 1, 1, 'C', $desenhar_elementos);

    $pdf->SetFont('helvetica', '', 10);
    if (empty($servicos_selecionados)) {
        $pdf->Cell(180, 10, 'Nenhum serviço selecionado.', 1, 1, 'C');
    } else {
        foreach ($servicos_selecionados as $servico) {
            $nome_servico = $servico['nome'] ?? 'N/A';
            $custo_servico = isset($servico['custo']) ? 'R$ ' . number_format($servico['custo'], 2, ',', '.') : 'N/A';
            
            $pdf->Cell(130, 7, $nome_servico, 1, 0, 'L');
            $pdf->Cell(50, 7, $custo_servico, 1, 1, 'C');
        }
    }
    $pdf->Ln(5);
    
    // --- VALOR TOTAL ---
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(200, 220, 200);
    $pdf->Cell(130, 10, 'VALOR TOTAL DA PROPOSTA', 1, 0, 'R', $desenhar_elementos);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 10, 'R$ ' . number_format($custo_total, 2, ',', '.'), 1, 1, 'C', $desenhar_elementos);
    
    $pdf->Ln(10); // Adiciona um espaço antes da nova seção
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Formas de Pagamento', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0, 7, 'Pagamento via boleto bancário.', 0, 1, 'L');
    // --- CONTATO ---
    // AJUSTE: Valor aumentado para 65 para descer o bloco de contato ao máximo.
    $pdf->Ln(46); 
    
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Entre em Contato', 0, 1, 'C');
    
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 8, 'Canal Verde (44) 9883-0233', 0, 1, 'C');

    // --- SAÍDA DO PDF ---
    $nomeArquivoLimpo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nome);
    $nomeFinalPDF = "Proposta_Manutencao_{$nomeArquivoLimpo}.pdf";

    $pdf->Output($nomeFinalPDF, 'I');
    exit;

} else {
    http_response_code(405);
    echo "Método não permitido.";
}
?>