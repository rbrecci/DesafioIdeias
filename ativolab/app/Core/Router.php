<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Roteador minimo.
 *
 * Aceita parametros no formato {nome}, que chegam como argumentos
 * do metodo do controller na ordem em que aparecem na rota.
 */
final class Router
{
    /** @var array<string,array<int,array{padrao:string,acao:array}>> */
    private array $rotas = ['GET' => [], 'POST' => []];

    public function get(string $padrao, array $acao): void
    {
        $this->rotas['GET'][] = ['padrao' => $padrao, 'acao' => $acao];
    }

    public function post(string $padrao, array $acao): void
    {
        $this->rotas['POST'][] = ['padrao' => $padrao, 'acao' => $acao];
    }

    public function despachar(string $metodo, string $uri): void
    {
        $metodo = strtoupper($metodo);
        $caminho = '/' . trim(parse_url($uri, PHP_URL_PATH) ?? '/', '/');

        $base = (string) Config::get('base_url', '');
        if ($base !== '' && str_starts_with($caminho, $base)) {
            $caminho = '/' . trim(substr($caminho, strlen($base)), '/');
        }

        foreach ($this->rotas[$metodo] ?? [] as $rota) {
            $regex = '#^' . preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $rota['padrao']) . '$#';

            if (preg_match($regex, $caminho, $m)) {
                array_shift($m);
                [$classe, $acao] = $rota['acao'];
                $controller = new $classe();
                $controller->$acao(...array_map('urldecode', $m));

                return;
            }
        }

        http_response_code(404);
        (new \App\Controllers\ErroController())->naoEncontrado();
    }
}
