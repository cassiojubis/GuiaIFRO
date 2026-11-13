<?php
/**
 * Testes da leitura do CSV do quadro de horários (model/QuadroCsv.php).
 *   C:\xampp\php\php.exe tests\quadro_test.php
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../model/QuadroCsv.php';

$falhas = 0;
function confereQuadro(string $descricao, $esperado, $obtido): void
{
    global $falhas;
    $ok = $esperado === $obtido;
    $falhas += $ok ? 0 : 1;
    echo ($ok ? '  ok   ' : '  FALHOU ') . $descricao . ($ok ? '' : ': esperava ' . var_export($esperado, true) . ', veio ' . var_export($obtido, true)) . "\n";
}

$salas = [['id' => 'biblioteca', 'nome' => 'Biblioteca', 'nomeCurto' => 'Biblioteca']];

$csv = "turma;dia;inicio;fim;disciplina;professor;sala\n1A;Segunda;07:00;07:50;Matemática;Ana;Biblioteca\n1A;terça-feira;7h50;8h40;Física;;Sala 2\n";
$r = lerQuadroCsv($csv, $salas);
confereQuadro('lê 2 aulas', 2, count($r['aulas']));
confereQuadro('sem erros', [], $r['erros']);
confereQuadro('turma encontrada', ['1A'], $r['turmas']);
confereQuadro('dia "Segunda" = 1', 1, $r['aulas'][0]['dia_semana']);
confereQuadro('hora "7h50" vira 07:50:00', '07:50:00', $r['aulas'][1]['inicio']);
confereQuadro('sala do mapa reconhecida', 'biblioteca', $r['aulas'][0]['sala_id']);
confereQuadro('sala fora do mapa fica só como texto', [null, 'Sala 2'], [$r['aulas'][1]['sala_id'], $r['aulas'][1]['sala_texto']]);

$r = lerQuadroCsv("turma,dia,horario,disciplina\n2B,quarta,13:00-13:50,Química\n");
confereQuadro('separador vírgula e coluna "horario"', ['13:00:00', '13:50:00'], [$r['aulas'][0]['inicio'], $r['aulas'][0]['fim']]);

$r = lerQuadroCsv("turma;dia;inicio;fim;disciplina\n1A;dia-nenhum;07:00;07:50;X\n1A;segunda;08:00;07:00;Y\n;segunda;07:00;07:50;Z\n");
confereQuadro('3 linhas com erro (dia, horário invertido, turma vazia)', [2, 3, 4], array_keys($r['erros']));
confereQuadro('nenhuma aula aproveitada', 0, count($r['aulas']));

$r = lerQuadroCsv("turma;disciplina\n1A;X\n");
confereQuadro('cabeçalho sem colunas obrigatórias dá erro na linha 1', [1], array_keys($r['erros']));

$r = lerQuadroCsv(mb_convert_encoding("turma;dia;inicio;fim;disciplina\n1A;terça;07:00;07:50;Educação Física\n", 'Windows-1252', 'UTF-8'));
confereQuadro('arquivo do Excel (Windows-1252) vira UTF-8', 'Educação Física', $r['aulas'][0]['disciplina'] ?? null);

echo $falhas === 0 ? "\nTodos os testes do quadro passaram.\n" : "\n{$falhas} teste(s) falharam.\n";
exit($falhas === 0 ? 0 : 1);
