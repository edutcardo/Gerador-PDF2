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

    // --- ETAPA 1: LÓGICA DE DADOS DINÂMICOS (do segundo script) ---

    // Dados do cliente recebidos via POST
    $nome = isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : 'N/A';
    $endereco = isset($_POST['endereco']) ? htmlspecialchars($_POST['endereco']) : 'N/A';
    $cidade_input = isset($_POST['cidade']) ? htmlspecialchars($_POST['cidade']) : 'N/A';
    $usina = isset($_POST['usina']) ? htmlspecialchars($_POST['usina']) : 'Não especificada';

    // Serviços recebidos como JSON string
    $servicos_json = isset($_POST['servicos_selecionados']) ? $_POST['servicos_selecionados'] : '[]';
    $servicos_selecionados = json_decode($servicos_json, true);

    // Validação do JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("PDF ERRO: Falha ao decodificar JSON de serviços: " . json_last_error_msg());
        $servicos_selecionados = [];
    }

    // Cálculo dinâmico do custo total
    $custo_total = 0;
    foreach ($servicos_selecionados as $servico) {
        if (isset($servico['custo']) && is_numeric($servico['custo'])) {
            $custo_total += floatval($servico['custo']);
        }
    }

    // Data atual dinâmica
    $dataAtual = date('d/m/Y');


    // --- ETAPA 2: CRIAÇÃO DO PDF COM O ESTILO (do primeiro script) ---

    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
    
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Canal Verde');
    // O título agora é genérico para "Serviços"
    $pdf->SetTitle('Proposta de Serviços - ' . $nome);
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    
    $pdf->AddPage();

    // --- LÓGICA PARA IMAGEM DE FUNDO (mantida e corrigida) ---
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
            $desenhar_elementos = true; // Ativa o fundo colorido como fallback
        }
    }
    
    // --- CABEÇALHO ---
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetXY(60, 18);
    $pdf->Cell(0, 10, 'CANAL VERDE', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetXY(60, 26);
    $pdf->Cell(0, 10, 'COOPERATIVA DE ENERGIAS RENOVÁVEIS', 0, 1, 'L');

    // --- TÍTULO DA PROPOSTA ---
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetY(45);
    // Título alterado para "Proposta de Serviços de Manutenção"
    $pdf->Cell(0, 10, 'Proposta de Serviços de Manutenção', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'Data: ' . $dataAtual, 0, 1, 'C');
    
    if ($desenhar_elementos) {
        $pdf->Line(15, $pdf->GetY() + 3, 195, $pdf->GetY() + 3);
    }
    $pdf->SetY($pdf->GetY() + 8);

    // --- SEÇÃO DE DADOS DO CLIENTE (preenchida com dados do POST) ---
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

    // --- SEÇÃO VALORES DOS SERVIÇOS (tabela preenchida com dados do JSON) ---
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Valores dos Serviços', 0, 1, 'L', $desenhar_elementos);
    $pdf->Ln(2);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(200, 220, 200);
    $pdf->Cell(80, 7, 'Tipo de Serviço', 1, 0, 'C', $desenhar_elementos);
    $pdf->Cell(40, 7, 'Valor', 1, 0, 'C', $desenhar_elementos);
    $pdf->Cell(60, 7, 'Parcelamento', 1, 1, 'C', $desenhar_elementos);

    $pdf->SetFont('helvetica', '', 10);
    if (empty($servicos_selecionados)) {
        $pdf->Cell(180, 10, 'Nenhum serviço selecionado.', 1, 1, 'C');
    } else {
        foreach ($servicos_selecionados as $servico) {
            $nome_servico = $servico['nome'] ?? 'N/A';
            $custo_servico = isset($servico['custo']) ? 'R$ ' . number_format($servico['custo'], 2, ',', '.') : 'N/A';
            // O campo 'parcelamento' é opcional no JSON. Se não vier, o padrão é 'À vista'.
            $parcelamento = $servico['parcelamento'] ?? 'À vista';

            $pdf->Cell(80, 7, $nome_servico, 1, 0, 'L');
            $pdf->Cell(40, 7, $custo_servico, 1, 0, 'C');
            $pdf->Cell(60, 7, $parcelamento, 1, 1, 'C');
        }
    }
    $pdf->Ln(5);
    
    // --- SEÇÃO DE BENEFÍCIOS (mantida como no original) ---
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Benefício Extra', 0, 1, 'L', $desenhar_elementos);
    $pdf->Ln(2);
    
    $pdf->SetFont('dejavusans', '', 11);
    $pdf->MultiCell(0, 7, "Na contratação de qualquer plano de manutenção, você também ganha:", 0, 'L');
    $pdf->MultiCell(0, 7, "  ✓ 1 ano de monitoramento online completo do sistema solar.", 0, 'L');
    $pdf->MultiCell(0, 7, "  ✓ Suporte técnico prioritário.", 0, 'L');
    $pdf->Ln(5);
    
    // --- VALOR TOTAL (calculado dinamicamente) ---
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetFillColor(200, 220, 200);
    $pdf->Cell(130, 10, 'VALOR TOTAL DA PROPOSTA', 1, 0, 'R', $desenhar_elementos);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(50, 10, 'R$ ' . number_format($custo_total, 2, ',', '.'), 1, 1, 'C', $desenhar_elementos);
    $pdf->Ln(5);
    
    // --- FORMAS DE PAGAMENTO E CONTATO (mantido como no original) ---
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(85, 8, 'Formas de Pagamento', 0, 0, 'L', $desenhar_elementos);
    $pdf->SetX(110);
    $pdf->Cell(85, 8, 'Entre em Contato', 0, 1, 'L', $desenhar_elementos);

    $pdf->Ln(2);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(85, 8, 'Pagamento via boleto bancário.', 0, 0, 'L');
    $pdf->SetX(110);
    $pdf->Cell(85, 8, 'Canal Verde (44) 9883-0233', 0, 1, 'L');

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