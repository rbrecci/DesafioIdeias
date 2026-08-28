<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;
use RuntimeException;

/**
 * Conexao unica com o MySQL.
 *
 * Todo acesso ao banco passa por prepared statements. Nenhum metodo desta
 * classe concatena valor de usuario dentro do SQL - a unica defesa real
 * contra SQL injection.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function conn(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = (string) Config::get('db.host');
        $nome = (string) Config::get('db.nome');
        $dsn  = "mysql:host={$host};dbname={$nome};charset=utf8mb4";

        try {
            self::$pdo = new PDO(
                $dsn,
                (string) Config::get('db.user'),
                (string) Config::get('db.senha'),
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_STRINGIFY_FETCHES  => false,
                ]
            );
        } catch (\PDOException $e) {
            if (Config::get('debug')) {
                throw $e;
            }
            throw new RuntimeException('Nao foi possivel conectar ao banco de dados.');
        }

        return self::$pdo;
    }

    /** @return array<int,array<string,mixed>> */
    public static function select(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public static function first(string $sql, array $params = []): ?array
    {
        $linha = self::run($sql, $params)->fetch();

        return $linha === false ? null : $linha;
    }

    public static function valor(string $sql, array $params = []): mixed
    {
        $v = self::run($sql, $params)->fetchColumn();

        return $v === false ? null : $v;
    }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::conn()->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    public static function inserir(string $sql, array $params = []): int
    {
        self::run($sql, $params);

        return (int) self::conn()->lastInsertId();
    }

    public static function transacao(callable $fn): mixed
    {
        $pdo = self::conn();
        $pdo->beginTransaction();

        try {
            $r = $fn();
            $pdo->commit();

            return $r;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
