<?php require __DIR__ . '/parciais/cabecalho.php'; ?>



<!-- HERO DA PÁGINA CONTATO -->
<section class="contact-hero about-hero">
    <div class="about-decoration contact-decoration contact-decoration-one"></div>
    <div class="about-decoration contact-decoration contact-decoration-two"></div>

    <div class="container reveal">
        <nav class="breadcrumb breadcrumb-light">
            <a href="index.php">Início</a><?= icone('chevron-right') ?><strong>Contato</strong>
        </nav>
        <h1>Fale Conosco</h1>
        <p>Tire suas dúvidas, envie sugestões ou solicite informações — nossa equipe está pronta para ajudar.</p>
    </div>
</section>

<section class="section container">
    <!-- CANAIS DE CONTATO -->
    <div class="contact-channel-grid">
        <?php foreach ($canaisContato as $indice => $canal): ?>
            <article class="contact-channel-card reveal" style="--delay: <?= $indice * 60 ?>ms">
                <span class="quick-icon color-verde"><?= icone($canal['icone']) ?></span>
                <span>
                    <strong><?= htmlspecialchars($canal['titulo']) ?></strong>
                    <small><?= htmlspecialchars($canal['linha1']) ?></small>
                    <em><?= htmlspecialchars($canal['destaque']) ?></em>
                </span>
            </article>
        <?php endforeach; ?>
    </div>

    <!-- FORMULÁRIO + INFORMAÇÕES -->
    <div class="contact-layout">
        <article class="contact-form-card">
            <?php
            // Atalhos pra view: valor antigo do campo (quando voltou com erro) e mensagem de erro.
            $valorAntigo = static function (string $campo) use ($valoresContato): string {
                return htmlspecialchars((string) ($valoresContato[$campo] ?? ''), ENT_QUOTES, 'UTF-8');
            };
            $erroCampo = static function (string $campo) use ($errosContato): string {
                if (empty($errosContato[$campo])) {
                    return '';
                }
                return '<small class="campo-erro" id="erro-' . $campo . '">' . htmlspecialchars($errosContato[$campo], ENT_QUOTES, 'UTF-8') . '</small>';
            };
            ?>
            <div id="contact-form-wrapper" <?= $contatoEnviado ? 'hidden' : '' ?>>
                <h2>Envie sua mensagem</h2>
                <p>Preencha o formulário abaixo e retornaremos o mais breve possível.</p>

                <?php if ($resultadoContato && !$contatoEnviado): ?>
                    <p class="aviso-faixa" role="alert"><?= icone('triangle-alert') ?> <span><?= htmlspecialchars($resultadoContato['mensagem'], ENT_QUOTES, 'UTF-8') ?></span></p>
                <?php endif; ?>
                <p class="aviso-faixa" id="contact-form-erro" role="alert" tabindex="-1" hidden></p>

                <form id="contact-form" class="contact-form" action="processar-contato.php" method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($tokenCsrf, ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Isca pra robô (honeypot): escondida de quem usa o site. Não preencha. -->
                    <div class="campo-isca" aria-hidden="true">
                        <label for="contato-site">Seu site</label>
                        <input type="text" id="contato-site" name="site" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-grid-two">
                        <label>
                            <span>Nome completo</span>
                            <input type="text" name="nome" placeholder="Digite seu nome" required maxlength="150" autocomplete="name" value="<?= $valorAntigo('nome') ?>" <?= isset($errosContato['nome']) ? 'aria-invalid="true" aria-describedby="erro-nome"' : '' ?>>
                            <?= $erroCampo('nome') ?>
                        </label>
                        <label>
                            <span>E-mail</span>
                            <input type="email" name="email" placeholder="seu@email.com" required maxlength="150" autocomplete="email" value="<?= $valorAntigo('email') ?>" <?= isset($errosContato['email']) ? 'aria-invalid="true" aria-describedby="erro-email"' : '' ?>>
                            <?= $erroCampo('email') ?>
                        </label>
                    </div>

                    <div class="form-grid-two">
                        <label>
                            <span>Telefone (opcional)</span>
                            <input type="tel" name="telefone" placeholder="(69) 90000-0000" maxlength="20" autocomplete="tel" value="<?= $valorAntigo('telefone') ?>" <?= isset($errosContato['telefone']) ? 'aria-invalid="true" aria-describedby="erro-telefone"' : '' ?>>
                            <?= $erroCampo('telefone') ?>
                        </label>
                        <label>
                            <span>Assunto</span>
                            <select name="assunto" required <?= isset($errosContato['assunto']) ? 'aria-invalid="true" aria-describedby="erro-assunto"' : '' ?>>
                                <option value="" <?= empty($valoresContato['assunto']) ? 'selected' : '' ?> disabled>Selecione um assunto</option>
                                <?php foreach ($assuntos as $assunto): ?>
                                    <option value="<?= htmlspecialchars($assunto, ENT_QUOTES, 'UTF-8') ?>" <?= ($valoresContato['assunto'] ?? '') === $assunto ? 'selected' : '' ?>><?= htmlspecialchars($assunto, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= $erroCampo('assunto') ?>
                        </label>
                    </div>

                    <label>
                        <span>Mensagem</span>
                        <textarea name="mensagem" placeholder="Escreva sua mensagem..." required minlength="10" maxlength="3000" <?= isset($errosContato['mensagem']) ? 'aria-invalid="true" aria-describedby="erro-mensagem"' : '' ?>><?= $valorAntigo('mensagem') ?></textarea>
                        <?= $erroCampo('mensagem') ?>
                    </label>

                    <button type="submit" class="button button-primary contact-submit">
                        <?= icone('send') ?>
                        <span>Enviar mensagem</span>
                    </button>

                    <p class="privacy-note">Ao enviar, você concorda com nossa <a href="privacidade.php">Política de Privacidade</a>.</p>
                </form>
            </div>

            <div id="form-success" class="form-success" tabindex="-1" <?= $contatoEnviado ? '' : 'hidden' ?> role="status">
                <span><?= icone('circle-check-big') ?></span>
                <h2>Mensagem enviada!</h2>
                <p>Obrigado por entrar em contato. Nossa equipe responderá o mais breve possível pelo e-mail informado.</p>
                <a href="contato.php" id="send-another-message" class="button button-outline">Enviar outra mensagem</a>
            </div>
        </article>

        <aside class="contact-sidebar">
            <article class="contact-form-card">
                <h2>Nossa localização</h2>
                <div class="location-line">
                    <?= icone('map-pin') ?>
                    <p><strong>Campus Guajará-Mirim</strong><br><?= htmlspecialchars($enderecoCampus ?: 'Endereço não informado', ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="map-placeholder">
                    <?= icone('map') ?>
                    <small>Veja o mapa interativo do campus</small>
                </div>
                <a href="mapa.php" class="button button-outline" style="width: 100%; justify-content: center;">
                    Abrir mapa interativo <?= icone('arrow-right') ?>
                </a>
            </article>

            <article class="contact-form-card">
                <h2>Redes sociais</h2>
                <div class="social-contact-list">
                    <a href="https://www.facebook.com/ifrondonia" target="_blank" rel="noopener noreferrer">
                        <span class="social-facebook"><?= icone('facebook') ?></span>
                        <span><strong>Facebook</strong><small>@ifroguajaramirim</small></span>
                    </a>
                    <a href="https://www.instagram.com/ifro.guajara/" target="_blank" rel="noopener noreferrer">
                        <span class="social-instagram"><?= icone('instagram') ?></span>
                        <span><strong>Instagram</strong><small>@ifroguajaramirim</small></span>
                    </a>
                    <a href="https://www.youtube.com/c/ifrocampusguajaramirim" target="_blank" rel="noopener noreferrer">
                        <span class="social-youtube"><?= icone('youtube') ?></span>
                        <span><strong>YouTube</strong><small>IFRO Guajará-Mirim</small></span>
                    </a>
                </div>
            </article>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/parciais/rodape.php'; ?>
