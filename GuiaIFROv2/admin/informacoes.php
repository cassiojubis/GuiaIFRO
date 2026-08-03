<?php
/**
 * GERENCIAMENTO DE INFORMAÇÕES GERAIS
 *
 * INTEGRAÇÃO FUTURA:
 * Cada aba abaixo simula uma tabela do banco de dados (avisos, notícias,
 * calendário, horários, perguntas frequentes, links úteis e informações
 * institucionais). Adicionar, editar, excluir, reordenar e publicar/ocultar
 * hoje só alteram a lista na tela (assets/js/admin.js). Ao integrar o
 * backend, cada ação deve persistir a alteração no banco.
 */
$tituloPaginaAdmin = 'Informações';
$paginaAtualAdmin = 'informacoes';
require __DIR__ . '/includes/admin-cabecalho.php';

$abasInformacoes = [
    ['id' => 'avisos', 'icone' => 'megaphone', 'texto' => 'Avisos'],
    ['id' => 'noticias', 'icone' => 'newspaper', 'texto' => 'Notícias'],
    ['id' => 'calendario', 'icone' => 'calendar-days', 'texto' => 'Calendário acadêmico'],
    ['id' => 'horarios', 'icone' => 'clock', 'texto' => 'Horários'],
    ['id' => 'faq', 'icone' => 'circle-help', 'texto' => 'Perguntas frequentes'],
    ['id' => 'links', 'icone' => 'link', 'texto' => 'Links úteis'],
    ['id' => 'institucional', 'icone' => 'landmark', 'texto' => 'Informações institucionais'],
];

$itensAvisos = [
    ['titulo' => 'Manutenção programada no SUAP', 'mensagem' => 'O sistema ficará indisponível no sábado, das 8h às 12h.', 'destaque' => true, 'status' => 'publicado'],
    ['titulo' => 'Semana de provas finais', 'mensagem' => 'As provas finais ocorrem entre os dias 10 e 14 de agosto.', 'destaque' => false, 'status' => 'publicado'],
];

$itensNoticias = [
    ['titulo' => 'Inscrições abertas para auxílio estudantil 2026', 'data' => '24/05/2026', 'categoria' => 'Assistência', 'destaque' => true, 'status' => 'publicado'],
    ['titulo' => 'Novo laboratório de informática inaugurado', 'data' => '22/05/2026', 'categoria' => 'Infraestrutura', 'destaque' => false, 'status' => 'publicado'],
    ['titulo' => 'Calendário acadêmico 2026/2 disponível no SUAP', 'data' => '20/05/2026', 'categoria' => 'Acadêmico', 'destaque' => false, 'status' => 'oculto'],
];

$itensCalendario = [
    ['evento' => 'Início do semestre letivo', 'data' => '03/08/2026', 'periodo' => '2026/2', 'status' => 'publicado'],
    ['evento' => 'Semana de provas finais', 'data' => '10/08/2026', 'periodo' => '2026/2', 'status' => 'publicado'],
    ['evento' => 'Recesso acadêmico', 'data' => '21/12/2026', 'periodo' => '2026/2', 'status' => 'publicado'],
];

$itensHorarios = [
    ['servico' => 'Biblioteca — Acervo Geral', 'dias' => 'Segunda a sexta', 'horario' => '7h às 21h', 'status' => 'publicado'],
    ['servico' => 'CRA — Atendimento presencial', 'dias' => 'Segunda a sexta', 'horario' => '7h às 17h', 'status' => 'publicado'],
    ['servico' => 'Refeitório — Almoço', 'dias' => 'Segunda a sexta', 'horario' => '11h às 14h', 'status' => 'publicado'],
];

$itensFaq = [
    ['pergunta' => 'Como faço para solicitar uma declaração de matrícula?', 'resposta' => 'Pelo SUAP ou presencialmente no CRA, com documento de identificação. Prazo de até 3 dias úteis.', 'status' => 'publicado'],
    ['pergunta' => 'Como faço o trancamento de matrícula?', 'resposta' => 'Solicite pelo SUAP ou presencialmente no CRA, com justificativa. Análise em até 5 dias úteis.', 'status' => 'publicado'],
];

$itensLinks = [
    ['titulo' => 'Portal do IFRO', 'url' => 'https://ifro.edu.br', 'status' => 'publicado'],
    ['titulo' => 'SUAP', 'url' => 'https://suap.ifro.edu.br', 'status' => 'publicado'],
];

$itensInstitucional = [
    ['titulo' => 'Missão', 'conteudo' => 'Promover educação profissional e tecnológica de qualidade para o desenvolvimento da região.', 'status' => 'publicado'],
    ['titulo' => 'Sobre o campus', 'conteudo' => 'O Campus Guajará-Mirim oferece cursos técnicos e superiores voltados à região do Vale do Guaporé.', 'status' => 'publicado'],
];

/** Gera os atributos data-* de um item para reaproveitar a mesma linha de <li>. */
function renderizarItemInformacao(array $item, array $camposExtras): string
{
    $atributos = '';
    foreach ($camposExtras as $chave) {
        if (array_key_exists($chave, $item)) {
            $valor = is_bool($item[$chave]) ? ($item[$chave] ? '1' : '0') : (string) $item[$chave];
            $atributos .= ' data-' . $chave . '="' . htmlspecialchars($valor) . '"';
        }
    }
    return $atributos;
}
?>

<div class="admin-page-header">
    <div>
        <h2>Informações do site</h2>
        <p>Gerencie os conteúdos gerais exibidos para os visitantes do Guia IFRO.</p>
    </div>
</div>

<div class="admin-tabs" role="tablist" aria-label="Tipos de informação">
    <?php foreach ($abasInformacoes as $indice => $aba): ?>
        <button
            type="button"
            class="admin-tab-button <?= $indice === 0 ? 'active' : '' ?>"
            role="tab"
            aria-selected="<?= $indice === 0 ? 'true' : 'false' ?>"
            data-aba-alvo="<?= htmlspecialchars($aba['id']) ?>"
        >
            <?= icone($aba['icone']) ?>
            <span><?= htmlspecialchars($aba['texto']) ?></span>
        </button>
    <?php endforeach; ?>
</div>

<!-- ABA: AVISOS -->
<section class="admin-tab-painel" data-aba-painel="avisos">
    <div class="admin-tab-painel-header">
        <p>Avisos de destaque exibidos no topo do site público.</p>
        <button type="button" class="button button-primary button-small admin-adicionar-informacao" data-aba="avisos">
            <?= icone('plus') ?><span>Adicionar aviso</span>
        </button>
    </div>
    <ul class="admin-lista-informacoes" data-lista-informacao="avisos">
        <?php foreach ($itensAvisos as $item): ?>
            <li class="admin-item-informacao" <?= renderizarItemInformacao($item, ['titulo', 'mensagem', 'destaque', 'status']) ?>>
                <div class="admin-item-informacao-corpo">
                    <strong><?= htmlspecialchars($item['titulo']) ?></strong>
                    <?php if (!empty($item['destaque'])): ?><span class="admin-badge-destaque">Urgente</span><?php endif; ?>
                    <p><?= htmlspecialchars($item['mensagem']) ?></p>
                </div>
                <div class="admin-item-informacao-acoes">
                    <button type="button" class="admin-icon-button" data-mover="cima" aria-label="Mover para cima"><?= icone('chevron-up') ?></button>
                    <button type="button" class="admin-icon-button" data-mover="baixo" aria-label="Mover para baixo"><?= icone('chevron-down') ?></button>
                    <button type="button" class="admin-status-badge admin-status-<?= $item['status'] === 'publicado' ? 'ativo' : 'inativo' ?>" data-alternar-publicacao aria-pressed="<?= $item['status'] === 'publicado' ? 'true' : 'false' ?>">
                        <?= icone($item['status'] === 'publicado' ? 'eye' : 'eye-off') ?>
                        <span data-texto-status><?= $item['status'] === 'publicado' ? 'Publicado' : 'Oculto' ?></span>
                    </button>
                    <button type="button" class="admin-icon-button admin-editar-item-informacao" aria-label="Editar item"><?= icone('pencil') ?></button>
                    <button type="button" class="admin-icon-button admin-icon-button-danger admin-excluir-item-informacao" aria-label="Excluir item"><?= icone('trash-2') ?></button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<!-- ABA: NOTÍCIAS -->
<section class="admin-tab-painel" data-aba-painel="noticias" hidden>
    <div class="admin-tab-painel-header">
        <p>Notícias exibidas na página inicial do site público.</p>
        <button type="button" class="button button-primary button-small admin-adicionar-informacao" data-aba="noticias">
            <?= icone('plus') ?><span>Adicionar notícia</span>
        </button>
    </div>
    <ul class="admin-lista-informacoes" data-lista-informacao="noticias">
        <?php foreach ($itensNoticias as $item): ?>
            <li class="admin-item-informacao" <?= renderizarItemInformacao($item, ['titulo', 'data', 'categoria', 'destaque', 'status']) ?>>
                <div class="admin-item-informacao-corpo">
                    <strong><?= htmlspecialchars($item['titulo']) ?></strong>
                    <?php if (!empty($item['destaque'])): ?><span class="admin-badge-destaque">Urgente</span><?php endif; ?>
                    <p><?= htmlspecialchars($item['categoria']) ?> · <?= htmlspecialchars($item['data']) ?></p>
                </div>
                <div class="admin-item-informacao-acoes">
                    <button type="button" class="admin-icon-button" data-mover="cima" aria-label="Mover para cima"><?= icone('chevron-up') ?></button>
                    <button type="button" class="admin-icon-button" data-mover="baixo" aria-label="Mover para baixo"><?= icone('chevron-down') ?></button>
                    <button type="button" class="admin-status-badge admin-status-<?= $item['status'] === 'publicado' ? 'ativo' : 'inativo' ?>" data-alternar-publicacao aria-pressed="<?= $item['status'] === 'publicado' ? 'true' : 'false' ?>">
                        <?= icone($item['status'] === 'publicado' ? 'eye' : 'eye-off') ?>
                        <span data-texto-status><?= $item['status'] === 'publicado' ? 'Publicado' : 'Oculto' ?></span>
                    </button>
                    <button type="button" class="admin-icon-button admin-editar-item-informacao" aria-label="Editar item"><?= icone('pencil') ?></button>
                    <button type="button" class="admin-icon-button admin-icon-button-danger admin-excluir-item-informacao" aria-label="Excluir item"><?= icone('trash-2') ?></button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<!-- ABA: CALENDÁRIO ACADÊMICO -->
<section class="admin-tab-painel" data-aba-painel="calendario" hidden>
    <div class="admin-tab-painel-header">
        <p>Datas importantes do calendário acadêmico.</p>
        <button type="button" class="button button-primary button-small admin-adicionar-informacao" data-aba="calendario">
            <?= icone('plus') ?><span>Adicionar data</span>
        </button>
    </div>
    <ul class="admin-lista-informacoes" data-lista-informacao="calendario">
        <?php foreach ($itensCalendario as $item): ?>
            <li class="admin-item-informacao" <?= renderizarItemInformacao($item, ['evento', 'data', 'periodo', 'status']) ?>>
                <div class="admin-item-informacao-corpo">
                    <strong><?= htmlspecialchars($item['evento']) ?></strong>
                    <p><?= htmlspecialchars($item['periodo']) ?> · <?= htmlspecialchars($item['data']) ?></p>
                </div>
                <div class="admin-item-informacao-acoes">
                    <button type="button" class="admin-icon-button" data-mover="cima" aria-label="Mover para cima"><?= icone('chevron-up') ?></button>
                    <button type="button" class="admin-icon-button" data-mover="baixo" aria-label="Mover para baixo"><?= icone('chevron-down') ?></button>
                    <button type="button" class="admin-status-badge admin-status-<?= $item['status'] === 'publicado' ? 'ativo' : 'inativo' ?>" data-alternar-publicacao aria-pressed="<?= $item['status'] === 'publicado' ? 'true' : 'false' ?>">
                        <?= icone($item['status'] === 'publicado' ? 'eye' : 'eye-off') ?>
                        <span data-texto-status><?= $item['status'] === 'publicado' ? 'Publicado' : 'Oculto' ?></span>
                    </button>
                    <button type="button" class="admin-icon-button admin-editar-item-informacao" aria-label="Editar item"><?= icone('pencil') ?></button>
                    <button type="button" class="admin-icon-button admin-icon-button-danger admin-excluir-item-informacao" aria-label="Excluir item"><?= icone('trash-2') ?></button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<!-- ABA: HORÁRIOS -->
<section class="admin-tab-painel" data-aba-painel="horarios" hidden>
    <div class="admin-tab-painel-header">
        <p>Horários de funcionamento exibidos nas páginas de setor.</p>
        <button type="button" class="button button-primary button-small admin-adicionar-informacao" data-aba="horarios">
            <?= icone('plus') ?><span>Adicionar horário</span>
        </button>
    </div>
    <ul class="admin-lista-informacoes" data-lista-informacao="horarios">
        <?php foreach ($itensHorarios as $item): ?>
            <li class="admin-item-informacao" <?= renderizarItemInformacao($item, ['servico', 'dias', 'horario', 'status']) ?>>
                <div class="admin-item-informacao-corpo">
                    <strong><?= htmlspecialchars($item['servico']) ?></strong>
                    <p><?= htmlspecialchars($item['dias']) ?> · <?= htmlspecialchars($item['horario']) ?></p>
                </div>
                <div class="admin-item-informacao-acoes">
                    <button type="button" class="admin-icon-button" data-mover="cima" aria-label="Mover para cima"><?= icone('chevron-up') ?></button>
                    <button type="button" class="admin-icon-button" data-mover="baixo" aria-label="Mover para baixo"><?= icone('chevron-down') ?></button>
                    <button type="button" class="admin-status-badge admin-status-<?= $item['status'] === 'publicado' ? 'ativo' : 'inativo' ?>" data-alternar-publicacao aria-pressed="<?= $item['status'] === 'publicado' ? 'true' : 'false' ?>">
                        <?= icone($item['status'] === 'publicado' ? 'eye' : 'eye-off') ?>
                        <span data-texto-status><?= $item['status'] === 'publicado' ? 'Publicado' : 'Oculto' ?></span>
                    </button>
                    <button type="button" class="admin-icon-button admin-editar-item-informacao" aria-label="Editar item"><?= icone('pencil') ?></button>
                    <button type="button" class="admin-icon-button admin-icon-button-danger admin-excluir-item-informacao" aria-label="Excluir item"><?= icone('trash-2') ?></button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<!-- ABA: PERGUNTAS FREQUENTES -->
<section class="admin-tab-painel" data-aba-painel="faq" hidden>
    <div class="admin-tab-painel-header">
        <p>Perguntas e respostas exibidas nas páginas de setor.</p>
        <button type="button" class="button button-primary button-small admin-adicionar-informacao" data-aba="faq">
            <?= icone('plus') ?><span>Adicionar pergunta</span>
        </button>
    </div>
    <ul class="admin-lista-informacoes" data-lista-informacao="faq">
        <?php foreach ($itensFaq as $item): ?>
            <li class="admin-item-informacao" <?= renderizarItemInformacao($item, ['pergunta', 'resposta', 'status']) ?>>
                <div class="admin-item-informacao-corpo">
                    <strong><?= htmlspecialchars($item['pergunta']) ?></strong>
                    <p><?= htmlspecialchars($item['resposta']) ?></p>
                </div>
                <div class="admin-item-informacao-acoes">
                    <button type="button" class="admin-icon-button" data-mover="cima" aria-label="Mover para cima"><?= icone('chevron-up') ?></button>
                    <button type="button" class="admin-icon-button" data-mover="baixo" aria-label="Mover para baixo"><?= icone('chevron-down') ?></button>
                    <button type="button" class="admin-status-badge admin-status-<?= $item['status'] === 'publicado' ? 'ativo' : 'inativo' ?>" data-alternar-publicacao aria-pressed="<?= $item['status'] === 'publicado' ? 'true' : 'false' ?>">
                        <?= icone($item['status'] === 'publicado' ? 'eye' : 'eye-off') ?>
                        <span data-texto-status><?= $item['status'] === 'publicado' ? 'Publicado' : 'Oculto' ?></span>
                    </button>
                    <button type="button" class="admin-icon-button admin-editar-item-informacao" aria-label="Editar item"><?= icone('pencil') ?></button>
                    <button type="button" class="admin-icon-button admin-icon-button-danger admin-excluir-item-informacao" aria-label="Excluir item"><?= icone('trash-2') ?></button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<!-- ABA: LINKS ÚTEIS -->
<section class="admin-tab-painel" data-aba-painel="links" hidden>
    <div class="admin-tab-painel-header">
        <p>Links úteis exibidos no rodapé e nas páginas de setor.</p>
        <button type="button" class="button button-primary button-small admin-adicionar-informacao" data-aba="links">
            <?= icone('plus') ?><span>Adicionar link</span>
        </button>
    </div>
    <ul class="admin-lista-informacoes" data-lista-informacao="links">
        <?php foreach ($itensLinks as $item): ?>
            <li class="admin-item-informacao" <?= renderizarItemInformacao($item, ['titulo', 'url', 'status']) ?>>
                <div class="admin-item-informacao-corpo">
                    <strong><?= htmlspecialchars($item['titulo']) ?></strong>
                    <p><?= htmlspecialchars($item['url']) ?></p>
                </div>
                <div class="admin-item-informacao-acoes">
                    <button type="button" class="admin-icon-button" data-mover="cima" aria-label="Mover para cima"><?= icone('chevron-up') ?></button>
                    <button type="button" class="admin-icon-button" data-mover="baixo" aria-label="Mover para baixo"><?= icone('chevron-down') ?></button>
                    <button type="button" class="admin-status-badge admin-status-<?= $item['status'] === 'publicado' ? 'ativo' : 'inativo' ?>" data-alternar-publicacao aria-pressed="<?= $item['status'] === 'publicado' ? 'true' : 'false' ?>">
                        <?= icone($item['status'] === 'publicado' ? 'eye' : 'eye-off') ?>
                        <span data-texto-status><?= $item['status'] === 'publicado' ? 'Publicado' : 'Oculto' ?></span>
                    </button>
                    <button type="button" class="admin-icon-button admin-editar-item-informacao" aria-label="Editar item"><?= icone('pencil') ?></button>
                    <button type="button" class="admin-icon-button admin-icon-button-danger admin-excluir-item-informacao" aria-label="Excluir item"><?= icone('trash-2') ?></button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<!-- ABA: INFORMAÇÕES INSTITUCIONAIS -->
<section class="admin-tab-painel" data-aba-painel="institucional" hidden>
    <div class="admin-tab-painel-header">
        <p>Textos institucionais exibidos na página "Sobre o IFRO".</p>
        <button type="button" class="button button-primary button-small admin-adicionar-informacao" data-aba="institucional">
            <?= icone('plus') ?><span>Adicionar texto</span>
        </button>
    </div>
    <ul class="admin-lista-informacoes" data-lista-informacao="institucional">
        <?php foreach ($itensInstitucional as $item): ?>
            <li class="admin-item-informacao" <?= renderizarItemInformacao($item, ['titulo', 'conteudo', 'status']) ?>>
                <div class="admin-item-informacao-corpo">
                    <strong><?= htmlspecialchars($item['titulo']) ?></strong>
                    <p><?= htmlspecialchars($item['conteudo']) ?></p>
                </div>
                <div class="admin-item-informacao-acoes">
                    <button type="button" class="admin-icon-button" data-mover="cima" aria-label="Mover para cima"><?= icone('chevron-up') ?></button>
                    <button type="button" class="admin-icon-button" data-mover="baixo" aria-label="Mover para baixo"><?= icone('chevron-down') ?></button>
                    <button type="button" class="admin-status-badge admin-status-<?= $item['status'] === 'publicado' ? 'ativo' : 'inativo' ?>" data-alternar-publicacao aria-pressed="<?= $item['status'] === 'publicado' ? 'true' : 'false' ?>">
                        <?= icone($item['status'] === 'publicado' ? 'eye' : 'eye-off') ?>
                        <span data-texto-status><?= $item['status'] === 'publicado' ? 'Publicado' : 'Oculto' ?></span>
                    </button>
                    <button type="button" class="admin-icon-button admin-editar-item-informacao" aria-label="Editar item"><?= icone('pencil') ?></button>
                    <button type="button" class="admin-icon-button admin-icon-button-danger admin-excluir-item-informacao" aria-label="Excluir item"><?= icone('trash-2') ?></button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<!-- DRAWER genérico: formulário reaproveitado por todas as abas, campos montados via JavaScript -->
<div class="admin-modal" id="modal-informacao" aria-hidden="true">
    <div class="admin-modal-backdrop" data-fechar-modal></div>
    <div class="admin-modal-panel" role="dialog" aria-modal="true" aria-labelledby="modal-informacao-titulo">
        <div class="admin-modal-header">
            <h2 id="modal-informacao-titulo">Novo item</h2>
            <button type="button" class="admin-icon-button" data-fechar-modal aria-label="Fechar formulário">
                <?= icone('x') ?>
            </button>
        </div>
        <form class="admin-form" id="form-informacao" novalidate>
            <div class="admin-form-campos-dinamicos" id="campos-informacao"></div>
            <div class="admin-modal-footer">
                <button type="button" class="button button-outline" data-fechar-modal>Cancelar</button>
                <button type="submit" class="button button-primary admin-botao-salvar">
                    <span class="admin-botao-texto">Salvar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/admin-rodape.php'; ?>
