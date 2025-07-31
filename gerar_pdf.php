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


    // Cálculos iniciais da proposta

    $geracao = $potenciaGerador * 3.9 * 30;
    $qtdmodulos = ($potenciaGerador*1000)/$potenciaModulo;
    $qtdmodulosArredondado = round($qtdmodulos);
    $metrosOcupados = $qtdmodulosArredondado * 2.9;

    // Cálculos PG4
    $peso = $qtdmodulosArredondado * 33;
    $percentualSolar = ($geracao / $media) * 100;
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

    function calcularMargemEComissao($potenciaGerador) {
        $margem = 0;
        $comissao = 0;
        $mobra = 0;

        if ($potenciaGerador >= 0 && $potenciaGerador < 20) {
            $margem = 1.4816930;
            $mobra = 136.76;
            $comissao = 0.07; // Original
        } elseif ($potenciaGerador >= 20 && $potenciaGerador < 60) {
            $margem = 1.4589524;
            $mobra = 134.46;
            $comissao = 0.07; // Original
        } elseif ($potenciaGerador >= 60 && $potenciaGerador < 114) {
            $margem = 1.3667618;
            $mobra = 250.45;
            $comissao = 0.06; // Original
        } elseif ($potenciaGerador >= 114) {
            $margem = 1.3213386;
            $mobra = 242.52;
            $comissao = 0.05; // Original
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
            $padrao = 11279.8;
            $descPadrao = "3x175A";
            break;
        case "3x200A":
            $padrao = 12969.57;
            $descPadrao = "3x200A";
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



    $precoFinal =(($precoKit * $margem) + ($mobra * $qtdmodulosArredondado) + $valorFixo + $valoramais + $padrao) * $desconto ;
    $precoFinalRs = 'R$ ' . number_format($precoFinal, 2, ',', '.');

function calcularParcela_corrigido($taxa, $nper, $vp, $vf = 0, $tipo = 0) {
    // Define uma alta escala de precisão para os cálculos intermediários
    bcscale(10);

    // Converte todas as entradas para strings para uso com BCMath
    $taxa_str = (string)$taxa;
    $nper_str = (string)abs($nper); // Usa o valor absoluto, pois o número de períodos deve ser positivo
    $vp_str   = (string)$vp;
    $vf_str   = (string)$vf;

    // Se a taxa for 0, o cálculo é uma simples divisão
    if (bccomp($taxa_str, '0') == 0) {
        $soma_valores = bcadd($vp_str, $vf_str);
        return bcdiv(bcmul($soma_valores, '-1'), $nper_str, 2);
    }

    // Implementação da fórmula da Tabela Price (PMT) com BCMath
    // Fórmula: P = (PV * i) / (1 - (1 + i)^-n)
    // Para evitar a potência negativa, usamos a forma equivalente: P = (PV * i * (1+i)^n) / ((1+i)^n - 1)

    // 1. (1 + i)
    $um_mais_i = bcadd('1', $taxa_str);

    // 2. (1 + i)^n
    $fator_potencia = bcpow($um_mais_i, $nper_str);

    // 3. Numerador: (PV * i * (1+i)^n)
    $numerador_parcial = bcmul($vp_str, $taxa_str);
    $numerador = bcmul($numerador_parcial, $fator_potencia);

    // 4. Denominador: ((1+i)^n - 1)
    $denominador = bcsub($fator_potencia, '1');
    
    // Ignora $vf e $tipo por enquanto, pois a fórmula padrão da Tabela Price não os utiliza diretamente
    // e o seu exemplo usa os valores padrão (0).

    // 5. Cálculo da parcela: Numerador / Denominador
    $valorParcela = bcdiv($numerador, $denominador, 2); // Arredonda para 2 casas decimais no final

    // Retorna o valor negativo, como na função original
    return bcmul($valorParcela, '-1', 2);
    }

    // --- Exemplo de uso com suas variáveis ---

    $taxa = 0.015; // Taxa de juros mensal (1.5%)
    $vp = $precoFinal;   // Valor presente do empréstimo
    $vf = 0;       // Valor futuro
    $tipo = 0;     // Tipo

    // Períodos
    $nper1 = 36;
    $nper2 = 48;
    $nper3 = 60;

    // Cálculo para 36 meses
    $valorParcela = calcularParcela_corrigido($taxa, $nper1, $vp, $vf, $tipo);
    $valorParcelaRs = 'R$ '. number_format(abs((float)$valorParcela), 2, ',', '.');

    // Cálculo para 48 meses
    $valorParcela2 = calcularParcela_corrigido($taxa, $nper2, $vp, $vf, $tipo);
    $valorParcela2Rs = 'R$ '. number_format(abs((float)$valorParcela2), 2, ',', '.');

    // Cálculo para 60 meses
    $valorParcela3 = calcularParcela_corrigido($taxa, $nper3, $vp, $vf, $tipo);
    $valorParcela3Rs = 'R$ '. number_format(abs((float)$valorParcela3), 2, ',', '.');


    $payback = $precoFinal / $diferencaGastosAno;
    $paybackArredondado = round($payback);
    $retorno25anos = $diferencaGastosAno * 25;
    $retorno25anosRs = 'R$ ' . number_format($retorno25anos, 2, ',', '.');

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

    // Data atual
    $formatoData = 'd/m/Y';
    $dataAtual = date($formatoData);

    // Criação do PDF
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
    
    $pdf->Text(92, 157.9, "$metrosOcupados m²");
    $pdf->Text(99, 164.70, "$qtdmodulosArredondado Placas");
    $pdf->Text(63.7, 171, "$potenciaGerador kWp");
    $pdf->Text(61.7, 178.2, "$media kWh");
    $pdf->Text(60.7, 185, "$geracao kWh");

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text(21, 225.5, "$dataAtual");

    // Segunda Página (com a imagem genérica e gráfico)
    $pdf->AddPage();  // Adiciona a segunda página
    $pdf->Image('pg2.png', 0, 0, 210, 297);
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

//     // Dados para o gráfico
//     $data = [$jan3, $fev3, $mar3, $abr3, $mai3, $jun3, $jul3, $ago3, $set3, $out3, $nov3, $dez3]; // Valores para as barras
//     $labels = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"]; // Rótulos (meses)

//     // Definindo as cores para as barras
//     $barColor = [1, 133, 56];  // Cor Verde Canal

//     // Posições e tamanho do gráfico
//     $x = 23;  // Posição X para o gráfico
//     $y = 260; // Posição Y para o gráfico (mais para baixo na página)
//     $barWidth = 5; // Largura das barras
//     $gap = 15;  // Distância entre as barras
//     $maxBarHeight = 40; // Altura máxima do gráfico (limite)

//     // Determinando o maior valor para escalar as barras
//     $maxValue = max($data);

//     // Desenhar a moldura ao redor do gráfico
//     $molduraX = $x - 5; // Ajuste para começar um pouco antes das barras
//     $molduraY = $y - $maxBarHeight - 7; // Ajuste para incluir espaço acima das barras
//     $molduraWidth = count($data) * $gap; // Largura total baseada no número de barras e espaçamento
//     $molduraHeight = $maxBarHeight + 10; // Altura total (incluindo margem superior e inferior)

//     $pdf->SetDrawColor(0, 0, 0); // Cor da moldura (preto)
//     $pdf->SetLineWidth(0.006); // Espessura da linha da moldura
//     $pdf->Rect($molduraX, $molduraY, $molduraWidth, $molduraHeight, 'D'); // 'D' para apenas desenhar a linha


//     // Desenhando as barras e adicionando os valores
//     foreach ($data as $index => $value) {
//     // Calculando a altura proporcional da barra
//     $barHeight = ($value / $maxValue) * $maxBarHeight;

//     // Desenhando cada barra
//     $pdf->SetFillColor(60, 179, 113); // Verde
//     $pdf->Rect($x + ($index * $gap), $y - $barHeight, $barWidth, $barHeight, 'DF'); // Barra

//     // Adicionando o valor acima da barra
//     $pdf->SetFont('helvetica', '', 10);
//     $pdf->SetTextColor(0, 0, 0);
//     $valueX = $x + ($index * $gap) + ($barWidth / 2) - 5; // Ajuste para centralizar o texto
//     $valueY = $y - $barHeight - 5; // Ajuste para posicionar acima da barra
//     $pdf->Text($valueX, $valueY, (string)$value); // Adiciona o valor como texto
// }
//     // Adicionando rótulos nas barras
//     $pdf->SetFont('helvetica', '', 10);
//     $pdf->SetTextColor(0, 0, 0);
//     foreach ($labels as $index => $label) {
//         // Centralizar os rótulos horizontalmente e posicionar abaixo das barras
//         $labelX = $x + ($index * $gap) + ($barWidth / 2) - (strlen($label) * 1.5); // Ajuste baseado no comprimento do texto
//         $labelY = $y + 5; // Posição logo abaixo da barra
//         $pdf->Text($labelX, $labelY, $label);
//     }

    // Terceira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg3.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à terceira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);


    // Quarta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
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

    $pdf->Text(23, 180, "INCLUSO MATERIAL ELÉTRICO");
    $pdf->Text(23, 188, "INCLUSO PROJETO SOLAR FOTOVOLTAICO");
    $pdf->Text(23, 196, "INCLUSO ART DE PROJETO E EXECUÇÃO");
    $pdf->Text(23, 204, "INCLUSO ACOMPANHAMENTO JUNTO A CONCESSIONÁRIA LOCAL");
    $pdf->Text(23, 212, "$qtdmodulosArredondado MÓDULO SOLAR SUNOVA/OSDA/RONMA " . round($potenciaModulo) . " Wp ");
    $pdf->Text(23, 220, "1 INVERSOR 220V CHINT/SAJ " . round($potenciaInversor) . " KW");
    $pdf->Text(23, 228, "ESTRUTURA DE FIXAÇÃO");


    // $componentes = html_entity_decode($componentes);
    // $componentes = str_replace(
    //     ["<\/th>", "<\/td>", "<\/tr>", "<\/table>"], 
    //     ["</th>", "</td>", "</tr>", "</table>"], 
    //     $componentes
    // );
    
    // // Extrair os dados da tabela
    // preg_match_all('/<td>\s*(.*?)\s*<\/td>\s*<td>\s*(.*?)\s*<\/td>\s*<td>\s*(.*?)\s*<\/td>/', $componentes, $matches, PREG_SET_ORDER);
    
    // // Ajuste na altura da descrição
    // $y = 176; // Posição inicial Y
    // $linhaAltura = 8; // Altura de cada linha no PDF
    // $larguraDescricao = 180; // Ajuste para a largura da descrição
    // $larguraQuantidade = 20; // Ajuste para a largura da quantidade
    // $maxY = 280; // Limite Y da página
    
    // // Função para adicionar uma nova página se necessário
    // function verificaQuebraPagina($pdf, $y, $linhaAltura, $maxY) {
    //     if ($y + $linhaAltura > $maxY) {
    //         $pdf->AddPage(); // Adiciona uma nova página
    //         return 10; // Reseta a posição Y após a nova página
    //     }
    //     return $y;
    // }
    
    // // Escrever os dados extraídos no PDF
    // if (empty($matches)) {
    //     $pdf->Text(10, $y + 3, "Nenhum dado encontrado.");
    // } else {
    //     foreach ($matches as $match) {
    //         $sku = trim($match[1]);
    //         $quantidade = trim($match[2]);
    //         $descricao = trim($match[3]);
    
    //         // Verificar se há espaço suficiente para escrever na página
    //         $y = verificaQuebraPagina($pdf, $y, $linhaAltura, $maxY);
    
    //         // Adicionar quantidade, com ajuste para subir um pouco
    //         $pdf->SetXY(16, $y - 0.9); // Ajuste para subir um pouco a posição Y
    //         $pdf->Cell($larguraQuantidade, $linhaAltura, $quantidade, 0, 0, 'L'); // Alinhamento à esquerda
    
    //         // Adicionar a descrição com quebra automática de linha
    //         $pdf->SetXY(27, $y); // Ajuste a posição X para alinhar a descrição
    //         $pdf->MultiCell($larguraDescricao, $linhaAltura, $descricao, 0, 'L', 0);
    
    //         // Atualizar Y para a próxima linha somente após o MultiCell
    //         $y += max($linhaAltura, $pdf->GetY() - $y);
    //     }
    // }
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(16, 256, "$textoPadrao");
    
    // Definir fonte e adicionar conteúdo à quarta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);


    // Quinta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg5.png', 0, 0, 210, 297);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(255, 255, 255);

    $pdf->Text(45, 45, "$gastoSemGeradorAnoRs");
    $pdf->Text(47.5, 61.5, "$gastoSemGeradorRs");
    $pdf->Text(91, 45, "$gastoComGeradorAnoRs");
    $pdf->Text(93, 61.5, "$gastoComGeradorRs");
    $pdf->Text(135, 45, "$diferencaGastosAnoRs");
    $pdf->Text(138, 61.5, "$diferencaGastosRs");

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(147, 98.5, "$precoFinalRs");

    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->Text(26, 123, "36 X $valorParcelaRs");
    $pdf->Text(85, 123, "48 X $valorParcela2Rs");
    $pdf->Text(146, 123, "60 X $valorParcela3Rs");

    $pdf->Text(152, 166, "$paybackArredondado anos");
    $pdf->Text(143, 178, "$retorno25anosRs");

    // Dados do Payback
    $anos = 25; // Total de anos

    // Calcular o retorno acumulado ao longo dos anos
    $dados = [];
    $retornoAcumulado = 0;
    for ($ano = 1; $ano <= $anos; $ano++) {
        $retornoAcumulado += $diferencaGastosAno;
        $dados[$ano] = $retornoAcumulado - $precoFinal; // Payback acumulado
    }

    // Configurações do gráfico
    $xInicial = 20; // Posição X do gráfico
    $yInicial = 213; // Posição Y do gráfico
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
    $pdf->Text($xInicial, $yInicial - 10, 'Gráfico de Payback (25 anos)');
    
    // Definir fonte e adicionar conteúdo à quinta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Sexta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg6.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à sexta página
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Text(90, 50, "$fabricante ". round($potenciaInversor) ." kW");
    $pdf->Text(155, 50, "10 ANOS");
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Text(75,67, "$marca ". round($potenciaModulo) ." W");
    $pdf->Text(146, 90, "$valorParcela3Rs");


    // Sétima Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg7.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à sétima página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Oitava Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg8.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à oitava página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Nona Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg9.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à nona página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);


    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador
    
}
?>