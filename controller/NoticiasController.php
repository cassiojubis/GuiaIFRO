<?php
// Avisos que valem agora + todas as notícias (noticias.php). N6.
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';

$tituloPagina = 'Avisos e notícias';
$paginaAtual = 'noticias';
$avisosAtivos = listarAvisosAtivos();
$todasNoticias = listarNoticias(100);
$linkWhatsapp = configuracao('whatsapp_canal');

require __DIR__ . '/../view/noticias.php';
