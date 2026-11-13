<?php require __DIR__ . '/parciais/cabecalho.php'; ?>

<section class="page-header light-header sector-header">
    <div class="container reveal">
        <nav class="breadcrumb">
            <a href="index.php"><?= icone('arrow-left') ?> Início</a>
            <?= icone('chevron-right') ?>
            <a href="categorias.php">Categorias</a>
            <?= icone('chevron-right') ?>
            <strong><?= htmlspecialchars($siglaSetor, ENT_QUOTES, 'UTF-8') ?></strong>
        </nav>

        <div class="sector-title-row">
            <span class="sector-main-icon"><?= icone($setorSelecionado['icone']) ?></span>
            <div>
                <div class="sector-name-line">
                    <h1><?= htmlspecialchars($nomeSetor, ENT_QUOTES, 'UTF-8') ?></h1>
                    <?php if ($setorSelecionado['sigla'] !== ''): ?>
                        <span><?= htmlspecialchars($setorSelecionado['sigla'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                    <?php if ($abertoAgora === true): ?>
                        <span class="status-horario status-aberto"><?= icone('circle') ?> <?= htmlspecialchars($estadoHorario['resumo'] ?? 'Aberto agora', ENT_QUOTES, 'UTF-8') ?></span>
                    <?php elseif ($abertoAgora === false): ?>
                        <span class="status-horario status-fechado"><?= icone('circle') ?> <?= htmlspecialchars($estadoHorario['resumo'] ?? 'Fechado agora', ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($descricaoSetor !== ''): ?>
                    <p <?= $audioDescricao ? 'data-audio-url="' . htmlspecialchars($audioDescricao, ENT_QUOTES, 'UTF-8') . '"' : '' ?>><?= htmlspecialchars($descricaoSetor, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php // Selo de confiança (N7): quando e por quem os dados foram atualizados ?>
                <p class="sector-grupo sector-atualizado">
                    <?= icone('badge-check') ?>
                    Atualizado em <?= htmlspecialchars((new DateTimeImmutable($setorSelecionado['atualizado_em']))->format('d/m/Y'), ENT_QUOTES, 'UTF-8') ?>
                    <?php if ($setorSelecionado['atualizado_por'] && $setorSelecionado['atualizado_por'] !== 'Importação inicial'): ?>
                        por <?= htmlspecialchars($setorSelecionado['atualizado_por'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </p>
                <?php if (!empty($setorSelecionado['categoria_nome'])): ?>
                    <p class="sector-grupo"><?= icone('shapes') ?> <?= htmlspecialchars($setorSelecionado['categoria_nome'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>
            <button type="button" class="botao-favoritar" data-favoritar-id="<?= htmlspecialchars($setorSelecionado['id'], ENT_QUOTES, 'UTF-8') ?>" aria-label="Favoritar este setor" aria-pressed="false">
                <?= icone('star') ?>
            </button>
        </div>
    </div>
</section>

<div id="setor-atual-id" data-id="<?= htmlspecialchars($setorSelecionado['id'], ENT_QUOTES, 'UTF-8') ?>" hidden></div>

<section class="section container">
    <!-- LEITURA FÁCIL (I4): só aparece com a opção ligada -->
    <article class="content-card so-leitura-facil facil-setor">
        <h2><?= icone('message-square-text') ?> Em poucas palavras</h2>
        <?php if (!empty($textoFacil)): ?>
            <?php foreach (preg_split('/\R+/u', trim($textoFacil)) as $frase): ?>
                <p><?= htmlspecialchars($frase, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Este setor ainda não tem texto em leitura fácil.</p>
            <p>Se precisar de ajuda, <a href="contato.php">fale com a gente</a>.</p>
        <?php endif; ?>
        <div class="facil-grade">
            <?php if ($salaNoMapa): ?>
                <a href="mapa.php?sala=<?= rawurlencode($salaNoMapa['id']) ?>" class="facil-botao"><?= icone('map-pin') ?><span>Como chegar</span></a>
            <?php endif; ?>
            <a href="contato.php" class="facil-botao"><?= icone('message-circle') ?><span>Perguntar</span></a>
        </div>
    </article>

    <?php if ($salaAqui && $veioDoQr): ?>
        <?php require __DIR__ . '/parciais/estou-aqui.php'; ?>
    <?php endif; ?>
    <?php if ($setorSemDados): ?>
        <!-- Setor ainda sem informações: nunca uma página vazia ou quebrada -->
        <article class="content-card setor-em-breve reveal">
            <span class="setor-em-breve-icone"><?= icone('clock') ?></span>
            <div>
                <h2>Informações em breve</h2>
                <p>Ainda estamos reunindo os dados deste setor (horário, contato e localização). Enquanto isso, fale com a gente que ajudamos você a encontrar o que precisa.</p>
                <div class="setor-em-breve-acoes">
                    <a href="contato.php" class="button button-primary"><?= icone('mail') ?> Falar conosco</a>
                    <?php if ($salaNoMapa): ?>
                        <a href="mapa.php?sala=<?= rawurlencode($salaNoMapa['id']) ?>" class="button button-outline"><?= icone('map-pin') ?> Ver no mapa</a>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php if ($salaAcess): ?>
            <?php require __DIR__ . '/parciais/acessibilidade-local.php'; ?>
        <?php endif; ?>
    <?php else: ?>
    <!-- Informações principais do setor -->
    <div class="info-chip-grid">
        <?php
        $informacoes = [
            ['phone', 'Telefone', $setorSelecionado['telefone']],
            ['mail', 'E-mail', $setorSelecionado['email']],
            ['clock', 'Horário', $textoHorario . ($observacaoHorario !== '' ? ' (' . $observacaoHorario . ')' : '')],
            ['map-pin', 'Localização', $setorSelecionado['localizacao']],
        ];
        ?>
        <?php foreach ($informacoes as $indice => [$iconeInfo, $rotuloInfo, $valorInfo]): ?>
            <article class="info-chip reveal" style="--delay: <?= $indice * 80 ?>ms">
                <span><?= icone($iconeInfo) ?></span>
                <div>
                    <small><?= htmlspecialchars($rotuloInfo, ENT_QUOTES, 'UTF-8') ?></small>
                    <strong><?= htmlspecialchars($valorInfo ?: 'Não informado', ENT_QUOTES, 'UTF-8') ?></strong>
                    <?php if ($iconeInfo === 'map-pin' && $salaNoMapa): ?>
                        <a class="info-chip-link" href="mapa.php?sala=<?= rawurlencode($salaNoMapa['id']) ?>"><?= icone('map') ?> Ver no mapa (<?= htmlspecialchars($localNoMapa, ENT_QUOTES, 'UTF-8') ?>)</a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="sector-layout">
        <div class="sector-main-column">
            <?php if ($responsavel): ?>
            <!-- Responsável principal -->
            <article class="content-card reveal">
                <h2>Responsável</h2>
                <div class="responsible-person">
                    <span class="avatar-large"><?= htmlspecialchars($inicialResponsavel, ENT_QUOTES, 'UTF-8') ?></span>
                    <div>
                        <h3><?= htmlspecialchars($responsavel, ENT_QUOTES, 'UTF-8') ?></h3>
                        <p>Responsável pelo setor</p>
                        <div>
                            <?php if ($setorSelecionado['email']): ?>
                                <a href="mailto:<?= htmlspecialchars($setorSelecionado['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($setorSelecionado['email'], ENT_QUOTES, 'UTF-8') ?></a>
                            <?php endif; ?>
                            <?php if ($setorSelecionado['email'] && $setorSelecionado['telefone']): ?><i>·</i><?php endif; ?>
                            <?php if ($setorSelecionado['telefone']): ?>
                                <span><?= htmlspecialchars($setorSelecionado['telefone'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Vídeo do responsável explicando o setor. Enquanto não existe o vídeo real,
                     mostra um aviso no lugar do player (ver $videoResponsavel em SetorController.php) -->
                <div class="responsible-video">
                    <?php if (!empty($videoResponsavel)): ?>
                        <video controls preload="metadata" class="responsible-video-player">
                            <source src="<?= htmlspecialchars($videoResponsavel, ENT_QUOTES, 'UTF-8') ?>" type="video/mp4">
                            Seu navegador não suporta vídeo em HTML5.
                        </video>
                    <?php else: ?>
                        <div class="responsible-video-placeholder">
                            <?= icone('video') ?>
                            <div>
                                <strong>Vídeo em breve</strong>
                                <p>Ainda não gravamos o vídeo de <?= htmlspecialchars($responsavel, ENT_QUOTES, 'UTF-8') ?> explicando este setor.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
            <?php endif; ?>

            <?php if ($equipe): ?>
            <!-- Equipe -->
            <article class="content-card reveal">
                <h2><?= icone('star') ?> Equipe</h2>
                <div class="team-list">
                    <?php foreach ($equipe as $membro): ?>
                        <div class="team-member">
                            <span class="avatar-small"><?= htmlspecialchars($membro['iniciais'], ENT_QUOTES, 'UTF-8') ?></span>
                            <div><strong><?= htmlspecialchars($membro['nome'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($membro['cargo'], ENT_QUOTES, 'UTF-8') ?></small></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
            <?php endif; ?>

            <?php if ($faqSetor): ?>
            <!-- FAQ em formato de acordeão controlado por JavaScript -->
            <article class="content-card reveal">
                <h2>Perguntas Frequentes</h2>
                <div class="accordion" data-accordion>
                    <?php foreach ($faqSetor as $indice => $item): ?>
                        <div class="accordion-item <?= $indice === 0 ? 'open' : '' ?>">
                            <button type="button" class="accordion-trigger" aria-expanded="<?= $indice === 0 ? 'true' : 'false' ?>">
                                <span><?= htmlspecialchars($item['pergunta'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?= icone('chevron-down') ?>
                            </button>
                            <div class="accordion-content">
                                <p><?= htmlspecialchars($item['resposta'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </article>
            <?php endif; ?>

            <article class="content-card reveal">
                <h2>Ficou com dúvida ou viu algo errado?</h2>
                <p>Mande sua dúvida para o setor ou avise se alguma informação desta página estiver errada. A resposta chega no seu e-mail.</p>
                <p class="setor-acoes-contato">
                    <a href="contato.php?assunto=<?= rawurlencode('Dúvida para um setor') ?>" class="button button-outline button-small"><?= icone('message-circle') ?> Mandar dúvida</a>
                    <a href="contato.php?assunto=<?= rawurlencode('Informação errada no site') ?>" class="button button-outline button-small"><?= icone('triangle-alert') ?> Avisar informação errada</a>
                </p>
            </article>
        </div>

        <aside class="sector-sidebar">
            <article class="content-card sticky-card reveal">
                <h2><?= icone('file-text') ?> Serviços Oferecidos</h2>
                <?php if ($servicos): ?>
                    <ul class="check-list">
                        <?php foreach ($servicos as $servico): ?>
                            <li><?= icone('circle-check-big') ?><span><?= htmlspecialchars($servico, ENT_QUOTES, 'UTF-8') ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Não informado.</p>
                <?php endif; ?>
            </article>

            <article class="content-card reveal">
                <h2>Documentos Úteis</h2>
                <div class="document-list">
                    <?php if ($documentos): ?>
                        <?php foreach ($documentos as $documento): ?>
                            <div class="document-item">
                                <span><strong><?= htmlspecialchars($documento['nome'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($documento['tamanho'], ENT_QUOTES, 'UTF-8') ?></small></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Não informado.</p>
                    <?php endif; ?>
                </div>
            </article>

            <?php if ($salaAcess): ?>
                <?php require __DIR__ . '/parciais/acessibilidade-local.php'; ?>
            <?php endif; ?>

            <?php if ($salaAqui && !$veioDoQr): ?>
                <?php require __DIR__ . '/parciais/estou-aqui.php'; ?>
            <?php endif; ?>

            <article class="sidebar-green-card reveal">
                <h2>Dúvidas?</h2>
                <p>Entre em contato diretamente com nossa equipe.</p>
                <a href="contato.php" class="button button-white button-full">Fale conosco</a>
            </article>
        </aside>
    </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
