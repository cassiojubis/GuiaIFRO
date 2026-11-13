<?php
/**
 * IDIOMAS DO SITE (I6)
 *
 * O português fica aqui no código (é o texto original). Espanhol e Oro Wari
 * vêm da tabela `traducoes`, e só aparece no site o que está PUBLICADO.
 * Faltou tradução? Mostra o português (nunca deixa a tela vazia).
 *
 * Fluxo de cada tradução (admin › Traduções): rascunho -> revisado -> publicado.
 * Oro Wari: só pode ser publicado depois de revisado por falante da língua;
 * enquanto não houver nada publicado, a opção mostra o aviso explicando isso.
 */

require_once __DIR__ . '/Config.php'; // conexaoSegura() do cookie de idioma
require_once __DIR__ . '/Repositorio.php';

$idiomasDisponiveis = [
    'pt' => ['nome' => 'Português', 'bandeira' => '🇧🇷', 'html' => 'pt-BR'],
    'es' => ['nome' => 'Español', 'bandeira' => '🇪🇸', 'html' => 'es'],
    'wari' => ['nome' => 'Oro Wari (Pakaa Nova)', 'bandeira' => '🌿', 'html' => 'pnv'],
];

/** Textos da interface em português (a fonte de tudo). Chave => texto. */
const TEXTOS_INTERFACE_PT = [
    'nav_inicio' => 'Início',
    'nav_categorias' => 'Categorias',
    'nav_mapa' => 'Mapa',
    'nav_sobre' => 'Sobre',
    'nav_contato' => 'Contato',
    'busca_placeholder' => 'Buscar setor...',
    'btn_fale_conosco' => 'Fale conosco',
    'rodape_navegacao' => 'Navegação',
    'rodape_direitos' => 'Todos os direitos reservados.',
    'rodape_acessibilidade' => 'Acessibilidade',
    'meta_descricao' => 'Sistema de navegação e informações do IFRO Campus Guajará-Mirim.',
];

/** Cookie que lembra o idioma mesmo depois de fechar o navegador. */
const COOKIE_IDIOMA = 'guiaifro_idioma';

/**
 * Dicionário do Oro Wari (model/idiomas/wari.php), feito a partir do documento de
 * tradução. Lido uma vez por requisição.
 */
function dicionarioWari(): array
{
    static $dicionario = null;
    if ($dicionario === null) {
        $dicionario = require __DIR__ . '/idiomas/wari.php';
    }
    return $dicionario;
}

/**
 * Tradução em Oro Wari de um texto em português, ou null se ainda não tem.
 * Procura primeiro na página (nome do arquivo, ex.: 'setor'), depois no comum.
 */
function textoWari(string $textoPt, string $pagina = ''): ?string
{
    $dicionario = dicionarioWari();
    foreach ([$dicionario['paginas'][$pagina] ?? [], $dicionario['comum']] as $textos) {
        $traducao = trim((string) ($textos[$textoPt] ?? ''));
        if ($traducao !== '') {
            return $traducao;
        }
    }
    return null;
}

/**
 * Quantos textos do documento já têm tradução: ['traduzidos' => n, 'total' => n].
 * Os padrões sem expressão (só registrados como pendentes) também contam no total.
 */
function progressoWari(): array
{
    $dicionario = dicionarioWari();
    $grupos = array_merge([$dicionario['comum']], array_values($dicionario['paginas']), array_column($dicionario['escopos'], 'textos'));
    $traduzidos = 0;
    $total = 0;
    foreach ($grupos as $textos) {
        foreach ($textos as $traducao) {
            $total++;
            $traduzidos += trim($traducao) !== '' ? 1 : 0;
        }
    }
    foreach ($dicionario['padroes'] as $padrao) {
        $total++;
        $traduzidos += $padrao['expr'] !== '' ? 1 : 0;
    }
    return ['traduzidos' => $traduzidos, 'total' => $total];
}

/** Dados que o js/traducao-wari.js usa (só o que tem tradução; o resto fica em português). */
function dicionarioWariParaJs(): array
{
    $soPreenchidos = static fn (array $textos): array => array_filter($textos, static fn ($texto) => trim((string) $texto) !== '');
    $dicionario = dicionarioWari();
    return [
        'comum' => $soPreenchidos($dicionario['comum']),
        'paginas' => array_map($soPreenchidos, $dicionario['paginas']),
        'escopos' => array_map(static fn ($escopo) => [
            'pagina' => $escopo['pagina'] ?? '',
            'seletor' => $escopo['seletor'],
            'textos' => $soPreenchidos($escopo['textos']),
        ], $dicionario['escopos']),
        'padroes' => array_values(array_filter($dicionario['padroes'], static fn ($padrao) => $padrao['expr'] !== '')),
    ];
}

/**
 * O idioma pode ser escolhido? Oro Wari quando houver tradução publicada no admin
 * ou vinda do documento de tradução (model/idiomas/wari.php).
 */
function idiomaPodeSerUsado(string $codigo): bool
{
    if ($codigo === 'pt' || $codigo === 'es') {
        return true;
    }
    return $codigo === 'wari' && (progressoWari()['traduzidos'] > 0 || contarTraducoesPublicadas('wari') > 0);
}

/** Idioma atual: o da sessão, senão o do cookie (padrão: português). */
function idiomaAtual(): string
{
    $idioma = (string) ($_SESSION['idioma'] ?? $_COOKIE[COOKIE_IDIOMA] ?? 'pt');
    return idiomaPodeSerUsado($idioma) ? $idioma : 'pt';
}

/** Guarda o idioma escolhido na sessão e num cookie de 1 ano (só o código: pt, es ou wari). */
function salvarIdioma(string $codigo): void
{
    $_SESSION['idioma'] = $codigo;
    setcookie(COOKIE_IDIOMA, $codigo, [
        'expires' => time() + 365 * 24 * 60 * 60,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => conexaoSegura(),
    ]);
}

/** Página usada pra escolher a tradução certa (nome do arquivo; '404' na página de erro). */
function paginaTraducao(): string
{
    if (http_response_code() === 404) {
        return '404';
    }
    return basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '.php');
}

/** Valor do atributo lang do <html> pro idioma atual. */
function idiomaHtml(): string
{
    return $GLOBALS['idiomasDisponiveis'][idiomaAtual()]['html'] ?? 'pt-BR';
}

/**
 * Texto da interface no idioma atual (ou em português, se faltar).
 * Já sai escapado pra HTML (as traduções em Oro Wari têm apóstrofo).
 * Ordem no Oro Wari: o que foi publicado no admin, depois o documento de tradução.
 */
function t(string $chave): string
{
    $padrao = TEXTOS_INTERFACE_PT[$chave] ?? $chave;
    $idioma = idiomaAtual();
    $texto = $padrao;
    if ($idioma !== 'pt') {
        $texto = traducoesPublicadas($idioma)['interface||' . $chave]['texto']
            ?? ($idioma === 'wari' ? textoWari($padrao) : null)
            ?? $padrao;
    }
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

/**
 * Conteúdo (nome/descrição de setor...) no idioma atual, ou o original em português.
 * Ex.: traduzirConteudo('setor', 'cra', 'descricao', $setor['descricao'])
 */
function traduzirConteudo(string $entidade, string $idEntidade, string $campo, ?string $textoPt): ?string
{
    $idioma = idiomaAtual();
    if ($idioma === 'pt' || $textoPt === null || $textoPt === '') {
        return $textoPt;
    }
    return traducoesPublicadas($idioma)["{$entidade}|{$idEntidade}|{$campo}"]['texto'] ?? $textoPt;
}

/** Áudio gravado (ex.: por falante de Oro Wari) de um conteúdo no idioma atual, se houver. */
function audioDoConteudo(string $entidade, string $idEntidade, string $campo): ?string
{
    $idioma = idiomaAtual();
    if ($idioma === 'pt') {
        return null;
    }
    $traducao = traducoesPublicadas($idioma)["{$entidade}|{$idEntidade}|{$campo}"] ?? null;
    return $traducao['audio_url'] ?? null;
}
