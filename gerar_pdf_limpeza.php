<?php
require_once('vendor/autoload.php'); // Ou o caminho correto para o autoload do TCPDF

// Função para formatar o payback
function format_payback($custo_total, $economia_mensal) {
    if ($economia_mensal <= 0) {
        return "N/A (Economia mensal não positiva)";
    }
    if ($custo_total <= 0) {
        return "N/A (Custo não positivo)";
    }

    $meses_fracionados = $custo_total / $economia_mensal;
    $meses_inteiros = floor($meses_fracionados);
    $dias_restantes = round(($meses_fracionados - $meses_inteiros) * 30);

    $texto_meses = $meses_inteiros . ($meses_inteiros < 2 ? " Mês" : " Meses");

    $texto_dias = "";
    if ($dias_restantes >= 1) {
        $texto_dias = " e " . $dias_restantes . ($dias_restantes < 2 ? " Dia" : " Dias");
    }

    if ($meses_inteiros == 0 && $dias_restantes < 1) { // Caso o payback seja menor que 1 dia
        return "Menos de 1 dia";
    }
    
    return $texto_meses . $texto_dias;
}


// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- CAMPOS DO FORMULÁRIO ATUAL ---
    $nome = isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : 'N/A';
    $endereco = isset($_POST['endereco']) ? htmlspecialchars($_POST['endereco']) : 'N/A';
    $cidade = isset($_POST['cidade']) ? htmlspecialchars($_POST['cidade']) : 'N/A';
    $qtdmodulos = isset($_POST['qtdmodulos']) ? floatval($_POST['qtdmodulos']) : 0; // B6
    $potmodulos_Wp = isset($_POST['potmodulos']) ? floatval($_POST['potmodulos']) : 0; // D6
    $precokwh_input_str = isset($_POST['precokwh']) ? htmlspecialchars($_POST['precokwh']) : '0.80'; // E6 (string)
    $precokwh_numeric = floatval(str_replace(',', '.', $precokwh_input_str)); // E6 (numeric)

    // --- NOVAS VARIÁVEIS E CONSTANTES DA PLANILHA ---
    $perdageracao_f6 = 0.15; // F6 da planilha
    $fator_solar_mensal_equivalente = 30 * 4; // (30 dias * 4 horas de sol pleno equivalentes por dia)

    // Cálculo de Economia
    $econMensalSemLimp = ($qtdmodulos / (1 + $perdageracao_f6)) * $potmodulos_Wp * $fator_solar_mensal_equivalente * $precokwh_numeric / 1000;
    $econMensalComLimp = $qtdmodulos * $potmodulos_Wp * $fator_solar_mensal_equivalente * $precokwh_numeric / 1000;
    $econDiferenca = $econMensalComLimp - $econMensalSemLimp; // E11

    // Tabela de Custo por Módulo
    $custolimpezamodulo = 0; // H6
    $custofixo = 0;          // I6

    if ($qtdmodulos <= 0) { // Segurança para evitar divisão por zero ou resultados estranhos
        $custolimpezamodulo = 10; // Valor padrão ou de erro
        $custofixo = 80;      // Valor padrão ou de erro
    } elseif ($qtdmodulos <= 10) {
        $custolimpezamodulo = 10;
        $custofixo = 80;
    } elseif ($qtdmodulos <= 30) {
        $custolimpezamodulo = 9;
        $custofixo = 110;
    } elseif ($qtdmodulos <= 40) {
        $custolimpezamodulo = 8;
        $custofixo = 150;
    } elseif ($qtdmodulos <= 100) {
        $custolimpezamodulo = 7;
        $custofixo = 250;
    } elseif ($qtdmodulos <= 150) {
        $custolimpezamodulo = 6;
        $custofixo = 400;
    } elseif ($qtdmodulos <= 200) {
        $custolimpezamodulo = 5.5;
        $custofixo = 500;
    } elseif ($qtdmodulos <= 300) {
        $custolimpezamodulo = 5;
        $custofixo = 650;
    } elseif ($qtdmodulos <= 500) {
        $custolimpezamodulo = 4.5;
        $custofixo = 900;
    } else { // Mais de 500 módulos
        $custolimpezamodulo = 4; // Exemplo, defina conforme sua tabela
        $custofixo = 1200;   // Exemplo, defina conforme sua tabela
    }

    $deslocamento = 26.25;      // J6 (fixo)
    $k6_comissao_val = 0.05;    // K6 (fixo, conforme solicitado)
    $estrutura_j4 = "TELHADO"; // J4 (fixo)
    $fator_estrutura_solo = ($estrutura_j4 == "SOLO" ? 1.0 : 1.0); // Na prática, sempre 1 com J4="TELHADO"

    // Cálculo Custo Total (B15 da planilha)
    $denominador_comissao = (1 - $k6_comissao_val);
    if ($denominador_comissao == 0) { // Evitar divisão por zero
        $denominador_comissao = 0.00001; // Um valor muito pequeno para evitar erro, mas indica problema com K6
    }
    $custototal_B15 = ($qtdmodulos * $custolimpezamodulo + $custofixo + $deslocamento) / $denominador_comissao * $fator_estrutura_solo;

    // Cálculo para Custo 3 Limpezas
    $f15_calc_raw = ($qtdmodulos * $custolimpezamodulo * 0.85 + $custofixo * 0.85 + $deslocamento) / $denominador_comissao * $fator_estrutura_solo;
    $f15_text = "R$ " . number_format($f15_calc_raw, 2, ',', '.');

    $g15_calc_raw = ($qtdmodulos * $custolimpezamodulo * 0.85 + $custofixo * 0.85 + $deslocamento + 0.85) / $denominador_comissao * 3 * $fator_estrutura_solo;
    $g15_text = "R$ " . number_format($g15_calc_raw, 2, ',', '.');
    $custo3limpezas_text = "3x de " . $f15_text . " = " . $g15_text;

    // Cálculo Payback
    $payback1limpeza_text = format_payback($custototal_B15, $econDiferenca);
    $payback3limpezas_text = format_payback($f15_calc_raw, $econDiferenca); // A planilha usa o valor de F15 para o payback de 3 limpezas

    // Data atual
    $formatoData = 'd/m/Y';
    $dataAtual = date($formatoData);

    // --- CRIAÇÃO DO PDF ---
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Canal Verde ' );
    $pdf->SetTitle('Proposta Limpeza de Módulos');
    $pdf->SetSubject('Proposta Comercial');

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0, true);
    $pdf->SetAutoPageBreak(FALSE, 0);

    // Página 1
    $pdf->AddPage();
    if (file_exists('PL1.png')) {
        $pdf->Image('PL1.png', 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
    } else {
        $pdf->SetFillColor(200, 220, 255); $pdf->Rect(0, 0, 210, 297, 'F');
        $pdf->SetTextColor(255,0,0); $pdf->Text(10,10, "Erro: Imagem PL1.png não encontrada!");
    }

    $pdf->SetFont('helvetica', 'B', 14); // Fonte um pouco menor para caber mais info
    $pdf->SetTextColor(255, 255, 255); // Cor do texto Branca (ajuste se sua imagem tiver fundo claro)

    $x_coord = 34.2;
    $y_coord = 85; // Subi um pouco para mais espaço
    $line_height = 6;

    // Data atual no canto inferior direito
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetXY(175, 280); // Ajuste Y se necessário
    $pdf->Cell(0, 10, $dataAtual, 0, 0, 'R');

    // Páginas 2, 3 e 4 (apenas com imagem de fundo)
    if (file_exists('PL2.png')) {
        $pdf->AddPage(); $pdf->Image('PL2.png', 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
    }
    if (file_exists('PL3.png')) {
        $pdf->AddPage(); $pdf->Image('PL3.png', 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
    }
    if (file_exists('PL4.png')) {
        $pdf->AddPage(); $pdf->Image('PL4.png', 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
    }
    
    $pdf->SetXY(18.4, 71); $pdf->MultiCell(160, $line_height,"Cliente: " .$nome, 0, 'L'); $y_coord += $line_height;
    $pdf->SetXY(18.4,78); $pdf->MultiCell(160, $line_height,$qtdmodulos." módulos, ". $potmodulos_Wp ." Wp, de ". $potenciaTotalSistemaKWp."kWp", 0, 'L'); $y_coord += $line_height * 1.2;
    $pdf->SetXY(18.4, 85); $pdf->MultiCell(160, $line_height,"Endereço: " .$endereco, 0, 'L'); $y_coord += $line_height;
    $pdf->SetXY(18.4,92); $pdf->MultiCell(160, $line_height,"Cidade: " .$cidade, 0, 'L'); $y_coord += $line_height * 1.2;

    $pdf->SetXY(18.4, 115); $pdf->MultiCell(160, $line_height, "Custo por Limpeza Avulsa: R$ " . number_format($custototal_B15, 2, ',', '.'), 0, 'L'); $y_coord += $line_height;
    $pdf->SetXY(18.4, 122); $pdf->MultiCell(160, $line_height, "Payback (1 Limpeza): " . $payback1limpeza_text, 0, 'L'); $y_coord += $line_height * 1.2;
    $pdf->SetXY(18.4, 129); $pdf->MultiCell(160, $line_height, "Pacote 3 Limpezas: " . $custo3limpezas_text, 0, 'L'); $y_coord += $line_height;
    $pdf->SetXY(18.4, 136); $pdf->MultiCell(160, $line_height, "Payback (Pacote 3 Limpezas, valor 1ª parc.): " . $payback3limpezas_text, 0, 'L'); $y_coord += $line_height;
    
    $pdf->SetXY(18.4, 196); $pdf->MultiCell(160, $line_height,"Aderindo ao pacote de três limpezas anuais, além da manutenção preventiva, você ganhará o monitoramento online completo do seu sistema durante 1 ano!", 2, false); 

    $pdf->SetXY(18.4, 159); $pdf->MultiCell(160, $line_height,"Economia mensal do sistema SEM limpeza: R$ " .number_format($econMensalComLimp, 2, ',', '.'), 0, 'L'); $y_coord += $line_height;
    $pdf->SetXY(18.4, 166); $pdf->MultiCell(160, $line_height,"Economia mensal do sistema COM limpeza: R$ " .number_format($econMensalSemLimp, 2, ',', '.'), 0, 'L'); $y_coord += $line_height;
    $pdf->SetXY(18.4, 173); $pdf->MultiCell(160, $line_height,"Diferença na economia mensal do sistema: R$ " .number_format($econDiferenca, 2, ',', '.'), 0, 'L'); $y_coord += $line_height * 1.2;
    $nomeArquivoLimpo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nome);
    $pdf->Output("Proposta_Limpeza_Modulos_{$nomeArquivoLimpo}.pdf", 'I');

} else {
    header("HTTP/1.1 405 Method Not Allowed");
    echo "Erro: Este script aceita apenas requisições POST.";
    exit;
}
?>