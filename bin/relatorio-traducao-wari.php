<?php
/**
 * RELATÓRIO DA TRADUÇÃO EM ORO WARI
 * Lista o que já tem tradução e o que continua pendente (em português no site),
 * a partir do model/idiomas/wari.php.
 *
 * Uso (no terminal, na pasta do projeto):
 *   php bin/relatorio-traducao-wari.php            -> mostra o resumo
 *   php bin/relatorio-traducao-wari.php --salvar   -> também grava docs/TRADUCAO-ORO-WARI.md
 */
if (PHP_SAPI !== 'cli') {
    exit('Use pelo terminal.');
}

$dicionario = require __DIR__ . '/../model/idiomas/wari.php';

$nomesPaginas = [
    'index' => 'Início (index.php)', 'mapa' => 'Mapa (mapa.php)', 'categorias' => 'Categorias (categorias.php)',
    'setor' => 'Detalhes do setor (setor.php)', 'sobre' => 'Sobre o IFRO + Diversidade (sobre.php)', 'contato' => 'Fale Conosco (contato.php)',
    'acessibilidade' => 'Acessibilidade (acessibilidade.php)', 'privacidade' => 'Política de Privacidade (privacidade.php)', '404' => 'Página não encontrada (404)',
];

// Junta tudo em grupos: [título => [português => tradução]]
$grupos = ['Elementos comuns (menu, rodapé, notícias)' => $dicionario['comum']];
foreach ($dicionario['paginas'] as $pagina => $textos) {
    $grupos[$nomesPaginas[$pagina] ?? $pagina] = $textos;
}
foreach ($dicionario['escopos'] as $escopo) {
    $titulo = 'Trecho ' . $escopo['seletor'] . (!empty($escopo['pagina']) ? ' (' . ($nomesPaginas[$escopo['pagina']] ?? $escopo['pagina']) . ')' : '');
    $grupos[$titulo] = array_merge($grupos[$titulo] ?? [], $escopo['textos']);
}
$padroes = [];
foreach ($dicionario['padroes'] as $padrao) {
    $padroes[$padrao['origem']] = $padrao['expr'] !== '' ? $padrao['troca'] : '';
}
$grupos['Frases com número/ano'] = $padroes;

$total = 0;
$traduzidos = 0;
$md = "# Tradução Oro Wari (Pakaa Nova) — situação\n\n";
$md .= "Gerado por `php bin/relatorio-traducao-wari.php --salvar` em " . date('d/m/Y H:i') . ".\n";
$md .= "Fonte: documento *tradução oro wari (pakaa nova).pdf*, guardado em `model/idiomas/wari.php`.\n\n";
$corpo = '';
foreach ($grupos as $titulo => $textos) {
    $feitos = array_filter($textos, static fn ($t) => trim($t) !== '');
    $faltam = array_diff_key($textos, $feitos);
    $total += count($textos);
    $traduzidos += count($feitos);
    $corpo .= "## {$titulo} — " . count($feitos) . '/' . count($textos) . "\n\n";
    if ($feitos) {
        $corpo .= "**Traduzidos**\n\n| Português | Oro Wari |\n|---|---|\n";
        foreach ($feitos as $pt => $wari) {
            $corpo .= '| ' . str_replace('|', '\|', $pt) . ' | ' . str_replace('|', '\|', $wari) . " |\n";
        }
        $corpo .= "\n";
    }
    if ($faltam) {
        $corpo .= "**Pendentes (aparecem em português)**\n\n";
        foreach (array_keys($faltam) as $pt) {
            $corpo .= "- {$pt}\n";
        }
        $corpo .= "\n";
    }
}
$porcento = $total ? round($traduzidos * 100 / $total) : 0;
$md .= "**Total: {$traduzidos} de {$total} textos traduzidos ({$porcento}%) — tradução PARCIAL, em validação.**\n\n" . $corpo;

echo "Oro Wari: {$traduzidos} de {$total} textos traduzidos ({$porcento}%).\n";
foreach ($grupos as $titulo => $textos) {
    $feitos = count(array_filter($textos, static fn ($t) => trim($t) !== ''));
    printf("  %-70s %3d/%d\n", $titulo, $feitos, count($textos));
}

if (in_array('--salvar', $argv, true)) {
    file_put_contents(__DIR__ . '/../docs/TRADUCAO-ORO-WARI.md', $md);
    echo "Relatório salvo em docs/TRADUCAO-ORO-WARI.md\n";
}
