<?php
require_once('vendor/autoload.php'); // Ou o caminho correto, se você não estiver usando o Composer

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $cep = $_POST['cep'];
    $cidade = $_POST['cidade'];
    $uc = $_POST['uc'];
    $inputValorCompensavel = $_POST['inputValorCompensavel'];
    $inputValorFiob = $_POST['inputValorFiob'];
    $inputValorTarifaCrua = $_POST['inputValorTarifaCrua'];
    $media = $_POST['media'];
    $iluminacao = $_POST['iluminacao'];
    $desconto = $_POST['desconto'];
    $numeroDeFases = $_POST['numeroDeFases'];

    if ($numeroDeFases == 'mono rural') {
        $demandaMinima = 1;
    } elseif ($numeroDeFases == 'monofasico') {
        $demandaMinima = 30;
    } elseif ($numeroDeFases == 'bifasico') {
        $demandaMinima = 50;
    } elseif ($numeroDeFases == 'trifasico') {
        $demandaMinima = 100;
    }

    $kwhSemImpostos = ($inputValorTarifaCrua *0.85);
    $precoFatura = $kwhSemImpostos  * $media + $iluminacao;
    $kwhCopel = $inputValorTarifaCrua;
    $precoCopel = ($kwhCopel * $media) + $iluminacao;
    $economia = ($precoCopel -$precoFatura);
    $precoFatura = $precoCopel- $economia ;
    $precoCopelx12 = $precoCopel * 12;
    $precoFaturax12 = $precoFatura * 12;
    $economiax12 = $economia * 12;
    $precoCopelRs = number_format($precoCopel, 2, ',', '.');
    $precoFaturaRs = number_format($precoFatura, 2, ',', '.');
    $economiaRs = number_format($economia, 2, ',', '.');
    $precoCopelRsx12 = number_format($precoCopelx12, 2, ',', '.');
    $precoFaturaRsx12 = number_format($precoFaturax12, 2, ',', '.');
    $economiaRsx12 = number_format($economiax12, 2, ',', '.');
    $diferencaAnual = $precoCopelx12 - $precoFaturax12;
    $diferencaAnualRs =  number_format($diferencaAnual, 2, ',', '.');
    $mediax12 = $media * 12;
    $totalFaturas = $precoCopel + $economia;

    // Criação do PDF
    $pdf = new TCPDF();
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Primeira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('EE1.png', 7, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(50, 27, "$nome");
    $pdf->Text(50, 32.4, "U.C.: $uc");


    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->Text(69, 47.4, "$media kWh");
    $pdf->Text(57, 128, "$economiaRs");
    $pdf->Text(149, 128, "$economiaRsx12");
    $pdf->Text(57, 97, "$precoCopelRs");
    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->Text(165, 47.4, "$precoCopelRs");
    $pdf->Text(149, 97, "$precoFaturaRs");





    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('EE2.png', 7, 0, 210, 297);
    $pdf->Text(42, 160, "$precoCopelRs");
    $pdf->Text(94, 160, "$precoFaturaRs");
    $pdf->Text(150, 160, "$totalFaturas");

    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('EE3.png', 7, 0, 210, 297);

    
    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador
    
}
?>