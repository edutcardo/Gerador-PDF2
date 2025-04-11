<?php
require_once('vendor/autoload.php'); // Ou o caminho correto, se você não estiver usando o Composer


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");




if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    $input1 = $data['input1'];
    $input2 = $data['input2'];
    $input3= $data['input3'];
    $input4 = $data['input4'];
    $input5 = $data['input5'];
    $input6 = $data['input6'];
    $input7 = $data['input7'];
    $input8 = $data['input8'];
    $input9 = $data['input9'];
    $input10 = $data['input10'];
    $input11 = $data['input11'];
    $consumomes = $data['consumomes'];
    $consumoano = $data['consumoano'];
    $contacopel = $data['contacopel'];
    $iluminacao = $data['iluminacao'];

    $resto = (($input5*1.19*1.025) - $input7);
    $restomaisfatura = $resto + $input7;

    $input7s = number_format($input7, 2, ',', '.');
    $input5s = number_format($input5, 2, ',', '.');
    $input3s = number_format($input3, 2, ',', '.');
    $input4s = number_format($input4, 2, ',', '.');
    $restos = number_format($resto, 2, ',', '.');
    $restomaisfaturas = number_format($restomaisfatura, 2, ',', '.');
    
    $input7sx12 = (($input7) * 12);
    $input5sx12 = (($input5) * 12);
    $diferenca = $input5sx12 - $input7sx12;

    $input7sx12s = number_format($input7sx12, 2, ',', '.');
    $input5sx12s = number_format($input5sx12, 2, ',', '.');
    $diferencas = number_format($diferenca, 2, ',', '.');

    // Criação do PDF
    $pdf = new TCPDF();
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Primeira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('EE1.png', 0, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(40, 27, "$input1");
    $pdf->Text(40, 32.4, "U.C.: $input2");
    


    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Text(62, 47.5, "$consumomes kWh");
    $pdf->Text(165, 47.5, "$input5s");
    $pdf->Text(50, 97, "$input5s");
    $pdf->Text(142, 97, "$input7s");
    $pdf->Text(50, 128, "$input3s");
    $pdf->Text(142, 128, "$input4s");


    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Image('EE2.png', 0, 0, 210, 297);
    $pdf->Text(32, 152, "$input5sx12s");
    $pdf->Text(98, 152, "$input7sx12s");
    $pdf->Text(165, 152, "$diferencas");

    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('EE3.png', 0, 0, 210, 297);

    
    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador
    
}
?>