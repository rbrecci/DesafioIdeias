<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Ativo;
use App\Models\Categoria;
use App\Models\Setor;

/**
 * Folha de etiquetas para impressao.
 *
 * O QR e desenhado no navegador, em SVG. Isso evita depender da extensao
 * imagick no servidor, que costuma faltar em hospedagem compartilhada,
 * e imprime mais nitido que PNG em etiqueta pequena.
 */
final class EtiquetaController extends Controller
{
    public function index(): void
    {
        Auth::exigirPapel('admin', 'gestor');

        $filtros = [
            'busca'     => get('busca'),
            'setor'     => get('setor'),
            'categoria' => get('categoria'),
            'status'    => get('status'),
        ];

        // Ate 200 etiquetas por folha: acima disso o navegador engasga.
        $lista = Ativo::listar($filtros, 1, 200);

        $this->view('ativos/etiquetas', [
            'titulo'     => 'Etiquetas',
            'ativos'     => $lista['itens'],
            'total'      => $lista['total'],
            'filtros'    => $filtros,
            'setores'    => Setor::todos(),
            'categorias' => Categoria::todas(),
        ], 'layout/impressao');
    }
}
