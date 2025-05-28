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

    // Inputs diretos do formulário/JSON
    // É uma boa prática adicionar valores padrão ou verificações para evitar erros caso alguma chave não exista
    $input1 = $data['input1'] ?? '';
    $input2 = $data['input2'] ?? '';
    $input3_valor = $data['input3'] ?? 0; // Assumindo que input3 é um valor numérico
    $input4_valor = $data['input4'] ?? 0; // Assumindo que input4 é um valor numérico
    $input5_base = $data['input5'] ?? 0;  // Valor base para cálculo, garantir que seja numérico
    $input6 = $data['input6'] ?? '';
    $input7_proposta = $data['input7'] ?? 0; // Valor da proposta, garantir que seja numérico
    $input8 = $data['input8'] ?? '';
    $input9 = $data['input9'] ?? '';
    $input10 = $data['input10'] ?? '';
    $input11 = $data['input11'] ?? '';
    $consumomes = $data['consumomes'] ?? ''; // Se for numérico, considere (float)$data['consumomes'] ?? 0;
    $consumoano = $data['consumoano'] ?? ''; // Não usado nos cálculos principais, mas recuperado
    $contacopel = $data['contacopel'] ?? ''; // Não usado nos cálculos principais, mas recuperado
    $iluminacao = $data['iluminacao'] ?? ''; // Não usado nos cálculos principais, mas recuperado

    // --- Cálculos Chave ---
    // Assegura que os inputs usados em operações aritméticas sejam tratados como números (float)
    $input5_base_float = (float)$input5_base;
    $input7_proposta_float = (float)$input7_proposta;
    $input3_valor_float = (float)$input3_valor;
    $input4_valor_float = (float)$input4_valor;

    // Valor mensal que será usado para comparação (ex: fatura atual ajustada com taxas/impostos)
    $valorMensalComparacao = $input5_base_float * 1.19 * 1.025;

    // Economia mensal
    $economiaMensal = $valorMensalComparacao - $input7_proposta_float;

    // Formatação dos valores mensais para o PDF
    $input3s = number_format($input3_valor_float, 2, ',', '.');
    $input4s = number_format($input4_valor_float, 2, ',', '.');
    $valorMensalComparacao_s = number_format($valorMensalComparacao, 2, ',', '.');
    $input7_proposta_s = number_format($input7_proposta_float, 2, ',', '.');
    // Se precisar exibir a economia mensal no PDF, descomente a linha abaixo:
    // $economiaMensal_s = number_format($economiaMensal, 2, ',', '.');

    // --- Cálculos Anuais ---
    $valorAnualComparacao = $valorMensalComparacao * 12;
    $input7_propostaAnual = $input7_proposta_float * 12;
    $economiaAnual = $economiaMensal * 12; // Esta é a diferença anual baseada nos valores ajustados

    // Formatação dos valores anuais para o PDF
    $valorAnualComparacao_s = number_format($valorAnualComparacao, 2, ',', '.');
    $input7_propostaAnual_s = number_format($input7_propostaAnual, 2, ',', '.');
    $economiaAnual_s = number_format($economiaAnual, 2, ',', '.');

    // Criação do PDF
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Primeira Página (com a imagem EE1.png)
    $pdf->AddPage();
    $pdf->Image('EE1.png', 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);

    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0); // Preto
    $pdf->Text(40, 27, "$input1");
    $pdf->Text(40, 32.4, "U.C.: $input2");
    
    $pdf->SetFont('helvetica', 'B', 16);
    // Certifique-se que $consumomes é um valor adequado para exibição aqui
    $pdf->Text(62, 47.5, "$consumomes kWh");

    // Valores mensais atualizados
    $pdf->Text(165, 47.5, "$valorMensalComparacao_s"); // Era $input5s
    $pdf->Text(50, 97, "$valorMensalComparacao_s");    // Era $input5s
    $pdf->Text(142, 97, "$input7_proposta_s");       // Era $input7s

    // $input3s e $input4s - Mantidos como no original, formatados a partir de input3_valor e input4_valor
    $pdf->Text(50, 128, "$input3s");
    $pdf->Text(142, 128, "$input4s");

    // Segunda Página (com a imagem EE2.png)
    $pdf->AddPage();
    $pdf->Image('EE2.png', 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
    $pdf->SetFont('helvetica', 'B', 16); // Reafirmar a fonte para a nova página, se necessário
    $pdf->SetTextColor(0, 0, 0); // Reafirmar a cor do texto

    // Valores anuais atualizados
    $pdf->Text(32, 152, "$valorAnualComparacao_s");   // Era $input5sx12s
    $pdf->Text(98, 152, "$input7_propostaAnual_s");    // Era $input7sx12s
    $pdf->Text(165, 152, "$economiaAnual_s");         // Era $diferencas

    // Terceira Página (com a imagem EE3.png)
    $pdf->AddPage();
    $pdf->Image('EE3.png', 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
    
    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I'); // 'I' para exibir no navegador
    
} else {
    // Opcional: Lidar com casos onde o método não é POST
    // header("HTTP/1.1 405 Method Not Allowed");
    // echo "Método não permitido.";
}
?>