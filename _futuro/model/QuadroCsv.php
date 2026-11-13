<?php
/**
 * LEITURA DO CSV DO QUADRO DE HORÁRIOS (N13)
 *
 * Colunas (a primeira linha é o cabeçalho, em qualquer ordem):
 *   turma ; dia ; inicio ; fim ; disciplina ; professor ; sala
 * (também aceita "horario" no lugar de inicio/fim: "07:00-07:50" ou "07:00 às 07:50")
 *
 * Separador ";" ou "," (o Excel brasileiro salva com ";"). Arquivo em UTF-8
 * ou no padrão do Windows (é convertido). Não usa banco: dá pra testar sozinho.
 * Modelo: docs/modelo-quadro-horarios.csv
 */

require_once __DIR__ . '/Horario.php';

/**
 * Lê o conteúdo do CSV. $salas = salas do mapa (pra reconhecer a sala e
 * ligar o "Como chegar"). Devolve ['aulas' => [...], 'erros' => [linha => msg], 'turmas' => [...]].
 */
function lerQuadroCsv(string $conteudo, array $salas = []): array
{
    if (strncmp($conteudo, "\xEF\xBB\xBF", 3) === 0) {
        $conteudo = substr($conteudo, 3); // tira o BOM do Excel
    }
    if (!mb_check_encoding($conteudo, 'UTF-8')) {
        $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'Windows-1252');
    }
    $linhas = preg_split('/\r\n|\r|\n/', trim($conteudo));
    if (!$linhas || trim($linhas[0]) === '') {
        return ['aulas' => [], 'erros' => [1 => 'O arquivo está vazio.'], 'turmas' => []];
    }
    $separador = substr_count($linhas[0], ';') >= substr_count($linhas[0], ',') ? ';' : ',';

    $normalizar = static function (string $texto): string {
        return trim(strtr(mb_strtolower(trim($texto), 'UTF-8'), ['á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'ç' => 'c']));
    };
    $cabecalho = array_map($normalizar, str_getcsv($linhas[0], $separador));
    $posicao = array_flip($cabecalho);
    $temHorario = isset($posicao['horario']);
    $faltando = array_diff(['turma', 'dia', 'disciplina'], $cabecalho);
    if (!$temHorario && (!isset($posicao['inicio']) || !isset($posicao['fim']))) {
        $faltando[] = 'inicio e fim (ou horario)';
    }
    if ($faltando) {
        return ['aulas' => [], 'erros' => [1 => 'Faltam colunas no cabeçalho: ' . implode(', ', $faltando) . '.'], 'turmas' => []];
    }

    // salas do mapa por nome normalizado (pra achar "Lab. 1", "laboratorio 1"...)
    $salasPorNome = [];
    foreach ($salas as $sala) {
        foreach ([$sala['id'], $sala['nome'], $sala['nomeCurto'] ?? ''] as $nome) {
            if ($nome !== '') {
                $salasPorNome[$normalizar($nome)] = $sala['id'];
            }
        }
    }

    $hora = static function (string $valor): ?string {
        return preg_match('/^([01]?\d|2[0-3])[:h]([0-5]\d)$/', trim($valor), $m) ? sprintf('%02d:%02d:00', $m[1], $m[2]) : null;
    };

    $aulas = [];
    $erros = [];
    $turmas = [];
    foreach (array_slice($linhas, 1) as $indice => $linha) {
        $numero = $indice + 2;
        if (trim(str_replace($separador, '', $linha)) === '') {
            continue; // linha vazia
        }
        $celulas = str_getcsv($linha, $separador);
        $valor = static function (string $coluna) use ($celulas, $posicao): string {
            return isset($posicao[$coluna]) ? trim((string) ($celulas[$posicao[$coluna]] ?? '')) : '';
        };

        $turma = $valor('turma');
        $disciplina = $valor('disciplina');
        $dias = diasCitadosNoTexto($valor('dia'));
        if (!$dias && preg_match('/^[0-6]$/', $valor('dia'))) {
            $dias = [(int) $valor('dia')];
        }
        if ($temHorario) {
            $partes = preg_split('/\s*(?:-|às|as|a)\s*/u', $valor('horario'));
            $inicio = $hora($partes[0] ?? '');
            $fim = $hora($partes[1] ?? '');
        } else {
            $inicio = $hora($valor('inicio'));
            $fim = $hora($valor('fim'));
        }

        $problemas = [];
        if ($turma === '' || mb_strlen($turma) > 60) {
            $problemas[] = 'turma vazia ou longa demais';
        }
        if ($disciplina === '' || mb_strlen($disciplina) > 150) {
            $problemas[] = 'disciplina vazia ou longa demais';
        }
        if (count($dias) !== 1) {
            $problemas[] = 'dia da semana não reconhecido ("' . $valor('dia') . '")';
        }
        if (!$inicio || !$fim || $fim <= $inicio) {
            $problemas[] = 'horário inválido (use 07:00 e 07:50)';
        }
        if ($problemas) {
            $erros[$numero] = implode('; ', $problemas);
            continue;
        }

        $salaTexto = mb_substr($valor('sala'), 0, 100);
        $aulas[] = [
            'turma' => $turma,
            'dia_semana' => $dias[0],
            'inicio' => $inicio,
            'fim' => $fim,
            'disciplina' => $disciplina,
            'professor' => mb_substr($valor('professor'), 0, 150) ?: null,
            'sala_texto' => $salaTexto ?: null,
            'sala_id' => $salaTexto !== '' ? ($salasPorNome[$normalizar($salaTexto)] ?? null) : null,
        ];
        $turmas[$turma] = true;
    }

    $turmas = array_keys($turmas);
    sort($turmas);
    return ['aulas' => $aulas, 'erros' => $erros, 'turmas' => $turmas];
}
