<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Setor
{
    /** @return array<int,array<string,mixed>> */
    public static function todos(): array
    {
        return Database::select(
            'SELECT s.*, (SELECT COUNT(*) FROM ativos a WHERE a.setor_id = s.id) AS total_ativos
               FROM setores s
           ORDER BY s.nome'
        );
    }

    public static function porId(int $id): ?array
    {
        return Database::first('SELECT * FROM setores WHERE id = ?', [$id]);
    }

    public static function criar(string $nome, string $sigla): int
    {
        $id = Database::inserir('INSERT INTO setores (nome, sigla) VALUES (?, ?)', [$nome, $sigla]);
        Auditoria::registrar('setores', $id, 'criou', null, null, $nome);

        return $id;
    }

    public static function atualizar(int $id, string $nome, string $sigla): void
    {
        $antes = self::porId($id);
        Database::run('UPDATE setores SET nome = ?, sigla = ? WHERE id = ?', [$nome, $sigla, $id]);

        if ($antes !== null) {
            Auditoria::diferenca('setores', $id, $antes, ['nome' => $nome, 'sigla' => $sigla]);
        }
    }

    public static function excluir(int $id): bool
    {
        $emUso = (int) Database::valor('SELECT COUNT(*) FROM ativos WHERE setor_id = ?', [$id]);

        if ($emUso > 0) {
            return false;
        }

        Database::run('DELETE FROM setores WHERE id = ?', [$id]);
        Auditoria::registrar('setores', $id, 'excluiu');

        return true;
    }

    public static function siglaEmUso(string $sigla, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM setores WHERE sigla = ?';
        $p   = [$sigla];

        if ($ignorarId !== null) {
            $sql .= ' AND id <> ?';
            $p[]  = $ignorarId;
        }

        return (int) Database::valor($sql, $p) > 0;
    }
}
