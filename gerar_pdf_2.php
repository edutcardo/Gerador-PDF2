<?php
require_once('vendor/autoload.php'); // Ou o caminho correto, se você não estiver usando o Composer
// Funções de desenho compartilhadas pelas duas modalidades de proposta.
require_once __DIR__ . '/pdf_helpers.php';

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
    $precoKitPrazo = isset($_POST['precoKitPrazo']) && $_POST['precoKitPrazo'] !== ''
        ? floatval($_POST['precoKitPrazo'])
        : 0;
    $irradiacao = $_POST['irradiacao'];
    // Converter para número
    $irradiacaoValor = floatval($irradiacao);
    if ($irradiacaoValor <= 0) {
        $irradiacaoValor = 4.5; // valor default
    }
    $marca = $_POST['marca'];
    $fabricante = $_POST['fabricante'];
    $potenciaInversor = $_POST['potenciaInversor'];
    $padrao = $_POST['padrao'];
    $vendedor = isset($_POST['vendedor']) ? $_POST['vendedor'] : '';
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

    $geracao = $potenciaGerador * $irradiacaoValor * 30;
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
    $tarifaFinal = isset($_POST['tarifaFinal']) && floatval($_POST['tarifaFinal']) > 0
        ? floatval($_POST['tarifaFinal']) : 0.81;
    $fioB = isset($_POST['fioB']) ? floatval($_POST['fioB']) : 0;
    $icmsRate = isset($_POST['icmsRate']) ? floatval($_POST['icmsRate']) : 0;

    $percentuaisFioB = [
        2023 => 0.15,
        2024 => 0.30,
        2025 => 0.45,
        2026 => 0.60,
        2027 => 0.75,
        2028 => 0.90,
        2029 => 1.00
    ];
    $anoAtual = (int) date('Y');
    $percentualFioB = isset($percentuaisFioB[$anoAtual]) ? $percentuaisFioB[$anoAtual] : 1.00;

    $ucsSeparadas = preg_split('/[,\/]/', $uc);
    $ucsValidas = array_filter(array_map('trim', $ucsSeparadas), function ($v) {
        return $v !== '';
    });
    $numUCs = max(1, count($ucsValidas));

    $energiaInjetada = min($media, $geracao);
    $custoDisponibilidade = $demandaMinima * $tarifaFinal;
    $custoFioBInjetado = $energiaInjetada * $fioB * $percentualFioB * (1 + $icmsRate);

    $gastoSemGerador = ($media * $tarifaFinal) * $numUCs;
    $gastoSemGeradorRs = 'R$ ' . number_format($gastoSemGerador, 2, ',', '.');
    $gastoSemGeradorAno = $gastoSemGerador * 12;
    $gastoSemGeradorAnoRs = 'R$ ' . number_format($gastoSemGeradorAno, 2, ',', '.');
    $gastoComGerador = ($custoDisponibilidade + $custoFioBInjetado) * $numUCs;
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

    $valorPadraoNumerico = isset($_POST['valorPadrao']) ?
        floatval($_POST['valorPadrao']) : 0;
    $descPadrao = $padrao;
    
    if ($valorPadraoNumerico > 0) {
        $padraoRs = 'R$ ' . number_format($valorPadraoNumerico, 2, ',', '.');
        $textoPadrao = "ENTRADA DE ENERGIA ($descPadrao) INCLUSO NO ORÇAMENTO: $padraoRs";
    }

    // ALTERAÇÃO PRINCIPAL: O preço final agora é exatamente o preço do kit.
    $precoFinal = $precoKit;
    $precoFinalRs = 'R$ ' . number_format($precoFinal, 2, ',', '.');

    $precoFinalPrazo = $precoKitPrazo;
    $precoFinalPrazoRs = 'R$ ' . number_format($precoFinalPrazo, 2, ',', '.');

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

    $irradiacaoMensal = [
        5888,
        5792,
        5219,
        4544,
        3636,
        3333,
        3529,
        4451,
        4683,
        5311,
        5969,
        6327
    ];
    $jan = $irradiacaoMensal[0];
    $fev = $irradiacaoMensal[1];
    $mar =
        $irradiacaoMensal[2];
    $abr = $irradiacaoMensal[3];
    $mai = $irradiacaoMensal[4];
    $jun
        = $irradiacaoMensal[5];
    $jul = $irradiacaoMensal[6];
    $ago = $irradiacaoMensal[7];
    $set = $irradiacaoMensal[8];
    $out = $irradiacaoMensal[9];
    $nov =
        $irradiacaoMensal[10];
    $dez = $irradiacaoMensal[11];
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
    $pdf->Image('PGAUT1.png', 0, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 16);
    $pdf->SetTextColor(85, 85, 85);
    $pdf->Text(38, 91, "$nome");
    $pdf->Text(44, 96.8, "$endereco");
    $pdf->Text(40, 101.9, "$cidade");
    $pdf->Text(23, 107.6, "UC(s): $uc");

    $pdf->Text(98, 156, "$metrosOcupados m²");
    $pdf->Text(102.3, 161.7, "$qtdmodulosArredondado Placas");
    $pdf->Text(64, 167.3, "$potenciaGerador kWp");
    $pdf->Text(64.5, 173.1, "$mediaArredondado kWh");
    $pdf->Text(64, 178.8, "$geracaoArredondado kWh");
    if (!empty($vendedor)) {
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Text(39, 231.6, "$vendedor");
        $pdf->SetTextColor(0, 0, 0);
    }
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(85, 85, 85);
    $pdf->Text(35, 196.35, "$dataAtual");

    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Text(24, 236, "Este orçamento tem validade de 7 dias.");
    $pdf->SetTextColor(0, 0, 0);

    // Segunda Página (com a imagem genérica e gráfico)
    $pdf->AddPage();  // Adiciona a segunda página
    $pdf->Image('PGINV2.png', 0, 0, 210, 297);
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página
  $pdf->AddPage();
    $pdf->Image('PGINV3.png', 0, 0, 210, 297);

    // Página PGAUT5 — estudo de viabilidade + composição da proposta
    $pdf->AddPage();
    $pdf->Image('PGAUT5.png', 0, 0, 210, 297);

    // -----------------------------------------------------------------------
    // Coordenadas da arte PGAUT5, em milímetros (A4 210x297).
    // A arte é a MESMA da PGINV14 (conferida pixel a pixel), por isso as
    // medidas são idênticas às do gerador de investimento. Trocou a arte?
    // Ajuste SÓ as constantes deste bloco.
    // -----------------------------------------------------------------------
    // Linhas da tabela do topo: Potência, Área Ocupada, Peso, Base do Consumo
    // e Geração Estimada — valem para as duas colunas.
    $viabLinhasY = array(43.2, 54.7, 66.2, 78.0, 89.5);
    $viabColunaLargura = 36; // largura útil da coluna (a caixa da arte tem 38mm)

    // Um "bloco" é a metade da página dedicada a uma tecnologia: a coluna dela
    // na tabela do topo + a lista de composição + as caixas de preço + payback.
    $blocosLayout = [
        'LONGI' => [
            'viabColunaX' => 137,   // centro da coluna "LONGI - BC"
            'xQtd' => 16,           // coluna de quantidade da lista
            'xDesc' => 27,          // coluna de descrição
            'larguraDesc' => 105,   // vai até x=132, logo antes das caixas de preço
            'yLista' => 140,        // 1a linha, abaixo de "LONGI - Tecnologia BC"
            'alturaLista' => 45,    // até o topo da faixa "Payback:"
            'xPreco' => 153,        // à direita dos rótulos da arte, que acabam em x=150
            'larguraPreco' => 45,   // até x=198, borda interna da caixa escura
            'yVista' => 152,
            'yPrazo' => 166.8,      // as linhas de prazo ficam centradas na caixa
            'passoPrazo' => 3.8,
            'xPayback' => 50,       // logo após o rótulo "Payback:"
            'yPayback' => 189,
            'larguraPayback' => 57,
        ],
        'N-TYPE' => [
            'viabColunaX' => 179,   // centro da coluna "N-TYPE"
            'xQtd' => 16,
            'xDesc' => 27,
            'larguraDesc' => 105,
            'yLista' => 205,        // 1a linha, abaixo de "Tecnologia N-TYPE"
            'alturaLista' => 45,
            'xPreco' => 153,
            'larguraPreco' => 45,
            'yVista' => 222,
            'yPrazo' => 237.3,
            'passoPrazo' => 3.8,
            'xPayback' => 50,
            'yPayback' => 256,
            'larguraPayback' => 57,
        ],
    ];

    // ----------------------------------------------------------------------
    // Tempo de payback, exibido de forma simples (ex.: "1 ano e 6 meses").
    // $payback já vem calculado em ANOS mais acima (preço final dividido pela
    // economia anual); aqui ele só vira texto.
    // ----------------------------------------------------------------------
    $anosPayback = (int) floor($payback);
    $mesesPayback = (int) round(($payback - $anosPayback) * 12);

    // O arredondamento dos meses pode chegar a 12; normaliza para +1 ano.
    if ($mesesPayback >= 12) {
        $anosPayback += 1;
        $mesesPayback = 0;
    }

    if ($payback <= 0) {
        $paybackTexto = 'Nao se paga no periodo analisado';
    } else {
        $partes = [];
        if ($anosPayback > 0) {
            $partes[] = $anosPayback . ($anosPayback == 1 ? ' ano' : ' anos');
        }
        if ($mesesPayback > 0) {
            $partes[] = $mesesPayback . ($mesesPayback == 1 ? ' mês' : ' meses');
        }
        // Payback menor que meio mês: evita string vazia.
        $paybackTexto = $partes ? implode(' e ', $partes) : 'Menos de 1 mês';
    }

    // ----------------------------------------------------------------------
    // Composição da proposta: a lista é FIXA nesta modalidade (o autoconsumo
    // não recebe tabela de componentes do kit).
    // ----------------------------------------------------------------------
    $qtdEstrutrura = number_format(($qtdmodulosArredondado / 4), 0, ',', '.');
    $qtdCabos = number_format(($qtdmodulosArredondado * 2), 0, ',', '.');

    if ($solo === true) {
        $textoEstrutura = "ESTRUTURA SOLO";
    } elseif ($carport === true) {
        $textoEstrutura = "ESTRUTURA CARPORT";
    } else {
        $textoEstrutura = "$qtdEstrutrura ESTRUTURA COLONIAL/FIBROMETAL/FIBROMADEIRA/METÁLICO";
    }

    $itensComposicao = [
        // Sem marca aqui de propósito: cada tecnologia troca esta linha pelo
        // painel dela (ver trocarPainelNaComposicao mais abaixo).
        ['qtd' => '', 'desc' => "$qtdmodulosArredondado MÓDULO SOLAR"],
        ['qtd' => '', 'desc' => "INVERSOR 220V CHINT/SAJ/SOLIS/SOLPLANET $potenciaInversor KW"],
        ['qtd' => '', 'desc' => $textoEstrutura],
        ['qtd' => '', 'desc' => "$qtdCabos CABO SOLAR PV 1.8KVCC 4MM PRETO NBR 16612"],
        ['qtd' => '', 'desc' => "$qtdCabos CABO SOLAR PV 1.8KVCC 4MM VERMELHO NBR 16612"],
        ['qtd' => '', 'desc' => "INSTALAÇÃO / MÃO DE OBRA / EMISSÃO DE ART"],
        ['qtd' => '', 'desc' => "RAMAL DE LIGAÇÃO LIMITADO A 10 METROS (INVERSOR PADRÃO)"],
        ['qtd' => '', 'desc' => "1 ANO DE SEGURO INCLUSO"],
        ['qtd' => '', 'desc' => $textoPadrao],
    ];

    // Valores da tabela "Estudo de viabilidade", na ordem de $viabLinhasY.
    $viabValoresLongi = array(
        number_format((float) $potenciaGerador, 2, ',', '.') . ' kWp',
        number_format((float) $metrosOcupados, 2, ',', '.') . ' m²',
        number_format((float) $peso, 0, ',', '.') . ' kg',
        number_format((float) $mediaArredondado, 0, ',', '.') . ' kWh/mês',
        number_format((float) $geracaoArredondado, 0, ',', '.') . ' kWh/mês',
    );

    // Linhas da caixa "À prazo". Quando o formulário manda um valor de preço a
    // prazo, é ele que a arte quer mostrar; sem esse valor, caímos nas 3
    // parcelas simuladas — mesma regra que a página de análise já usava.
    if ($precoFinalPrazo > 0) {
        $linhasPrazo = [$precoFinalPrazoRs];
    } else {
        $linhasPrazo = [
            "36x de $valorParcelaRs",
            "48x de $valorParcela2Rs",
            "60x de $valorParcela3Rs",
        ];
    }

    // =======================================================================
    // PONTO ÚNICO DE LIGAÇÃO DAS TECNOLOGIAS — é SÓ AQUI que se mexe.
    // -----------------------------------------------------------------------
    // Hoje o orçamento que chega é um só, então o bloco N-TYPE repete os
    // números do LONGI. É TEMPORÁRIO e combinado, para a página não sair pela
    // metade enquanto a N-TYPE não tem valores próprios.
    //
    // ATENÇÃO: enquanto isso durar, a proposta mostra o MESMO preço nas duas
    // tecnologias, e o cliente vê. Não publique sem os valores reais da
    // N-TYPE, ou sem combinar que as duas custam igual.
    //
    // O painel JÁ é diferente entre as duas — é o único item da composição que
    // muda. A faixa de potência é TEXTO de catálogo, não a potência do módulo
    // do kit: o cálculo da quantidade de módulos continua usando
    // $potenciaModulo, que é o número de verdade.
    // =======================================================================
    $potenciaPainelTexto = '600-625 W';
    $descricaoPainelLongi = "MÓDULO SOLAR LONGI $potenciaPainelTexto";
    $descricaoPainelNtype = "MÓDULO SOLAR ZNSHINE / RONMA / OSDA / WEG $potenciaPainelTexto";

    $dadosLongi = [
        'itens' => trocarPainelNaComposicao($itensComposicao, $descricaoPainelLongi),
        'viabilidade' => $viabValoresLongi,
        'vista' => $precoFinalRs,
        'prazo' => $linhasPrazo,
        'payback' => $paybackTexto,
    ];

    // TODO(N-TYPE): trocar 'vista', 'prazo', 'payback' e 'viabilidade' pelos
    // valores próprios assim que o formulário começar a enviá-los.
    $dadosNtype = $dadosLongi;
    $dadosNtype['itens'] = trocarPainelNaComposicao($itensComposicao, $descricaoPainelNtype);

    $blocos = [
        'LONGI' => $dadosLongi,
        'N-TYPE' => $dadosNtype,
    ];

    // ----------------------------------------------------------------------
    // Desenho da página: uma coluna na tabela do topo e um bloco de
    // composição/preços para cada tecnologia.
    // ----------------------------------------------------------------------
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(50, 50, 50);
    foreach ($blocos as $tecnologia => $dados) {
        $centroX = $blocosLayout[$tecnologia]['viabColunaX'];
        foreach ($dados['viabilidade'] as $indice => $valor) {
            escreverCentralizadoNaCaixa($pdf, $centroX, $viabLinhasY[$indice], $valor, $viabColunaLargura);
        }
    }

    foreach ($blocos as $tecnologia => $dados) {
        escreverBlocoTecnologia($pdf, $dados, $blocosLayout[$tecnologia]);
    }

    // Restaura a fonte/cor padrão antes da próxima página.
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    // Página PGAUT7 — análise financeira + garantia e degradação.
    // Substitui a antiga PGAUT6. O topo ("Análise Financeira") é idêntico nas
    // duas artes, por isso os 6 valores abaixo mantêm as coordenadas; o que
    // mudou é a seção de garantia, que agora vem com a coluna "Marca" vazia
    // na arte e é preenchida por código.
    $pdf->AddPage();
    $pdf->Image('PGAUT7.png', 0, 0, 210, 297);

    // Coluna "Marca" da tabela de garantia. Duas linhas por célula, como a
    // arte comporta. Conteúdo definido pelo time comercial.
    $marcaColunaX = 107;      // centro da coluna "Marca"
    $marcaColunaLargura = 50; // largura útil da célula
    $marcasGarantia = [
        // [linha de cima, linha de baixo, y da 1a linha, y da 2a linha]
        ['ZNSHINE, RONMA,', 'OSDA, LONGI, WEG', 153.5, 159.0],   // Inversor
        ['CHINT, GROWATT,', 'SAJ, SOFAR, WEG', 169.0, 174.5],    // Módulo FV
    ];
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(85, 85, 85);
    foreach ($marcasGarantia as $linha) {
        escreverCentralizadoNaCaixa($pdf, $marcaColunaX, $linha[2], $linha[0], $marcaColunaLargura);
        escreverCentralizadoNaCaixa($pdf, $marcaColunaX, $linha[3], $linha[1], $marcaColunaLargura);
    }

    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->SetTextColor(85, 85, 85);
    $pdf->Text(27, 68, "$gastoSemGeradorAnoRs");
    $pdf->Text(29.5, 96, "$gastoSemGeradorRs");
    $pdf->Text(91, 68, "$gastoComGeradorAnoRs");
    $pdf->Text(93, 96, "$gastoComGeradorRs");
    $pdf->Text(145, 68, "$diferencaGastosAnoRs");
    $pdf->Text(148, 96, "$diferencaGastosRs");
    // // CÓDIGO CORRIGIDO
// // ... (código anterior da página 5) ...
//  $pdf->Text(138, 61.5, "$diferencaGastosRs");

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
    // Bloco de preço: a PGAUT7 não tem caixa para ele, é texto solto na faixa
    // livre entre as notas de rodapé (acabam em y=113) e o título "Garantia e
    // degradação" (começa em y=130). São 15mm — na PGAUT6 eram ~23, por isso
    // as alturas abaixo subiram em relação à arte antiga.
    // O mesmo valor também aparece na PGAUT5, por tecnologia.
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(85, 85, 85);
    if ($precoFinalPrazo > 0) {
        $pdf->Text(106, 115, "À vista: $precoFinalRs");
        $pdf->Text(106, 121.5, "A prazo: $precoFinalPrazoRs");
    } else {
        // fallback: comportamento antigo (só o total à vista)
        $pdf->Text(106, 118, "Total: $precoFinalRs");
    }
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(39, 84, 70);
    $pdf->Text(16, 116, "36 x $valorParcelaRs");
    $pdf->Text(16, 120.1, "48 x $valorParcela2Rs");
    $pdf->Text(16, 124.2, "60 x $valorParcela3Rs");


    // Páginas restantes
    $pdf->AddPage();
    $pdf->Image('PGINV9.png', 0, 0, 210, 297);
    $pdf->AddPage();
    $pdf->Image('PGINV11.png', 0, 0, 210, 297);
    $pdf->AddPage();
    $pdf->Image('PGINV12.png', 0, 0, 210, 297);
    // $pdf->AddPage();
    // $pdf->Image('PGINV9.png', 0, 0, 210, 297);

    $pdf->Output('arquivo_gerado.pdf', 'I');
}
?>