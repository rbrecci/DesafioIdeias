<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Categoria
{
    /** @return array<int,array<string,mixed>> */
    public static function todas(): array
    {
        return Database::select(
            'SELECT c.*, (SELECT COUNT(*) FROM ativos a WHERE a.categoria_id = c.id) AS total_ativos
               FROM categorias c
           ORDER BY c.nome'
        );
    }

    public static function porId(int $id): ?array
    {
        return Database::first('SELECT * FROM categorias WHERE id = ?', [$id]);
    }

    public static function criar(string $nome, string $prefixo): int
    {
        $id = Database::inserir('INSERT INTO categorias (nome, prefixo) VALUES (?, ?)', [$nome, $prefixo]);
        Auditoria::registrar('categorias', $id, 'criou', null, null, $nome);

        return $id;
    }

    public static function atualizar(int $id, string $nome, string $prefixo): void
    {
        $antes = self::porId($id);
        Database::run('UPDATE categorias SET nome = ?, prefixo = ? WHERE id = ?', [$nome, $prefixo, $id]);

        if ($antes !== null) {
            Auditoria::diferenca('categorias', $id, $antes, ['nome' => $nome, 'prefixo' => $prefixo]);
        }
    }

    public static function excluir(int $id): bool
    {
        $emUso = (int) Database::valor('SELECT COUNT(*) FROM ativos WHERE categoria_id = ?', [$id]);

        if ($emUso > 0) {
            return false;
        }

        Database::run('DELETE FROM categorias WHERE id = ?', [$id]);
        Auditoria::registrar('categorias', $id, 'excluiu');

        return true;
    }

    public static function nomeEmUso(string $nome, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categorias WHERE nome = ?';
        $p   = [$nome];

        if ($ignorarId !== null) {
            $sql .= ' AND id <> ?';
            $p[]  = $ignorarId;
        }

        return (int) Database::valor($sql, $p) > 0;
    }
}
