<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Flash;

/**
 * Escapa para HTML. Toda saida de dado vindo do banco passa por aqui.
 */
function e(mixed $valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Monta uma URL interna respeitando o base_url.
 */
function url(string $caminho = '/'): string
{
    $base = rtrim((string) Config::get('base_url', ''), '/');

    return $base . '/' . ltrim($caminho, '/');
}

function asset(string $caminho): string
{
    return url('assets/' . ltrim($caminho, '/'));
}

function redirecionar(string $caminho): never
{
    header('Location: ' . url($caminho));
    exit;
}

function velho(string $campo, string $padrao = ''): string
{
    return e(Flash::velho($campo, $padrao));
}

/**
 * Le um campo do POST como string ja aparada.
 */
function post(string $campo, string $padrao = ''): string
{
    $v = $_POST[$campo] ?? $padrao;

    return is_string($v) ? trim($v) : $padrao;
}

function get(string $campo, string $padrao = ''): string
{
    $v = $_GET[$campo] ?? $padrao;

    return is_string($v) ? trim($v) : $padrao;
}

/**
 * Formata data do MySQL para o padrao brasileiro.
 */
function dataBr(?string $data, bool $comHora = false): string
{
    if ($data === null || $data === '' || str_starts_with($data, '0000')) {
        return '-';
    }

    $ts = strtotime($data);

    return $ts === false ? '-' : date($comHora ? 'd/m/Y H:i' : 'd/m/Y', $ts);
}

function moedaBr(mixed $valor): string
{
    if ($valor === null || $valor === '') {
        return '-';
    }

    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

/**
 * Rotulo e cor Bootstrap de cada status de ativo.
 *
 * @return array{rotulo:string,cor:string}
 */
function statusAtivo(string $status): array
{
    return [
        'disponivel'    => ['rotulo' => 'Disponivel',     'cor' => 'success'],
        'em_uso'        => ['rotulo' => 'Em uso',         'cor' => 'primary'],
        'em_manutencao' => ['rotulo' => 'Em manutencao',  'cor' => 'warning'],
        'emprestado'    => ['rotulo' => 'Emprestado',     'cor' => 'info'],
        'baixado'       => ['rotulo' => 'Baixado',        'cor' => 'secondary'],
    ][$status] ?? ['rotulo' => $status, 'cor' => 'secondary'];
}
