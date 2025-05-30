<?php

// Inclui o autoload do Composer (ajuste o caminho se necessário)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
} else {
    // Se não encontrar, você precisará garantir que PhpSpreadsheet está acessível.
    error_log("PHPSpreadsheet autoload não encontrado. Verifique a instalação do Composer.");
    // Sem o autoload, as classes 'use' abaixo falharão.
}

use PhpOffice\PhpSpreadsheet\IOFactory;

// Constante para o raio da Terra em KM
define('RAIO_TERRA_KM_XLSX', 6371);

/**
 * Normaliza texto para comparação (minúsculas, sem acentos comuns).
 */
function normalizarTextoXLSX($texto) {
    if (!is_string($texto)) return '';
    $texto = strtolower(trim($texto));
    $comAcentos = ['á','à','ã','â','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','õ','ô','ö','ú','ù','û','ü','ç'];
    $semAcentos = ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c'];
    return str_replace($comAcentos, $semAcentos, $texto);
}

/**
 * Calcula a distância Haversine (linha reta) entre duas coordenadas.
 */
function calcularDistanciaHaversineXLSX($lat1, $lon1, $lat2, $lon2) {
    if (!is_numeric($lat1) || !is_numeric($lon1) || !is_numeric($lat2) || !is_numeric($lon2)) {
        error_log("Valores de coordenadas inválidos para Haversine: $lat1, $lon1, $lat2, $lon2");
        return 0.0;
    }
    $dLat = deg2rad((float)$lat2 - (float)$lat1);
    $dLon = deg2rad((float)$lon2 - (float)$lon1);
    $lat1_rad = deg2rad((float)$lat1);
    $lat2_rad = deg2rad((float)$lat2);
    $a = sin($dLat / 2) * sin($dLat / 2) +
         sin($dLon / 2) * sin($dLon / 2) * cos($lat1_rad) * cos($lat2_rad);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return RAIO_TERRA_KM_XLSX * $c;
}

/**
 * Carrega e processa as coordenadas de um arquivo XLSX.
 * Detecta automaticamente as colunas de Cidade, UF, Latitude e Longitude pelo cabeçalho.
 *
 * @param string $caminhoArquivoXLSX Caminho para o arquivo XLSX.
 * @return array Array de cidades com suas coordenadas, ou array vazio em caso de erro.
 */
function carregarCoordenadasXLSX($caminhoArquivoXLSX) {
    $coordenadasCidades = [];
    if (!file_exists($caminhoArquivoXLSX) || !is_readable($caminhoArquivoXLSX)) {
        error_log("XLSX: Arquivo não encontrado ou não legível: " . $caminhoArquivoXLSX);
        return $coordenadasCidades;
    }

    try {
        $spreadsheet = IOFactory::load($caminhoArquivoXLSX);
        $sheet = $spreadsheet->getActiveSheet(); // Pega a primeira planilha

        // Lê o cabeçalho (primeira linha) para identificar as colunas
        $cabecalhoLido = [];
        $primeiraLinhaIterator = $sheet->getRowIterator(1, 1)->current();
        if ($primeiraLinhaIterator) {
            $cellIterator = $primeiraLinhaIterator->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ($cellIterator as $cell) {
                $cabecalhoLido[] = $cell->getValue();
            }
        } else {
            error_log("XLSX: Não foi possível ler o cabeçalho do arquivo: " . $caminhoArquivoXLSX);
            return [];
        }

        // Mapeamento flexível de nomes de coluna esperados (normalizados)
        $mapaNomesColuna = [
            'cidade'    => ['cidade', 'municipio', 'município', 'localidade', 'nome cidade', 'nome_municipio'],
            'uf'        => ['uf', 'estado', 'est'],
            'latitude'  => ['latitude', 'lat', 'lt'],
            'longitude' => ['longitude', 'lon', 'lng', 'long']
        ];
        $indicesColuna = []; // Para armazenar o índice numérico (0-based) de cada coluna encontrada

        foreach ($mapaNomesColuna as $chaveLogica => $nomesPossiveis) {
            foreach ($cabecalhoLido as $idx => $nomeColunaPlanilha) {
                if ($nomeColunaPlanilha === null) continue; // Pular células de cabeçalho vazias
                if (in_array(normalizarTextoXLSX((string)$nomeColunaPlanilha), $nomesPossiveis)) {
                    $indicesColuna[$chaveLogica] = $idx; // $idx é 0-based
                    break;
                }
            }
        }

        // Verifica se as colunas essenciais foram encontradas
        if (!isset($indicesColuna['cidade']) || !isset($indicesColuna['latitude']) || !isset($indicesColuna['longitude'])) {
            error_log("XLSX: Colunas essenciais (cidade, latitude, longitude) não encontradas no cabeçalho. Detectado: " . implode(', ', $cabecalhoLido));
            return [];
        }

        // Itera pelas linhas de dados (começando da segunda linha)
        foreach ($sheet->getRowIterator(2) as $row) {
            $numLinha = $row->getRowIndex();
            $nomeCidade = trim((string)$sheet->getCellByColumnAndRow($indicesColuna['cidade'] + 1, $numLinha)->getValue());
            
            // Pular linha se o nome da cidade estiver vazio
            if (empty($nomeCidade)) {
                continue;
            }

            $uf = isset($indicesColuna['uf']) ? trim((string)$sheet->getCellByColumnAndRow($indicesColuna['uf'] + 1, $numLinha)->getValue()) : '';
            $latitudeStr = trim((string)$sheet->getCellByColumnAndRow($indicesColuna['latitude'] + 1, $numLinha)->getValue());
            $longitudeStr = trim((string)$sheet->getCellByColumnAndRow($indicesColuna['longitude'] + 1, $numLinha)->getValue());

            $latitude = str_replace(',', '.', $latitudeStr);   // Suporta vírgula como decimal
            $longitude = str_replace(',', '.', $longitudeStr); // Suporta vírgula como decimal

            if (is_numeric($latitude) && is_numeric($longitude)) {
                $chave = normalizarTextoXLSX($nomeCidade);
                if (!empty($uf)) {
                    $chave .= "-" . normalizarTextoXLSX($uf);
                }
                $coordenadasCidades[$chave] = [
                    'cidade'    => $nomeCidade,
                    'uf'        => $uf,
                    'latitude'  => (float)$latitude,
                    'longitude' => (float)$longitude
                ];
            } else {
                error_log("XLSX: Dados de lat/lon inválidos ou cidade vazia na linha " . $numLinha . ": Cidade='{$nomeCidade}', Lat='{$latitudeStr}', Lon='{$longitudeStr}'");
            }
        }
    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        error_log("XLSX Reader Exception: " . $e->getMessage());
        return [];
    } catch (Exception $e) {
        error_log("XLSX Erro geral ao processar: " . $e->getMessage());
        return [];
    }
    return $coordenadasCidades;
}

/**
 * Calcula a distância entre duas cidades usando coordenadas de um arquivo XLSX.
 *
 * @param string $nomeCidade1 Nome da primeira cidade.
 * @param string $ufCidade1 (Opcional) UF da primeira cidade para desambiguação.
 * @param string $nomeCidade2 Nome da segunda cidade.
 * @param string $ufCidade2 (Opcional) UF da segunda cidade para desambiguação.
 * @param string $caminhoArquivoXLSX Caminho para o arquivo XLSX.
 * @return float|null Distância em KM, ou null se cidades não encontradas ou erro.
 */
function obterDistanciaEntreCidadesXLSX($nomeCidade1, $ufCidade1, $nomeCidade2, $ufCidade2, $caminhoArquivoXLSX) {
    static $cacheCoordenadas = []; // Cache para evitar recarregar o mesmo arquivo XLSX múltiplas vezes na mesma requisição

    $chaveCacheArquivo = md5($caminhoArquivoXLSX); // Chave para o cache baseada no caminho do arquivo

    if (!isset($cacheCoordenadas[$chaveCacheArquivo])) {
        $cacheCoordenadas[$chaveCacheArquivo] = carregarCoordenadasXLSX($caminhoArquivoXLSX);
        if (empty($cacheCoordenadas[$chaveCacheArquivo])) {
            error_log("XLSX: Não foi possível carregar coordenadas do arquivo: " . $caminhoArquivoXLSX);
            return null; // Retorna null se o carregamento falhar
        }
    }
    $todasCoordenadas = $cacheCoordenadas[$chaveCacheArquivo];

    // Normaliza e cria chaves para busca
    $chaveNormCidade1 = normalizarTextoXLSX($nomeCidade1);
    if (!empty($ufCidade1)) $chaveNormCidade1 .= "-" . normalizarTextoXLSX($ufCidade1);

    $chaveNormCidade2 = normalizarTextoXLSX($nomeCidade2);
    if (!empty($ufCidade2)) $chaveNormCidade2 .= "-" . normalizarTextoXLSX($ufCidade2);

    $coordsCidade1 = null;
    if (isset($todasCoordenadas[$chaveNormCidade1])) {
        $coordsCidade1 = $todasCoordenadas[$chaveNormCidade1];
    } elseif (empty($ufCidade1)) { // Se não achou com UF (ou UF não foi dada), tenta só pelo nome
        foreach ($todasCoordenadas as $dados) {
            if (normalizarTextoXLSX($dados['cidade']) == normalizarTextoXLSX($nomeCidade1)) {
                $coordsCidade1 = $dados; break;
            }
        }
    }

    $coordsCidade2 = null;
    if (isset($todasCoordenadas[$chaveNormCidade2])) {
        $coordsCidade2 = $todasCoordenadas[$chaveNormCidade2];
    } elseif (empty($ufCidade2)) { // Se não achou com UF (ou UF não foi dada), tenta só pelo nome
        foreach ($todasCoordenadas as $dados) {
            if (normalizarTextoXLSX($dados['cidade']) == normalizarTextoXLSX($nomeCidade2)) {
                $coordsCidade2 = $dados; break;
            }
        }
    }

    if (!$coordsCidade1) {
        error_log("XLSX: Cidade 1 ('" . $nomeCidade1 . ($ufCidade1 ? "-".$ufCidade1 : "") . "') não encontrada no arquivo.");
        return null;
    }
    if (!$coordsCidade2) {
        error_log("XLSX: Cidade 2 ('" . $nomeCidade2 . ($ufCidade2 ? "-".$ufCidade2 : "") . "') não encontrada no arquivo.");
        return null;
    }

    return calcularDistanciaHaversineXLSX(
        $coordsCidade1['latitude'], $coordsCidade1['longitude'],
        $coordsCidade2['latitude'], $coordsCidade2['longitude']
    );
}

?>