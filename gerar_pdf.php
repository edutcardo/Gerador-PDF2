<?php
require_once('vendor/autoload.php'); // Ou o caminho correto, se você não estiver usando o Composer

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $valor1 = $_POST['valor1'];
    $valor2 = $_POST['valor2'];
    $cidade = $_POST['cidade'];
    $uc = $_POST['uc'];
    $media = $_POST['media'];
    $iluminacao = $_POST['iluminacao'];
    $potenciaGerador = $_POST['potenciaGerador'];
    $componentes = $_POST['componentes'];

    // Cálculos iniciais da proposta
    $resultado = $valor1 + $valor2;
    $geracao = $potenciaGerador * 3.9 * 30;

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


    $pdf->Text(63, 172.5, "$potenciaGerador kWp");
    $pdf->Text(61.5, 178.75, "$media kWh");
    $pdf->Text(57.5, 185, "$geracao kWh");


    // Segunda Página (com a imagem genérica e gráfico)
    $pdf->AddPage();  // Adiciona a segunda página
    $pdf->Image('pg2.png', 0, 0, 210, 297);
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Dados para o gráfico
    $data = [181, 179, 150, 189, 200, 187, 220, 230, 180, 198, 187, 200]; // Valores para as barras
    $labels = ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"]; // Rótulos (meses)

    // Definindo as cores para as barras
    $barColor = [0, 0, 255];  // Cor Azul

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
    $pdf->SetFillColor($barColor[0], $barColor[1], $barColor[2]);
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
    $pdf->Image('pg3.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à terceira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);


    // Quarta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg4.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à quarta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);


    // Quinta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg5.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à quinta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);


    // Sexta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg6.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à sexta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);


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