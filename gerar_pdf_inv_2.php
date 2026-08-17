<?php
session_start();
require_once('vendor/autoload.php'); // Ou o caminho correto, se você não estiver usando o Composer


// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    
    // Variáveis que já estavam corretas
    $nome = isset($_POST['nome']) ? $_POST['nome'] : 'Informar';
    $endereco = isset($_POST['endereco']) ? $_POST['endereco'] : 'Informar';
    $cidade = isset($_POST['cidade']) ? $_POST['cidade'] : 'Informar';
    $uf = isset($_POST['uf']) ? $_POST['uf'] : 'Informar';
    $uc = isset($_POST['uc']) ? $_POST['uc'] : '0000';
    $media = isset($_POST['media']) && $_POST['media'] !== '' ? floatval($_POST['media']) : 1;
    $valoramais = isset($_POST['valoramais']) && $_POST['valoramais'] !== '' ? floatval($_POST['valoramais']) : 0;

    // Variáveis que foram corrigidas
    $iluminacao = isset($_POST['iluminacao']) ? floatval($_POST['iluminacao']) : 0;
    $potenciaGerador = isset($_POST['potenciaGerador']) ? floatval($_POST['potenciaGerador']) : 0;
    $componentes = isset($_POST['componentes']) ? $_POST['componentes'] : '';
    $potenciaModulo = isset($_POST['potenciaModulo']) ? floatval($_POST['potenciaModulo']) : 0;
    $numeroDeFases = isset($_POST['numeroDeFases']) ? $_POST['numeroDeFases'] : '';
    $precoKit = isset($_POST['precoKit']) ? floatval($_POST['precoKit']) : 0;
    $irradiacao = isset($_POST['irradiacao']) ? $_POST['irradiacao'] : '';
    $marca = isset($_POST['marca']) ? $_POST['marca'] : '';
    $fabricante = isset($_POST['fabricante']) ? $_POST['fabricante'] : '';
    $potenciaInversor = isset($_POST['potenciaInversor']) ? floatval($_POST['potenciaInversor']) : 0;
    $padrao = isset($_POST['padrao']) ? $_POST['padrao'] : '';
    $desconto = isset($_POST['desconto']) ? $_POST['desconto'] : '';
    $inputConcessionaria = isset($_POST['inputConcessionaria']) ? $_POST['inputConcessionaria'] : '';
    $inputValorCompensavel = isset($_POST['inputValorCompensavel']) ? floatval($_POST['inputValorCompensavel']) : 0;
    $multiplicador = isset($_POST['multiplicador']) ? intval($_POST['multiplicador']) : 1;
    $quantidadePlacas = isset($_POST['quantidadePlacas']) ? intval($_POST['quantidadePlacas']) : 0;
    $estrutura = isset($_POST['estrutura']) ? $_POST['estrutura'] : '';
    $opcao_adicional = isset($_POST['opcao_adicional']) ? $_POST['opcao_adicional'] : '';
    $usina = isset($_POST['usina']) ? $_POST['usina'] : '';
    $tensaoSaida = isset($_POST['tensaoSaida']) ? $_POST['tensaoSaida'] : '';
    $adicionalAPlus = isset($_POST['adicionalAPlus']) ? trim($_POST['adicionalAPlus']) : '';
    $adicionalBrita = !empty($_POST['adicionalBrita']);
    $adicionalGrade = !empty($_POST['adicionalGrade']);
    $adicionalAlambrado = !empty($_POST['adicionalAlambrado']);
    $adicionalIndicacao = isset($_POST['adicionalIndicacao']) ? trim($_POST['adicionalIndicacao']) : '';
    $geracao = isset($_POST['geracaoKwhMes']) ? floatval($_POST['geracaoKwhMes']) : 0;
    $vendedor = isset($_POST['vendedor']) ? $_POST['vendedor'] : '';

    // 1. Cria um array para armazenar os textos dos itens selecionados
$adicionais_seguranca_inclusos = [];

// 2. Verifica cada variável e adiciona o texto correspondente ao array
if ($adicionalBrita) {
    $adicionais_seguranca_inclusos[] = 'BRITA';
}
if ($adicionalGrade) {
    $adicionais_seguranca_inclusos[] = 'GRADE';
}
if ($adicionalAlambrado) {
    $adicionais_seguranca_inclusos[] = 'ALAMBRADO';
}

// 3. Monta a string final com base na quantidade de itens no array
$texto_seguranca = '';
$total_adicionais_seguranca = count($adicionais_seguranca_inclusos);

if ($total_adicionais_seguranca > 0) {
    if ($total_adicionais_seguranca == 1) {
        // Se houver apenas 1 item
        $texto_seguranca = 'INCLUSO ' . $adicionais_seguranca_inclusos[0];
    } else {
        // Se houver 2 ou mais itens, prepara para usar vírgula e "E"
        $ultimo_item = array_pop($adicionais_seguranca_inclusos); // Pega o último item
        $primeiros_itens = implode(', ', $adicionais_seguranca_inclusos); // Junta os outros com vírgula
        $texto_seguranca = 'INCLUSO ' . $primeiros_itens . ' E ' . $ultimo_item;
    }
}
// =======================================================================
// FIM DO NOVO CÓDIGO
// =======================================================================

    // 1. Captura as 4 variáveis individuais. A verificação !empty retorna true/false.
    $adicional_padrao_selecionado       = !empty($_POST['adicionalPadrao']);
    $adicional_sala_tecnica_selecionado = !empty($_POST['adicionalSalaTecnica']);
    $adicional_cocamar_selecionado      = !empty($_POST['adicionalCocamar']);

    // 2. Monta as strings de texto baseadas nas variáveis booleanas
    $texto_padrao_e_sala = '';
    $texto_cocamar = '';
    $adicionais_inclusos = [];

    if ($adicional_padrao_selecionado) {
        $adicionais_inclusos[] = 'PADRÃO DE ENERGIA INCLUSO';
    }
    if ($adicional_sala_tecnica_selecionado) {
        $adicionais_inclusos[] = 'SALA TÉCNICA PARA INVERSOR';
    }

    if (!empty($adicionais_inclusos)) {
        $texto_padrao_e_sala = '' . implode(' E ', $adicionais_inclusos) . '.';
    }
    if ($adicional_cocamar_selecionado) {
        $texto_cocamar = 'Liberado Cocamar.';
    }

$texto_final_adicionais = trim($texto_padrao_e_sala . ' ' . $texto_cocamar);

// =======================================================================
// NOVO CÓDIGO PARA CALCULAR A POTÊNCIA DO MÓDULO
// =======================================================================
$potModuloCalculado = 0; // Inicia com 0 para segurança
if ($qtdmodulosArredondado > 0) {
    // Divide a potência total em Watts pelo número de placas
    $potModuloCalculado = round(($potenciaGerador * 1000) / $qtdmodulosArredondado); 
}
    class DataProcessor {
        /**
         * Processa valores monetários removendo formatação
         * @param string $valor Valor monetário formatado (ex: R$ 1.234,56)
         * @return float Valor processado (1234.56)
         */
        public static function tratarValorMonetario($valor) {
            if (empty($valor)) return 0.00;
            $valor = preg_replace('/[R$\s.]/', '', $valor);
            $valor = str_replace(',', '.', $valor);
            return round(floatval($valor), 2);
        }

        /**
         * Processa valores percentuais removendo símbolo
         * @param string $valor Valor percentual formatado (ex: 10,5%)
         * @return float Valor processado (10.5)
         */
        public static function tratarPercentual($valor) {
            if (empty($valor)) return 0.00;
            $valor = str_replace(['%', ' '], '', $valor);
            return round(floatval(str_replace(',', '.', $valor)), 4);
        }

        /**
         * Valida dados técnicos do sistema
         * @param array $dados Array com dados técnicos
         * @return bool True se válido, Exception caso contrário
         */
        public static function validarDadosTecnicos($dados) {
            $camposNumericos = ['potencia_gerador', 'quantidade_placas', 'geracao_arredondado'];
            foreach ($camposNumericos as $campo) {
                if (isset($dados[$campo]) && !is_numeric($dados[$campo])) {
                    throw new Exception("Valor inválido para {$campo}");
                }
            }
            return true;
        }
    }
        // Função para verificar e ajustar valores
// Função para verificar e ajustar valores
function verificarValor($valor) {
    // Se o valor for 0, mantenha 0, mas se for estritamente 1, use 1. Para outros, use o valor.
    // Isso evita que um valor 0 se torne 1, o que pode quebrar cálculos.
    return $valor == 0 ? 0 : ($valor == 1 ? 1 : $valor);
}
function verificarValor2($valor) {
    // Se o módulo for 0, retorna 1 para evitar divisão por zero.
    return $valor == 0 ? 1 : $valor;
}
// Ajustes necessários para potenciaGerador e potenciaModulo
$potenciaGerador = verificarValor($potenciaGerador);
$potenciaModulo = verificarValor2($potenciaModulo);

    if (empty($multiplicador)) {
        $multiplicador = 1; // Valor padrão atribuído
    }
    $potenciaInversorUnitario = $potenciaInversor;

    $estrutura = strtoupper($estrutura);

    if ($estrutura == 'TELHADO') {
        $m2placa = 3.1;
    } elseif ($estrutura == 'SOLO') {
        $m2placa = 7.8;
    } else {
        // É boa prática definir um valor padrão caso não seja nenhum dos dois
        $m2placa = 0;
    }
    $precoPlaca = 0;
    $custoEstrutrura = 0;
    $maoObraSolo = 0;
    $potenciaGerador = $potenciaGerador * $multiplicador;
    $potenciaInversor = $potenciaInversor * $multiplicador;
    $precoKit = ($precoKit + $precoPlaca) * $multiplicador;

    //     // NOVO CÓDIGO INSERIDO AQUI
    // // Adicional de custo para projetos em SP com potência específica
    // if (strtoupper($uf) === 'SP' && $potenciaInversor > 75 && $potenciaInversor <= 350) {
    //     $precoKit += 290000.00; // Adiciona o valor de R$ 290.000,00 ao preço do kit
    // }
    // // FIM DO NOVO CÓDIGO
    
    function calcularTributario($potenciaInversor) {
        if ($potenciaInversor <= 75) {
            $tributario = "MEI";
        } elseif ($potenciaInversor > 75 && $potenciaInversor <= 350) {
            $tributario = "SIMPLES NACIONAL 7,3%";
        } elseif ($potenciaInversor > 350 && $potenciaInversor <= 720) {
            $tributario = "SIMPLES NACIONAL 9,5%";
        } elseif ($potenciaInversor > 720) {
            $tributario = "LUCRO PRESUMIDO";
        } else {
            $tributario = false; // Este caso não deve ser alcançado com base na lógica.
        }
    
        return $tributario;
    }
    $tributario = calcularTributario($potenciaInversor);

    $precoDemanda = 50; // Preço da demanda
    $qtdDemanda = 3; // Quantidade de demanda

    function calcularDemanda($potenciaInversor, $precoDemanda, $qtdDemanda, $iluminacao, $media) {
        // Valores fixos definidos
        $AA12 = 1; // Equivalente a 'PLANILHA RESUMO'!AA12
        $TUSDG = 8.6760; // Equivalente a 'PLANILHA RESUMO'!R26
        $Q77 = 0; // Valor de Q77 não foi especificado, ajuste conforme necessário
        $S77 = 0; // Valor de S77 não foi especificado, ajuste conforme necessário
    
        // Cálculo principal
        if ($AA12 * $potenciaInversor <= 75) {
            $resultado = ($media * 0.83) + $iluminacao;
        } else {
            $resultado = ($potenciaInversor * $AA12 * $TUSDG) + $S77;
        }
    
        // Subtrações
        $demanda = $resultado - ($precoDemanda * $qtdDemanda);
    
        return $demanda;
    }
    
    $demanda = abs(calcularDemanda($potenciaInversor, $precoDemanda, $qtdDemanda, $iluminacao, $media));

    // Cálculos iniciais da proposta
    $qtdmodulos = ($potenciaGerador*1000)/$potenciaModulo;
    $qtdmodulosArredondado = (round($qtdmodulos));
    $metrosOcupados = ($qtdmodulosArredondado * $m2placa)* $multiplicador;

    // Cálculos PGCV4
    $peso = ($qtdmodulosArredondado * 33)* $multiplicador;
if ($media > 0) {
    $percentualSolar = ($geracao / $media) * 100;
} else {
    $percentualSolar = 0; // Define como 0 se a média for 0 para evitar erro.
}
    $percentualSolarArredondado = round($percentualSolar);
    $mediaArredondado = round($media);
    $geracaoArredondado = round($geracao);
    $geracaoAnual = $geracaoArredondado * 12;

    function calcularManutencao($qtdmodulosArredondado) {
        if ($qtdmodulosArredondado >= 10) {
            $manutencao = (150 / pow($qtdmodulosArredondado, 0.485) + 10 - 20 / $qtdmodulosArredondado) * $qtdmodulosArredondado;
        } else {
            $manutencao = (150 / pow(10, 0.485) + 10 - 20 / 10) * 10;
        }
    
        return $manutencao / 12; // Divide o resultado por 12 conforme a fórmula original
    }

    $manutencao = calcularManutencao($qtdmodulosArredondado);

    //Cálculos PGCV5
    $demandaMinima = 0; // Inicializa a variável

    if ($numeroDeFases == 'mono rural') {
        $demandaMinima = 1;
    } elseif ($numeroDeFases == 'monofasico') {
        $demandaMinima = 30;
    } elseif ($numeroDeFases == 'bifasico') {
        $demandaMinima = 50;
    } elseif ($numeroDeFases == 'trifasico') {
        $demandaMinima = 100;
    }

    $gastoSemGerador = abs(($demandaMinima * 0.81) + $iluminacao + ($media * 0.81));
    $gastoSemGeradorRs = 'R$ ' . number_format($gastoSemGerador, 2, ',', '.');
    $gastoSemGeradorAno = $gastoSemGerador * 12;
    $gastoSemGeradorAnoRs = 'R$ ' . number_format($gastoSemGeradorAno, 2, ',', '.');
    $gastoComGerador = abs(($demandaMinima * 0.81) + $iluminacao);
    $gastoComGeradorRs = 'R$ ' . number_format($gastoComGerador, 2, ',', '.');
    $gastoComGeradorAno = $gastoComGerador * 12;
    $gastoComGeradorAnoRs = 'R$ ' . number_format($gastoComGeradorAno, 2, ',', '.');
    $diferencaGastos = $gastoSemGerador - $gastoComGerador;
    $diferencaGastosRs = 'R$ ' . number_format($diferencaGastos, 2, ',', '.');
    $diferencaGastosAno = $diferencaGastos * 12;
    $diferencaGastosAnoRs = 'R$ ' . number_format($diferencaGastosAno, 2, ',', '.');

function calcularParcela_corrigido($taxa, $nper, $vp, $vf = 0, $tipo = 0) {
    // Define uma alta escala de precisão para os cálculos intermediários
    bcscale(10);

    // Converte todas as entradas para strings para uso com BCMath
    $taxa_str = (string)$taxa;
    $nper_str = (string)abs($nper); // Usa o valor absoluto, pois o número de períodos deve ser positivo
    $vp_str   = (string)$vp;
    $vf_str   = (string)$vf;

    // Se a taxa for 0, o cálculo é uma simples divisão
    if (bccomp($taxa_str, '0') == 0) {
        $soma_valores = bcadd($vp_str, $vf_str);
        return bcdiv(bcmul($soma_valores, '-1'), $nper_str, 2);
    }

    // Implementação da fórmula da Tabela Price (PMT) com BCMath
    // Fórmula: P = (PV * i * (1+i)^n) / ((1+i)^n - 1)

    // 1. (1 + i)
    $um_mais_i = bcadd('1', $taxa_str);

    // 2. (1 + i)^n
    $fator_potencia = bcpow($um_mais_i, $nper_str);

    // 3. Numerador: (PV * i * (1+i)^n)
    $numerador_parcial = bcmul($vp_str, $taxa_str);
    $numerador = bcmul($numerador_parcial, $fator_potencia);

    // 4. Denominador: ((1+i)^n - 1)
    $denominador = bcsub($fator_potencia, '1');

    // 5. Cálculo da parcela: Numerador / Denominador
    $valorParcela = bcdiv($numerador, $denominador, 2); // Arredonda para 2 casas decimais no final

    // Retorna o valor negativo, como na função original
    return bcmul($valorParcela, '-1', 2);
}

    $precoFinal =($precoKit ) ;
    // Adicional de custo para projetos em SP com potência específica (adicionado ao valor final)
// if (strtoupper($uf) === 'SP' && $potenciaInversor > 75 && $potenciaInversor <= 350) {
//     $precoFinal += 471544.71;
// }

    $descrição2 = "";
    $descrição3 = "";
    $descrição4 = "";
    $descrição5 = "";


    $precoFinalRs = 'R$. ' . number_format($precoFinal, 2, ',', '.');
// CÓDIGO NOVO (CORRETO) A SER INSERIDO NO LUGAR
// --- Exemplo de uso com suas variáveis ---
$taxa = 0.015; // Taxa de juros mensal (1.5%)
$vp = $precoFinal;   // Valor presente do empréstimo (USA O PREÇO FINAL CALCULADO)
$vf = 0;       // Valor futuro
$tipo = 0;     // Tipo

// Períodos (agora positivos)
$nper1 = 36;
$nper2 = 48;
$nper3 = 60;

// Cálculo para 36 meses (chamando a nova função)
$valorParcela = calcularParcela_corrigido($taxa, $nper1, $vp, $vf, $tipo);
$valorParcelaRs = 'R$ '. number_format(abs((float)$valorParcela), 2, ',', '.');

// Cálculo para 48 meses
$valorParcela2 = calcularParcela_corrigido($taxa, $nper2, $vp, $vf, $tipo);
$valorParcela2Rs = 'R$ '. number_format(abs((float)$valorParcela2), 2, ',', '.');

// Cálculo para 60 meses
$valorParcela3 = calcularParcela_corrigido($taxa, $nper3, $vp, $vf, $tipo);
$valorParcela3Rs = 'R$ '. number_format(abs((float)$valorParcela3), 2, ',', '.');
  
if ($diferencaGastosAno > 0) {
    $payback = $precoFinal / $diferencaGastosAno;
    $paybackArredondado = round($payback);
} else {
    $payback = 0;
    $paybackArredondado = 0;
}

    $retornoVerde = $geracao * $inputValorCompensavel;
    $retornoAmarelo = $geracao * $bandeiraAmarela;
    $retornoVermelho = $geracao * $bandeiraVermelha;
    $retornoVermelhoP1 = $geracao * $bandeiraVermelhaP1;
    $bandeiraAmarela = $inputValorCompensavel + 0.01885;
    $bandeiraVermelha = $inputValorCompensavel + 0.04463;
    $bandeiraVermelhaP1 = $inputValorCompensavel + 0.07877;

    function calcularImposto($tributario, $retornoVerde) {
        $imposto = 0; // Inicializa a variável para evitar erros
    
        switch ($tributario) {
            case "MEI":
                $imposto = 81.9;
                break;
            case "SIMPLES NACIONAL 7,3%":
                $imposto = $retornoVerde * 0.073;
                break;
            case "SIMPLES NACIONAL 9,5%":
                $imposto = $retornoVerde * 0.095;
                break;
            case "LUCRO PRESUMIDO":
                $imposto = $retornoVerde * 0.113;
                break;
            default:
                $imposto = false; // Caso o valor de $tributario não seja reconhecido
                break;
        }
    
        return $imposto;
    }
    $imposto = calcularImposto($tributario, $retornoVerde);
    $seguro = ($precoFinal * 0.012) / 12;

    //Cálculos investidor
    $retornoVerde = $geracao * $inputValorCompensavel;
    $retornoAmarelo = $geracao * $bandeiraAmarela;
    $retornoVermelho = $geracao * $bandeiraVermelha;
    $retornoVermelhoP1 = $geracao * $bandeiraVermelhaP1;
    $bandeiraAmarela = $inputValorCompensavel + 0.01885;
    $bandeiraVermelha = $inputValorCompensavel + 0.04463;
    $bandeiraVermelhaP1 = $inputValorCompensavel + 0.07877;
    $liquidoVerde = $retornoVerde - ($seguro + $manutencao + $imposto + $demanda);
    $liquidoAmarelo = $retornoAmarelo - $seguro - $manutencao - $imposto - $demanda;
    $liquidoVermelho = $retornoVermelho - $seguro - $manutencao - $imposto - $demanda;
    $liquidoVermelhoP1 = $retornoVermelhoP1 - $seguro - $manutencao - $imposto - $demanda;
if ($precoFinal > 0) {
    $rentabilidadeVerde = ($liquidoVerde / $precoFinal) * 100;
    $rentabilidadeAmarela = ($liquidoAmarelo / $precoFinal) * 100;
    $rentabilidadeVermelha = ($liquidoVermelho / $precoFinal)* 100;
    $rentabilidadeVermelhaP1 = ($liquidoVermelhoP1 / $precoFinal) * 100;
} else {
    $rentabilidadeVerde = 0;
    $rentabilidadeAmarela = 0;
    $rentabilidadeVermelha = 0;
    $rentabilidadeVermelhaP1 = 0;
}

    
    $irradiacao = [5888, 5792, 5219, 4544, 3636, 3333, 3529, 4451, 4683, 5311, 5969, 6327];

    //Cálculo de irradiação
    $jan = $irradiacao[0];
    $fev = $irradiacao[1];
    $mar = $irradiacao[2];
    $abr = $irradiacao[3];
    $mai = $irradiacao[4];
    $jun = $irradiacao[5];
    $jul = $irradiacao[6];
    $ago = $irradiacao[7];
    $set = $irradiacao[8];
    $out = $irradiacao[9];
    $nov = $irradiacao[10];
    $dez = $irradiacao[11];

    $jan1 = $jan * 1.076687117 / 5265 * 4 * 0.95;
    $fev1 = $fev * 1.076687117 / 5265 * 4 * 0.95;
    $mar1 = $mar * 1.076687117 / 5265 * 4 * 0.95;
    $abr1 = $abr * 1.076687117 / 5265 * 4 * 0.95;
    $mai1 = $mai * 1.076687117 / 5265 * 4 * 0.95;
    $jun1 = $jun * 1.076687117 / 5265 * 4 * 0.95;
    $jul1 = $jul * 1.076687117 / 5265 * 4 * 0.95;
    $ago1 = $ago * 1.076687117 / 5265 * 4 * 0.95;
    $set1 = $set * 1.076687117 / 5265 * 4 * 0.95;
    $out1 = $out * 1.076687117 / 5265 * 4 * 0.95;
    $nov1 = $nov * 1.076687117 / 5265 * 4 * 0.95;
    $dez1 = $dez * 1.076687117 / 5265 * 4 * 0.95;

    $jan2 = $jan1 * $potenciaGerador * 30;
    $fev2 = $fev1 * $potenciaGerador * 30;
    $mar2 = $mar1 * $potenciaGerador * 30;
    $abr2 = $abr1 * $potenciaGerador * 30;
    $mai2 = $mai1 * $potenciaGerador * 30;
    $jun2 = $jun1 * $potenciaGerador * 30;
    $jul2 = $jul1 * $potenciaGerador * 30;
    $ago2 = $ago1 * $potenciaGerador * 30;
    $set2 = $set1 * $potenciaGerador * 30;
    $out2 = $out1 * $potenciaGerador * 30;
    $nov2 = $nov1 * $potenciaGerador * 30;
    $dez2 = $dez1 * $potenciaGerador * 30;

    $jan3 = number_format($jan2 / 1000, 3, '.', '');
    $fev3 = number_format($fev2 / 1000, 3, '.', '');
    $mar3 = number_format($mar2 / 1000, 3, '.', '');
    $abr3 = number_format($abr2 / 1000, 3, '.', '');
    $mai3 = number_format($mai2 / 1000, 3, '.', '');
    $jun3 = number_format($jun2 / 1000, 3, '.', '');
    $jul3 = number_format($jul2 / 1000, 3, '.', '');
    $ago3 = number_format($ago2 / 1000, 3, '.', '');
    $set3 = number_format($set2 / 1000, 3, '.', '');
    $out3 = number_format($out2 / 1000, 3, '.', '');
    $nov3 = number_format($nov2 / 1000, 3, '.', '');
    $dez3 = number_format($dez2 / 1000, 3, '.', '');


function calcularVPL_variavel($investimento, $fluxosDeCaixa, $taxaDesconto) {
    $vpl = -$investimento;
    foreach ($fluxosDeCaixa as $ano => $fluxo) {
        $vpl += $fluxo / pow(1 + $taxaDesconto, $ano + 1);
    }
    return $vpl;
}

/**
 * Calcula a Taxa Interna de Retorno (TIR) para um fluxo de caixa VARIÁVEL.
 */
function calcularTIR_variavel($investimento, $fluxosDeCaixa, $maxIteracoes = 1000, $precisao = 1e-7) {
    $taxaMin = -0.99;
    $taxaMax = 1.0;
    for ($i = 0; $i < $maxIteracoes; $i++) {
        $taxaMedia = ($taxaMin + $taxaMax) / 2;
        if (abs($taxaMedia) < $precisao) $taxaMedia = $precisao;
        $vplCalculado = calcularVPL_variavel($investimento, $fluxosDeCaixa, $taxaMedia);
        if (abs($vplCalculado) < $precisao) {
            return $taxaMedia;
        }
        if ($vplCalculado > 0) {
            $taxaMin = $taxaMedia;
        } else {
            $taxaMax = $taxaMedia;
        }
    }
    return ($taxaMin + $taxaMax) / 2;
}

/**
 * Calcula o Retorno sobre o Investimento (ROI) para todo o período.
 */
function calcularROI($investimento, $ganhoLiquidoTotal) {
    if ($investimento == 0) return 0;
    return ($ganhoLiquidoTotal - $investimento) / $investimento;
}

/**
 * Calcula a "Taxa de Lucratividade" (Margem Líquida Média).
 */
function calcularTaxaLucratividade($receitaMedia, $liquidoMedio) {
    if ($receitaMedia == 0) return 0;
    return $liquidoMedio / $receitaMedia;
}

/**
 * =======================================================================
 * CÁLCULO CENTRALIZADO DAS MÉTRICAS FINANCEIRAS
 * =======================================================================
 */

$investimentoInicial = $precoFinal;
$periodoAnos = 25;

$mediaLiquidoMensal = ($liquidoVerde + $liquidoAmarelo + $liquidoVermelho + $liquidoVermelhoP1) / 4;
$fluxoCaixaPrimeiroAno = $mediaLiquidoMensal * 12;

// --- PARÂMETROS PARA AJUSTE ---
// O PDF não informa estas taxas, então são hipóteses para você ajustar.
$taxaCrescimentoAnualFluxoCaixa = 0.00; // Altere este valor para alinhar com o PDF!
$taxaMinimaAtratividade = 0.10;          // TMA de 10% a.a. para o cálculo do VPL.

$fluxosDeCaixaAnuais = [];
$ganhoLiquidoTotalPeriodo = 0;
for ($ano = 0; $ano < $periodoAnos; $ano++) {
    $fluxoDoAno = $fluxoCaixaPrimeiroAno * pow(1 + $taxaCrescimentoAnualFluxoCaixa, $ano);
    $fluxosDeCaixaAnuais[] = $fluxoDoAno;
    $ganhoLiquidoTotalPeriodo += $fluxoDoAno;
}

// Execução das funções financeiras com os dados corretos
$vpl = calcularVPL_variavel($investimentoInicial, $fluxosDeCaixaAnuais, $taxaMinimaAtratividade);
$tir = calcularTIR_variavel($investimentoInicial, $fluxosDeCaixaAnuais);
$roi = calcularROI($investimentoInicial, $ganhoLiquidoTotalPeriodo);

$receitaMediaMensal = ($retornoVerde + $retornoAmarelo + $retornoVermelho + $retornoVermelhoP1) / 4;
$taxaLucratividade = calcularTaxaLucratividade($receitaMediaMensal, $mediaLiquidoMensal);

// Formatação dos resultados para exibição no PDF
$VPL_formatado = 'R$ ' . number_format($vpl, 2, ',', '.');
$TIR_formatado = number_format($tir * 100, 2, ',', '.') . '%';
$ROI_formatado = number_format($roi * 100, 2, ',', '.') . '%';
$TaxaLucratividade_formatada = number_format($taxaLucratividade * 100, 2, ',', '.') . '%';


    // Data atual
    $formatoData = 'd/m/Y';
    $dataAtual = date($formatoData);


    // Criação do PDF
    $pdf = new TCPDF();
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Primeira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV1.png', 0, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à primeira página
    $pdf->SetFont('helvetica', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(34.2, 98, "Nome: $nome");
    $pdf->Text(34.2, 104, "Endereço: $endereco");
    $pdf->Text(34.2, 110, "Cidade: $cidade");

    $pdf->Text(34.6, 160, "Disponibilidade de área em metros²: $metrosOcupados m²");
    $pdf->Text(34.6, 166.25, "Quantidade de Módulos Fotovoltáicos: $qtdmodulosArredondado Placas");
    $pdf->Text(34.6, 172.5, "Potência do Projeto: $potenciaGerador kWp");
    $pdf->Text(34.6, 178.75, "Geração Estimada: $geracao kWh");

    if (!empty($vendedor)) {
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Text(34.6, 274, "Vendedor: $vendedor");
        $pdf->SetTextColor(0, 0, 0);
    }
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text(34.6, 280, "$dataAtual");

    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Text(34.6, 286, "Este orçamento tem validade de 7 dias.");
    $pdf->SetTextColor(0, 0, 0);

    // Segunda Página (com a imagem genérica e gráfico)
    $pdf->AddPage();  // Adiciona a segunda página
    $pdf->Image('PGCV2.png', 0, 0, 210, 297);
    $pdf->SetMargins(0, 0, 0); // Remove as margens esquerda, superior e direita
    $pdf->SetAutoPageBreak(FALSE); // Desativa a quebra automática de página

    // Terceira Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV3.png', 0, 0, 210, 297);

    // Definir fonte e adicionar conteúdo à terceira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Quarta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV4.png', 0, 0, 210, 297);
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->SetTextColor(255, 0, 0);

    
    // Definir fonte e adicionar conteúdo à terceira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCVEX.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à quarta página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Quinta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV5.png', 0, 0, 210, 297);
    $pdf->SetFont('helvetica', 'B', 13.5);
    $pdf->SetTextColor(50, 50, 50);

    $componentes = html_entity_decode($componentes);
    $componentes = str_replace(
        ["<\/th>", "<\/td>", "<\/tr>", "<\/table>"], 
        ["</th>", "</td>", "</tr>", "</table>"], 
        $componentes
    );
    
    // Extrair os dados da tabela
    preg_match_all('/<td>\s*(.?)\s<\/td>\s*<td>\s*(.?)\s<\/td>\s*<td>\s*(.?)\s<\/td>/', $componentes, $matches, PREG_SET_ORDER);
    
// Posição Y inicial e altura da linha (permanecem os mesmos)
$y = 66;
$linhaAltura = 8;
$maxY = 280;

// 1. DEFINIÇÃO DAS MARGENS E POSIÇÕES (baseado no seu código)
$margemEsquerda = 16;  // Você começa a escrever em X = 16
$margemDireita = 10;   // Uma margem segura de 10mm no lado direito
$posicaoXDescricao = 27; // A descrição começa na posição X = 27

// 2. CÁLCULO DA LARGURA DA COLUNA DE QUANTIDADE
// A quantidade vai da margem esquerda (16) até o início da descrição (27)
$larguraQuantidade = $posicaoXDescricao - $margemEsquerda; // Resultado será 11

// 3. CÁLCULO DA LARGURA DA COLUNA DE DESCRIÇÃO
// A largura da descrição é a largura total da página (210mm para A4)
// menos a posição onde a descrição começa, menos a margem direita.
$larguraDescricao = $pdf->GetPageWidth() - $posicaoXDescricao - $margemDireita; // Ex: 210 - 27 - 10 = 173

// ========================================================
    // Função para adicionar uma nova página se necessário
    function verificaQuebraPagina($pdf, $y, $linhaAltura, $maxY) {
        if ($y + $linhaAltura > $maxY) {
            $pdf->AddPage(); // Adiciona uma nova página
            return 10; // Reseta a posição Y após a nova página
        }
        return $y;
    }
    
    // Escrever os dados extraídos no PDF
    if (empty($matches)) {
        if (strtoupper($estrutura) == 'SOLO') {
            $descricaoEstrutura = "ESTRUTURA DE SOLO BIPOSTE AÇO GALVANIZADO";
        } elseif (strtoupper($estrutura) == 'TELHADO') {
            // Quando você decidir a descrição para telhado, coloque aqui.
            // Por enquanto, vou colocar um exemplo:
            $descricaoEstrutura = "ESTRUTURA PARA TELHADO";
        } else {
            // Opcional: Uma descrição padrão caso a variável não seja nem SOLO nem TELHADO
            $descricaoEstrutura = "ESTRUTURA A DEFINIR";
        }

        // 2. Imprime o bloco de texto, usando a variável que acabamos de definir
        $pdf->Text(16, $y + 3, "$qtdmodulosArredondado MODULOS FOTOVOLTÁICO AESOLAR/ZNSHINE/SINE/RONMA $potenciaModulo/620 W");
        $pdf->Text(16, $y + 12, "$multiplicador INVERSOR SOLAR CHINT/SAJ/GROWATT $fabricante DE $potenciaInversorUnitario KW");
        $qtdEstrutrura = number_format(($qtdmodulosArredondado / 4), 0, ',', '.');
        $qtdCabos = number_format(($qtdmodulosArredondado * 2), 0, ',', '.');
        $pdf->Text(16, $y + 20, $qtdEstrutrura . " " . $descricaoEstrutura); // <-- AQUI USAMOS A VARIÁVEL
        $pdf->Text(16, 94, "$qtdCabos CABO SOLAR PV 1.8KVCC 4MM PRETO NBR 16612");
        $pdf->Text(16, 102, "$qtdCabos CABO SOLAR PV 1.8KVCC 4MM VERMELHO NBR 16612");
        $pdf->Text(16, 110, "INSTALAÇÃO / MÃO DE OBRA / EMISSÃO DE ART");
        $pdf->Text(16, 118, "RAMAL DE LIGAÇÃO LIMITADO A 10 METROS (INVERSOR PADRÃO)");
        $pdf->Text(16, 126, "$textoPadrao");
        $pdf->Text(16, 134, "$texto_seguranca");
        $pdf->Text(16, 142, "$texto_final_adicionais");


        // --- Configurações da Fonte (defina antes do loop) ---
    } else {
    // --- Configurações da Fonte ---
    $defaultFontSize = 13.5;
    $minFontSize = 7;
    $fontStyle = 'B';
    $fontFamily = 'helvetica';

    foreach ($matches as $match) {
        $sku = trim($match[1]);
        $quantidade = (trim($match[2])) * $multiplicador;
        $descricao = trim($match[3]);
        
        $y = verificaQuebraPagina($pdf, $y, $linhaAltura, $maxY);

        // --- Impressão da Quantidade ---
        // A fonte da quantidade deve ser a padrão
        $pdf->SetFont($fontFamily, $fontStyle, $defaultFontSize);
        $pdf->SetXY(16, $y); // Simplificado o Y para alinhar melhor
        $pdf->Cell($larguraQuantidade, $linhaAltura, $quantidade, 0, 0, 'L');
        
        // --- Lógica para a Descrição ---
        $currentFontSize = $defaultFontSize;
        $pdf->SetFont($fontFamily, $fontStyle, $currentFontSize);
        
        // Loop de ajuste de fonte
        while ($pdf->GetStringWidth($descricao) > $larguraDescricao && $currentFontSize > $minFontSize) {
            $currentFontSize -= 0.5;
            $pdf->SetFont($fontFamily, $fontStyle, $currentFontSize);
        }
        
        // Escreve a descrição com a fonte já ajustada
        $pdf->SetXY(27, $y);
        // O último parâmetro '1' move o cursor para a próxima linha automaticamente
        $pdf->Cell($larguraDescricao, $linhaAltura, $descricao, 0, 1, 'L'); 
        
        // Atualiza a posição Y para o próximo item
        $y = $pdf->GetY();
    }
    // Garante que a fonte volte ao normal para qualquer coisa escrita DEPOIS do loop
    $pdf->SetFont($fontFamily, $fontStyle, $defaultFontSize);
}

    if (!empty($adicionalAPlus)) {
        $adicionalAPlus = "*";
    }
        if (!empty($adicionalIndicacao)) {
        $adicionalIndicacao = "*";
    }
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetTextColor(50, 50, 50);

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Text(113, 164, "$adicionalAPlus");
    $pdf->Text(99, 164, "$adicionalIndicacao");
    $pdf->Text(152, 164, "$precoFinalRs");

    $pdf->SetFont('helvetica', 'B', 15);
    $pdf->Text(26, 180, "36 X $valorParcelaRs");
    $pdf->Text(85, 180, "48 X $valorParcela2Rs");
    $pdf->Text(146, 180, "60 X $valorParcela3Rs");

    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Text(59, 46, "$qtdmodulosArredondado");
    $pdf->Text(85, 46, "$potenciaInversor kW");
    $pdf->Text(110.5, 46, "$potenciaGerador kWp");
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

    // Configurações do gráfico
    $xInicial = 17; // Posição X do gráfico
    $yInicial = 228; // Posição Y do gráfico
    $larguraGrafico = 160; // Largura total do gráfico
    $alturaGrafico = 30; // Altura total do gráfico
    $larguraBarra = 5; // Largura de cada barra
    $espacoEntreBarras = 2; // Espaço entre as barras
    $linhaBase = $yInicial + $alturaGrafico; // Posição da linha base (eixo X)

    // Determinar o maior e menor valor
    $min = min($dados);
    $max = max($dados);
    $escalaY = $alturaGrafico / ($max - $min); // Escala de altura por unidade

    // Desenhar eixo X e Y
    $pdf->SetDrawColor(0, 0, 0); // Preto
    $pdf->Line($xInicial, $linhaBase, $xInicial + $larguraGrafico, $linhaBase); // Eixo X
    $pdf->Line($xInicial, $linhaBase - $alturaGrafico, $xInicial, $linhaBase); // Eixo Y

    // Desenhar as barras do gráfico
    $xPos = $xInicial; // Posição inicial no eixo X
    foreach ($dados as $ano => $valor) {
        // Calcular altura da barra
        $barHeight = abs($valor * $escalaY);
    
        // Determinar a posição Y da barra
        if ($valor >= 0) {
            $yBarra = $linhaBase - $barHeight; // Barra positiva
        } else {
            $yBarra = $linhaBase; // Barra negativa
        }
    
        // Desenhar barra
        $pdf->SetFillColor(60, 179, 113); // Verde
        $pdf->Rect($xPos, $yBarra, $larguraBarra, $barHeight, 'DF'); // 'DF' para desenhar e preencher
    
        // Adicionar o ano abaixo da barra
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Text($xPos - 1, $linhaBase + 3, (string)$ano);
    
        // Condição opcional para pular certas barras (exemplo: mostrar só em anos pares)
        if ($ano % 2 == 0) { // Alterna entre anos pares ou um sim, um não
            // Adicionar o valor na barra apenas condicionalmente
            $valorTexto = 'R$ ' . number_format($valor, 0, ',', '.');
            $yTexto = $valor >= 0 ? $yBarra - 5 : $yBarra + $barHeight + 3;
            $pdf->Text($xPos - 2, $yTexto, $valorTexto);
        }
    
        // Avançar posição X
        $xPos += $larguraBarra + $espacoEntreBarras;
    }
    
    // Título do gráfico
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Text($xInicial, $yInicial - 10, '');

    // Definir fonte e adicionar conteúdo à quinta página
    $pdf->SetFont('helvetica', 12);
    $pdf->SetTextColor(0, 0, 0);

    // Sexta Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV6.png', 0, 0, 210, 297);
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

    $pdf->SetFont('helvetica', 10);
    $pdf->Text(27, 220, "$VPL_formatado");
    $pdf->Text(80, 220, "$TIR_formatado");
    $pdf->Text(127, 220, "$TaxaLucratividade_formatada");
    $pdf->Text(172, 220, "$ROI_formatado");
    // CÓDIGO NOVO E CORRIGIDO
    $pdf->Text(80, 98, "Tributação vigente: $tributario");
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Text(19, 203, "Cálculo de acordo com reajuste (24/06)");
    $pdf->SetFont('helvetica', 10);
    $pdf->SetTextColor(0, 0, 0);

    // Dados para o gráfico
    $values = [$retornoVerde, $liquidoVerde, $imposto, $demanda, $seguro, $manutencao];
    $labels = ['Receita', 'Líquido', 'Impostos', 'Demanda', 'Seguro', 'Opex/Limpeza'];

    // Configurações do gráfico
    $x = 27; // Margem inicial
    $y = 240; // Posição vertical inicial
    $barWidth = 15; // Largura das barras
    $maxBarHeight = 30; // Altura máxima das barras
    $gap = 10;
    $pageWidth = 170; // Largura total da área utilizável (A4 menos margens)

    // Ajustar espaçamento entre barras dinamicamente
    $chartWidth = (count($values) * $barWidth); 
    $gap = ($pageWidth - $chartWidth) / (count($values) - 1);

    // Limite superior do gráfico (valor máximo representado)
    $limitValue = max($values) > 0 ? max($values) : 1; // Evitar divisão por zero
    $scalingFactor = $maxBarHeight / $limitValue;

    // Cores das barras
    $colors = [
        [70, 130, 180], // Blue
        [220, 20, 60],  // Red
        [85, 107, 47],  // Green
        [128, 0, 128],  // Purple
        [0, 128, 128],  // Teal
        [255, 165, 0]   // Orange
    ];

    // Desenhar barras
    foreach ($values as $index => $value) {
        $barHeight = $value * $scalingFactor; // Altura da barra proporcional ao valor
        $pdf->SetFillColor($colors[$index][0], $colors[$index][1], $colors[$index][2]);
        $pdf->Rect($x, $y + ($maxBarHeight - $barHeight), $barWidth, $barHeight, 'DF'); // Desenhar barra

        // Adicionar valor acima da barra
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Text($x, $y + ($maxBarHeight - $barHeight) - 7, 'R$' . number_format($value, 2, ',', '.'));

        // Adicionar rótulo abaixo da barra
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Text($x, $y + $maxBarHeight + 5, $labels[$index]);

        // Incrementar posição horizontal
        $x += $barWidth + $gap;
    }
        // Definir fonte e adicionar conteúdo à terceira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // // Quarta Página (com a imagem undo.jpeg)
    // $pdf->AddPage();  // Adiciona a primeira página
    // $pdf->Image('PGCV7.png', 0, 0, 210, 297);
    // $pdf->SetFont('helvetica', 'B', 14);
    // $pdf->SetTextColor(255, 0, 0);


    // Nona Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV9.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à nona página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

 
    // Decima primeira página Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV11.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à Decima primeira página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Decima segunda página Página (com a imagem undo.jpeg)
    $pdf->AddPage();  // Adiciona a primeira página
    $pdf->Image('PGCV12.png', 0, 0, 210, 297);
    
    // Definir fonte e adicionar conteúdo à Decima segunda página
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetTextColor(0, 0, 0);

    // Salva ou exibe o PDF
    $pdf->Output('arquivo_gerado.pdf', 'I');  // 'I' para exibir no navegador

    try {
        // Configurações do Banco de Dados com Segurança Aprimorada
        $dbConfig = [
            'host' => 'srv1781.hstgr.io',
            'dbname' => 'u345670158_propostainv',
            'user' => 'u345670158_eduardotcardo4',
            'pass' => 'Rtz6ngqr@',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        ];
    
        // Estabelece conexão segura
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
        $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $dbConfig['options']);
    

        // Prepara dados para inserção
        $dadosProcessados = [
            // Dados do Cliente
            'nome' => trim($nome),                          // Remove espaços extras
            'endereco' => mb_strtoupper($endereco),         // Padroniza endereço em maiúsculas
            'cidade' => mb_strtoupper($cidade),             // Padroniza cidade em maiúsculas
            'uc' => preg_replace('/[^0-9]/', '', $uc),      // Mantém apenas números da UC
            'usina' => trim($usina), 
            'input_concessionaria' => $inputConcessionaria,
            // Remove espaços extras
            
            // Dados Técnicos (com validação de valores numéricos)
            'media' => floatval($media),
            'iluminacao' => floatval($iluminacao),
            'potencia_gerador' => floatval($potenciaGerador),
            'quantidade_placas' => intval($qtdmodulosArredondado),
            
            // Dados Financeiros (com formatação adequada)
            'preco_final' => round(floatval($precoFinal), 2),
            'desconto' => floatval($desconto),
            'geracao_anual' => round(floatval($geracaoAnual), 2),
            'payback' => round(floatval($paybackArredondado), 2),
            'percentual_solar' => round(floatval($percentualSolarArredondado), 2)
        ];
        
        // Processamento dos valores financeiros e indicadores
        $dadosProcessados += [
            // Retornos por Bandeira Tarifária
            'retorno_verde' => DataProcessor::tratarValorMonetario($retornoVerdeRs),
            'retorno_amarelo' => DataProcessor::tratarValorMonetario($retornoAmareloRs),
            'retorno_vermelho' => DataProcessor::tratarValorMonetario($retornoVermelhoRs),
            'retorno_vermelho_p1' => DataProcessor::tratarValorMonetario($retornoVermelhoP1Rs),
            
            // Indicadores de Rentabilidade
            'rentabilidade_verde' => DataProcessor::tratarPercentual($rentabilidadeVerdeRs),
            'rentabilidade_amarela' => DataProcessor::tratarPercentual($rentabilidadeAmarelaRs),
            'rentabilidade_vermelha' => DataProcessor::tratarPercentual($rentabilidadeVermelhaRs),
            'rentabilidade_vermelha_p1' => DataProcessor::tratarPercentual($rentabilidadeVermelhaP1Rs),
            
            // Custos Operacionais
            'seguro' => DataProcessor::tratarValorMonetario($seguroRs),
            'manutencao' => DataProcessor::tratarValorMonetario($manutencaoRs),
            'imposto' => DataProcessor::tratarValorMonetario($impostoRs),
            'demanda' => DataProcessor::tratarValorMonetario($demandaRs),
            
            // Resultados Líquidos
            'liquido_verde' => DataProcessor::tratarValorMonetario($liquidoVerdeRs),
            'liquido_amarelo' => DataProcessor::tratarValorMonetario($liquidoAmareloRs),
            'liquido_vermelho' => DataProcessor::tratarValorMonetario($liquidoVermelhoRs),
            'liquido_vermelho_p1' => DataProcessor::tratarValorMonetario($liquidoVermelhoP1Rs),
            'media_liquido' => DataProcessor::tratarValorMonetario($mediaLiquidoRs),
            
            // Dados Comerciais
            'margem' => round(floatval($margem), 4),
            'comissao' => round(floatval($comissao), 2),
            'mobra' => round(floatval($mobra), 2),
            'geracao_arredondado' => round(floatval($geracaoArredondado), 2),
            
            // Metadados do Sistema
            'usuario_criacao' => $_SESSION['usuario'] ?? 'sistema',
            'status_projeto' => 'Ativo',
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];
        
    
        // Query de inserção otimizada
        $campos = implode(', ', array_keys($dadosProcessados));
        $valores = ':' . implode(', :', array_keys($dadosProcessados));
        
        $sql = "INSERT INTO resultados_calculados ($campos) VALUES ($valores)";
        $stmt = $pdo->prepare($sql);
    
        // Executa inserção com dados processados
        $stmt->execute($dadosProcessados);
    
        // Registra sucesso
        $mensagem = "Dados armazenados com sucesso! ID: " . $pdo->lastInsertId();
        error_log($mensagem);
    
    } catch (PDOException $e) {
        $erro = "Erro no banco de dados: " . $e->getMessage();
        error_log($erro);
        throw new Exception($erro);
    } catch (Exception $e) {
        $erro = "Erro ao processar dados: " . $e->getMessage();
        error_log($erro);
        throw $e;
}

}
?>