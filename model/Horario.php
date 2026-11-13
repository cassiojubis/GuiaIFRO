<?php
// Funções de horário ("Aberto agora"). Não usam banco: os testes (tests/horario_test.php) rodam sem ele.

/**
 * Tenta ler o texto livre de horário (ex.: "07:00 às 13:00 / 13:00 às 18:00" ou
 * "Segunda: 08:00 às 12:00 | Terça: 13:00 às 18:00") e diz se está aberto agora.
 * Retorna null quando o texto não tem um horário reconhecível (ex.: "Durante os
 * turnos letivos", "Não possui") — nesses casos o site não mostra selo nenhum.
 *
 * É um "melhor esforço": textos que citam dias específicos só são considerados
 * se baterem com o dia de hoje (entende intervalos como "Segunda a Quinta");
 * textos sem dia são tratados como válidos todo dia.
 *
 * $agora é opcional: serve pros testes (tests/horario_test.php) fixarem uma data.
 * O horário de fechamento já conta como fechado (12:00 em "08:00 às 12:00").   ff
 */
function setorEstaAberto(string $horario, ?DateTimeImmutable $agora = null): ?bool
{
    $agora = $agora ?? new DateTimeImmutable('now');
    $hojeIndice = (int) $agora->format('w'); // 0 = domingo
    $agoraMinutos = ((int) $agora->format('H')) * 60 + (int) $agora->format('i');

    $segmentos = preg_split('/[|\/]/u', $horario);
    $encontrouHorario = false;

    foreach ($segmentos as $segmento) {
        $diasDoSegmento = diasCitadosNoTexto($segmento);

        // Segmento sem dia citado vale pra todo dia.
        $valeParaHoje = $diasDoSegmento === [] || in_array($hojeIndice, $diasDoSegmento, true);

        if (preg_match_all('/(\d{1,2}):(\d{2})\s*(?:às|as|-)\s*(\d{1,2}):(\d{2})/u', $segmento, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $encontrouHorario = true;
                if (!$valeParaHoje) {
                    continue;
                }
                $inicio = ((int) $m[1]) * 60 + (int) $m[2];
                $fim = ((int) $m[3]) * 60 + (int) $m[4];
                if ($agoraMinutos >= $inicio && $agoraMinutos < $fim) {
                    return true;
                }
            }
        }
    }

    if (!$encontrouHorario) {
        return null; // texto sem horário reconhecível (ex.: "Durante os turnos letivos")
    }

    return false;
}

/**
 * Quais dias da semana (0 = domingo ... 6 = sábado) um trecho de texto cita.
 * Entende dia solto ("Terça") e intervalo ("Segunda a Quinta", "segunda-feira até sexta").
 * Devolve [] quando o texto não cita dia nenhum.
 */
function diasCitadosNoTexto(string $texto): array
{
    $nomes = ['domingo' => 0, 'segunda' => 1, 'terca' => 2, 'quarta' => 3, 'quinta' => 4, 'sexta' => 5, 'sabado' => 6];
    $semAcento = strtr(mb_strtolower($texto, 'UTF-8'), ['ç' => 'c', 'á' => 'a', 'à' => 'a', 'é' => 'e', 'ê' => 'e']);
    $padraoDia = '(domingo|segunda|terca|quarta|quinta|sexta|sabado)(?:-feira)?';
    $dias = [];

    // Intervalos primeiro ("segunda a quinta"); depois tira do texto pra não contar de novo.
    if (preg_match_all('/' . $padraoDia . '\s+(?:a|ate)\s+' . $padraoDia . '/u', $semAcento, $intervalos, PREG_SET_ORDER)) {
        foreach ($intervalos as $intervalo) {
            $dia = $nomes[$intervalo[1]];
            $fim = $nomes[$intervalo[2]];
            // anda pela semana até chegar no último dia (aceita "sexta a segunda", que vira a semana)
            for ($passos = 0; $passos < 7; $passos++) {
                $dias[] = $dia;
                if ($dia === $fim) {
                    break;
                }
                $dia = ($dia + 1) % 7;
            }
        }
        $semAcento = preg_replace('/' . $padraoDia . '\s+(?:a|ate)\s+' . $padraoDia . '/u', ' ', $semAcento);
    }

    if (preg_match_all('/' . $padraoDia . '/u', $semAcento, $soltos)) {
        foreach ($soltos[1] as $nome) {
            $dias[] = $nomes[$nome];
        }
    }

    return array_values(array_unique($dias));
}

/* =============================================================
 * HORÁRIO ESTRUTURADO (B4)
 * Faixas: [['dia_semana' => 1, 'abre' => '08:00:00', 'fecha' => '12:00:00'], ...]
 * Exceções do dia: [['fechado' => 1, 'abre' => null, 'fecha' => null, 'motivo' => 'Feriado'], ...]
 * ============================================================= */

const NOMES_DIAS = ['domingo', 'segunda', 'terça', 'quarta', 'quinta', 'sexta', 'sábado'];

/** '13:00:00' -> '13h', '13:15:00' -> '13h15' */
function formatarHora(string $hora): string
{
    [$h, $m] = array_map('intval', explode(':', $hora));
    return $h . 'h' . ($m ? sprintf('%02d', $m) : '');
}

/** Minutos desde a meia-noite: '13:15:00' -> 795 */
function minutosDaHora(string $hora): int
{
    [$h, $m] = array_map('intval', explode(':', $hora));
    return $h * 60 + $m;
}

/** Junta faixas encostadas (07–13 + 13–18 = 07–18) e ordena. */
function juntarFaixas(array $faixas): array
{
    usort($faixas, static function ($a, $b) { return strcmp($a['abre'], $b['abre']); });
    $juntas = [];
    foreach ($faixas as $faixa) {
        $ultima = count($juntas) - 1;
        if ($ultima >= 0 && minutosDaHora($faixa['abre']) <= minutosDaHora($juntas[$ultima]['fecha'])) {
            if (minutosDaHora($faixa['fecha']) > minutosDaHora($juntas[$ultima]['fecha'])) {
                $juntas[$ultima]['fecha'] = $faixa['fecha'];
            }
            continue;
        }
        $juntas[] = ['abre' => $faixa['abre'], 'fecha' => $faixa['fecha']];
    }
    return $juntas;
}

/**
 * Diz se está aberto agora e monta a frase do selo:
 *   ['aberto' => true,  'resumo' => 'Aberto agora · fecha às 17h']
 *   ['aberto' => false, 'resumo' => 'Fechado · abre às 13h']
 *   ['aberto' => false, 'resumo' => 'Fechado hoje: Feriado']
 *   ['aberto' => null,  'resumo' => null]   (setor sem horário cadastrado)
 *
 * $excecoesPorData: ['2026-10-12' => [exceção, ...]] — só precisa ter os próximos dias.
 */
function estadoHorario(array $faixas, array $excecoesPorData, DateTimeImmutable $agora): array
{
    if (!$faixas) {
        return ['aberto' => null, 'resumo' => null];
    }

    // Faixas de um dia, já considerando feriado/horário especial.
    $faixasDoDia = static function (DateTimeImmutable $dia) use ($faixas, $excecoesPorData): array {
        $excecoes = $excecoesPorData[$dia->format('Y-m-d')] ?? [];
        foreach ($excecoes as $excecao) {
            if ((int) $excecao['fechado'] === 1) {
                return ['fechado_motivo' => $excecao['motivo'], 'faixas' => []];
            }
        }
        $especiais = array_values(array_filter($excecoes, static function ($excecao) { return $excecao['abre'] && $excecao['fecha']; }));
        if ($especiais) {
            return ['fechado_motivo' => null, 'faixas' => juntarFaixas($especiais)];
        }
        $doDia = array_values(array_filter($faixas, static function ($faixa) use ($dia) {
            return (int) $faixa['dia_semana'] === (int) $dia->format('w');
        }));
        return ['fechado_motivo' => null, 'faixas' => juntarFaixas($doDia)];
    };

    $hoje = $faixasDoDia($agora);
    $minutoAgora = minutosDaHora($agora->format('H:i'));

    foreach ($hoje['faixas'] as $faixa) {
        if ($minutoAgora >= minutosDaHora($faixa['abre']) && $minutoAgora < minutosDaHora($faixa['fecha'])) {
            return ['aberto' => true, 'resumo' => 'Aberto agora · fecha às ' . formatarHora($faixa['fecha'])];
        }
    }
    foreach ($hoje['faixas'] as $faixa) {
        if (minutosDaHora($faixa['abre']) > $minutoAgora) {
            return ['aberto' => false, 'resumo' => 'Fechado agora · abre às ' . formatarHora($faixa['abre'])];
        }
    }

    // Procura o próximo dia que abre (até uma semana pra frente).
    for ($dias = 1; $dias <= 7; $dias++) {
        $dia = $agora->modify("+{$dias} day");
        $info = $faixasDoDia($dia);
        if ($info['faixas']) {
            $quando = $dias === 1 ? 'amanhã' : NOMES_DIAS[(int) $dia->format('w')];
            $prefixo = $hoje['fechado_motivo'] ? 'Fechado hoje (' . $hoje['fechado_motivo'] . ')' : 'Fechado agora';
            return ['aberto' => false, 'resumo' => $prefixo . ' · abre ' . $quando . ' às ' . formatarHora($info['faixas'][0]['abre'])];
        }
    }
    return ['aberto' => false, 'resumo' => $hoje['fechado_motivo'] ? 'Fechado hoje (' . $hoje['fechado_motivo'] . ')' : 'Fechado agora'];
}

/**
 * Texto curto da semana: "Segunda a quinta: 7h às 13h15 · Sexta: 7h às 19h".
 * Dias seguidos com o mesmo horário viram um intervalo.
 */
function resumoHorarioSemana(array $faixas): string
{
    $porDia = [];
    foreach ([1, 2, 3, 4, 5, 6, 0] as $dia) {
        $doDia = array_values(array_filter($faixas, static function ($faixa) use ($dia) { return (int) $faixa['dia_semana'] === $dia; }));
        if ($doDia) {
            $porDia[$dia] = implode(' e ', array_map(static function ($faixa) {
                return formatarHora($faixa['abre']) . ' às ' . formatarHora($faixa['fecha']);
            }, juntarFaixas($doDia)));
        }
    }
    $partes = [];
    $grupo = null;
    foreach ($porDia as $dia => $texto) {
        $anterior = $grupo ? ($grupo['fim'] === 6 ? 0 : $grupo['fim'] + 1) : null;
        if ($grupo && $grupo['texto'] === $texto && $anterior === $dia) {
            $grupo['fim'] = $dia;
            continue;
        }
        if ($grupo) {
            $partes[] = $grupo;
        }
        $grupo = ['inicio' => $dia, 'fim' => $dia, 'texto' => $texto];
    }
    if ($grupo) {
        $partes[] = $grupo;
    }
    return implode(' · ', array_map(static function ($parte) {
        $dias = ucfirst(NOMES_DIAS[$parte['inicio']]) . ($parte['inicio'] !== $parte['fim'] ? ' a ' . NOMES_DIAS[$parte['fim']] : '');
        return $dias . ': ' . $parte['texto'];
    }, $partes));
}

/**
 * Converte o texto livre antigo em faixas estruturadas.
 * Só converte quando TODO trecho cita o dia da semana — se o texto não diz
 * os dias (ex.: "07:00 às 13:00"), não dá pra saber se abre no sábado, então
 * devolve null (melhor não inventar).
 */
function converterHorarioTexto(string $texto): ?array
{
    $faixas = [];
    foreach (preg_split('/[|\/]/u', $texto) as $segmento) {
        if (trim($segmento) === '') {
            continue;
        }
        if (!preg_match_all('/(\d{1,2}):(\d{2})\s*(?:às|as|-)\s*(\d{1,2}):(\d{2})/u', $segmento, $horas, PREG_SET_ORDER)) {
            return null;
        }
        $dias = diasCitadosNoTexto($segmento);
        if (!$dias) {
            return null;
        }
        foreach ($dias as $dia) {
            foreach ($horas as $hora) {
                $faixas[] = [
                    'dia_semana' => $dia,
                    'abre' => sprintf('%02d:%02d:00', $hora[1], $hora[2]),
                    'fecha' => sprintf('%02d:%02d:00', $hora[3], $hora[4]),
                ];
            }
        }
    }
    return $faixas ?: null;
}
