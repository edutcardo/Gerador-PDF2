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
    $desconto = $_POST['desconto'];
    $valoramais = isset($_POST['valoramais']) && $_POST['valoramais'] !== '' ? floatval($_POST['valoramais']) : 0;
    $inputConcessionaria = $_POST['inputConcessionaria'];
    $inputValorCompensavel = $_POST['inputValorCompensavel'];
    $valorSafras = $_POST['valorSafras'];
    $qtdSafras = $_POST['qtdSafras'];
    $dataSafras = $_POST['dataSafras'];
    //Tributação

    function calcularTributario($potenciaInversor)
    {
        if ($potenciaInversor <= 75) {
            $tributario = "MEI";
        } elseif ($potenciaInversor > 75 && $potenciaInversor <= 350) {
            $tributario = "SIMPLES NACIONAL 7,3%";
        } elseif ($potenciaInversor > 350 && $potenciaInversor <= 720) {
            $tributario = "SIMPLES NACIONAL 9,5%";
        } elseif ($potenciaInversor > 720) {
            $tributario = "LUCRO PRESUMIDO";
        } else {
            $tributario = false; // Este caso não deve ser alcançado com base na lógica.
        }

        return $tributario;
    }
    $tributario = calcularTributario($potenciaInversor);

    $precoDemanda = 50; // Preço da demanda
    $qtdDemanda = 3; // Quantidade de demanda

    function calcularDemanda($potenciaInversor, $precoDemanda, $qtdDemanda, $iluminacao, $media)
    {
        // Valores fixos definidos
        $AA12 = 1;       // Equivalente a 'PLANILHA RESUMO'!AA12
        $TUSDG = 8.6760; // Equivalente a 'PLANILHA RESUMO'!R26
        $S77 = 0;        // Ajuste conforme necessário

        // Cálculo principal
        if ($AA12 * $potenciaInversor <= 75) {
            $resultado = ($media * 0.83) + $iluminacao;
        } else {
            $resultado = ($potenciaInversor * $AA12 * $TUSDG) + $S77;
        }

        // Subtrações
        $calculoFinal = $resultado - ($precoDemanda * $qtdDemanda);

        // A função abs() transforma qualquer número negativo em positivo
        return abs($calculoFinal);
    }

    $demanda = calcularDemanda($potenciaInversor, $precoDemanda, $qtdDemanda, $iluminacao, $media);
    // Cálculos iniciais da proposta

    $geracao = $potenciaGerador * 3.9 * 30;
    $media = $geracao;
    $qtdmodulos = ($potenciaGerador * 1000) / $potenciaModulo;
    $qtdmodulosArredondado = round($qtdmodulos);
    $metrosOcupados = $qtdmodulosArredondado * 2.9;

    // Cálculos PGCV4
    $peso = $qtdmodulosArredondado * 33;
    $percentualSolar = ($geracao / $media) * 100;
    $percentualSolarArredondado = round($percentualSolar);
    $mediaArredondado = round($media);
    $geracaoArredondado = round($geracao);
    $geracaoAnual = $geracaoArredondado * 12;

    function calcularManutencao($qtdmodulosArredondado)
    {
        if ($qtdmodulosArredondado >= 10) {
            $manutencao = (150 / pow($qtdmodulosArredondado, 0.485) + 10 - 20 / $qtdmodulosArredondado) * $qtdmodulosArredondado;
        } else {
            $manutencao = (150 / pow(10, 0.485) + 10 - 20 / 10) * 10;
        }

        return $manutencao / 12; // Divide o resultado por 12 conforme a fórmula original
    }

    $manutencao = calcularManutencao($qtdmodulosArredondado);

    //Cálculos PGCV5
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


    function calcularMargemEComissao($potenciaGerador)
    {
        $margem = 0;
        $comissao = 0;

        if ($potenciaGerador >= 0 && $potenciaGerador <= 20) {
            $margem = 1.4786325;
            $comissao = 0.07;
            $mobra = 136.76;
        } elseif ($potenciaGerador > 20 && $potenciaGerador <= 60) {
            $margem = 1.4537815;
            $comissao = 0.07;
            $mobra = 134.46;
        } elseif ($potenciaGerador > 60 && $potenciaGerador <= 114) {
            $margem = 1.3643089;
            $comissao = 0.06;
            $mobra = 250.45;
        } elseif ($potenciaGerador > 114) {
            $margem = 1.3213386;
            $comissao = 0.05;
            $mobra = 242.52;
        }

        return [
            'margem' => $margem,
            'comissao' => $comissao,
            'mobra' => $mobra
        ];
    }
    // Chama a função e armazena os resultados
    $resultadoComissao = calcularMargemEComissao($potenciaGerador);

    // Acessa os valores
    $margem = $resultadoComissao['margem'];
    $comissao = $resultadoComissao['comissao'];
    $mobra = $resultadoComissao['mobra'];

    function calcularFixo($potenciaGerador)
    {

        if ($potenciaGerador >= 0 && $potenciaGerador < 3) {
            $fixo = 1025.65;
        } elseif ($potenciaGerador >= 3 && $potenciaGerador < 9) {
            $fixo = 1367.53;
        } elseif ($potenciaGerador >= 9 && $potenciaGerador < 10) {
            $fixo = 1538.47;
        } elseif ($potenciaGerador >= 10 && $potenciaGerador < 15) {
            $fixo = 2051.29;
        } elseif ($potenciaGerador >= 15 && $potenciaGerador < 20) {
            $fixo = 3418.81;
        } elseif ($potenciaGerador >= 20 && $potenciaGerador < 30) {
            $fixo = 7563.03;
        } elseif ($potenciaGerador >= 30 && $potenciaGerador < 40) {
            $fixo = 10084.04;
        } elseif ($potenciaGerador >= 40 && $potenciaGerador < 50) {
            $fixo = 11764.71;
        } elseif ($potenciaGerador >= 50 && $potenciaGerador < 60) {
            $fixo = 13445.38;
        } elseif ($potenciaGerador >= 60 && $potenciaGerador < 75) {
            $fixo = 16260.17;
        } elseif ($potenciaGerador >= 75 && $potenciaGerador < 82) {
            $fixo = 0;
        } elseif ($potenciaGerador >= 82 && $potenciaGerador <= 112.2) {
            $fixo = 0;
        }

        return $fixo;
    }
    $valorFixo = calcularFixo($potenciaGerador);


    // Cálculo do desconto no preço do kit
    if ($desconto == "" || $desconto == "selecione um desconto") {
        $desconto = 1;
    } elseif ($desconto == "1%") {
        $desconto = 0.99;
    } elseif ($desconto == "2%") {
        $desconto = 0.98;
    } elseif ($desconto == "3%") {
        $desconto = 0.97;
    } else {
        $desconto = 1; // Valor padrão caso nenhum caso corresponda
    }

    // Condicional do preço do padrão de energia
    switch ($padrao) {
        case "2x50A":
            $padrao = 2512.88;
            break;
        case "3x50A":
            $padrao = 2941.22;
            break;
        case "3x63A":
            $padrao = 2815.24;
            break;
        case "3x80A":
            $padrao = 3190.17;
            break;
        case "3x100A":
            $padrao = 4870.36;
            break;
        case "3x125A":
            $padrao = 8539.65;
            break;
        case "3x150A":
            $padrao = 10366.42;
            break;
        case "3x175A":
            $padrao = 11279.8;
            break;
        case "3x200A":
            $padrao = 12969.57;
            break;
        case "":
        case "selecione um padrao":
            $padrao = 0;
            break;
        default:
            $padrao = 0; // Caso não corresponda a nenhuma opção válida
            break;
    }
    function calcularImposto($tributario, $retornoVerde)
    {
        $imposto = 0; // Inicializa a variável para evitar erros

        switch ($tributario) {
            case "MEI":
                $imposto = 76.6;

                break;
            case "SIMPLES NACIONAL 7,3%":
                $imposto = $retornoVerde * 0.073;

                break;
            case "SIMPLES NACIONAL 9,5%":
                $imposto = $retornoVerde * 0.095;

                break;
            case "LUCRO PRESUMIDO":
                $imposto = $retornoVerde * 0.113;

                break;
            default:
                $imposto = false; // Caso o valor de $tributario não seja reconhecido


                break;
        }

        return $imposto;
    }



    $precoFinal = ($precoKit);
    $precoFinalRs = 'R$ ' . number_format($precoFinal, 2, ',', '.');

    $payback = $precoFinal / $diferencaGastosAno;
    $paybackArredondado = round($payback);
    $retorno25anos = $diferencaGastosAno * 25;
    $retorno25anosRs = 'R$ ' . number_format($retorno25anos, 2, ',', '.');

    //Cálculos investidor
    $bandeiraAmarela = $inputValorCompensavel + 0.01885;
    $bandeiraVermelha = $inputValorCompensavel + 0.04463;
    $bandeiraVermelhaP1 = $inputValorCompensavel + 0.07877;
    $retornoVerde = $geracao * $inputValorCompensavel;
    $retornoAmarelo = $geracao * $bandeiraAmarela;
    $retornoVermelho = $geracao * $bandeiraVermelha;
    $retornoVermelhoP1 = $geracao * $bandeiraVermelhaP1;

    $imposto = calcularImposto($tributario, $retornoVerde);
    $seguro = ($precoFinal * 0.007) / 12;


    $rentabilidadeVerde = ($retornoVerde / $precoFinal) * 100;
    $rentabilidadeAmarela = ($retornoAmarelo / $precoFinal) * 100;
    $rentabilidadeVermelha = ($retornoVermelho / $precoFinal) * 100;
    $rentabilidadeVermelhaP1 = ($retornoVermelhoP1 / $precoFinal) * 100;
    $liquidoVerde = $retornoVerde - $seguro - $manutencao - $imposto - $demanda;
    $liquidoAmarelo = $retornoAmarelo - $seguro - $manutencao - $imposto - $demanda;
    $liquidoVermelho = $retornoVermelho - $seguro - $manutencao - $imposto - $demanda;
    $liquidoVermelhoP1 = $retornoVermelhoP1 - $seguro - $manutencao - $imposto - $demanda;
    $liquidoVerdeAnual = $liquidoVerde * 12;

    function calcularParcela($taxa, $nper, $vp, $vf = 0, $tipo = 0)
    {
        bcscale(10);
        $taxa_str = (string) $taxa;
        $nper_str = (string) abs($nper);
        $vp_str = (string) $vp;
        $vf_str = (string) $vf;
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
    $valorParcela = calcularParcela($taxa, $nper1, $vp, $vf, $tipo);
    $valorParcelaRs = 'R$ ' . number_format(abs((float) $valorParcela), 2, ',', '.');
    $valorParcela2 = calcularParcela($taxa, $nper2, $vp, $vf, $tipo);
    $valorParcela2Rs = 'R$ ' . number_format(abs((float) $valorParcela2), 2, ',', '.');
    $valorParcela3 = calcularParcela($taxa, $nper3, $vp, $vf, $tipo);
    $valorParcela3Rs = 'R$ ' . number_format(abs((float) $valorParcela3), 2, ',', '.');




    $irradiacao = [5888, 5792, 5219, 4544, 3636, 3333, 3529, 4451, 4683, 5311, 5969, 6327];

    //Cálculo de irradiação
    $jan = $irradiacao[0];
    $fev = $irradiacao[1];
    $mar = $irradiacao[2];
    $abr = $irradiacao[3];
    $mai = $irradiacao[4];
    $jun = $irradiacao[5];
    $jul = $irradiacao[6];
    $ago = $irradiacao[7];
    $set = $irradiacao[8];
    $out = $irradiacao[9];
    $nov = $irradiacao[10];
    $dez = $irradiacao[11];

    $jan1 = $jan * 1.076687117 / 5265 * 4 * 0.95;
    $fev1 = $fev * 1.076687117 / 5265 * 4 * 0.95;
    $mar1 = $mar * 1.076687117 / 5265 * 4 * 0.95;
    $abr1 = $abr * 1.076687117 / 5265 * 4 * 0.95;
    $mai1 = $mai * 1.076687117 / 5265 * 4 * 0.95;
    $jun1 = $jun * 1.076687117 / 5265 * 4 * 0.95;
    $jul1 = $jul * 1.076687117 / 5265 * 4 * 0.95;
    $ago1 = $ago * 1.076687117 / 5265 * 4 * 0.95;
    $set1 = $set * 1.076687117 / 5265 * 4 * 0.95;
    $out1 = $out * 1.076687117 / 5265 * 4 * 0.95;
    $nov1 = $nov * 1.076687117 / 5265 * 4 * 0.95;
    $dez1 = $dez * 1.076687117 / 5265 * 4 * 0.95;

    $jan2 = $jan1 * $potenciaGerador * 30;
    $fev2 = $fev1 * $potenciaGerador * 30;
    $mar2 = $mar1 * $potenciaGerador * 30;
    $abr2 = $abr1 * $potenciaGerador * 30;
    $mai2 = $mai1 * $potenciaGerador * 30;
    $jun2 = $jun1 * $potenciaGerador * 30;
    $jul2 = $jul1 * $potenciaGerador * 30;
    $ago2 = $ago1 * $potenciaGerador * 30;
    $set2 = $set1 * $potenciaGerador * 30;
    $out2 = $out1 * $potenciaGerador * 30;
    $nov2 = $nov1 * $potenciaGerador * 30;
    $dez2 = $dez1 * $potenciaGerador * 30;

    $jan3 = number_format($jan2 / 1000, 3, '.', '');
    $fev3 = number_format($fev2 / 1000, 3, '.', '');
    $mar3 = number_format($mar2 / 1000, 3, '.', '');
    $abr3 = number_format($abr2 / 1000, 3, '.', '');
    $mai3 = number_format($mai2 / 1000, 3, '.', '');
    $jun3 = number_format($jun2 / 1000, 3, '.', '');
    $jul3 = number_format($jul2 / 1000, 3, '.', '');
    $ago3 = number_format($ago2 / 1000, 3, '.', '');
    $set3 = number_format($set2 / 1000, 3, '.', '');
    $out3 = number_format($out2 / 1000, 3, '.', '');
    $nov3 = number_format($nov2 / 1000, 3, '.', '');
    $dez3 = number_format($dez2 / 1000, 3, '.', '');

    function calcularVPL($precoFinal, $fluxoCaixaAnual, $taxaDesconto, $periodoAnos)
    {
        $VPL = -$precoFinal; // Começa com o investimento inicial como um fluxo negativo
        for ($ano = 1; $ano <= $periodoAnos; $ano++) {
            $VPL += $fluxoCaixaAnual / pow(1 + $taxaDesconto, $ano);
        }
        return $VPL;
    }

    // Assumindo valores aproximados
    $fluxoCaixaAnual = 72000; // Exemplo de um fluxo de caixa médio anual
    $taxaDesconto = 0.08; // Exemplo de taxa de desconto anual de 8%
    $periodoAnos = 25; // Considerando a vida útil do projeto

    // Calcular o VPL
    $VPL = calcularVPL($precoFinal, $fluxoCaixaAnual, $taxaDesconto, $periodoAnos);
    $VPLP = 'R$ ' . number_format($VPL, 2, ',', '.');

    // Estimação de Payback
    $fluxoCaixaAnual = 7200; // Exemplo de fluxo de caixa anual

    $payback = $precoFinal / $fluxoCaixaAnual; // Payback estimado em anos

    // Estimação de porcentagem do payback com relação aos 25 anos
    $percentualPayback = ($payback / 25) * 100; // Porcentagem do payback no total de 25 anos

    function calcularVPL_variavel($investimento, $fluxosDeCaixa, $taxaDesconto)
    {
        $vpl = -$investimento;
        foreach ($fluxosDeCaixa as $ano => $fluxo) {
            $vpl += $fluxo / pow(1 + $taxaDesconto, $ano + 1);
        }
        return $vpl;
    }

    /**
     * Calcula a Taxa Interna de Retorno (TIR) para um fluxo de caixa VARIÁVEL.
     */
    function calcularTIR_variavel($investimento, $fluxosDeCaixa, $maxIteracoes = 1000, $precisao = 1e-7)
    {
        $taxaMin = -0.99;
        $taxaMax = 1.0;
        for ($i = 0; $i < $maxIteracoes; $i++) {
            $taxaMedia = ($taxaMin + $taxaMax) / 2;
            if (abs($taxaMedia) < $precisao)
                $taxaMedia = $precisao;
            $vplCalculado = calcularVPL_variavel($investimento, $fluxosDeCaixa, $taxaMedia);
            if (abs($vplCalculado) < $precisao) {
                return $taxaMedia;
            }
            if ($vplCalculado > 0) {
                $taxaMin = $taxaMedia;
            } else {
                $taxaMax = $taxaMedia;
            }
        }
        return ($taxaMin + $taxaMax) / 2;
    }

    /**
     * Calcula o Retorno sobre o Investimento (ROI) para todo o período.
     */
    function calcularROI($investimento, $ganhoLiquidoTotal)
    {
        if ($investimento == 0)
            return 0;
        return ($ganhoLiquidoTotal - $investimento) / $investimento;
    }

    /**
     * Calcula a "Taxa de Lucratividade" (Margem Líquida Média).
     */
    function calcularTaxaLucratividade($receitaMedia, $liquidoMedio)
    {
        if ($receitaMedia == 0)
            return 0;
        return $liquidoMedio / $receitaMedia;
    }

    /**
     * =======================================================================
     * CÁLCULO CENTRALIZADO DAS MÉTRICAS FINANCEIRAS
     * =======================================================================
     */

    $investimentoInicial = $precoFinal;
    $periodoAnos = 25;

    $mediaLiquidoMensal = ($liquidoVerde + $liquidoAmarelo + $liquidoVermelho + $liquidoVermelhoP1) / 4;
    $fluxoCaixaPrimeiroAno = $mediaLiquidoMensal * 12;

    // --- PARÂMETROS PARA AJUSTE ---
// O PDF não informa estas taxas, então são hipóteses para você ajustar.
    $taxaCrescimentoAnualFluxoCaixa = 0.00; // Altere este valor para alinhar com o PDF!
    $taxaMinimaAtratividade = 0.10;          // TMA de 10% a.a. para o cálculo do VPL.

    $fluxosDeCaixaAnuais = [];
    $ganhoLiquidoTotalPeriodo = 0;
    for ($ano = 0; $ano < $periodoAnos; $ano++) {
        $fluxoDoAno = $fluxoCaixaPrimeiroAno * pow(1 + $taxaCrescimentoAnualFluxoCaixa, $ano);
        $fluxosDeCaixaAnuais[] = $fluxoDoAno;
        $ganhoLiquidoTotalPeriodo += $fluxoDoAno;
    }

    // Execução das funções financeiras com os dados corretos
    $vpl = calcularVPL_variavel($investimentoInicial, $fluxosDeCaixaAnuais, $taxaMinimaAtratividade);
    $tir = calcularTIR_variavel($investimentoInicial, $fluxosDeCaixaAnuais);
    $roi = calcularROI($investimentoInicial, $ganhoLiquidoTotalPeriodo);

    $receitaMediaMensal = ($retornoVerde + $retornoAmarelo + $retornoVermelho + $retornoVermelhoP1) / 4;
    $taxaLucratividade = calcularTaxaLucratividade($receitaMediaMensal, $mediaLiquidoMensal);

    // Formatação dos resultados para exibição no PDF
    $VPL_formatado = 'R$ ' . number_format($vpl, 2, ',', '.');
    $TIR_formatado = number_format($tir * 100, 2, ',', '.') . '%';
    $ROI_formatado = number_format($roi * 100, 2, ',', '.') . '%';
    $TaxaLucratividade_formatada = number_format($taxaLucratividade * 100, 2, ',', '.') . '%';

    $geracao_formatado = number_format($geracao, 2, ',', '.');
    $potenciaGerador_formatado = number_format($potenciaGerador, 2, ',', '.');
    $metrosOcupados_formatado = number_format($metrosOcupados, 2, ',', '.');


    // Data atual
    $formatoData = 'd/m/Y';
    $dataAtual = date($formatoData);

    // Criação do PDF
    $pdf = new TCPDF();
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Primeira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCOC1.png', 0, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(34.2, 98, "Nome: $nome");
    $pdf->Text(34.2, 104, "Endereço: $endereco");
    $pdf->Text(34.2, 110, "Cidade: $cidade");
    $pdf->Text(34.2, 138, "UC $uc");

    $pdf->Text(34.6, 160, "Disponibilidade de área necessária: $metrosOcupados_formatado m²");
    $pdf->Text(34.6, 166.25, "Quantidade de Módulos Fotovoltáicos: $qtdmodulosArredondado Placas");
    $pdf->Text(34.6, 172.5, "Potência do Projeto: $potenciaGerador_formatado kWp");
    $pdf->Text(34.6, 178.75, "Geração Estimada: $geracao_formatado kWh");

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text(180, 280, "$dataAtual");


    // Segunda Página (com a imagem genérica e gráfico)
    $pdf->AddPage();  // Adiciona a segunda página
    $pdf->Image('PGCOC2.png', 0, 0, 210, 297);
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // --- INÍCIO DOS DADOS DE EXEMPLO ---
// Supondo que estas são as suas variáveis originais de CONSUMO (Barras Vermelhas)
    $dataGeracao = [$jan3, $fev3, $mar3, $abr3, $mai3, $jun3, $jul3, $ago3, $set3, $out3, $nov3, $dez3];

    // --- VOCÊ PRECISA INSERIR SUAS VARIÁVEIS DE GERAÇÃO AQUI ---
// Criei dados fictícios para GERAÇÃO (Barras Verdes) para o exemplo funcionar.
// Substitua pelas suas variáveis reais ($geracaoJan, etc.)


    $labels = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"]; // Rótulos (meses)

    // Definindo as cores (RGB) baseadas na imagem alvo
// Verde (para borda da Geração)
    $colGerR = 0;
    $colGerG = 128;
    $colGerB = 0;
    // Vermelho (para preenchimento do Consumo)
    $colConR = 204;
    $colConG = 0;
    $colConB = 0;

    // Posições e tamanho do gráfico
    $x = 46;  // Posição X inicial
    $y = 256; // Posição Y da linha de base
    $barWidth = 4; // Largura individual de CADA barra (reduzi um pouco para caber o par)
    $gap = 11;  // Distância entre o INÍCIO de um grupo de meses e o próximo. Deve ser maior que 2 * $barWidth.
    $maxBarHeight = 40; // Altura máxima do gráfico

    // --- CRUCIAL: Determinando o maior valor GLOBAL para escalar as barras corretamente ---
// Precisamos saber qual é o maior valor entre TODAS as gerações e TODOS os consumos.
    $maxValConsumo = empty($dataConsumo) ? 0 : max($dataConsumo);
    $maxValGeracao = empty($dataGeracao) ? 0 : max($dataGeracao);
    // O valor máximo para a escala é o maior entre os dois conjuntos
    $maxValueGlobal = max($maxValConsumo, $maxValGeracao);

    // Prevenção contra divisão por zero se os dados estiverem vazios
    if ($maxValueGlobal == 0) {
        $maxValueGlobal = 1;
    }


    // Configuração de fonte para o gráfico
    $pdf->SetFont('helvetica', '', 8); // Fonte ligeiramente menor para ajudar a caber
    $pdf->SetLineWidth(0.3); // Espessura da linha para a barra verde

    // --- LOOP DE DESENHO PRINCIPAL ---
    foreach ($labels as $index => $label) {

        // Obter valores atuais
        // Usa 0 se não houver dado para aquele índice para evitar erros
        $valGeracao = isset($dataGeracao[$index]) ? $dataGeracao[$index] : 0;
        $valConsumo = isset($dataConsumo[$index]) ? $dataConsumo[$index] : 0;

        // Calculando a altura proporcional de cada barra usando o Máximo Global
        $hGeracao = ($valGeracao / $maxValueGlobal) * $maxBarHeight;
        $hConsumo = ($valConsumo / $maxValueGlobal) * $maxBarHeight;

        // --- CÁLCULO DAS POSIÇÕES X ---
        // Posição X da primeira barra (Geração - Verde)
        $xPosGeracao = $x + ($index * $gap);
        // Posição X da segunda barra (Consumo - Vermelho), posicionada logo após a primeira
        $xPosConsumo = $xPosGeracao + $barWidth;


        // --- DESENHAR BARRA 1: GERAÇÃO (Borda Verde, Fundo Branco) ---
        // Conforme a imagem: Apenas a borda é colorida ('D' = Draw border)
        $pdf->SetDrawColor($colGerR, $colGerG, $colGerB);
        // Se quiser o fundo branco explicitamente, descomente a linha abaixo e use 'DF' no Rect
        // $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($xPosGeracao, $y - $hGeracao, $barWidth, $hGeracao, 'D');


        // --- DESENHAR BARRA 2: CONSUMO (Preenchimento Vermelho) ---
        // Conforme a imagem: A barra é preenchida ('F' = Fill)
        $pdf->SetFillColor($colConR, $colConG, $colConB);
        // Opcional: Se quiser uma borda na barra vermelha também, defina SetDrawColor e use 'DF'

        // --- RÓTULOS DOS MESES (Abaixo das barras) ---
        $pdf->SetTextColor(0, 0, 0);
        // Precisamos centralizar o rótulo abaixo do PAR de barras.
        // O centro matemático do par de barras é a posição X da primeira barra + a largura de uma barra.
        $centerXp = $xPosGeracao + $barWidth;

        // Calcula a largura do texto para centralizar perfeitamente
        $textWidth = $pdf->GetStringWidth($label);
        $labelX = $centerXp - ($textWidth / 2);
        $labelY = $y + 5; // Posição logo abaixo da linha de base
        $pdf->Text($labelX, $labelY, $label);

        /*
        // --- OPCIONAL: VALORES ACIMA DAS BARRAS ---
        // Com barras duplas, os valores podem ficar encavalados. Se quiser tentar, descomente abaixo.
        $pdf->SetFont('helvetica', '', 7);
        // Valor Geração
        if($valGeracao > 0) {
             $valGerX = ($xPosGeracao + $barWidth/2) - ($pdf->GetStringWidth($valGeracao)/2);
             $pdf->Text($valGerX, $y - $hGeracao - 2, $valGeracao);
        }
        // Valor Consumo
        if($valConsumo > 0) {
             $valConX = ($xPosConsumo + $barWidth/2) - ($pdf->GetStringWidth($valConsumo)/2);
             $pdf->Text($valConX, $y - $hConsumo - 2, $valConsumo);
        }
        $pdf->SetFont('helvetica', '', 8); // Retorna a fonte
        */
    }

    // --- LINHA DE BASE DO GRÁFICO (Eixo X) ---
    $pdf->SetDrawColor(150, 150, 150); // Cinza claro
    $pdf->SetLineWidth(0.1);
    $larguraTotalGrafico = (count($labels) * $gap) + $barWidth; // Cálculo aproximado da largura total
    $pdf->Line($x - 2, $y, $x + $larguraTotalGrafico, $y);

    // --- LEGENDA SIMPLES (Essencial para gráficos de barras duplas) ---
    $legendX = $x + 10;
    $legendY = $y + 15;

    $pdf->SetFont('helvetica', '', 9);

    // Item Geração
    $pdf->SetDrawColor($colGerR, $colGerG, $colGerB);
    $pdf->Rect($legendX, $legendY, 8, 4, 'D');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(68, 271, "Geração");

    // Terceira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCOC3.png', 0, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à sexta página
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Text(52, 172, "CHINT/SAJ/SOLIS/SOLPLANET");
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Text(62, 176, "$potenciaInversor kW - MONO 220V");

    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->Text(126, 173, "10 ANOS");
    $pdf->SetFont('helvetica', 'B', 9.5);
    $pdf->Text(51, 187, "AESOLAR/ZNSHINE/SINE/RENEPV");
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Text(74, 191, "$potenciaModulo W");

    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->Text(126, 188, "12 ANOS");



    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetTextColor(0, 0, 0);

    $geracaoAnual_formatado = number_format($geracaoAnual, 2, ',', '.');


    $pdf->Text(65, 238, "$qtdmodulosArredondado");
    $pdf->Text(92, 238, "$potenciaInversor kW");
    $pdf->Text(119, 238, "$potenciaGerador_formatado");
    $pdf->Text(146, 238, "$geracao_formatado");
    $pdf->Text(174.5, 238, "$geracaoAnual_formatado");

    $pdf->SetFont('helvetica', 'B', 12);

    // Sexta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCOC4.png', 0, 0, 210, 297);
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
    $mediaLiquido = ($liquidoVerde + $liquidoAmarelo + $liquidoVermelho + $liquidoVermelhoP1) / 4;
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

    $pdf->SetFont('helvetica', 10);
    // Formatação dos resultados para exibição no PDF
    $pdf->Text(27, 220, "$VPL_formatado");
    $pdf->Text(80, 220, "$TIR_formatado");
    $pdf->Text(127, 220, "$TaxaLucratividade_formatada");
    $pdf->Text(172, 220, "$ROI_formatado");
    $pdf->Text(80, 98, "Tributação vigente: $tributario");



    // Dados para o gráfico
    $values = [$retornoVerde, $liquidoVerde, $imposto, $demanda, $seguro, $manutencao];
    $labels = ['Receita', 'Líquido', 'Impostos', 'Demanda', 'Seguro', 'Opex/Limpeza'];

    // Configurações do gráfico
    $x = 27; // Margem inicial
    $y = 240; // Posição vertical inicial
    $barWidth = 15; // Largura das barras
    $maxBarHeight = 30; // Altura máxima das barras
    $gap = 10;
    $pageWidth = 170; // Largura total da área utilizável (A4 menos margens)

    // Ajustar espaçamento entre barras dinamicamente
    $chartWidth = (count($values) * $barWidth);
    $gap = ($pageWidth - $chartWidth) / (count($values) - 1);

    // Limite superior do gráfico (valor máximo representado)
    $limitValue = max($values) > 0 ? max($values) : 1; // Evitar divisão por zero
    $scalingFactor = $maxBarHeight / $limitValue;

    // Cores das barras
    $colors = [
        [70, 130, 180], // Blue
        [220, 20, 60],  // Red
        [85, 107, 47],  // Green
        [128, 0, 128],  // Purple
        [0, 128, 128],  // Teal
        [255, 165, 0]   // Orange
    ];

    // Desenhar barras
    foreach ($values as $index => $value) {
        $barHeight = $value * $scalingFactor; // Altura da barra proporcional ao valor
        $pdf->SetFillColor($colors[$index][0], $colors[$index][1], $colors[$index][2]);
        $pdf->Rect($x, $y + ($maxBarHeight - $barHeight), $barWidth, $barHeight, 'DF'); // Desenhar barra

        // Adicionar valor acima da barra
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Text($x, $y + ($maxBarHeight - $barHeight) - 7, 'R$' . number_format($value, 2, ',', '.'));

        // Adicionar rótulo abaixo da barra
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Text($x, $y + $maxBarHeight + 5, $labels[$index]);

        // Incrementar posição horizontal
        $x += $barWidth + $gap;
    }


    $pdf->AddPage();
    $pdf->Image('PGCOCANALISE.png', 0, 0, 210, 297);


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

    // Verifica se os dados necessários existem e são válidos
    if (isset($precoFinal) && is_numeric($precoFinal) && isset($diferencaGastosAno) && is_numeric($diferencaGastosAno) && $diferencaGastosAno > 0) {
        for ($ano = 1; $ano <= 25; $ano++) {
            $retornoAcumulado += $liquidoVerdeAnual;
            // O dado é o saldo acumulado menos o investimento inicial
            $dados[$ano] = $retornoAcumulado - $precoFinal;
        }
    }

    if (!empty($dados)) {
        // --- 1. Configurações de Dimensões e Posição (Ajustadas para ficar maior como na foto) ---
        $xInicial = 18;         // Margem esquerda maior para caber o título do eixo Y
        $yInicialTop = 227;      // Posição do topo do gráfico na página
        $larguraGrafico = 170;  // Mais largo para acomodar 25 barras confortavelmente
        $alturaGrafico = 40;   // Bem mais alto para caber os rótulos verticais
        $larguraBarra = 5;
        $espacoEntreBarras = 1.8; // Ajuste fino para caber as 25 barras na largura

        // --- 2. Cálculos de Escala e Linha Zero ---
        $min = min($dados);
        $max = max($dados);
        // Garante que o 0 esteja no range, caso todos os dados sejam positivos ou todos negativos
        $minScale = min(0, $min);
        $maxScale = max(0, $max);
        $rangeTotal = $maxScale - $minScale;

        // Evita divisão por zero se o range for 0
        $escalaY = ($rangeTotal > 0) ? $alturaGrafico / $rangeTotal : 0;

        // Calcula onde fica a linha visual do ZERO (R$ 0,00) no eixo Y.
        // A distância do topo ($maxScale) até o zero é proporcional ao valor de $maxScale.
        $yLinhaZero = $yInicialTop + ($maxScale * $escalaY);


        // --- 3. Desenhando Títulos e Eixos ---

        // Título Principal
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(50, 50, 70); // Cor cinza escuro para o título

        // Subtítulo "Lucro Total"
        $pdf->SetFont('helvetica', 'B', 14);
        // Centraliza aproximadamente sobre o gráfico
        $pdf->Text($xInicial + ($larguraGrafico / 2) - 15, $yInicialTop - 10, 'Lucro Total');

        // Rótulo do Eixo Y (Rotacionado) - REQUER TCPDF ou extensão FPDF
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(100, 100, 100);
        if (method_exists($pdf, 'StartTransform')) {
            $pdf->StartTransform();
            // Rotaciona 90 graus. Posiciona no meio da altura do gráfico, à esquerda.
            $pdf->Rotate(90, $xInicial - 15, $yInicialTop + ($alturaGrafico / 2));

            $pdf->StopTransform();
        } else {
            // Fallback se não suportar rotação: texto normal
            $pdf->Text($xInicial - 25, $yInicialTop, 'Retorno');
            $pdf->Text($xInicial - 25, $yInicialTop + 5, 'Financeiro');
        }


        // Desenha a LINHA BASE (Linha do Zero) - Cinza, um pouco mais grossa
        $pdf->SetLineWidth(0.4);
        $pdf->SetDrawColor(180, 180, 180); // Cinza claro
        $pdf->Line($xInicial, $yLinhaZero, $xInicial + $larguraGrafico, $yLinhaZero);

        // --- 4. Loop para desenhar Barras e Rótulos ---
        $xPos = $xInicial + ($espacoEntreBarras * 2); // Pequeno recuo inicial
        $pdf->SetLineWidth(0.2); // Volta para linha fina para as barras

        foreach ($dados as $ano => $valor) {
            // Altura da barra é sempre positiva para o cálculo do retângulo
            $barHeight = abs($valor * $escalaY);

            // Determina a posição Y inicial e a cor da barra
            if ($valor >= 0) {
                // Valor Positivo: Barra sobe a partir da linha zero
                $yBarra = $yLinhaZero - $barHeight;
                // Ponto de ancoragem para o texto (logo acima da barra)
                $yTextoAnchor = $yBarra - 1;
            } else {
                // Valor Negativo: Barra desce a partir da linha zero
                $yBarra = $yLinhaZero;
                // Ponto de ancoragem para o texto (logo abaixo da barra, que na rotação fica "acima" visualmente)
                $yTextoAnchor = $yBarra + $barHeight + 1;
            }

            // Configura cor da barra (Verde Neon brilhante com borda preta)
            $pdf->SetFillColor(0, 255, 127); // SpringGreen (mais parecido com a imagem)
            $pdf->SetDrawColor(0, 0, 0);     // Borda preta
            $pdf->Rect($xPos, $yBarra, $larguraBarra, $barHeight, 'DF');

            // Rótulo do Ano (Eixo X) na parte inferior
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(0, 0, 0);
            // Posiciona o ano abaixo da linha mais baixa do gráfico
            $yAno = $yInicialTop + $alturaGrafico + 3;
            // Centraliza o número do ano na largura da barra
            $pdf->Text($xPos + ($larguraBarra / 2) - 1, $yAno, (string) $ano);


            // Rótulo do Valor (Rotacionado Verticalmente)
            // Mostra para TODOS os anos, com 2 casas decimais
            $pdf->SetFont('helvetica', '', 7); // Fonte menor para caber
            $valorTexto = 'R$ ' . number_format($valor, 2, ',', '.');

            // Calcula o centro X da barra para alinhar o texto
            $xTextoAnchor = $xPos + ($larguraBarra / 2);
            // Pequeno ajuste vertical para centralizar a fonte na rotação
            $ajusteFonteRotacao = 1;

            if (method_exists($pdf, 'StartTransform')) {
                // --- INÍCIO ROTAÇÃO TCPDF ---
                $pdf->StartTransform();
                // Rotaciona 90 graus em torno do ponto de ancoragem.
                // O texto é desenhado "deitado", e a rotação o coloca em pé.
                $pdf->Rotate(90, $xTextoAnchor, $yTextoAnchor);
                $pdf->Text($xTextoAnchor - $ajusteFonteRotacao, $yTextoAnchor, $valorTexto);
                $pdf->StopTransform();
                // --- FIM ROTAÇÃO TCPDF ---
            } else {
                // Fallback ruim se não houver rotação: mostra horizontal (vai ficar sobreposto)
                $pdf->Text($xPos - 5, $yBarra - 2, $valorTexto);
            }


            // Avança a posição X para a próxima barra
            $xPos += $larguraBarra + $espacoEntreBarras;
        }
    } else {
        // Caso não haja dados válidos
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Text(20, 230, 'Não foi possível gerar o Gráfico de Payback (Faltam dados de investimento ou economia anual).');
    }

    $peso_formatado = number_format($peso, 2, ',', '.');

    // Quarta Página
    $pdf->AddPage();  // Adiciona a quarta página
    $pdf->Image('PGCOCDESC.png', 0, 0, 210, 297);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(0, 100, 0);
    $pdf->Text(148, 43.5, "$qtdmodulosArredondado X " . round($potenciaModulo) . " W");
    $pdf->Text(149, 57, "$potenciaGerador_formatado kWp");
    $pdf->Text(152, 71.5, "$metrosOcupados_formatado m²");
    $pdf->Text(152, 85, "$peso_formatado kg");
    $pdf->Text(142, 98.5, "$geracao_formatado kWh mensal");
    $pdf->Text(142, 112, "$geracao_formatado kWh mensal");
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(158, 141.5, "$percentualSolarArredondado %");
    $pdf->Text(23, 180, "$qtdmodulosArredondado MÓDULO SOLAR SUNOVA/OSDA/RONMA " . round($potenciaModulo) . " Wp ");
    $pdf->Text(23, 188, "1 INVERSOR 220V CHINT/SAJ/SOLIS/SOLPLANET " . round($potenciaInversor) . " KW");
    $pdf->Text(23, 196, "ESTRUTURA COLONIAL/FIBROMETAL/FIBROMADEIRA/METÁLICO");
    $pdf->Text(23, 204, "CABEAMENTO CC 1.8 KVCC - USO ESPECÍFICO PARA USINA SOLAR");
    $pdf->Text(23, 212, "INSTALAÇÃO / MÃO DE OBRA / EMISSÃO DE ART");
    $pdf->Text(23, 220, "RAMAL DE LIGAÇÃO LIMITADO A 10 METROS (INVERSOR PADRÃO)");
    $pdf->Text(23, 228, "1 (UM) ANO DE SEGURO CONTRA DANOS ELÉTRICOS E CLIMÁTICOS");
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(16, 256, "$textoPadrao");


    // Definir fonte e adicionar conteúdo à quinta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Quinta Página 
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCOC5.png', 0, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à quinta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Sexta Página 
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCOC7.png', 0, 0, 210, 297);

    // Setima Página 
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCOC8.png', 0, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à setima página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador



}
?>