<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Protecao CSRF.
 *
 * O Laravel fazia isso sozinho; em PHP puro e responsabilidade nossa.
 * Regra do projeto: TODO formulario POST chama Csrf::campo() no HTML,
 * e TODA rota POST chama Csrf::validar() antes de tocar no banco.
 */
final class Csrf
{
    private const CHAVE = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::CHAVE])) {
            $_SESSION[self::CHAVE] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::CHAVE];
    }

    public static function campo(): string
    {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function valido(?string $enviado): bool
    {
        if ($enviado === null || $enviado === '' || empty($_SESSION[self::CHAVE])) {
            return false;
        }

        return hash_equals($_SESSION[self::CHAVE], $enviado);
    }

    /**
     * Aborta a requisicao se o token nao conferir.
     */
    public static function validar(): void
    {
        $enviado = $_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

        if (!self::valido(is_string($enviado) ? $enviado : null)) {
            http_response_code(419);
            exit('Sessao expirada ou requisicao invalida. Volte, atualize a pagina e tente de novo.');
        }
    }
}
