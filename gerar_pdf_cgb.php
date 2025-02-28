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
    $precoCopelRs = 'R$ ' . number_format($precoCopel, 2, ',', '.');
    $precoFaturaRs = 'R$ ' . number_format($precoFatura, 2, ',', '.');
    $economiaRs = 'R$ ' . number_format($economia, 2, ',', '.');
    $precoCopelRsx12 = 'R$ ' . number_format($precoCopelx12, 2, ',', '.');
    $precoFaturaRsx12 = 'R$ ' . number_format($precoFaturax12, 2, ',', '.');
    $economiaRsx12 = 'R$ ' . number_format($economiax12, 2, ',', '.');
    $diferencaAnual = $precoCopelx12 - $precoFaturax12;
    $diferencaAnualRs =  'R$ ' . number_format($diferencaAnual, 2, ',', '.');
    $mediax12 = $media * 12;

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
    $pdf->Text(49, 96, "$economiaRs");
    $pdf->Text(124, 96, "$economiaRsx12");

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Text(70, 47.3, "$media kWh");
    $pdf->Text(156.5, 136.6, "$mediax12 kWh");

    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->Text(159, 47.3, "$precoCopelRs");
    $pdf->Text(133, 128, "$precoCopelRsx12");
    $pdf->Text(68, 161, "$precoFaturaRs");
    $pdf->Text(133, 161, "$precoFaturaRsx12");

    $pdf->Text(76, 185, "$precoFaturaRsx12");
    $pdf->Text(76, 213, "$precoCopelRsx12");
    $pdf->Text(148, 202, "$diferencaAnualRs");

    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('EE2.png', 7, 0, 210, 297);

    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('EE3.png', 7, 0, 210, 297);

    
    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador
    
}
?>