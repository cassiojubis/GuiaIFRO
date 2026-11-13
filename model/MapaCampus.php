<?php
/**
 * MAPA DO CAMPUS (planta 2D, 3D, rotas) — js/mapa-campus.js
 *
 * De onde vêm os dados:
 *  - data/mapa-campus.json ........ o mapa salvo pelo admin (admin › Editor do mapa › Salvar no site);
 *  - model/mapa-campus-padrao.json  o mapa feito no editor do campus (usado enquanto o admin não salvar).
 *
 * Formato (versão 3), medidas em metros, x = leste, z = sul:
 *   { versao, norte, limite:{x0,x1,z0,z1}, portao:{x,z}, blocos:[...] }
 *   prédio: { id, nome, tipo:'predio', x, z, rot, andares, partes, portas, escada, salas:[...] }
 *   sala:   { id, n, andar, x, z, w, d, porta:{lado,t}, cat, cor?, setor }
 * Os ids das salas do prédio principal são os mesmos da tabela `salas` (cra, napne, biblioteca...),
 * então os links "Ver no mapa" (mapa.php?sala=cra) e os QR das portas continuam funcionando.
 */

require_once __DIR__ . '/Repositorio.php';

const MAPA_CAMPUS_PADRAO = __DIR__ . '/mapa-campus-padrao.json';
const MAPA_CAMPUS_TIPOS = ['predio', 'patio', 'estac', 'campo', 'quadra', 'mata', 'verde', 'obra', 'arvore', 'caminho', 'ponto', 'rotulo'];
const MAPA_CAMPUS_CATEGORIAS = ['aula', 'lab', 'banheiro', 'adm', 'atend', 'grande', 'apoio', 'outro'];
const MAPA_CAMPUS_MAX_ANDARES = 4;

/** Arquivo onde o admin salva o mapa. */
function arquivoMapaCampus(): string
{
    return __DIR__ . '/../data/mapa-campus.json';
}

/** O mapa que vale agora: o salvo pelo admin ou, se ainda não houver, o padrão. */
function mapaCampus(): array
{
    static $mapa = null;
    if ($mapa !== null) {
        return $mapa;
    }
    foreach ([arquivoMapaCampus(), MAPA_CAMPUS_PADRAO] as $arquivo) {
        if (!is_file($arquivo)) {
            continue;
        }
        $dados = json_decode((string) file_get_contents($arquivo), true);
        $limpo = is_array($dados) ? limparMapaCampus($dados) : null;
        if ($limpo !== null) {
            return $mapa = $limpo;
        }
    }
    // Nenhum arquivo válido: mapa vazio (o site mostra só o terreno e o portão).
    return $mapa = ['versao' => 3, 'norte' => 0, 'limite' => ['x0' => -100, 'x1' => 150, 'z0' => -120, 'z1' => 80], 'portao' => ['x' => 0, 'z' => 81], 'blocos' => []];
}

/**
 * Confere e grava o mapa enviado pelo editor. Devolve o mapa limpo ou null se for inválido
 * ou se a pasta data/ não puder ser gravada. Guarda a versão anterior em data/mapa-campus-anterior.json.
 */
function salvarMapaCampus($dados): ?array
{
    $limpo = is_array($dados) ? limparMapaCampus($dados) : null;
    if ($limpo === null) {
        return null;
    }
    $arquivo = arquivoMapaCampus();
    $pasta = dirname($arquivo);
    if (!is_dir($pasta) && !@mkdir($pasta, 0775, true)) {
        return null;
    }
    // Ninguém abre os arquivos da pasta pelo navegador (o PHP lê direto do disco).
    if (!is_file($pasta . '/.htaccess')) {
        @file_put_contents($pasta . '/.htaccess', "# Dados do site: só o PHP lê, nunca pela URL.\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n    Order deny,allow\n    Deny from all\n</IfModule>\n");
    }
    if (is_file($arquivo)) {
        @copy($arquivo, $pasta . '/mapa-campus-anterior.json');
    }
    // Grava num temporário e troca de uma vez: quem abrir o site nunca pega um arquivo pela metade.
    $temporario = $arquivo . '.tmp';
    $json = json_encode($limpo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false || @file_put_contents($temporario, $json, LOCK_EX) === false || !@rename($temporario, $arquivo)) {
        @unlink($temporario);
        return null;
    }
    return $limpo;
}

/* ---------- validação (o mesmo cuidado do normalizar() do JS, no servidor) ---------- */

function mapaNumero($valor, float $padrao, float $minimo = -5000, float $maximo = 5000): float
{
    if (!is_int($valor) && !is_float($valor) && !(is_string($valor) && is_numeric($valor))) {
        return $padrao;
    }
    $numero = (float) $valor;
    return is_finite($numero) ? round(max($minimo, min($maximo, $numero)), 2) : $padrao;
}

function mapaTexto($valor, int $maximo): string
{
    if (!is_string($valor) && !is_int($valor) && !is_float($valor)) {
        return '';
    }
    $texto = trim(preg_replace('/[\x00-\x1F\x7F]/u', ' ', (string) $valor) ?? '');
    return mb_substr($texto, 0, $maximo);
}

function mapaId($valor): ?string
{
    $id = is_string($valor) || is_int($valor) ? (string) $valor : '';
    return preg_match('/^[A-Za-z0-9_-]{1,60}$/', $id) ? $id : null;
}

function mapaCor($valor): ?string
{
    return is_string($valor) && preg_match('/^#[0-9a-f]{6}$/i', $valor) ? strtolower($valor) : null;
}

/** Lista de retângulos {x,z,w,d} (partes do prédio). */
function mapaRetangulos($lista, int $maximo): array
{
    $saida = [];
    foreach (is_array($lista) ? array_slice($lista, 0, $maximo) : [] as $item) {
        if (!is_array($item)) {
            continue;
        }
        $w = mapaNumero($item['w'] ?? null, 0, 0, 2000);
        $d = mapaNumero($item['d'] ?? null, 0, 0, 2000);
        if ($w > 0.4 && $d > 0.4) {
            $saida[] = ['x' => mapaNumero($item['x'] ?? null, 0), 'z' => mapaNumero($item['z'] ?? null, 0), 'w' => $w, 'd' => $d];
        }
    }
    return $saida;
}

/** Limpa o mapa inteiro. null = não dá pra usar (faltam blocos ou o portão). */
function limparMapaCampus(array $dados): ?array
{
    if (!isset($dados['blocos'], $dados['portao']) || !is_array($dados['blocos']) || !is_array($dados['portao'])) {
        return null;
    }
    $limite = is_array($dados['limite'] ?? null) ? $dados['limite'] : [];
    $mapa = [
        'versao' => 3,
        'norte' => mapaNumero($dados['norte'] ?? 0, 0, -180, 180),
        'limite' => [
            'x0' => mapaNumero($limite['x0'] ?? null, -100), 'x1' => mapaNumero($limite['x1'] ?? null, 150),
            'z0' => mapaNumero($limite['z0'] ?? null, -120), 'z1' => mapaNumero($limite['z1'] ?? null, 80),
        ],
        'portao' => ['x' => mapaNumero($dados['portao']['x'] ?? null, 0), 'z' => mapaNumero($dados['portao']['z'] ?? null, 0)],
        'blocos' => [],
    ];
    $setoresValidos = array_column(consultar('SELECT seto_id AS id FROM setor'), 'id', 'id');
    $idsBlocos = [];
    $idsSalas = [];
    foreach (array_slice($dados['blocos'], 0, 800) as $bloco) {
        if (!is_array($bloco) || !($id = mapaId($bloco['id'] ?? null)) || isset($idsBlocos[$id])) {
            continue;
        }
        $idsBlocos[$id] = true;
        $tipo = in_array($bloco['tipo'] ?? '', MAPA_CAMPUS_TIPOS, true) ? $bloco['tipo'] : 'verde';
        $limpo = [
            'id' => $id,
            'nome' => mapaTexto($bloco['nome'] ?? '', 120),
            'tipo' => $tipo,
            'x' => mapaNumero($bloco['x'] ?? null, 0),
            'z' => mapaNumero($bloco['z'] ?? null, 0),
            'w' => mapaNumero($bloco['w'] ?? null, 10, 0.1, 2000),
            'd' => mapaNumero($bloco['d'] ?? null, 10, 0.1, 2000),
            'rot' => mapaNumero($bloco['rot'] ?? 0, 0, -360, 360),
            'andares' => 1,
            'salas' => [],
        ];
        if ($cor = mapaCor($bloco['cor'] ?? null)) {
            $limpo['cor'] = $cor;
        }
        if ($tipo === 'predio') {
            $limpo['andares'] = max(1, min(MAPA_CAMPUS_MAX_ANDARES, (int) ($bloco['andares'] ?? 1)));
            $limpo['partes'] = mapaRetangulos($bloco['partes'] ?? [], 40) ?: [['x' => 0, 'z' => 0, 'w' => $limpo['w'], 'd' => $limpo['d']]];
            $limpo['portas'] = [];
            foreach (is_array($bloco['portas'] ?? null) ? array_slice($bloco['portas'], 0, 20) : [] as $porta) {
                if (is_array($porta)) {
                    $limpo['portas'][] = ['x' => mapaNumero($porta['x'] ?? null, 0), 'z' => mapaNumero($porta['z'] ?? null, 0)];
                }
            }
            $escada = is_array($bloco['escada'] ?? null) ? $bloco['escada'] : [];
            $limpo['escada'] = [
                'x' => mapaNumero($escada['x'] ?? null, 0), 'z' => mapaNumero($escada['z'] ?? null, 0),
                'w' => mapaNumero($escada['w'] ?? null, 3, 0.5, 40), 'd' => mapaNumero($escada['d'] ?? null, 6, 0.5, 40),
            ];
            foreach (is_array($bloco['salas'] ?? null) ? array_slice($bloco['salas'], 0, 400) : [] as $sala) {
                if (!is_array($sala) || !($idSala = mapaId($sala['id'] ?? null)) || isset($idsSalas[$idSala])) {
                    continue;
                }
                $idsSalas[$idSala] = true;
                $porta = is_array($sala['porta'] ?? null) ? $sala['porta'] : [];
                $setor = mapaId($sala['setor'] ?? null);
                $salaLimpa = [
                    'id' => $idSala,
                    'n' => mapaTexto($sala['n'] ?? '', 120),
                    'andar' => max(0, min($limpo['andares'] - 1, (int) ($sala['andar'] ?? 0))),
                    'x' => mapaNumero($sala['x'] ?? null, 0),
                    'z' => mapaNumero($sala['z'] ?? null, 0),
                    'w' => mapaNumero($sala['w'] ?? null, 6, 0.3, 500),
                    'd' => mapaNumero($sala['d'] ?? null, 6, 0.3, 500),
                    'porta' => [
                        'lado' => in_array($porta['lado'] ?? '', ['N', 'S', 'L', 'O'], true) ? $porta['lado'] : 'S',
                        't' => mapaNumero($porta['t'] ?? 0.5, 0.5, 0, 1),
                    ],
                    'cat' => in_array($sala['cat'] ?? '', MAPA_CAMPUS_CATEGORIAS, true) ? $sala['cat'] : 'outro',
                    // setor que não existe mais vira "sem setor" (o botão "Ver página do setor" some)
                    'setor' => $setor !== null && isset($setoresValidos[$setor]) ? $setor : null,
                ];
                if ($cor = mapaCor($sala['cor'] ?? null)) {
                    $salaLimpa['cor'] = $cor;
                }
                $limpo['salas'][] = $salaLimpa;
            }
        }
        if ($tipo === 'caminho') {
            $pontos = [];
            foreach (is_array($bloco['pontos'] ?? null) ? array_slice($bloco['pontos'], 0, 300) : [] as $ponto) {
                if (is_array($ponto) && count($ponto) >= 2) {
                    $pontos[] = [mapaNumero($ponto[0], 0), mapaNumero($ponto[1], 0)];
                }
            }
            if (count($pontos) < 2) {
                continue;
            }
            $limpo['pontos'] = $pontos;
            $limpo['larg'] = mapaNumero($bloco['larg'] ?? 4, 4, 0.5, 30);
            $limpo['curva'] = !empty($bloco['curva']);
            if (!empty($bloco['curvaTocada'])) {
                $limpo['curvaTocada'] = true;
            }
        }
        if ($tipo === 'ponto') {
            $limpo['icone'] = mapaTexto($bloco['icone'] ?? '📍', 8) ?: '📍';
            $limpo['andar'] = max(0, min(MAPA_CAMPUS_MAX_ANDARES - 1, (int) ($bloco['andar'] ?? 0)));
        }
        $mapa['blocos'][] = $limpo;
    }
    return $mapa;
}

/* ---------- usados pelas páginas ---------- */

/** Setores pro mapa: nome e link da página (botão "Ver página do setor"). */
function listarSetoresParaMapa(string $prefixo = ''): array
{
    return array_map(static function (array $setor) use ($prefixo): array {
        return ['id' => $setor['id'], 'nome' => $setor['nome'], 'url' => $prefixo . 'setor.php?id=' . rawurlencode($setor['id'])];
    }, listarSetores());
}

/** A sala (ou o lugar) existe no mapa? Aceita o id da sala, o id do setor dela ou o id de um prédio/lugar. */
function existeNoMapaCampus(string $id): bool
{
    if ($id === '') {
        return false;
    }
    foreach (mapaCampus()['blocos'] as $bloco) {
        if ($bloco['id'] === $id) {
            return true;
        }
        foreach ($bloco['salas'] as $sala) {
            if ($sala['id'] === $id || $sala['setor'] === $id) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Arquivos do 3D (three.js r128). Só são baixados quando alguém clica em "Ver em 3D".
 * Com a pasta js/vendor/three/ completa usa a cópia local (funciona sem internet); senão, o jsDelivr.
 */
function scriptsMapaCampus3d(string $prefixo = ''): array
{
    $arquivos = ['three.min.js', 'OrbitControls.js', 'CSS2DRenderer.js'];
    $pasta = __DIR__ . '/../js/vendor/three/';
    $local = true;
    foreach ($arquivos as $arquivo) {
        $local = $local && is_file($pasta . $arquivo);
    }
    if ($local) {
        return array_map(static fn (string $arquivo): string => $prefixo . 'js/vendor/three/' . $arquivo, $arquivos);
    }
    return [
        'https://cdn.jsdelivr.net/npm/three@0.128.0/build/three.min.js',
        'https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js',
        'https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/renderers/CSS2DRenderer.js',
    ];
}
