</main>

<!-- RODAPÉ COMPARTILHADO -->
<footer class="site-footer">
    <div class="container footer-grid">
        <section class="footer-brand">
            <a href="index.php" class="brand footer-logo">
                <span class="brand-mark">IFRO</span>
                <span class="brand-copy">
                    <strong><?= NOME_SISTEMA ?></strong>
                    <small><?= NOME_CAMPUS ?></small>
                </span>
            </a>
            <p>Sistema oficial de navegação e informações do Instituto Federal de Rondônia — Campus Guajará-Mirim.</p>
            <div class="social-list">
                <a href="#" aria-label="Facebook"><?= icone('facebook') ?></a>
                <a href="#" aria-label="Instagram"><?= icone('instagram') ?></a>
                <a href="#" aria-label="YouTube"><?= icone('youtube') ?></a>
            </div>
        </section>

        <section>
            <h2>Navegação</h2>
            <ul class="footer-links">
                <li><a href="index.php">Início</a></li>
                <li><a href="categorias.php">Categorias</a></li>
                <li><a href="mapa.php">Mapa Interativo</a></li>
                <li><a href="sobre.php">Sobre o IFRO</a></li>
                <li><a href="contato.php">Contato</a></li>
            </ul>
        </section>

        <section>
            <h2>Acesso Rápido</h2>
            <ul class="footer-links">
                <li><a href="#">SUAP</a></li>
                <li><a href="setor.php?id=biblioteca">Biblioteca</a></li>
                <li><a href="setor.php?id=cra">Matrícula</a></li>
                <li><a href="setor.php?id=assistencia">Assistência Estudantil</a></li>
                <li><a href="https://ifro.edu.br" target="_blank" rel="noopener noreferrer">Portal IFRO <?= icone('arrow-up-right') ?></a></li>
            </ul>
        </section>

        <section>
            <h2>Contato</h2>
            <ul class="footer-contact">
                <li><?= icone('phone') ?><span>(69) 3541-5282</span></li>
                <li><?= icone('mail') ?><span>guajara-mirim@ifro.edu.br</span></li>
                <li><?= icone('map-pin') ?><span>Av. Jorge Teixeira, 3146<br>Jardim Tropical, Guajará-Mirim – RO</span></li>
            </ul>

            <!-- Acesso restrito: leva o administrador para a área administrativa -->
            <a href="admin/login.php" class="button button-outline button-small footer-admin-login">
                <?= icone('lock') ?>
                <span>Área do Administrador</span>
            </a>
        </section>
    </div>

    <div class="container footer-bottom">
        <p>© <?= ANO_ATUAL ?> IFRO – Campus Guajará-Mirim. Todos os direitos reservados.</p>
        <div>
            <a href="#">Política de Privacidade</a>
            <a href="#">Acessibilidade</a>
        </div>
    </div>
</footer>

<!-- Biblioteca de ícones em JavaScript puro, sem React -->
<script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>
