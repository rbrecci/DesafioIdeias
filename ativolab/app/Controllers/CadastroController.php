<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Categoria;
use App\Models\Setor;

/**
 * Setores e categorias sao dois cadastros de mesma forma - nome, um codigo,
 * e a checagem de nao poder excluir enquanto houver ativo vinculado.
 * Ficam no mesmo controller para nao duplicar a mesma logica duas vezes.
 */
final class CadastroController extends Controller
{
    public function setores(): void
    {
        Auth::exigirPapel('admin', 'gestor');

        $this->view('cadastros/setores', [
            'titulo'  => 'Setores',
            'setores' => Setor::todos(),
        ]);
    }

    public function salvarSetor(): void
    {
        Auth::exigirPapel('admin', 'gestor');
        Csrf::validar();

        $id    = (int) post('id');
        $nome  = post('nome');
        $sigla = mb_strtoupper(post('sigla'));

        if (mb_strlen($nome) < 3) {
            $this->voltarComErro('O nome do setor precisa ter ao menos 3 caracteres.', '/setores');
        }

        if (!preg_match('/^[A-Z0-9\-]{2,12}$/', $sigla)) {
            $this->voltarComErro('A sigla aceita letras, numeros e hifen (2 a 12 caracteres).', '/setores');
        }

        if (Setor::siglaEmUso($sigla, $id > 0 ? $id : null)) {
            $this->voltarComErro('Ja existe um setor com a sigla ' . $sigla . '.', '/setores');
        }

        if ($id > 0) {
            Setor::atualizar($id, $nome, $sigla);
            Flash::add('sucesso', 'Setor atualizado.');
        } else {
            Setor::criar($nome, $sigla);
            Flash::add('sucesso', 'Setor criado.');
        }

        redirecionar('/setores');
    }

    public function excluirSetor(string $id): void
    {
        Auth::exigirPapel('admin');
        Csrf::validar();

        if (!Setor::excluir((int) $id)) {
            Flash::add('erro', 'Este setor tem ativos vinculados. Mova os ativos antes de excluir.');
        } else {
            Flash::add('sucesso', 'Setor excluido.');
        }

        redirecionar('/setores');
    }

    public function categorias(): void
    {
        Auth::exigirPapel('admin', 'gestor');

        $this->view('cadastros/categorias', [
            'titulo'     => 'Categorias',
            'categorias' => Categoria::todas(),
        ]);
    }

    public function salvarCategoria(): void
    {
        Auth::exigirPapel('admin', 'gestor');
        Csrf::validar();

        $id      = (int) post('id');
        $nome    = post('nome');
        $prefixo = mb_strtoupper(post('prefixo'));

        if (mb_strlen($nome) < 3) {
            $this->voltarComErro('O nome da categoria precisa ter ao menos 3 caracteres.', '/categorias');
        }

        if (!preg_match('/^[A-Z0-9]{2,8}$/', $prefixo)) {
            $this->voltarComErro('O prefixo aceita letras e numeros (2 a 8 caracteres).', '/categorias');
        }

        if (Categoria::nomeEmUso($nome, $id > 0 ? $id : null)) {
            $this->voltarComErro('Ja existe uma categoria com esse nome.', '/categorias');
        }

        if ($id > 0) {
            Categoria::atualizar($id, $nome, $prefixo);
            Flash::add('sucesso', 'Categoria atualizada.');
        } else {
            Categoria::criar($nome, $prefixo);
            Flash::add('sucesso', 'Categoria criada.');
        }

        redirecionar('/categorias');
    }

    public function excluirCategoria(string $id): void
    {
        Auth::exigirPapel('admin');
        Csrf::validar();

        if (!Categoria::excluir((int) $id)) {
            Flash::add('erro', 'Esta categoria tem ativos vinculados. Reclassifique os ativos antes de excluir.');
        } else {
            Flash::add('sucesso', 'Categoria excluida.');
        }

        redirecionar('/categorias');
    }
}
