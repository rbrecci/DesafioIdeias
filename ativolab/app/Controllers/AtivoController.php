<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Flash;
use App\Models\Ativo;
use App\Models\Auditoria;
use App\Models\Categoria;
use App\Models\Movimentacao;
use App\Models\Setor;
use RuntimeException;

final class AtivoController extends Controller
{
    public function index(): void
    {
        Auth::exigirLogin();

        $filtros = [
            'busca'     => get('busca'),
            'setor'     => get('setor'),
            'categoria' => get('categoria'),
            'status'    => get('status'),
        ];

        $pagina = max(1, (int) get('pagina', '1'));
        $lista  = Ativo::listar($filtros, $pagina);

        $this->view('ativos/index', [
            'titulo'     => 'Ativos',
            'lista'      => $lista,
            'filtros'    => $filtros,
            'setores'    => Setor::todos(),
            'categorias' => Categoria::todas(),
        ]);
    }

    public function ver(string $id): void
    {
        Auth::exigirLogin();

        $ativo = Ativo::porId((int) $id);

        if ($ativo === null) {
            $this->naoEncontrado();
        }

        $this->view('ativos/ver', [
            'titulo'     => $ativo['patrimonio'],
            'ativo'      => $ativo,
            'historico'  => Movimentacao::doAtivo((int) $ativo['id']),
            'auditoria'  => Auditoria::doRegistro('ativos', (int) $ativo['id'], 30),
            'setores'    => Setor::todos(),
        ]);
    }

    /**
     * Destino da leitura de QR: /p/{patrimonio}.
     * Redireciona para a ficha completa do ativo.
     */
    public function porPatrimonio(string $patrimonio): void
    {
        Auth::exigirLogin();

        $ativo = Ativo::porPatrimonio($patrimonio);

        if ($ativo === null) {
            Flash::add('erro', 'Nenhum ativo com o patrimonio "' . $patrimonio . '".');
            redirecionar('/ativos');
        }

        redirecionar('/ativos/' . $ativo['id']);
    }

    public function formularioCriar(): void
    {
        Auth::exigirPapel('admin', 'gestor');

        $this->view('ativos/formulario', [
            'titulo'     => 'Novo ativo',
            'ativo'      => null,
            'setores'    => Setor::todos(),
            'categorias' => Categoria::todas(),
        ]);
    }

    public function salvar(): void
    {
        Auth::exigirPapel('admin', 'gestor');
        Csrf::validar();

        $dados = $this->dadosDoFormulario();
        $erro  = $this->validar($dados, null);

        if ($erro !== null) {
            $this->voltarComErro($erro, '/ativos/novo');
        }

        $id = Ativo::criar($dados);
        $this->salvarFoto($id);

        Flash::add('sucesso', 'Ativo ' . $dados['patrimonio'] . ' cadastrado.');
        redirecionar('/ativos/' . $id);
    }

    public function formularioEditar(string $id): void
    {
        Auth::exigirPapel('admin', 'gestor');

        $ativo = Ativo::porId((int) $id);

        if ($ativo === null) {
            $this->naoEncontrado();
        }

        $this->view('ativos/formulario', [
            'titulo'     => 'Editar ' . $ativo['patrimonio'],
            'ativo'      => $ativo,
            'setores'    => Setor::todos(),
            'categorias' => Categoria::todas(),
        ]);
    }

    public function atualizar(string $id): void
    {
        Auth::exigirPapel('admin', 'gestor');
        Csrf::validar();

        $idInt = (int) $id;
        $ativo = Ativo::porId($idInt);

        if ($ativo === null) {
            $this->naoEncontrado();
        }

        $dados = $this->dadosDoFormulario();
        $erro  = $this->validar($dados, $idInt);

        if ($erro !== null) {
            $this->voltarComErro($erro, '/ativos/' . $idInt . '/editar');
        }

        Ativo::atualizar($idInt, $dados);
        $this->salvarFoto($idInt);

        Flash::add('sucesso', 'Ativo atualizado.');
        redirecionar('/ativos/' . $idInt);
    }

    public function excluir(string $id): void
    {
        Auth::exigirPapel('admin');
        Csrf::validar();

        $idInt = (int) $id;

        if (Ativo::porId($idInt) === null) {
            $this->naoEncontrado();
        }

        Ativo::excluir($idInt);

        Flash::add('sucesso', 'Ativo excluido.');
        redirecionar('/ativos');
    }

    /**
     * Movimenta o ativo entre setores. A regra de bloqueio por status
     * mora no model, nao aqui - qualquer caminho que chame mover() a respeita.
     */
    public function movimentar(string $id): void
    {
        Auth::exigirLogin();
        Csrf::validar();

        $idInt = (int) $id;

        try {
            Movimentacao::mover(
                $idInt,
                (int) post('setor_destino_id'),
                post('local_destino'),
                post('observacao')
            );
            Flash::add('sucesso', 'Movimentacao registrada.');
        } catch (RuntimeException $e) {
            Flash::add('erro', $e->getMessage());
        }

        redirecionar('/ativos/' . $idInt);
    }

    // ------------------------------------------------------------------ apoio

    /** @return array<string,mixed> */
    private function dadosDoFormulario(): array
    {
        $categoria = post('categoria_id');
        $setor     = post('setor_id');
        $valor     = str_replace(['.', ','], ['', '.'], post('valor'));
        $data      = post('data_aquisicao');
        $status    = post('status');

        return [
            'patrimonio'     => mb_strtoupper(post('patrimonio')),
            'nome'           => post('nome'),
            'categoria_id'   => ctype_digit($categoria) ? (int) $categoria : null,
            'setor_id'       => ctype_digit($setor) ? (int) $setor : null,
            'local_atual'    => post('local_atual') ?: null,
            'numero_serie'   => post('numero_serie') ?: null,
            'fabricante'     => post('fabricante') ?: null,
            'modelo'         => post('modelo') ?: null,
            'data_aquisicao' => $data !== '' ? $data : null,
            'valor'          => is_numeric($valor) ? (float) $valor : null,
            'status'         => in_array($status, Ativo::STATUS, true) ? $status : 'disponivel',
            'observacoes'    => post('observacoes') ?: null,
        ];
    }

    /** @param array<string,mixed> $d */
    private function validar(array $d, ?int $ignorarId): ?string
    {
        if ($d['patrimonio'] === '') {
            return 'Informe o numero de patrimonio.';
        }

        if (!preg_match('/^[A-Z0-9\-]{3,40}$/', (string) $d['patrimonio'])) {
            return 'O patrimonio aceita apenas letras, numeros e hifen (3 a 40 caracteres).';
        }

        if (Ativo::patrimonioEmUso((string) $d['patrimonio'], $ignorarId)) {
            return 'Ja existe um ativo com o patrimonio ' . $d['patrimonio'] . '.';
        }

        if (mb_strlen((string) $d['nome']) < 3) {
            return 'O nome do ativo precisa ter ao menos 3 caracteres.';
        }

        if ($d['data_aquisicao'] !== null && strtotime((string) $d['data_aquisicao']) === false) {
            return 'Data de aquisicao invalida.';
        }

        return null;
    }

    /**
     * Upload da foto do ativo.
     *
     * Em hospedagem compartilhada esse e um dos pontos mais explorados.
     * Tres defesas: tipo real via finfo (nao a extensao enviada),
     * nome de arquivo gerado por nos, e .htaccess que desliga o PHP na pasta.
     */
    private function salvarFoto(int $ativoId): void
    {
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        $arquivo = $_FILES['foto'];
        $max     = (int) \App\Core\Config::get('upload.max_bytes', 2097152);

        if ($arquivo['size'] > $max) {
            Flash::add('aviso', 'A foto passou do tamanho maximo e nao foi salva.');

            return;
        }

        $tiposOk = (array) \App\Core\Config::get('upload.tipos', []);
        $finfo   = new \finfo(FILEINFO_MIME_TYPE);
        $mime    = (string) $finfo->file($arquivo['tmp_name']);

        if (!isset($tiposOk[$mime])) {
            Flash::add('aviso', 'Formato de imagem nao aceito. Use JPG, PNG ou WEBP.');

            return;
        }

        $nome    = bin2hex(random_bytes(16)) . '.' . $tiposOk[$mime];
        $destino = dirname(__DIR__, 2) . '/storage/uploads/' . $nome;

        if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
            Flash::add('aviso', 'Nao foi possivel salvar a foto.');

            return;
        }

        $anterior = Ativo::porId($ativoId)['foto'] ?? null;
        Ativo::definirFoto($ativoId, $nome);

        if (is_string($anterior) && $anterior !== '') {
            @unlink(dirname(__DIR__, 2) . '/storage/uploads/' . basename($anterior));
        }
    }

    private function naoEncontrado(): never
    {
        http_response_code(404);
        (new ErroController())->naoEncontrado();
        exit;
    }
}
