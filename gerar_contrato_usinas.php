<?php
require_once('vendor/autoload.php'); // Ou o caminho correto, se você não estiver usando o Composer
class CustomPDF extends TCPDF
{
    private $backgroundImage;

    public function __construct()
    {
        parent::__construct();
        // Carrega o caminho da imagem de fundo no construtor
        $this->backgroundImage = dirname(__FILE__) . '/timbradocompra.png';
    }

    // Sobrescreve o método Header para adicionar uma imagem de fundo
    public function Header()
    {
        // Obtém as dimensões da página
        $pageWidth = $this->getPageWidth();
        $pageHeight = $this->getPageHeight();

        // Verifica em qual página está
        $currentPage = $this->getPage();

        // Se for a primeira página, define margem diferente
        if ($currentPage == 1) {
            $topMargin = 70; // Margem da primeira página
        } else {
            $topMargin = 40; // Margem das páginas subsequentes
        }

        // Para todas as páginas
        $this->SetMargins(0, 0, 0);
        $this->SetAutoPageBreak(false, 0);

        // Adiciona a imagem de fundo estendida para cobrir toda a página
        $this->Image(
            $this->backgroundImage,
            0,    // Posição X
            0,    // Posição Y
            $pageWidth,   // Largura 
            $pageHeight,  // Altura
            '',   // Tipo
            '',   // Link
            '',   // Alinhamento
            false,// Redimensionar
            300,  // DPI
            '',   // Alinhamento
            false,// Máscaras
            false // Transparência
        );

        // Restaura a quebra automática de página com margem inferior
        $this->SetAutoPageBreak(true, 20);

        // Define a margem de conteúdo
        $this->SetMargins(15, $topMargin, 15);
    }
}


// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $estado = $_POST['estado'];
    $cidade = $_POST['cidade'];
    $uc = $_POST['uc'];
    $quantidades = $_POST['quantidade']; // Contém as quantidades
    $itens = $_POST['item']; // Contém os itens
    $telefone = $_POST['telefone']; // Contém os itens
    $potencia = $_POST['potencia']; // Contém os itens
    $precoFinal = $_POST['precoFinal']; // Contém os itens
    $valores_pagamento = $_POST['valor_pagamento'];
    $datas_pagamento = $_POST['data_pagamento'];
    $email = $_POST['email'];
    $CPF = $_POST['CPF'];
    $nascimento = $_POST['nascimento'];
    $cep = $_POST['cep'];
    $modalidade = $_POST['modalidade'];
    $formaPagamento = $_POST['formaPagamento'];
    $potenciaInversor = $_POST['potenciaInversor'];

    $potenciaInversor = floatval($potenciaInversor); // Converte para float

 

    if ($potenciaInversor > 75) {
        $prazo = 120;
    } else {
        $prazo = 60;
    }

    
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
    $dataAtual = date("d/m/Y");

    foreach ($valores_pagamento as $index => $valor) {
        $dataPagamento = $datas_pagamento[$index];


        // Usar essas variáveis para compor o conteúdo do PDF conforme necessário
    }
    $dataPagamento1 = $datas_pagamento[0];
    $valor_pagamento = $valores_pagamento[0];

    $dataPagamento2 = $datas_pagamento[1];


    $dataPagamento3 = $datas_pagamento[2];


    $dataPagamento4 = $datas_pagamento[3];


    $dataPagamento5 = $datas_pagamento[4];



    if (isset($_POST['quantidade']) && isset($_POST['item'])) {
        $quantidades = $_POST['quantidade'];
        $itens = $_POST['item'];
    }
    $primeiraQuantidade = $quantidades[0];
    $primeiroItem = $itens[0];
    $segundaQuantidade = $quantidades[1];
    $segundoItem = $itens[1];
    $terceiraQuantidade = $quantidades[2];
    $terceiroItem = $itens[2];
    $quartaQuantidade = $quantidades[3];
    $quartoItem = $itens[3];
    $quintaQuantidade = $quantidades[4];
    $quintoItem = $itens[4];
    $sextaQuantidade = $quantidades[5];
    $sextoItem = $itens[5];
    $setimaQuantidade = $quantidades[6];
    $setimoItem = $itens[6];
    $oitavaQuantidade = $quantidades[7];
    $oitavoItem = $itens[7];
    $nonaQuantidade = $quantidades[8];
    $nonoItem = $itens[8];
    $decimaQuantidade = $quantidades[9];
    $decimoItem = $itens[9];

    $precoFinalRs = 'R$ ' . number_format($precoFinal, 2, ',', '.');



    // Criação do PDF
    $pdf = new CustomPDF();
    $pdf->setCellPaddings(0, 0, 0, 0); // Remove quaisquer preenchimentos extras
    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 70, 15); // Margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(TRUE, 20); // Quebra automática com 20 unidades na margem inferior
    $pdf->setLanguageArray(['a_meta_charset' => 'UTF-8']);



    // Primeira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Text(80, 40, "$cidade, $dataAtual"); // Cidade e data

    
    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 16);
    $pdf->SetTextColor(0, 0, 0);


    function renderTextWithBold($pdf, $paragraphs, $cellWidth)
    {
        $fragments = preg_split('/(\*\*.+?\*\*)/', $paragraphs, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($fragments as $index => $fragment) {
            if (preg_match('/\*\*(.+?)\*\*/', $fragment, $matches)) {
                $pdf->SetFont('helvetica', 'B', 12);
                $fragment = $matches[1];
            } else {
                $pdf->SetFont('helvetica', '', 12);
            }

            // Alinhamento: última linha à esquerda
            $align = ($index === count($fragments) - 1) ? 'L' : 'J';

            $pdf->MultiCell(
                $cellWidth,
                0,
                $fragment,
                0,
                $align,
                false,
                1
            );
        }
    }
    $telefone = $_POST['telefone']; // Contém os itens
    $potencia = $_POST['potencia']; // Contém os itens
    $precoFinal = $_POST['precoFinal']; // Contém os itens
    $entrada = $_POST['entrada']; // Contém os itens

    $pdf->SetFont('dejavusans', '', 10);

    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
    $dataAtual = date("d/m/Y");


    // Lógica dos textos dinâmicos de acordo com o estado
    switch ($estado) {
        case "MT":
            $textoEstado = '
            <p><strong>PALLADIUM IMPORTADORA DE EQUIPAMENTOS LTDA</strong>, pessoa jurídica de direito privado, inscrita
                no CNPJ sob o n.º 49.348.620/0001-05, com sede na Av. Colombo, n.º 5088, zona 07, na cidade de 
                Maringá/PR - CEP 87.030-121, representada neste ato por seu representante legal, doravante denominada 
                “DISTRIBUIDORA”.
            </p>
            <p><strong>CANAL VERDE GESTAO DE EMPREENDIMENTOS MT LTDA</strong>, pessoa jurídica de direito privada, 
                inscrita no CNPJ sob o n.° 54.399.517/0001-24, com sede na Rua das Tamareiras, 23, Sala 04, Jardim Botanico,
                na cidade de Sinop, MT - CEP 78.556-002, representada neste ato por seu representante legal,
                doravante denominada “CONTRATADA”.
            </p>';
            $cnpjEstado = '<strong>CANAL VERDE GESTAO DE EMPREENDIMENTOS MT LTDA</strong>';
            $cidadeComarca = 'Sinop - MT';
            break;
    
        case "SP":
            $textoEstado = '
            <p><strong>PALLADIUM IMPORTADORA DE EQUIPAMENTOS LTDA</strong>, pessoa jurídica de direito privado, inscrita
                no CNPJ sob o n.º 49.348.620/0001-05, com sede na Av. Colombo, n.º 5088, zona 07, na cidade de 
                Maringá/PR - CEP 87.030-121, representada neste ato por seu representante legal, doravante denominada 
                “DISTRIBUIDORA”.
            </p>
            <p><strong>CANAL VERDE SP</strong>, pessoa jurídica de direito privada, inscrita no CNPJ sob o n.° 
                48.892.992/0001-35, com sede na Avenida Doutor Paulo de Moraes, 555, Paulista, na cidade de
                Piracicaba, SP - CEP 13.400-853, representada neste ato por seu representante legal,
                doravante denominada “CONTRATADA”.
            </p>';
            $cnpjEstado = '<strong>CANAL VERDE SP</strong>';
            $cidadeComarca = 'Piracicaba - SP';
            break;
    
        case "PR":
            if ($modalidade == "autoconsumo") {
                $textoEstado = '
                <p><strong>PALLADIUM IMPORTADORA DE EQUIPAMENTOS LTDA</strong>, pessoa jurídica de direito privado, inscrita
                    no CNPJ sob o n.º 49.348.620/0001-05, com sede na Av. Colombo, n.º 5088, zona 07, na cidade de 
                    Maringá/PR - CEP 87.030-121, representada neste ato por seu representante legal, doravante denominada 
                    “DISTRIBUIDORA”.
                </p>
                <p><strong>NEO MARINGÁ ENGENHARIA ELÉTRICA LTDA</strong>, pessoa jurídica de direito privada, inscrita 
                    no CNPJ sob o n.° 32.608.889/0001-80, com sede na Av. Colombo, n.º 5088, zona 07, na cidade de Maringá/PR - 
                    CEP 87.030-121, representada neste ato por seu representante legal, doravante denominada “CONTRATADA”.
                </p>';
                $cnpjEstado = '<strong>NEO MARINGÁ ENGENHARIA ELÉTRICA LTDA</strong>';
            } else if ($modalidade == "investimento") {
                $textoEstado = '
                <p><strong>PALLADIUM IMPORTADORA DE EQUIPAMENTOS LTDA</strong>, pessoa jurídica de direito privado, inscrita
                    no CNPJ sob o n.º 49.348.620/0001-05, com sede na Av. Colombo, n.º 5088, zona 07, na cidade de 
                    Maringá/PR - CEP 87.030-121, representada neste ato por seu representante legal, doravante denominada 
                    “DISTRIBUIDORA”.
                </p>
                <p><strong>CANAL VERDE GESTAO DE EMPREENDIMENTOS LTDA CNPJ</strong>, pessoa jurídica de direito privada, inscrita 
                    no CNPJ sob o n.° 35.067.916/0001-43, com sede na Av. Colombo, n.º 5088, zona 07, na cidade de Maringá/PR -
                    CEP 87.030-121, representada neste ato por seu representante legal, doravante denominada “CONTRATADA”.
                </p>';
                $cnpjEstado = '<strong>CANAL VERDE GESTAO DE EMPREENDIMENTOS LTDA CNPJ</strong>';
            } else {
                // Condição padrão SEM modalidade específica para PR
                $textoEstado = '
                <p><strong>PALLADIUM IMPORTADORA DE EQUIPAMENTOS LTDA</strong>, pessoa jurídica de direito privado, inscrita
                    no CNPJ sob o n.º 49.348.620/0001-05, com sede na Av. Colombo, n.º 5088, zona 07, na cidade de 
                    Maringá/PR - CEP 87.030-121, representada neste ato por seu representante legal, doravante denominada 
                    “DISTRIBUIDORA”.
                </p>
                <p><strong>NEO MARINGÁ ENGENHARIA ELÉTRICA LTDA</strong>, pessoa jurídica de direito privada, inscrita 
                    no CNPJ sob o n.° 35.067.916/0001-43, com sede na Av. Colombo, n.º 5088, zona 07, na cidade de Maringá/PR - 
                    CEP 87.030-121, representada neste ato por seu representante legal, doravante denominada “CONTRATADA”.
                </p>';
                $cnpjEstado = '<strong>NEO MARINGÁ ENGENHARIA ELÉTRICA LTDA</strong>';
            }
            $cidadeComarca = 'Maringá - PR';
            break;
    
        default:
            $textoEstado = '<p><strong>[Informações de Contratada específicas para o estado não disponíveis]</strong></p>';
            $cidadeComarca = 'Indefinida';
            break;
    }

    // Conteúdo HTML
    $htmlContent = '

        <p></p>
        <p></p>
        <p></p>
    <div style="text-align: center;">
        <p></p>
        <p></p>
        <p></p>
        <p><strong>CONTRATO DE VENDA E INSTALAÇÃO DE EQUIPAMENTOS SOLARES FOTOVOLTAICOS</strong></p>
    </div>
   <p>Por este instrumento,</p>
   <p>'.$textoEstado .'</p>
   <p><strong>' . $nome . '</strong>, inscrito no CPF n.º '. $CPF .', residente e domiciliado em '. $endereco .', na cidade de '. $cidade .' - CEP: '. $cep .', correio eletrônico: '. $email .', contato: '. $telefone .', doravante denominada CONTRATANTE.</p>
   <p>Considerando que:</p>
   <p>1. A Palladium (Distribuidora) é a empresa responsável pelo fornecimento de equipamentos e materiais necessários para a instalação de sistema solar fotovoltaico.</p>
   <p>2. A Canal Verde (Contratada) é a empresa responsável pala instalação, manutenção e suporte técnico do sistema solar fotovoltaico comercializado.</p>
   <p>3. O cliente (Contratante) que realiza a compra e a contratação dos serviços de instalação de sistema solar fotovoltaico em sua propriedade.</p>
   <p>As partes acima identificadas têm, entre si, justas e acertadas o presente Contrato de Venda e Instalação de Equipamentos Solares Fotovoltaicos, conforme cláusulas e condições adiante estipuladas:As partes acima identificadas têm, entre si, justas e acertadas o presente Contrato de Venda e Instalação de Equipamentos Solares Fotovoltaicos, conforme cláusulas e condições adiante estipuladas:</p>
   <p><strong>Cláusula 1ª – Do objeto do contrato</strong></p>
   <p>1.1.	O presente contrato tem como objeto a venda e implantação de 01 (um) gerador fotovoltaico ON GRID conectado à rede pela CONTRATADA, com potência operacional estimada de ' . $potencia . ' kWp, em '. $endereco.', ' .$cidade .' conforme condições, quantidades e procedimentos estabelecidos neste Instrumento.</p>
   <p>1.2. A contratação inclui:</p>
   <ul>
       <li> Elaboração de Projeto Solar Fotovoltaico;</li>
       <li> Fornecimento de todos os materiais, equipamentos e mão de obra necessários para a instalação do sistema de geração de energia fotovoltaica contratado;</li>
       <li> Aprovação e conexão à rede de distribuição de energia;</li>
       <li> 1 (um) ano de seguro contra furto qualificado, danos elétricos e climáticos;</li>
       <li> O escoamento da energia pela CONTRATADA, caso o sistema adquirido tenha sido projetado para a geração compartilhada.</li>
   </ul>
   <p>1.3. A produção energética do sistema fotovoltaico poderá variar para mais ou para menos, levando-se em consideração a localização (com base no índice de irradiação solar), período do ano, condições climáticas, realização de manutenções preventivas (limpeza dos painéis), posicionamento e ângulo de inclinação dos painéis e eventuais intercorrências pela Concessionária de energia.</p>
   <p><strong>Cláusula 2ª – Da condição de aprovação do projeto pela Concessionária</strong></p>
   <p>2.1. Este contrato terá validade e produzirá efeitos legais a partir da aprovação do projeto pela Concessionária de energia local. A aprovação do referido projeto é condição terminantemente obrigatória para o cumprimento das obrigações contratuais estipuladas neste contrato.</p>
   <p>2.2. Caso o projeto seja considerado inviável pela Concessionária, após esgotadas todas as instâncias administrativas e/ou judiciais, este contrato será automaticamente rescindido. A CONTRATADA deverá reembolsar integralmente os valores adiantados pelo CONTRATANTE, no prazo máximo de 03 (três) dias úteis, a contar da data da decisão final da Concessionária.</p>
   <p><strong>Cláusula 3ª – Das especificações técnicas dos produtos</strong></p>
   <p>3.1. Com base nas informações fornecidas pelo CONTRATANTE, cálculos foram realizados pelo setor de engenharia da CONTRATADA, cujos equipamentos solares fotovoltaicos propostos para o endereço informado é composto dos seguintes elementos:</p>
           <table border="1" cellpadding="5" cellspacing="0" width="100%">
       <tr>
           <th width="20%"><b>QUANTIDADE</b></th>
           <th width="80%"><b>ITEM</b></th>
       </tr>
       <tr>
           <td>Incluso</td>
           <td>MATERIAL ELÉTRICO;</td>
       </tr>
       <tr>
           <td>Incluso</td>
           <td>PROJETO SOLAR FOTOVOLTAICO;</td>
       </tr>
       <tr>
           <td>Incluso</td>
           <td>ART DE PROJETO E EXECUÇÃO;</td>
       </tr>
       <tr>
           <td>Incluso</td>
           <td>ACOMPANHAMENTO JUNTO A CONCESSIONÁRIA LOCAL;</td>
       </tr>';

    // Adiciona itens da lista dinâmica
    for ($i = 0; $i < count($quantidades); $i++) {
        if (!empty($quantidades[$i]) && !empty($itens[$i])) {
            $htmlContent .= '<tr><td>' . htmlspecialchars($quantidades[$i]) . '</td><td>' . htmlspecialchars($itens[$i]) . ';</td></tr>';
        }
    }

    $htmlContent .= '</table>
   <p>3.2. Não estão inclusos custos adicionais com obras de rede pela Concessionária local e terraplanagem.</p>
   <p>3.3. O painel fotovoltaico, inversor e demais componentes aplicáveis são aprovados pelo Programa Brasileiro de Etiquetagem (PBE), coordenado pelo Inmetro.</p>
   <p>3.4. Os equipamentos são instalados de acordo com as orientações dos fabricantes e regulamentações fornecidas pela Agência Nacional de Energia Elétrica (ANEEL).</p>
   <p><strong>Cláusula 4ª – Dos critérios para substituição de produtos</strong></p>
   <p>4.1. Todo material e/ou equipamento empregado na execução dos serviços será novo e de primeira qualidade.</p>
   <p>4.2. Na eventual falta do produto especificado na Proposta Comercial, a CONTRATADA poderá realizar a substituição por outro de espécie, marca ou modelo similar, desde que este possua potência igual ou superior àquela do produto inicialmente proposto, sem qualquer ônus para o CONTRATANTE.</p>
   <p><strong>Cláusula 5ª – Do preço e forma de pagamento</strong></p>
   <p>5.1. Pela prestação dos serviços contratados, a CONTRATANTE pagará a CONTRATADA a quantia total de ' . $precoFinalRs . ', cujo pagamento será realizado da seguinte forma:</p>';
   
    foreach ($valores_pagamento as $index => $valor) {
        $dataPagamento = $datas_pagamento[$index];


        $valor_formatado = 'R$ ' . number_format((float)$valor, 2, ',', '.');


        if (!empty($dataPagamento)){
            $htmlContent .= '<p>';
            $htmlContent .= '<b>Valor ' . ($index + 1) . ': ' . htmlspecialchars($valor_formatado) . ', </b>';
            $htmlContent .= '<b> que será pago na data : ' . htmlspecialchars($dataPagamento) . ', </b>';
            $htmlContent .= ' através de Boleto que será efetuado na instituição banco <b> 756 SICOOB, </b>';
            $htmlContent .= '<b>Agência: 4340-0, </b>';
            $htmlContent .= '<b>Conta Corrente: 299.832-7</b>, em nome de <b>PALLADIUM IMPORTADORA DE EQUIPAMENTOS LTDA, 
            CNPJ nº 49.348.620/0001-05 e PIX chave nº 49.348.620/0001-05</b>. Com os pagamentos dos materiais fotovoltaicos e mão-de-obra da forma combinada
             entre as partes, onde será efetuado a emissão da nota fiscal total dos produtos.';
            $htmlContent .= '</p>';
        }
    }


   $htmlContent .= '
   <p>5.1.1.  Caso não ocorra a coincidência entre a data de liberação do financiamento e a data da Proposta Comercial, o CONTRATANTE, em caráter irrevogável e irretratável, autoriza a CONTRATADA a proceder ao pertinente e necessário recálculo da proposta para atualização do valor total do Contrato.</p>
   <p>5.1.2. Caso o CONTRATANTE dependa exclusivamente de financiamento bancário e este não seja aprovado pelo agente finaceiro para realizar a contratação dos serviços pactuados, o contrato será rescindido de pleno direito, sem que sejam devidas quaisquer multas e/ou indenizações pela CONTRATANTE.</p>
   <p>5.2. A CONTRATANTE não vindo a efetuar o pagamento na data estipulada, fica obrigada a pagar multa de 2% (dois por cento) sobre o valor devido, bem como juros de mora de 1% (um por cento) ao mês, mais correção monetária apurada conforme variação do IGP-M no período.</p>
   <p>5.3. Em caso de desistência ou renúncia pela CONTRATANTE sem motivo justo, dentro do prazo de instalação (cláusula 4.1), será devido a CONTRATADA a título de reparação e indenização multa de 10% (dez por cento) sobre o valor do contrato.</p>
   <p>5.4.  A CONTRATANTE reconhece e concorda que apenas terá a posse do gerador fotovoltaico após o seu pagamento integral, podendo este ser retirado pela CONTRATADA em caso de não pagamento do valor e no prazo pactuado.</p>
   <p><strong>Cláusula 6ª – Do prazo de entrega e instalação</strong></p>
   <p>6.1. O prazo para entrega dos equipamentos, instalação e início da operação do sistema solar fotovoltaico é de até '. $prazo .' (cento e vinte) dias úteis pela CONTRATADA, contado a partir da aprovação do projeto pela Concessionária de energia local.</p>
   <p>6.1.1. O prazo mencionado na cláusula 6.1. não abrange os prazos de responsabilidade da Concessionária de Energia. Havendo necessidade de aumento de carga, reforço na estrutura, reprovação/devolução do projeto pela Concessionária ou outro fator que demande maior tempo para a aprovação do projeto junto à concessionária de energia, o início da contagem do prazo previsto na cláusula 6.1. dar-se-á após a regularização efetiva deste último perante a companhia local.</p>
   <p>6.2. Serão descontados do prazo mencionado no item 6.1., dias chuvosos que não permitam a execução do serviço de instalação.</p>
   <p>6.3. Este contrato encerrar-se-á com o cumprimento das obrigações de entrega e instalação dos equipamentos solares fotovoltaicos pela CONTRATADA e de pagamento pelo CONTRATANTE do valor total do contrato pactuado. </p>
   <p>6.3.1. Após o cumprimento pelas partes das obrigações previstas na cláusula 5.3., este contrato será considerado extinto de pleno direito. Entretanto, quaisquer direitos que, expressamente ou por sua natureza, devam permanecer em vigor após o seu encerramento, não serão afetados ou limitados após a sua extinção.</p>
   <p><strong>Cláusula 7ª – Do horário e condições para prestação dos serviços</strong></p>
   <p>7.1.	O serviço de instalação ocorrerá de segunda a sexta-feira, no horário das 8:00 horas às 18:00 horas, podendo ser convencionado dia e horário distinto por mútuo consentimento entre as partes.</p>
   <p>7.2.	O CONTRATANTE deverá realizar as adequações necessárias no terreno que receberá a instalação dos painéis fotovoltaicos, tais como: limpeza do local, remoção de vegetação que possa interferir no sombreamento sobre os painéis e/ou outros serviços indispensáveis para a obtenção de máxima eficiência energética e segurança das instalações.</p>
   <p><strong>Cláusula 8ª – Do recebimento e encerramento dos serviços</strong></p>
   <p>8.1. O encerramento dos serviços de instalação do sistema fotovoltaico será precedido de uma vistoria por parte da CONTRATADA, para que esta verifique e comprove a satisfatória execução dos serviços realizados.</p>
   <p><strong>Cláusula 9ª – Da garantia dos equipamentos</strong></p>
   <p>9.1. Os fabricantes garantem a perfeita execução dos equipamentos e periféricos comercializados ao CONTRATANTE, de acordo com os seguintes prazos:</p>
   <p>a)	Módulo fotovoltaico - possui garantia de fábrica de 12 (doze) anos contra defeito de fabricação e de 25 (vinte e cinco) anos para performance de geração;</p>
   <p>b)	Inversor – possui 10 (dez) anos de garantia contra defeito de fabricação;</p>
   <p>c)	Outros componentes do gerador fotovoltaico (materiais periféricos) - possuem 01 (ano) de garantia contra defeito de fabricação.</p>
   <p>9.2. A contagem do período de garantia dos equipamentos pelos fabricantes inicia-se a partir da efetiva ativação do sistema solar fotovoltaico conectado à rede elétrica da concessionária. Decorrido os prazos mencionados na cláusula 9.1., o CONTRATANTE torna-se o único responsável por eventuais defeitos nos equipamentos.</p>
   <p><strong>Cláusula 10ª – Das obrigações da Contratada</strong></p>
   <p>10.1. Sem prejuízo de outras disposições deste contrato, constituem obrigações da CONTRATADA:</p>ilateral ou não aplicação de multas, os inadimplementos decorrentes das situações a seguir, quando vierem a afetar a realização da entrega do objeto do contrato no local indicado:</p>
   <ul>
       <li>a)	Cumprir integralmente este contrato na forma e modo ajustados;</li>
       <li>b)	Conduzir os trabalhos com estrita observância às normas da legislação pertinente, cumprindo as determinações dos Poderes Públicos, mantendo sempre limpo o local dos serviços e nas melhores condições de segurança, higiene e disciplina;</li>
       <li>c)	Fornecer e utilizar os materiais, equipamentos, ferramentas e utensílios necessários, na qualidade e quantidade especificadas na Proposta Comercial;</li>
       <li>d)	Empregar na execução dos serviços apenas profissionais capacitados e qualificados para essas funções;</li>
       <li>e)	Apresentar ao CONTRATANTE, quando for o caso, a relação nominal dos profissionais que adentrarão em suas instalações;</li>
       <li>f)	Obedecer às normas técnicas, de saúde, de higiene e de segurança do trabalho, de acordo com as normas do Ministério do Trabalho e Emprego (MTE);</li>
       <li>g)	Assumir a responsabilidade pelos atos e/ou omissões praticados por si e por seus subordinados, bem como pelos danos que venham a causar para a CONTRATANTE, desde que comprovados, em decorrência da prestação dos serviços prestados;</li>
       <li>h)  Arcar devidamente, nos termos da legislação trabalhista, com a remuneração e demais verbas laborais devidas a seus subordinados, inclusive, encargos fiscais e previdenciários referentes às relações de trabalho;</li>
       <li>i)	Arcar com todas as despesas de natureza tributária decorrentes dos serviços especificados neste contrato;</li>
       <li>j)	Relatar à CONTRATANTE toda e qualquer irregularidade verificada no decorrer da prestação dos serviços;</li>
       <li>k)	Guardar sigilo sobre todas as informações obtidas em decorrência do cumprimento do contrato;</li>
       <li>l)	 Providenciar junto ao CREA as Anotações de Responsabilidade Técnica (ART), referentes ao objeto do contrato e especialidades, nos termos das normas pertinentes.</li>
   </ul>
   <p><strong>Cláusula 11ª – Das obrigações do Contratante</strong></p>
   <p>11.1. Sem prejuízo de outras disposições deste contrato, constituem obrigações do CONTRATANTE:</p>
    <ul>
       <li>a)	É imperativo adquirir um serviço de internet que satisfaça os critérios mínimos de velocidade e estabilidade necessários para o monitoramento eficaz do gerador fotovoltaico. A falta de conexão com a internet inviabiliza o monitoramento da energia, comprometendo a eficácia da gestão energética. Sem acesso à internet, não há possibilidade de monitoramento;</li>
       <li>b)	Manter e acompanhar o monitoramento do gerador fotovoltaico (por aplicativo ou site) para conferir a performance energética. Qualquer inconsistência, acionar o canal de suporte da CONTRATADA.</li>
       <li>c)	Notificar imediatamente a CONTRATADA em caso de suspeitas ou problemas relacionados a geração de energia solar fotovoltaica;</li>
       <li>d) Realizar a manutenção preventiva e limpezas periódicas dos equipamentos fotovoltaicos instalados (recomenda-se realizar de três a quatro limpezas dos painéis por ano). A falta dessas manutenções pode resultar em perda de desempenho do sistema e, consequentemente, na redução da produção de energia;</li>
       <li>e) Avaliar a necessidade de contratação de empresa de seguro contra furtos e/ou roubos;</li>
       <li>f) Efetuar o pagamento na data e nos termos definidos neste contrato;</li>
       <li>g) Fornecer todos os dados e informações pertinentes ao desenvolvimento dos trabalhos, objeto deste Contrato;</li>
       <li>h) Facilitar o acesso dos trabalhadores da CONTRATADA ao local da obra, caso seja necessário;</li>
       <li>i) Garantir a segurança dos trabalhadores da CONTRATADA, mantendo animais de estimação presos em local adequado durante a instalação dos equipamentos solares fotovoltaicos;</li>
       <li>j) Afastar outras condições que estejam sob seu controle e que possam trazer riscos à segurança e saúde dos trabalhadores envolvidos na execução dos serviços;</li>
       <li>k) Retirar do local de instalação quaisquer objetos de decoração ou aparelhos sensíveis que possam sofrer dano caso;</li>
       <li>l) Notificar a CONTRATADA, por escrito, da ocorrência de eventuais imperfeições, falhas ou irregularidades constatadas no curso da execução dos serviços;</li>
       <li>m) Realizar a segurança e preservação do sistema solar fotovoltaico, após a instalação;</li>
       <li>n) Realizar a manutenção preventiva do sistema fotovoltaico e da estrutura de sustentação, seguindo as normas de engenharia durante toda a vida útil do sistema de geração, após a conclusão da instalação.</li>
   </ul>
   <p><strong>Cláusula 12ª – Da proteção de dados pessoais</strong></p>
   <p>12.1. Em observação às determinações constantes da Lei n.º 13.709/18 – Lei Geral de Proteção de Dados (LGPD) -, o CONTRATANTE e a CONTRATADA se comprometem a proteger os direitos fundamentais de liberdade e de privacidade e o livre desenvolvimento da personalidade da pessoa natural, relativos ao tratamento de dados pessoais, inclusive nos meios digitais..</p>
   <p>12.2. A coleta e o tratamento de dados pessoais pelas partes ocorrerão quando estritamente necessário para a prestação dos serviços objeto deste contrato ou nas demais hipóteses, previstas nos arts. 7º e/ou 11 da Lei n.º 13.709/2018 e, em hipótese alguma, poderão ser compartilhados ou utilizados para outros fins.</p>
   <p>12.3. A CONTRATADA se compromete a responder todos os questionamentos feitos pela CONTRATANTE que envolvam dados pessoais repassados e a LGPD, no prazo de 5 (cinco) dias úteis, sem prejuízos dos demais deveres ajustados neste instrumento.</p>
   <p>12.4. A CONTRATADA deverá comunicar imediatamente a CONTRATANTE a ocorrência de qualquer incidente que implique violação ou risco de violação de dados pessoais, devendo adotar todas as medidas cabíveis, inclusive, técnicas para minimizar ou cessar o incidente com a maior brevidade possível. </p>
   <p>12.5. Encerrada a vigência do contrato ou não havendo mais necessidade de utilização dos dados pessoais, a CONTRATADA interromperá o tratamento dos dados pessoais disponibilizados pela CONTRATANTE e, em no máximo 30 (trinta) dias, eliminará todos os dados e cópias porventura existentes (formato digital ou físico), salvo quando a CONTRATADA tenha que manter os dados para cumprimento de obrigação legal ou outra hipótese da LGPD.</p>
   <p><strong>Cláusula 13ª – Normas da contratada</strong></p>
   <p>13.1. A CONTRATADA observará todas as exigências legais federais, estaduais e municipais relativas à segurança, higiene e medicina do trabalho; ao meio ambiente e aos requisitos sociais, está nos seguintes requisitos: trabalho infantil, trabalho forçado, saúde e segurança, liberdade de associação e direito à negociação coletiva, discriminação, práticas disciplinares, horários de trabalho e remuneração.</p>
   <p><strong>Cláusula 14ª – Do caso fortuito e da força maior</strong></p>
   <p>14.1. As obrigações do presente contrato suspender-se-ão sempre que ocorrerem circunstâncias alheias à vontade, controle e ação das partes, causadas por motivo de força maior ou caso fortuito, na forma do Código Civil, desde que sua ocorrência seja alegada e comprovada no prazo de 48 (quarenta e oito) horas.</p>
   <p>14.2. Serão considerados casos fortuitos ou de força maior, para efeito de rescisão contratual unilateral ou não aplicação de multas, os inadimplementos decorrentes das situações a seguir, quando vierem a afetar a realização da entrega do objeto do contrato no local indicado:</p>
   <ol type="a">
       <li> Greve geral no país;</li>
       <li> Calamidade pública;</li>
       <li> Interrupção dos meios normais de transportes que impeça a locomoção do pessoal;</li>
       <li> Acidentes, sem culpa da CONTRATADA, que impliquem em retardamento da execução da atividade;</li>
       <li> Consequências, devidamente comprovadas, de condições meteorológicas excepcionalmente prejudiciais e não passíveis de previsão;</li>
       <li> Eventuais atrasos decorrentes de dificuldades técnicas que venham a requerer a modificação do(s)Projeto(s) e Especificações, desde que exigidas pela engenharia ou órgão oficiais;</li>
       <li> Outros casos que se enquadrem no art. 393, parágrafo único, do Código Civil Brasileiro.</li>
   </ol>        
   <p><strong>Cláusula 15ª – Da rescisão</strong></p>
   <p>15.1. Constituirá justa causa para a rescisão deste contrato a parte que deixar de cumprir com qualquer cláusula ou condição contratual, após ter sido notificada do fato e não ter sanado integralmente seu inadimplemento no prazo de 30 (trinta) dias a partir da data de recebimento da notificação.</p>
   <p>15.1.1. A rescisão, por dolo ou culpa de uma das partes, lhe acarretará a responsabilidade pelas perdas e danos a que der causa, sem prejuízo das demais sanções contratuais e/ou legais aplicáveis. </p>
   <p>15.2. Após a ocorrência de 30 (trinta) dias de atraso de pagamento pelo CONTRATANTE e não saneamento de seu inadimplemento no prazo de 05 (cinco) dias após o recebimento da notificação, a CONTRATADA poderá rescindir este contrato, sem prejuízo das perdas e danos, com o cancelamento do Projeto junto a Concessionária local e, se for o caso, com o bloqueio do Sistema de Compensação de Energia Elétrica (SCEE).</p>
   <p><strong>Cláusula 16ª – Das penalidades</strong></p>
   <p>16.1. A violação das cláusulas deste Instrumento enseja a aplicação de multa correspondente a 20% (vinte por cento) sobre o valor do contrato, a ser corrigido no momento de sua aplicação, conforme variação do IGP-M (Fundação Getúlio Vargas) no período, sem prejuízo de demais cominações legais cabíveis.</p>
   <p>16.2. Além das multas contratuais, será devida indenização suplementar pelas perdas, danos, lucros cessantes, danos indiretos e quaisquer outros prejuízos patrimoniais ou morais percebidos pela parte contrária.</p>
   <p>16.3. A mera tolerância de uma das partes em relação ao descumprimento das cláusulas contidas neste instrumento não importa em renúncia, perdão, novação ou alteração da norma infringida.</p>
   <p><strong>Cláusula 17ª – Das disposições gerais</strong></p>
   <p>17.1. O CONTRATANTE declara ter recebido esclarecimentos sobre os equipamentos solares fotovoltaicos e não possui dúvidas quanto à sua finalidade e modo de funcionamento.</p>
   <p>17.2. Este Contrato reflete o único e integral acordo entre as partes, substituindo todos os outros eventuais documentos, cartas, memorandos, contratos, compromissos, promessas e/ou propostas entre as Partes, sejam orais ou escritos, firmados e/ou acordados antes da data do presente Instrumento.</p>
   <p>17.3. As modificações de quaisquer cláusulas deste instrumento deverão ser realizadas por meio de Aditivo Contratual, desde que feita por escrito e assinada por ambas as partes.</p>
   <p>17.4. Todos os avisos, notificações e comunicações enviados no âmbito deste contrato deverão ser feitos por escrito, preferencialmente via e-mail, com aviso de recebimento, para o endereço eletrônico das pessoas indicada pelas partes.</p>
   <p>17.5. Em caso de nulidade, total ou parcial, de uma disposição do contrato, as restantes disposições não serão afetadas pela disposição nula, valendo as demais clausulas que não foram afetadas. </p>
   <p>17.6. Declaram as partes, outrossim, terem plena ciência do teor do presente Contrato e que o mesmo tem validade de título executivo extrajudicial, nos termos do artigo 784 do Código de Processo Civil.</p>
   <p>17.7. As partes reconhecem por meio do presente Instrumento, a validade da assinatura eletrônica, nos termos do artigo 10, § 2º, da Medida Provisória n.º 2.200-2/2001 e Lei Geral de Proteção de Dados, bem como de que a referida assinatura eletrônica não implicará em qualquer alteração, desqualificação ou desnaturação de quaisquer deveres ou obrigações aqui previstas, os quais as partes continuam obrigadas a cumprir.</p>
   <p>17.8. Considera-se data da assinatura do contrato, para todos os efeitos, a data da aposição da última assinatura digital no presente instrumento.</p>
   <p><strong>Cláusula 18ª – Do foro</strong></p>
   <p>18.1. Para a resolução de eventuais litígios que se refiram a direitos ou a obrigações decorrentes deste contrato, fica eleito o foro da comarca da cidade de ' . $cidadeComarca . ' em que será assinado este instrumento.</p>
   <p>E, para firmeza e como prova de assim haverem entre si, ajustado e contratado, assinam o presente, em 02 (duas) vias de igual teor e forma, para que produza os seus legais e jurídicos efeitos.</p>
   <p>' . $cidade . ', ' . $dataAtual . '.</p>
    <style>
    p {
        margin: 0; /* Remove margens automáticas */
        text-align: justify; /* Define o texto como justificado */
    }
    </style>
   <p></p>
   <p></p>
   <p></p>
   <p></p>
   <p></p>
   <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;________________________________________________________</p>
   <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>PALLADIUM IMPORTADORA DE EQUIPAMENTOS LTDA</strong></p>
   <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Distribuidora)</p>
   <p></p>
   <p></p>
   <p></p>
   <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;________________________________________________________</p>
   <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $cnpjEstado . '</p>
   <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Contratada)</p>
   <p></p>
   <p></p>
   <p></p>
   <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;________________________________________________________</p>
   <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $nome . '</strong></p>
   <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(Contratante)</p>

';

    $pdf->writeHTMLCell(0, 0, 15, 40, $htmlContent, 0, 2, 0, true, 'J', true);


    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador


}