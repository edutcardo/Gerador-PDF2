<?php
require_once('vendor/autoload.php'); // Ou o caminho correto, se você não estiver usando o Composer

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $valor1 = $_POST['valor1'];
    $valor2 = $_POST['valor2'];


    // Criação do PDF
    $pdf = new TCPDF();
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Primeira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg1.png', 0, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(100, 50, "Nome: $valor1 kWh");
    $pdf->Text(20, 60, "Email: $email");


    // Segunda Página (com a imagem genérica e gráfico)
    $pdf->AddPage();  // Adiciona a segunda página
    $pdf->Image('pg2.png', 0, 0, 210, 297);
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Definir fonte para texto
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(20, 50, "Nome: $nome");
    $pdf->Text(20, 60, "Email: $email");

    // Criando um gráfico de barras simples
    // Dados para o gráfico
    $data = [10, 20, 30, 40, 50]; // Valores para as barras
    $labels = ["Jan", "Feb", "Mar", "Apr", "May"]; // Rótulos (meses)

    // Definindo as cores para as barras
    $barColor = [0, 0, 255];  // Cor Azul

    // Posições e tamanho do gráfico
    $x = 40;  // Posição X para o gráfico
    $y = 150; // Posição Y para o gráfico (mais para baixo na página)
    $barWidth = 6; // Largura das barras
    $gap = 12;  // Distância entre as barras

    // Desenhando as barras
    foreach ($data as $index => $value) {
        // Calculando a altura da barra
        $barHeight = $value;

        // Desenhando cada barra com a função Rect()
        $pdf->SetFillColor($barColor[0], $barColor[1], $barColor[2]);
        $pdf->Rect($x + ($index * $gap), $y - $barHeight, $barWidth, $barHeight, 'DF');  // Barra
    }

    // Adicionando rótulos nas barras
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    foreach ($labels as $index => $label) {
        $pdf->Text($x + ($index * $gap) + 5, $y + 5, $label);  // Rótulo de cada mês
    }

    // Terceira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg1.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à terceira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(20, 50, "Nome: $nome");
    $pdf->Text(20, 60, "Email: $email");

    // Quarta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg1.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à quarta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(20, 50, "Nome: $nome");
    $pdf->Text(20, 60, "Email: $email");

    // Quinta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg1.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à quinta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(20, 50, "Nome: $nome");
    $pdf->Text(20, 60, "Email: $email");

    // Sexta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg1.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à sexta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(20, 50, "Nome: $nome");
    $pdf->Text(20, 60, "Email: $email");

    // Sétima Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg1.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à sétima página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(20, 50, "Nome: $nome");
    $pdf->Text(20, 60, "Email: $email");

    // Oitava Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg1.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à oitava página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(20, 50, "Nome: $nome");
    $pdf->Text(20, 60, "Email: $email");

    // Nona Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('pg1.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à nona página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(20, 50, "Nome: $nome");
    $pdf->Text(20, 60, "Email: $email");


    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador
    

    
}
?>