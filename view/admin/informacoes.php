<?php require __DIR__ . '/parciais/admin-cabecalho.php'; ?>
<?php
// Valores de cada item no formato que o formulário da aba espera (pro botão "editar").
$valoresParaEditar = static function (string $aba, array $item): array {
    switch ($aba) {
        case 'avisos':
            return ['id' => $item['id'], 'titulo' => $item['titulo'], 'mensagem' => $item['mensagem'], 'nivel' => $item['nivel'],
                'inicio' => paraDataHoraLocal($item['inicio']), 'fim' => paraDataHoraLocal($item['fim'])];
        case 'noticias':
            return ['id' => $item['id'], 'titulo' => $item['titulo'], 'resumo' => (string) $item['resumo'], 'conteudo' => (string) $item['conteudo'],
                'categoria' => (string) $item['categoria'], 'publicada_em' => $item['publicada_em'], 'destaque' => (bool) $item['destaque']];
        case 'faq':
            return ['id' => $item['id'], 'pergunta' => $item['pergunta'], 'resposta' => $item['resposta'], 'setores' => $item['setores']];
        case 'links':
            return ['id' => $item['id'], 'titulo' => $item['titulo'], 'url' => $item['url']];
        default:
            return ['id' => $item['id'], 'titulo' => $item['titulo'], 'conteudo' => $item['conteudo']];
    }
};
$descricoesAbas = [
    'avisos' => 'Avisos aparecem no site durante o período escolhido. Os urgentes viram uma faixa no topo de todas as páginas.',
    'noticias' => 'Notícias aparecem na página inicial e têm página própria.',
    'faq' => 'Perguntas frequentes. Ligue a pergunta a um ou mais setores para ela aparecer na página deles; sem setor, é FAQ geral.',
    'links' => 'Links úteis (SUAP, portal do IFRO...).',
    'institucional' => 'Textos institucionais (missão, visão, valores).',
];
?>

<div class="admin-page-header">
    <div>
        <h2>Informações do site</h2>
        <p>Conteúdos gerais exibidos para os visitantes do Guia IFRO.</p>
    </div>
</div>

<div class="admin-tabs" role="tablist" aria-label="Tipos de informação">
    <?php foreach ($abasInformacoes as $indice => $aba): ?>
        <button type="button" class="admin-tab-button <?= $indice === 0 ? 'active' : '' ?>" role="tab"
            id="aba-<?= $aba['id'] ?>" aria-controls="painel-<?= $aba['id'] ?>"
            aria-selected="<?= $indice === 0 ? 'true' : 'false' ?>" data-aba-alvo="<?= $aba['id'] ?>">
            <?= icone($aba['icone']) ?>
            <span><?= htmlspecialchars($aba['texto'], ENT_QUOTES, 'UTF-8') ?></span>
            <small>(<?= count($itensPorAba[$aba['id']]) ?>)</small>
        </button>
    <?php endforeach; ?>
</div>

<?php foreach ($abasInformacoes as $indice => $aba): ?>
    <?php $idAba = $aba['id']; $itens = $itensPorAba[$idAba]; $campoTitulo = abasInformacao()[$idAba]['titulo']; ?>
    <section class="admin-tab-painel" id="painel-<?= $idAba ?>" role="tabpanel" aria-labelledby="aba-<?= $idAba ?>" data-aba-painel="<?= $idAba ?>" <?= $indice === 0 ? '' : 'hidden' ?>>
        <div class="admin-tab-painel-header">
            <p><?= htmlspecialchars($descricoesAbas[$idAba], ENT_QUOTES, 'UTF-8') ?></p>
            <button type="button" class="button button-primary button-small" data-abrir-modal="modal-<?= $idAba ?>" data-titulo="Adicionar <?= htmlspecialchars($aba['rotulo'], ENT_QUOTES, 'UTF-8') ?>">
                <?= icone('plus') ?><span>Adicionar <?= htmlspecialchars($aba['rotulo'], ENT_QUOTES, 'UTF-8') ?></span>
            </button>
        </div>

        <?php if (!$itens): ?>
            <p class="admin-empty-state"><?= icone('inbox') ?> Nada cadastrado ainda.</p>
        <?php endif; ?>

        <ul class="admin-lista-informacoes">
            <?php foreach ($itens as $posicao => $item): ?>
                <?php $publicado = (int) $item['publicado'] === 1; $tituloItem = $item[$campoTitulo]; ?>
                <li class="admin-item-informacao">
                    <div class="admin-item-informacao-corpo">
                        <strong><?= htmlspecialchars($tituloItem, ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php if (($item['nivel'] ?? '') === 'urgente' || !empty($item['destaque'])): ?>
                            <span class="admin-badge-destaque"><?= $idAba === 'avisos' ? 'Urgente' : 'Destaque' ?></span>
                        <?php endif; ?>
                        <p><?= htmlspecialchars(resumoItemInformacao($idAba, $item), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="admin-item-informacao-acoes">
                        <?php foreach (['cima' => 'chevron-up', 'baixo' => 'chevron-down'] as $direcao => $iconeDirecao): ?>
                            <form method="post" class="admin-form-inline">
                                <?= campoCsrf() ?>
                                <input type="hidden" name="aba" value="<?= $idAba ?>">
                                <input type="hidden" name="acao" value="mover">
                                <input type="hidden" name="direcao" value="<?= $direcao ?>">
                                <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                <button type="submit" class="admin-icon-button" aria-label="Mover para <?= $direcao ?>"
                                    <?= ($direcao === 'cima' && $posicao === 0) || ($direcao === 'baixo' && $posicao === count($itens) - 1) ? 'disabled' : '' ?>><?= icone($iconeDirecao) ?></button>
                            </form>
                        <?php endforeach; ?>
                        <form method="post" class="admin-form-inline">
                            <?= campoCsrf() ?>
                            <input type="hidden" name="aba" value="<?= $idAba ?>">
                            <input type="hidden" name="acao" value="alternar">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <button type="submit" class="admin-status-badge admin-status-<?= $publicado ? 'ativo' : 'inativo' ?>" aria-label="<?= $publicado ? 'Publicado. Clique para ocultar' : 'Oculto. Clique para publicar' ?>">
                                <?= icone($publicado ? 'eye' : 'eye-off') ?><span><?= $publicado ? 'Publicado' : 'Oculto' ?></span>
                            </button>
                        </form>
                        <button type="button" class="admin-icon-button" aria-label="Editar" data-abrir-modal="modal-<?= $idAba ?>" data-titulo="Editar <?= htmlspecialchars($aba['rotulo'], ENT_QUOTES, 'UTF-8') ?>"
                            data-valores="<?= dadosParaModal($valoresParaEditar($idAba, $item)) ?>"><?= icone('pencil') ?></button>
                        <form method="post" class="admin-form-inline">
                            <?= campoCsrf() ?>
                            <input type="hidden" name="aba" value="<?= $idAba ?>">
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <button type="submit" class="admin-icon-button admin-icon-button-danger" aria-label="Excluir"
                                data-confirmar-exclusao="Excluir &quot;<?= htmlspecialchars(mb_substr($tituloItem, 0, 80), ENT_QUOTES, 'UTF-8') ?>&quot;? Essa ação não pode ser desfeita."><?= icone('trash-2') ?></button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endforeach; ?>

<?php
/** Abre um modal de formulário (o conteúdo de cada um vem logo abaixo). */
$abrirModal = static function (string $aba, string $titulo): void { ?>
    <div class="admin-modal" id="modal-<?= $aba ?>" aria-hidden="true">
        <div class="admin-modal-backdrop" data-fechar-modal></div>
        <div class="admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-<?= $aba ?>-titulo">
            <div class="admin-modal-header">
                <h2 id="modal-<?= $aba ?>-titulo" data-titulo-modal><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></h2>
                <button type="button" class="admin-icon-button" data-fechar-modal aria-label="Fechar formulário"><?= icone('x') ?></button>
            </div>
            <form class="admin-form" method="post" action="informacoes.php#<?= $aba ?>" novalidate>
                <?= campoCsrf() ?>
                <input type="hidden" name="aba" value="<?= $aba ?>">
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="id" value="" data-limpar>
<?php };
$fecharModal = static function (): void { ?>
                <div class="admin-modal-footer">
                    <button type="button" class="button button-outline" data-fechar-modal>Cancelar</button>
                    <button type="submit" class="button button-primary admin-botao-salvar"><span class="admin-botao-texto">Salvar</span></button>
                </div>
            </form>
        </div>
    </div>
<?php };
?>

<?php $abrirModal('avisos', 'Adicionar aviso'); ?>
    <div class="admin-field">
        <label for="aviso-titulo">Título</label>
        <input type="text" id="aviso-titulo" name="titulo" required maxlength="200">
        <span class="admin-field-error">Informe o título.</span>
    </div>
    <div class="admin-field">
        <label for="aviso-mensagem">Mensagem</label>
        <textarea id="aviso-mensagem" name="mensagem" rows="3" required maxlength="2000"></textarea>
        <span class="admin-field-error">Informe a mensagem.</span>
    </div>
    <div class="admin-field">
        <label for="aviso-nivel">Nível</label>
        <select id="aviso-nivel" name="nivel">
            <option value="normal">Normal</option>
            <option value="urgente">Urgente (faixa no topo do site)</option>
        </select>
    </div>
    <div class="admin-field-grid">
        <div class="admin-field">
            <label for="aviso-inicio">Mostrar a partir de (opcional)</label>
            <input type="datetime-local" id="aviso-inicio" name="inicio">
        </div>
        <div class="admin-field">
            <label for="aviso-fim">Tirar do ar em (opcional)</label>
            <input type="datetime-local" id="aviso-fim" name="fim">
            <span class="admin-field-error">Data inválida.</span>
        </div>
    </div>
<?php $fecharModal(); ?>

<?php $abrirModal('noticias', 'Adicionar notícia'); ?>
    <div class="admin-field">
        <label for="noticia-titulo">Título</label>
        <input type="text" id="noticia-titulo" name="titulo" required maxlength="200">
        <span class="admin-field-error">Informe o título.</span>
    </div>
    <div class="admin-field-grid">
        <div class="admin-field">
            <label for="noticia-data">Data</label>
            <input type="date" id="noticia-data" name="publicada_em" required value="<?= date('Y-m-d') ?>">
            <span class="admin-field-error">Informe a data.</span>
        </div>
        <div class="admin-field">
            <label for="noticia-categoria">Categoria</label>
            <input type="text" id="noticia-categoria" name="categoria" maxlength="60" placeholder="Ex.: Assistência">
        </div>
    </div>
    <div class="admin-field">
        <label for="noticia-resumo">Resumo (aparece na lista)</label>
        <textarea id="noticia-resumo" name="resumo" rows="2" maxlength="400"></textarea>
    </div>
    <div class="admin-field">
        <label for="noticia-conteudo">Texto completo</label>
        <textarea id="noticia-conteudo" name="conteudo" rows="6" maxlength="10000"></textarea>
        <p class="admin-campo-ajuda">Texto simples. Linha em branco separa parágrafos.</p>
    </div>
    <label class="admin-checkbox"><input type="checkbox" name="destaque" value="1"><span>Destaque (marcado como importante)</span></label>
<?php $fecharModal(); ?>

<?php $abrirModal('faq', 'Adicionar pergunta'); ?>
    <div class="admin-field">
        <label for="faq-pergunta">Pergunta</label>
        <input type="text" id="faq-pergunta" name="pergunta" required maxlength="300">
        <span class="admin-field-error">Informe a pergunta.</span>
    </div>
    <div class="admin-field">
        <label for="faq-resposta">Resposta</label>
        <textarea id="faq-resposta" name="resposta" rows="4" required maxlength="4000"></textarea>
        <span class="admin-field-error">Informe a resposta.</span>
    </div>
    <div class="admin-field">
        <span class="admin-field-legenda">Aparece na página destes setores (nenhum = FAQ geral)</span>
        <div class="admin-checkbox-grade">
            <?php foreach ($setoresParaFaq as $setor): ?>
                <label><input type="checkbox" name="setores[]" value="<?= htmlspecialchars($setor['id'], ENT_QUOTES, 'UTF-8') ?>"> <?= htmlspecialchars($setor['sigla'] ?: $setor['nome'], ENT_QUOTES, 'UTF-8') ?></label>
            <?php endforeach; ?>
        </div>
    </div>
<?php $fecharModal(); ?>

<?php $abrirModal('links', 'Adicionar link'); ?>
    <div class="admin-field">
        <label for="link-titulo">Título</label>
        <input type="text" id="link-titulo" name="titulo" required maxlength="150">
        <span class="admin-field-error">Informe o título.</span>
    </div>
    <div class="admin-field">
        <label for="link-url">Endereço</label>
        <input type="url" id="link-url" name="url" required maxlength="255" placeholder="https://">
        <span class="admin-field-error">Informe o endereço.</span>
    </div>
<?php $fecharModal(); ?>

<?php $abrirModal('institucional', 'Adicionar texto'); ?>
    <div class="admin-field">
        <label for="inst-titulo">Título</label>
        <input type="text" id="inst-titulo" name="titulo" required maxlength="150">
        <span class="admin-field-error">Informe o título.</span>
    </div>
    <div class="admin-field">
        <label for="inst-conteudo">Texto</label>
        <textarea id="inst-conteudo" name="conteudo" rows="5" required maxlength="5000"></textarea>
        <span class="admin-field-error">Informe o texto.</span>
    </div>
<?php $fecharModal(); ?>

<?php require __DIR__ . '/parciais/admin-rodape.php'; ?>
