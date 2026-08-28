<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class ErroController extends Controller
{
    public function naoEncontrado(): void
    {
        http_response_code(404);
        $this->view('erros/404', [], 'layout/simples');
    }
}
