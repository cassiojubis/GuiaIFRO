<?php
/**
 * "PARA QUEM VEM DE OUTRO PAÍS" (estrangeiros.php) — I7
 * Página em português e espanhol. Os documentos exigidos e o "como chegar"
 * vêm de admin › Configurações: enquanto o campus não informar, a página só
 * orienta a procurar o CRA e a CAED (nenhuma regra é inventada aqui).
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$tituloPagina = 'Para quem vem de outro país · Si vienes de otro país';
$paginaAtual = 'estrangeiros';

$linhas = static function (string $chave): array {
    return array_values(array_filter(array_map('trim', preg_split('/\R/u', configuracao($chave)))));
};
$conteudo = [
    'pt' => ['documentos' => $linhas('estrangeiros_documentos_pt'), 'chegada' => configuracao('estrangeiros_chegada_pt')],
    'es' => ['documentos' => $linhas('estrangeiros_documentos_es'), 'chegada' => configuracao('estrangeiros_chegada_es')],
];
$salaCra = listarSalasDoSetor('cra')[0] ?? null;
$salaCaed = listarSalasDoSetor('caed')[0] ?? null;

require __DIR__ . '/../view/estrangeiros.php';
