<?php
/**
 * CONTADOR ANÔNIMO (contar.php?tipo=rota&chave=portao>cra) — N9
 * O JavaScript avisa quando alguém pede uma rota no mapa ou muda a acessibilidade.
 * Só soma +1 no dia; não guarda IP, cookie nem nada da pessoa.
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$tipo = (string) ($_GET['tipo'] ?? '');
$chave = (string) ($_GET['chave'] ?? '');
if (in_array($tipo, ['rota', 'acessibilidade'], true) && preg_match('/^[a-z0-9_>-]{1,100}$/', $chave)) {
    contarEstatistica($tipo, $chave);
}
http_response_code(204);
