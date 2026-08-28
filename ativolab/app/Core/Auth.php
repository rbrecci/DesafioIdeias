<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\Auditoria;

/**
 * Autenticacao por sessao com password_hash / password_verify.
 */
final class Auth
{
    private const CHAVE = '_usuario_id';
    private static ?array $cache = null;

    public static function tentar(string $email, string $senha): bool
    {
        $u = Database::first(
            'SELECT * FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1',
            [$email]
        );

        // Compara mesmo sem usuario, para nao vazar quais e-mails existem
        // pela diferenca de tempo de resposta.
        $hash = $u['senha_hash'] ?? '$2y$12$pk.1qCz/392EhggvYWoN9.rsYl0OGQc.Svzv6pC7fUzkTqIXQhQT2';

        if (!password_verify($senha, $hash) || $u === null) {
            return false;
        }

        // Impede fixacao de sessao: o id muda no momento do login.
        session_regenerate_id(true);

        $_SESSION[self::CHAVE] = (int) $u['id'];
        self::$cache = $u;

        Database::run('UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?', [$u['id']]);
        Auditoria::registrar('usuarios', (int) $u['id'], 'login');

        return true;
    }

    public static function logado(): bool
    {
        return isset($_SESSION[self::CHAVE]);
    }

    public static function id(): ?int
    {
        return isset($_SESSION[self::CHAVE]) ? (int) $_SESSION[self::CHAVE] : null;
    }

    public static function usuario(): ?array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $id = self::id();
        if ($id === null) {
            return null;
        }

        self::$cache = Database::first(
            'SELECT u.*, s.nome AS setor_nome
               FROM usuarios u
          LEFT JOIN setores s ON s.id = u.setor_id
              WHERE u.id = ? AND u.ativo = 1
              LIMIT 1',
            [$id]
        );

        if (self::$cache === null) {
            self::sair();
        }

        return self::$cache;
    }

    public static function ehPapel(string ...$papeis): bool
    {
        $u = self::usuario();

        return $u !== null && in_array($u['papel'], $papeis, true);
    }

    public static function exigirLogin(): void
    {
        if (!self::logado() || self::usuario() === null) {
            Flash::add('erro', 'Faca login para continuar.');
            redirecionar('/login');
        }
    }

    public static function exigirPapel(string ...$papeis): void
    {
        self::exigirLogin();

        if (!self::ehPapel(...$papeis)) {
            http_response_code(403);
            exit('Voce nao tem permissao para esta acao.');
        }
    }

    public static function sair(): void
    {
        self::$cache = null;
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();
    }
}
