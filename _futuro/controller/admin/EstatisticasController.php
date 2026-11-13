<?php
/**
 * ESTATÍSTICAS (admin/estatisticas.php) — N9
 * Só contadores por dia (tabelas estatisticas_dia e buscas_log): nada de IP,
 * cookie ou dado pessoal. ?csv=1 baixa tudo do período em planilha.
 */
require_once __DIR__ . '/BaseAdmin.php';
exigirAdminGeral();

$tituloPaginaAdmin = 'Estatísticas';
$paginaAtualAdmin = 'estatisticas';
$periodos = [7 => 'Últimos 7 dias', 30 => 'Últimos 30 dias', 90 => 'Últimos 90 dias'];
$dias = (int) ($_GET['dias'] ?? 30);
if (!isset($periodos[$dias])) {
    $dias = 30;
}

if (!empty($_GET['csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="estatisticas-guia-ifro-' . $dias . 'dias-' . date('Y-m-d') . '.csv"');
    $saida = fopen('php://output', 'w');
    fwrite($saida, "\xEF\xBB\xBF"); // BOM: o Excel abre os acentos certo
    fputcsv($saida, ['dia', 'tipo', 'chave', 'quantidade'], ';');
    foreach (exportarEstatisticas($dias) as $linha) {
        // evita fórmula no Excel (=, +, -, @ no começo viram texto)
        $linha['chave'] = preg_match('/^[=+\-@]/', $linha['chave']) ? "'" . $linha['chave'] : $linha['chave'];
        fputcsv($saida, $linha, ';');
    }
    fclose($saida);
    exit;
}

$nomesSetores = array_column(listarSetores(true), 'nome', 'id');
$nomesSalas = array_column(listarMapaSalas()['salas'], 'nome', 'id');
$nomesPaginas = [
    'inicio' => 'Início', 'categorias' => 'Categorias / busca', 'mapa' => 'Mapa', 'sobre' => 'Sobre', 'contato' => 'Contato',
    'acessibilidade' => 'Acessibilidade', 'privacidade' => 'Privacidade', 'noticias' => 'Avisos e notícias',
    'calendario' => 'Calendário', 'setor' => 'Páginas de setor', 'sala' => 'Fichas de sala', 'erro-404' => 'Página não encontrada (404)', 'outra' => 'Outras',
    'como-faco' => 'Como faço para…', 'primeiro-dia' => 'Primeiro dia', 'minha-turma' => 'Minha turma', 'estrangeiros' => 'Para estrangeiros',
];
$nomesAcessibilidade = [
    'fonte-normal' => 'Fonte normal', 'fonte-media' => 'Fonte média (A+)', 'fonte-grande' => 'Fonte grande (A++)',
    'tema-escuro' => 'Ligou o modo escuro', 'tema-claro' => 'Voltou pro modo claro',
];

$acessosPorDia = totalPorDia('pagina', $dias);
$totalAcessos = array_sum($acessosPorDia);
$paginasMais = rankingEstatistica('pagina', $dias, 12);
$setoresMais = rankingEstatistica('setor', $dias, 12);
$buscasMais = rankingBuscas($dias, false, 15);
$buscasSemResultado = rankingBuscas($dias, true, 15);
$rotasMais = array_map(static function ($linha) use ($nomesSalas) {
    [$de, $para] = array_pad(explode('>', $linha['chave'], 2), 2, '');
    $nomeDe = ['portao' => 'Portão', 'entrada' => 'Entrada'][$de] ?? ($nomesSalas[$de] ?? $de);
    $linha['rotulo'] = $nomeDe . ' → ' . ($nomesSalas[$para] ?? $para);
    return $linha;
}, rankingEstatistica('rota', $dias, 12));
$acessibilidade = rankingEstatistica('acessibilidade', $dias, 10);
$idiomas = rankingEstatistica('idioma', $dias, 5);

require __DIR__ . '/../../view/admin/estatisticas.php';
