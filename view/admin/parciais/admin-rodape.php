    </main>

    <!-- MODAL COMPARTILHADO: confirmação de exclusão, usado por todas as páginas com listas.
         Ao confirmar, o admin.js envia o formulário do botão que abriu este modal. -->
    <div class="admin-modal admin-modal-confirmacao" id="modal-confirmar-exclusao" aria-hidden="true">
        <div class="admin-modal-backdrop" data-fechar-modal></div>
        <div class="admin-modal-panel" role="alertdialog" aria-modal="true" aria-labelledby="modal-confirmar-exclusao-titulo" aria-describedby="modal-confirmar-exclusao-texto">
            <span class="admin-modal-confirmacao-icone"><?= icone('triangle-alert') ?></span>
            <h2 id="modal-confirmar-exclusao-titulo">Confirmar exclusão</h2>
            <p class="admin-modal-confirmacao-texto" id="modal-confirmar-exclusao-texto">Tem certeza que deseja excluir este item? Essa ação não poderá ser desfeita.</p>
            <div class="admin-modal-footer">
                <button type="button" class="button button-outline" data-fechar-modal>Cancelar</button>
                <button type="button" class="button button-primary admin-botao-perigo" id="botao-confirmar-exclusao">
                    <?= icone('trash-2') ?>
                    <span>Excluir</span>
                </button>
            </div>
        </div>
    </div>

    <footer class="admin-footer">
        <p>© <?= ANO_ATUAL ?> <?= NOME_SISTEMA ?> — <?= NOME_CAMPUS ?>. Painel administrativo interno.</p>
    </footer>

</div><!-- /.admin-shell -->

<!-- Container das notificações toast, preenchido pelo admin.js -->
<div class="admin-toast-container" id="admin-toast-container" aria-live="polite"></div>

<!-- Biblioteca de ícones em JavaScript puro, sem React -->
<script src="../js/lucide.min.js"></script>
<script src="../js/util.js?v=<?= filemtime(__DIR__ . '/../../../js/util.js') ?>"></script>
<script src="assets/js/admin.js?v=<?= filemtime(__DIR__ . '/../../../admin/assets/js/admin.js') ?>"></script>
<?php foreach ($scriptsExtrasAdmin ?? [] as $scriptExtra): ?>
    <script src="<?= htmlspecialchars($scriptExtra, ENT_QUOTES, 'UTF-8') ?>?v=<?= filemtime(__DIR__ . '/../../../admin/' . $scriptExtra) ?>"></script>
<?php endforeach; ?>
</body>
</html>
