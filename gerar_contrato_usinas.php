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
    $pdf->SetMargins(15, 20, 15); // Margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(TRUE, 20); // Quebra automática com 20 unidades na margem inferior


    // Primeira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página


    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 16);
    $pdf->SetTextColor(0, 0, 0);

    
    function renderTextWithBold($pdf, $paragraphs, $cellWidth) {
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

    // Conteúdo HTML
    $htmlContent = '

    <p><strong>CONTRATO DE VENDA E INSTALAÇÃO DE EQUIPAMENTOS SOLARES FOTOVOLTAICOS</strong></p>
    <p>Por este instrumento,</p>
    <p><strong>PALLADIUM IMPORTADORA DE EQUIPAMENTOS LTDA</strong>, pessoa jurídica de direito privado, inscrita no CNPJ sob o n.º 49.348.620/0001-05, com sede na Av. Colombo, n.º 5088, zona 07, na cidade de Maringá/PR - CEP 87.030-121, representada neste ato por seu representante legal, doravante denominada “DISTRIBUIDORA”.</p>
    <p><strong>NEO MARINGÁ ENGENHARIA ELÉTRICA LTDA</strong>, pessoa jurídica de direito privada, inscrita no CNPJ sob o n.° 35.067.916/0001-43, com sede na Av. Colombo, n.º 5088, zona 07, na cidade de Maringá/PR - CEP 87.030-121, representada neste ato por seu representante legal, doravante denominada “CONTRATADA”.</p>
    <p><strong>'.$nome.'</strong>, brasileiro, portador do RG n.º 8397325-0 SESP/PR, inscrito no CPF n.º 009.425.209-20, residente e domiciliado na Rua Pioneiro Alcides de Araújo Vargas, n.º 219, Vila Esperança, na cidade de Maringá/PR - CEP: 87.020-620, correio eletrônico: willazevedo@gmail.com, contato: (44) 9-9951-4331, doravante denominada CONTRATANTE.</p>
    <p>Considerando que:</p>
    <p>1. A Palladium (Distribuidora) é a empresa responsável pelo fornecimento de equipamentos e materiais necessários para a instalação de sistema solar fotovoltaico.</p>
    <p>2. A Canal Verde (Contratada) é a empresa responsável pala instalação, manutenção e suporte técnico do sistema solar fotovoltaico comercializado.</p>
    <p>3. O cliente (Contratante) que realiza a compra e a contratação dos serviços de instalação de sistema solar fotovoltaico em sua propriedade.</p>
    <p>As partes acima identificadas têm, entre si, justas e acertadas o presente Contrato de Venda e Instalação de Equipamentos Solares Fotovoltaicos, conforme cláusulas e condições adiante estipuladas:As partes acima identificadas têm, entre si, justas e acertadas o presente Contrato de Venda e Instalação de Equipamentos Solares Fotovoltaicos, conforme cláusulas e condições adiante estipuladas:</p>
    <p><strong>Cláusula 1ª – Do objeto</strong></p>
    <p>1.1. O presente contrato tem como objeto a venda e implantação de 01 (um) gerador fotovoltaico ON GRID conectado à rede pela CONTRATADA, com potência operacional estimada de 63,44 kWp, na Fazenda Santa Fé, rural, na cidade de Iguatemi/MS - CEP: 79.960-000, localização LOCALIZAÇÃO, conforme condições, quantidades e procedimentos estabelecidos neste Instrumento.</p>
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
        </tr>
        <tr>
            <td>17</td>
            <td>MÓDULOS SOLARES SINE TOPCON 585W;</td>
        </tr>
        <tr>
            <td>50</td>
            <td>CABOS SOLARES PV 1.8KVCC 6MM VERMELHO NBR 16612;</td>
        </tr>
        <tr>
            <td>50</td>
            <td>CABOS SOLARES PV 1.8KVCC 6MM PRETO NBR 16612;</td>
        </tr>
        <tr>
            <td>06</td>
            <td>CONECTORES MC4 MACHO/FÊMEA 1000V TI-LANE;</td>
        </tr>
        <tr>
            <td>01</td>
            <td>INVERSOR 220V SAJ MONOFÁSICO 6KW;</td>
        </tr>
        <tr>
            <td>05</td>
            <td>ESTRUTURAS P/ 4 MOD. SOLAR COLONIAL;</td>
        </tr>
        <tr>
            <td>10</td>
            <td>PERFIS TUBULARES DE ALUMÍNIO 4,80M;</td>
        </tr>
    </table>
    <p>3.2. Não estão inclusos custos adicionais com obras de rede pela Concessionária local e terraplanagem.</p>
    <p>3.3. O painel fotovoltaico, inversor e demais componentes aplicáveis são aprovados pelo Programa Brasileiro de Etiquetagem (PBE), coordenado pelo Inmetro.</p>
    <p>3.4. Os equipamentos são instalados de acordo com as orientações dos fabricantes e regulamentações fornecidas pela Agência Nacional de Energia Elétrica (ANEEL).</p>
    <p><strong>Cláusula 4ª – Dos critérios para substituição de produtos</strong></p>
    <p>4.1. Todo material e/ou equipamento empregado na execução dos serviços será novo e de primeira qualidade.</p>
    <p>4.2. Na eventual falta do produto especificado na Proposta Comercial, a CONTRATADA poderá realizar a substituição por outro de espécie, marca ou modelo similar, desde que este possua potência igual ou superior àquela do produto inicialmente proposto, sem qualquer ônus para o CONTRATANTE.</p>
    <p><strong>Cláusula 5ª – Do preço e forma de pagamento</strong></p>
    <p>5.1. Pela prestação dos serviços contratados, a CONTRATANTE pagará a CONTRATADA a quantia total de R$ 198.878,84 (cento e noventa e oito mil, oitocentos e setenta e oito reais e oitenta e quatro centavos), cujo pagamento será realizado da seguinte forma:</p>
    <p>XXXXXXXXXXXXXXXXXXX</p>
    <p>5.1.1.  Caso não ocorra a coincidência entre a data de liberação do financiamento e a data da Proposta Comercial, o CONTRATANTE, em caráter irrevogável e irretratável, autoriza a CONTRATADA a proceder ao pertinente e necessário recálculo da proposta para atualização do valor total do Contrato.</p>
    <p><strong>Cláusula 5ª – Do prazo de entrega e instalação</strong></p>
    <p>5.1.2. Caso o CONTRATANTE dependa exclusivamente de financiamento bancário e este não seja aprovado pelo agente finaceiro para realizar a contratação dos serviços pactuados, o contrato será rescindido de pleno direito, sem que sejam devidas quaisquer multas e/ou indenizações pela CONTRATANTE.</p>
    <p>5.2. A CONTRATANTE não vindo a efetuar o pagamento na data estipulada, fica obrigada a pagar multa de 2% (dois por cento) sobre o valor devido, bem como juros de mora de 1% (um por cento) ao mês, mais correção monetária apurada conforme variação do IGP-M no período.</p>
    <p>5.3. Em caso de desistência ou renúncia pela CONTRATANTE sem motivo justo, dentro do prazo de instalação (cláusula 4.1), será devido a CONTRATADA a título de reparação e indenização multa de 10% (dez por cento) sobre o valor do contrato.</p>
    <p>5.4.  A CONTRATANTE reconhece e concorda que apenas terá a posse do gerador fotovoltaico após o seu pagamento integral, podendo este ser retirado pela CONTRATADA em caso de não pagamento do valor e no prazo pactuado.</p>
    <p><strong>Cláusula 6ª – Do prazo de entrega e instalação</strong></p>
    <p>6.1. O prazo para entrega dos equipamentos, instalação e início da operação do sistema solar fotovoltaico é de até 120 (cento e vinte) dias úteis pela CONTRATADA, contado a partir da aprovação do projeto pela Concessionária de energia local.</p>
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
        <li>g) Outros casos que se enquadrem no art. 393, parágrafo único, do Código Civil Brasileiro.</li>
    </ul>
    <p><strong>Cláusula 14ª – Da rescisão</strong></p>
    <p>14.1. Constituirá justa causa para a rescisão deste contrato a parte que deixar de cumprir com qualquer cláusula ou condição contratual, após ter sido notificada do fato e não ter sanado integralmente seu inadimplemento no prazo de 30 (trinta) dias, a contar da data de recebimento da notificação.</p>
    <p>14.1.1. A rescisão, por dolo ou culpa de uma das partes, lhe acarretará a responsabilidade pelas perdas e danos a que der causa, sem prejuízo das demais sanções contratuais e/ou legais aplicáveis.</p>
    <p>14.2. Após a ocorrência de 30 (trinta) dias de atraso de pagamento pelo CONTRATANTE e não saneamento de seu inadimplemento no prazo de 05 (cinco) dias após o recebimento da notificação, a CONTRATADA poderá rescindir este contrato, sem prejuízo das perdas e danos, com o cancelamento do Projeto junto a Concessionária local e, se for o caso, com o bloqueio do Sistema de Compensação de Energia Elétrica (SCEE).</p>
    <p><strong>Cláusula 15ª – Das penalidades</strong></p>
    <p>15.1. A violação das cláusulas deste Instrumento enseja a aplicação de multa correspondente a 20% (vinte por cento) sobre o valor do contrato, a ser corrigido no momento de sua aplicação, conforme variação do IGP-M (Fundação Getúlio Vargas) no período, sem prejuízo de demais cominações legais cabíveis.</p>
    <p>15.2. Além das multas contratuais, será devida indenização suplementar pelas perdas, danos, lucros cessantes, danos indiretos e quaisquer outros prejuízos patrimoniais ou morais percebidos pela parte contrária.</p>
    <p>15.3. A mera tolerância de uma das partes em relação ao descumprimento das cláusulas contidas neste instrumento não importa em renúncia, perdão, novação ou alteração da norma infringida.</p>
    <p><strong>Cláusula 16ª – Das disposições gerais</strong></p>
    <p>16.1. O CONTRATANTE declara ter recebido esclarecimentos sobre os equipamentos solares fotovoltaicos e não possui dúvidas quanto à sua finalidade e modo de funcionamento.</p>
    <p>16.2. Este Contrato reflete o único e integral acordo entre as partes, substituindo todos os outros eventuais documentos, cartas, memorandos, contratos, compromissos, promessas e/ou propostas entre as Partes, sejam orais ou escritos, firmados e/ou acordados antes da data do presente Instrumento.</p>
    <p>16.3. As modificações de quaisquer cláusulas deste instrumento deverão ser realizadas por meio de Aditivo Contratual, desde que feita por escrito e assinada por ambas as partes.</p>
    <p>16.4. Todos os avisos, notificações e comunicações enviados no âmbito deste contrato deverão ser feitos por escrito, preferencialmente via e-mail, com aviso de recebimento, para o endereço eletrônico das pessoas indicada pelas partes.</p>
    <p>16.5. Em caso de nulidade, total ou parcial, de uma disposição do contrato, as restantes disposições não serão afetadas pela disposição nula, valendo as demais cláusulas que não foram afetadas.</p>
    <p>16.6. Declaram as partes, outrossim, terem plena ciência do teor do presente Contrato e que o mesmo tem validade de título executivo extrajudicial, nos termos do artigo 784 do Código de Processo Civil.</p>
    <p>16.7. As partes reconhecem por meio do presente Instrumento, a validade da assinatura eletrônica, nos termos do art. 10, § 2º, da Medida Provisória n.º 2.200-2/2001 e Lei Geral de Proteção de Dados, bem como de que a referida assinatura eletrônica não implicará em qualquer alteração, desqualificação ou desnaturação de quaisquer deveres ou obrigações aqui previstas, os quais as partes continuam obrigadas a cumprir.</p>
    <p><strong>Cláusula 17ª – Do foro</strong></p>
    <p>17.1. Para a resolução de eventuais litígios que se refiram a direitos ou a obrigações decorrentes deste contrato, fica eleito o foro da comarca da cidade de Maringá/PR em que será assinado este instrumento.</p>
    <p>Por estarem de justo acordo, as partes assinam o presente contrato, em 02 (duas) vias de idêntico teor e forma.</p>
    <p>Maringá, 23 de dezembro de 2024.</p>
    <p>________________________________________________________</p>
    <p>PALLADIUM IMPORTADORA DE EQUIPAMENTOS LTDA</p>
    <p>(Contratada)</p>
    <p>________________________________________________________</p>
    <p>NEO MARINGÁ ENGENHARIA ELÉTRICA LTDA</p>
    <p>(Contratada)</p>
    <p>________________________________________________________</p>
    <p>WILLIAM DE AZEVEDO</p>
    <p>CPF n.º 009.425.209-20</p>
    <p>(Contratante)</p>
';

    $pdf->writeHTMLCell(0, 0, 15, 15, $htmlContent, 0, 1, 0, true, 'J', true);


    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador
    
    
}
?>