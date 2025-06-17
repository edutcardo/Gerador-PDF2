<?php
// Certifique-se de que o autoload do TCPDF está correto
require_once('vendor/autoload.php');

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");


// --- CONFIGURAÇÕES DE DEBUG E ERRO ---
ini_set('display_errors', 0); // Em produção, idealmente 0
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/gerar_pdf_limpeza_errors.log'); // Log na mesma pasta

error_log("==================================================");
error_log("INÍCIO DA EXECUÇÃO DO SCRIPT PHP: " . date("Y-m-d H:i:s"));
error_log("Método da Requisição: " . $_SERVER['REQUEST_METHOD']);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log("Conteúdo de _POST recebido: " . print_r($_POST, true));
}
error_log("==================================================");

// -----------------------------------------------------------------------------
// --- FUNÇÕES HELPER ---
// -----------------------------------------------------------------------------

/**
 * Fetches and decodes the list of Brazilian municipalities using cURL.
 */
function getMunicipalitiesData() {
    error_log("Helper: getMunicipalitiesData() chamada (usando cURL).");
    $url = 'https://raw.githubusercontent.com/kelvins/municipios-brasileiros/refs/heads/main/json/municipios.json';

    if (!function_exists('curl_init')) {
        error_log("Helper: getMunicipalitiesData (cURL) - ERRO: Extensão cURL não está disponível/habilitada no PHP.");
        return null;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); // Tempo limite de conexão em segundos
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);       // Tempo limite total da requisição em segundos
    curl_setopt($ch, CURLOPT_USERAGENT, "PHP-Proposal-Script/1.0 (+" . (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost') . ")");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Importante para segurança
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);   // Importante para segurança
    // Tentar seguir redirecionamentos pode ser útil se a URL mudar
    // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


    $json_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error_msg = curl_error($ch);

    if ($json_data === false || $http_code != 200) {
        error_log("Helper: getMunicipalitiesData (cURL) - FALHA ao buscar dados. Código HTTP: {$http_code}. Erro cURL: " . $curl_error_msg);
        curl_close($ch);
        return null;
    }
    curl_close($ch);

    error_log("Helper: getMunicipalitiesData (cURL) - Dados JSON buscados (" . strlen($json_data) . " bytes). Código HTTP: {$http_code}.");
    $data = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Helper: getMunicipalitiesData (cURL) - FALHA ao decodificar JSON: " . json_last_error_msg());
        return null;
    }
    error_log("Helper: getMunicipalitiesData (cURL) - Sucesso ao carregar e decodificar dados.");
    return $data;
}

/**
 * Removes accents from a string.
 */
function removeAccents($string) {
    if ($string === null) return null;
    // Tenta usar Normalizer se a classe existir (PHP 5.3+)
    if (class_exists('Normalizer')) {
        error_log("Helper: removeAccents - Usando Normalizer.");
        // Garante que a string é UTF-8 válida antes de normalizar
        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string));
        }
        $normalized = Normalizer::normalize(strtr($string, '’', '\''), Normalizer::FORM_D); // Decomposição Canônica
        if ($normalized === false) {
             error_log("Helper: removeAccents - Normalizer::normalize retornou false para: " . $string);
             // Fallback para strtr se Normalizer falhar (raro, mas possível com entradas malformadas)
        } else {
            return preg_replace('/[\pM]/u', '', $normalized); // Remove marcas diacríticas (Combining Diacritical Marks)
        }
    } else {
        error_log("Helper: removeAccents - Classe Normalizer NÃO encontrada. Usando strtr fallback.");
    }

    // Fallback se Normalizer não estiver disponível ou falhar
    $unwanted_array = array(
        'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
        // Opcional: substituir espaços e barras se necessário para comparação, mas geralmente não para normalização de nomes
        // '/'=>'-', ' '=>'-' 
    );
    return strtr( $string, $unwanted_array );
}


/**
 * Finds coordinates for a given city name and optional state.
 */
function getCityCoordinates($cityName, $municipalitiesData, $stateCode = null) {
    error_log("Helper: getCityCoordinates() chamada para Cidade: '{$cityName}', Estado: '{$stateCode}'");
    if (!$municipalitiesData || empty(trim($cityName))) { // Verifica se cityName não está vazia após trim
        error_log("Helper: getCityCoordinates - ERRO: Dados dos municípios ou nome da cidade vazio/nulo.");
        return null;
    }

    $normalizedCityName = strtoupper(removeAccents(trim($cityName))); // Adicionado trim()
    $normalizedStateCode = $stateCode ? strtoupper(removeAccents(trim($stateCode))) : null; // Adicionado trim()
    error_log("Helper: getCityCoordinates - Buscando por Normalizado: Cidade='{$normalizedCityName}', Estado='{$normalizedStateCode}'");

    foreach ($municipalitiesData as $municipio) {
        if (empty($municipio['nome'])) { // Pular se nome estiver vazio no JSON
            // error_log("Helper: getCityCoordinates - Registro de município com nome vazio encontrado no JSON.");
            continue;
        }

        $currentNormalizedCity = strtoupper(removeAccents($municipio['nome']));
        $currentNormalizedState = isset($municipio['uf']) ? strtoupper(removeAccents($municipio['uf'])) : null;

        if ($currentNormalizedCity == $normalizedCityName) {
            if ($normalizedStateCode === null || ($currentNormalizedState !== null && $currentNormalizedState == $normalizedStateCode)) {
                if (isset($municipio['latitude']) && isset($municipio['longitude']) &&
                    is_numeric($municipio['latitude']) && is_numeric($municipio['longitude'])) { // Verifica se são numéricos
                    error_log("Helper: getCityCoordinates - ENCONTRADO para '{$cityName}' ({$stateCode}): Lat={$municipio['latitude']}, Lon={$municipio['longitude']}");
                    return ['latitude' => floatval($municipio['latitude']), 'longitude' => floatval($municipio['longitude'])];
                } else {
                     error_log("Helper: getCityCoordinates - Nome da cidade '{$currentNormalizedCity}' coincide, mas lat/lon ausentes ou inválidos no JSON. Lat: ".(isset($municipio['latitude'])?$municipio['latitude']:'N/A').", Lon: ".(isset($municipio['longitude'])?$municipio['longitude']:'N/A'));
                }
            }
        }
    }
    error_log("Helper: getCityCoordinates - NÃO ENCONTRADO para Cidade: '{$cityName}', Estado: '{$stateCode}' (Normalizado: '{$normalizedCityName}', Estado Normalizado: '{$normalizedStateCode}')");
    return null;
}

/**
 * Calculates the Haversine distance.
 */
function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    error_log("Helper: haversineDistance() chamada com Lat1:{$lat1}, Lon1:{$lon1}, Lat2:{$lat2}, Lon2:{$lon2}");
    if (!is_numeric($lat1) || !is_numeric($lon1) || !is_numeric($lat2) || !is_numeric($lon2)) {
        error_log("Helper: haversineDistance - ERRO: Pelo menos uma das coordenadas não é numérica.");
        return 0; // Ou null, ou lançar uma exceção
    }
    $earthRadius = 6371; // Raio da Terra em km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;
    error_log("Helper: haversineDistance() - Distância calculada: {$distance} km");
    return $distance;
}

/**
 * Formats payback period.
 */
function format_payback($custo_total, $economia_mensal) {
    error_log("Helper: format_payback() chamada com Custo Total: {$custo_total}, Economia Mensal: {$economia_mensal}");
    if (!is_numeric($custo_total) || !is_numeric($economia_mensal)) {
        error_log("Helper: format_payback - ERRO: Custo total ou economia mensal não são numéricos.");
        return "N/A (Dados inválidos)";
    }
    if ($economia_mensal <= 0) return "N/A (Economia mensal não positiva)";
    if ($custo_total <= 0) return "N/A (Custo não positivo)";
    
    $meses_fracionados = $custo_total / $economia_mensal;
    $meses_inteiros = floor($meses_fracionados);
    $dias_restantes = round(($meses_fracionados - $meses_inteiros) * 30); // Aproximação de 30 dias por mês

    $texto_meses = $meses_inteiros . ($meses_inteiros == 1 ? " Mês" : " Meses"); // Singular/plural
    $texto_dias = "";

    if ($dias_restantes >= 1) {
        $texto_dias = " e " . $dias_restantes . ($dias_restantes == 1 ? " Dia" : " Dias"); // Singular/plural
    }

    if ($meses_inteiros == 0 && $dias_restantes < 1) { // Caso seja menos de 1 dia
        return "Menos de 1 dia";
    }
    if ($meses_inteiros == 0 && $dias_restantes >=1) { // Caso seja apenas dias
        return $dias_restantes . ($dias_restantes == 1 ? " Dia" : " Dias");
    }

    return $texto_meses . $texto_dias;
}

// -----------------------------------------------------------------------------
// --- FIM DAS FUNÇÕES HELPER ---
// -----------------------------------------------------------------------------


// --- LÓGICA PRINCIPAL DO SCRIPT ---

if (class_exists('Normalizer')) {
    error_log("PHP: Classe Normalizer ESTÁ disponível.");
} else {
    error_log("PHP: Classe Normalizer NÃO ESTÁ disponível. Usando fallback strtr para remover acentos.");
}


// AÇÃO 1: VERIFICAR SE É UMA REQUISIÇÃO DE SIMULAÇÃO
if (isset($_POST['acao']) && $_POST['acao'] == 'simular_deslocamento') {
    error_log("PHP: DETECTADA AÇÃO DE SIMULAÇÃO.");
    header('Content-Type: application/json');

    $cidade_input_simulacao = isset($_POST['cidade']) ? $_POST['cidade'] : ''; // Não usar htmlspecialchars aqui ainda
    error_log("PHP Simulação: Cidade recebida para simulação (raw): '{$cidade_input_simulacao}'");

    $resposta_simulacao = [
        'sucesso' => false,
        'custo_deslocamento' => 26.25, // Custo padrão caso algo falhe
        'distancia_km' => 0,
        'mensagem_erro' => 'Falha ao iniciar simulação.',
        'mensagem' => ''
    ];

    if (empty(trim($cidade_input_simulacao))) { // trim para verificar se é só espaço
        $resposta_simulacao['mensagem_erro'] = 'Nome da cidade não fornecido para simulação.';
        error_log("PHP Simulação: ERRO - Nome da cidade vazio.");
    } else {
        $safe_cidade_input_simulacao = htmlspecialchars($cidade_input_simulacao); // Para usar em mensagens de erro
        $municipalitiesData = getMunicipalitiesData();
        if (!$municipalitiesData) {
            $resposta_simulacao['mensagem_erro'] = 'Servidor não conseguiu carregar dados de municípios.';
            error_log("PHP Simulação: ERRO CRÍTICO - Falha ao carregar dados dos municípios via getMunicipalitiesData().");
        } else {
            $maringaCoords = getCityCoordinates("Maringá", $municipalitiesData, "PR");
            if (!$maringaCoords) {
                $resposta_simulacao['mensagem_erro'] = 'Servidor não encontrou coordenadas de Maringá.';
                error_log("PHP Simulação: ERRO CRÍTICO - Falha ao obter coordenadas de Maringá.");
            } else {
                $cidade_nome_destino = $cidade_input_simulacao; // Usar o valor raw para parsing
                $estado_destino = null;
                // Tenta extrair UF se presente (ex: "Nome Cidade / UF", "Nome Cidade - UF", "Nome Cidade UF")
                if (preg_match('/(.+?)[\s\-\/]+([A-Z]{2})$/i', $cidade_nome_destino, $matches)) {
                    $cidade_nome_destino = trim($matches[1]);
                    $estado_destino = strtoupper(trim($matches[2]));
                }
                
                $destinoCoords = getCityCoordinates($cidade_nome_destino, $municipalitiesData, $estado_destino);
                if (!$destinoCoords && $estado_destino) { // Se falhou com UF, tenta sem UF
                    error_log("PHP Simulação: Coordenadas não encontradas para '{$cidade_nome_destino}/{$estado_destino}'. Tentando sem estado.");
                    $destinoCoords = getCityCoordinates($cidade_nome_destino, $municipalitiesData, null);
                }

                if (!$destinoCoords) {
                    $resposta_simulacao['mensagem_erro'] = "Coordenadas para '{$safe_cidade_input_simulacao}' não encontradas.";
                    error_log("PHP Simulação: ERRO CRÍTICO - Coordenadas de destino NÃO encontradas para '{$safe_cidade_input_simulacao}'.");
                } else {
                    $distancia = haversineDistance($maringaCoords['latitude'], $maringaCoords['longitude'], $destinoCoords['latitude'], $destinoCoords['longitude']);
                    $resposta_simulacao['distancia_km'] = round($distancia, 1);
                    error_log("PHP Simulação: Distância calculada: {$distancia} km.");

                    // Parâmetros para cálculo de custo (poderiam vir do frontend ou serem configuráveis)
                    $preco_combustivel_litro = 6.50; 
                    $km_por_litro = 8.00; 
                    $num_viagens_ida_volta = 1; 
                    $custo_adicional_deslocamento_fixo = 10.00; // Taxa fixa adicional

                    if ($km_por_litro > 0) {
                        $custo_combustivel_viagem = ($distancia * 2 * $num_viagens_ida_volta / $km_por_litro) * $preco_combustivel_litro;
                        $custo_total_deslocamento = $custo_combustivel_viagem + $custo_adicional_deslocamento_fixo;
                        $resposta_simulacao['custo_deslocamento'] = round($custo_total_deslocamento, 2);
                        $resposta_simulacao['sucesso'] = true; 
                        $resposta_simulacao['mensagem_erro'] = ''; 
                        $resposta_simulacao['mensagem'] = 'Cálculo de deslocamento OK.';
                        error_log("PHP Simulação: SUCESSO! Custo deslocamento: " . $resposta_simulacao['custo_deslocamento']);
                    } else { 
                        $resposta_simulacao['custo_deslocamento'] = round($custo_adicional_deslocamento_fixo, 2); // Se km/L inválido, usa apenas o fixo
                        $resposta_simulacao['sucesso'] = true; 
                        $resposta_simulacao['mensagem_erro'] = ''; 
                        $resposta_simulacao['mensagem'] = 'Km/L inválido, usado custo fixo de deslocamento.';
                        error_log("PHP Simulação: SUCESSO (com ressalva)! Km/L inválido. Custo deslocamento: " . $resposta_simulacao['custo_deslocamento']);
                    }
                }
            }
        }
    }
    echo json_encode($resposta_simulacao);
    error_log("PHP Simulação: JSON enviado -> " . json_encode($resposta_simulacao));
    error_log("PHP Simulação: CHAMANDO EXIT AGORA.");
    exit; 
}

// AÇÃO 2: SE FOR UMA REQUISIÇÃO POST NORMAL (NÃO SIMULAÇÃO), GERAR PDF
if ($_SERVER["REQUEST_METHOD"] == "POST" && (!isset($_POST['acao']) || (isset($_POST['acao']) && $_POST['acao'] != 'simular_deslocamento'))) {
    error_log("PHP: DETECTADA AÇÃO DE GERAÇÃO DE PDF.");

    $nome = isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : 'N/A';
    $endereco = isset($_POST['endereco']) ? htmlspecialchars($_POST['endereco']) : 'N/A';
    $cidade_input = isset($_POST['cidade']) ? htmlspecialchars($_POST['cidade']) : 'N/A'; // Usar htmlspecialchars para exibição no PDF
    $qtdmodulos = isset($_POST['qtdmodulos']) ? floatval($_POST['qtdmodulos']) : 0;
    $potmodulos_Wp = isset($_POST['potmodulos']) ? floatval($_POST['potmodulos']) : 0;
    $precokwh_input_str = isset($_POST['precokwh']) ? htmlspecialchars($_POST['precokwh']) : '0.80'; // Valor padrão se não enviado
    $precokwh_numeric = floatval(str_replace(',', '.', $precokwh_input_str)); // Converte para float, tratando vírgula
    
    $perdageracao_f6 = 0.15; // F6 da planilha (Perda de geração por sujeira, sombreamento, etc.)
    $fator_solar_mensal_equivalente = 30 * 4; // (30 dias * 4 horas de sol pleno equivalentes por dia)


    // --- LÓGICA DE CÁLCULO DO DESLOCAMENTO (PARA PDF) ---
    $deslocamento = 26.25; // Padrão inicial
    $distancia_calculada_km_pdf = 0; // Para display no PDF se calculado
    $cidade_destino_para_pdf_display = $cidade_input; // Default para cidade da proposta
    $aviso_deslocamento_pdf = ""; // Mensagem para o PDF sobre o deslocamento

    // Verifica se o frontend enviou um cálculo específico
    $usar_deslocamento_frontend = isset($_POST['precisa_deslocamento_calculado']) && $_POST['precisa_deslocamento_calculado'] === 'true';

    if ($usar_deslocamento_frontend) {
        $valor_recebido = isset($_POST['valor_deslocamento_calculado']) ? $_POST['valor_deslocamento_calculado'] : null;
        // Valida se o valor recebido é numérico
        if ($valor_recebido !== null && is_numeric($valor_recebido)) {
            $deslocamento = floatval($valor_recebido);
            error_log("PHP PDF: Usando deslocamento calculado do frontend: R$ " . $deslocamento);
            
            if (isset($_POST['distancia_calculada_frontend_km']) && is_numeric($_POST['distancia_calculada_frontend_km'])) {
                $distancia_calculada_km_pdf = floatval($_POST['distancia_calculada_frontend_km']);
            }
            if (isset($_POST['cidade_destino_calculada_frontend'])) {
                $cidade_destino_para_pdf_display = htmlspecialchars($_POST['cidade_destino_calculada_frontend']);
            } else {
                 $cidade_destino_para_pdf_display = "Destino (calculadora)"; // Fallback
            }
            $aviso_deslocamento_pdf = "(Custo de deslocamento específico: R$ " . number_format($deslocamento, 2, ',', '.');
            if ($distancia_calculada_km_pdf > 0) {
                $aviso_deslocamento_pdf .= " para " . $cidade_destino_para_pdf_display . ", aprox. " . number_format($distancia_calculada_km_pdf, 1, ',', '.') . " km";
            }
            $aviso_deslocamento_pdf .= ")";

        } else {
            error_log("PHP PDF: 'precisa_deslocamento_calculado' era true, mas 'valor_deslocamento_calculado' inválido ou ausente. Usando padrão R$ 26.25.");
            $deslocamento = 26.25; // Fallback para default se valor do frontend for ruim
            $aviso_deslocamento_pdf = "(Cálculo de deslocamento específico solicitado, mas valor inválido. Usado padrão: R$ 26,25)";
        }
    } else {
        // Checkbox estava desmarcado, ou 'precisa_deslocamento_calculado' era 'false' ou não enviado.
        // Tenta calcular dinamicamente com base na cidade da proposta, ou usa o padrão 26.25.
        error_log("PHP PDF: Não usando deslocamento do frontend. Tentando cálculo dinâmico para '{$cidade_input}' ou usando padrão R$ 26.25.");
        $deslocamento = 26.25; // Padrão se não houver cálculo dinâmico bem-sucedido
        $calculo_dinamico_sucesso_pdf = false;
        $raw_cidade_input_pdf = isset($_POST['cidade']) ? $_POST['cidade'] : ''; // Cidade da proposta (raw)

        if (!empty(trim($raw_cidade_input_pdf))) {
            $municipalitiesData_pdf = getMunicipalitiesData(); // Reutiliza a função com cURL
            if ($municipalitiesData_pdf) {
                $maringaCoords_pdf = getCityCoordinates("Maringá", $municipalitiesData_pdf, "PR");
                if($maringaCoords_pdf) {
                    $cidade_nome_destino_pdf = $raw_cidade_input_pdf;
                    $estado_destino_pdf = null;
                    if (preg_match('/(.+?)[\s\-\/]+([A-Z]{2})$/i', $raw_cidade_input_pdf, $matches_pdf)) {
                        $cidade_nome_destino_pdf = trim($matches_pdf[1]);
                        $estado_destino_pdf = strtoupper(trim($matches_pdf[2]));
                    }
                    $destinoCoordsPdf = getCityCoordinates($cidade_nome_destino_pdf, $municipalitiesData_pdf, $estado_destino_pdf);
                    if (!$destinoCoordsPdf && $estado_destino_pdf) { // Tenta sem estado se falhou com
                         $destinoCoordsPdf = getCityCoordinates($cidade_nome_destino_pdf, $municipalitiesData_pdf, null);
                    }

                    if ($destinoCoordsPdf) {
                        $distancia_calculada_km_pdf = haversineDistance($maringaCoords_pdf['latitude'], $maringaCoords_pdf['longitude'], $destinoCoordsPdf['latitude'], $destinoCoordsPdf['longitude']);
                        // Parâmetros fixos para este cálculo (poderiam ser configuráveis)
                        $preco_combustivel_litro_pdf = 6.50; $km_por_litro_pdf = 8.00; $num_viagens_ida_volta_pdf = 1; $custo_adicional_deslocamento_fixo_pdf = 10.00;
                        
                        if($km_por_litro_pdf > 0){
                            $custo_combustivel_viagem_pdf = ($distancia_calculada_km_pdf * 2 * $num_viagens_ida_volta_pdf / $km_por_litro_pdf) * $preco_combustivel_litro_pdf;
                            $deslocamento = round($custo_combustivel_viagem_pdf + $custo_adicional_deslocamento_fixo_pdf, 2);
                            $calculo_dinamico_sucesso_pdf = true;
                            $cidade_destino_para_pdf_display = htmlspecialchars($cidade_nome_destino_pdf . ($estado_destino_pdf ? " - " . $estado_destino_pdf : ""));
                            $aviso_deslocamento_pdf = "(Deslocamento para {$cidade_destino_para_pdf_display}: R$ " . number_format($deslocamento, 2, ',', '.') . ", aprox. " . number_format($distancia_calculada_km_pdf, 1, ',', '.') . " km)";
                        } else {
                            $deslocamento = round($custo_adicional_deslocamento_fixo_pdf, 2); // Usa apenas o fixo se km/L inválido
                            $calculo_dinamico_sucesso_pdf = true; // Sucesso, mas usou lógica alternativa
                            $aviso_deslocamento_pdf = "(Km/L inválido, usado custo fixo de deslocamento para {$cidade_destino_para_pdf_display}: R$ " . number_format($deslocamento, 2, ',', '.') . ")";
                        }
                    } else { // Não encontrou coordenadas para a cidade da proposta
                         $aviso_deslocamento_pdf = "(Não foi possível calcular deslocamento para '{$cidade_input}'. Usado padrão: R$ 26,25)";
                    }
                } else { // Não encontrou coordenadas de Maringá
                     $aviso_deslocamento_pdf = "(Coordenadas de Maringá não encontradas. Usado padrão: R$ 26,25)";
                }
            } else { // Falha ao carregar dados dos municípios
                 $aviso_deslocamento_pdf = "(Dados de municípios indisponíveis. Usado padrão: R$ 26,25)";
            }
        } else { // Cidade da proposta não informada
            $aviso_deslocamento_pdf = "(Cidade da proposta não informada. Usado deslocamento padrão: R$ 26,25)";
        }
         error_log("PHP PDF: Deslocamento (não frontend): {$deslocamento}. Sucesso dinâmico: " . ($calculo_dinamico_sucesso_pdf ? 'SIM' : 'NÃO') . ". Aviso: " . $aviso_deslocamento_pdf);
    }
    // --- FIM DA LÓGICA DE CÁLCULO DO DESLOCAMENTO ---

    error_log("PHP PDF: Valor final de DESLOCAMENTO usado nos cálculos: R$ " . $deslocamento);

    // Cálculo de Economia
    $econMensalSemLimp = ($qtdmodulos / (1 + $perdageracao_f6)) * $potmodulos_Wp * $fator_solar_mensal_equivalente * $precokwh_numeric / 1000;
    $econMensalComLimp = $qtdmodulos * $potmodulos_Wp * $fator_solar_mensal_equivalente * $precokwh_numeric / 1000;
    $econDiferenca = $econMensalComLimp - $econMensalSemLimp; // E11

    // Tabela de Custo por Módulo (ajustar conforme sua planilha)
    $custolimpezamodulo = 0; $custofixo = 0;
    if ($qtdmodulos <= 0) { $custolimpezamodulo = 10; $custofixo = 80; } // Caso base ou erro
    elseif ($qtdmodulos <= 10) { $custolimpezamodulo = 10; $custofixo = 80; }
    elseif ($qtdmodulos <= 30) { $custolimpezamodulo = 9; $custofixo = 110; }
    elseif ($qtdmodulos <= 40) { $custolimpezamodulo = 8; $custofixo = 150; }
    elseif ($qtdmodulos <= 100) { $custolimpezamodulo = 7; $custofixo = 250; }
    elseif ($qtdmodulos <= 150) { $custolimpezamodulo = 6; $custofixo = 400; }
    elseif ($qtdmodulos <= 200) { $custolimpezamodulo = 5.5; $custofixo = 500; }
    elseif ($qtdmodulos <= 300) { $custolimpezamodulo = 5; $custofixo = 650; }
    elseif ($qtdmodulos <= 500) { $custolimpezamodulo = 4.5; $custofixo = 900; }
    else { $custolimpezamodulo = 4; $custofixo = 1200; } // Para mais de 500 módulos


    $k6_comissao_val = 0.05; // Comissão de 5%
    $estrutura_j4 = "TELHADO"; // Exemplo, poderia vir do form
    $fator_estrutura_solo = ($estrutura_j4 == "SOLO" ? 1.0 : 1.0); // Ajustar se houver diferença

    $denominador_comissao = (1 - $k6_comissao_val);
    if ($denominador_comissao == 0) $denominador_comissao = 0.00001; // Evitar divisão por zero
    
    error_log("PHP PDF: Valores para B15: QtdModulos={$qtdmodulos}, CustoLimpezaModulo={$custolimpezamodulo}, CustoFixo={$custofixo}, Deslocamento={$deslocamento}");
    $custototal_B15 = ($qtdmodulos * $custolimpezamodulo + $custofixo + $deslocamento) / $denominador_comissao * $fator_estrutura_solo;
    error_log("PHP PDF: Custo Total B15 Calculado: R$ " . number_format($custototal_B15, 2));

    // Cálculos para pacote de 3 limpezas (exemplo, ajustar conforme sua lógica)
    $f15_calc_raw = ($qtdmodulos * $custolimpezamodulo * 0.85 + $custofixo * 0.85 + $deslocamento) / $denominador_comissao * $fator_estrutura_solo; // Exemplo de desconto
    $f15_text = "R$ " . number_format($f15_calc_raw, 2, ',', '.');
    $g15_calc_raw = $f15_calc_raw * 3; // Custo total do pacote
    $g15_text = "R$ " . number_format($g15_calc_raw, 2, ',', '.');
    $custo3limpezas_text = "3x de " . $f15_text . " = " . $g15_text;

    $payback1limpeza_text = format_payback($custototal_B15, $econDiferenca);
    $payback3limpezas_text = format_payback($f15_calc_raw, $econDiferenca); // Payback considerando o valor de 1 parcela do pacote

    $formatoData = 'd/m/Y'; $dataAtual = date($formatoData);
    $potenciaTotalSistemaKWp = ($qtdmodulos * $potmodulos_Wp) / 1000;

    // --- CRIAÇÃO DO PDF ---
    error_log("PHP PDF: Iniciando criação do objeto TCPDF.");
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR); $pdf->SetAuthor('Canal Verde'); $pdf->SetTitle('Proposta Limpeza de Módulos'); $pdf->SetSubject('Proposta Comercial');
    $pdf->setPrintHeader(false); $pdf->setPrintFooter(false); $pdf->SetMargins(0, 0, 0, true); $pdf->SetAutoPageBreak(FALSE, 0);

    // Adiciona as páginas de imagem de fundo
    $imagens_fundo = ['PL1.png', 'PL2.png', 'PL3.png', 'PL4.png'];
    foreach ($imagens_fundo as $idx => $img_nome) {
        $pdf->AddPage();
        if (file_exists(__DIR__ . '/' . $img_nome)) { // Verifica se a imagem existe na mesma pasta do script
            $pdf->Image(__DIR__ . '/' . $img_nome, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
            error_log("PHP PDF: Imagem de fundo '{$img_nome}' carregada.");
        } else {
            error_log("PHP PDF: ERRO - Imagem de fundo '{$img_nome}' NÃO encontrada em " . __DIR__);
            // Desenha um placeholder ou mensagem de erro na página do PDF
            $pdf->SetFillColor(200, 220, 255); $pdf->Rect(0, 0, 210, 297, 'F'); 
            $pdf->SetTextColor(255,0,0); $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Text(10,10, "Erro: Imagem de fundo '{$img_nome}' não encontrada!");
        }
    }
    
    // --- CONTEÚDO DA ÚLTIMA PÁGINA (PL4.png) ---
    // Posiciona na última página adicionada (que deve ser a PL4.png)
    $pdf->setPage($pdf->getNumPages()); // Garante que estamos escrevendo na última página

    $pdf->SetFont('helvetica', 'B', 10); $pdf->SetXY(175, 280); $pdf->Cell(0, 10, $dataAtual, 0, 0, 'R');
    
    $pdf->SetFont('helvetica', 'B', 14); $pdf->SetTextColor(255, 255, 255); $line_height = 7;
    
    $pdf->SetXY(18.4, 71); $pdf->MultiCell(170, $line_height,"Cliente: " .$nome, 0, 'L');
    $potenciaTotalSistemaKWp_formatted = number_format($potenciaTotalSistemaKWp, 2, ',', '.');
    $pdf->SetXY(18.4, 78); $pdf->MultiCell(170, $line_height, $qtdmodulos." módulos, ". $potmodulos_Wp ." Wp, de ". $potenciaTotalSistemaKWp_formatted." kWp", 0, 'L');
    $pdf->SetXY(18.4, 85); $pdf->MultiCell(170, $line_height,"Endereço: " .$endereco, 0, 'L');
    
    $current_y_pdf = 85 + $line_height;
    $pdf->SetXY(18.4, $current_y_pdf); $pdf->MultiCell(170, $line_height,"Cidade: " .$cidade_input, 0, 'L'); 
    $current_y_pdf += $line_height;

    // Adiciona o aviso sobre o cálculo de deslocamento
    if (!empty($aviso_deslocamento_pdf)) {
        $pdf->SetFont('helvetica', 'I', 9); // Fonte menor e itálico para o aviso
        $pdf->SetTextColor(230, 230, 230); // Cor cinza claro para o aviso
        $pdf->SetXY(18.4, $current_y_pdf);
        // Ajustar a altura da MultiCell para o aviso se necessário
        $pdf->MultiCell(170, $line_height - 2, $aviso_deslocamento_pdf, 0, 'L');
        $current_y_pdf += ($line_height - 2);
        $pdf->SetTextColor(255, 255, 255); // Restaura a cor do texto para branco
        $pdf->SetFont('helvetica', 'B', 14); // Restaura a fonte principal
    }
    $current_y_pdf += 1; // Pequeno espaço antes dos custos

    $pdf->SetXY(18.4, $current_y_pdf); $pdf->MultiCell(170, $line_height, "Custo por Limpeza Avulsa: R$ " . number_format($custototal_B15, 2, ',', '.'), 0, 'L'); $current_y_pdf += $line_height;
    $pdf->SetXY(18.4, $current_y_pdf); $pdf->MultiCell(170, $line_height, "Payback (1 Limpeza): " . $payback1limpeza_text, 0, 'L'); $current_y_pdf += $line_height;
    $pdf->SetXY(18.4, $current_y_pdf); $pdf->MultiCell(170, $line_height, "Pacote 3 Limpezas: " . $custo3limpezas_text, 0, 'L'); $current_y_pdf += $line_height;
    $pdf->SetXY(18.4, $current_y_pdf); $pdf->MultiCell(170, $line_height, "Payback (Pacote 3 Limpezas, valor 1ª parc.): " . $payback3limpezas_text, 0, 'L'); $current_y_pdf += $line_height + 5; // Espaço maior

    $pdf->SetXY(18.4, $current_y_pdf); $pdf->MultiCell(170, $line_height,"Economia mensal sistema COM limpeza: R$ " .number_format($econMensalComLimp, 2, ',', '.'), 0, 'L'); $current_y_pdf += $line_height;
    $pdf->SetXY(18.4, $current_y_pdf); $pdf->MultiCell(170, $line_height,"Economia mensal sistema SEM limpeza: R$ " .number_format($econMensalSemLimp, 2, ',', '.'), 0, 'L'); $current_y_pdf += $line_height;
    $pdf->SetXY(18.4, $current_y_pdf); $pdf->MultiCell(170, $line_height,"Diferença na economia mensal: R$ " .number_format($econDiferenca, 2, ',', '.'), 0, 'L'); $current_y_pdf += $line_height + 5; // Espaço maior
    
    $pdf->SetFont('helvetica', 'B', 11.5); $pdf->SetXY(18.4, $current_y_pdf);
    $pdf->MultiCell(173, $line_height-2,"Aderindo ao pacote de três limpezas anuais, além da manutenção preventiva, você ganhará o monitoramento online completo do seu sistema durante 1 ano!", 0, 'J'); // Justificado

    // Nome do arquivo PDF
    $nomeArquivoLimpo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $nome); // Limpa o nome do cliente para o nome do arquivo
    $nomeFinalPDF = "Proposta_Limpeza_Modulos_{$nomeArquivoLimpo}.pdf";
    
    error_log("PHP PDF: Preparando para enviar o PDF: {$nomeFinalPDF}");
    // Envia o PDF para o navegador para download (inline 'I' ou download 'D')
    $pdf->Output($nomeFinalPDF, 'I'); 
    error_log("PHP PDF: PDF Output chamado.");
    exit; // Garante que nada mais seja enviado após o PDF

} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao']) && $_POST['acao'] == 'simular_deslocamento') {
    // Este bloco já foi tratado e tem um exit, então não deve ser alcançado aqui.
    // Mas para clareza, esta condição é para a simulação.
} elseif ($_SERVER["REQUEST_METHOD"] != "POST") {
    error_log("PHP: Requisição GET ou outro método recebido (não é simulação nem PDF). Nenhuma ação definida.");
    // Poderia retornar um erro 405 Method Not Allowed se quisesse ser mais estrito.
    // header("HTTP/1.1 405 Method Not Allowed");
    // echo "Método não permitido.";
}

error_log("FIM DA EXECUÇÃO DO SCRIPT PHP: " . date("Y-m-d H:i:s"));
error_log("==================================================\n\n");
?>
