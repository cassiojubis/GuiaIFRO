    </main>

    <!-- MODAL COMPARTILHADO: confirmação de exclusão, usado por todas as páginas com listas -->
    <div class="admin-modal admin-modal-confirmacao" id="modal-confirmar-exclusao" aria-hidden="true">
        <div class="admin-modal-backdrop" data-fechar-modal></div>
        <div class="admin-modal-panel" role="alertdialog" aria-modal="true" aria-labelledby="modal-confirmar-exclusao-titulo">
            <span class="admin-modal-confirmacao-icone"><?= icone('triangle-alert') ?></span>
            <h2 id="modal-confirmar-exclusao-titulo">Confirmar exclusão</h2>
            <p class="admin-modal-confirmacao-texto" id="modal-confirmar-exclusao-texto">Tem certeza que deseja excluir este item? Essa ação não poderá ser desfeita.</p>
            <div class="admin-modal-footer">
                <button type="button" class="button button-outline" data-fechar-modal>Cancelar</button>
                <button type="button" class="button button-primary" id="botao-confirmar-exclusao" style="background: var(--danger); box-shadow: none;">
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

<!-- Container das notificações toast, preenchido dinamicamente pelo admin.js -->
<div class="admin-toast-container" id="admin-toast-container" aria-live="polite"></div>

<!-- Biblioteca de ícones em JavaScript puro, sem React -->
<script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
<script src="assets/js/admin.js"></script>
</body>
</html>
