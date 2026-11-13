<?php
// robots.txt gerado na hora, pra o endereço do sitemap usar o domínio configurado
// em model/Config.php (URL_SITE_CONFIGURADA). O .htaccess faz /robots.txt cair aqui.
require_once __DIR__ . '/model/Config.php';

header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /controller/\n";
echo "Disallow: /model/\n";
echo "\n";
echo 'Sitemap: ' . urlBaseSite() . "/sitemap.xml\n";
