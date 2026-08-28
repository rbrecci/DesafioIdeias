# DesafioIdeias

Projeto Integrador a partir das demandas do **SAGA SENAI de Inovação** (Grand Prix SENAI de Inovação).

O repositório tem duas partes: a **pesquisa** que selecionou a demanda e o **sistema** que a resolve.

```
DesafioIdeias/
├── docs/            pesquisa: rankings das demandas e propostas de solução
└── ativolab/        AtivoLab — o sistema, em PHP puro + MySQL
```

---

## docs — a pesquisa

Páginas estáticas, abrem com duplo clique no navegador.

| Arquivo | O que é |
|---|---|
| `docs/index.html` | As 34 demandas de TI em SP, com dois rankings: mais fáceis e mais interessantes |
| `docs/propostas.html` | Propostas técnicas para as três demandas mais fáceis |
| `docs/demandas.json` | A base bruta das 34 demandas |

Fonte: plataforma SAGA SENAI de Inovação, filtro Área = Tecnologia da Informação e Estado = SP, coletado em 28/08/2026.

---

## AtivoLab — o sistema

Implementa a **demanda 12569 — Gestão de Laboratórios e Ativos** (W K Soluções Industriais,
Escola SENAI Engº Octávio Marcondes Ferraz).

> O controle de equipamentos e ordens de serviço vive em anotações físicas, e ninguém consegue
> responder rápido onde está um ativo, qual seu histórico e qual o setor responsável.

Duas decisões definem o sistema:

1. **A etiqueta QR é o centro, não o cadastro.** A dor relatada é tempo de consulta. Apontar a
   câmera do celular para o equipamento abre a ficha dele. Movimentar entre setores é escanear
   e escolher o destino.
2. **O histórico é append-only.** As tabelas `movimentacoes` e `auditoria` só recebem `INSERT`.
   Corrigir um erro significa registrar um novo evento, nunca apagar o anterior. Rastreabilidade
   vira propriedade da estrutura de dados, não uma promessa na documentação.

### Stack

PHP 8.1+ sem framework, MySQL 8, Bootstrap 5, JavaScript puro. Sem Composer: nada para instalar
no servidor, o que torna o deploy em hospedagem compartilhada uma questão de subir arquivos.

Bootstrap e as bibliotecas de QR vêm de CDN. Para uso offline, baixe e troque os links por
arquivos locais em `assets/`.

### O que já funciona

- Login com sessão, `password_hash`/`password_verify` e três papéis: admin, gestor, técnico
- CRUD de ativos com busca, filtros por setor/categoria/status e paginação
- Upload de foto do ativo com validação de tipo real
- Cadastro de setores e categorias, com bloqueio de exclusão quando há ativos vinculados
- Numeração automática de patrimônio por categoria (`NB-000001`, `PROJ-000002`)
- Folha de etiquetas A4 com QR em SVG, pronta para impressão em papel adesivo
- Leitor de QR pela câmera do celular, que abre a ficha do ativo
- Movimentação entre setores com histórico completo
- Trilha de auditoria campo a campo
- Painel com totais por status, distribuição por setor e movimentações recentes

### Regra de negócio principal

Ativo com status **Em manutenção** ou **Baixado** não circula entre setores. A regra mora em
`Movimentacao::mover()`, não na tela — qualquer caminho que chame o método a respeita.

---

## Deploy no InfinityFree

### Passo 0 — SSL, antes de tudo

A câmera do navegador só funciona em contexto seguro. Sem certificado, o leitor de QR não
funciona no celular e o sistema perde a razão de ser. Instale o SSL pelo painel, aguarde a
emissão e **confirme que o site abre em `https://` antes de continuar**.

### Passo 1 — Banco de dados

No painel, em *MySQL Databases*, crie o banco e anote host, nome, usuário e senha.

### Passo 2 — Tabelas

Abra o **phpMyAdmin** pelo painel, selecione o banco na coluna da esquerda e use a aba
*Importar*, nesta ordem:

1. `ativolab/database/schema.sql` — cria as 6 tabelas
2. `ativolab/database/seed.sql` — cria o usuário admin e dados de exemplo

A ordem importa: o `seed` depende das tabelas que o `schema` cria.

Confira ao final: devem existir `setores`, `categorias`, `usuarios`, `ativos`,
`movimentacoes` e `auditoria`, com 10 ativos cadastrados.

### Passo 3 — Configuração

Copie `ativolab/config/config.example.php` para `ativolab/config/config.php` e preencha com os
dados do passo 1. Mantenha `debug` em `false` e `base_url` em `''`.

> **Este arquivo não está no repositório.** Ele tem a senha do banco e está no `.gitignore`.
> Quem clonar o projeto precisa criá-lo a partir do `.example`.

### Passo 4 — Enviar os arquivos

Por FTP (FileZilla, com as credenciais em *FTP Accounts* no painel), envie **o conteúdo de
`ativolab/`** para dentro de `htdocs/` — não a pasta `ativolab` inteira. O `index.php` precisa
ficar na raiz do site.

Apague o `index2.html` que o InfinityFree deixa em `htdocs/`, senão ele disputa com o nosso
`index.php`.

> **Cuidado com os arquivos ocultos.** São 6 arquivos `.htaccess` e sem eles nada funciona:
> o roteamento quebra e as pastas internas ficam expostas. Muitos clientes de FTP escondem
> arquivos que começam com ponto. No FileZilla: *Servidor → Forçar exibição de arquivos ocultos*.

Ao final, `htdocs/` deve ter esta cara:

```
htdocs/
├── index.php
├── .htaccess
├── servidor-local.php   (inofensivo em produção; pode apagar)
├── app/        + .htaccess
├── assets/
├── config/     + .htaccess  (com o config.php do passo 3)
├── database/   + .htaccess
└── storage/    + .htaccess
    └── uploads/ + .htaccess
```

### Passo 5 — Testar

Abra o site. Deve aparecer a tela de login. Entre com `admin@ativolab.local` e `ativolab123`.

Checklist rápido:

- [ ] Painel mostra 10 ativos e R$ 28.150,00
- [ ] Lista de ativos abre e o filtro por status funciona
- [ ] Ficha de um ativo mostra o QR desenhado
- [ ] `/etiquetas` monta a folha A4
- [ ] `/scanner` abre a câmera no celular — **o teste que importa**

### Passo 6 — Trocar a senha do admin

O `seed.sql` traz uma senha conhecida, publicada neste README. Gere um hash novo e atualize o
registro pelo phpMyAdmin antes de colocar qualquer dado real:

```bash
php -r "echo password_hash('SUA_NOVA_SENHA', PASSWORD_BCRYPT, ['cost' => 12]), PHP_EOL;"
```

```sql
UPDATE usuarios SET senha_hash = 'COLE_O_HASH_AQUI' WHERE email = 'admin@ativolab.local';
```

### Se algo der errado

Abra o `config.php` no servidor, mude `debug` para `true`, recarregue a página e leia a
mensagem de erro real. **Volte para `false` assim que resolver** — com `true` o sistema mostra
caminhos de arquivo e detalhes do banco para qualquer visitante.

| Sintoma | Causa provável |
|---|---|
| Erro 500 em tudo | Faltou enviar algum `.htaccess`, ou o `config.php` não existe |
| Só a home abre, o resto dá 404 | O `.htaccess` da raiz não subiu |
| "Configuracao ausente" | Falta `config/config.php` |
| "Nao foi possivel conectar" | Dados do banco errados no `config.php` |
| Câmera não abre | Site sendo acessado por `http://` em vez de `https://` |
| Fotos não carregam | O `.htaccess` de `storage/uploads/` não subiu |

### Troque a senha do admin

O `seed.sql` traz uma senha conhecida, publicada aqui neste README. Gere um hash novo e atualize
o registro antes de colocar qualquer dado real no sistema:

```bash
php -r "echo password_hash('SUA_NOVA_SENHA', PASSWORD_BCRYPT, ['cost' => 12]), PHP_EOL;"
```

```sql
UPDATE usuarios SET senha_hash = 'COLE_O_HASH_AQUI' WHERE email = 'admin@ativolab.local';
```

---

## Rodando localmente

```bash
cd ativolab
php -S localhost:8000 servidor-local.php
```

`servidor-local.php` faz o papel do `.htaccess` no servidor embutido do PHP; na hospedagem ele
não tem efeito nenhum.

---

## Estrutura

```
ativolab/
├── index.php              front controller: config, sessão, rotas, despacho
├── .htaccess              rewrite para o front controller
├── servidor-local.php     roteador do servidor embutido (só desenvolvimento)
├── app/
│   ├── Core/              Config, Database, Auth, Csrf, Flash, Router, Controller, helpers
│   ├── Controllers/       Auth, Dashboard, Ativo, Cadastro, Etiqueta, Scanner, Erro
│   ├── Models/            Ativo, Setor, Categoria, Movimentacao, Auditoria
│   └── Views/             layout/, auth/, ativos/, cadastros/, erros/
├── assets/                css e js públicos
├── config/                config.example.php (o config.php real não é versionado)
├── database/              schema.sql e seed.sql
└── storage/uploads/       fotos dos ativos
```

Tudo fora de `assets/` e `storage/uploads/` está protegido por `.htaccess` com `Require all denied`.

---

## Segurança

O que o Laravel entregaria pronto e aqui está escrito à mão:

| Defesa | Onde |
|---|---|
| SQL injection | `Database` só usa prepared statements; nenhum valor de usuário é concatenado no SQL |
| CSRF | `Csrf::campo()` em todo formulário, `Csrf::validar()` em toda rota POST |
| Fixação de sessão | `session_regenerate_id(true)` no login |
| Cookie de sessão | `HttpOnly`, `SameSite=Lax`, e `Secure` quando há HTTPS |
| XSS | `e()` em toda saída de dado vindo do banco |
| Upload malicioso | Tipo real via `finfo`, nome de arquivo gerado pelo sistema, PHP desligado na pasta por `.htaccess` |
| Enumeração de usuário | Login compara hash mesmo sem usuário e devolve sempre a mesma mensagem |
| Mass assignment | `Ativo::apenasEditaveis()` descarta qualquer campo fora da lista permitida |

---

## Estado da verificação

Todos os 35 arquivos PHP passam em `php -l`. O sistema foi exercitado end-to-end contra um
MySQL 8 real, com o `schema.sql` e o `seed.sql` deste repositório:

| Fluxo | Resultado |
|---|---|
| Login com a senha do seed | 302 para o painel |
| Rota protegida sem sessão | 302 para `/login` |
| CSRF: POST sem token / com token | 419 / passa |
| Painel com dados reais | 10 ativos, R$ 28.150,00, 1 em manutenção |
| Lista, busca e filtro por status | 10 linhas, busca e filtro retornam o esperado |
| Criar ativo | criado, redireciona para a ficha |
| Patrimônio duplicado | recusado com mensagem |
| Movimentação de ativo disponível | setor e local atualizados, evento no histórico |
| Movimentação de ativo em manutenção | form escondido na tela **e** POST forçado recusado pelo model |
| Folha de etiquetas | 11 etiquetas na grade A4 |
| Excluir setor com ativos vinculados | bloqueado com mensagem |
| Trilha de auditoria | eventos de login, criação e movimentação gravados |
| Roteamento | 13 rotas, incluindo `/ativos/novo` sem colidir com `/ativos/{id}` |

**Ainda não verificado:** o teste que só existe no mundo físico — imprimir uma etiqueta e ler
com a câmera de um celular de verdade, a 15 cm, conferindo contraste e tamanho mínimo do código.
E o comportamento no ambiente do InfinityFree em si, que só o primeiro deploy revela.

---

## Próximos passos

- Ordens de serviço: abertura, atribuição, ciclo de vida, anexos (sprint 3 da proposta)
- Importação da base atual da empresa via CSV
- Relatórios exportáveis em CSV e PDF
- Cadastro de usuários pela interface (hoje só pelo `seed.sql`)
