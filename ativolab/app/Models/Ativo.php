<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\Database;

final class Ativo
{
    public const STATUS = ['disponivel', 'em_uso', 'em_manutencao', 'emprestado', 'baixado'];

    /** Status em que o ativo nao pode circular entre setores. */
    public const STATUS_BLOQUEIA_MOVIMENTACAO = ['em_manutencao', 'baixado'];

    private const CAMPOS_EDITAVEIS = [
        'patrimonio', 'nome', 'categoria_id', 'setor_id', 'local_atual', 'numero_serie',
        'fabricante', 'modelo', 'data_aquisicao', 'valor', 'status', 'observacoes',
    ];

    /**
     * Lista com filtros e paginacao.
     *
     * @param array{busca?:string,setor?:string,categoria?:string,status?:string} $filtros
     * @return array{itens:array<int,array<string,mixed>>,total:int,paginas:int,pagina:int}
     */
    public static function listar(array $filtros = [], int $pagina = 1, int $porPagina = 15): array
    {
        $where  = [];
        $params = [];

        $busca = trim((string) ($filtros['busca'] ?? ''));
        if ($busca !== '') {
            $where[] = '(a.nome LIKE ? OR a.patrimonio LIKE ? OR a.numero_serie LIKE ? OR a.modelo LIKE ?)';
            $termo   = '%' . $busca . '%';
            array_push($params, $termo, $termo, $termo, $termo);
        }

        foreach (['setor' => 'a.setor_id', 'categoria' => 'a.categoria_id'] as $chave => $coluna) {
            $v = (string) ($filtros[$chave] ?? '');
            if ($v !== '' && ctype_digit($v)) {
                $where[]  = $coluna . ' = ?';
                $params[] = (int) $v;
            }
        }

        $status = (string) ($filtros['status'] ?? '');
        if ($status !== '' && in_array($status, self::STATUS, true)) {
            $where[]  = 'a.status = ?';
            $params[] = $status;
        }

        $sqlWhere = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        $total     = (int) Database::valor('SELECT COUNT(*) FROM ativos a' . $sqlWhere, $params);
        $porPagina = max(1, $porPagina);
        $paginas   = max(1, (int) ceil($total / $porPagina));
        $pagina    = min(max(1, $pagina), $paginas);
        $offset    = ($pagina - 1) * $porPagina;

        // LIMIT e OFFSET sao inteiros ja validados, nunca texto vindo do usuario.
        $itens = Database::select(
            'SELECT a.*, c.nome AS categoria_nome, s.nome AS setor_nome, s.sigla AS setor_sigla
               FROM ativos a
          LEFT JOIN categorias c ON c.id = a.categoria_id
          LEFT JOIN setores    s ON s.id = a.setor_id'
            . $sqlWhere .
            ' ORDER BY a.patrimonio
              LIMIT ' . $porPagina . ' OFFSET ' . $offset,
            $params
        );

        return ['itens' => $itens, 'total' => $total, 'paginas' => $paginas, 'pagina' => $pagina];
    }

    public static function porId(int $id): ?array
    {
        return Database::first(
            'SELECT a.*, c.nome AS categoria_nome, s.nome AS setor_nome, s.sigla AS setor_sigla,
                    u.nome AS criador_nome
               FROM ativos a
          LEFT JOIN categorias c ON c.id = a.categoria_id
          LEFT JOIN setores    s ON s.id = a.setor_id
          LEFT JOIN usuarios   u ON u.id = a.criado_por
              WHERE a.id = ?
              LIMIT 1',
            [$id]
        );
    }

    /** Usado pela leitura de QR: a etiqueta carrega o patrimonio. */
    public static function porPatrimonio(string $patrimonio): ?array
    {
        $linha = Database::first('SELECT id FROM ativos WHERE patrimonio = ? LIMIT 1', [$patrimonio]);

        return $linha === null ? null : self::porId((int) $linha['id']);
    }

    public static function patrimonioEmUso(string $patrimonio, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM ativos WHERE patrimonio = ?';
        $p   = [$patrimonio];

        if ($ignorarId !== null) {
            $sql .= ' AND id <> ?';
            $p[]  = $ignorarId;
        }

        return (int) Database::valor($sql, $p) > 0;
    }

    /**
     * Sugere o proximo patrimonio da categoria, no formato PREFIXO-000001.
     */
    public static function proximoPatrimonio(int $categoriaId): string
    {
        $cat = Categoria::porId($categoriaId);
        $pre = $cat === null ? 'ATV' : strtoupper((string) $cat['prefixo']);

        $ultimo = (string) (Database::valor(
            'SELECT patrimonio FROM ativos WHERE patrimonio LIKE ? ORDER BY patrimonio DESC LIMIT 1',
            [$pre . '-%']
        ) ?? '');

        $n = 0;
        if ($ultimo !== '' && preg_match('/-(\d+)$/', $ultimo, $m)) {
            $n = (int) $m[1];
        }

        return $pre . '-' . str_pad((string) ($n + 1), 6, '0', STR_PAD_LEFT);
    }

    /** @param array<string,mixed> $dados */
    public static function criar(array $dados): int
    {
        $dados = self::apenasEditaveis($dados);

        $colunas = array_keys($dados);
        $marcas  = implode(', ', array_fill(0, count($colunas), '?'));

        $sql = 'INSERT INTO ativos (' . implode(', ', $colunas) . ', criado_por) VALUES (' . $marcas . ', ?)';

        $id = Database::inserir($sql, [...array_values($dados), Auth::id()]);

        Auditoria::registrar('ativos', $id, 'criou', null, null, $dados['patrimonio'] ?? '');

        // Movimentacao de abertura: o ativo nasce em algum lugar.
        if (!empty($dados['setor_id'])) {
            Movimentacao::registrarBruta(
                $id,
                null,
                (int) $dados['setor_id'],
                null,
                (string) ($dados['local_atual'] ?? ''),
                'Cadastro inicial do ativo'
            );
        }

        return $id;
    }

    /** @param array<string,mixed> $dados */
    public static function atualizar(int $id, array $dados): void
    {
        $antes = self::porId($id);
        if ($antes === null) {
            return;
        }

        $dados = self::apenasEditaveis($dados);
        if ($dados === []) {
            return;
        }

        $sets = implode(' = ?, ', array_keys($dados)) . ' = ?';
        Database::run('UPDATE ativos SET ' . $sets . ' WHERE id = ?', [...array_values($dados), $id]);

        Auditoria::diferenca('ativos', $id, $antes, $dados);
    }

    public static function definirFoto(int $id, string $arquivo): void
    {
        Database::run('UPDATE ativos SET foto = ? WHERE id = ?', [$arquivo, $id]);
        Auditoria::registrar('ativos', $id, 'alterou', 'foto', null, $arquivo);
    }

    public static function excluir(int $id): void
    {
        $a = self::porId($id);
        Database::run('DELETE FROM ativos WHERE id = ?', [$id]);
        Auditoria::registrar('ativos', $id, 'excluiu', null, $a['patrimonio'] ?? null, null);
    }

    /**
     * Regra de negocio da demanda: ativo em manutencao ou baixado nao circula.
     */
    public static function podeMovimentar(array $ativo): bool
    {
        return !in_array($ativo['status'], self::STATUS_BLOQUEIA_MOVIMENTACAO, true);
    }

    /** @return array<string,int> */
    public static function contagemPorStatus(): array
    {
        $linhas = Database::select('SELECT status, COUNT(*) AS n FROM ativos GROUP BY status');
        $saida  = array_fill_keys(self::STATUS, 0);

        foreach ($linhas as $l) {
            $saida[$l['status']] = (int) $l['n'];
        }

        return $saida;
    }

    /** @return array<int,array<string,mixed>> */
    public static function porSetor(): array
    {
        return Database::select(
            'SELECT s.nome, s.sigla, COUNT(a.id) AS n
               FROM setores s
          LEFT JOIN ativos a ON a.setor_id = s.id
           GROUP BY s.id, s.nome, s.sigla
           ORDER BY n DESC, s.nome'
        );
    }

    /**
     * Descarta qualquer chave que nao seja coluna editavel.
     * Evita que um campo extra no POST vire coluna no UPDATE.
     *
     * @param array<string,mixed> $dados
     * @return array<string,mixed>
     */
    private static function apenasEditaveis(array $dados): array
    {
        return array_intersect_key($dados, array_flip(self::CAMPOS_EDITAVEIS));
    }
}
