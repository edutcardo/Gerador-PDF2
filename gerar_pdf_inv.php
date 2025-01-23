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
    $multiplicador = $_POST['multiplicador'];
    $quantidadePlacas = $_POST['quantidadePlacas'];
    $estrutura = $_POST['estrutura'];
    $opcao_adicional = $_POST['opcao_adicional'];

        // Função para verificar e ajustar valores
    function verificarValor($valor) {
        return $valor == 0 ? 1 : $valor;
    }
    function verificarValor2($valor) {
        return $valor == 0 ? 575 : $valor;
    }
    // Ajustes necessários para potenciaGerador e potenciaModulo
    $potenciaGerador = verificarValor($potenciaGerador);
    $potenciaModulo = verificarValor2($potenciaModulo);

    if (empty($multiplicador)) {
        $multiplicador = 1; // Valor padrão atribuído
    }
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
        $custoEstrutrura = 175 * $quantidadePlacas;
        $maoObraSolo = 125 * $quantidadePlacas;
        $precoKit = ($precoKit + $precoPlaca + $custoEstrutrura) * $multiplicador;
    }

    //Tributação
    function calcularTributario($potenciaInversor) {
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

    function calcularDemanda($potenciaInversor, $precoDemanda, $qtdDemanda, $iluminacao, $media) {
        // Valores fixos definidos
        $AA12 = 1; // Equivalente a 'PLANILHA RESUMO'!AA12
        $TUSDG = 8.6760; // Equivalente a 'PLANILHA RESUMO'!R26
        $Q77 = 0; // Valor de Q77 não foi especificado, ajuste conforme necessário
        $S77 = 0; // Valor de S77 não foi especificado, ajuste conforme necessário
    
        // Cálculo principal
        if ($AA12 * $potenciaInversor <= 75) {
            $resultado = ($media * 0.83) + $iluminacao;
        } else {
            $resultado = ($potenciaInversor * $AA12 * $TUSDG) + $S77;
        }
    
        // Subtrações
        $demanda = $resultado - ($precoDemanda * $qtdDemanda);
    
        return $demanda;
    }
    
    $demanda = calcularDemanda($potenciaInversor, $precoDemanda, $qtdDemanda, $iluminacao, $media);

    // Cálculos iniciais da proposta

    $geracao = ($potenciaGerador * 3.9 * 30);
    $qtdmodulos = ($potenciaGerador*1000)/$potenciaModulo;
    $qtdmodulosArredondado = (round($qtdmodulos));
    $metrosOcupados = ($qtdmodulosArredondado * 2.9)* $multiplicador;

    if (isset($_POST['opcao_adicional'])) {
        $opcaoAdicional = $_POST['opcao_adicional'];
        // Determina o valor baseado na opção selecionada
        if ($opcaoAdicional === "Adicionar alambrado") {
            $opcao_adicional = (sqrt($metrosocupados) + 8)*4*140;
        } elseif ($opcaoAdicional === "Adicionar britas") {
            $opcao_adicional = $metrosOcupados * 2.75;
        } elseif ($opcaoAdicional === "Adicionar alambrado + britas") {
            $opcao_adicional = ((sqrt($metrosocupados) + 8) * 4 * 140) + (2.75 * $metrosOcupados);
        }
    }

    // Cálculos PGCV4
    $peso = ($qtdmodulosArredondado * 33)* $multiplicador;
    $percentualSolar = ($geracao / $media) * 100;
    $percentualSolarArredondado = round($percentualSolar);
    $mediaArredondado = round($media);
    $geracaoArredondado = round($geracao);
    $geracaoAnual = $geracaoArredondado * 12;

    function calcularManutencao($qtdmodulosArredondado) {
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


    function calcularMargemEComissao($potenciaGerador) {
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

    function calcularFixo($potenciaGerador) {

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

    function calcularParcela($taxa, $nper, $vp, $vf = 0, $tipo = 0) {
        if ($taxa == 0) {
            return -($vp + $vf) / $nper;
        } else {
            $valorParcela = ($vp * pow(1 + $taxa, $nper) + $vf) * $taxa / ((1 + $taxa * $tipo) * (pow(1 + $taxa, $nper) - 1));
            return -$valorParcela;
        }
    }
    
    // Exemplo de uso
    $taxa = 0.015; // Taxa de juros mensal (1.5% convertido para decimal)
    $nper = -36;    // Número de períodos
    $nper2 = -48;    // Número de períodos
    $nper3 = -60;    // Número de períodos
    $vp = 36000;   // Valor presente do empréstimo
    $vf = 0;       // Valor futuro (geralmente 0, se não especificado)
    $tipo = 0;     // Tipo (0 = fim do período, 1 = início do período)
    
    $valorParcela = calcularParcela($taxa, $nper, $vp, $vf, $tipo);
    $valorParcela2 = calcularParcela($taxa, $nper2, $vp, $vf, $tipo);
    $valorParcela3 = calcularParcela($taxa, $nper3, $vp, $vf, $tipo);
    $valorParcelaRs = 'R$ ' . number_format($valorParcela, 2, ',', '.');
    $valorParcela2Rs = 'R$ ' . number_format($valorParcela2, 2, ',', '.');
    $valorParcela3Rs = 'R$ ' . number_format($valorParcela3, 2, ',', '.');

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
            $descPadrao = "2x50A";
            break;
        case "3x50A":
            $padrao = 2941.22;
            $descPadrao = "3x50A";
            break;
        case "3x63A":
            $padrao = 2815.24;
            $descPadrao = "3x63A";
            break;
        case "3x80A":
            $padrao = 3190.17;
            $descPadrao = "3x80A";
            break;
        case "3x100A":
            $padrao = 4870.36;
            $descPadrao = "3x100A";
            break;
        case "3x125A":
            $padrao = 8539.65;
            $descPadrao = "3x125A";
            break;
        case "3x150A":
            $padrao = 10366.42;
            $descPadrao = "3x150A";
            break;
        case "3x175A":
            $padrao = 11279.80;
            $descPadrao = "3x175A";
            break;
        case "3x200A":
            $padrao = 12969.57;
            $descPadrao = "3x200A";
            break;
        case "TORRE 112,5 KVA":
            $padrao = 51581.25;
            $descPadrao = "TORRE 112,5 KVA";
            break;    
        case "TORRE 150 KVA":
            $padrao = 74497.50;
            $descPadrao = "TORRE 150 KVA";
            break; 
        case "TORRE 225 KVA":
            $padrao = 94445.00;
            $descPadrao = "TORRE 225 KVA";
            break; 
        case "TORRE 300 KVA":
            $padrao = 110407.50;
            $descPadrao = "TORRE 300 KVA";
            break; 
        case "":
        case "selecione um padrao":
            $padrao = 0;
            break;
        default:
            $padrao = 0; // Caso não corresponda a nenhuma opção válida
            break;
    }

    $padraoRs = 'R$ ' . number_format($padrao, 2, ',', '.');

    if ($padrao <> 0) {
        $textoPadrao = "ENTRADA DE ENERGIA ($descPadrao) INCLUSO NO ORÇAMENTO: $padraoRs";
    }



    $precoFinal =(((($precoKit + $opcao_adicional) * $margem) + ($mobra * $qtdmodulosArredondado) + $valorFixo + $valoramais + $padrao) * $desconto) + $maoObraSolo ;
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
    $rentabilidadeVerde = ($retornoVerde / $precoFinal) * 100;
    $rentabilidadeAmarela = ($retornoAmarelo / $precoFinal) * 100;
    $rentabilidadeVermelha = ($retornoVermelho / $precoFinal)* 100;
    $rentabilidadeVermelhaP1 = ($retornoVermelhoP1 / $precoFinal) * 100;
    $liquidoVerde = $retornoVerde - $seguro - $manutencao - $imposto - $demanda;
    $liquidoAmarelo = $retornoAmarelo - $seguro - $manutencao - $imposto - $demanda;
    $liquidoVermelho = $retornoVermelho - $seguro - $manutencao - $imposto - $demanda;
    $liquidoVermelhoP1 = $retornoVermelhoP1 - $seguro - $manutencao - $imposto - $demanda;

    
    function calcularImposto($tributario, $retornoVerde) {
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
    $imposto = calcularImposto($tributario, $retornoVerde);
    $seguro = ($precoFinal * 0.007) /12;

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

    function calcularVPL($precoFinal, $fluxoCaixaAnual, $taxaDesconto, $periodoAnos) {
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

    // Agora, para a TIR, podemos considerar a porcentagem do payback como um valor aproximado da TIR
    // Isso é uma aproximação simples, pois o tempo de payback e TIR não são diretamente proporcionais, mas podemos usar essa lógica para um valor aproximado.
    $TIR = $percentualPayback; // Converte a porcentagem em valor decimal para TIR
    $TIRP  = number_format($TIR, 2, ',', '.') . '%';

    // Exemplo de receita anual usando a bandeira verde
    $receitasAnuais = $retornoVerde * 12; // Receita mensal vezes 12 meses

    // Exemplo de custo anual
    $custosAnuais = ($manutencao + $seguro + $imposto + $demanda) * 12;

    // Calcular lucratividade
    $lucratividade = (($receitasAnuais - $custosAnuais) / $receitasAnuais) * 100;

    // Formatando a lucratividade como porcentagem
    $lucratividadeFormatada = number_format($lucratividade, 2, ',', '.') . '%';

    // Função para calcular o ROI
    function calcularROI($retornoVerde, $precoFinal) {
        return ((($retornoVerde*12*25) - $precoFinal) / $precoFinal) * 100;
    }

    // Calcula o ROI e armazena na variável $ROI
    $ROI = calcularROI($retornoVerde, $precoFinal);

    // Formatação do ROI para apresentação
    $ROIPorcentagem = number_format($ROI, 2, ',', '.') . '%';

    // Data atual
    $formatoData = 'd/m/Y';
    $dataAtual = date($formatoData);


    // Criação do PDF
    $pdf = new TCPDF();
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Primeira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV1.png', 0, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(34.2, 98, "Nome: $nome");
    $pdf->Text(34.2, 104, "Endereço: $endereco");
    $pdf->Text(34.2, 110, "Cidade: $cidade");
    $pdf->Text(34.2, 138, "UC $uc");
    
    $pdf->Text(34.6, 160, "Disponibilidade de área necessária: $metrosOcupados m²");
    $pdf->Text(34.6, 166.25, "Quantidade de Módulos Fotovoltáicos: $qtdmodulosArredondado Placas");
    $pdf->Text(34.6, 172.5, "Potência do Projeto: $potenciaGerador kWp");
    $pdf->Text(34.6, 178.75, "Média de Consumo: $media kWh");
    $pdf->Text(34.6, 185, "Geração Estimada: $geracao kWh");

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text(46, 223, "$dataAtual");


    // Segunda Página (com a imagem genérica e gráfico)
    $pdf->AddPage();  // Adiciona a segunda página
    $pdf->Image('PGCV2.png', 0, 0, 210, 297);
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Dados para o gráfico
    $data = [$jan3, $fev3, $mar3, $abr3, $mai3, $jun3, $jul3, $ago3, $set3, $out3, $nov3, $dez3]; // Valores para as barras
    $labels = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"]; // Rótulos (meses)

    // Definindo as cores para as barras
    $barColor = [1, 133, 56];  // Cor Verde Canal

    // Posições e tamanho do gráfico
    $x = 23;  // Posição X para o gráfico
    $y = 260; // Posição Y para o gráfico (mais para baixo na página)
    $barWidth = 5; // Largura das barras
    $gap = 15;  // Distância entre as barras
    $maxBarHeight = 40; // Altura máxima do gráfico (limite)

    // Determinando o maior valor para escalar as barras
    $maxValue = max($data);

    // Desenhar a moldura ao redor do gráfico
    $molduraX = $x - 5; // Ajuste para começar um pouco antes das barras
    $molduraY = $y - $maxBarHeight - 7; // Ajuste para incluir espaço acima das barras
    $molduraWidth = count($data) * $gap; // Largura total baseada no número de barras e espaçamento
    $molduraHeight = $maxBarHeight + 10; // Altura total (incluindo margem superior e inferior)

    $pdf->SetDrawColor(0, 0, 0); // Cor da moldura (preto)
    $pdf->SetLineWidth(0.006); // Espessura da linha da moldura
    $pdf->Rect($molduraX, $molduraY, $molduraWidth, $molduraHeight, 'D'); // 'D' para apenas desenhar a linha


    // Desenhando as barras e adicionando os valores
    foreach ($data as $index => $value) {
    // Calculando a altura proporcional da barra
    $barHeight = ($value / $maxValue) * $maxBarHeight;

    // Desenhando cada barra
    $pdf->SetFillColor(60, 179, 113); // Verde
    $pdf->Rect($x + ($index * $gap), $y - $barHeight, $barWidth, $barHeight, 'DF'); // Barra

    // Adicionando o valor acima da barra
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $valueX = $x + ($index * $gap) + ($barWidth / 2) - 5; // Ajuste para centralizar o texto
    $valueY = $y - $barHeight - 5; // Ajuste para posicionar acima da barra
    $pdf->Text($valueX, $valueY, (string)$value); // Adiciona o valor como texto
}
    // Adicionando rótulos nas barras
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    foreach ($labels as $index => $label) {
        // Centralizar os rótulos horizontalmente e posicionar abaixo das barras
        $labelX = $x + ($index * $gap) + ($barWidth / 2) - (strlen($label) * 1.5); // Ajuste baseado no comprimento do texto
        $labelY = $y + 5; // Posição logo abaixo da barra
        $pdf->Text($labelX, $labelY, $label);
    }

    // Terceira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV3.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à terceira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Quarta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV4.png', 0, 0, 210, 297);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(255, 0, 0);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(158, 141.5, "$percentualSolarArredondado %");

    // Definir fonte e adicionar conteúdo à quarta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Quinta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV5.png', 0, 0, 210, 297);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);

    $componentes = html_entity_decode($componentes);
    $componentes = str_replace(
        ["<\/th>", "<\/td>", "<\/tr>", "<\/table>"], 
        ["</th>", "</td>", "</tr>", "</table>"], 
        $componentes
    );
    
    // Extrair os dados da tabela
    preg_match_all('/<td>\s*(.*?)\s*<\/td>\s*<td>\s*(.*?)\s*<\/td>\s*<td>\s*(.*?)\s*<\/td>/', $componentes, $matches, PREG_SET_ORDER);
    
    // Ajuste na altura da descrição
    $y = 66; // Posição inicial Y
    $linhaAltura = 8; // Altura de cada linha no PDF
    $larguraDescricao = 180; // Ajuste para a largura da descrição
    $larguraQuantidade = 20; // Ajuste para a largura da quantidade
    $maxY = 280; // Limite Y da página
    
    // Função para adicionar uma nova página se necessário
    function verificaQuebraPagina($pdf, $y, $linhaAltura, $maxY) {
        if ($y + $linhaAltura > $maxY) {
            $pdf->AddPage(); // Adiciona uma nova página
            return 10; // Reseta a posição Y após a nova página
        }
        return $y;
    }
    
    // Escrever os dados extraídos no PDF
    if (empty($matches)) {
        $pdf->Text(16, $y + 3, "$qtdmodulosArredondado MODULOS FOTOVOLTÁICO BIFACIAL HJT 700W");
        $pdf->Text(16, $y + 10, "$multiplicador INVERSOR SOLAR $fabricante DE $potenciaInversor KW");
        $pdf->Text(16, $y + 17, "ESTRUTURA DE SOLO MONOPOSTE PARA TERRENO HORIZONTAL");
    } else {
        foreach ($matches as $match) {
            $sku = trim($match[1]);
            $quantidade = (trim($match[2])) * $multiplicador;
            $descricao = trim($match[3]);
    
            // Verificar se há espaço suficiente para escrever na página
            $y = verificaQuebraPagina($pdf, $y, $linhaAltura, $maxY);
    
            // Adicionar quantidade, com ajuste para subir um pouco
            $pdf->SetXY(16, $y - 0.9); // Ajuste para subir um pouco a posição Y
            $pdf->Cell($larguraQuantidade, $linhaAltura, $quantidade, 0, 0, 'L'); // Alinhamento à esquerda
    
            // Adicionar a descrição com quebra automática de linha
            $pdf->SetXY(27, $y); // Ajuste a posição X para alinhar a descrição
            $pdf->MultiCell($larguraDescricao, $linhaAltura, $descricao, 0, 'L', 0);
    
            // Atualizar Y para a próxima linha somente após o MultiCell
            $y += max($linhaAltura, $pdf->GetY() - $y);
        }
    }

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(16, 155, "$textoPadrao");

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(152, 164, "$precoFinalRs");

    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->Text(26, 180, "36 X $valorParcelaRs");
    $pdf->Text(85, 180, "48 X $valorParcela2Rs");
    $pdf->Text(146, 180, "60 X $valorParcela3Rs");

    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Text(59, 46, "$qtdmodulosArredondado");
    $pdf->Text(85, 46, "$potenciaInversor kW");
    $pdf->Text(110, 46, "$potenciaGerador kWp");
    $pdf->Text(139, 46, "$geracaoArredondado kWh");
    $pdf->Text(167, 46, "$geracaoAnual kWh");

    // Dados do Payback
    $anos = 25; // Total de anos

    // Calcular o retorno verde anual
    $retornoAnualVerde = $retornoVerde * 12;

    // Inicializar o acumulado de retorno
    $dados = [];
    $retornoAcumulado = 0;

    // Calcular o retorno acumulado ao longo dos anos
    for ($ano = 1; $ano <= $anos; $ano++) {
        $retornoAcumulado += $retornoAnualVerde;
        $dados[$ano] = $retornoAcumulado - $precoFinal; // Payback acumulado ao final de cada ano
    }

    // Configurações do gráfico
    $xInicial = 17; // Posição X do gráfico
    $yInicial = 228; // Posição Y do gráfico
    $larguraGrafico = 160; // Largura total do gráfico
    $alturaGrafico = 30; // Altura total do gráfico
    $larguraBarra = 5; // Largura de cada barra
    $espacoEntreBarras = 2; // Espaço entre as barras
    $linhaBase = $yInicial + $alturaGrafico; // Posição da linha base (eixo X)

    // Determinar o maior e menor valor
    $min = min($dados);
    $max = max($dados);
    $escalaY = $alturaGrafico / ($max - $min); // Escala de altura por unidade

    // Desenhar eixo X e Y
    $pdf->SetDrawColor(0, 0, 0); // Preto
    $pdf->Line($xInicial, $linhaBase, $xInicial + $larguraGrafico, $linhaBase); // Eixo X
    $pdf->Line($xInicial, $linhaBase - $alturaGrafico, $xInicial, $linhaBase); // Eixo Y

    // Desenhar as barras do gráfico
    $xPos = $xInicial; // Posição inicial no eixo X
    foreach ($dados as $ano => $valor) {
        // Calcular altura da barra
        $barHeight = abs($valor * $escalaY);

        // Determinar a posição Y da barra
        if ($valor >= 0) {
            $yBarra = $linhaBase - $barHeight; // Barra positiva
        } else {
            $yBarra = $linhaBase; // Barra negativa
        }

        // Desenhar barra
        $pdf->SetFillColor(60, 179, 113); // Verde
        $pdf->Rect($xPos, $yBarra, $larguraBarra, $barHeight, 'DF'); // 'DF' para desenhar e preencher

        // Adicionar o ano abaixo da barra
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Text($xPos - 1, $linhaBase + 3, (string)$ano);

        // Adicionar o valor na barra
        $valorTexto = number_format($valor, 0, ',', '.');
        $yTexto = $valor >= 0 ? $yBarra - 5 : $yBarra + $barHeight + 3;
        $pdf->Text($xPos - 2, $yTexto, $valorTexto);

        // Avançar posição X
        $xPos += $larguraBarra + $espacoEntreBarras;
    }

    // Título do gráfico
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text($xInicial, $yInicial - 10, '');

    // Definir fonte e adicionar conteúdo à quinta página
    $pdf->SetFont('helvetica','B', 12);
    $pdf->SetTextColor(0, 0, 0);

    // Sexta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
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
    $mediaLiquido = ($liquidoVerde + $liquidoAmarelo + $liquidoVermelho + $liquidoVermelhoP1) / 4;
    $mediaLiquidoRs =  'R$ ' . number_format($mediaLiquido, 2, ',', '.');
    
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

    $pdf->Text(27, 220, "$VPLP");
    $pdf->Text(80, 220, "$TIRP");
    $pdf->Text(127, 220, "$lucratividadeFormatada");
    $pdf->Text(172, 220, "$ROIPorcentagem");
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

    // Sétima Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV7.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à sétima página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Nona Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV9.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à nona página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Decima página Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV10.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à decima página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Decima primeira página Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV11.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à Decima primeira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Decima segunda página Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV12.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à Decima segunda página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador
    

    
}
?>