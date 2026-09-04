# EXPLICAÇÃO: o projeto em 5 minutos

> Documento de orientação do grupo. Se você chegou agora, leia só isto.

| | |
|---|---|
| **Programa** | Desafio de Ideias, SAGA SENAI de Inovação |
| **Turma** | 2IEDS · (SP) 6.02 Escola SENAI Engº Octávio Marcondes Ferraz |
| **Product Owner** | Rafael Brecci de Souza |
| **Grupo** | Enzo Avanze, Felipe Bertaco, Vitor Canali, João Pedro, Nicolas Fernandes, Julia Possebon |
| **Demanda** | nº 12569, Gestão de Laboratórios e Ativos |
| **Empresa** | W K Soluções Industriais |
| **Prazo da demanda** | 05/04/2027 |

---

## 1. Em uma frase

Uma empresa industrial controla seus equipamentos em papel, ninguém sabe onde as coisas estão,
e nós estamos construindo o sistema web que resolve isso, com etiqueta QR colada em cada
equipamento e histórico que nunca é apagado.

---

## 2. O que é o Desafio de Ideias

Empresas reais publicam problemas reais numa plataforma do SENAI. Os grupos escolhem um,
aplicam **Design Thinking** para entender o problema a fundo e, no fim, apresentam a solução
num **pitch de 7 minutos** para uma banca, com mais 7 minutos de perguntas.

A banca avalia cinco critérios: **Inovação · Satisfação · Viabilidade · Escalabilidade · Apresentação**
(nota de 1 a 5 em cada).

> ⚠️ O material de capacitação (o PDF em `docs/`) usa como exemplo um desafio de
> **indústria têxtil / coleção para mulheres mastectomizadas**. **Aquilo não é o nosso desafio**:
> é só o caso de treinamento das aulas. O nosso é a demanda 12569.

---

## 3. Por que escolhemos esta demanda

Não foi por gosto. Levantamos as **34 demandas de TI abertas em São Paulo** e pontuamos cada uma
por facilidade de entrega e valor técnico (a base está em `docs/index.html`).

A 12569 ganhou por três motivos objetivos:

1. **Escopo delimitado**: inventário + ordem de serviço + estados. Sem zona cinzenta.
2. **Requisitos já escritos pela empresa**: elimina a fase mais cara de um projeto, que é a descoberta.
3. **Não exige hardware nem infraestrutura cara**: dá para *entregar funcionando*, não só prototipar.

Nota interna: facilidade **9/10**, interesse **5/10**. Sabemos que é terreno conhecido.
Trocamos brilho por certeza de entrega.

---

## 4. O problema, na prática

A W K Soluções Industriais registra equipamentos e ordens de serviço em **anotações físicas e
planilhas soltas**. As consequências que a própria empresa relatou:

- Ninguém responde rápido **onde está** um equipamento, **qual seu histórico** ou **quem é o responsável**
- Documentos de setores diferentes **se contradizem**
- Retrabalho, **compra duplicada** e equipamentos parados sem ninguém notar
- Impossível prestar contas para a diretoria

**A dor central é tempo de consulta.** Guarde essa frase, porque ela é o eixo de tudo que decidimos.

---

## 5. A solução: AtivoLab

Sistema web em PHP puro + MySQL. Duas decisões de projeto explicam todo o resto:

### 🔖 A etiqueta QR é o centro, não o cadastro

Todo sistema de patrimônio tem tela de cadastro. Isso não resolve a dor. **Apontar a câmera do
celular para o equipamento abre a ficha dele.** Movimentar entre setores é escanear e escolher o
destino. A consulta sai da mesa e vai para o pé da máquina.

### 📜 O histórico é append-only (só cresce, nunca apaga)

As tabelas de movimentação e auditoria **só recebem inserção**. Corrigir um erro significa
registrar um evento novo, jamais apagar o anterior. Assim a rastreabilidade vira uma
propriedade da estrutura de dados, e não uma promessa na documentação.

**Estado atual:** login com 3 papéis (admin/gestor/técnico), CRUD de ativos, etiquetas A4 com QR,
leitor pela câmera, movimentação com histórico, trilha de auditoria e painel. Testado ponta a
ponta contra um MySQL real. Detalhes técnicos e deploy estão no `README.md`.

---

## 6. Onde estamos no processo

O Design Thinking segue o **Duplo Diamante**: abre para explorar, fecha para decidir, duas vezes.

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
| Empatia | **Persona** | ✅ definida (abaixo) |
| Empatia | **Mapa de Empatia** | ✅ montado (abaixo) |
| Definição | **Matriz CSD** | ✅ 5 certezas, 5 suposições, 5 dúvidas |
| Ideação | **Matriz de Prioridade** | ✅ 12 ideias posicionadas |
| Empatia | Entrevista Exploratória com a empresa | ⏳ próximo, para resolver as 5 dúvidas |
| Prototipação | AtivoLab | 🔨 já em andamento |
| Pitch | 10 passos, 7 minutos | ⏳ |

A matriz de prioridade confirmou o rumo: as três ideias de **maior valor e menor
complexidade** (ficha pelo QR, cadastro com busca, folha de etiquetas A4) já estão prontas,
e das três de maior valor e maior complexidade só falta a **ordem de serviço**. O quadrante
descartado reúne aplicativo nativo, RFID e integração com ERP.

> Sim, o sistema começou antes da ideação formal. Isso é normal aqui: os requisitos vieram
> prontos da empresa. As ferramentas de empatia servem para **validar** se acertamos o alvo,
> e para construir o argumento do pitch.

---

## 7. A Persona: Márcio Tavares

Persona **não é** público-alvo. É uma pessoa específica e concreta, para o grupo parar de
projetar para "o usuário" abstrato.

Escolhemos o **técnico de campo** porque ele sustenta nossa aposta central: se ele não conseguir
usar o celular no pé da máquina, o projeto inteiro perde o sentido.

| | |
|---|---|
| **Nome** | Márcio Tavares |
| **Idade** | 34 anos |
| **Formação** | Técnico em Eletromecânica (SENAI), NR-10 e NR-12 em dia |
| **Profissão** | Técnico de manutenção industrial, atende laboratório e chão de fábrica |
| **Habilidades pessoais** | Resolve no improviso; conhece cada máquina pelo barulho; anota tudo numa caderneta no bolso; sem paciência com sistema lento; usa o celular o dia inteiro, mas de luva |
| **Onde vive** | Zona leste de São Paulo, 1h10 de trajeto até a empresa |
| **Onde estuda** | Atualização no SENAI aos sábados; o resto aprende em vídeo |

> 🗣️ *"Eu sei consertar a máquina. O que me atrasa é descobrir onde ela está e quem mexeu nela por último."*

**Personas secundárias:** Simone (coordenadora, precisa prestar contas) e Débora (auxiliar do
patrimônio, faz a conferência anual). Correspondem aos papéis *gestor* e *admin* do sistema.

---

## 8. Mapa de Empatia do Márcio

| Quadrante | Principais itens |
|---|---|
| **VÊ** | Papéis colados na parede e caderneta de bolso · planilhas diferentes em cada setor · etiquetas apagadas ou arrancadas · colega procurando a mesma ferramenta |
| **PENSA E SENTE** | *"Vou gastar mais tempo procurando do que consertando"* · receio de ser cobrado por equipamento que nem pegou · o serviço é técnico, mas o dia vira burocracia |
| **OUVE** | *"Você viu o multímetro?"* · *"Na planilha consta que está no laboratório"* · *"Isso não é comigo"* · cobrança de prazo do supervisor |
| **FALA E FAZ** | Anda pelo prédio procurando · pergunta ao colega em vez de consultar o documento · tira foto do patrimônio com o celular · avisa por WhatsApp |
| 🔴 **DORES** | **Tempo perdido localizando o ativo** · histórico inexistente, refaz diagnóstico já feito · documentos que se contradizem · sem responsável claro |
| 🟢 **NECESSIDADES** | Saber onde está e com quem, **na hora** · consultar o histórico **no pé da máquina** · registrar em segundos sem sair do fluxo · fonte única confiável |

**Repare na ligação:** "consultar no pé da máquina" → é isso que justifica o QR.
A necessidade veio primeiro; a solução é consequência. No pitch, conta-se nessa ordem.

---

## 9. O que precisa ser entregue

**Hoje:** Duplo Diamante evoluído · **Persona** e **Mapa de Empatia** preenchidos à mão nos
templates do SENAI · **foto do grupo** com os dois templates prontos.

**Documentação em padrão ABNT.**

Situação: o documento ABNT está em `docs/Documentacao-ABNT-Empatia.docx` e cobre as três
etapas percorridas, com quatro quadros de transcrição e cinco figuras (os quatro templates
preenchidos mais o registro da equipe, este último no Apêndice A). As fotografias entram pelo
gerador: basta colocar os arquivos em `docs/imagens/bruto/`, rodar `preparar.py` para recortar
e girar, e regerar o `.docx`.

---

## 10. Três armadilhas para evitar

**1. Não escreva a solução no campo NECESSIDADES.**
Escrever *"precisa de um app com QR"* é pular etapa. Necessidade é *"consultar o histórico no pé
da máquina"*. A regra vale também no pitch: o passo 2 manda descrever a jornada da persona
**sem mencionar a solução**.

**2. Quase tudo que temos ainda é hipótese.**
O Mapa foi montado a partir do que a empresa escreveu, não de conversa com um técnico real.
Na Matriz CSD, a maior parte disso vai para **Suposições**, não para Certezas. A entrevista
exploratória existe justamente para converter suposição em certeza.

**3. A banca não quer ouvir sobre tecnologia.**
Ninguém dá nota por "PHP com prepared statements". A pergunta é: *o problema do Márcio foi
resolvido?* Fale de tempo economizado e de informação confiável. A stack é detalhe.

---

## 11. Próximos passos

1. ~~Preencher os templates à mão e fotografar~~ ✅
2. ~~Matriz CSD~~ ✅ · ~~Matriz de Prioridade~~ ✅
3. Montar o roteiro da **entrevista exploratória** com a W K, partindo das 5 dúvidas da CSD
   (perguntas abertas, sem "sim/não")
4. Entrevistar e revisar a CSD com as respostas, movendo suposições para certezas
5. Implementar a **ordem de serviço**, único item de maior valor que falta
6. Treinar o pitch

As três suposições mais perigosas da CSD não dependem do grupo e derrubam o projeto se
estiverem erradas: a etiqueta aguentar óleo e calor, haver sinal no chão de fábrica, e o
celular ser liberado na área. Elas precisam virar pergunta na entrevista.

---

## Mapa dos arquivos

| Onde | O que é |
|---|---|
| `EXPLICACAO.md` | Este documento, a visão geral |
| `JUSTIFICATIVA.md` | Por que escolhemos a demanda 12569 |
| `README.md` | O sistema: stack, deploy, segurança, o que já funciona |
| `docs/Documentacao-ABNT-Empatia.docx` | **A entrega para o professor**, em norma ABNT |
| `docs/persona-mapa-empatia.md` | Persona e mapa de empatia para transcrever à mão nos templates |
| `docs/gerar-abnt.js` | Gerador do `.docx`, para regerar quando o texto mudar |
| `docs/index.html` | As 34 demandas de TI analisadas, com ranking |
| `docs/propostas.html` | Propostas técnicas para as 3 demandas finalistas |
| `docs/Entregavel.txt` | O que precisa ser entregue |
| `docs/*.jpeg` | Os templates em branco (Persona e Mapa de Empatia) |
| `docs/CAPACITAÇÃO...pdf` | Material oficial do workshop |
| `ativolab/` | O sistema AtivoLab |

---

## Glossário

- **Design Thinking**: método de projeto que começa entendendo a pessoa, não a tecnologia.
- **Duplo Diamante**: as 4 fases: Empatia → Definição → Ideação → Prototipação.
- **Persona**: pessoa fictícia, mas concreta, que representa o usuário real.
- **Mapa de Empatia**: o que essa pessoa vê, pensa, ouve e faz, e o que a machuca.
- **Matriz CSD**: separa o que sabemos (Certezas), o que achamos (Suposições) e o que ignoramos (Dúvidas).
- **Pitch**: apresentação curta de 7 minutos para convencer a banca.
- **Ativo**: qualquer equipamento que a empresa precise controlar.
- **Append-only**: registro que só cresce: nada é editado, nada é apagado.
