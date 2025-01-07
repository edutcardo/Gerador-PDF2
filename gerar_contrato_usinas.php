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
        "9.1. Sem prejuízo de outras disposições deste contrato, constituem obrigações da CONTRATADA:",
        "a) Cumprir integralmente este contrato na forma e modo ajustados;",
        "b) Conduzir os trabalhos com estrita observância às normas da legislação pertinente, cumprindo as determinações dos Poderes Públicos, mantendo sempre limpo o local dos serviços e nas melhores condições de segurança, higiene e disciplina;",
        "c) Fornecer e utilizar os materiais, equipamentos, ferramentas e utensílios necessários, na qualidade e quantidade especificadas na Proposta Comercial;",
        "d) Empregar na execução dos serviços apenas profissionais capacitados e qualificados para essas funções",
        "e) Apresentar ao CONTRATANTE, quando for o caso, a relação nominal dos profissionais que adentrarão em suas instalações;",
        "f) Obedecer às normas técnicas, de saúde, de higiene e de segurança do trabalho, de acordo com as normas do Ministério do Trabalho e Emprego (MTE);",
        "g) Assumir a responsabilidade pelos atos e/ou omissões praticados por si e por seus subordinados, bem como pelos danos que venham a causar para a CONTRATANTE, desde que comprovados, em decorrência da prestação dos serviços prestados;",
        "l) Arcar devidamente, nos termos da legislação trabalhista, com a remuneração e demais verbas laborais devidas a seus subordinados, inclusive, encargos fiscais e previdenciários referentes às relações de trabalho;",
        "h) Arcar com todas as despesas de natureza tributária decorrentes dos serviços especificados neste contrato;",
        "i) Relatar à CONTRATANTE toda e qualquer irregularidade verificada no decorrer da prestação dos serviços;",
        "j) Guardar sigilo sobre todas as informações obtidas em decorrência do cumprimento do contrato;",
        "k) Providenciar junto ao CREA as Anotações de Responsabilidade Técnica (ART), referentes ao objeto do contrato e especialidades, nos termos das normas pertinentes.",
        "Cláusula 10ª – Das obrigações do Contratante",
        "10.1. Sem prejuízo de outras disposições deste contrato, constituem obrigações do CONTRATANTE:",
        "a) É imperativo adquirir um serviço de internet que satisfaça os critérios mínimos de velocidade e estabilidade necessários para o monitoramento eficaz do gerador fotovoltaico. A falta de conexão com a internet inviabiliza o monitoramento da energia, comprometendo a eficácia da gestão energética. Sem acesso à internet, não há possibilidade de monitoramento;",
        "b) manter e acompanhar o monitoramento do gerador fotovoltaico (por aplicativo ou site) para conferir a performance energética. Qualquer inconsistência, acionar o canal de suporte da CONTRATADA;",
        "c) notificar imediatamente a CONTRATADA em caso de suspeitas ou problemas relacionados à geração de energia solar fotovoltaica;",
        "d) realizar a manutenção preventiva e executar limpezas periódicas dos sistemas fotovoltaicos instalados (recomenda-se realizar de três a quatro limpezas dos painéis por ano), uma vez que a falta dessas manutenções pode resultar em perda de desempenho do sistema e, consequentemente, na redução da produção de energia;",
        "e) avaliar a necessidade de contratação de empresa de seguro contra furtos e/ou roubos;",
        "f) efetuar o pagamento na data e nos termos definidos neste contrato;",
        "g) facilitar o acesso dos trabalhadores da CONTRATADA ao local da obra, caso seja necessário;",
        "h) garantir a segurança dos trabalhadores da CONTRATADA, mantendo animais de estimação presos em local adequado durante a instalação dos equipamentos solares fotovoltaicos;",
        "i) afastar outras condições que estejam sob seu controle e que possam trazer riscos à segurança e saúde dos trabalhadores envolvidos na execução dos serviços;",
        "j) retirar do local de instalação quaisquer objetos de decoração ou aparelhos sensíveis que possam sofrer dano caso;",
        "k) notificar a CONTRATADA, por escrito, da ocorrência de eventuais imperfeições, falhas ou irregularidades constatadas no curso da execução dos serviços;",
        "l) realizar a segurança e preservação do sistema solar fotovoltaico, após a instalação;",
        "m)realizar a manutenção preventiva do sistema fotovoltaico e da estrutura de sustentação, seguindo as normas de engenharia durante toda a vida útil do sistema de geração, após a conclusão da instalação.",
        "Cláusula 11ª – Da proteção de dados pessoais (LGPD)",
        "11.1. Em observação às determinações constantes da Lei n.º 13.709/18 – Lei Geral de Proteção de Dados (LGPD) -, a CONTRATADA se compromete a proteger os direitos fundamentais de liberdade e de privacidade e o livre desenvolvimento da personalidade da pessoa natural, relativos ao tratamento de dados pessoais, inclusive nos meios digitais.",
        "11.2. A coleta e o tratamento de dados pessoais pela CONTRATADA ocorrerá quando estritamente necessário para a prestação dos serviços objeto deste contrato ou nas demais hipóteses, previstas nos arts. 7º e/ou 11 da Lei n.º 13.709/2018 e, em hipótese alguma, poderão ser compartilhados ou utilizados para outros fins.",
        "11.3. Em caso de necessidade de coleta de dados pessoais indispensáveis à prestação do serviço, esta será realizada mediante prévia aprovação da CONTRATANTE, responsabilizando-se a CONTRATADA por obter o consentimento do titular, salvo nos casos em que opere outra hipótese legal de tratamento.",
        "11.4. A CONTRATADA se compromete a responder todos os questionamentos feitos pela CONTRATANTE que envolvam dados pessoais repassados e a LGPD, no prazo de 5 (cinco) dias úteis, sem prejuízos dos demais deveres ajustados neste instrumento.",
        "11.5. A CONTRATADA deverá comunicar imediatamente a CONTRATANTE a ocorrência de qualquer incidente que implique violação ou risco de violação de dados pessoais, devendo adotar todas as medidas cabíveis, inclusive, técnicas para minimizar ou cessar o incidente com a maior brevidade possível. ",
        "11.6. Encerrada a vigência do contrato ou não havendo mais necessidade de utilização dos dados pessoais, a CONTRATADA interromperá o tratamento dos dados pessoais disponibilizados pela CONTRATANTE e, em no máximo 30 (trinta) dias, eliminará todos os dados e cópias porventura existentes (formato digital ou físico), salvo quando tenha que manter os dados para cumprimento de obrigação legal ou outra hipótese prevista em lei.",
        "Cláusula 12ª – Normas da contratada",
        "12.1. A CONTRATADA observará todas as exigências legais federais, estaduais e municipais relativas à segurança, higiene e medicina do trabalho; ao meio ambiente e aos requisitos sociais, está nos seguintes requisitos: trabalho infantil, trabalho forçado, saúde e segurança, liberdade de associação e direito à negociação coletiva, discriminação, práticas disciplinares, horários de trabalho e remuneração.",
        "Cláusula 13ª – Do caso fortuito e da força maior",
        "13.1. As obrigações do presente contrato suspender-se-ão sempre que ocorrerem circunstâncias alheias à vontade, controle e ação das partes, causadas por motivo de força maior ou caso fortuito, na forma do Código Civil, desde que sua ocorrência seja alegada e comprovada no prazo de 48 (quarenta e oito) horas.",
        "13.2. Serão considerados casos fortuitos ou de força maior, para efeito de rescisão contratual unilateral ou não aplicação de multas, os inadimplementos decorrentes das situações a seguir, quando vierem a afetar a realização da entrega do objeto do contrato no local indicado:",
        "a) Greve geral no país;",
        "b) Calamidade pública;",
        "c) Interrupção dos meios normais de transportes que impeça a locomoção do pessoal;",
        "d) Acidentes, sem culpa da CONTRATADA, que impliquem em retardamento da execução da atividade;",
        "e) Consequências, devidamente comprovadas, de condições meteorológicas excepcionalmente prejudiciais e não passíveis de previsão;",
        "f) Eventuais atrasos decorrentes de dificuldades técnicas que venham a requerer a modificação do(s)Projeto(s) e Especificações, desde que exigidas pela engenharia ou órgão oficiais; e",
        "g) Outros casos que se enquadrem no art. 393, parágrafo único, do Código Civil Brasileiro.",
        "Cláusula 14ª – Da rescisão",
        "14.1. Constituirá justa causa para a rescisão deste contrato a parte que deixar de cumprir com qualquer cláusula ou condição contratual, após ter sido notificada do fato e não ter sanado integralmente seu inadimplemento no prazo de 30 (trinta) dias, a contar da data de recebimento da notificação.",
        "14.1.1. A rescisão, por dolo ou culpa de uma das partes, lhe acarretará a responsabilidade pelas perdas e danos a que der causa, sem prejuízo das demais sanções contratuais e/ou legais aplicáveis. ",
        "14.2. Após a ocorrência de 30 (trinta) dias de atraso de pagamento pelo CONTRATANTE e não saneamento de seu inadimplemento no prazo de 05 (cinco) dias após o recebimento da notificação,  a CONTRATADA poderá rescindir este contrato, sem prejuízo das perdas e danos, com o cancelamento do Projeto junto a Concessionária local e, se for o caso, com o bloqueio do Sistema de Compensação de Energia Elétrica (SCEE).",
        "Cláusula 15ª – Das penalidades",
        "15.1. A violação das cláusulas deste Instrumento enseja a aplicação de multa correspondente a 20% (vinte por cento) sobre o valor do contrato, a ser corrigido no momento de sua aplicação, conforme variação do IGP-M (Fundação Getúlio Vargas) no período, sem prejuízo de demais cominações legais cabíveis.",
        "15.2. Além das multas contratuais, será devida indenização suplementar pelas perdas, danos, lucros cessantes, danos indiretos e quaisquer outros prejuízos patrimoniais ou morais percebidos pela parte contrária.",
        "15.3. A mera tolerância de uma das partes em relação ao descumprimento das cláusulas contidas neste instrumento não importa em renúncia, perdão, novação ou alteração da norma infringida.",
        "Cláusula 16ª – Das disposições gerais",
        "16.1. O CONTRATANTE declara ter recebido esclarecimentos sobre os equipamentos solares fotovoltaicos e não possui dúvidas quanto à sua finalidade e modo de funcionamento.",
        "16.2. Este Contrato reflete o único e integral acordo entre as partes, substituindo todos os outros eventuais documentos, cartas, memorandos, contratos, compromissos, promessas e/ou propostas entre as Partes, sejam orais ou escritos, firmados e/ou acordados antes da data do presente Instrumento.",
        "16.3. As modificações de quaisquer cláusulas deste instrumento deverão ser realizadas por meio de Aditivo Contratual, desde que feita por escrito e assinada por ambas as partes.",
        "16.4. Todos os avisos, notificações e comunicações enviados no âmbito deste contrato deverão ser feitos por escrito, preferencialmente via e-mail, com aviso de recebimento, para o endereço eletrônico das pessoas indicada pelas partes.",
        "16.5. Em caso de nulidade, total ou parcial, de uma disposição do contrato, as restantes disposições não serão afetadas pela disposição nula, valendo as demais clausulas que não foram afetadas.",
        "16.6. Declaram as partes, outrossim, terem plena ciência do teor do presente Contrato e que o mesmo tem validade de título executivo extrajudicial, nos termos do artigo 784 do Código de Processo Civil.",
        "16.7. As partes reconhecem por meio do presente Instrumento, a validade da assinatura eletrônica, nos termos do art. 10, § 2º, da Medida Provisória n.º 2.200-2/2001 e Lei Geral de Proteção de Dados, bem como de que a referida assinatura eletrônica não implicará em qualquer alteração, desqualificação ou desnaturação de quaisquer deveres ou obrigações aqui previstas, os quais as partes continuam obrigadas a cumprir.",
        "Cláusula 17ª – Do foro",
        "17.1. Para a resolução de eventuais litígios que se refiram a direitos ou a obrigações decorrentes deste contrato, fica eleito o foro da comarca da cidade de Maringá/PR em que será assinado este instrumento.",
        "Por estarem de justo acordo, as partes assinam o presente contrato, em 02 (duas) vias de idêntico teor e forma.",
        "Maringá, 23 de dezembro de 2024.",
        "________________________________________________________",
        "PALLADIUM IMPORTADORA DE EQUIPAMENTOS LTDA",
        "(Contratada)",
        "________________________________________________________",
        "NEO MARINGÁ ENGENHARIA ELÉTRICA LTDA",
        "(Contratada)",
        "________________________________________________________",
        "WILLIAM DE AZEVEDO",
        "CPF n.º 009.425.209-20",
        "(Contratante)"
        


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