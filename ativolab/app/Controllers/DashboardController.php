<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Ativo;
use App\Models\Movimentacao;

final class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::exigirLogin();

        $this->view('dashboard', [
            'titulo'      => 'Painel',
            'porStatus'   => Ativo::contagemPorStatus(),
            'porSetor'    => Ativo::porSetor(),
            'totalAtivos' => (int) Database::valor('SELECT COUNT(*) FROM ativos'),
            'valorTotal'  => (float) (Database::valor('SELECT COALESCE(SUM(valor), 0) FROM ativos') ?? 0),
            'semSetor'    => (int) Database::valor('SELECT COUNT(*) FROM ativos WHERE setor_id IS NULL'),
            'recentes'    => Movimentacao::recentes(8),
        ]);
    }
}
