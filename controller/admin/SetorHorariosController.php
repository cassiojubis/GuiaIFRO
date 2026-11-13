<?php
/**
 * HORÁRIOS DE UM SETOR (admin/setor-horarios.php?id=cra)
 * Faixas por dia da semana + exceções (feriado, recesso, horário especial).
 * Admin geral também cadastra exceções do campus todo.
 */
require_once __DIR__ . '/BaseAdmin.php';

$idSetor = preg_replace('/[^a-z0-9_-]/i', '', (string) ($_GET['id'] ?? $_POST['setor_id'] ?? ''));
$setor = $idSetor !== '' ? buscarSetorPorId($idSetor, true) : null;
if (!$setor) {
    voltarComAviso('setores.php', 'Setor não encontrado.', 'erro');
}
if (!podeEditarSetor($idSetor)) {
    voltarComAviso('setores.php', 'Você não tem permissão para editar este setor.', 'erro');
}

$tituloPaginaAdmin = 'Horários — ' . ($setor['sigla'] ?: $setor['nome']);
$paginaAtualAdmin = 'setores';
$voltar = 'setor-horarios.php?id=' . rawurlencode($idSetor);

/** 'HH:MM' válido -> 'HH:MM:00', senão null. */
function horaValida(string $valor): ?string
{
    return preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $valor) ? $valor . ':00' : null;
}

if (ehPost()) {
    exigirCsrf();
    $acao = acaoPedida(['salvar_semana', 'nova_excecao', 'excluir_excecao']);

    if ($acao === 'salvar_semana') {
        $faixas = [];
        $erros = [];
        $abres = $_POST['abre'] ?? [];
        $fechas = $_POST['fecha'] ?? [];
        foreach (range(0, 6) as $dia) {
            $listaAbre = is_array($abres[$dia] ?? null) ? $abres[$dia] : [];
            $listaFecha = is_array($fechas[$dia] ?? null) ? $fechas[$dia] : [];
            foreach ($listaAbre as $indice => $abre) {
                $abre = trim((string) $abre);
                $fecha = trim((string) ($listaFecha[$indice] ?? ''));
                if ($abre === '' && $fecha === '') {
                    continue; // linha vazia: ignora
                }
                $horaAbre = horaValida($abre);
                $horaFecha = horaValida($fecha);
                if (!$horaAbre || !$horaFecha) {
                    $erros[] = ucfirst(NOMES_DIAS[$dia]) . ': preencha as duas horas (ex.: 08:00 e 12:00).';
                } elseif ($horaFecha <= $horaAbre) {
                    $erros[] = ucfirst(NOMES_DIAS[$dia]) . ': a hora de fechar precisa ser depois da de abrir.';
                } else {
                    $faixas[] = ['dia_semana' => $dia, 'abre' => $horaAbre, 'fecha' => $horaFecha];
                }
            }
        }
        // Faixas do mesmo dia não podem se sobrepor (o banco também recusa: trigger no fim do sql/guiaifro.sql).
        foreach (range(0, 6) as $dia) {
            $doDia = array_values(array_filter($faixas, static function ($faixa) use ($dia) { return $faixa['dia_semana'] === $dia; }));
            usort($doDia, static function ($a, $b) { return strcmp($a['abre'], $b['abre']); });
            for ($i = 1; $i < count($doDia); $i++) {
                if ($doDia[$i]['abre'] < $doDia[$i - 1]['fecha']) {
                    $erros[] = ucfirst(NOMES_DIAS[$dia]) . ': há duas faixas de horário que se sobrepõem.';
                    break;
                }
            }
        }
        if (count($faixas) > 50) {
            $erros[] = 'Faixas demais. Junte os horários seguidos numa faixa só.';
        }
        if ($erros) {
            voltarComAviso($voltar, implode(' ', array_unique($erros)), 'erro');
        }
        $antes = listarHorariosSetor($idSetor);
        salvarHorariosSetor($idSetor, $faixas);
        registrarAuditoria($usuarioAdmin, 'editar', 'setor_horarios', $idSetor, ['faixas' => $antes], ['faixas' => $faixas],
            'Horário de "' . $setor['nome'] . '" atualizado: ' . (resumoHorarioSemana($faixas) ?: 'sem horário'));
        voltarComAviso($voltar, $faixas ? 'Horário salvo: ' . resumoHorarioSemana($faixas) : 'Horário apagado.');
    }

    if ($acao === 'nova_excecao') {
        $erros = [];
        $data = lerData('data', 'a data', true, $erros);
        $motivo = lerCampo('motivo', 'o motivo', 150, true, $erros);
        $fechado = ($_POST['tipo'] ?? 'fechado') === 'fechado';
        $abre = $fechado ? null : horaValida(trim((string) ($_POST['abre'] ?? '')));
        $fecha = $fechado ? null : horaValida(trim((string) ($_POST['fecha'] ?? '')));
        $doCampus = ehAdminGeral() && lerCheckbox('campus_todo');
        if (!$fechado && (!$abre || !$fecha || $fecha <= $abre)) {
            $erros['abre'] = 'No horário especial, informe a hora de abrir e a de fechar (fechar depois de abrir).';
        }
        if ($data !== '' && $data < date('Y-m-d')) {
            $erros['data'] = 'A data já passou.';
        }
        if ($erros) {
            voltarComAviso($voltar, implode(' ', $erros), 'erro');
        }
        $idExcecao = criarExcecao($doCampus ? null : $idSetor, $data, $fechado, $abre, $fecha, $motivo);
        registrarAuditoria($usuarioAdmin, 'criar', 'setor_excecao', (string) $idExcecao, null, compact('data', 'fechado', 'abre', 'fecha', 'motivo'),
            ($doCampus ? 'Campus todo' : $setor['nome']) . ': exceção em ' . dataHoraBr($data, false) . " ({$motivo})");
        voltarComAviso($voltar, 'Exceção cadastrada.');
    }

    if ($acao === 'excluir_excecao') {
        $excecao = buscarExcecao((int) ($_POST['id'] ?? 0));
        // Exceção do campus só o admin geral apaga; a do setor, quem pode editar o setor.
        if (!$excecao || ($excecao['setor_id'] === null && !ehAdminGeral()) || ($excecao['setor_id'] !== null && $excecao['setor_id'] !== $idSetor)) {
            voltarComAviso($voltar, 'Exceção não encontrada.', 'erro');
        }
        excluirExcecao((int) $excecao['id']);
        registrarAuditoria($usuarioAdmin, 'excluir', 'setor_excecao', (string) $excecao['id'], $excecao, null, 'Exceção de ' . dataHoraBr($excecao['data'], false) . ' excluída');
        voltarComAviso($voltar, 'Exceção excluída.');
    }

    voltarComAviso($voltar, 'Ação desconhecida.', 'erro');
}

$faixasSetor = listarHorariosSetor($idSetor);
$faixasPorDia = [];
foreach ($faixasSetor as $faixa) {
    $faixasPorDia[(int) $faixa['dia_semana']][] = $faixa;
}
$excecoesSetor = listarExcecoes($idSetor, true);
$estadoAgora = estadoHorarioSetor($setor);

require __DIR__ . '/../../view/admin/setor-horarios.php';
