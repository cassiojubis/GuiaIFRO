<?php
/**
 * GERADOR DE QR CODE (sem serviço externo e sem biblioteca)
 *
 * Gera o QR code no próprio servidor e devolve como SVG. Usado nas portas
 * das salas (N2) e nas placas impressas (N3).
 *
 * Só o necessário para o Guia: modo "byte" (qualquer texto UTF-8), correção
 * de erro nível M (aguenta ~15% da placa suja/riscada) e versões 1 a 10
 * (até 213 bytes — sobra pra qualquer endereço do site).
 *
 * Segue a norma ISO/IEC 18004. Os nomes dos passos estão em português pra
 * ficar fácil de acompanhar: dados -> correção de erro (Reed-Solomon) ->
 * desenho dos padrões fixos -> dados em zigue-zague -> máscara -> formato.
 * Validado decodificando QR das versões 1 a 10 com um leitor independente (jsQR)
 * e com o vetor de teste da norma (tests/qrcode_test.php).
 */

/**
 * Tabela do nível M, versões 1 a 10:
 * [bytes de correção por bloco, [quantidade de blocos, bytes de dados por bloco], ...]
 */
const QR_TABELA_M = [
    1 => [10, [[1, 16]]],
    2 => [16, [[1, 28]]],
    3 => [26, [[1, 44]]],
    4 => [18, [[2, 32]]],
    5 => [24, [[2, 43]]],
    6 => [16, [[4, 27]]],
    7 => [18, [[4, 31]]],
    8 => [22, [[2, 38], [2, 39]]],
    9 => [22, [[3, 36], [2, 37]]],
    10 => [26, [[4, 43], [1, 44]]],
];

/** Centros dos padrões de alinhamento por versão. */
const QR_ALINHAMENTO = [
    1 => [], 2 => [6, 18], 3 => [6, 22], 4 => [6, 26], 5 => [6, 30],
    6 => [6, 34], 7 => [6, 22, 38], 8 => [6, 24, 42], 9 => [6, 26, 46], 10 => [6, 28, 50],
];

/**
 * Matriz do QR code: array de linhas, cada linha um array de bool (true = quadrado escuro).
 * Lança exceção se o texto não couber na versão 10.
 */
function gerarQrCode(string $texto): array
{
    $bytes = array_values(unpack('C*', $texto) ?: []);

    // 1) Menor versão em que o texto cabe.
    $versao = null;
    foreach (QR_TABELA_M as $v => [$ecPorBloco, $grupos]) {
        $capacidade = 0;
        foreach ($grupos as [$quantidade, $tamanho]) {
            $capacidade += $quantidade * $tamanho;
        }
        $bitsContagem = $v <= 9 ? 8 : 16;
        if (4 + $bitsContagem + 8 * count($bytes) <= $capacidade * 8) {
            $versao = $v;
            break;
        }
    }
    if ($versao === null) {
        throw new InvalidArgumentException('Texto grande demais para o QR code (máximo 213 bytes).');
    }
    [$ecPorBloco, $grupos] = QR_TABELA_M[$versao];
    $capacidade = 0;
    foreach ($grupos as [$quantidade, $tamanho]) {
        $capacidade += $quantidade * $tamanho;
    }

    // 2) Bits: modo byte (0100) + quantidade + dados + terminador + completar.
    $bits = '0100' . str_pad(decbin(count($bytes)), $versao <= 9 ? 8 : 16, '0', STR_PAD_LEFT);
    foreach ($bytes as $byte) {
        $bits .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
    }
    $bits .= str_repeat('0', min(4, $capacidade * 8 - strlen($bits)));
    $bits .= str_repeat('0', (8 - strlen($bits) % 8) % 8);
    $dados = array_map('bindec', str_split($bits, 8));
    for ($preenchimento = 0; count($dados) < $capacidade; $preenchimento++) {
        $dados[] = $preenchimento % 2 === 0 ? 0xEC : 0x11;
    }

    // 3) Divide em blocos, calcula a correção de erro de cada um e intercala.
    $blocosDados = [];
    $blocosCorrecao = [];
    $posicao = 0;
    foreach ($grupos as [$quantidade, $tamanho]) {
        for ($b = 0; $b < $quantidade; $b++) {
            $bloco = array_slice($dados, $posicao, $tamanho);
            $posicao += $tamanho;
            $blocosDados[] = $bloco;
            $blocosCorrecao[] = qrReedSolomon($bloco, $ecPorBloco);
        }
    }
    $final = [];
    $maiorBloco = max(array_map('count', $blocosDados));
    for ($i = 0; $i < $maiorBloco; $i++) {
        foreach ($blocosDados as $bloco) {
            if (isset($bloco[$i])) {
                $final[] = $bloco[$i];
            }
        }
    }
    for ($i = 0; $i < $ecPorBloco; $i++) {
        foreach ($blocosCorrecao as $bloco) {
            $final[] = $bloco[$i];
        }
    }

    // 4) Desenha os padrões fixos e depois os dados.
    $tamanho = 17 + 4 * $versao;
    $modulos = array_fill(0, $tamanho, array_fill(0, $tamanho, false));
    $fixo = array_fill(0, $tamanho, array_fill(0, $tamanho, false));
    $marcar = static function (int $x, int $y, bool $escuro) use (&$modulos, &$fixo): void {
        $modulos[$y][$x] = $escuro;
        $fixo[$y][$x] = true;
    };

    // linhas de sincronismo (timing)
    for ($i = 0; $i < $tamanho; $i++) {
        $marcar(6, $i, $i % 2 === 0);
        $marcar($i, 6, $i % 2 === 0);
    }
    // os três quadrados grandes dos cantos (com a borda branca em volta)
    foreach ([[3, 3], [$tamanho - 4, 3], [3, $tamanho - 4]] as [$cx, $cy]) {
        for ($dy = -4; $dy <= 4; $dy++) {
            for ($dx = -4; $dx <= 4; $dx++) {
                $x = $cx + $dx;
                $y = $cy + $dy;
                if ($x >= 0 && $x < $tamanho && $y >= 0 && $y < $tamanho) {
                    $distancia = max(abs($dx), abs($dy));
                    $marcar($x, $y, $distancia !== 2 && $distancia !== 4);
                }
            }
        }
    }
    // padrões de alinhamento (quadradinhos 5x5)
    $centros = QR_ALINHAMENTO[$versao];
    $ultimo = count($centros) - 1;
    foreach ($centros as $i => $cx) {
        foreach ($centros as $j => $cy) {
            if (($i === 0 && $j === 0) || ($i === 0 && $j === $ultimo) || ($i === $ultimo && $j === 0)) {
                continue; // em cima dos quadrados grandes
            }
            for ($dy = -2; $dy <= 2; $dy++) {
                for ($dx = -2; $dx <= 2; $dx++) {
                    $marcar($cx + $dx, $cy + $dy, max(abs($dx), abs($dy)) !== 1);
                }
            }
        }
    }
    // reserva as áreas do formato (preenchidas depois da máscara) e da versão
    qrDesenharFormato($modulos, $fixo, $tamanho, 0, $marcar);
    if ($versao >= 7) {
        $resto = $versao;
        for ($i = 0; $i < 12; $i++) {
            $resto = ($resto << 1) ^ (($resto >> 11) * 0x1F25);
        }
        $bitsVersao = ($versao << 12) | $resto;
        for ($i = 0; $i < 18; $i++) {
            $bit = (($bitsVersao >> $i) & 1) === 1;
            $a = $tamanho - 11 + $i % 3;
            $b = intdiv($i, 3);
            $marcar($a, $b, $bit);
            $marcar($b, $a, $bit);
        }
    }

    // dados em zigue-zague, de duas em duas colunas, de baixo pra cima e de volta
    $totalBits = count($final) * 8;
    $indice = 0;
    for ($direita = $tamanho - 1; $direita >= 1; $direita -= 2) {
        if ($direita === 6) {
            $direita = 5; // pula a coluna do sincronismo
        }
        for ($vertical = 0; $vertical < $tamanho; $vertical++) {
            for ($j = 0; $j < 2; $j++) {
                $x = $direita - $j;
                $subindo = (($direita + 1) & 2) === 0;
                $y = $subindo ? $tamanho - 1 - $vertical : $vertical;
                if (!$fixo[$y][$x] && $indice < $totalBits) {
                    $modulos[$y][$x] = (($final[$indice >> 3] >> (7 - ($indice & 7))) & 1) === 1;
                    $indice++;
                }
            }
        }
    }

    // 5) Escolhe a máscara que deixa o desenho mais fácil de ler.
    $melhor = null;
    $menorPenalidade = PHP_INT_MAX;
    for ($mascara = 0; $mascara < 8; $mascara++) {
        $teste = qrAplicarMascara($modulos, $fixo, $tamanho, $mascara);
        qrDesenharFormato($teste, $fixo, $tamanho, $mascara, null);
        $penalidade = qrPenalidade($teste, $tamanho);
        if ($penalidade < $menorPenalidade) {
            $menorPenalidade = $penalidade;
            $melhor = $teste;
        }
    }
    return $melhor;
}

/** Bytes de correção de erro (Reed-Solomon sobre GF(256), polinômio 0x11D). */
function qrReedSolomon(array $dados, int $quantidade): array
{
    static $exp = null, $log = null;
    if ($exp === null) {
        $exp = array_fill(0, 512, 0);
        $log = array_fill(0, 256, 0);
        $valor = 1;
        for ($i = 0; $i < 255; $i++) {
            $exp[$i] = $valor;
            $log[$valor] = $i;
            $valor <<= 1;
            if ($valor & 0x100) {
                $valor ^= 0x11D;
            }
        }
        for ($i = 255; $i < 512; $i++) {
            $exp[$i] = $exp[$i - 255];
        }
    }
    $multiplicar = static function (int $a, int $b) use ($exp, $log): int {
        return ($a === 0 || $b === 0) ? 0 : $exp[$log[$a] + $log[$b]];
    };

    // polinômio gerador: (x - a^0)(x - a^1)...(x - a^(n-1))
    $gerador = [1];
    for ($i = 0; $i < $quantidade; $i++) {
        $novo = array_fill(0, count($gerador) + 1, 0);
        foreach ($gerador as $j => $coeficiente) {
            $novo[$j] ^= $coeficiente;
            $novo[$j + 1] ^= $multiplicar($coeficiente, $exp[$i]);
        }
        $gerador = $novo;
    }

    // resto da divisão dos dados pelo gerador
    $resto = array_fill(0, $quantidade, 0);
    foreach ($dados as $byte) {
        $fator = $byte ^ $resto[0];
        array_shift($resto);
        $resto[] = 0;
        for ($i = 0; $i < $quantidade; $i++) {
            $resto[$i] ^= $multiplicar($gerador[$i + 1], $fator);
        }
    }
    return $resto;
}

/** Desenha os 15 bits de formato (nível M + máscara) nas duas cópias. */
function qrDesenharFormato(array &$modulos, array &$fixo, int $tamanho, int $mascara, ?callable $marcar): void
{
    $dados = (0 << 3) | $mascara; // nível M = 00
    $resto = $dados;
    for ($i = 0; $i < 10; $i++) {
        $resto = ($resto << 1) ^ (($resto >> 9) * 0x537);
    }
    $bits = (($dados << 10) | $resto) ^ 0x5412;
    $pintar = $marcar ?? static function (int $x, int $y, bool $escuro) use (&$modulos): void {
        $modulos[$y][$x] = $escuro;
    };
    $bit = static function (int $i) use ($bits): bool {
        return (($bits >> $i) & 1) === 1;
    };

    for ($i = 0; $i <= 5; $i++) {
        $pintar(8, $i, $bit($i));
    }
    $pintar(8, 7, $bit(6));
    $pintar(8, 8, $bit(7));
    $pintar(7, 8, $bit(8));
    for ($i = 9; $i < 15; $i++) {
        $pintar(14 - $i, 8, $bit($i));
    }
    for ($i = 0; $i < 8; $i++) {
        $pintar($tamanho - 1 - $i, 8, $bit($i));
    }
    for ($i = 8; $i < 15; $i++) {
        $pintar(8, $tamanho - 15 + $i, $bit($i));
    }
    $pintar(8, $tamanho - 8, true); // o quadradinho escuro que sempre existe
}

function qrAplicarMascara(array $modulos, array $fixo, int $tamanho, int $mascara): array
{
    for ($y = 0; $y < $tamanho; $y++) {
        for ($x = 0; $x < $tamanho; $x++) {
            if ($fixo[$y][$x]) {
                continue;
            }
            switch ($mascara) {
                case 0: $inverte = ($x + $y) % 2 === 0; break;
                case 1: $inverte = $y % 2 === 0; break;
                case 2: $inverte = $x % 3 === 0; break;
                case 3: $inverte = ($x + $y) % 3 === 0; break;
                case 4: $inverte = (intdiv($x, 3) + intdiv($y, 2)) % 2 === 0; break;
                case 5: $inverte = ($x * $y) % 2 + ($x * $y) % 3 === 0; break;
                case 6: $inverte = (($x * $y) % 2 + ($x * $y) % 3) % 2 === 0; break;
                default: $inverte = (($x + $y) % 2 + ($x * $y) % 3) % 2 === 0;
            }
            if ($inverte) {
                $modulos[$y][$x] = !$modulos[$y][$x];
            }
        }
    }
    return $modulos;
}

/** Pontos de "dificuldade de leitura" (regras da norma; quanto menor, melhor). */
function qrPenalidade(array $m, int $tamanho): int
{
    $pontos = 0;
    // regra 1: 5 ou mais da mesma cor seguidos, nas linhas e nas colunas
    for ($eixo = 0; $eixo < 2; $eixo++) {
        for ($a = 0; $a < $tamanho; $a++) {
            $seguidos = 1;
            for ($b = 1; $b < $tamanho; $b++) {
                $atual = $eixo === 0 ? $m[$a][$b] : $m[$b][$a];
                $anterior = $eixo === 0 ? $m[$a][$b - 1] : $m[$b - 1][$a];
                if ($atual === $anterior) {
                    $seguidos++;
                    if ($seguidos === 5) {
                        $pontos += 3;
                    } elseif ($seguidos > 5) {
                        $pontos++;
                    }
                } else {
                    $seguidos = 1;
                }
            }
        }
    }
    // regra 2: blocos 2x2 da mesma cor
    for ($y = 0; $y < $tamanho - 1; $y++) {
        for ($x = 0; $x < $tamanho - 1; $x++) {
            $c = $m[$y][$x];
            if ($c === $m[$y][$x + 1] && $c === $m[$y + 1][$x] && $c === $m[$y + 1][$x + 1]) {
                $pontos += 3;
            }
        }
    }
    // regra 3: padrão parecido com os quadrados dos cantos (1:1:3:1:1 com 4 brancos)
    $padroes = ['10111010000', '00001011101'];
    for ($a = 0; $a < $tamanho; $a++) {
        $linha = '';
        $coluna = '';
        for ($b = 0; $b < $tamanho; $b++) {
            $linha .= $m[$a][$b] ? '1' : '0';
            $coluna .= $m[$b][$a] ? '1' : '0';
        }
        foreach ($padroes as $padrao) {
            $pontos += 40 * (substr_count($linha, $padrao) + substr_count($coluna, $padrao));
        }
    }
    // regra 4: equilíbrio entre escuro e claro
    $escuros = 0;
    foreach ($m as $linha) {
        $escuros += count(array_filter($linha));
    }
    $porcento = $escuros * 100 / ($tamanho * $tamanho);
    $pontos += (int) (floor(abs($porcento - 50) / 5) * 10);
    return $pontos;
}

/**
 * QR code em SVG (com a margem branca de 4 quadrados que a norma pede).
 * $rotulo vira o texto alternativo (leitor de tela).
 */
function qrCodeSvg(string $texto, int $tamanhoPx = 200, string $rotulo = 'QR code'): string
{
    $matriz = gerarQrCode($texto);
    $lado = count($matriz);
    $total = $lado + 8;
    $caminho = '';
    foreach ($matriz as $y => $linha) {
        foreach ($linha as $x => $escuro) {
            if ($escuro) {
                $caminho .= 'M' . ($x + 4) . ' ' . ($y + 4) . 'h1v1h-1z';
            }
        }
    }
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $total . ' ' . $total . '" width="' . $tamanhoPx . '" height="' . $tamanhoPx . '"'
        . ' role="img" aria-label="' . htmlspecialchars($rotulo, ENT_QUOTES, 'UTF-8') . '" shape-rendering="crispEdges">'
        . '<rect width="100%" height="100%" fill="#fff"/><path fill="#000" d="' . $caminho . '"/></svg>';
}
