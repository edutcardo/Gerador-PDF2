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

    $kwhSemImpostos = $inputValorTarifaCrua - ($inputValorFiob * 0.3);
    $precoFatura = (((($kwhSemImpostos * $media * 0.85)* 1.19 * 1.0925)) + $iluminacao);
    $kwhCopel = $inputValorTarifaCrua * 1.19 * 1.0925;
    $precoCopel = ($kwhCopel * $media) + $iluminacao;
    $economia = $precoCopel - $precoFatura;


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
    $pdf->Text(72, 72, "$kwhSemImpostos $precoFatura $precoCopel $economia $inputValorFiob");
    $pdf->Text(81, 76.87, "$uc");
    
    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador
    
}
?>