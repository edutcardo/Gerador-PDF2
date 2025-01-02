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

    $kwhSemImpostos = $inputValorTarifaCrua - ($inputValorFiob * 0.3);
    $precoFatura = ((($demandaMinima*0.82) + $iluminacao + (($media - $demandaMinima)* $kwhSemImpostos)) + (($demandaMinima * $kwhCopel)+ $iluminacao));
    $kwhCopel = $inputValorTarifaCrua * 1.19 * 1.0925;
    $precoCopel = ($kwhCopel * $media) + $iluminacao;
    $economia = $precoCopel - $precoFatura;
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

    // Criação do PDF
    $pdf = new TCPDF();
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Primeira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('esteconomia.png', 7, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(72, 72, "$nome");
    $pdf->Text(81, 76.87, "$uc");

    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->Text(49, 96, "$economiaRs");
    $pdf->Text(124, 96, "$economiaRsx12");

    $pdf->Text(68, 128, "$precoCopelRs");
    $pdf->Text(133, 128, "$precoCopelRsx12");
    $pdf->Text(68, 161, "$precoFaturaRs");
    $pdf->Text(133, 161, "$precoFaturaRsx12");

    $pdf->Text(76, 185, "$precoFaturaRsx12");
    $pdf->Text(76, 213, "$precoCopelRsx12");
    $pdf->Text(148, 202, "$diferencaAnualRs");

    
    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador
    
}
?>