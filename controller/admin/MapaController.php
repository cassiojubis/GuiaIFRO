<?php
/**
 * EDITOR DO MAPA (admin/mapa.php)
 *  - salas: criar, editar, excluir e arrumar a ordem (arrastar);
 *  - cenário: peças em volta do prédio (bloco, obra, quadra, estacionamento...).
 * O mapa público (2D e 3D) lê as mesmas tabelas, então salvou aqui = aparece lá.
 */
require_once __DIR__ . '/BaseAdmin.php';
exigirAdminGeral();

$tituloPaginaAdmin = 'Salas (ficha e QR)';
$paginaAtualAdmin = 'mapa';
$andaresMapa = listarMapaSalas()['andares'];
$tiposPeca = tiposPecaMapa();

/** Número dentro de um intervalo (o editor manda texto). */
function numeroEntre($valor, float $minimo, float $maximo): ?float
{
    if (!is_numeric($valor)) {
        return null;
    }
    $numero = round((float) $valor, 2);
    return ($numero < $minimo || $numero > $maximo) ? null : $numero;
}

if (ehPost()) {
    exigirCsrf();
    $acao = acaoPedida(['salvar_sala', 'excluir_sala', 'salvar_ordem', 'salvar_pecas']);

    // ---- formulário da sala (POST normal) ----
    if ($acao === 'salvar_sala') {
        $id = (string) ($_POST['id'] ?? '');
        $antes = $id !== '' ? buscarSalaBruta($id) : null;
        if ($id !== '' && !$antes) {
            voltarComAviso('mapa.php', 'Sala não encontrada.', 'erro');
        }
        $erros = [];
        $dados = [
            'nome' => lerCampo('nome', 'o nome', 150, true, $erros),
            'nome_curto' => lerCampo('nome_curto', 'o nome curto', 60, false, $erros),
            'funcao' => lerCampo('funcao', 'a função', 255, false, $erros),
            'andar_id' => (string) ($_POST['andar_id'] ?? ''),
            'ala' => ($_POST['ala'] ?? '') === 'esquerda' ? 'esquerda' : 'direita',
            'lado' => ($_POST['lado'] ?? '') === 'fundos' ? 'fundos' : 'frente',
            'ponta' => lerCheckbox('ponta'),
            'vazia' => lerCheckbox('vazia'),
            'setor_id' => (string) ($_POST['setor_id'] ?? ''),
        ];
        if ($dados['nome_curto'] === '') {
            $dados['nome_curto'] = mb_substr($dados['nome'], 0, 60);
        }
        if (!isset($andaresMapa[$dados['andar_id']])) {
            $erros['andar_id'] = 'Escolha um andar.';
        }
        if ($dados['setor_id'] !== '' && !setorExiste($dados['setor_id'])) {
            $erros['setor_id'] = 'Escolha um setor da lista.';
        }
        if ($erros) {
            voltarComAviso('mapa.php', 'Confira os campos marcados.', 'erro', [
                'modal' => 'modal-sala', 'valores' => $dados + ['id' => $id], 'erros' => $erros,
            ]);
        }
        $idSalvo = salvarSala($antes ? $id : null, $dados);
        // Acessibilidade física (I3): '' = não informado, '1' = tem, '0' = não tem
        $acessibilidade = [];
        foreach (array_keys(itensAcessibilidadeSala()) as $item) {
            $valor = (string) ($_POST['acess_' . $item] ?? '');
            $acessibilidade[$item] = $valor === '1' ? 1 : ($valor === '0' ? 0 : null);
        }
        $errosAcess = [];
        $acessibilidade['observacao'] = lerCampo('acess_observacao', 'a observação', 255, false, $errosAcess);
        salvarAcessibilidadeSala($idSalvo, $acessibilidade);
        registrarAuditoria($usuarioAdmin, $antes ? 'editar' : 'criar', 'sala', $idSalvo, $antes, $dados, 'Sala "' . $dados['nome'] . '" ' . ($antes ? 'editada' : 'criada') . ' no mapa');
        voltarComAviso('mapa.php', $antes ? 'Sala atualizada.' : 'Sala criada no fim da ala. Arraste para o lugar certo.');
    }

    if ($acao === 'excluir_sala') {
        $id = (string) ($_POST['id'] ?? '');
        $antes = buscarSalaBruta($id);
        if (!$antes) {
            voltarComAviso('mapa.php', 'Sala não encontrada.', 'erro');
        }
        excluirSala($id);
        registrarAuditoria($usuarioAdmin, 'excluir', 'sala', $id, $antes, null, 'Sala "' . $antes['nome'] . '" excluída do mapa');
        voltarComAviso('mapa.php', 'Sala excluída do mapa.');
    }

    // ---- arrastar e cenário: chegam por fetch (JSON) ----
    $json = json_decode((string) ($_POST['dados'] ?? ''), true);
    if (!is_array($json)) {
        responderJson(['ok' => false, 'erro' => 'Dados inválidos.'], 422);
    }

    if ($acao === 'salvar_ordem') {
        $posicoes = [];
        foreach ($json as $item) {
            $idSala = (string) ($item['id'] ?? '');
            if (!buscarSalaBruta($idSala) || !isset($andaresMapa[$item['andar_id'] ?? ''])
                || !in_array($item['ala'] ?? '', ['esquerda', 'direita'], true)) {
                responderJson(['ok' => false, 'erro' => 'Sala, andar ou ala inválidos.'], 422);
            }
            $posicoes[] = ['id' => $idSala, 'andar_id' => $item['andar_id'], 'ala' => $item['ala'], 'ordem' => max(1, (int) ($item['ordem'] ?? 1))];
        }
        reordenarSalas($posicoes);
        registrarAuditoria($usuarioAdmin, 'editar', 'sala', null, null, ['posicoes' => $posicoes], 'Ordem das salas do mapa alterada');
        responderJson(['ok' => true, 'mensagem' => 'Ordem das salas salva.']);
    }

    if ($acao === 'salvar_pecas') {
        if (count($json) > 300) {
            responderJson(['ok' => false, 'erro' => 'Peças demais (máximo 300).'], 422);
        }
        $pecas = [];
        foreach ($json as $indice => $item) {
            $tipo = (string) ($item['tipo'] ?? '');
            $nome = mb_substr(trim((string) ($item['nome'] ?? '')), 0, 150);
            $peca = [
                'tipo' => $tipo,
                'nome' => $nome,
                'x' => numeroEntre($item['x'] ?? null, -80, 120),
                'z' => numeroEntre($item['z'] ?? null, -100, 100),
                'largura' => numeroEntre($item['largura'] ?? null, 0.5, 120),
                'profundidade' => numeroEntre($item['profundidade'] ?? null, 0.5, 120),
                'rotacao' => numeroEntre($item['rotacao'] ?? 0, -360, 360),
                'inicio' => null,
                'fim' => null,
            ];
            foreach (['inicio', 'fim'] as $campoData) {
                $valor = trim((string) ($item[$campoData] ?? ''));
                if ($valor !== '') {
                    $data = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $valor) ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $valor);
                    if (!$data) {
                        responderJson(['ok' => false, 'erro' => 'Peça ' . ($indice + 1) . ': data inválida.'], 422);
                    }
                    $peca[$campoData] = $data->format('Y-m-d H:i:00');
                }
            }
            if (!isset($tiposPeca[$tipo]) || !mb_check_encoding($nome, 'UTF-8') || in_array(null, [$peca['x'], $peca['z'], $peca['largura'], $peca['profundidade'], $peca['rotacao']], true)) {
                responderJson(['ok' => false, 'erro' => 'Peça ' . ($indice + 1) . ' com tipo, posição ou tamanho inválido.'], 422);
            }
            if ($peca['inicio'] && $peca['fim'] && $peca['fim'] < $peca['inicio']) {
                responderJson(['ok' => false, 'erro' => 'Peça ' . ($indice + 1) . ': o fim precisa ser depois do início.'], 422);
            }
            $pecas[] = $peca;
        }
        $antes = listarPecasMapa(false);
        salvarPecasMapa($pecas);
        registrarAuditoria($usuarioAdmin, 'editar', 'pecas_mapa', null, ['pecas' => count($antes)], ['pecas' => count($pecas)], 'Cenário do mapa atualizado (' . count($pecas) . ' peças)');
        responderJson(['ok' => true, 'mensagem' => 'Cenário salvo. O mapa do site já mostra as mudanças.']);
    }

    voltarComAviso('mapa.php', 'Ação desconhecida.', 'erro');
}

$mapaAdmin = listarMapaSalas();
$acessibilidadePorSala = [];
foreach ($mapaAdmin['salas'] as $salaMapa) {
    $acessibilidadePorSala[$salaMapa['id']] = buscarAcessibilidadeSala($salaMapa['id']);
}
$itensAcessibilidade = itensAcessibilidadeSala();
$salasPorGrupo = [];
foreach ($mapaAdmin['salas'] as $sala) {
    $salasPorGrupo[$sala['andar'] . '|' . $sala['ala']][] = $sala;
}
$setoresParaSala = listarSetores(true);
$nomesSetoresMapa = array_column($setoresParaSala, 'nome', 'id');
$pecasMapa = listarPecasMapa(false);
$scriptsExtrasAdmin = ['assets/js/editor-mapa.js'];

require __DIR__ . '/../../view/admin/mapa.php';
