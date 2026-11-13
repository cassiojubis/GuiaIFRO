<?php
require_once __DIR__ . '/BaseAdmin.php';
exigirAdminGeral();

$tituloPaginaAdmin = 'Configurações';
$paginaAtualAdmin = 'configuracoes';

// [rótulo, tipo, ajuda]. Tudo fica na tabela configuracoes.
$camposConfiguracao = [
    'Mensagens e avisos' => [
        'contato_email_destino' => ['E-mail que recebe aviso de mensagem nova', 'email', 'Vazio = as mensagens ficam só no admin. O servidor precisa ter envio de e-mail configurado (mail() do PHP).'],
        'whatsapp_canal' => ['Link do canal de WhatsApp do campus', 'url', 'Aparece como "Receber avisos no WhatsApp". Vazio = o botão não aparece.'],
    ],
    'Para quem vem de outro país (estrangeiros.php)' => [
        'estrangeiros_documentos_pt' => ['Documentos exigidos — em português (um por linha)', 'texto_longo', 'Só o que o CRA confirmar. Vazio = a página avisa que ainda está sendo confirmado.'],
        'estrangeiros_documentos_es' => ['Documentos exigidos — em espanhol (um por linha)', 'texto_longo', 'Mesma lista, em espanhol.'],
        'estrangeiros_chegada_pt' => ['Como chegar ao campus — em português', 'texto_longo', 'Ex.: ônibus, ponte, ponto de referência. Vazio = não aparece.'],
        'estrangeiros_chegada_es' => ['Como chegar ao campus — em espanhol', 'texto_longo', 'Mesmo texto, em espanhol.'],
    ],
    'Emergência e mapa' => [
        'telefone_enfermaria' => ['Telefone da Enfermaria (botão Emergência)', 'telefone', 'Vazio = não aparece. Use só número confirmado.'],
        'telefone_portaria' => ['Telefone da portaria (botão Emergência)', 'telefone', 'Vazio = não aparece.'],
        'velocidade_caminhada' => ['Velocidade média a pé (metros por segundo)', 'decimal', 'Usada no tempo a pé do mapa. 1,2 m/s é o passo normal de um adulto.'],
    ],
];

if (ehPost()) {
    exigirCsrf();
    $erros = [];
    $valores = [];
    foreach ($camposConfiguracao as $campos) {
        foreach ($campos as $chave => [$rotulo, $tipo]) {
            if ($tipo === 'sim_nao') {
                $valores[$chave] = lerCheckbox($chave) ? '1' : '0';
                continue;
            }
            $valor = lerCampo($chave, mb_strtolower($rotulo), $tipo === 'texto_longo' ? 3000 : 255, false, $erros);
            if ($valor !== '') {
                if ($tipo === 'email' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                    $erros[$chave] = 'E-mail inválido.';
                } elseif ($tipo === 'url' && !preg_match('#^https://#i', $valor)) {
                    $erros[$chave] = 'Use o endereço completo, começando com https://';
                } elseif ($tipo === 'numero' && (!ctype_digit($valor) || (int) $valor < 7 || (int) $valor > 730)) {
                    $erros[$chave] = 'Use um número de dias entre 7 e 730.';
                } elseif ($tipo === 'decimal') {
                    $valor = str_replace(',', '.', $valor);
                    if (!is_numeric($valor) || (float) $valor < 0.3 || (float) $valor > 3) {
                        $erros[$chave] = 'Use um número entre 0,3 e 3.';
                    }
                } elseif ($tipo === 'telefone' && !preg_match('/^[0-9()+\-\s]{3,20}$/', $valor)) {
                    $erros[$chave] = 'Telefone inválido.';
                }
            }
            $valores[$chave] = $valor;
        }
    }
    if ($erros) {
        definirFlash('erros_config', $erros);
        definirFlash('valores_config', $valores);
        voltarComAviso('configuracoes.php', 'Confira os campos marcados.', 'erro');
    }
    $antes = array_intersect_key(listarConfiguracoes(), $valores);
    salvarConfiguracoes($valores);
    registrarAuditoria($usuarioAdmin, 'editar', 'configuracao', 'geral', $antes, $valores, 'Configurações do sistema atualizadas');
    voltarComAviso('configuracoes.php', 'Configurações salvas.');
}

$errosConfig = pegarFlash('erros_config', []);
$valoresConfig = pegarFlash('valores_config') ?? listarConfiguracoes();

require __DIR__ . '/../../view/admin/configuracoes.php';
