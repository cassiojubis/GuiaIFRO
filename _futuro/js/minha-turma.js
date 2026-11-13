// "MINHA TURMA HOJE" (minha-turma.php): lembra a turma escolhida neste aparelho
// e, na próxima visita, já abre direto nela. Sem login, nada vai pro servidor além da turma na URL.
(() => {
    const CHAVE = 'guiaifro-minha-turma';
    const form = document.getElementById('minha-turma-form');
    if (!form) return;
    const seletor = form.querySelector('[name="turma"]');
    const ler = () => { try { return localStorage.getItem(CHAVE) || ''; } catch (erro) { return ''; } };
    const gravar = (valor) => { try { localStorage.setItem(CHAVE, valor); } catch (erro) { /* modo privado */ } };

    const daUrl = new URLSearchParams(window.location.search).get('turma');
    const salva = ler();
    // Entrou sem turma na URL mas tem uma salva (e ela ainda existe): abre direto nela.
    if (!daUrl && salva && Array.from(seletor.options).some((opcao) => opcao.value === salva)) {
        window.location.replace(`minha-turma.php?turma=${encodeURIComponent(salva)}`);
        return;
    }
    if (daUrl) gravar(daUrl);
    // Trocar a turma já mostra as aulas (sem precisar do botão).
    seletor.addEventListener('change', () => { if (seletor.value) form.submit(); });
})();
