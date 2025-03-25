<?php
require_once('vendor/autoload.php'); // Ou o caminho correto, se você não estiver usando o Composer


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    $nome = $data['nome'];
    $endereco = $data['endereco'];
    $cep = $data['cep'];
    $cidade = $data['cidade'];
    $uc = $data['uc'];
    $inputValorCompensavel = $data['inputValorCompensavel'];
    $inputValorFiob = $data['inputValorFiob'];
    $inputValorTarifaCrua = $data['inputValorTarifaCrua'];
    $media = $data['media'];
    $iluminacao = $data['iluminacao'];
    $desconto = $data['desconto'];
    $numeroDeFases = $data['numeroDeFases'];


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
    $resto = $demandaMinima * 0.82 + $iluminacao;
    $restoRs = number_format($resto, 2, ',', '.');
    $restoMaisFatura = $precoFatura + $resto;
    $restoMaisFaturaRs = number_format($restoMaisFatura, 2, ',', '.');

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
    $pdf->Text(40, 27, "$nome");
    $pdf->Text(40, 32.4, "U.C.: $uc");
    


    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Text(62, 47.5, "$media kWh");
    $pdf->Text(165, 47.5, "$precoCopelRs");
    $pdf->Text(50, 97, "$precoCopelRs");
    $pdf->Text(142, 97, "$precoFaturaRs");
    $pdf->Text(50, 128, "$economiaRs");
    $pdf->Text(142, 128, "$economiaRsx12");


    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Image('EE2.png', 0, 0, 210, 297);
    $pdf->Text(32, 152, "$restoRs");
    $pdf->Text(98, 152, "$precoFaturaRs");
    $pdf->Text(165, 152, "$restoMaisFaturaRs");

    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('EE3.png', 0, 0, 210, 297);

    
    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador
    
}
?>