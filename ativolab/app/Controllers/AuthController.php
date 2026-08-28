<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;

final class AuthController extends Controller
{
    public function formulario(): void
    {
        if (Auth::logado()) {
            redirecionar('/');
        }

        $this->view('auth/login', ['titulo' => 'Entrar'], 'layout/simples');
    }

    public function entrar(): void
    {
        Csrf::validar();

        $email = post('email');
        $senha = $_POST['senha'] ?? '';

        if ($email === '' || $senha === '') {
            $this->voltarComErro('Preencha e-mail e senha.', '/login');
        }

        if (!Auth::tentar($email, (string) $senha)) {
            // Mensagem generica: nao revela se o e-mail existe.
            $this->voltarComErro('E-mail ou senha invalidos.', '/login');
        }

        Flash::add('sucesso', 'Bem-vindo de volta.');
        redirecionar('/');
    }

    public function sair(): void
    {
        Csrf::validar();
        Auth::sair();

        session_start();
        Flash::add('sucesso', 'Voce saiu com seguranca.');
        redirecionar('/login');
    }
}
