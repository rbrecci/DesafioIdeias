<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Guarda o array de configuracao carregado do config.php.
 */
final class Config
{
    private static array $itens = [];

    public static function carregar(array $itens): void
    {
        self::$itens = $itens;
    }

    /**
     * Le uma chave com notacao de ponto: Config::get('db.host').
     */
    public static function get(string $chave, mixed $padrao = null): mixed
    {
        $valor = self::$itens;

        foreach (explode('.', $chave) as $parte) {
            if (!is_array($valor) || !array_key_exists($parte, $valor)) {
                return $padrao;
            }
            $valor = $valor[$parte];
        }

        return $valor;
    }
}
