<?php
require_once('vendor/autoload.php'); // Ou o caminho correto, se você não estiver usando o Composer

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $cidade = $_POST['cidade'];
    $uc = $_POST['uc'];

    // Criação do PDF
    $pdf = new TCPDF();
    $pdf->setCellPaddings(0, 0, 0, 0); // Remove quaisquer preenchimentos extras
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    function renderTextWithBold($pdf, $text, $cellWidth, $lineHeight, $x, $y) {
        // Divide o texto em partes normais e com negrito
        $fragments = preg_split('/(\*\*.+?\*\*)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    
        foreach ($fragments as $fragment) {
            if (preg_match('/\*\*(.+?)\*\*/', $fragment, $matches)) {
                // Aplica fonte em negrito para o texto capturado entre ** **
                $pdf->SetFont('helvetica', 'B', 12);
                $fragment = $matches[1];
            } else {
                // Aplica fonte normal
                $pdf->SetFont('helvetica', '', 12);
            }
    
            // Renderiza o fragmento na célula
            $pdf->MultiCell($cellWidth, $lineHeight, $fragment, 0, 'L', false, 1, $x, $y);
            $y += $lineHeight; // Incrementa a posição vertical
        }
    
        return $y; // Retorna a nova posição Y
    }

        // Função para dividir o texto em linhas
    function wrapText($pdf, $text, $width) {
        $lines = [];
        $currentLine = '';
        foreach (explode(' ', $text) as $word) {
            $testLine = $currentLine . ' ' . $word;
            if ($pdf->GetStringWidth(trim($testLine)) > $width) {
                $lines[] = trim($currentLine);
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }
        $lines[] = trim($currentLine); // Adiciona a última linha
        return $lines;
    }

    // Primeira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 16);
    $pdf->SetTextColor(0, 0, 0);


    // Defina os textos dos parágrafos
    $paragraphs = [
        "CONTRATO DE VENDA E INSTALAÇÃO DE EQUIPAMENTOS SOLARES FOTOVOLTAICOS",
        "Por este instrumento,",
        "PALLADIUM IMPORTADORA DE EQUIPAMENTOS LTDA, pessoa jurídica de direito privado, inscrita no CNPJ sob o n.º 49.348.620/0001-05, com sede na Av. Colombo, n.º 5088, zona 07, na cidade Maringá/PR - CEP 87.030-121, neste ato representada por seu representante legal, doravante denominada DISTRIBUIDORA.",
        "NEO MARINGÁ ENGENHARIA ELÉTRICA LTDA, pessoa jurídica devidamente inscrita no CNPJ sob o n.º 12.345.678/0001-90, com sede à Rua Exemplo, 123, na cidade de Maringá/PR - CEP 87.000-000, neste ato representada por seu representante legal, doravante denominada CONTRATANTE.",
        "WILLIAM DE AZEVEDO, brasileiro, portador do RG n.º 8397325-0 SESP/PR, inscrito no CPF n.º 009.425.209-20, residente e domiciliado na Rua Pioneiro Alcides de Araújo Vargas, n.º 219, Vila Esperança, na cidade de Maringá/PR - CEP: 87.020-620, correio eletrônico: willazevedo@gmail.com, contato: (44) 9-9951-4331, doravante denominada CONTRATANTE.",
        "Considerando que:",
        "1. A Palladium (Distribuidora) é responsável pelo fornecimento de equipamentos e materiais necessários para a instalação dos equipamentos solares fotovoltaicos.",
        "2. A Néo Maringá (Contratada) é responsável pala instalação, manutenção e suporte técnico dos equipamentos solares fotovoltaicos comercializados.",
        "3. O cliente (Contratante) que realiza a compra e a contratação dos serviços de instalação de equipamentos solares fotovoltaicos em sua propriedade. ",
        "As partes acima identificadas têm, entre si, justas e acertadas o presente Contrato de Venda e Instalação de Equipamentos Solares Fotovoltaicos, conforme cláusulas e condições adiante estipuladas:",
        "Cláusula 1ª – Do objeto",
        "1.1. O presente contrato tem como objeto a venda, execução e implantação pela CONTRATADA de 01 (um) gerador fotovoltaico ON GRID conectado à rede pela CONTRATADA, com potência operacional estimada de 9,945 kWp, na Rua Pioneiro Alcides de Araújo Vargas, n.º 219, Vila Esperança, na cidade de Maringá/PR - CEP: 87.020-620, conforme condições, quantidades e procedimentos estabelecidos neste Instrumento.",
        "1.2. A contratação inclui:",
        "a) elaboração de Projeto Solar Fotovoltaico;",
        "b) fornecimento de todos os materiais, equipamentos e mão de obra necessários para a instalação do sistema de geração de energia fotovoltaica contratado;",
        "c) aprovação e conexão à rede de distribuição de energia;",
        "d) 1 (um) ano de seguro contra furto qualificado, danos elétricos e climáticos;",
        "e) o escoamento da energia pela CONTRATADA, caso o sistema adquirido tenha sido projetado para a geração compartilhada.",
        "1.3. A produção energética do sistema fotovoltaico poderá variar para mais ou para menos, levando-se em consideração a localização (com base no índice de irradiação solar), período do ano, condições climáticas, realização de manutenções preventivas (limpeza dos painéis), posicionamento e ângulo de inclinação dos painéis e eventuais intercorrências pela Concessionária de energia.",
        "Cláusula 2ª – Das especificações técnicas dos produtos",
        "2.1. Com base nas informações fornecidas pelo CONTRATANTE, cálculos foram realizados pelo setor de engenharia da CONTRATADA, cujo sistema de geração solar fotovoltaico proposto para o endereço informado é composto dos seguintes equipamentos:",
        "2.2. Não estão inclusos custos adicionais com obras de rede pela Concessionária local.",
        "2.3. O painel fotovoltaico, inversor e demais componentes aplicáveis são aprovados pelo Programa Brasileiro de Etiquetagem (PBE), coordenado pelo Inmetro.",
        "2.4. Os equipamentos são instalados de acordo com as orientações dos fabricantes e regulamentações fornecidas pela Agência Nacional de Energia Elétrica (ANEEL).",
        "Cláusula 3ª – Dos critérios para substituição de produtos",
        "3.1. Todo material e/ou equipamento empregado na execução dos serviços será novo e de primeira qualidade.",
        "3.2. Na eventual falta do produto especificado na Proposta Comercial, a CONTRATADA poderá realizar a substituição por outro de espécie, marca ou modelo similar, desde que este possua potência igual ou superior àquela do produto inicialmente proposto, sem qualquer ônus para o CONTRATANTE.",
        "Cláusula 4ª – Do preço e forma de pagamento",
        "4.1. Pela prestação dos serviços contratados, a CONTRATANTE pagará a CONTRATADA a quantia total de R$ 22.200,00 (vinte e dois mil e duzentos reais), cujo pagamento será realizado da seguinte forma:",
        "• R$ 6.660,00 (seis mil e seiscentos e sessenta reais) será pago na data da assinatura do contrato, por meio de boleto bancário, à instituição financeira SICOOB (Banco 756), Agência 4340-0, Conta Corrente 299.832-7, pertencente à Palladium Importadora de Equipamentos Ltda, inscrita no CNPJ sob o n.º 49.348.620/0001-05, chave PIX 49.348.620/0001-05.",
        "• R$ 15.540,00 (quinze mil e quinhentos e quarenta reais) será pago por meio de boleto bancário, com vencimento em 28/02/2025, à instituição financeira SICOOB (Banco 756), Agência 4340-0, Conta Corrente 299.832-7, pertencente à Palladium Importadora de Equipamentos Ltda, inscrita no CNPJ sob o n.º 49.348.620/0001-05, chave PIX 49.348.620/0001-05.",
        "4.1.1. Caso não ocorra a coincidência entre a data de liberação do financiamento e a data da Proposta Comercial, o CONTRATANTE, em caráter irrevogável e irretratável, autoriza a CONTRATADA a proceder ao pertinente e necessário recálculo da proposta para atualização do valor total do Contrato.",
        "4.1.2. Caso o CONTRATANTE dependa exclusivamente de financiamento bancário e este não seja aprovado pelo agente finaceiro para realizar a contratação dos serviços pactuados, o contrato será rescindido de pleno direito, sem que sejam devidas quaisquer multas e/ou indenizações pela CONTRATANTE.",
        "4.2. A CONTRATANTE não vindo a efetuar o pagamento na data estipulada, fica obrigada a pagar multa de 2% (dois por cento) sobre o valor devido, bem como juros de mora de 1% (um por cento) ao mês, mais correção monetária apurada conforme variação do IGP-M no período.",
        "4.3. Em caso de desistência ou renúncia pela CONTRATANTE sem motivo justo, dentro do prazo de instalação (cláusula 4.1), será devido a CONTRATADA a título de reparação e indenização multa de 10% (dez por cento) sobre o valor do contrato.",
        "4.4. A CONTRATANTE reconhece e concorda que apenas terá a posse do Gerador Fotovoltaico após o seu pagamento integral, podendo este ser retirado pela CONTRATADA em caso de não pagamento do valor e no prazo pactuado.",
        "Cláusula 5ª – Do prazo de entrega e instalação",
        "5.1. O prazo para entrega dos equipamentos, instalação e início da operação do sistema solar fotovoltaico é de até 60 (sessenta) dias úteis pela CONTRATADA, contado a partir da aprovação do projeto pela concessionária COPEL.",
        "5.1.1. Havendo necessidade de aumento de carga, reforço na estrutura, reprovação/ devolução do projeto pela Concessionária ou outro fator que demande maior tempo para a aprovação do projeto junto à concessionária de energia, o início da contagem do prazo previsto na cláusula 4.1. dar-seá após a regularização efetiva deste último perante a companhia local.",
        "5.2. Serão descontados do prazo mencionado no item 5.1., dias chuvosos que não permitam a execução do serviço de instalação.",
        "5.3. Este contrato encerrar-se-á com o cumprimento das obrigações de entrega e instalação dos equipamentos solares fotovoltaicos pela CONTRATADA e de pagamento pelo CONTRATANTE do valor total do contrato pactuado. ",
        "5.3.1. Após o cumprimento pelas partes das obrigações previstas na cláusula 5.3., este contrato será considerado extinto de pleno direito. Entretanto, quaisquer direitos que, expressamente ou por sua natureza, devam permanecer em vigor após o seu encerramento, não serão afetados ou limitados após a sua extinção",
        "Cláusula 6ª – Do horário e condições para prestação dos serviços",
        "6.1. O serviço de instalação ocorrerá de segunda a sexta-feira, no horário das 8:00 horas às 18:00 horas, podendo ser convencionado dia e horário distinto por mútuo consentimento entre as partes",
        "6.2. Antes da instalação dos painéis solares fotovoltaicos, o CONTRATANTE deverá realizar as adequações necessárias no seu terreno, como limpeza, remoção de vegetação e árvores que possam interferir no sombreamento sobre os painéis e/ou outros serviços indispensáveis para a obtenção de máxima eficiência energética e segurança das instalações.",
        "Cláusula 7ª – Do recebimento e encerramento dos serviços",
        "7.1. O encerramento dos serviços de instalação do sistema fotovoltaico será precedido de uma vistoria por parte da CONTRATADA, para que esta verifique e comprove a satisfatória execução dos serviços realizados.",
        "Cláusula 8ª – Da garantia dos equipamentos",
        "8.1. Os fabricantes garantem a perfeita execução dos equipamentos e periféricos comercializados ao CONTRATANTE, de acordo com os seguintes prazos:",
        "a) Módulo fotovoltaico - possui garantia de fábrica de 12 (doze) anos contra defeito de fabricação e de 25 (vinte e cinco) anos para performance de geração;",
        "b) Inversor – possui 10 (dez) anos de garantia contra defeito de fabricação;",
        "c) Outros componentes do gerador fotovoltaico (materiais periféricos) - possuem 01 (ano) de garantia contra defeito de fabricação.",
        "8.2. A contagem do período de garantia dos equipamentos pelos fabricantes inicia-se a partir da efetiva ativação do sistema solar fotovoltaico conectado à rede elétrica da concessionária. Decorrido os prazos mencionados na cláusula 8.1., o CONTRATANTE torna-se o único responsável por eventuais defeitos nos equipamentos.",
        "Cláusula 9ª – Das obrigações da Contratada",
        "",
        "",
        "",
        "",
        "",


    ];

    // Configuração de layout
    $cellWidth = 180;    // Largura do texto
    $lineHeight = 6;     // Altura entre linhas
    $paragraphSpacing = 10; // Espaçamento entre parágrafos
    $x = 15;             // Margem esquerda
    $y = 25;            // Margem inicial superior


    // Função para renderizar um parágrafo
    function renderParagraph($pdf, $text, $width, $lineHeight, $x, $y) {
        // Divide o texto em linhas
        $lines = wrapText($pdf, $text, $width);

        foreach ($lines as $i => $line) {
            // Define o alinhamento: última linha à esquerda, demais justificadas
            $align = ($i === count($lines) - 1) ? 'L' : 'J';

            // Renderiza a linha
            $pdf->MultiCell($width, $lineHeight, $line, 0, $align, false, 1, $x, $y);
            $y += $lineHeight; // Próxima linha
        }

        return $y; // Retorna a nova posição Y
    }

    // Renderiza os parágrafos
    foreach ($paragraphs as $paragraph) {
        $y = renderParagraph($pdf, $paragraph, $cellWidth, $lineHeight, $x, $y);
        $y += $paragraphSpacing; // Adiciona espaçamento entre parágrafos
    }


    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text(46, 223, "$dataAtual");


    // Segunda Página (com a imagem genérica e gráfico)
    $pdf->AddPage();  // Adiciona a segunda página

    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página


    // Terceira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

    
    // Definir fonte e adicionar conteúdo à terceira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Quarta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(255, 0, 0);

    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(158, 141.5, "$percentualSolarArredondado %");

    // Definir fonte e adicionar conteúdo à quarta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Quinta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);



    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(152, 164, "$precoFinalRs");

    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->Text(26, 180, "36 X $valorParcelaRs");
    $pdf->Text(85, 180, "48 X $valorParcela2Rs");
    $pdf->Text(146, 180, "60 X $valorParcela3Rs");

    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Text(59, 46, "$qtdmodulosArredondado");
    $pdf->Text(85, 46, "$potenciaInversor kW");
    $pdf->Text(110, 46, "$potenciaGerador kWp");
    $pdf->Text(139, 46, "$geracaoArredondado kWh");
    $pdf->Text(167, 46, "$geracaoAnual kWh");

    // Dados do Payback
    $anos = 25; // Total de anos

    // Calcular o retorno verde anual
    $retornoAnualVerde = $retornoVerde * 12;

    // Inicializar o acumulado de retorno
    $dados = [];
    $retornoAcumulado = 0;

    // Calcular o retorno acumulado ao longo dos anos
    for ($ano = 1; $ano <= $anos; $ano++) {
        $retornoAcumulado += $retornoAnualVerde;
        $dados[$ano] = $retornoAcumulado - $precoFinal; // Payback acumulado ao final de cada ano
    }

    // Título do gráfico
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text($xInicial, $yInicial - 10, '');

    // Definir fonte e adicionar conteúdo à quinta página
    $pdf->SetFont('helvetica','B', 12);
    $pdf->SetTextColor(0, 0, 0);

    // Sexta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

    $retornoVerdeRs = 'R$ ' . number_format($retornoVerde, 2, ',', '.');
    $retornoAmareloRs = 'R$ ' . number_format($retornoAmarelo, 2, ',', '.');
    $retornoVermelhoRs = 'R$ ' . number_format($retornoVermelho, 2, ',', '.');
    $retornoVermelhoP1Rs = 'R$ ' . number_format($retornoVermelhoP1, 2, ',', '.');
    $rentabilidadeVerdeRs = number_format($rentabilidadeVerde, 2, ',', '.') . '%';
    $rentabilidadeAmarelaRs = number_format($rentabilidadeAmarela, 2, ',', '.') . '%';
    $rentabilidadeVermelhaRs = number_format($rentabilidadeVermelha, 2, ',', '.') . '%';
    $rentabilidadeVermelhaP1Rs = number_format($rentabilidadeVermelhaP1, 2, ',', '.') . '%';

    $seguroRs = 'R$ ' . number_format($seguro, 2, ',', '.');
    $manutencaoRs = 'R$ ' . number_format($manutencao, 2, ',', '.');
    $impostoRs = 'R$ ' . number_format($imposto, 2, ',', '.');
    $demandaRs = 'R$ ' . number_format($demanda, 2, ',', '.');
    $liquidoVerdeRs = 'R$ ' . number_format($liquidoVerde, 2, ',', '.');
    $liquidoAmareloRs = 'R$ ' . number_format($liquidoAmarelo, 2, ',', '.');
    $liquidoVermelhoRs = 'R$ ' . number_format($liquidoVermelho, 2, ',', '.');
    $liquidoVermelhoP1Rs = 'R$ ' . number_format($liquidoVermelhoP1, 2, ',', '.');
    $mediaLiquido = ($liquidoVerde + $liquidoAmarelo + $liquidoVermelho + $liquidoVermelhoP1) / 4;
    $mediaLiquidoRs =  'R$ ' . number_format($mediaLiquido, 2, ',', '.');
    
    $pdf->Text(61, 122, "$retornoVerdeRs");
    $pdf->Text(98, 122, "$retornoAmareloRs");
    $pdf->Text(135, 122, "$retornoVermelhoRs");
    $pdf->Text(172, 122, "$retornoVermelhoP1Rs");

    $pdf->Text(64, 134.5, "$seguroRs");
    $pdf->Text(101, 134.5, "$seguroRs");
    $pdf->Text(138, 134.5, "$seguroRs");
    $pdf->Text(175, 134.5, "$seguroRs");

    $pdf->Text(63, 145, "$manutencaoRs");
    $pdf->Text(100, 145, "$manutencaoRs");
    $pdf->Text(137, 145, "$manutencaoRs");
    $pdf->Text(174, 145, "$manutencaoRs");

    $pdf->Text(64, 156.5, "$impostoRs");
    $pdf->Text(101, 156.5, "$impostoRs");
    $pdf->Text(138, 156.5, "$impostoRs");
    $pdf->Text(175, 156.5, "$impostoRs");

    $pdf->Text(63, 167.5, "$demandaRs");
    $pdf->Text(100, 167.5, "$demandaRs");
    $pdf->Text(137, 167.5, "$demandaRs");
    $pdf->Text(174, 167.5, "$demandaRs");

    $pdf->Text(66, 180, "$rentabilidadeVerdeRs");
    $pdf->Text(103, 180, "$rentabilidadeAmarelaRs");
    $pdf->Text(141, 180, "$rentabilidadeVermelhaRs");
    $pdf->Text(177, 180, "$rentabilidadeVermelhaP1Rs");

    $pdf->SetTextColor(255, 255, 255);
    $pdf->Text(61, 192.2, "$liquidoVerdeRs");
    $pdf->Text(98, 192.2, "$liquidoAmareloRs");
    $pdf->Text(135, 192.2, "$liquidoVermelhoRs");
    $pdf->Text(172, 192.2, "$liquidoVermelhoP1Rs");

    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(172, 203.5, "$mediaLiquidoRs");

    $pdf->Text(27, 220, "$VPLP");
    $pdf->Text(80, 220, "$TIRP");
    $pdf->Text(127, 220, "$lucratividadeFormatada");
    $pdf->Text(172, 220, "$ROIPorcentagem");
    $pdf->Text(80, 98, "Tributação vigente: $tributario");


    // Sétima Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

    
    // Definir fonte e adicionar conteúdo à sétima página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Nona Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página

    
    // Definir fonte e adicionar conteúdo à nona página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Text(15, 5, "CONTRATO DE VENDA E INSTALAÇÃO DE EQUIPAMENTOS SOLARES FOTOVOLTAICOS");
    $pdf->Text(15, 15, "Por este Instrumento,");
    
    // Texto a ser exibido
    $text = "Por este instrumento, PALLADIUM IMPORTADORA DE EQUIPAMENTOS LTDA, pessoa jurídica de direito privado, inscrita no CNPJ sob o n.º 49.348.620/0001-05, com sede na Av. Colombo, n.º 5088, zona 07, na cidade de Maringá/PR - CEP 87.030-121, neste ato representada por seu representante legal, doravante denominada DISTRIBUIDORA.";

    // Define o layout
    $cellWidth = 180;
    $lineHeight = 6;
    $x = 15;
    $y = 20;

    // Renderiza o texto
    foreach (wrapText($pdf, $text, $cellWidth) as $i => $line) {
        $align = ($i === count(wrapText($pdf, $text, $cellWidth)) - 1) ? 'L' : 'J'; // Última linha à esquerda
        $pdf->MultiCell($cellWidth, $lineHeight, $line, 0, $align, false, 1, $x, $y);
        $y += $lineHeight; // Próxima linha
    }

        // Texto a ser exibido
    $text = "NEO MARINGÁ ENGENHARIA ELÉTRICA LTDA, pessoa jurídica devidamente inscrita no CNPJ sob o n° 32.608.889/0001-80, com sede e foro na Av. Colombo n.º 5088, zona 07, na cidade de Maringá/PR – CEP 87.030-121, neste ato representada por seu representante legal, doravante denominada CONTRATADA.";



    // Define o layout
    $cellWidth = 180;
    $lineHeight = 6;
    $x = 15;
    $y = 50;

    // Renderiza o texto
    foreach (wrapText($pdf, $text, $cellWidth) as $i => $line) {
        $align = ($i === count(wrapText($pdf, $text, $cellWidth)) - 1) ? 'L' : 'J'; // Última linha à esquerda
        $pdf->MultiCell($cellWidth, $lineHeight, $line, 0, $align, false, 1, $x, $y);
        $y += $lineHeight; // Próxima linha
    }
    

    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador
    
    
}
?>