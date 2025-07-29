<?php
require_once('vendor/autoload.php'); // Ou o caminho correto, se você não estiver usando o Composer


// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... (Todo o seu código inicial de captura de $_POST permanece o mesmo) ...
    $nome = isset($_POST['nome']) ? $_POST['nome'] : 'Informar';
    $endereco = isset($_POST['endereco']) ? $_POST['endereco'] : 'Informar';
    $cidade = isset($_POST['cidade']) ? $_POST['cidade'] : 'Informar';
    $uc = isset($_POST['uc']) ? $_POST['uc'] : '0000';
    $media = isset($_POST['media']) && $_POST['media'] !== '' ? floatval($_POST['media']) : 1;
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
    $desconto = $_POST['desconto'];
    $valoramais = isset($_POST['valoramais']) && $_POST['valoramais'] !== '' ? floatval($_POST['valoramais']) : 0;
    $inputConcessionaria = $_POST['inputConcessionaria'];
    $inputValorCompensavel = $_POST['inputValorCompensavel'];
    $multiplicador = $_POST['multiplicador'];
    $quantidadePlacas = $_POST['quantidadePlacas'];
    $estrutura = $_POST['estrutura'];
    $opcao_adicional = $_POST['opcao_adicional'];
    $usina = $_POST['usina'];
    $tensaoSaida = $_POST['tensaoSaida'];

    // ... (Todas as suas classes, funções de cálculo de preço, demanda, manutenção, etc., permanecem as mesmas) ...
    class DataProcessor {
        public static function tratarValorMonetario($valor) {
            if (empty($valor)) return 0.00;
            $valor = preg_replace('/[R$\s.]/', '', $valor);
            $valor = str_replace(',', '.', $valor);
            return round(floatval($valor), 2);
        }
        public static function tratarPercentual($valor) {
            if (empty($valor)) return 0.00;
            $valor = str_replace(['%', ' '], '', $valor);
            return round(floatval(str_replace(',', '.', $valor)), 4);
        }
        public static function validarDadosTecnicos($dados) {
            $camposNumericos = ['potencia_gerador', 'quantidade_placas', 'geracao_arredondado'];
            foreach ($camposNumericos as $campo) {
                if (isset($dados[$campo]) && !is_numeric($dados[$campo])) {
                    throw new Exception("Valor inválido para {$campo}");
                }
            }
            return true;
        }
    }
    function verificarValor($valor) { return $valor == 0 ? 1 : $valor; }
    function verificarValor2($valor) { return $valor == 0 ? 575 : $valor; }
    $potenciaGerador = verificarValor($potenciaGerador);
    $potenciaModulo = verificarValor2($potenciaModulo);
    if (empty($multiplicador)) { $multiplicador = 1; }
    $potenciaInversorUnitario = $potenciaInversor;
    $m2placa= 3.1;
    $precoPlaca = 0;
    $custoEstrutrura = 0;
    $maoObraSolo = 0;
    $potenciaGerador = $potenciaGerador * $multiplicador;
    $potenciaInversor = $potenciaInversor * $multiplicador;
    $precoKit = ($precoKit + $precoPlaca) * $multiplicador;
    if ($estrutura === "SOLO") {
        $potenciaModulo = 700;
        $potenciaGerador = ($quantidadePlacas * ($potenciaModulo/1000)) * $multiplicador;
        $precoPlaca = $quantidadePlacas * 487.50;
        $custoEstrutrura = (175 * $quantidadePlacas);
        $maoObraSolo = 125 * $quantidadePlacas;
        $precoKit = ($precoKit  + $precoPlaca + $custoEstrutrura) * $multiplicador;
        $m2placa= 9.3;
    }
    $transformador = 0;
    if ($usina === "1mwTelhado") { $precoKit = 2548698.84 + 132500; }
    if ($tensaoSaida === "380" & $potenciaGerador <= 120) { $transformador = 6590; }
    if ($estrutura === "SOLO" & $potenciaGerador <= 120) { $transformador = 30000; }
    function calcularTributario($potenciaInversor) {
        if ($potenciaInversor <= 75) { $tributario = "MEI"; }
        elseif ($potenciaInversor > 75 && $potenciaInversor <= 350) { $tributario = "SIMPLES NACIONAL 7,3%"; }
        elseif ($potenciaInversor > 350 && $potenciaInversor <= 720) { $tributario = "SIMPLES NACIONAL 9,5%"; }
        elseif ($potenciaInversor > 720) { $tributario = "LUCRO PRESUMIDO"; }
        else { $tributario = false; }
        return $tributario;
    }
    $tributario = calcularTributario($potenciaInversor);
    $precoDemanda = 50;
    $qtdDemanda = 3;
    function calcularDemanda($potenciaInversor, $precoDemanda, $qtdDemanda, $iluminacao, $media) {
        $AA12 = 1; $TUSDG = 8.6760; $Q77 = 0; $S77 = 0;
        if ($AA12 * $potenciaInversor <= 75) { $resultado = ($media * 0.83) + $iluminacao; }
        else { $resultado = ($potenciaInversor * $AA12 * $TUSDG) + $S77; }
        $demanda = $resultado - ($precoDemanda * $qtdDemanda);
        return $demanda;
    }
    $demanda = abs(calcularDemanda($potenciaInversor, $precoDemanda, $qtdDemanda, $iluminacao, $media));
    $geracao = ($potenciaGerador * 3.8 * 30);
    $qtdmodulos = ($potenciaGerador*1000)/$potenciaModulo;
    $qtdmodulosArredondado = (round($qtdmodulos));
    $metrosOcupados = ($qtdmodulosArredondado * $m2placa)* $multiplicador;
    if (isset($_POST['opcao_adicional'])) {
        $opcaoAdicional = $_POST['opcao_adicional'];
        if ($opcaoAdicional === "Adicionar alambrado") { $opcao_adicional = (sqrt($metrosOcupados) + 8)*4*140; }
        elseif ($opcaoAdicional === "Adicionar britas") { $opcao_adicional = $metrosOcupados * 2.75; }
        elseif ($opcaoAdicional === "Adicionar alambrado + britas") { $opcao_adicional = ((sqrt($metrosOcupados) + 8) * 4 * 140) + (2.75 * $metrosOcupados); }
    }
    $peso = ($qtdmodulosArredondado * 33)* $multiplicador;
    $percentualSolar = ($geracao / $media) * 100;
    $percentualSolarArredondado = round($percentualSolar);
    $mediaArredondado = round($media);
    $geracaoArredondado = round($geracao);
    $geracaoAnual = $geracaoArredondado * 12;
    function calcularManutencao($qtdmodulosArredondado) {
        if ($qtdmodulosArredondado >= 10) { $manutencao = (150 / pow($qtdmodulosArredondado, 0.485) + 10 - 20 / $qtdmodulosArredondado) * $qtdmodulosArredondado; }
        else { $manutencao = (150 / pow(10, 0.485) + 10 - 20 / 10) * 10; }
        return $manutencao / 12;
    }
    $manutencao = calcularManutencao($qtdmodulosArredondado);
    $demandaMinima = 0;
    if ($numeroDeFases == 'mono rural') { $demandaMinima = 1; }
    elseif ($numeroDeFases == 'monofasico') { $demandaMinima = 30; }
    elseif ($numeroDeFases == 'bifasico') { $demandaMinima = 50; }
    elseif ($numeroDeFases == 'trifasico') { $demandaMinima = 100; }
    $gastoSemGerador = abs(($demandaMinima * 0.81) + $iluminacao + ($media * 0.81));
    $gastoSemGeradorRs = 'R$ ' . number_format($gastoSemGerador, 2, ',', '.');
    $gastoSemGeradorAno = $gastoSemGerador * 12;
    $gastoSemGeradorAnoRs = 'R$ ' . number_format($gastoSemGeradorAno, 2, ',', '.');
    $gastoComGerador = abs(($demandaMinima * 0.81) + $iluminacao);
    $gastoComGeradorRs = 'R$ ' . number_format($gastoComGerador, 2, ',', '.');
    $gastoComGeradorAno = $gastoComGerador * 12;
    $gastoComGeradorAnoRs = 'R$ ' . number_format($gastoComGeradorAno, 2, ',', '.');
    $diferencaGastos = $gastoSemGerador - $gastoComGerador;
    $diferencaGastosRs = 'R$ ' . number_format($diferencaGastos, 2, ',', '.');
    $diferencaGastosAno = $diferencaGastos * 12;
    $diferencaGastosAnoRs = 'R$ ' . number_format($diferencaGastosAno, 2, ',', '.');
    function calcularMargemEComissao($potenciaGerador) {
        $margem = 0; $comissao = 0;
        if ($potenciaGerador >= 0 && $potenciaGerador <= 30) { $margem = 1.5043478; $comissao = 0.07; $mobra = 136.76; }
        elseif ($potenciaGerador > 30 && $potenciaGerador <= 60) { $margem = 1.4537815; $comissao = 0.07; $mobra = 134.46; }
        elseif ($potenciaGerador > 60 && $potenciaGerador <= 114) { $margem = 1.4065041; $comissao = 0.06; $mobra = 250.45; }
        elseif ($potenciaGerador > 114) { $margem = 1.4065041; $comissao = 0.05; $mobra = 242.52; }
        return ['margem' => $margem, 'comissao' => $comissao, 'mobra' => $mobra];
    }
    $resultadoComissao = calcularMargemEComissao($potenciaGerador);
    $margem = $resultadoComissao['margem'];
    $comissao = $resultadoComissao['comissao'];
    $mobra = $resultadoComissao['mobra'];
    function calcularFixo($potenciaGerador) {
        if ($potenciaGerador >= 0 && $potenciaGerador < 3) { $fixo = 1025.65; }
        elseif ($potenciaGerador >= 3 && $potenciaGerador < 9) { $fixo = 1367.53; }
        elseif ($potenciaGerador >= 9 && $potenciaGerador < 10) { $fixo = 1538.47; }
        elseif ($potenciaGerador >= 10 && $potenciaGerador < 15) { $fixo = 2051.29; }
        elseif ($potenciaGerador >= 15 && $potenciaGerador < 20) { $fixo = 3418.81; }
        elseif ($potenciaGerador >= 20 && $potenciaGerador < 30) { $fixo = 7563.03; }
        elseif ($potenciaGerador >= 30 && $potenciaGerador < 40) { $fixo = 10084.04; }
        elseif ($potenciaGerador >= 40 && $potenciaGerador < 50) { $fixo = 11764.71; }
        elseif ($potenciaGerador >= 50 && $potenciaGerador < 60) { $fixo = 13445.38; }
        elseif ($potenciaGerador >= 60 && $potenciaGerador < 75) { $fixo = 16260.17; }
        elseif ($potenciaGerador >= 75 && $potenciaGerador < 82) { $fixo = 14000; }
        elseif ($potenciaGerador >= 82) { $fixo = 14000; }
        return $fixo;
    }
    $valorFixo = calcularFixo($potenciaGerador);
    function calcularParcela($taxa, $nper, $vp, $vf = 0, $tipo = 0) {
        if ($taxa == 0) { return -($vp + $vf) / $nper; }
        else { $valorParcela = ($vp * pow(1 + $taxa, $nper) + $vf) * $taxa / ((1 + $taxa * $tipo) * (pow(1 + $taxa, $nper) - 1)); return -$valorParcela; }
    }
    $taxa = 0.015; $nper = -36; $nper2 = -48; $nper3 = -60; $vp = 36000; $vf = 0; $tipo = 0;
    $valorParcela = calcularParcela($taxa, $nper, $vp, $vf, $tipo);
    $valorParcela2 = calcularParcela($taxa, $nper2, $vp, $vf, $tipo);
    $valorParcela3 = calcularParcela($taxa, $nper3, $vp, $vf, $tipo);
    $valorParcelaRs = 'R$ ' . number_format($valorParcela, 2, ',', '.');
    $valorParcela2Rs = 'R$ ' . number_format($valorParcela2, 2, ',', '.');
    $valorParcela3Rs = 'R$ ' . number_format($valorParcela3, 2, ',', '.');
    if ($desconto == "" || $desconto == "selecione um desconto") { $desconto = 1; }
    elseif ($desconto == "1%") { $desconto = 0.99; }
    elseif ($desconto == "2%") { $desconto = 0.98; }
    elseif ($desconto == "3%") { $desconto = 0.97; }
    else { $desconto = 1; }
    switch ($padrao) {
        case "2x50A": $padrao = 2512.88; $descPadrao = "2x50A"; break;
        case "3x50A": $padrao = 2941.22; $descPadrao = "3x50A"; break;
        case "3x63A": $padrao = 2815.24; $descPadrao = "3x63A"; break;
        case "3x80A": $padrao = 3190.17; $descPadrao = "3x80A"; break;
        case "3x100A": $padrao = 4870.36; $descPadrao = "3x100A"; break;
        case "3x125A": $padrao = 8539.65; $descPadrao = "3x125A"; break;
        case "3x150A": $padrao = 10366.42; $descPadrao = "3x150A"; break;
        case "3x175A": $padrao = 11279.80; $descPadrao = "3x175A"; break;
        case "3x200A": $padrao = 10187.84; $descPadrao = "3x200A"; break;
        case "TORRE 112,5 KVA": $padrao = 51581.25; $descPadrao = "TORRE 112,5 KVA"; break;
        case "TORRE 150 KVA": $padrao = 74497.50; $descPadrao = "TORRE 150 KVA"; break;
        case "TORRE 225 KVA": $padrao = 94445.00; $descPadrao = "TORRE 225 KVA"; break;
        case "TORRE 300 KVA": $padrao = 113983.74; $descPadrao = "TORRE 300 KVA"; break;
        case "TORRE 1 MVA": $padrao = 720000; $descPadrao = "TORRE 1 MVA"; break;
        case "": case "selecione um padrao": $padrao = 0; break;
        default: $padrao = 0; break;
    }
    $padraoRs = 'R$ ' . number_format($padrao, 2, ',', '.');
    if ($padrao == 720000) { $padrao = 0; $textoPadrao = "INCLUSO ADEQUAÇÃO DE PADRÃO - CABINE DE TRANSFORMAÇÃO 1MW"; }
    else if ($padrao <> 0) { $textoPadrao = "ENTRADA DE ENERGIA ($descPadrao) INCLUSO NO ORÇAMENTO: $padraoRs"; }
    $precoFinal =(((($precoKit + $opcao_adicional + $transformador) * $margem) + ($mobra * $qtdmodulosArredondado) + $valorFixo + $valoramais + $padrao) * $desconto) + $maoObraSolo ;
    $descrição2 = ""; $descrição3 = ""; $descrição4 = ""; $descrição5 = "";
    if ($usina === "1mwSolo") {
        $precoFinal = 4838398.60; $potenciaInversor = 975;
        $descrição2 = "360 CONECTOR MC4 MACHO/FEMEA 1000V TI-LANE";
        $descrição3 = "5000 CABO SOLAR PV 1.8KVCC 6MM PRETO NBR 16612";
        $descrição4 = "5000 CABO SOLAR PV 1.8KVCC 6MM VERMELHO NBR 16612";
        $descrição5 = "357 ESTRUTURA SOLO P/ 6 MOD.";
        $multiplicador = 13;
    }
    if ($usina === "75kwTelhado") { $precoFinal = $precoFinal *0.965; }
    $precoFinalRs = 'R$ ' . number_format($precoFinal, 2, ',', '.');
    $retornoVerde = $geracao * $inputValorCompensavel;
    $bandeiraAmarela = $inputValorCompensavel + 0.01885;
    $bandeiraVermelha = $inputValorCompensavel + 0.04463;
    $bandeiraVermelhaP1 = $inputValorCompensavel + 0.07877;
    $retornoAmarelo = $geracao * $bandeiraAmarela;
    $retornoVermelho = $geracao * $bandeiraVermelha;
    $retornoVermelhoP1 = $geracao * $bandeiraVermelhaP1;
    function calcularImposto($tributario, $retornoVerde) {
        $imposto = 0;
        switch ($tributario) {
            case "MEI": $imposto = 81.9; break;
            case "SIMPLES NACIONAL 7,3%": $imposto = $retornoVerde * 0.073; break;
            case "SIMPLES NACIONAL 9,5%": $imposto = $retornoVerde * 0.095; break;
            case "LUCRO PRESUMIDO": $imposto = $retornoVerde * 0.113; break;
            default: $imposto = false; break;
        }
        return $imposto;
    }
    $imposto = calcularImposto($tributario, $retornoVerde);
    $seguro = ($precoFinal * 0.007) / 12;
    $liquidoVerde = $retornoVerde - ($seguro + $manutencao + $imposto + $demanda);
    $liquidoAmarelo = $retornoAmarelo - $seguro - $manutencao - $imposto - $demanda;
    $liquidoVermelho = $retornoVermelho - $seguro - $manutencao - $imposto - $demanda;
    $liquidoVermelhoP1 = $retornoVermelhoP1 - $seguro - $manutencao - $imposto - $demanda;
    $rentabilidadeVerde = ($liquidoVerde / $precoFinal) * 100;
    $rentabilidadeAmarela = ($liquidoAmarelo / $precoFinal) * 100;
    $rentabilidadeVermelha = ($liquidoVermelho / $precoFinal)* 100;
    $rentabilidadeVermelhaP1 = ($liquidoVermelhoP1 / $precoFinal) * 100;
    $irradiacao = [5888, 5792, 5219, 4544, 3636, 3333, 3529, 4451, 4683, 5311, 5969, 6327];
    $jan = $irradiacao[0]; $fev = $irradiacao[1]; $mar = $irradiacao[2]; $abr = $irradiacao[3]; $mai = $irradiacao[4]; $jun = $irradiacao[5]; $jul = $irradiacao[6]; $ago = $irradiacao[7]; $set = $irradiacao[8]; $out = $irradiacao[9]; $nov = $irradiacao[10]; $dez = $irradiacao[11];
    $jan1 = $jan * 1.076687117 / 5265 * 4 * 0.95; $fev1 = $fev * 1.076687117 / 5265 * 4 * 0.95; $mar1 = $mar * 1.076687117 / 5265 * 4 * 0.95; $abr1 = $abr * 1.076687117 / 5265 * 4 * 0.95; $mai1 = $mai * 1.076687117 / 5265 * 4 * 0.95; $jun1 = $jun * 1.076687117 / 5265 * 4 * 0.95; $jul1 = $jul * 1.076687117 / 5265 * 4 * 0.95; $ago1 = $ago * 1.076687117 / 5265 * 4 * 0.95; $set1 = $set * 1.076687117 / 5265 * 4 * 0.95; $out1 = $out * 1.076687117 / 5265 * 4 * 0.95; $nov1 = $nov * 1.076687117 / 5265 * 4 * 0.95; $dez1 = $dez * 1.076687117 / 5265 * 4 * 0.95;
    $jan2 = $jan1 * $potenciaGerador * 30; $fev2 = $fev1 * $potenciaGerador * 30; $mar2 = $mar1 * $potenciaGerador * 30; $abr2 = $abr1 * $potenciaGerador * 30; $mai2 = $mai1 * $potenciaGerador * 30; $jun2 = $jun1 * $potenciaGerador * 30; $jul2 = $jul1 * $potenciaGerador * 30; $ago2 = $ago1 * $potenciaGerador * 30; $set2 = $set1 * $potenciaGerador * 30; $out2 = $out1 * $potenciaGerador * 30; $nov2 = $nov1 * $potenciaGerador * 30; $dez2 = $dez1 * $potenciaGerador * 30;
    $jan3 = number_format($jan2 / 1000, 3, '.', ''); $fev3 = number_format($fev2 / 1000, 3, '.', ''); $mar3 = number_format($mar2 / 1000, 3, '.', ''); $abr3 = number_format($abr2 / 1000, 3, '.', ''); $mai3 = number_format($mai2 / 1000, 3, '.', ''); $jun3 = number_format($jun2 / 1000, 3, '.', ''); $jul3 = number_format($jul2 / 1000, 3, '.', ''); $ago3 = number_format($ago2 / 1000, 3, '.', ''); $set3 = number_format($set2 / 1000, 3, '.', ''); $out3 = number_format($out2 / 1000, 3, '.', ''); $nov3 = number_format($nov2 / 1000, 3, '.', ''); $dez3 = number_format($dez2 / 1000, 3, '.', '');

    // ### ALTERAÇÃO INÍCIO: NOVAS FUNÇÕES FINANCEIRAS CORRIGIDAS ###
    // As funções antigas e os cálculos incorretos de VPL, TIR, ROI e Lucratividade foram removidos.
    // As funções abaixo implementam os conceitos financeiros corretamente.

    /**
     * Calcula o Valor Presente Líquido (VPL) de um projeto.
     */
    function calcularVPL($investimento, $fluxoCaixaAnual, $periodoAnos, $taxaDesconto) {
        $vpl = -$investimento;
        for ($ano = 1; $ano <= $periodoAnos; $ano++) {
            $vpl += $fluxoCaixaAnual / pow(1 + $taxaDesconto, $ano);
        }
        return $vpl;
    }

    /**
     * Calcula a Taxa Interna de Retorno (TIR) usando um método numérico.
     */
    function calcularTIR($investimento, $fluxoCaixaAnual, $periodoAnos, $maxIteracoes = 1000) {
        $taxaMin = -0.99; $taxaMax = 1.0;
        for ($i = 0; $i < $maxIteracoes; $i++) {
            $taxaMedia = ($taxaMin + $taxaMax) / 2;
            $vplCalculado = calcularVPL($investimento, $fluxoCaixaAnual, $periodoAnos, $taxaMedia);
            if (abs($vplCalculado) < 1e-5) { return $taxaMedia; }
            if ($vplCalculado > 0) { $taxaMin = $taxaMedia; } else { $taxaMax = $taxaMedia; }
        }
        return ($taxaMin + $taxaMax) / 2;
    }

    /**
     * Calcula o Retorno sobre o Investimento (ROI) para todo o período.
     */
    function calcularROI($investimento, $ganhoLiquidoTotal) {
        if ($investimento == 0) return 0;
        return ($ganhoLiquidoTotal - $investimento) / $investimento;
    }

    /**
     * Calcula o Índice de Lucratividade (IL).
     */
    function calcularIndiceLucratividade($vpl, $investimento) {
        if ($investimento == 0) return 0;
        return ($vpl + $investimento) / $investimento;
    }
    
    // ### ALTERAÇÃO FIM: NOVAS FUNÇÕES FINANCEIRAS ###

    // ### ALTERAÇÃO INÍCIO: CÁLCULO CENTRALIZADO DAS MÉTRICAS FINANCEIRAS ###
    // Usamos os resultados dos seus cálculos (como $precoFinal e $liquidoVerde) para alimentar as novas funções.

    // 1. Definição das variáveis de entrada para os cálculos financeiros
    $investimentoInicial = $precoFinal;
    $mediaLiquido = ($liquidoVerde + $liquidoAmarelo + $liquidoVermelho + $liquidoVermelhoP1) / 4;
    $fluxoCaixaAnual = $mediaLiquido * 12;
    $periodoAnos = 25; // Vida útil do projeto
    $taxaMinimaAtratividade = 0.10; // Exemplo de TMA de 10% a.a. (ajuste conforme necessário)
    
    // 2. Execução das novas funções
    $vpl = calcularVPL($investimentoInicial, $fluxoCaixaAnual, $periodoAnos, $taxaMinimaAtratividade);
    $tir = calcularTIR($investimentoInicial, $fluxoCaixaAnual, $periodoAnos);
    $ganhoLiquidoTotalPeriodo = $fluxoCaixaAnual * $periodoAnos;
    $roi = calcularROI($investimentoInicial, $ganhoLiquidoTotalPeriodo);
    $indiceLucratividade = calcularIndiceLucratividade($vpl, $investimentoInicial);

    // 3. Formatação dos resultados para exibição no PDF
    $VPL_formatado = 'R$ ' . number_format($vpl, 2, ',', '.');
    $TIR_formatado = number_format($tir * 100, 2, ',', '.') . '%';
    $ROI_formatado = number_format($roi * 100, 2, ',', '.') . '%';
    // O PDF pede "Taxa de Lucratividade", que geralmente é o Índice de Lucratividade (IL).
    $IL_formatado = number_format($indiceLucratividade, 2, ',', '.'); 

    // ### ALTERAÇÃO FIM: CÁLCULO CENTRALIZADO ###

    $formatoData = 'd/m/Y';
    $dataAtual = date($formatoData);


    // Criação do PDF
    $pdf = new TCPDF();
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(FALSE);
    
    // ... (Todo o seu código de criação de PDF das páginas 1 a 5 permanece o mesmo) ...
    
    // Primeira Página
    $pdf->AddPage();
    $pdf->Image('PGCV1.png', 0, 0, 210, 297);
    $pdf->SetFont('helvetica', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(34.2, 98, "Nome: $nome");
    $pdf->Text(34.2, 104, "Endereço: $endereco");
    $pdf->Text(34.2, 110, "Cidade: $cidade");
    $pdf->Text(34.6, 160, "Disponibilidade de área necessária: $metrosOcupados m²");
    $pdf->Text(34.6, 166.25, "Quantidade de Módulos Fotovoltáicos: $qtdmodulosArredondado Placas");
    $pdf->Text(34.6, 172.5, "Potência do Projeto: $potenciaGerador kWp");
    $pdf->Text(34.6, 178.75, "Geração Estimada: $geracao kWh");
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text(46, 223, "$dataAtual");

    // Segunda Página
    $pdf->AddPage();
    $pdf->Image('PGCV2.png', 0, 0, 210, 297);
    // ... (seu código do gráfico da segunda página aqui) ...
    $data = [$jan3, $fev3, $mar3, $abr3, $mai3, $jun3, $jul3, $ago3, $set3, $out3, $nov3, $dez3];
    $labels = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"];
    $x = 23; $y = 260; $barWidth = 5; $gap = 15; $maxBarHeight = 40;
    $maxValue = max($data);
    $molduraX = $x - 5; $molduraY = $y - $maxBarHeight - 7; $molduraWidth = count($data) * $gap; $molduraHeight = $maxBarHeight + 10;
    $pdf->SetDrawColor(0, 0, 0); $pdf->SetLineWidth(0.006); $pdf->Rect($molduraX, $molduraY, $molduraWidth, $molduraHeight, 'D');
    foreach ($data as $index => $value) {
        $barHeight = ($value / $maxValue) * $maxBarHeight;
        $pdf->SetFillColor(60, 179, 113);
        $pdf->Rect($x + ($index * $gap), $y - $barHeight, $barWidth, $barHeight, 'DF');
    }
    $pdf->SetFont('helvetica', '', 10); $pdf->SetTextColor(0, 0, 0);
    foreach ($labels as $index => $label) {
        $labelX = $x + ($index * $gap) + ($barWidth / 2) - (strlen($label) * 1.5);
        $labelY = $y + 5;
        $pdf->Text($labelX, $labelY, $label);
    }
    $pdf->Text(77, 216, "Geração Estimada: $geracao kWh");

    // Terceira Página
    $pdf->AddPage(); $pdf->Image('PGCV3.png', 0, 0, 210, 297);
    $pdf->AddPage(); $pdf->Image('PGCVEX.png', 0, 0, 210, 297);
    
    // Quarta Página
    $pdf->AddPage(); $pdf->Image('PGCV4.png', 0, 0, 210, 297);
    $pdf->SetFont('helvetica', 'B', 14); $pdf->SetTextColor(255, 0, 0);
    $pdf->SetTextColor(0, 0, 0); $pdf->Text(158, 141.5, "$percentualSolarArredondado %");
    
    // Quinta Página
    $pdf->AddPage(); $pdf->Image('PGCV5.png', 0, 0, 210, 297);
    // ... (seu código de descrição de componentes, payback, etc. permanece igual) ...
    $pdf->SetFont('helvetica', 'B', 13.5); $pdf->SetTextColor(50, 50, 50);
    // ...
    $pdf->SetFont('helvetica', 'B', 16); $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(152, 164, "$precoFinalRs");
    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->Text(26, 180, "36 X $valorParcelaRs");
    $pdf->Text(85, 180, "48 X $valorParcela2Rs");
    $pdf->Text(146, 180, "60 X $valorParcela3Rs");
    $pdf->SetFont('helvetica', 'B', 12); $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(59, 46, "$qtdmodulosArredondado");
    $pdf->Text(85, 46, "$potenciaInversor kW");
    $pdf->Text(110.5, 46, "$potenciaGerador kWp");
    $pdf->Text(139, 46, "$geracaoArredondado kWh");
    $pdf->Text(167, 46, "$geracaoAnual kWh");
    // ... (gráfico de payback) ...

    // Sexta Página (com a imagem undo.jpeg)
    $pdf->AddPage();
    $pdf->Image('PGCV6.png', 0, 0, 210, 297);
    $retornoVerdeRs = 'R$ ' . number_format($retornoVerde, 2, ',', '.');
    $retornoAmareloRs = 'R$ ' . number_format($retornoAmarelo, 2, ',', '.');
    $retornoVermelhoRs = 'R$ ' . number_format($retornoVermelho, 2, ',', '.');
    $retornoVermelhoP1Rs = 'R$ ' . number_format($retornoVermelhoP1, 2, ',', '.');
    $rentabilidadeVerdeRs = number_format($rentabilidadeVerde, 2, ',', '.') . '%';
    $rentabilidadeAmarelaRs = number_format($rentabilidadeAmarela, 2, ',', '.') . '%';
    $rentabilidadeVermelhaRs = number_format($rentabilidadeVermelha, 2, ',', '.') . '%';
    $rentabilidadeVermelhaP1Rs = number_format($rentabilidadeVermelhaP1, 2, ',', '.') . '%';
    $seguroRs = 'R$ ' . number_format($seguro, 2, ',', '.');
    $manutencaoRs = 'R$ ' . number_format($manutencao, 2, ',', '.');
    $impostoRs = 'R$ ' . number_format($imposto, 2, ',', '.');
    $demandaRs = 'R$ ' . number_format($demanda, 2, ',', '.');
    $liquidoVerdeRs = 'R$ ' . number_format($liquidoVerde, 2, ',', '.');
    $liquidoAmareloRs = 'R$ ' . number_format($liquidoAmarelo, 2, ',', '.');
    $liquidoVermelhoRs = 'R$ ' . number_format($liquidoVermelho, 2, ',', '.');
    $liquidoVermelhoP1Rs = 'R$ ' . number_format($liquidoVermelhoP1, 2, ',', '.');
    $mediaLiquidoRs = 'R$ ' . number_format($mediaLiquido, 2, ',', '.');
    
    $pdf->Text(61, 122, "$retornoVerdeRs");
    $pdf->Text(98, 122, "$retornoAmareloRs");
    $pdf->Text(135, 122, "$retornoVermelhoRs");
    $pdf->Text(172, 122, "$retornoVermelhoP1Rs");
    $pdf->Text(64, 134.5, "$seguroRs");
    $pdf->Text(101, 134.5, "$seguroRs");
    $pdf->Text(138, 134.5, "$seguroRs");
    $pdf->Text(175, 134.5, "$seguroRs");
    $pdf->Text(63, 145, "$manutencaoRs");
    $pdf->Text(100, 145, "$manutencaoRs");
    $pdf->Text(137, 145, "$manutencaoRs");
    $pdf->Text(174, 145, "$manutencaoRs");
    $pdf->Text(64, 156.5, "$impostoRs");
    $pdf->Text(101, 156.5, "$impostoRs");
    $pdf->Text(138, 156.5, "$impostoRs");
    $pdf->Text(175, 156.5, "$impostoRs");
    $pdf->Text(63, 167.5, "$demandaRs");
    $pdf->Text(100, 167.5, "$demandaRs");
    $pdf->Text(137, 167.5, "$demandaRs");
    $pdf->Text(174, 167.5, "$demandaRs");
    $pdf->Text(66, 180, "$rentabilidadeVerdeRs");
    $pdf->Text(103, 180, "$rentabilidadeAmarelaRs");
    $pdf->Text(141, 180, "$rentabilidadeVermelhaRs");
    $pdf->Text(177, 180, "$rentabilidadeVermelhaP1Rs");
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Text(61, 192.2, "$liquidoVerdeRs");
    $pdf->Text(98, 192.2, "$liquidoAmareloRs");
    $pdf->Text(135, 192.2, "$liquidoVermelhoRs");
    $pdf->Text(172, 192.2, "$liquidoVermelhoP1Rs");
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(172, 203.5, "$mediaLiquidoRs");

    // ### ALTERAÇÃO INÍCIO: USANDO AS NOVAS VARIÁVEIS FORMATADAS NO PDF ###
    // As variáveis antigas ($VPLP, $TIRP, etc.) foram substituídas pelas novas
    // que contêm os resultados dos cálculos financeiros corretos.
    $pdf->SetFont('helvetica', 10);
    $pdf->Text(27, 220, "$VPL_formatado");
    $pdf->Text(80, 220, "$TIR_formatado");
    $pdf->Text(127, 220, "$IL_formatado"); // Usando o Índice de Lucratividade
    $pdf->Text(172, 220, "$ROI_formatado");
    $pdf->Text(80, 98, "Tributação vigente: $tributario");
    // ### ALTERAÇÃO FIM: FIM DA ATUALIZAÇÃO DO PDF ###

    // ... (seu código do gráfico da página 6 e o restante das páginas permanecem os mesmos) ...
    // ...
    
    // Finalização do PDF e Conexão com Banco de Dados
    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I');

    // ... (Seu código de conexão com o banco de dados e INSERT permanecem os mesmos) ...
    // DICA: Você pode querer adicionar os novos valores de VPL, TIR e ROI ao seu INSERT no banco de dados.
}
?>