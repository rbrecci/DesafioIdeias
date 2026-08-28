<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * Renderiza uma view dentro do layout.
     *
     * @param array<string,mixed> $dados
     */
    protected function view(string $nome, array $dados = [], ?string $layout = 'layout/app'): void
    {
        $arquivo = dirname(__DIR__) . '/Views/' . $nome . '.php';

        if (!is_file($arquivo)) {
            throw new \RuntimeException("View nao encontrada: {$nome}");
        }

        extract($dados, EXTR_SKIP);

        ob_start();
        require $arquivo;
        $conteudo = ob_get_clean();

        Flash::limparEntrada();

        if ($layout === null) {
            echo $conteudo;

            return;
        }

        require dirname(__DIR__) . '/Views/' . $layout . '.php';
    }

    /** @param array<string,mixed> $dados */
    protected function json(array $dados, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Volta para o formulario mantendo o que o usuario ja tinha digitado.
     */
    protected function voltarComErro(string $mensagem, string $caminho): never
    {
        Flash::add('erro', $mensagem);
        Flash::guardarEntrada($_POST);
        redirecionar($caminho);
    }
}
