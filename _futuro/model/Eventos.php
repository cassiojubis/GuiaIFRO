<?php
// Funções do calendário acadêmico (tabela eventos). Saíram do model/Repositorio.php.

/** Eventos publicados a partir de uma data (calendário), em ordem. */
function listarEventos(?string $aPartirDe = null, int $limite = 200): array
{
    return consultar(
        'SELECT id, titulo, descricao, inicio, fim, tipo, periodo_letivo FROM eventos
          WHERE publicado = 1 AND COALESCE(fim, inicio) >= ?
          ORDER BY inicio, ordem, id LIMIT ' . max(1, $limite),
        [$aPartirDe ?? '1900-01-01']
    );
}

function buscarEvento(int $id): ?array
{
    return consultarUm('SELECT * FROM eventos WHERE id = ? AND publicado = 1', [$id]);
}

/** Eventos que acontecem num dia. */
function eventosDoDia(string $data): array
{
    return consultar(
        'SELECT id, titulo, tipo FROM eventos WHERE publicado = 1 AND inicio <= ? AND COALESCE(fim, inicio) >= ? ORDER BY ordem, id',
        [$data, $data]
    );
}

function proximoEvento(string $depoisDe): ?array
{
    return consultarUm(
        'SELECT id, titulo, tipo, inicio, fim FROM eventos WHERE publicado = 1 AND inicio > ? ORDER BY inicio, ordem LIMIT 1',
        [$depoisDe]
    );
}
