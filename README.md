# Desafio de Ideias: demanda nº 12569

Documentação do projeto do grupo **GR4** no **Desafio de Ideias / SAGA SENAI de Inovação**,
sobre a demanda nº 12569, **Gestão de Laboratórios e Ativos**, da **W K Soluções Industriais**.

| | |
|---|---|
| Curso | Técnico em Desenvolvimento de Sistemas |
| Unidade Curricular | Projetos de Software (45 horas) |
| Turma | 2IEDS · (SP) 6.02 Escola SENAI Engº Octávio Marcondes Ferraz |
| Grupo | GR4 |
| Product Owner | Rafael Brecci de Souza |
| Scrum Master | Enzo Avanze |
| Developers | Felipe Bertaco Cruz, João Pedro Tomazini Rodrigues, Julia Possebon Leme, Nicolas Fernandes Santos, Rafael Brecci de Souza, Vitor Matheus Canali Pereira |
| Docente responsável | Paulo Cesar de Camargo |
| Prazo da demanda | 05/04/2027 |

> **Esta branch guarda só a documentação:** o processo de Design Thinking, a solução e a
> prototipagem. O código do protótipo vive na branch `ativolab`.

---

## O problema

A W K Soluções Industriais controla equipamentos e ordens de serviço por anotações físicas e
planilhas soltas. Ninguém responde rápido onde está um equipamento, qual seu histórico de
manutenção ou qual o setor responsável, e documentos de setores diferentes se contradizem.
O resultado é retrabalho, compra duplicada, equipamentos parados e impossibilidade de prestar
contas.

**A dor central é tempo de consulta.** Ela é o eixo de todas as decisões de projeto.

---

## A solução: AtivoLab

Sistema web com etiqueta QR colada em cada equipamento. Duas decisões explicam o resto:

1. **A etiqueta QR é o centro, não o cadastro.** Apontar a câmera do celular para o equipamento
   abre a ficha dele. Movimentar entre setores é escanear e escolher o destino. A consulta sai
   da mesa e vai para o pé da máquina, que é onde a persona primária trabalha.
2. **O histórico é append-only.** Movimentação e auditoria só recebem inserção. Corrigir um erro
   significa registrar um evento novo, nunca apagar o anterior. A rastreabilidade pedida pela
   empresa vira propriedade da estrutura de dados, não uma promessa na documentação.

A proposta de valor, para o pitch: **saber onde está, com quem está e o que já aconteceu, na hora,
sem sair de perto da máquina.**

---

## Onde estamos no Duplo Diamante

```
   EMPATIA         DEFINIÇÃO        IDEAÇÃO       PROTOTIPAÇÃO
     ◇ ─────────────── ◆ ─────────────── ◇ ─────────────── ◆
  entender o        definir o        gerar muitas      construir e
   usuário           problema           ideias            testar
                                          ▲
                                    ESTAMOS AQUI
```

| Etapa | Ferramenta | Situação |
|---|---|---|
| Empatia | Persona | ✅ definida |
| Empatia | Mapa de Empatia | ✅ montado |
| Empatia | Entrevista Exploratória | ⏳ roteiro, termo e ata prontos, aguardando a W K |
| Definição | Matriz CSD | ✅ 5 certezas, 5 suposições, 5 dúvidas (a revisar após a entrevista) |
| Definição | Registro de riscos | ✅ 10 riscos, com resposta planejada para cada um |
| Ideação | Brainstorming e diagrama de afinidade | ✅ 12 ideias em 4 grupos de afinidade |
| Ideação | Matriz de Prioridade | ✅ 12 ideias posicionadas |
| Ideação | Decisão sobre as ideias | ✅ por consenso sobre a matriz, com justificativa por ideia |
| Prototipação | AtivoLab (branch `ativolab`) | 🔨 em andamento |
| Pitch | 10 passos, 7 minutos | ⏳ |

---

## A prototipagem

O protótipo é funcional, não de papel: PHP puro e MySQL, sem framework. O que já roda é o
quadrante de maior valor e menor complexidade da matriz de prioridade.

| Entregue | Situação |
|---|---|
| Login com três papéis (admin, gestor, técnico) | ✅ |
| Cadastro, busca e consulta de ativos | ✅ |
| Folha de etiquetas A4 com QR | ✅ |
| Leitura do QR pela câmera do celular | ✅ |
| Movimentação entre setores com histórico | ✅ |
| Trilha de auditoria campo a campo | ✅ |
| Painel com totais e distribuição | ✅ |
| Ordens de serviço | ⏳ depende da entrevista |

**Falta o teste que só existe no mundo físico:** imprimir uma etiqueta, colar num equipamento e
ler com um celular de verdade, conferindo contraste, tamanho mínimo e resistência a óleo e calor.

O código, o esquema do banco, as instruções de deploy e o relato de verificação estão na branch
`ativolab`:

```bash
git switch ativolab
```

---

## Mapa dos documentos

| Onde | O que é |
|---|---|
| `EXPLICACAO.md` | Visão geral do projeto em 5 minutos, para quem chega agora |
| `JUSTIFICATIVA.md` | Por que o grupo escolheu a demanda 12569 (entrega do P.O.) |
| `docs/Documentacao-ABNT-Empatia.docx` | A entrega ao professor em norma ABNT: Empatia, Definição e Ideação |
| `docs/03 - Template_Plano_de_Ensino_GR4_Preenchido_v2.docx` | Plano de Ensino da UC, com as 4 etapas de 15 aulas e os critérios de avaliação |
| `docs/06 - Roteiro_Entrevista_GR4_Essencial.pdf` | Roteiro da entrevista exploratória com a W K: 29 perguntas em 60 minutos |
| `docs/07 - Roteiro_Entrevista_GR4_Respostas_Esperadas.pdf` | O mesmo roteiro com as respostas que o grupo espera, para comparar depois |
| `docs/gerar-entrevista.py`, `docs/gerar-entrevista-respostas.py` | Geradores dos dois PDFs acima |
| `docs/08 - Ata_e_Autorizacao_Entrevista_GR4.pdf` | Termo de autorização de registro e ata da entrevista, para levar impressos |
| `docs/09 - Rubrica_e_Autoavaliacao_GR4.pdf` | Rubrica da Etapa 1, inventário de evidências e fichas de autoavaliação |
| `docs/gerar-ata.py`, `docs/gerar-rubrica.py` | Geradores dos dois PDFs acima |
| `docs/persona-mapa-empatia.md` | Persona e mapa de empatia, para transcrição nos templates impressos |
| `docs/gerar-abnt.js` | Gerador do `.docx` acima, para regerar quando o texto mudar |
| `docs/imagens/` | Fotografias dos templates preenchidos, com o `preparar.py` que recorta e gira |
| `docs/PersonaModelo.jpeg`, `docs/MapaEmpatia.jpeg` | Os templates em branco do SENAI |
| `docs/Entregavel.txt` | O enunciado da entrega da etapa de Empatia |
| `docs/CAPACITAÇÃO...pdf` | Material oficial do workshop, com os critérios da banca e os 10 passos do pitch |

> ⚠️ O material de capacitação usa como exemplo um desafio de indústria têxtil, uma coleção para
> mulheres mastectomizadas. **Aquele não é o nosso desafio:** é o caso de treinamento das aulas.

---

## Como a banca avalia

Nota de 1 a 5 em cinco critérios, no pitch de 7 minutos (mais 7 minutos de perguntas):

**Inovação · Satisfação · Viabilidade · Escalabilidade · Apresentação**

A banca não dá nota por tecnologia. A pergunta é se o problema do técnico foi resolvido, medido
em tempo economizado e informação confiável.
