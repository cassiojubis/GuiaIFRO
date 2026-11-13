<?php
/**
 * E-MAIL PEDINDO REVISÃO DOS SETORES (N7)
 *
 * Manda um e-mail para o responsável de cada setor que passou do prazo sem
 * revisão (admin › Configurações › "Pedir revisão do setor depois de quantos dias").
 * Cada setor recebe no máximo um e-mail por semana.
 *
 * Rodar uma vez por dia. No Windows, pelo Agendador de Tarefas:
 *   Programa: C:\xampp\php\php.exe
 *   Argumentos: C:\xampp\htdocs\guiaIFRO\bin\avisar-revisao.php
 * Teste sem enviar nada: acrescente --simular
 *
 * Precisa do envio de e-mail do PHP configurado (mail() / sendmail do XAMPP).
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../model/Config.php';
require __DIR__ . '/../model/Repositorio.php';

$simular = in_array('--simular', $argv, true);
$dias = max(7, (int) configuracao('revisao_dias', '90'));
$enderecoAdmin = urlBaseSite() . '/admin/setores.php';

$enviados = 0;
$semResponsavel = [];
foreach (listarAvisosDeRevisaoPendentes($dias) as $setor) {
    if (!$setor['responsaveis']) {
        $semResponsavel[] = $setor['nome'];
        continue;
    }
    $quando = $setor['revisado_em'] ? 'desde ' . (new DateTimeImmutable($setor['revisado_em']))->format('d/m/Y') : 'ainda nunca foi revisado';
    foreach ($setor['responsaveis'] as $pessoa) {
        $assunto = '[Guia IFRO] Confira os dados de ' . ($setor['sigla'] ?: $setor['nome']);
        $corpo = "Olá, {$pessoa['nome']}!\n\n"
            . "O setor {$setor['nome']} está sem revisão no Guia IFRO ({$quando}).\n"
            . "Entre no painel, confira telefone, e-mail, horário e serviços, e salve (ou clique em \"Está tudo certo\"):\n"
            . "{$enderecoAdmin}\n\nObrigado por manter o Guia certinho para os estudantes!\n";
        if ($simular) {
            echo "[simulação] para {$pessoa['email']}: {$assunto}\n";
            continue;
        }
        $ok = @mail($pessoa['email'], '=?UTF-8?B?' . base64_encode($assunto) . '?=', $corpo, "Content-Type: text/plain; charset=UTF-8\r\n");
        echo ($ok ? 'enviado' : 'FALHOU') . " para {$pessoa['email']} ({$setor['nome']})\n";
        $enviados += $ok ? 1 : 0;
    }
    if (!$simular) {
        marcarAvisoDeRevisaoEnviado($setor['id']);
    }
}

echo "\n{$enviados} e-mail(s) enviados.\n";
if ($semResponsavel) {
    echo count($semResponsavel) . " setor(es) vencidos sem responsável com e-mail (ligue alguém em admin › Usuários):\n  - " . implode("\n  - ", $semResponsavel) . "\n";
}
