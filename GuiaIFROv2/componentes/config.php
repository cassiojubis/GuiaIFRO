<?php
/**
 * CONFIGURAÇÕES GERAIS DO PROJETO
 *
 * Este arquivo concentra informações compartilhadas por todas as páginas.
 * No futuro, o backend poderá substituir estes valores por dados do banco.
 */

define('NOME_SISTEMA', 'Guia IFRO');
define('NOME_CAMPUS', 'Campus Guajará-Mirim');
define('ANO_ATUAL', '2026');

/**
 * Retorna a classe "active" quando o link corresponde à página atual.
 * Isso permite destacar o item correto no menu sem repetir lógica.
 */
function linkAtivo(string $pagina, string $paginaAtual): string
{
    return $pagina === $paginaAtual ? 'active' : '';
}

/**
 * Gera um ícone do Lucide.
 * O JavaScript transforma esta tag em SVG quando a página é carregada.
 */
function icone(string $nome, string $classe = ''): string
{
    $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
    $classeSegura = htmlspecialchars($classe, ENT_QUOTES, 'UTF-8');

    return "<i data-lucide=\"{$nomeSeguro}\" class=\"icon {$classeSegura}\"></i>";
}
