<?php
/**
 * PLANTA DO ANDAR EM SVG, GERADA NO SERVIDOR (placas impressas, N3)
 *
 * Mesmo cálculo do js/mapa.js (mesmas medidas): em cada ala, as salas de cada
 * lado (frente/fundos) dividem o comprimento da ala igualmente, na ordem do
 * campo 'ordem'; a sala de ponta fica depois do fim da ala.
 * Se mudar as medidas lá, mude aqui também.
 *
 * Eixos: x = da entrada para o fundo (0 = porta do prédio), z = para os lados
 * (negativo = ala esquerda). No desenho, a entrada fica embaixo.
 */

const PLANTA_CW = 3.2;   // largura dos corredores
const PLANTA_RD = 5.4;   // profundidade das salas laterais
const PLANTA_X1 = 12;    // da porta até o corredor que corta o prédio
const PLANTA_ED = 8;     // profundidade das salas de ponta
const PLANTA_ESCADA = 7; // comprimento da escada
const PLANTA_ALA = ['terreo' => 16, 'andar1' => 22];

/** Calcula a posição (x0, x1, z0, z1, porta) de cada sala do andar. */
function calcularPlanta(array $salas, string $andar): array
{
    $ala = PLANTA_ALA[$andar] ?? PLANTA_ALA['terreo'];
    $grupos = [];
    foreach ($salas as $sala) {
        if ($sala['andar'] === $andar) {
            $grupos[$sala['ala']][] = $sala;
        }
    }
    $posicoes = [];
    foreach ($grupos as $nomeAla => $lista) {
        usort($lista, static function ($a, $b) { return $a['ordem'] <=> $b['ordem']; });
        $sinal = $nomeAla === 'esquerda' ? -1 : 1;
        $normais = array_values(array_filter($lista, static function ($s) { return !$s['ponta']; }));
        foreach ($normais as $i => &$sala) {
            if ($sala['lado'] !== 'frente' && $sala['lado'] !== 'fundos') {
                $sala['lado'] = $i % 2 === 0 ? 'frente' : 'fundos';
            }
        }
        unset($sala);
        foreach (['frente', 'fundos'] as $lado) {
            $doLado = array_values(array_filter($normais, static function ($s) use ($lado) { return $s['lado'] === $lado; }));
            $largura = ($ala - PLANTA_CW / 2) / max(1, count($doLado));
            foreach ($doLado as $i => $sala) {
                $za = PLANTA_CW / 2 + $i * $largura;
                $frente = $lado === 'frente';
                $x0 = $frente ? PLANTA_X1 - PLANTA_RD : PLANTA_X1 + PLANTA_CW;
                $posicoes[$sala['id']] = $sala + [
                    'x0' => $x0, 'x1' => $x0 + PLANTA_RD, 'z0' => $sinal * $za, 'z1' => $sinal * ($za + $largura),
                    'portaX' => $frente ? PLANTA_X1 : PLANTA_X1 + PLANTA_CW, 'portaZ' => $sinal * ($za + $largura / 2),
                    'lado' => $lado, 'idx' => $i, // idx = qual porta desse lado (0 = a mais perto da escada)
                ];
            }
        }
        foreach ($lista as $sala) {
            if ($sala['ponta']) {
                $posicoes[$sala['id']] = $sala + [
                    'x0' => PLANTA_X1 - PLANTA_RD, 'x1' => PLANTA_X1 + PLANTA_CW + PLANTA_RD,
                    'z0' => $sinal * $ala, 'z1' => $sinal * ($ala + PLANTA_ED),
                    'portaX' => PLANTA_X1 + PLANTA_CW / 2, 'portaZ' => $sinal * $ala,
                ];
            }
        }
    }
    return $posicoes;
}

/**
 * SVG da planta do andar. $destaque = id da sala "você está aqui" (fica verde,
 * com um alfinete na porta). Tudo escapado: nomes de sala nunca viram tag.
 */
function plantaSvg(array $salas, string $andar, ?string $destaque = null): string
{
    $posicoes = calcularPlanta($salas, $andar);
    $ala = PLANTA_ALA[$andar] ?? PLANTA_ALA['terreo'];
    $temPonta = false;
    foreach ($posicoes as $p) {
        $temPonta = $temPonta || $p['ponta'];
    }
    $zFim = $ala + ($temPonta ? PLANTA_ED : 0);
    $xFundo = PLANTA_X1 + PLANTA_CW + PLANTA_RD;

    // planta -> tela: z vira horizontal, x vira vertical (entrada embaixo)
    $ret = static function (float $x0, float $x1, float $z0, float $z1, string $estilo): string {
        $x = min($z0, $z1);
        $y = -max($x0, $x1);
        return sprintf('<rect x="%.2f" y="%.2f" width="%.2f" height="%.2f" %s/>', $x, $y, abs($z1 - $z0), abs($x1 - $x0), $estilo);
    };
    $e = static function (string $texto): string { return htmlspecialchars($texto, ENT_QUOTES | ENT_XML1, 'UTF-8'); };

    $margem = 2;
    $vbX = -$zFim - $margem;
    $vbY = -$xFundo - $margem;
    $vbL = 2 * ($zFim + $margem);
    $vbA = $xFundo + $margem + ($andar === 'terreo' ? 4 : 2);
    $svg = sprintf('<svg xmlns="http://www.w3.org/2000/svg" viewBox="%.2f %.2f %.2f %.2f" role="img" aria-label="Planta do andar" font-family="Inter, Arial, sans-serif">', $vbX, $vbY, $vbL, $vbA);
    $svg .= $ret(PLANTA_X1 - PLANTA_RD, $xFundo, -$zFim, $zFim, 'fill="#f1f5f9" stroke="#334155" stroke-width="0.3"');
    $svg .= $ret(PLANTA_X1, PLANTA_X1 + PLANTA_CW, -$ala, $ala, 'fill="#e2e8f0"');
    if ($andar === 'terreo') {
        $svg .= $ret(0, PLANTA_X1, -PLANTA_CW / 2, PLANTA_CW / 2, 'fill="#e2e8f0" stroke="#334155" stroke-width="0.3"');
        $svg .= sprintf('<text x="0" y="2.6" font-size="1.4" text-anchor="middle" fill="#334155">ENTRADA</text>');
    }
    // escada (no meio, terminando no corredor)
    $svg .= $ret(PLANTA_X1 - PLANTA_ESCADA, PLANTA_X1, -0.9, 0.9, 'fill="#cbd5e1" stroke="#64748b" stroke-width="0.15"');
    $svg .= sprintf('<text x="0" y="%.2f" font-size="0.9" text-anchor="middle" fill="#334155" transform="rotate(-90 0 %.2f)">ESCADA</text>', -(PLANTA_X1 - PLANTA_ESCADA / 2), -(PLANTA_X1 - PLANTA_ESCADA / 2));

    foreach ($posicoes as $id => $p) {
        $aqui = $id === $destaque;
        $svg .= $ret($p['x0'], $p['x1'], $p['z0'], $p['z1'], $aqui
            ? 'fill="#0f7a43" stroke="#0b5e33" stroke-width="0.35"'
            : 'fill="#ffffff" stroke="#334155" stroke-width="0.2"');
        $largura = abs($p['z1'] - $p['z0']);
        $tamanhoFonte = min(0.95, $largura * 0.14);
        $cx = ($p['z0'] + $p['z1']) / 2;
        $cy = -($p['x0'] + $p['x1']) / 2;
        // quebra o nome em até 3 linhas que caibam na sala
        $palavras = explode(' ', $p['nomeCurto']);
        $linhas = [];
        $atual = '';
        $maxCaracteres = max(5, (int) floor($largura / ($tamanhoFonte * 0.6)));
        foreach ($palavras as $palavra) {
            if ($atual === '') {
                $atual = $palavra;
            } elseif (mb_strlen($atual . ' ' . $palavra) <= $maxCaracteres) {
                $atual .= ' ' . $palavra;
            } else {
                $linhas[] = $atual;
                $atual = $palavra;
            }
        }
        $linhas[] = $atual;
        $linhas = array_slice($linhas, 0, 3);
        $inicioY = $cy - (count($linhas) - 1) * $tamanhoFonte * 0.6;
        foreach ($linhas as $i => $linha) {
            $svg .= sprintf('<text x="%.2f" y="%.2f" font-size="%.2f" text-anchor="middle" dominant-baseline="middle" fill="%s" font-weight="%s">%s</text>',
                $cx, $inicioY + $i * $tamanhoFonte * 1.2, $tamanhoFonte, $aqui ? '#ffffff' : '#0f172a', $aqui ? '700' : '500', $e($linha));
        }
        if ($aqui) {
            // alfinete "você está aqui" na porta
            $px = $p['portaZ'];
            $py = -$p['portaX'];
            $svg .= sprintf('<circle cx="%.2f" cy="%.2f" r="1.1" fill="#e11d48" stroke="#fff" stroke-width="0.3"/>', $px, $py);
            $svg .= sprintf('<text x="%.2f" y="%.2f" font-size="1.1" font-weight="800" fill="#e11d48" text-anchor="middle">VOCÊ ESTÁ AQUI</text>', $px, $py + ($p['portaX'] > PLANTA_X1 ? 2.4 : -1.8));
        }
    }
    return $svg . '</svg>';
}

/**
 * Caminho em texto, do portão até a sala (mesmas frases do js/mapa.js).
 * Usado no "Mapa em texto" (I2): funciona sem JavaScript e com leitor de tela.
 */
function passosEmTexto(array $sala, array $posicoes): array
{
    $p = $posicoes[$sala['id']] ?? null;
    $lado = $sala['ala'] === 'esquerda' ? 'esquerda' : 'direita';
    $passos = ['Entre pelo portão e siga o caminho até a entrada do prédio.'];
    if ($sala['andar'] === 'andar1') {
        $passos[] = 'Siga reto pelo corredor até a escada e suba ao 1º andar. Ela termina no corredor que corta o prédio.';
    } else {
        $passos[] = 'Siga reto pelo corredor, contorne a escada pelo lado e entre no corredor que corta o prédio.';
    }
    $passos[] = 'Vire à ' . $lado . ' e siga pela ala' . (!empty($sala['ponta']) ? ' até o fundo' : '') . '.';
    if (!empty($sala['ponta'])) {
        $passos[] = 'A porta no fim da ala é a ' . $sala['nome'] . '.';
    } elseif ($p) {
        $passos[] = 'É a ' . ($p['idx'] + 1) . 'ª porta contando da escada, ' . ($p['lado'] === 'frente' ? 'do lado da frente (de quem entrou no prédio)' : 'do lado dos fundos') . '.';
    }
    return $passos;
}
