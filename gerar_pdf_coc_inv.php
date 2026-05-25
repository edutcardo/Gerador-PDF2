<?php
require_once('vendor/autoload.php'); // Ou o caminho correto, se você não estiver usando o Composer
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. CAPTURA E SANITIZAÇÃO BÁSICA ---
    $nome = isset($_POST['nome']) ? $_POST['nome'] : 'Informar';
    $endereco = isset($_POST['endereco']) ? $_POST['endereco'] : 'Informar';
    $cidade = isset($_POST['cidade']) ? $_POST['cidade'] : 'Informar';
    $uf = isset($_POST['uf']) ? $_POST['uf'] : 'Informar';
    $uc = isset($_POST['uc']) ? $_POST['uc'] : '0000';
    $media = isset($_POST['media']) && $_POST['media'] !== '' ? floatval($_POST['media']) : 1;
    $valoramais = isset($_POST['valoramais']) && $_POST['valoramais'] !== '' ? floatval($_POST['valoramais']) : 0;

    $iluminacao = isset($_POST['iluminacao']) ? floatval($_POST['iluminacao']) : 0;
    $potenciaGerador = isset($_POST['potenciaGerador']) ? floatval($_POST['potenciaGerador']) : 0;
    $componentes = isset($_POST['componentes']) ? $_POST['componentes'] : '';
    $potenciaModulo = isset($_POST['potenciaModulo']) ? floatval($_POST['potenciaModulo']) : 0;
    $numeroDeFases = isset($_POST['numeroDeFases']) ? $_POST['numeroDeFases'] : '';
    $precoKit = isset($_POST['precoKit']) ? floatval($_POST['precoKit']) : 0;
    $irradiacao = isset($_POST['irradiacao']) ? $_POST['irradiacao'] : '';
    $marca = isset($_POST['marca']) ? $_POST['marca'] : '';
    $fabricante = isset($_POST['fabricante']) ? $_POST['fabricante'] : '';
    $potenciaInversor = isset($_POST['potenciaInversor']) ? floatval($_POST['potenciaInversor']) : 0;
    $padrao = isset($_POST['padrao']) ? $_POST['padrao'] : '';
    $desconto = isset($_POST['desconto']) ? $_POST['desconto'] : '';
    $inputConcessionaria = isset($_POST['inputConcessionaria']) ? $_POST['inputConcessionaria'] : '';
    $inputValorCompensavel = isset($_POST['inputValorCompensavel']) ? floatval($_POST['inputValorCompensavel']) : 0;
    $multiplicador = isset($_POST['multiplicador']) ? intval($_POST['multiplicador']) : 1;
    $quantidadePlacas = isset($_POST['quantidadePlacas']) ? intval($_POST['quantidadePlacas']) : 0;
    $estrutura = isset($_POST['estrutura']) ? $_POST['estrutura'] : '';
    $usina = isset($_POST['usina']) ? $_POST['usina'] : '';
    $adicionalAPlus = isset($_POST['adicionalAPlus']) ? trim($_POST['adicionalAPlus']) : '';
    $adicionalIndicacao = isset($_POST['adicionalIndicacao']) ? trim($_POST['adicionalIndicacao']) : '';
    $geracao = isset($_POST['geracaoKwhMes']) ? floatval($_POST['geracaoKwhMes']) : 0;
    $vendedor = isset($_POST['vendedor']) ? $_POST['vendedor'] : '';

    // --- 2. CAPTURA DOS NOVOS VALORES E FLAGS (Lógica Atualizada) ---

    // Captura se o item foi selecionado (verifica se não está vazio)
    $temPadrao = !empty($_POST['adicionalPadrao']);
    $temSalaTecnica = !empty($_POST['adicionalSalaTecnica']);
    $temBrita = !empty($_POST['adicionalBrita']);
    $temGrade = !empty($_POST['adicionalGrade']);
    $temAlambrado = !empty($_POST['adicionalAlambrado']);
    $temCocamar = !empty($_POST['adicionalCocamar']);

    // Captura os valores monetários (convertendo string para float)
    $valPadrao = $_POST['valorPadrao'];
    $valSalaTecnica = $_POST['valorSalaTecnica'];
    $valBrita = $_POST['valorBrita'];
    $valGrade = $_POST['valorGrade'];
    $valAlambrado = $_POST['valorAlambrado'];

    // Função auxiliar para formatar moeda
    function fmtMoeda($valor)
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    // --- 3. LÓGICA PARA INFRAESTRUTURA (Padrão e Sala Técnica) ---
    // Formato: PADRÃO DE ENERGIA (R$) INCLUSO E SALA TÉCNICA (R$) PARA INVERSOR.

    $parts_infra = [];

    if ($temPadrao) {
        $parts_infra[] = 'PADRÃO DE ENERGIA (' . fmtMoeda($valPadrao) . ') INCLUSO';
    }
    if ($temSalaTecnica) {
        $parts_infra[] = 'SALA TÉCNICA (' . fmtMoeda($valSalaTecnica) . ') PARA INVERSOR';
    }

    $texto_padrao_e_sala = '';
    if (!empty($parts_infra)) {
        // Junta com " E " se houver os dois, ou mostra só um
        $texto_padrao_e_sala = implode(' E ', $parts_infra) . '.';
    }

    // Adiciona Cocamar se necessário
    $texto_cocamar = $temCocamar ? ' Liberado Cocamar.' : '';

    // Texto Final de Adicionais (Linha 1 de observações)
    $texto_final_adicionais = trim($texto_padrao_e_sala . $texto_cocamar);
    $texto_final_adicionais = wordwrap($texto_final_adicionais, 50, "\n");


    // --- 4. LÓGICA PARA SEGURANÇA/SOLO (Brita, Grade, Alambrado) ---
    // Formato: INCLUSO BRITA (R$), GRADE (R$) E ALAMBRADO (R$)

    $parts_seguranca = [];

    if ($temBrita) {
        $parts_seguranca[] = 'BRITA (' . fmtMoeda($valBrita) . ')';
    }
    if ($temGrade) {
        $parts_seguranca[] = 'GRADE (' . fmtMoeda($valGrade) . ')';
    }
    if ($temAlambrado) {
        $parts_seguranca[] = 'ALAMBRADO (' . fmtMoeda($valAlambrado) . ')';
    }

    $texto_seguranca = '';
    $count_seg = count($parts_seguranca);

    if ($count_seg > 0) {
        if ($count_seg == 1) {
            $texto_seguranca = 'INCLUSO ' . $parts_seguranca[0];
        } else {
            // Pega o último item
            $ultimo_item = array_pop($parts_seguranca);
            // Junta os anteriores com vírgula
            $primeiros = implode(', ', $parts_seguranca);
            // Monta a frase final
            $texto_seguranca = 'INCLUSO ' . $primeiros . ' E ' . $ultimo_item;
        }
    }
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
            $manutencao = (320 / pow($qtdmodulosArredondado, 0.485) + 10 - 20 / $qtdmodulosArredondado) * $qtdmodulosArredondado;
        } else {
            $manutencao = (320 / pow(10, 0.485) + 10 - 20 / 10) * 10;
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


    $payback = $precoFinal / $liquidoVerdeAnual;
    $paybackArredondado = round($payback) - 1;
    $retorno25anos = $liquidoVerdeAnual * 25;
    $retorno25anosRs = 'R$ ' . number_format($retorno25anos, 2, ',', '.');


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

    $pdf->Text(34.6, 160, "Disponibilidade de área necessária:");
    $pdf->Text(34.6, 166.25, "Quantidade de Módulos Fotovoltáicos:");
    $pdf->Text(34.6, 172.5, "Potência do Projeto:");
    $pdf->Text(34.6, 178.75, "Geração Estimada:");


    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text(102, 160, "$metrosOcupados_formatado m²");
    $pdf->Text(108, 166.25, "$qtdmodulosArredondado X " . round($potenciaModulo) . " W");
    $pdf->Text(73, 172.5, "$potenciaGerador_formatado kWp");
    $pdf->Text(71, 178.75, "$geracao_formatado kWh");
    if (!empty($vendedor)) {
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Text(34.6, 274, "Vendedor: $vendedor");
        $pdf->SetTextColor(0, 0, 0);
    }



    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text(34.6, 220, "Data:");
    $pdf->Text(34.6, 226.25, "Responsável Técnico:");
    $pdf->Text(34.6, 232.5, "CREA-PR:");
    $pdf->Text(34.6, 238.75, "CPF:");

    $pdf->SetFont('helvetica', 12);
    $pdf->Text(46, 220.05, "$dataAtual");
    $pdf->Text(80, 226.30, "Eduardo Garcia Ribeiro");
    $pdf->Text(56, 232.55, "160034/D");
    $pdf->Text(46.6, 238.75, "085.271.859-46");

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text(34.6, 280, "$dataAtual");

    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Text(34.6, 286, "Este orçamento tem validade de 7 dias.");
    $pdf->SetTextColor(0, 0, 0);

    // Segunda Página (com a imagem genérica e gráfico)
    $pdf->AddPage();  // Adiciona a segunda página
    $pdf->Image('PGCOC2.png', 0, 0, 210, 297);
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página
// --- CONFIGURAÇÃO DOS DADOS ---
// Supondo que $jan3, $fev3... sejam suas variáveis de GERAÇÃO.
    $dataGeracao = [$jan3, $fev3, $mar3, $abr3, $mai3, $jun3, $jul3, $ago3, $set3, $out3, $nov3, $dez3];
    $labels = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"];

    // --- CORES ---
// Verde (para borda da Geração e linha média)
    $colGerR = 0;
    $colGerG = 128;
    $colGerB = 0;

    // --- POSICIONAMENTO E DIMENSÕES ---
    $x = 28;            // Posição X inicial
    $y = 256;           // Posição Y da linha de base (chão do gráfico)
    $barWidth = 6;      // Largura da barra (aumentei um pouco pois agora é barra única)
    $gap = 14;          // Distância entre o INÍCIO de uma barra e a próxima
    $maxBarHeight = 40; // Altura máxima visual do gráfico

    // --- ESCALA ---
// Define o valor máximo apenas baseado na Geração para escalar as barras
    $maxValueGlobal = empty($dataGeracao) ? 1 : max($dataGeracao);
    if ($maxValueGlobal == 0)
        $maxValueGlobal = 1;

    // Configuração de fonte
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetLineWidth(0.3);

    // =============================================================================
// 1. LOOP DAS BARRAS
// =============================================================================
    foreach ($labels as $index => $label) {
        // Valor atual (proteção contra índice inexistente)
        $valGeracao = isset($dataGeracao[$index]) ? $dataGeracao[$index] : 0;

        // Altura proporcional
        $hGeracao = ($valGeracao / $maxValueGlobal) * $maxBarHeight;

        // Posição X da barra atual
        $xPos = $x + ($index * $gap);

        // --- DESENHAR BARRA (Geração) ---
        // Cor da linha (Borda Verde)
        $pdf->SetDrawColor($colGerR, $colGerG, $colGerB);
        // 'D' = Draw border only (Apenas contorno, sem preenchimento, conforme imagem)
        // Se quiser fundo branco opaco, use $pdf->SetFillColor(255,255,255) e mude 'D' para 'DF'
        $pdf->Rect($xPos + 3, $y - $hGeracao, $barWidth, $hGeracao, 'D');

        // --- RÓTULOS (Meses) ---
        $pdf->SetTextColor(0, 0, 0);

        // Centralizar texto abaixo da barra
        $centerX = $xPos + ($barWidth / 2);
        $textWidth = $pdf->GetStringWidth($label);
        $labelX = $centerX - ($textWidth / 2);

        $pdf->Text($labelX, $y + 4, $label);
    }

    // --- LINHA DE BASE (Eixo X) ---
    $pdf->SetDrawColor(150, 150, 150);
    $pdf->SetLineWidth(0.1);
    // Calcula largura total baseado no número de labels
    $larguraTotal = (count($labels) * $gap);
    $pdf->Line($x - 2, $y, $x + $larguraTotal, $y);

    // =============================================================================
// 2. LINHA TRACEJADA DA MÉDIA
// =============================================================================

    // Calcular média
    $soma = array_sum($dataGeracao);
    $qtd = count($dataGeracao);
    $media = ($qtd > 0) ? $soma / $qtd : 0;

    // Altura da linha média
    $hMedia = ($media / $maxValueGlobal) * $maxBarHeight;
    $yPosMedia = $y - $hMedia;

    // Estilo Tracejado (Verde)
// Nota: 'dash' funciona bem no TCPDF. No FPDF padrão pode exigir script add-on.
    $pdf->SetLineStyle(array('dash' => '3,2', 'color' => array($colGerR, $colGerG, $colGerB)));
    $pdf->SetLineWidth(0.3);

    // Desenha a linha cruzando todo o gráfico
    $pdf->Line($x, $yPosMedia, $x + $larguraTotal, $yPosMedia);

    // Restaura linha sólida para o resto do PDF
    $pdf->SetLineStyle(array('dash' => 0));

    // =============================================================================
// 3. LEGENDA
// =============================================================================
    $legendX = $x + 10; // Ajuste conforme necessário
    $legendY = $y + 15;

    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(0, 0, 0);

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

    // Quarta Página
    $pdf->AddPage();  // Adiciona a quarta página
    $pdf->Image('PGCOC9.png', 0, 0, 210, 297);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Text(23, 30, "$qtdmodulosArredondado MÓDULO SOLAR SUNOVA/OSDA/RONMA " . round($potenciaModulo) . "/620 Wp ");
    $pdf->Text(23, 38, "1 INVERSOR 220V CHINT/SAJ/SOLIS/SOLPLANET " . round($potenciaInversor) . " KW");
    $qtdEstrutrura = number_format(($qtdmodulosArredondado / 4), 0, ',', '.');
    $qtdCabos = number_format(($qtdmodulosArredondado * 2), 0, ',', '.');
    $pdf->Text(23, 46, "$qtdEstrutrura ESTRUTURA COLONIAL/FIBROMETAL/FIBROMADEIRA/METÁLICO") . " m";
    $pdf->Text(23, 54, "$qtdCabos CABO SOLAR PV 1.8KVCC 4MM PRETO NBR 16612");
    $pdf->Text(23, 62, "$qtdCabos CABO SOLAR PV 1.8KVCC 4MM VERMELHO NBR 16612");
    $pdf->Text(23, 70, "INSTALAÇÃO / MÃO DE OBRA / EMISSÃO DE ART");
    $pdf->Text(23, 78, "RAMAL DE LIGAÇÃO LIMITADO A 10 METROS (INVERSOR PADRÃO)");
    $pdf->Text(16, 86, "$textoPadrao");
    $pdf->Text(23, 94, "$texto_seguranca");
    $pdf->Text(23, 102, "$texto_final_adicionais");
    $pdf->Text(16, 110, "$textoPadrao");


    $pdf->AddPage();
    $pdf->Image('PGCOC6.png', 0, 0, 210, 297);
    $pdf->SetFont('helvetica', 'B', 24);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(120, 60, "$precoFinalRs");
    $pdf->SetFont('helvetica', 'B', 24);


    $pdf->Text(35, 60, "$potenciaGerador_formatado kWp");

    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(FALSE);

    // =========================================================================
    // --- 2. GRÁFICO DE PAYBACK (Movido para cima nesta página) ---
    // =========================================================================

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(50, 50, 50);
    $pdf->Text(83, 192, "Gráfico de Payback");

    // --- Dados do Gráfico de Payback ---
    // (Lógica reaproveitada do seu código anterior, mas posicionada aqui)
    $dadosGrafico = [];
    $retornoAcumulado = 0;

    // Recalcula o fluxo para o gráfico (Investimento negativo no ano 0, depois recupera)
    // O gráfico da imagem mostra o saldo acumulado crescendo.
    if (isset($precoFinal) && isset($liquidoVerdeAnual)) {
        for ($ano = 1; $ano <= 25; $ano++) {
            $retornoAcumulado += $liquidoVerdeAnual;
            // O valor a ser plotado é o Saldo (Lucro - Investimento)
            $dadosGrafico[$ano] = $retornoAcumulado - $precoFinal;
        }
    }

    if (!empty($dadosGrafico)) {
        // Configurações visuais do gráfico
        $chartX = 33;       // Margem esquerda
        $chartY = 250;      // Posição Y da LINHA BASE (Eixo X) - Ajustado para caber na pg
        $chartHeight = 50;  // Altura visual máxima das barras
        $barW = 5;          // Largura da barra
        $gap = 1.5;         // Espaço entre barras

        // Escala: Encontrar o maior valor para definir a altura máxima
        $maxVal = max($dadosGrafico);
        $minVal = min($dadosGrafico); // Provavelmente negativo nos primeiros anos

        // Se o maior valor for 0 (erro), evita divisão por zero
        $scale = ($maxVal != 0) ? $chartHeight / ($maxVal - min($minVal, 0)) : 1;

        // Posição visual do ZERO (onde o saldo deixa de ser negativo)
        // Se minVal for -100 e max for 1000, o zero está um pouco acima do fundo
        // Para simplificar visualmente igual a imagem:
        // A imagem mostra barras crescendo do fundo. Vamos fixar o fundo.

        // Re-ajuste simples: Normalizar para desenhar apenas a barra positiva (ou negativa)
        // A imagem mostra barras cinzas simples crescendo.
        $maxAbs = max(abs($minVal), abs($maxVal));
        $scaleSimple = $chartHeight / $maxAbs;

        $currentX = $chartX;

        $pdf->SetFont('helvetica', '', 7); // Fonte pequena para os números
        $pdf->SetLineWidth(0.1);

        foreach ($dadosGrafico as $ano => $valor) {

            // Altura da barra
            $hBar = abs($valor) * $scaleSimple;

            // Define cor: Cinza claro se negativo/pagando, Cinza escuro/Verde se lucrando?
            // A imagem usa cinza claro.
            $pdf->SetFillColor(200, 200, 200);
            $pdf->SetDrawColor(100, 100, 100);

            // Desenha barra
            // Se valor < 0, a barra teoricamente seria para baixo, mas gráficos simples de payback
            // costumam mostrar a "evolução" visual. Vamos desenhar do eixo para cima.
            // Para ser fiel à lógica financeira:
            if ($valor < 0) {
                // Barra "negativa" (ainda pagando) - desenha do eixo para baixo ou cor diferente
                $pdf->SetFillColor(150, 150, 150); // Cinza mais escuro
                // Opção visual da imagem: parece que todas partem da mesma base.
                // Vamos desenhar da base $chartY para cima.
                $yBar = $chartY - $hBar;
            } else {
                // Lucro
                $pdf->SetFillColor(220, 220, 220); // Cinza claro
                $yBar = $chartY - $hBar;
            }

            // Desenhar Retângulo
            $pdf->Rect($currentX, $yBar, $barW, $hBar, 'DF');

            // Rótulo vertical (Valor) - Rotacionado
            $pdf->StartTransform();
            $pdf->Rotate(90, $currentX + ($barW / 2), $yBar - 2);
            $textoValor = "R$ " . number_format($valor, 2, ',', '.');
            $pdf->Text($currentX + ($barW / 2), $yBar - 2, $textoValor);
            $pdf->StopTransform();

            // Rótulo Horizontal (Ano)
            $pdf->Text($currentX + 1, $chartY + 2, $ano);

            $currentX += ($barW + $gap);
        }

        // Legenda do Eixo Y (opcional)
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->StartTransform();
        $pdf->Rotate(90, 15, 250);
        $pdf->Text(15, 250, "Retorno Financeiro");
        $pdf->StopTransform();
    }
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


    // Sexta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCOC4.png', 0, 0, 210, 297);
    $pdf->SetFont('helvetica', 'B', 12);

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