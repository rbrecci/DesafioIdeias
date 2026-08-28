<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;

/**
 * Trilha append-only. Nunca sofre UPDATE nem DELETE - e essa propriedade,
 * e nao uma promessa na documentacao, que garante a rastreabilidade pedida
 * pela demanda 12569.
 */
final class Auditoria
{
    public static function registrar(
        string $entidade,
        int $entidadeId,
        string $acao,
        ?string $campo = null,
        mixed $anterior = null,
        mixed $novo = null
    ): void {
        Database::run(
            'INSERT INTO auditoria (entidade, entidade_id, acao, campo, valor_anterior, valor_novo, usuario_id, ip)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $entidade,
                $entidadeId,
                $acao,
                $campo,
                $anterior === null ? null : (string) $anterior,
                $novo === null ? null : (string) $novo,
                Auth::id(),
                substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
            ]
        );
    }

    /**
     * Compara dois estados de um registro e grava uma linha por campo alterado.
     *
     * @param array<string,mixed> $antes
     * @param array<string,mixed> $depois
     */
    public static function diferenca(string $entidade, int $id, array $antes, array $depois): void
    {
        foreach ($depois as $campo => $valorNovo) {
            $valorAntigo = $antes[$campo] ?? null;

            if ((string) $valorAntigo !== (string) $valorNovo) {
                self::registrar($entidade, $id, 'alterou', $campo, $valorAntigo, $valorNovo);
            }
        }
    }

    /** @return array<int,array<string,mixed>> */
    public static function doRegistro(string $entidade, int $id, int $limite = 50): array
    {
        return Database::select(
            'SELECT a.*, u.nome AS usuario_nome
               FROM auditoria a
          LEFT JOIN usuarios u ON u.id = a.usuario_id
              WHERE a.entidade = ? AND a.entidade_id = ?
           ORDER BY a.criado_em DESC, a.id DESC
              LIMIT ' . (int) $limite,
            [$entidade, $id]
        );
    }
}
