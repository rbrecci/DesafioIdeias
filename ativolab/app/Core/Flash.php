<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Mensagens de uma requisicao so, guardadas na sessao.
 * Tipos usados: sucesso, erro, aviso.
 */
final class Flash
{
    private const CHAVE = '_flash';

    public static function add(string $tipo, string $mensagem): void
    {
        $_SESSION[self::CHAVE][] = ['tipo' => $tipo, 'mensagem' => $mensagem];
    }

    /** @return array<int,array{tipo:string,mensagem:string}> */
    public static function consumir(): array
    {
        $itens = $_SESSION[self::CHAVE] ?? [];
        unset($_SESSION[self::CHAVE]);

        return $itens;
    }

    /**
     * Guarda os dados do formulario para repopular os campos apos um erro.
     */
    public static function guardarEntrada(array $dados): void
    {
        unset($dados['_token'], $dados['senha']);
        $_SESSION['_old'] = $dados;
    }

    public static function velho(string $campo, string $padrao = ''): string
    {
        return (string) ($_SESSION['_old'][$campo] ?? $padrao);
    }

    public static function limparEntrada(): void
    {
        unset($_SESSION['_old']);
    }
}
