<?php
/**
 * QR CODE DA PORTA (qr.php?sala=cra) — devolve a imagem SVG.
 * O QR abre a página do setor/sala com o bloco "Estou aqui, quero ir para...".
 * Gerado aqui mesmo (model/QrCode.php), sem serviço externo.
 */
require_once __DIR__ . '/../model/Config.php';
require_once __DIR__ . '/../model/Repositorio.php';
require_once __DIR__ . '/../model/QrCode.php';

$idSala = preg_replace('/[^a-z0-9_-]/i', '', (string) ($_GET['sala'] ?? ''));
$sala = $idSala !== '' ? buscarSalaDoMapa($idSala) : null;
if (!$sala) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Sala não encontrada.';
    exit;
}

$tamanho = max(120, min(1200, (int) ($_GET['tamanho'] ?? 300)));
header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');
echo qrCodeSvg(urlDaPorta($sala), $tamanho, 'QR code da porta: ' . $sala['nome']);
