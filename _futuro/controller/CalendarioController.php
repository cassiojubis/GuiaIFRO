<?php
/**
 * CALENDÁRIO ACADÊMICO (calendario.php) — N6
 *   calendario.php            -> página com os próximos eventos, por mês
 *   calendario.php?ics=todos  -> arquivo .ics com todo o calendário (Google Agenda, celular...)
 *   calendario.php?ics=12     -> .ics de um evento só
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$tiposEvento = ['evento' => 'Evento', 'letivo' => 'Período letivo', 'prova' => 'Provas', 'feriado' => 'Feriado', 'recesso' => 'Recesso'];

/** Texto no formato do .ics: escapa \ ; , e quebra de linha. */
function textoIcs(string $texto): string
{
    return str_replace(["\\", ';', ',', "\r\n", "\n"], ["\\\\", '\;', '\,', '\n', '\n'], $texto);
}

/** Linhas do .ics não podem passar de 75 bytes: quebra e continua com um espaço. */
function linhaIcs(string $linha): string
{
    $saida = '';
    while (strlen($linha) > 75) {
        $corte = 75;
        // não corta no meio de uma letra acentuada (UTF-8)
        while ($corte > 0 && (ord($linha[$corte]) & 0xC0) === 0x80) {
            $corte--;
        }
        $saida .= substr($linha, 0, $corte) . "\r\n ";
        $linha = substr($linha, $corte);
    }
    return $saida . $linha . "\r\n";
}

if (isset($_GET['ics'])) {
    $pedido = (string) $_GET['ics'];
    $eventos = $pedido === 'todos' ? listarEventos() : array_filter([buscarEvento((int) $pedido)]);
    if (!$eventos) {
        http_response_code(404);
        exit('Evento não encontrado.');
    }
    $agoraUtc = gmdate('Ymd\THis\Z');
    $ics = linhaIcs('BEGIN:VCALENDAR') . linhaIcs('VERSION:2.0') . linhaIcs('PRODID:-//IFRO Guajara-Mirim//Guia IFRO//PT-BR')
        . linhaIcs('CALSCALE:GREGORIAN') . linhaIcs('METHOD:PUBLISH') . linhaIcs('X-WR-CALNAME:' . textoIcs('IFRO Guajará-Mirim — Calendário'));
    foreach ($eventos as $evento) {
        // evento de dia inteiro: o fim no .ics é o dia SEGUINTE ao último dia
        $fim = (new DateTimeImmutable($evento['fim'] ?: $evento['inicio']))->modify('+1 day');
        $ics .= linhaIcs('BEGIN:VEVENT')
            . linhaIcs('UID:evento-' . (int) $evento['id'] . '@guia-ifro-guajara')
            . linhaIcs('DTSTAMP:' . $agoraUtc)
            . linhaIcs('DTSTART;VALUE=DATE:' . str_replace('-', '', $evento['inicio']))
            . linhaIcs('DTEND;VALUE=DATE:' . $fim->format('Ymd'))
            . linhaIcs('SUMMARY:' . textoIcs($evento['titulo']))
            . linhaIcs('CATEGORIES:' . textoIcs($tiposEvento[$evento['tipo']] ?? 'Evento'))
            . ($evento['descricao'] ? linhaIcs('DESCRIPTION:' . textoIcs($evento['descricao'])) : '')
            . linhaIcs('END:VEVENT');
    }
    $ics .= linhaIcs('END:VCALENDAR');
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . ($pedido === 'todos' ? 'calendario-ifro-guajara' : 'evento-' . (int) $pedido) . '.ics"');
    echo $ics;
    exit;
}

$tituloPagina = 'Calendário acadêmico';
$paginaAtual = 'calendario';
$meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
$eventosPorMes = [];
foreach (listarEventos(date('Y-m-d')) as $evento) {
    $data = new DateTimeImmutable($evento['inicio']);
    $eventosPorMes[ucfirst($meses[(int) $data->format('n') - 1]) . ' de ' . $data->format('Y')][] = $evento;
}

require __DIR__ . '/../view/calendario.php';
