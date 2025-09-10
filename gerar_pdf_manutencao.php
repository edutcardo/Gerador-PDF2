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
//          --- OPÇÕES DE CONFIGURAÇÃO DO LAYOUT ---
// =================================================================//
$usar_imagem_fundo = true;
$caminho_imagem_fundo = 'PL1.png';

// =================================================================//
//          --- CONFIGURAÇÕES GLOBAIS DE SERVIÇOS ---
// =================================================================//
$desconto_pacote_3_limpezas = 0.15; // 15% de desconto

// =================================================================//

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- ETAPA 1: DADOS DINÂMICOS ---
    $nome = isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : 'N/A';
    $endereco = isset($_POST['endereco']) ? htmlspecialchars($_POST['endereco']) : 'N/A';
    $cidade_input = isset($_POST['cidade']) ? htmlspecialchars($_POST['cidade']) : 'N/A';
    $usina = isset($_POST['usina']) ? htmlspecialchars($_POST['usina']) : 'Não especificada';
    $dataAtual = date('d/m/Y');
    $tipo_servico_principal = isset($_POST['tipo_servico']) ? htmlspecialchars($_POST['tipo_servico']) : '';

    // --- ETAPA 2: CRIAÇÃO DO PDF ---
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A4', true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Canal Verde');
    $pdf->SetTitle('Proposta de Serviços - ' . $nome);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 15);
    $pdf->AddPage();
    $desenhar_elementos = !$usar_imagem_fundo;

    if ($usar_imagem_fundo && file_exists($caminho_imagem_fundo)) {
        $bMargin = $pdf->getBreakMargin();
        $auto_page_break = $pdf->getAutoPageBreak();
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->Image($caminho_imagem_fundo, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
        $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
        $pdf->setPageMark();
    }

    // --- CABEÇALHO E DADOS DO CLIENTE ---
    $pdf->SetY(44);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'Proposta de Serviços de Manutenção', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'Data: ' . $dataAtual, 0, 1, 'C');
    $pdf->SetY($pdf->GetY() + 8);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Cliente', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Ln(2);
    $pdf->SetX(20); $pdf->Cell(20, 7, 'Nome:', 0, 0, 'L'); $pdf->MultiCell(0, 7, $nome, 0, 'L');
    $pdf->SetX(20); $pdf->Cell(20, 7, 'Endereço:', 0, 0, 'L'); $pdf->MultiCell(0, 7, $endereco, 0, 'L');
    $pdf->SetX(20); $pdf->Cell(20, 7, 'Cidade:', 0, 0, 'L'); $pdf->MultiCell(0, 7, $cidade_input, 0, 'L');
    $pdf->SetX(20); $pdf->Cell(20, 7, 'Usina:', 0, 0, 'L'); $pdf->MultiCell(0, 7, $usina, 0, 'L');
    $pdf->Ln(5);

    // --- TÍTULO DA SEÇÃO DE VALORES ---
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Valores dos Serviços', 0, 1, 'L');
    $pdf->Ln(2);

    // ==========================================================================================
    //                     LÓGICA CONDICIONAL PARA GERAR AS TABELAS
    // ==========================================================================================
if ($tipo_servico_principal == 'Limpeza de módulos fotovoltáicos') {

    // --- Bloco para SERVIÇO DE LIMPEZA ---
    
    $qtdmodulos = isset($_POST['qtdmodulos']) ? intval($_POST['qtdmodulos']) : 0;
    $distancia_km = isset($_POST['distancia_km']) ? floatval($_POST['distancia_km']) : 0;

    $custolimpezamodulo = 0;
    $custofixo = 0;
    // ... (seu bloco if/elseif para definir $custolimpezamodulo e $custofixo vai aqui) ...
    if ($qtdmodulos <= 10) { $custolimpezamodulo = 10.00; $custofixo = 80.00; } 
    elseif ($qtdmodulos <= 30) { $custolimpezamodulo = 9; $custofixo = 110.00; }
    elseif ($qtdmodulos <= 40) { $custolimpezamodulo = 8.00; $custofixo = 150.00; }
    elseif ($qtdmodulos <= 100) { $custolimpezamodulo = 7.00; $custofixo = 250.00; } 
    elseif ($qtdmodulos <= 150) { $custolimpezamodulo = 6.00; $custofixo = 400.00; } 
    elseif ($qtdmodulos <= 200) { $custolimpezamodulo = 5.50; $custofixo = 500.00; } 
    elseif ($qtdmodulos <= 300) { $custolimpezamodulo = 5.00; $custofixo = 650.00; } 
    elseif ($qtdmodulos <= 500) { $custolimpezamodulo = 4.50; $custofixo = 900.00; } 
    else { $custolimpezamodulo = 4.00; $custofixo = 1200.00; }

     $deslocamento_limpeza = 26.25; 
    $taxa_comissao_limpeza = 0.05;
    $denominador_comissao_limpeza = (1 - $taxa_comissao_limpeza);
    $desconto_fator = 1 - $desconto_pacote_3_limpezas;

    // Define o custo de deslocamento dinâmico baseado no formulário
    $custo_deslocamento = ($distancia_km > 0 ? $distancia_km * 1.00 : 0) ;
            $deslocamento_fixo_calculo = 26.25; 
    $Custo_Base = ($qtdmodulos * $custolimpezamodulo) + $custofixo;

    // Fórmulas que NÃO incluem o deslocamento
    $Preco_Limpeza_1x = ($Custo_Base + $deslocamento_fixo_calculo) / $denominador_comissao_limpeza;
    $Preco_Unitario_Pacote = (($Custo_Base * $desconto_fator) + $deslocamento_fixo_calculo) / $denominador_comissao_limpeza;
    $Preco_Total_Pacote = $Preco_Unitario_Pacote * 3;

    // Monta a tabela COM a linha de deslocamento separada
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(130, 7, 'Tipo de Serviço', 1, 0, 'C');
    $pdf->Cell(50, 7, 'Valor Total', 1, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
            
    $pdf->Cell(130, 7, 'Limpeza de '.$qtdmodulos.' Módulos Fotovoltáicos (Avulso)', 1, 0, 'L');
    $pdf->Cell(50, 7, 'R$ ' . number_format($Preco_Limpeza_1x, 2, ',', '.'), 1, 1, 'C');
            
    $texto_pacote = 'Pacote 3 Limpezas (3x de R$ ' . number_format($Preco_Unitario_Pacote, 2, ',', '.').')';
    $pdf->Cell(130, 7, $texto_pacote, 1, 0, 'L');
    $pdf->Cell(50, 7, 'R$ ' . number_format($Preco_Total_Pacote, 2, ',', '.'), 1, 1, 'C');

    // Adiciona a linha de deslocamento SE ele existir
    if ($custo_deslocamento > 0) {
        $pdf->Cell(130, 7, 'Taxa de Deslocamento (' . $distancia_km . ' km)', 1, 0, 'L');
        $pdf->Cell(50, 7, 'R$ ' . number_format($custo_deslocamento, 2, ',', '.'), 1, 1, 'C');
    }


    } else {
        
        // --- Bloco para OUTROS SERVIÇOS ---
        
        $servicos_json = isset($_POST['servicos_selecionados']) ? $_POST['servicos_selecionados'] : '[]';
        $servicos_selecionados = json_decode($servicos_json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("PDF ERRO: Falha ao decodificar JSON: " . json_last_error_msg());
            $servicos_selecionados = [];
        }

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(130, 7, 'Tipo de Serviço', 1, 0, 'C');
        $pdf->Cell(50, 7, 'Valor', 1, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);

        $custo_total = 0;
        if (!empty($servicos_selecionados)) {
            foreach ($servicos_selecionados as $servico) {
                $nome_servico = $servico['nome'] ?? 'N/A';
                $custo_servico = $servico['custo'] ?? 0;
                $custo_total += floatval($custo_servico);
                
                $pdf->Cell(130, 7, $nome_servico, 1, 0, 'L');
                $pdf->Cell(50, 7, 'R$ ' . number_format($custo_servico, 2, ',', '.'), 1, 1, 'C');
            }
        }
        
        $pdf->Ln(2);
        }

    // --- SEÇÃO FINAL DO DOCUMENTO ---
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Formas de Pagamento', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 7, 'Pagamento via boleto bancário.', 0, 1, 'L');
    
    $pdf->SetY(-40); // Posicionamento a partir do fim da página para garantir consistência
    
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