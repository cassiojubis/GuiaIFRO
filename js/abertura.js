/* ABERTURA DA HOME
   Toca o vídeo do logo em tela cheia e depois some, mostrando o site.
   - Aparece uma vez por sessão (quem volta para o Início não vê de novo).
   - Botão "Pular abertura" e a tecla Esc fecham na hora.
   - Se o navegador bloquear o vídeo ou ele der erro, fecha sozinha.
   Quem decide se mostra ou não é o script do cabeçalho (classe "com-abertura" no <html>). */
(function () {
    var html = document.documentElement;
    var tela = document.getElementById('abertura');
    if (!tela) return;

    if (!html.classList.contains('com-abertura')) {
        tela.remove();
        return;
    }

    var video = tela.querySelector('video');
    var botao = tela.querySelector('.abertura-pular');
    var progresso = tela.querySelector('.abertura-progresso');
    var fechada = false;

    function marcarComoVista() {
        try { sessionStorage.setItem('guiaifro-abertura-vista', '1'); } catch (e) { /* aba anônima: tudo bem */ }
    }

    function fechar() {
        if (fechada) return;
        fechada = true;
        marcarComoVista();
        document.removeEventListener('keydown', aoApertarTecla);
        tela.classList.add('saindo');
        setTimeout(function () {
            if (video) video.pause();
            tela.remove();
            html.classList.remove('com-abertura');
        }, 750);
    }

    function aoApertarTecla(evento) {
        if (evento.key === 'Escape') fechar();
    }

    document.addEventListener('keydown', aoApertarTecla);
    if (botao) botao.addEventListener('click', fechar);

    if (!video) {
        fechar();
        return;
    }

    // segura um instante no logo final antes de sumir
    video.addEventListener('ended', function () { setTimeout(fechar, 450); });
    video.addEventListener('error', fechar);

    video.addEventListener('timeupdate', function () {
        if (progresso && video.duration) {
            progresso.style.transform = 'scaleX(' + Math.min(video.currentTime / video.duration, 1) + ')';
        }
    });

    // garante que tenta tocar; se o navegador recusar (economia de bateria etc.), não trava o site
    var tentativa = video.play();
    if (tentativa && typeof tentativa.catch === 'function') {
        tentativa.catch(fechar);
    }

    // rede lenta: se em 10 s não acabou, fecha do mesmo jeito
    setTimeout(fechar, 10000);
})();
