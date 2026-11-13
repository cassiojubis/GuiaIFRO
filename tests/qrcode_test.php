<?php
/**
 * Testes do gerador de QR code (model/QrCode.php). Sem framework.
 *   C:\xampp\php\php.exe tests\qrcode_test.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../model/QrCode.php';

$falhas = 0;
function confereQr(string $descricao, $esperado, $obtido): void
{
    global $falhas;
    $ok = $esperado === $obtido;
    $falhas += $ok ? 0 : 1;
    echo ($ok ? '  ok   ' : '  FALHOU ') . $descricao . ($ok ? '' : ': esperava ' . var_export($esperado, true) . ', veio ' . var_export($obtido, true)) . "\n";
}

// Exemplo da norma/tutoriais: "HELLO WORLD" versão 1-M
confereQr('Reed-Solomon do exemplo HELLO WORLD 1-M', [196, 35, 39, 119, 235, 215, 231, 226, 93, 23],
    qrReedSolomon([32, 91, 11, 120, 209, 114, 220, 77, 67, 64, 236, 17, 236, 17, 236, 17], 10));
confereQr('texto curto usa a versão 1 (21x21)', 21, count(gerarQrCode('oi')));
confereQr('endereço de setor cabe na versão 4 (33x33)', 33, count(gerarQrCode('http://localhost/guiaIFRO/setor.php?id=cra&de=cra')));
confereQr('150 bytes usa a versão 8 (49x49)', 49, count(gerarQrCode(str_repeat('x', 150))));
$matriz = gerarQrCode('teste');
confereQr('quadrado do canto: borda escura', true, $matriz[0][0] && $matriz[0][6] && $matriz[6][0]);
confereQr('quadrado do canto: anel claro', false, $matriz[1][1] || $matriz[5][5]);
confereQr('o quadradinho escuro fixo existe', true, $matriz[count($matriz) - 8][8]);
try {
    gerarQrCode(str_repeat('x', 300));
    confereQr('texto grande demais dá erro', 'exceção', 'nenhuma');
} catch (InvalidArgumentException $erro) {
    confereQr('texto grande demais dá erro', true, true);
}
echo $falhas === 0 ? "\nTodos os testes do QR passaram.\n" : "\n{$falhas} teste(s) falharam.\n";
exit($falhas === 0 ? 0 : 1);
