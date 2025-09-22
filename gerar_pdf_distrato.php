<?php
// Mantenha o caminho correto para o seu autoload.php
require_once('vendor/autoload.php');

// Apenas executa se a requisição for do tipo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recebe os dados com chaves em português
    $nome = isset($_POST['nome']) ? $_POST['nome'] : 'Não informado';
    $telefone = isset($_POST['telefone']) ? $_POST['telefone'] : 'Não informado';
    $email = isset($_POST['email']) ? $_POST['email'] : 'Não informado';
    $endereco = isset($_POST['endereco']) ? $_POST['endereco'] : 'Não informado';
    $dataAtual = date('d/m/Y');

    // 2. Cria uma nova instância do TCPDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator('Seu Sistema RH');
    $pdf->SetAuthor('RH');
    $pdf->SetTitle('Termo de Distrato - ' . $nome);

    $pdf->AddPage();

    // 3. Define o conteúdo do PDF
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 15, 'TERMO DE DISTRATO DE CONTRATO DE TRABALHO', 0, 1, 'C');
    $pdf->Ln(10);

    $pdf->SetFont('helvetica', '', 11);

    // Corpo do texto
    $html = <<<EOD
<p>Pelo presente instrumento particular, de um lado, <strong>SUA EMPRESA LTDA</strong>, pessoa jurídica de direito privado, inscrita no CNPJ sob o nº XX.XXX.XXX/0001-XX, com sede na Rua Exemplo, nº 123, Bairro, Cidade - Estado, neste ato representada na forma de seu Contrato Social, doravante denominada <strong>EMPREGADORA</strong>.</p>

<p>De outro lado, <strong>$nome</strong>, portador(a) do CPF nº (informar), residente e domiciliado(a) no endereço: $endereco, contato telefônico $telefone e e-mail $email, doravante denominado(a) <strong>EMPREGADO(A)</strong>.</p>

<p>Resolvem, de comum acordo, na melhor forma do direito, rescindir o Contrato Individual de Trabalho firmado entre as partes, que se regerá pelas seguintes cláusulas e condições:</p>

<p><strong>CLÁUSULA PRIMEIRA - DO OBJETO</strong><br>O presente distrato tem por objeto a rescisão, de comum acordo, do contrato de trabalho firmado entre a EMPREGADORA e o(a) EMPREGADO(A), com fulcro no artigo 484-A da Consolidação das Leis do Trabalho (CLT).</p>

<p><strong>CLÁUSULA SEGUNDA - DA DATA DO TÉRMINO</strong><br>O contrato de trabalho se encerra em definitivo na data de $dataAtual, sendo este o último dia de trabalho do(a) EMPREGADO(A).</p>
<br><br><br><br>

<p align="center">_________________________________________<br>SUA EMPRESA LTDA</p>
<br><br>

<p align="center">_________________________________________<br>$nome</p>
<br><br>

<p align="center">Cidade, $dataAtual.</p>
EOD;

    $pdf->writeHTML($html, true, false, true, false, '');

    // 4. Gera o PDF como uma string (parâmetro 'S') para ser capturado pela API
    $pdfContent = $pdf->Output('distrato.pdf', 'S');
    
    // 5. Envia o conteúdo do PDF de volta
    header('Content-Type: application/pdf');
    echo $pdfContent;

} else {
    http_response_code(405);
    echo "Método não permitido.";
}
?>