<?php

/**
 * Helpers de escrita das propostas em PDF (TCPDF).
 *
 * Vivem aqui, e não dentro de cada gerador, porque as duas modalidades
 * desenham a MESMA página de composição com artes diferentes:
 *   - gerar_pdf_inv_2.php (investimento) → PGINV14
 *   - gerar_pdf_2.php     (autoconsumo)  → PGAUT5
 *
 * Mudança de regra de desenho é feita uma vez, aqui, e vale para as duas.
 *
 * Todas as funções recebem o objeto TCPDF já criado e trabalham em
 * MILÍMETROS, que é a unidade das artes (A4, 210x297).
 */


/**
 * Escreve o texto CENTRALIZADO em torno de $centroX, na fonte corrente.
 * Assim, se o número crescer, ele se expande para os dois lados e não
 * estoura a arte pela direita.
 */
if (!function_exists('escreverCentralizado')) {
    function escreverCentralizado($pdf, $centroX, $y, $texto)
    {
        // Mede a largura na fonte ATUAL e desloca meia-largura para a esquerda.
        $largura = $pdf->GetStringWidth($texto);
        $pdf->Text($centroX - ($largura / 2), $y, $texto);
    }
}

/**
 * Escreve alinhado à esquerda a partir de $x, ENCOLHENDO o corpo da fonte
 * até o texto caber em $larguraMax (mm). Restaura a fonte original ao sair.
 *
 * Pré-condição: a fonte corrente já é a desejada para este texto.
 * Pós-condição: nada é escrito além de $x + $larguraMax, exceto se nem o
 *               tamanho mínimo couber (caso extremo, assumido aceitável).
 */
if (!function_exists('escreverAjustado')) {
    function escreverAjustado($pdf, $x, $y, $texto, $larguraMax, $tamMin = 5.5)
    {
        $tamOriginal = $pdf->getFontSizePt();
        $tam = $tamOriginal;
        while ($tam > $tamMin && $pdf->GetStringWidth($texto) > $larguraMax) {
            $tam -= 0.5;
            $pdf->SetFontSize($tam);
        }
        $pdf->Text($x, $y, $texto);
        $pdf->SetFontSize($tamOriginal); // Restaura para não afetar os próximos textos.
    }
}

/**
 * Escreve centralizado numa caixa da arte, encolhendo a fonte se preciso.
 * Usado onde o texto tem tamanho imprevisível (ex.: "Nao se paga no
 * periodo analisado"), para NUNCA estourar a caixa.
 */
if (!function_exists('escreverCentralizadoNaCaixa')) {
    function escreverCentralizadoNaCaixa($pdf, $centroX, $y, $texto, $larguraMax, $tamMin = 7)
    {
        $tamOriginal = $pdf->getFontSizePt();
        $tam = $tamOriginal;
        // Reduz o corpo da fonte até o texto caber na largura da caixa.
        while ($tam > $tamMin && $pdf->GetStringWidth($texto) > $larguraMax) {
            $tam -= 0.5;
            $pdf->SetFontSize($tam);
        }
        escreverCentralizado($pdf, $centroX, $y, $texto);
        $pdf->SetFontSize($tamOriginal); // Restaura para não afetar os próximos textos.
    }
}

/**
 * Desenha a lista "Composição da Proposta" DENTRO do retângulo que a arte
 * reserva, sem nunca invadir o bloco de preços/payback logo abaixo.
 *
 * Pré-condição: $itens é uma lista de ['qtd' => string, 'desc' => string].
 *               'qtd' vazia => a linha ocupa a largura toda a partir de $xQtd.
 * Pós-condição: todas as linhas ficam dentro de
 *               [$yTopo, $yTopo + $alturaDisponivel]; a fonte é restaurada.
 *
 * A altura da linha e o corpo da fonte encolhem conforme a quantidade de
 * itens — é isso que garante que um kit com muitos itens não vaze a arte.
 *
 * Limite prático: até ~18 itens o texto ainda respira. Acima disso o corpo
 * mínimo (5,5pt) é atingido e as linhas começam a se encostar — se um kit
 * assim aparecer, a arte precisa de mais espaço, não o código de menos fonte.
 */
if (!function_exists('escreverComposicao')) {
    function escreverComposicao($pdf, array $itens, $xQtd, $xDesc, $yTopo, $alturaDisponivel, $larguraDesc, $tamMax = 13.5, $tamMin = 5.5)
    {
        // Linhas sem descrição (adicional não selecionado) não ocupam espaço.
        $itens = array_values(array_filter($itens, function ($item) {
            return isset($item['desc']) && trim($item['desc']) !== '';
        }));

        $total = count($itens);
        if ($total === 0) {
            return;
        }

        // Altura de linha: a padrão da arte (8mm), reduzida só se não couber.
        $alturaLinha = min(8, $alturaDisponivel / $total);

        // Corpo da fonte proporcional à entrelinha, limitado pelo tamanho
        // padrão da arte e por um mínimo ainda legível em impressão.
        $tamFonte = max($tamMin, min($tamMax, $alturaLinha * 2.4));

        $familia = 'helvetica';
        $estilo = 'B';
        $larguraQtd = $xDesc - $xQtd;

        // Um corpo de fonte ÚNICO para o bloco inteiro: encolher linha a
        // linha deixaria a lista visivelmente desalinhada. Procura o maior
        // tamanho em que TODAS as descrições cabem na faixa disponível.
        while ($tamFonte > $tamMin) {
            $pdf->SetFont($familia, $estilo, $tamFonte);
            $todasCabem = true;
            foreach ($itens as $item) {
                $temQtd = isset($item['qtd']) && trim($item['qtd']) !== '';
                $limite = $temQtd ? $larguraDesc : $larguraDesc + $larguraQtd;
                if ($pdf->GetStringWidth(trim($item['desc'])) > $limite) {
                    $todasCabem = false;
                    break;
                }
            }
            if ($todasCabem) {
                break;
            }
            $tamFonte -= 0.5;
        }

        $y = $yTopo;
        foreach ($itens as $item) {
            $qtd = isset($item['qtd']) ? trim($item['qtd']) : '';
            $desc = trim($item['desc']);

            $pdf->SetFont($familia, $estilo, $tamFonte);

            if ($qtd !== '') {
                $pdf->Text($xQtd, $y, $qtd);
                $xTexto = $xDesc;
                $larguraTexto = $larguraDesc;
            } else {
                // Sem coluna de quantidade: a descrição usa a faixa inteira.
                $xTexto = $xQtd;
                $larguraTexto = $larguraDesc + $larguraQtd;
            }

            // Rede de segurança: se uma descrição sozinha ainda estourar no
            // tamanho mínimo, SÓ ela encolhe mais — melhor que vazar a arte.
            escreverAjustado($pdf, $xTexto, $y, $desc, $larguraTexto, $tamMin);

            $y += $alturaLinha;
        }
    }
}

/**
 * Desenha UM bloco de tecnologia da página PGINV14: a lista de composição,
 * o preço à vista, as linhas de "à prazo" e o payback.
 *
 * LONGI e N-TYPE usam exatamente este mesmo desenho. Tudo que difere entre
 * elas está em $dados (o QUE mostrar) e $caixa (ONDE mostrar) — por isso
 * ligar a N-TYPE de verdade não exige tocar em nada aqui dentro.
 *
 * Pré-condição:
 *   $dados = ['itens' => array, 'vista' => string, 'prazo' => string[],
 *             'payback' => string]
 *   $caixa = coordenadas em mm do bloco (ver $blocosLayout na página).
 *   'prazo' é uma LISTA: hoje traz as 3 parcelas, amanhã pode trazer um
 *   único valor de "à prazo" — nos dois casos o desenho é o mesmo.
 * Pós-condição: nada é escrito fora das caixas que a arte reserva.
 */
if (!function_exists('escreverBlocoTecnologia')) {
    function escreverBlocoTecnologia($pdf, array $dados, array $caixa)
    {
        // Cor da lista definida aqui de propósito: sem isto o bloco herdaria
        // silenciosamente a cor deixada pelo bloco anterior.
        $pdf->SetTextColor(50, 50, 50);
        escreverComposicao(
            $pdf,
            $dados['itens'],
            $caixa['xQtd'],
            $caixa['xDesc'],
            $caixa['yLista'],
            $caixa['alturaLista'],
            $caixa['larguraDesc']
        );

        // Caixas escuras da arte pedem texto branco.
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetTextColor(255, 255, 255);
        escreverAjustado($pdf, $caixa['xPreco'], $caixa['yVista'], $dados['vista'], $caixa['larguraPreco']);

        // Uma linha só (valor único de "à prazo") cabe no mesmo corpo do
        // "à vista"; com as 3 parcelas empilhadas é preciso encolher.
        $linhasPrazo = array_values($dados['prazo']);
        $pdf->SetFont('helvetica', 'B', count($linhasPrazo) > 1 ? 9 : 13);
        foreach ($linhasPrazo as $indice => $linha) {
            escreverAjustado(
                $pdf,
                $caixa['xPreco'],
                $caixa['yPrazo'] + ($indice * $caixa['passoPrazo']),
                $linha,
                $caixa['larguraPreco']
            );
        }

        // A faixa do "Payback:" é clara: volta para o texto escuro.
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(50, 50, 50);
        escreverAjustado($pdf, $caixa['xPayback'], $caixa['yPayback'], $dados['payback'], $caixa['larguraPayback']);
    }
}

/**
 * Diz se a descrição de um item da composição é a linha do painel.
 *
 * É uma heurística sobre texto livre: no kit vindo do banco a descrição é
 * digitada por gente. A linha precisa COMEÇAR com "módulo(s)" ou "painel",
 * aceitando uma quantidade na frente ("84 MODULOS ..."). Exigir o início,
 * e não apenas conter a palavra, evita confundir com itens que só citam o
 * módulo — "ESTRUTURA PARA MÓDULOS", por exemplo.
 *
 * Se um kit usar outra palavra, a linha não é reconhecida e o painel entra
 * como item extra (ver trocarPainelNaComposicao): o erro possível é um item
 * a mais, nunca a proposta sem dizer qual módulo está sendo vendido.
 *
 * Não usa mb_* de propósito, para não depender da extensão mbstring: os
 * acentos que importam são normalizados na mão antes do strtoupper.
 */
if (!function_exists('ehLinhaDePainel')) {
    function ehLinhaDePainel($descricao)
    {
        $texto = strtr($descricao, [
            'ó' => 'o', 'Ó' => 'O',
            'á' => 'a', 'Á' => 'A',
        ]);
        $texto = strtoupper($texto);

        // MODUL... cobre MODULO/MODULOS; PAINE... cobre PAINEL/PAINEIS.
        return (bool) preg_match('/^\s*(?:[\d.,]+\s+)?(?:MODUL|PAINE)/', $texto);
    }
}

/**
 * Devolve a composição de UMA tecnologia, trocando apenas a linha do painel.
 *
 * O painel é o único item que muda entre LONGI e N-TYPE: inversor,
 * estrutura, cabos e serviços são os mesmos nos dois blocos. Por isso a
 * lista é montada uma vez e passada por aqui duas vezes.
 *
 * Pré-condição: $itens no formato de escreverComposicao(); $descricaoPainel
 *   é o texto do módulo JÁ PRONTO, mas SEM quantidade.
 * Pós-condição: devolve uma CÓPIA — $itens não é alterado, porque a mesma
 *   lista alimenta as duas tecnologias.
 */
if (!function_exists('trocarPainelNaComposicao')) {
    function trocarPainelNaComposicao(array $itens, $descricaoPainel)
    {
        foreach ($itens as $indice => $item) {
            if (!ehLinhaDePainel($item['desc'])) {
                continue;
            }

            // Na composição padrão a quantidade vem embutida no texto
            // ("84 MODULOS ..."); nos kits do banco ela mora na coluna
            // própria. Preserva as duas formas sem duplicar o número.
            $temColunaQtd = isset($item['qtd']) && trim($item['qtd']) !== '';
            $prefixoQtd = '';
            if (!$temColunaQtd && preg_match('/^\s*([\d.,]+)\s+/', $item['desc'], $captura)) {
                $prefixoQtd = $captura[1] . ' ';
            }

            $itens[$indice]['desc'] = $prefixoQtd . $descricaoPainel;

            return $itens; // Só a primeira linha de módulo é o painel.
        }

        // Kit sem nenhuma linha de módulo: o painel entra como primeiro
        // item, para a proposta nunca sair sem dizer o que está vendendo.
        array_unshift($itens, ['qtd' => '', 'desc' => $descricaoPainel]);

        return $itens;
    }
}
