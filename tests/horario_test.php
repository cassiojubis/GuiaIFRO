<?php
/**
 * Testes do selo "Aberto agora" (setorEstaAberto em model/Repositorio.php).
 * Sem framework: cada teste é um assert simples.
 *
 * Rodar no terminal, na pasta do projeto:
 *   C:\xampp\php\php.exe tests\horario_test.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../model/Horario.php';
date_default_timezone_set('America/Porto_Velho');

$falhas = 0;
$total = 0;

function confere(string $descricao, $esperado, $obtido): void
{
    global $falhas, $total;
    $total++;
    if ($esperado === $obtido) {
        echo "  ok   {$descricao}\n";
        return;
    }
    $falhas++;
    echo "  FALHOU {$descricao}: esperava " . var_export($esperado, true) . ', veio ' . var_export($obtido, true) . "\n";
}

function quando(string $data): DateTimeImmutable
{
    return new DateTimeImmutable($data, new DateTimeZone('America/Porto_Velho'));
}

// 2026-10-05 é uma segunda-feira.
$segunda10h = quando('2026-10-05 10:00');
$segunda13h = quando('2026-10-05 13:00');
$terca14h = quando('2026-10-06 14:00');
$terca20h = quando('2026-10-06 20:00');
$sexta18h = quando('2026-10-09 18:00');
$sabado10h = quando('2026-10-10 10:00');

echo "Horário sem dia (vale todo dia)\n";
confere('dentro do horário', true, setorEstaAberto('08:00 às 17:00', $segunda10h));
confere('fora do horário', false, setorEstaAberto('08:00 às 17:00', $terca20h));
confere('vários turnos separados por /', true, setorEstaAberto('07:00 às 13:00 / 13:00 às 18:00 / 18:00 às 21:00', $terca20h));
confere('horário de fechamento já é fechado', false, setorEstaAberto('08:00 às 13:00', $segunda13h));
confere('horário de abertura já é aberto', true, setorEstaAberto('13:00 às 18:00', $segunda13h));

echo "Horário com dia\n";
$cra = 'Segunda: 08:00 às 12:00 e 14:00 às 18:00 | Terça: 13:00 às 18:00 e 19:00 às 22:00';
confere('CRA segunda 10h aberto', true, setorEstaAberto($cra, $segunda10h));
confere('CRA segunda 13h fechado (almoço)', false, setorEstaAberto($cra, $segunda13h));
confere('CRA terça 14h aberto', true, setorEstaAberto($cra, $terca14h));
confere('CRA terça 20h aberto (noite)', true, setorEstaAberto($cra, $terca20h));
confere('CRA sábado: dia errado', false, setorEstaAberto($cra, $sabado10h));

echo "Intervalo de dias\n";
$caed = 'Segunda a Quinta: 07:00 às 13:15 | Sexta: 07:00 às 19:00';
confere('CAED terça 10h aberto (dentro de "Segunda a Quinta")', true, setorEstaAberto($caed, quando('2026-10-06 10:00')));
confere('CAED terça 14h fechado', false, setorEstaAberto($caed, $terca14h));
confere('CAED sexta 18h aberto', true, setorEstaAberto($caed, $sexta18h));
confere('CAED sábado fechado', false, setorEstaAberto($caed, $sabado10h));

echo "Texto sem horário reconhecido\n";
confere('"Durante os turnos letivos" não mostra selo', null, setorEstaAberto('Durante os turnos letivos', $segunda10h));
confere('texto vazio não mostra selo', null, setorEstaAberto('', $segunda10h));

echo "Dias citados no texto\n";
confere('intervalo segunda a sexta', [1, 2, 3, 4, 5], diasCitadosNoTexto('Segunda a Sexta'));
confere('dia com -feira e acento', [2, 6], diasCitadosNoTexto('terça-feira e sábado'));
confere('sem dia', [], diasCitadosNoTexto('08:00 às 12:00'));

echo "Horário estruturado (B4)\n";
$craFaixas = converterHorarioTexto($cra);
confere('converte o texto do CRA em 4 faixas', 4, count($craFaixas ?? []));
confere('não converte texto sem dia (não inventa sábado)', null, converterHorarioTexto('07:00 às 13:00 / 13:00 às 18:00'));
confere('não converte "Durante os turnos letivos"', null, converterHorarioTexto('Durante os turnos letivos'));
$caedFaixas = converterHorarioTexto($caed);
confere('resumo da semana da CAED', 'Segunda a quinta: 7h às 13h15 · Sexta: 7h às 19h', resumoHorarioSemana($caedFaixas));
confere('resumo junta faixas encostadas', 'Segunda: 7h às 18h', resumoHorarioSemana([
    ['dia_semana' => 1, 'abre' => '07:00:00', 'fecha' => '13:00:00'],
    ['dia_semana' => 1, 'abre' => '13:00:00', 'fecha' => '18:00:00'],
]));
confere('aberto: diz quando fecha', ['aberto' => true, 'resumo' => 'Aberto agora · fecha às 12h'], estadoHorario($craFaixas, [], $segunda10h));
confere('almoço: diz quando abre de novo', ['aberto' => false, 'resumo' => 'Fechado agora · abre às 14h'], estadoHorario($craFaixas, [], $segunda13h));
confere('sábado: próximo dia que abre', ['aberto' => false, 'resumo' => 'Fechado agora · abre segunda às 8h'], estadoHorario($craFaixas, [], $sabado10h));
confere('terça 23h: abre na segunda (pula dias sem horário)', ['aberto' => false, 'resumo' => 'Fechado agora · abre segunda às 8h'], estadoHorario($craFaixas, [], quando('2026-10-06 23:00')));
$feriado = ['2026-10-05' => [['fechado' => 1, 'abre' => null, 'fecha' => null, 'motivo' => 'Feriado']]];
confere('feriado fecha o dia todo', false, estadoHorario($craFaixas, $feriado, $segunda10h)['aberto']);
confere('feriado aparece no texto', 'Fechado hoje (Feriado) · abre amanhã às 13h', estadoHorario($craFaixas, $feriado, $segunda10h)['resumo']);
$especial = ['2026-10-05' => [['fechado' => 0, 'abre' => '09:00:00', 'fecha' => '11:00:00', 'motivo' => 'Meio expediente']]];
confere('horário especial do dia vale no lugar do normal', ['aberto' => true, 'resumo' => 'Aberto agora · fecha às 11h'], estadoHorario($craFaixas, $especial, $segunda10h));
confere('horário especial: depois dele fecha (mesmo que o normal fosse até 12h)', false, estadoHorario($craFaixas, $especial, quando('2026-10-05 11:30'))['aberto']);
confere('sem horário cadastrado: sem selo', ['aberto' => null, 'resumo' => null], estadoHorario([], [], $segunda10h));

echo "\n" . ($total - $falhas) . " de {$total} testes passaram.\n";
exit($falhas === 0 ? 0 : 1);
