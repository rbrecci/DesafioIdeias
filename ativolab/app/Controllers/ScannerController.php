<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

final class ScannerController extends Controller
{
    /**
     * Pagina do leitor de QR. A decodificacao acontece no navegador;
     * ao ler, o JS redireciona para /p/{patrimonio}.
     */
    public function index(): void
    {
        Auth::exigirLogin();

        $this->view('ativos/scanner', ['titulo' => 'Ler etiqueta']);
    }
}
