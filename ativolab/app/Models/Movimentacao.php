<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;
use RuntimeException;

/**
 * Historico de localizacao dos ativos.
 *
 * Tabela append-only: existe INSERT e SELECT, e nenhum UPDATE ou DELETE.
 * Corrigir um erro significa registrar uma nova movimentacao, nunca apagar
 * a anterior. E o que sustenta a rastreabilidade pedida na demanda 12569.
 */
final class Movimentacao
{
    /**
     * Movimenta um ativo entre setores e atualiza a posicao atual.
     *
     * @throws RuntimeException se o ativo estiver em status que bloqueia circulacao
     */
    public static function mover(
        int $ativoId,
        int $setorDestinoId,
        string $localDestino,
        string $observacao = ''
    ): void {
        $ativo = Ativo::porId($ativoId);

        if ($ativo === null) {
            throw new RuntimeException('Ativo nao encontrado.');
        }

        if (!Ativo::podeMovimentar($ativo)) {
            $rotulo = statusAtivo((string) $ativo['status'])['rotulo'];

            throw new RuntimeException(
                "Este ativo esta com status \"{$rotulo}\" e nao pode ser movimentado. "
                . 'Altere o status antes de mover.'
            );
        }

        if (Setor::porId($setorDestinoId) === null) {
            throw new RuntimeException('Setor de destino invalido.');
        }

        $origemId    = $ativo['setor_id'] === null ? null : (int) $ativo['setor_id'];
        $localOrigem = (string) ($ativo['local_atual'] ?? '');

        if ($origemId === $setorDestinoId && $localOrigem === $localDestino) {
            throw new RuntimeException('O ativo ja esta neste setor e local.');
        }

        Database::transacao(static function () use (
            $ativoId, $origemId, $setorDestinoId, $localOrigem, $localDestino, $observacao
        ): void {
            self::registrarBruta($ativoId, $origemId, $setorDestinoId, $localOrigem, $localDestino, $observacao);

            Database::run(
                'UPDATE ativos SET setor_id = ?, local_atual = ? WHERE id = ?',
                [$setorDestinoId, $localDestino, $ativoId]
            );

            Auditoria::registrar('ativos', $ativoId, 'movimentou', 'setor_id', $origemId, $setorDestinoId);
        });
    }

    /**
     * Grava a linha do historico sem validar nada.
     * Uso interno: cadastro inicial do ativo e a propria mover().
     */
    public static function registrarBruta(
        int $ativoId,
        ?int $origemId,
        ?int $destinoId,
        ?string $localOrigem,
        ?string $localDestino,
        string $observacao = ''
    ): void {
        Database::run(
            'INSERT INTO movimentacoes
                (ativo_id, setor_origem_id, setor_destino_id, local_origem, local_destino, responsavel_id, observacao)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $ativoId,
                $origemId,
                $destinoId,
                $localOrigem === '' ? null : $localOrigem,
                $localDestino === '' ? null : $localDestino,
                Auth::id(),
                $observacao === '' ? null : mb_substr($observacao, 0, 255),
            ]
        );
    }

    /** @return array<int,array<string,mixed>> */
    public static function doAtivo(int $ativoId): array
    {
        return Database::select(
            'SELECT m.*,
                    so.nome AS origem_nome,  so.sigla AS origem_sigla,
                    sd.nome AS destino_nome, sd.sigla AS destino_sigla,
                    u.nome  AS responsavel_nome
               FROM movimentacoes m
          LEFT JOIN setores  so ON so.id = m.setor_origem_id
          LEFT JOIN setores  sd ON sd.id = m.setor_destino_id
          LEFT JOIN usuarios u  ON u.id  = m.responsavel_id
              WHERE m.ativo_id = ?
           ORDER BY m.criado_em DESC, m.id DESC',
            [$ativoId]
        );
    }

    /** @return array<int,array<string,mixed>> */
    public static function recentes(int $limite = 10): array
    {
        return Database::select(
            'SELECT m.*, a.patrimonio, a.nome AS ativo_nome,
                    so.sigla AS origem_sigla, sd.sigla AS destino_sigla,
                    u.nome AS responsavel_nome
               FROM movimentacoes m
               JOIN ativos a    ON a.id  = m.ativo_id
          LEFT JOIN setores so  ON so.id = m.setor_origem_id
          LEFT JOIN setores sd  ON sd.id = m.setor_destino_id
          LEFT JOIN usuarios u  ON u.id  = m.responsavel_id
           ORDER BY m.criado_em DESC, m.id DESC
              LIMIT ' . (int) $limite
        );
    }
}
