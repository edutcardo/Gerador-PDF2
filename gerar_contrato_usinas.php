<?php
require_once('vendor/autoload.php'); // Ou o caminho correto, se você não estiver usando o Composer

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $cidade = $_POST['cidade'];
    $uc = $_POST['uc'];



    // Criação do PDF
    $pdf = new TCPDF();
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Primeira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(34.2, 98, "CONTRATO DE VENDA E INSTALAÇÃO DE EQUIPAMENTOS SOLARES FOTOVOLTAICOS");
    $pdf->Text(34.2, 104, "Por este Instrumento,");
    $pdf->MultiCell(
        200, // Largura da célula, 0 usa a largura máxima da página
        0, // Altura da linha, 0 permite altura dinâmica
        "PALLADIUM IMPORTADORA DE EQUIPAMENTOS LTDA, pessoa jurídica de direito privado, inscrita no CNPJ sob o n.º 49.348.620/0001-05, com sede na Av. Colombo, n.º 5088, zona 07, na cidade de Maringá/PR - CEP 87.030-121, neste ato representada por seu representante legal, doravante denominada DISTRIBUIDORA. ", // Texto a ser inserido
        0, // Sem borda
        'J', 
        false, // Sem preenchimento
        1, // Move o cursor para a próxima linha após escrever
        15, // Posição X
        108 // Posição Y
    );
    $pdf->Text(34.2, 138, "NEO MARINGÁ ENGENHARIA ELÉTRICA LTDA, pessoa jurídica devidamente inscrita no CNPJ ");
    
    $pdf->Text(34.6, 160, "Disponibilidade de área necessária: $metrosOcupados m²");
    $pdf->Text(34.6, 166.25, "Quantidade de Módulos Fotovoltáicos: $qtdmodulosArredondado Placas");
    $pdf->Text(34.6, 172.5, "Potência do Projeto: $potenciaGerador kWp");
    $pdf->Text(34.6, 178.75, "Média de Consumo: $media kWh");
    $pdf->Text(34.6, 185, "Geração Estimada: $geracao kWh");

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text(46, 223, "$dataAtual");


    // Segunda Página (com a imagem genérica e gráfico)
    $pdf->AddPage();  // Adiciona a segunda página

    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página


    // Terceira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

    
    // Definir fonte e adicionar conteúdo à terceira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Quarta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(255, 0, 0);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(158, 141.5, "$percentualSolarArredondado %");

    // Definir fonte e adicionar conteúdo à quarta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Quinta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);



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

    // Título do gráfico
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text($xInicial, $yInicial - 10, '');

    // Definir fonte e adicionar conteúdo à quinta página
    $pdf->SetFont('helvetica','B', 12);
    $pdf->SetTextColor(0, 0, 0);

    // Sexta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

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


    // Sétima Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

    
    // Definir fonte e adicionar conteúdo à sétima página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Nona Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

    
    // Definir fonte e adicionar conteúdo à nona página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador
    
    
}
?>