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


    // Data atual
    $formatoData = 'd/m/Y';
    $dataAtual = date($formatoData);

    // Criação do PDF
    $pdf = new TCPDF();
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Primeira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('esteconomia.png', 0, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(21, 94, "Nome: $nome");
    $pdf->Text(21, 100, "Endereço: $endereco");
    $pdf->Text(21, 106, "Cidade: $cidade");
    $pdf->Text(21, 128, "UC $uc");
    

    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador
    
}
?>