</main>

<!-- RODAPÉ COMPARTILHADO
     No celular fica curtinho: marca + redes numa linha e os três blocos viram itens que abrem e
     fecham (<details>, controlado em js/script.js > configurarRodape). No computador fica tudo aberto. -->
<footer class="site-footer">
    <div class="container footer-grid">
        <section class="footer-brand">
            <a href="index.php" class="brand footer-logo">
                <img class="brand-logo" src="img/logo-guiaifro.png" alt="" width="217" height="285">
                <span class="brand-copy">
                    <strong><?= NOME_SISTEMA ?></strong>
                    <small><?= NOME_CAMPUS ?></small>
                </span>
            </a>
            <p>Guia de navegação e informações do Instituto Federal de Rondônia — Campus Guajará-Mirim.</p>
            <div class="social-list">
                <a href="<?= htmlspecialchars(configuracao('rede_facebook', 'https://www.facebook.com/ifrondonia'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><?= icone('facebook') ?></a>
                <a href="<?= htmlspecialchars(configuracao('rede_instagram', 'https://www.instagram.com/ifro.guajara/'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><?= icone('instagram') ?></a>
                <a href="<?= htmlspecialchars(configuracao('rede_youtube', 'https://www.youtube.com/c/ifrocampusguajaramirim'), ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><?= icone('youtube') ?></a>
            </div>
        </section>

        <details class="footer-sec" open>
            <summary><h2><?= t('rodape_navegacao') ?></h2><?= icone('chevron-down', 'footer-sec-seta') ?></summary>
            <ul class="footer-links footer-links-nav">
                <li><a href="index.php"><?= t('nav_inicio') ?></a></li>
                <li><a href="categorias.php"><?= t('nav_categorias') ?></a></li>
                <li><a href="mapa.php">Mapa Interativo</a></li>
                <li><a href="sobre.php">Sobre o IFRO</a></li>
                <li><a href="estrangeiros.php" lang="es">Si vienes de otro país</a></li>
                <li><a href="como-faco.php">Como faço para…</a></li>
                <li><a href="noticias.php">Avisos e notícias</a></li>
                <li><a href="sobre.php#diversidade">Diversidade e Inclusão</a></li>
                <li><a href="contato.php"><?= t('nav_contato') ?></a></li>
            </ul>
        </details>

        <details class="footer-sec" open>
            <summary><h2>Acesso Rápido</h2><?= icone('chevron-down', 'footer-sec-seta') ?></summary>
            <ul class="footer-links">
                <li><a href="https://suap.ifro.edu.br" target="_blank" rel="noopener noreferrer">SUAP <?= icone('arrow-up-right') ?></a></li>
                <li><a href="setor.php?id=biblioteca">Biblioteca</a></li>
                <li><a href="setor.php?id=cra">Matrícula</a></li>
                <li><a href="setor.php?id=caed">Assistência Estudantil (CAED)</a></li>
                <li><a href="https://ifro.edu.br" target="_blank" rel="noopener noreferrer">Portal IFRO <?= icone('arrow-up-right') ?></a></li>
            </ul>
        </details>

        <details class="footer-sec" open>
            <summary><h2>Contato</h2><?= icone('chevron-down', 'footer-sec-seta') ?></summary>
            <ul class="footer-contact">
                <li><?= icone('phone') ?><span><?= htmlspecialchars(configuracao('contato_telefone', 'Não informado'), ENT_QUOTES, 'UTF-8') ?></span></li>
                <li><?= icone('mail') ?><span><?= htmlspecialchars(configuracao('contato_email', 'Não informado'), ENT_QUOTES, 'UTF-8') ?></span></li>
                <li><?= icone('map-pin') ?><span><?= htmlspecialchars(configuracao('contato_endereco', 'Não informado'), ENT_QUOTES, 'UTF-8') ?></span></li>
            </ul>

            <!-- Acesso restrito: leva o administrador para a área administrativa -->
            <a href="admin/login.php" class="button button-outline button-small footer-admin-login">
                <?= icone('lock') ?>
                <span>Área do Administrador</span>
            </a>
        </details>
    </div>

    <div class="container footer-bottom">
        <p>© <?= ANO_ATUAL ?> IFRO – Campus Guajará-Mirim.<span class="footer-direitos"> <?= t('rodape_direitos') ?></span></p>
        <div>
            <a href="privacidade.php">Política de Privacidade</a>
            <a href="acessibilidade.php"><?= t('rodape_acessibilidade') ?></a>
        </div>
    </div>
</footer>

<!-- WIDGET DE ACESSIBILIDADE: fica visível em todas as páginas -->
<div class="acessibilidade-widget" id="acessibilidade-widget">
    <button type="button" class="acessibilidade-botao" id="acessibilidade-botao" aria-label="Abrir opções de acessibilidade" aria-expanded="false">
        <?= icone('accessibility') ?>
    </button>
    <div class="acessibilidade-painel" id="acessibilidade-painel" hidden>
        <strong>Tamanho da fonte</strong>
        <div class="acessibilidade-fonte-botoes">
            <button type="button" data-fonte="normal" aria-label="Fonte normal">A</button>
            <button type="button" data-fonte="media" aria-label="Fonte média">A+</button>
            <button type="button" data-fonte="grande" aria-label="Fonte grande">A++</button>
        </div>
        <button type="button" class="acessibilidade-tema-botao alternar-tema">
            <?= icone('moon') ?> <span>Modo escuro</span>
        </button>
        <button type="button" class="preferencia-botao" data-preferencia="alto-contraste" aria-pressed="false">Alto contraste</button>
        <button type="button" class="preferencia-botao" data-preferencia="fonte-leitura" aria-pressed="false">Fonte de leitura fácil</button>
        <button type="button" class="preferencia-botao" data-preferencia="reduzir-movimento" aria-pressed="false">Menos movimento</button>
        <button type="button" class="preferencia-botao" data-preferencia="leitura-facil" aria-pressed="false">Leitura fácil</button>
        <a href="acessibilidade.php" class="acessibilidade-painel-link">
            Mais opções de acessibilidade <?= icone('arrow-right') ?>
        </a>
    </div>
</div>

<!-- VLibras: tradutor de Libras (governo federal, vlibras.gov.br) -->
<div vw class="enabled">
    <div vw-access-button class="active"></div>
    <div vw-plugin-wrapper>
        <div class="vw-plugin-top-wrapper"></div>
    </div>
</div>
<!-- carregado só depois da página pronta, sem travar nada (ver js/vlibras.js) -->
<script src="js/vlibras.js?v=<?= filemtime(__DIR__ . '/../../js/vlibras.js') ?>" defer></script>

<?php if (idiomaAtual() === 'es'): // tela em espanhol: traduz os textos fixos da página (js/traducao-es.js) ?>
    <script src="js/traducao-es.js?v=<?= filemtime(__DIR__ . '/../../js/traducao-es.js') ?>"></script>
<?php endif; ?>
<?php if (idiomaAtual() === 'wari'): // Oro Wari: troca só as frases que têm tradução no documento (js/traducao-wari.js) ?>
    <script type="application/json" id="traducao-wari-dados"><?= json_encode(dicionarioWariParaJs(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <script src="js/traducao-wari.js?v=<?= filemtime(__DIR__ . '/../../js/traducao-wari.js') ?>"></script>
<?php endif; ?>

<!-- Biblioteca de ícones em JavaScript puro, sem React -->
<?php /* Ícones agora são gerados no servidor (icone()); o lucide.min.js ficou só no admin. */ ?>
<script src="js/util.js?v=<?= filemtime(__DIR__ . '/../../js/util.js') ?>"></script>
<script src="js/script.js?v=<?= filemtime(__DIR__ . '/../../js/script.js') ?>"></script>
<script src="js/ouvir.js?v=<?= filemtime(__DIR__ . '/../../js/ouvir.js') ?>"></script>
<?php // scripts só de uma página (a página define $scriptsExtras = [...]) ?>
<?php foreach ($scriptsExtras ?? [] as $scriptExtra): ?>
    <script src="<?= htmlspecialchars($scriptExtra, ENT_QUOTES, 'UTF-8') ?>?v=<?= filemtime(__DIR__ . '/../../' . $scriptExtra) ?>"></script>
<?php endforeach; ?>
</body>
</html>
