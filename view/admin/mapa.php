<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>

<div class="admin-page-header">
    <div>
        <h2>Salas (ficha e QR)</h2>
        <p>Nome, função, setor e acessibilidade de cada sala: aparecem na ficha da sala, no QR da porta, no mapa em texto e no quadro de horários. O <strong>desenho</strong> do mapa (onde fica cada sala, prédios e calçadas) é feito no <a href="editor-mapa.php">Editor do mapa</a>.</p>
    </div>
    <div class="admin-row-actions">
        <a href="../impressao.php?todas=1" target="_blank" rel="noopener noreferrer" class="button button-outline"><?= icone('printer') ?><span>Imprimir todas as placas</span></a>
        <a href="../mapa.php" target="_blank" rel="noopener noreferrer" class="button button-outline"><?= icone('external-link') ?><span>Ver o mapa do site</span></a>
    </div>
</div>

<section class="admin-panel">
    <div class="admin-tab-painel-header">
        <h2 class="admin-panel-title"><?= icone('door-open') ?> Salas do prédio</h2>
        <button type="button" class="button button-primary button-small" data-abrir-modal="modal-sala" data-titulo="Nova sala"><?= icone('plus') ?><span>Nova sala</span></button>
    </div>
    <p class="admin-campo-ajuda">
        Arraste as salas para mudar a ordem ou levar para outra ala (no celular e no teclado, use as setas ↑ ↓).
        A ordem é de quem entra no prédio: a 1ª fica perto da escada. "Ponta" é a sala grande no fim da ala (como o Auditório).
        Depois de arrumar, clique em <strong>Salvar ordem</strong>.
    </p>

    <div class="editor-salas">
        <?php foreach ($andaresMapa as $idAndar => $andar): ?>
            <?php foreach (['esquerda', 'direita'] as $ala): ?>
                <?php $grupo = $idAndar . '|' . $ala; ?>
                <div class="editor-grupo">
                    <h3><?= htmlspecialchars($andar['nome'], ENT_QUOTES, 'UTF-8') ?> · ala <?= $ala ?></h3>
                    <ol class="editor-lista" data-grupo-salas="<?= htmlspecialchars($grupo, ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars($andar['nome'] . ', ala ' . $ala, ENT_QUOTES, 'UTF-8') ?>">
                        <?php foreach ($salasPorGrupo[$grupo] ?? [] as $sala): ?>
                            <?php
                            $valoresSala = [
                                'id' => $sala['id'], 'nome' => $sala['nome'], 'nome_curto' => $sala['nomeCurto'], 'funcao' => (string) $sala['funcao'],
                                'andar_id' => $sala['andar'], 'ala' => $sala['ala'], 'lado' => $sala['lado'] ?: 'frente',
                                'ponta' => $sala['ponta'], 'vazia' => $sala['vazia'], 'setor_id' => (string) $sala['setor_id_cadastrado'],
                            ];
                            foreach ($acessibilidadePorSala[$sala['id']] as $itemAcess => $valorAcess) {
                                $valoresSala['acess_' . $itemAcess] = $valorAcess === null ? '' : (string) $valorAcess;
                            }
                            ?>
                            <li class="editor-sala <?= $sala['ponta'] ? 'ponta' : '' ?> <?= $sala['vazia'] ? 'vazia' : '' ?>" draggable="true" data-id="<?= htmlspecialchars($sala['id'], ENT_QUOTES, 'UTF-8') ?>">
                                <span class="editor-alca" aria-hidden="true"><?= icone('grip-vertical') ?></span>
                                <span class="editor-sala-nome">
                                    <strong><?= htmlspecialchars($sala['nome'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <small>
                                        <?= $sala['ponta'] ? 'Ponta' : ($sala['lado'] === 'fundos' ? 'Fundos' : 'Frente') ?>
                                        <?= $sala['vazia'] ? ' · espaço vazio' : '' ?>
                                        <?= $sala['setor_id_cadastrado'] ? ' · ' . htmlspecialchars($nomesSetoresMapa[$sala['setor_id_cadastrado']] ?? $sala['setor_id_cadastrado'], ENT_QUOTES, 'UTF-8') : '' ?>
                                    </small>
                                </span>
                                <span class="editor-sala-acoes">
                                    <a class="admin-icon-button" href="../impressao.php?sala=<?= rawurlencode($sala['id']) ?>" target="_blank" rel="noopener noreferrer" aria-label="Placa com QR de <?= htmlspecialchars($sala['nome'], ENT_QUOTES, 'UTF-8') ?>" title="Placa com QR para a porta"><?= icone('qr-code') ?></a>
                                    <button type="button" class="admin-icon-button" data-mover-sala="cima" aria-label="Subir <?= htmlspecialchars($sala['nome'], ENT_QUOTES, 'UTF-8') ?>"><?= icone('arrow-up') ?></button>
                                    <button type="button" class="admin-icon-button" data-mover-sala="baixo" aria-label="Descer <?= htmlspecialchars($sala['nome'], ENT_QUOTES, 'UTF-8') ?>"><?= icone('arrow-down') ?></button>
                                    <button type="button" class="admin-icon-button" aria-label="Editar <?= htmlspecialchars($sala['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-abrir-modal="modal-sala" data-titulo="Editar sala" data-valores="<?= dadosParaModal($valoresSala) ?>"><?= icone('pencil') ?></button>
                                    <form method="post" class="admin-form-inline">
                                        <?= campoCsrf() ?>
                                        <input type="hidden" name="acao" value="excluir_sala">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($sala['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="admin-icon-button admin-icon-button-danger" aria-label="Excluir <?= htmlspecialchars($sala['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-confirmar-exclusao="Tirar &quot;<?= htmlspecialchars($sala['nome'], ENT_QUOTES, 'UTF-8') ?>&quot; do mapa? O setor ligado a ela continua existindo."><?= icone('trash-2') ?></button>
                                    </form>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <div class="admin-form-footer-inline">
        <span class="admin-campo-ajuda" id="editor-ordem-status" aria-live="polite"></span>
        <button type="button" class="button button-primary" id="editor-salvar-ordem" disabled><?= icone('save') ?><span>Salvar ordem</span></button>
    </div>
</section>

<?php // O cenário em volta do prédio (tabela pecas_mapa) era do mapa antigo; agora tudo é desenhado no Editor do mapa. ?>

<!-- Formulário da sala -->
<div class="admin-modal" id="modal-sala" aria-hidden="true">
    <div class="admin-modal-backdrop" data-fechar-modal></div>
    <div class="admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-sala-titulo">
        <div class="admin-modal-header">
            <h2 id="modal-sala-titulo" data-titulo-modal>Nova sala</h2>
            <button type="button" class="admin-icon-button" data-fechar-modal aria-label="Fechar formulário"><?= icone('x') ?></button>
        </div>
        <form class="admin-form" method="post" novalidate>
            <?= campoCsrf() ?>
            <input type="hidden" name="acao" value="salvar_sala">
            <input type="hidden" name="id" value="" data-limpar>
            <div class="admin-field">
                <label for="sala-nome">Nome</label>
                <input type="text" id="sala-nome" name="nome" required maxlength="150">
                <span class="admin-field-error">Informe o nome.</span>
            </div>
            <div class="admin-field">
                <label for="sala-curto">Nome curto (o que cabe dentro da sala no desenho)</label>
                <input type="text" id="sala-curto" name="nome_curto" maxlength="60">
            </div>
            <div class="admin-field-grid admin-field-grid-3">
                <div class="admin-field">
                    <label for="sala-andar">Andar</label>
                    <select id="sala-andar" name="andar_id">
                        <?php foreach ($andaresMapa as $idAndar => $andar): ?>
                            <option value="<?= htmlspecialchars($idAndar, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($andar['nome'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-field">
                    <label for="sala-ala">Ala (de quem entra)</label>
                    <select id="sala-ala" name="ala"><option value="esquerda">Esquerda</option><option value="direita">Direita</option></select>
                </div>
                <div class="admin-field">
                    <label for="sala-lado">Lado do corredor</label>
                    <select id="sala-lado" name="lado"><option value="frente">Frente (lado da entrada)</option><option value="fundos">Fundos</option></select>
                </div>
            </div>
            <label class="admin-checkbox"><input type="checkbox" name="ponta" value="1"><span>Sala grande na ponta da ala (como o Auditório e a Biblioteca)</span></label>
            <label class="admin-checkbox"><input type="checkbox" name="vazia" value="1"><span>Espaço vazio (só ocupa lugar no desenho)</span></label>
            <div class="admin-field">
                <label for="sala-setor">Setor que funciona aqui</label>
                <select id="sala-setor" name="setor_id">
                    <option value="">Nenhum (ficha simples de sala)</option>
                    <?php foreach ($setoresParaSala as $setor): ?>
                        <option value="<?= htmlspecialchars($setor['id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(($setor['sigla'] ? $setor['sigla'] . ' – ' : '') . $setor['nome'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-field">
                <label for="sala-funcao">Função (para sala sem setor)</label>
                <input type="text" id="sala-funcao" name="funcao" maxlength="255" placeholder="Ex.: Copa dos servidores, com micro-ondas e geladeira">
            </div>
            <h3 class="admin-form-subtitulo">Acessibilidade do local</h3>
            <p class="admin-campo-ajuda">Só marque "Sim" ou "Não" depois de conferir no prédio. Na dúvida, deixe "Não informado".</p>
            <div class="admin-field-grid">
                <?php foreach ($itensAcessibilidade as $itemAcess => $rotuloAcess): ?>
                    <div class="admin-field">
                        <label for="acess-<?= $itemAcess ?>"><?= htmlspecialchars($rotuloAcess, ENT_QUOTES, 'UTF-8') ?></label>
                        <select id="acess-<?= $itemAcess ?>" name="acess_<?= $itemAcess ?>">
                            <option value="">Não informado</option>
                            <option value="1">Sim</option>
                            <option value="0">Não</option>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="admin-field">
                <label for="acess-observacao">Observação de acessibilidade</label>
                <input type="text" id="acess-observacao" name="acess_observacao" maxlength="255" placeholder="Ex.: rampa pela lateral do prédio">
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="button button-outline" data-fechar-modal>Cancelar</button>
                <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Salvar sala</span></button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
