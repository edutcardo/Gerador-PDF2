<?php
require_once('vendor/autoload.php'); // Ou o caminho correto, se você não estiver usando o Composer

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $cidade = $_POST['cidade'];
    $uc = $_POST['uc'];
    $media = $_POST['media'];
    $iluminacao = $_POST['iluminacao'];
    $potenciaGerador = $_POST['potenciaGerador'];
    $componentes = $_POST['componentes'];
    $potenciaModulo = $_POST['potenciaModulo'];
    $numeroDeFases = $_POST['numeroDeFases'];
    $precoKit = $_POST['precoKit'];
    $irradiacao = $_POST['irradiacao'];
    $marca = $_POST['marca'];
    $fabricante = $_POST['fabricante'];
    $potenciaInversor = $_POST['potenciaInversor'];
    $padrao = $_POST['padrao'];
    // As variáveis abaixo não são mais usadas no cálculo do preço final.
    // $desconto = $_POST['desconto'];
    $valoramais = isset($_POST['valoramais']) && $_POST['valoramais'] !== '' ? floatval($_POST['valoramais']) : 0;
    $indicacao = isset($_POST['indicacao']) && $_POST['indicacao'] !== '' ? floatval($_POST['indicacao']) : 0;
    $orientacao = isset($dados['orientacao']) ? $dados['orientacao'] : '';
    $solo = false;
    if (isset($_POST['solo'])) {
        $soloRaw = $_POST['solo'];
        if ($soloRaw === true || $soloRaw === "true" || $soloRaw == 1 || $soloRaw === "1") {
            $solo = true;
        }
    }
    if (!$solo && isset($_POST['estrutura'])) {
        if (strtoupper(trim($_POST['estrutura'])) === 'SOLO') {
            $solo = true;
        }
    }

    $carport = false;
    if (isset($_POST['carport'])) {
        $carportRaw = $_POST['carport'];
        if ($carportRaw === true || $carportRaw === "true" || $carportRaw == 1 || $carportRaw === "1") {
            $carport = true;
        }
    }

    $geracao = $potenciaGerador * 3.72 * 30;
    switch ($orientacao) {
    case "Leste":
    case "Oeste":
        // Aplica uma perda de 7% para orientações Leste ou Oeste
        $geracao *= 0.93; // (100% - 7% = 93%)
        break;
    case "Sul":
        // Aplica uma perda de 14% para orientação Sul
        $geracao *= 0.86; // (100% - 14% = 86%)
        break;
    // Nenhuma ação é necessária para "Norte" ou outros valores (default),
    // pois não há perda de geração a ser aplicada.
}
    if ($solo === true) {
        $geracao *= 1.10;
    }
        if ($media == 1) {
        $media = $geracao;
    }

    $qtdmodulos = ($potenciaGerador*1000)/$potenciaModulo;
    $qtdmodulosArredondado = round($qtdmodulos);
    $metrosOcupados = $qtdmodulosArredondado * 2.9;

    // Cálculos PG4
    $peso = $qtdmodulosArredondado * 33;
    $percentualSolar = ($media > 0) ? ($geracao / $media) * 100 : 0; // Evita divisão por zero
    $percentualSolarArredondado = round($percentualSolar);
    $mediaArredondado = round($media);
    $geracaoArredondado = round($geracao);

    //Cálculos PG5
    $demandaMinima = 0; // Inicializa a variável
    if ($numeroDeFases == 'mono rural') {
        $demandaMinima = 1;
    } elseif ($numeroDeFases == 'monofasico') {
        $demandaMinima = 30;
    } elseif ($numeroDeFases == 'bifasico') {
        $demandaMinima = 50;
    } elseif ($numeroDeFases == 'trifasico') {
        $demandaMinima = 100;
    }
    $gastoSemGerador = ($demandaMinima * 0.81) + $iluminacao + ($media * 0.81);
    $gastoSemGeradorRs = 'R$ ' . number_format($gastoSemGerador, 2, ',', '.');
    $gastoSemGeradorAno = $gastoSemGerador * 12;
    $gastoSemGeradorAnoRs = 'R$ ' . number_format($gastoSemGeradorAno, 2, ',', '.');
    $gastoComGerador = ($demandaMinima * 0.81) + $iluminacao;
    $gastoComGeradorRs = 'R$ ' . number_format($gastoComGerador, 2, ',', '.');
    $gastoComGeradorAno = $gastoComGerador * 12;
    $gastoComGeradorAnoRs = 'R$ ' . number_format($gastoComGeradorAno, 2, ',', '.');
    $diferencaGastos = $gastoSemGerador - $gastoComGerador;
    $diferencaGastosRs = 'R$ ' . number_format($diferencaGastos, 2, ',', '.');
    $diferencaGastosAno = $diferencaGastos * 12;
    $diferencaGastosAnoRs = 'R$ ' . number_format($diferencaGastosAno, 2, ',', '.');

    // ==============================================================================
    // INÍCIO DA MODIFICAÇÃO: Funções e lógicas de acréscimo de preço foram removidas
    // ==============================================================================

    /*
    // As funções 'calcularMargemEComissao' e 'calcularFixo' não são mais necessárias
    // e foram comentadas para não interferir no cálculo.

    function calcularMargemEComissao($potenciaGerador) {
        // ... conteúdo da função ...
    }
    $resultadoComissao = calcularMargemEComissao($potenciaGerador);
    $margem = $resultadoComissao['margem'];
    $comissao = $resultadoComissao['comissao'];
    $mobra = $resultadoComissao['mobra'];

    function calcularFixo($potenciaGerador) {
        // ... conteúdo da função ...
    }
    $valorFixo = calcularFixo($potenciaGerador);
    */

    /*
    // A lógica de desconto também foi comentada, pois não será mais aplicada.
    if ($desconto == "" || $desconto == "selecione um desconto") {
        // ... conteúdo da lógica de desconto ...
    }
    */
    
    // A lógica do 'padrão' foi mantida apenas para exibir a descrição no PDF,
    // mas o valor não será somado ao preço final.
    $valorPadraoNumerico = 0;
    $descPadrao = "";
    $textoPadrao = "";
    
    switch ($padrao) {
        case "2x50A": $valorPadraoNumerico = 1453.08; $descPadrao = "2x50A"; break;
        case "3x50A": $valorPadraoNumerico = 1616.52; $descPadrao = "3x50A"; break;
        case "3x63A": $valorPadraoNumerico = 1999.65; $descPadrao = "3x63A"; break;
        case "3x80A": $valorPadraoNumerico = 2978.64; $descPadrao = "3x80A"; break;
        case "3x100A": $valorPadraoNumerico = 2945.12; $descPadrao = "3x100A"; break;
        case "3x125A": $valorPadraoNumerico = 4322.44; $descPadrao = "3x125A"; break;
        case "3x150A": $valorPadraoNumerico = 4952.11; $descPadrao = "3x150A"; break;
        case "3x175A": $valorPadraoNumerico = 5948.65; $descPadrao = "3x175A"; break;
        case "3x200A": $valorPadraoNumerico = 6265.52; $descPadrao = "3x200A"; break;
        default: $valorPadraoNumerico = 0; break;
    }
    
    if ($valorPadraoNumerico > 0) {
        $padraoRs = 'R$ ' . number_format($valorPadraoNumerico, 2, ',', '.');
        $textoPadrao = "ENTRADA DE ENERGIA ($descPadrao) INCLUSO NO ORÇAMENTO: $padraoRs";
    }

    // ALTERAÇÃO PRINCIPAL: O preço final agora é exatamente o preço do kit.
    $precoFinal = $precoKit;
    $precoFinalRs = 'R$ ' . number_format($precoFinal, 2, ',', '.');
    
    // ==============================================================================
    // FIM DA MODIFICAÇÃO
    // ==============================================================================


    function calcularParcela_corrigido($taxa, $nper, $vp, $vf = 0, $tipo = 0) {
        bcscale(10);
        $taxa_str = (string)$taxa;
        $nper_str = (string)abs($nper);
        $vp_str   = (string)$vp;
        $vf_str   = (string)$vf;
        if (bccomp($taxa_str, '0') == 0) {
            $soma_valores = bcadd($vp_str, $vf_str);
            return bcdiv(bcmul($soma_valores, '-1'), $nper_str, 2);
        }
        $um_mais_i = bcadd('1', $taxa_str);
        $fator_potencia = bcpow($um_mais_i, $nper_str);
        $numerador_parcial = bcmul($vp_str, $taxa_str);
        $numerador = bcmul($numerador_parcial, $fator_potencia);
        $denominador = bcsub($fator_potencia, '1');
        $valorParcela = bcdiv($numerador, $denominador, 2);
        return bcmul($valorParcela, '-1', 2);
    }

    $taxa = 0.015;
    $vp = $precoFinal;
    $vf = 0;
    $tipo = 0;
    $nper1 = 36;
    $nper2 = 48;
    $nper3 = 60;
    $valorParcela = calcularParcela_corrigido($taxa, $nper1, $vp, $vf, $tipo);
    $valorParcelaRs = 'R$ '. number_format(abs((float)$valorParcela), 2, ',', '.');
    $valorParcela2 = calcularParcela_corrigido($taxa, $nper2, $vp, $vf, $tipo);
    $valorParcela2Rs = 'R$ '. number_format(abs((float)$valorParcela2), 2, ',', '.');
    $valorParcela3 = calcularParcela_corrigido($taxa, $nper3, $vp, $vf, $tipo);
    $valorParcela3Rs = 'R$ '. number_format(abs((float)$valorParcela3), 2, ',', '.');

    // AJUSTE NO CÁLCULO DO PAYBACK PARA EVITAR ERROS
    if ($diferencaGastosAno > 0) {
        $payback = $precoFinal / $diferencaGastosAno;
        $paybackArredondado = round($payback);
    } else {
        $payback = 0; // Define um valor padrão para evitar erros
        $paybackArredondado = "N/A"; // Indica que o payback não é aplicável
    }
    
    $retorno25anos = $diferencaGastosAno * 25;
    $retorno25anosRs = 'R$ ' . number_format($retorno25anos, 2, ',', '.');

    // ... (O restante do código para cálculo de irradiação e geração do PDF permanece o mesmo) ...
    
    $irradiacao = [5888, 5792, 5219, 4544, 3636, 3333, 3529, 4451, 4683, 5311, 5969, 6327];
    $jan = $irradiacao[0]; $fev = $irradiacao[1]; $mar = $irradiacao[2]; $abr = $irradiacao[3]; $mai = $irradiacao[4]; $jun = $irradiacao[5]; $jul = $irradiacao[6]; $ago = $irradiacao[7]; $set = $irradiacao[8]; $out = $irradiacao[9]; $nov = $irradiacao[10]; $dez = $irradiacao[11];
    $fatorCalculo = 1.076687117 / 5265 * 4 * 0.95;
    $jan1 = $jan * $fatorCalculo; $fev1 = $fev * $fatorCalculo; $mar1 = $mar * $fatorCalculo; $abr1 = $abr * $fatorCalculo; $mai1 = $mai * $fatorCalculo; $jun1 = $jun * $fatorCalculo; $jul1 = $jul * $fatorCalculo; $ago1 = $ago * $fatorCalculo; $set1 = $set * $fatorCalculo; $out1 = $out * $fatorCalculo; $nov1 = $nov * $fatorCalculo; $dez1 = $dez * $fatorCalculo;
    $fatorPotencia = $potenciaGerador * 30;
    $jan2 = $jan1 * $fatorPotencia; $fev2 = $fev1 * $fatorPotencia; $mar2 = $mar1 * $fatorPotencia; $abr2 = $abr1 * $fatorPotencia; $mai2 = $mai1 * $fatorPotencia; $jun2 = $jun1 * $fatorPotencia; $jul2 = $jul1 * $fatorPotencia; $ago2 = $ago1 * $fatorPotencia; $set2 = $set1 * $fatorPotencia; $out2 = $out1 * $fatorPotencia; $nov2 = $nov1 * $fatorPotencia; $dez2 = $dez1 * $fatorPotencia;
    $jan3 = number_format($jan2 / 1000, 3, '.', ''); $fev3 = number_format($fev2 / 1000, 3, '.', ''); $mar3 = number_format($mar2 / 1000, 3, '.', ''); $abr3 = number_format($abr2 / 1000, 3, '.', ''); $mai3 = number_format($mai2 / 1000, 3, '.', ''); $jun3 = number_format($jun2 / 1000, 3, '.', ''); $jul3 = number_format($jul2 / 1000, 3, '.', ''); $ago3 = number_format($ago2 / 1000, 3, '.', ''); $set3 = number_format($set2 / 1000, 3, '.', ''); $out3 = number_format($out2 / 1000, 3, '.', ''); $nov3 = number_format($nov2 / 1000, 3, '.', ''); $dez3 = number_format($dez2 / 1000, 3, '.', '');

    $formatoData = 'd/m/Y';
    $dataAtual = date($formatoData);
    $pdf = new TCPDF();
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Primeira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg1.png', 0, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(21, 94, "Nome: $nome");
    $pdf->Text(21, 100, "Endereço: $endereco");
    $pdf->Text(21, 106, "Cidade: $cidade");
    $pdf->Text(21, 128, "UC $uc");

    $pdf->Text(95, 157.7, "$metrosOcupados m²");
    $pdf->Text(99, 164.70, "$qtdmodulosArredondado Placas");
    $pdf->Text(63.7, 171, "$potenciaGerador kWp");
    $pdf->Text(61.7, 178.2, "$mediaArredondado kWh");
    $pdf->Text(60.7, 185, "$geracaoArredondado kWh");

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text(21, 280, "$dataAtual");

    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Text(21, 286, "Este orçamento tem validade de 7 dias.");
    $pdf->SetTextColor(0, 0, 0);

    // Segunda Página (com a imagem genérica e gráfico)
    $pdf->AddPage();  // Adiciona a segunda página
    $pdf->Image('pg2.png', 0, 0, 210, 297);
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página
  $pdf->AddPage();
    $pdf->Image('pg3.png', 0, 0, 210, 297);

    // Página 4
    $pdf->AddPage();
    $pdf->Image('pg4.png', 0, 0, 210, 297);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(0, 100, 0);
    $pdf->Text(148, 43.5, "$qtdmodulosArredondado X " . round($potenciaModulo) . " W");
    $pdf->Text(149, 57, "$potenciaGerador kWp");
    $pdf->Text(152, 71.5, "$metrosOcupados m²");
    $pdf->Text(152, 85, "$peso kg");
    $pdf->Text(142, 98.5, "$mediaArredondado kWh mensal");
    $pdf->Text(142, 112, "$geracaoArredondado kWh mensal");
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(158, 141.5, "$percentualSolarArredondado %");
    $pdf->Text(23, 180, "$qtdmodulosArredondado MÓDULO SOLAR SUNOVA/OSDA/RONMA " . round($potenciaModulo) . "/620 Wp ");
    $pdf->Text(23, 188, "INVERSOR 220V CHINT/SAJ/GROWATT " . $potenciaInversor . " KW");
    $qtdEstrutrura = number_format(($qtdmodulosArredondado / 4), 0, ',', '.');
    $qtdCabos = number_format(($qtdmodulosArredondado * 2), 0, ',', '.');
    // Verifica se é Solo
    $textoEstrutura = "";

    if ($solo === true) {
        $textoEstrutura = "ESTRUTURA SOLO";
    } elseif ($carport === true) {
        $textoEstrutura = "ESTRUTURA CARPORT";
    } else {
        $textoEstrutura = "$qtdEstrutrura ESTRUTURA COLONIAL/FIBROMETAL/FIBROMADEIRA/METÁLICO";
    }

    // Imprime o resultado final (UMA VEZ SÓ)
    $pdf->Text(23, 196, $textoEstrutura);

    $pdf->Text(23, 204, "$qtdCabos CABO SOLAR PV 1.8KVCC 4MM PRETO NBR 16612");
    $pdf->Text(23, 212, "$qtdCabos CABO SOLAR PV 1.8KVCC 4MM VERMELHO NBR 16612");
    $pdf->Text(23, 220, "INSTALAÇÃO / MÃO DE OBRA / EMISSÃO DE ART");
    $pdf->Text(23, 228, "RAMAL DE LIGAÇÃO LIMITADO A 10 METROS (INVERSOR PADRÃO");
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(23, 236, "$textoPadrao");
    // Página 5
    // Página 5
    $pdf->AddPage();
    $pdf->Image('pg5.png', 0, 0, 210, 297);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Text(45, 45, "$gastoSemGeradorAnoRs");
    $pdf->Text(47.5, 61.5, "$gastoSemGeradorRs");
    $pdf->Text(91, 45, "$gastoComGeradorAnoRs");
    $pdf->Text(93, 61.5, "$gastoComGeradorRs");
    $pdf->Text(135, 45, "$diferencaGastosAnoRs");
    $pdf->Text(138, 61.5, "$diferencaGastosRs");
// CÓDIGO CORRIGIDO
// ... (código anterior da página 5) ...
 $pdf->Text(138, 61.5, "$diferencaGastosRs");

    // --- INÍCIO DA LÓGICA CORRIGIDA ---

    // 1. PRIMEIRO, definimos a fonte e a cor PRETA para o restante do conteúdo
   $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // 2. Lógica para o asterisco da ESQUERDA (agora será desenhado em preto)
    if ($indicacao == 1) {
        $pdf->Text(23, 98.5, '*');
    }

    // 4. Lógica para o asterisco da DIREITA (agora será desenhado em preto)
    if ($valoramais <> 0) {
        $larguraPreco = $pdf->GetStringWidth($precoFinalRs);
        $pdf->Text(45, 98.5, '*');
    }
    // --- FIM DA LÓGICA CORRIGIDA ---
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(147, 98.5, "$precoFinalRs");
    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->Text(26, 123, "36 X $valorParcelaRs");
    $pdf->Text(85, 123, "48 X $valorParcela2Rs");
    $pdf->Text(146, 123, "60 X $valorParcela3Rs");
    $pdf->Text(152, 166, "$paybackArredondado anos");
    $pdf->Text(143, 178, "$retorno25anosRs");
    
    // Gráfico de Payback
    $dados = [];
    $retornoAcumulado = 0;
    if (is_numeric($precoFinal) && is_numeric($diferencaGastosAno) && $diferencaGastosAno > 0) {
        for ($ano = 1; $ano <= 25; $ano++) {
            $retornoAcumulado += $diferencaGastosAno;
            $dados[$ano] = $retornoAcumulado - $precoFinal;
        }
    }

    if (!empty($dados)) {
        $xInicial = 20; $yInicial = 213; $larguraGrafico = 160; $alturaGrafico = 60;
        $larguraBarra = 5; $espacoEntreBarras = 2; $linhaBase = $yInicial + $alturaGrafico;
        $min = min($dados); $max = max($dados);
        $escalaY = ($max - $min > 0) ? $alturaGrafico / ($max - $min) : 0;
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line($xInicial, $linhaBase, $xInicial + $larguraGrafico, $linhaBase);
        $pdf->Line($xInicial, $linhaBase - $alturaGrafico, $xInicial, $linhaBase);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Text($xInicial, $yInicial - 10, 'Gráfico de Payback (25 anos)');
        $xPos = $xInicial;
        foreach ($dados as $ano => $valor) {
            $barHeight = abs($valor * $escalaY);
            $yBarra = ($valor >= 0) ? $linhaBase - $barHeight : $linhaBase;
            $pdf->SetFillColor(60, 179, 113);
            $pdf->Rect($xPos, $yBarra, $larguraBarra, $barHeight, 'DF');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Text($xPos - 2, $linhaBase + 3, (string)$ano);
            if ($ano % 2 == 1) {
                $valorTexto = 'R$ ' . number_format($valor, 0, ',', '.');                $yTexto = $valor >= 0 ? $yBarra - 5 : $yBarra + $barHeight + 3;
    $pdf->Text($xPos - 6, $yTexto, $valorTexto); // Linha modificada para dar mais espaço
            }
            $xPos += $larguraBarra + $espacoEntreBarras;
        }
    } else {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Text(20, 230, 'Gráfico de Payback não disponível.');
    }

    // Páginas restantes
    $pdf->AddPage();
    $pdf->Image('pg6.png', 0, 0, 210, 297);
    $pdf->AddPage();
    $pdf->Image('pg7.png', 0, 0, 210, 297);
    $pdf->AddPage();
    $pdf->Image('pg8.png', 0, 0, 210, 297);
    $pdf->AddPage();
    $pdf->Image('pg9.png', 0, 0, 210, 297);

    $pdf->Output('arquivo_gerado.pdf', 'I');
}
?>