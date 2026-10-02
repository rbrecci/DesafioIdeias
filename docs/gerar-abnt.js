// Gera a documentacao ABNT do Desafio de Ideias, demanda 12569.
// Uso: node gerar-abnt.js <saida.docx> [pasta-de-imagens]
// As imagens ausentes viram caixa cinza, para que o documento sempre compile.
const fs = require("fs");
const path = require("path");
const {
  Document, Packer, Paragraph, TextRun, AlignmentType, HeadingLevel,
  Header, PageNumber, Table, TableRow, TableCell, WidthType,
  ShadingType, BorderStyle, PositionalTab, PositionalTabAlignment,
  PositionalTabLeader, VerticalAlign, ImageRun,
} = require("docx");

const FONT = "Arial";
const CM = 566.93;                  // 1 cm em DXA
const PX = 96 / 2.54;               // 1 cm em pixels, base das imagens do docx
const TEXT_W = Math.round(16 * CM); // 21cm menos as margens de 3cm e 2cm

const DIR_IMG = process.argv[3] || "imagens";
let DIMS = {};
try {
  DIMS = JSON.parse(fs.readFileSync(path.join(DIR_IMG, "dimensoes.json"), "utf8"));
} catch (e) {
  DIMS = {};
}

// ---------- helpers ----------

// corpo do texto: Arial 12, entrelinha 1,5, recuo de primeira linha 1,25cm, justificado
const p = (text, opts = {}) =>
  new Paragraph({
    alignment: opts.align || AlignmentType.JUSTIFIED,
    spacing: { line: opts.line || 360, after: opts.after === undefined ? 0 : opts.after },
    indent: opts.noIndent ? undefined : { firstLine: Math.round(1.25 * CM) },
    children: [new TextRun({ text, font: FONT, size: opts.size || 24, bold: !!opts.bold })],
  });

// linha centrada, sem recuo
const center = (text, opts = {}) =>
  new Paragraph({
    alignment: AlignmentType.CENTER,
    pageBreakBefore: !!opts.pb,
    spacing: { line: opts.line || 360, after: opts.after === undefined ? 0 : opts.after },
    children: [new TextRun({ text, font: FONT, size: opts.size || 24, bold: !!opts.bold })],
  });

const blank = (n = 1) =>
  Array.from({ length: n }, () =>
    new Paragraph({ spacing: { line: 360 }, children: [new TextRun({ text: "", font: FONT, size: 24 })] }));

// linha da folha de rosto, recuada 8cm conforme a norma
const notaRosto = (text, opts = {}) =>
  new Paragraph({
    alignment: AlignmentType.JUSTIFIED,
    spacing: { line: 240 },
    indent: { left: Math.round(8 * CM) },
    children: [new TextRun({ text, font: FONT, size: 24, highlight: opts.marcar ? "yellow" : undefined })],
  });

// secao primaria: nova pagina, caixa alta, negrito, cor preta
const h1 = (text, opts = {}) =>
  new Paragraph({
    heading: HeadingLevel.HEADING_1,
    alignment: opts.align || AlignmentType.LEFT,
    spacing: { line: 360, after: 360 },
    pageBreakBefore: true,
    children: [new TextRun({ text, font: FONT, size: 24, bold: true, allCaps: true, color: "000000" })],
  });

// secao secundaria: negrito, sem caixa alta
const h2 = (text) =>
  new Paragraph({
    heading: HeadingLevel.HEADING_2,
    spacing: { line: 360, before: 360, after: 240 },
    children: [new TextRun({ text, font: FONT, size: 24, bold: true, color: "000000" })],
  });

// legenda ABNT: acima do elemento, Arial 10, simples, presa ao que vem depois
const legenda = (text) =>
  new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { line: 240, before: 240, after: 60 },
    keepNext: true,
    children: [new TextRun({ text, font: FONT, size: 20 })],
  });

// fonte ABNT: abaixo do elemento, Arial 10, simples
const fonte = (text) =>
  new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { line: 240, before: 60, after: 360 },
    children: [new TextRun({ text, font: FONT, size: 20 })],
  });

// caixa cinza tracejada, usada enquanto a fotografia nao chegou
const placeholder = (titulo, instrucao, alturaCm) => {
  const borda = { style: BorderStyle.DASHED, size: 6, color: "808080" };
  const nada = { style: BorderStyle.NONE, size: 0, color: "FFFFFF" };
  return new Table({
    columnWidths: [TEXT_W],
    borders: { top: borda, bottom: borda, left: borda, right: borda, insideHorizontal: nada, insideVertical: nada },
    rows: [new TableRow({
      cantSplit: true,
      height: { value: Math.round(alturaCm * CM), rule: "atLeast" },
      children: [new TableCell({
        width: { size: TEXT_W, type: WidthType.DXA },
        shading: { type: ShadingType.CLEAR, fill: "EFEFEF", color: "auto" },
        verticalAlign: VerticalAlign.CENTER,
        margins: { top: 200, bottom: 200, left: 200, right: 200 },
        children: [
          new Paragraph({
            alignment: AlignmentType.CENTER, spacing: { line: 240, after: 120 },
            children: [new TextRun({ text: titulo, font: FONT, size: 24, bold: true, color: "595959" })],
          }),
          new Paragraph({
            alignment: AlignmentType.CENTER, spacing: { line: 240 },
            children: [new TextRun({ text: instrucao, font: FONT, size: 20, color: "808080", italics: true })],
          }),
        ],
      })],
    })],
  });
};

// Insere a imagem se existir; senao deixa a caixa cinza.
// Ajusta para caber em 16cm de largura e na altura maxima informada.
const figura = (arquivo, rotuloVazio, instrucaoVazio, alturaMaxCm) => {
  const caminho = path.join(DIR_IMG, arquivo);
  const dim = DIMS[arquivo];
  if (!fs.existsSync(caminho) || !dim) {
    return placeholder(rotuloVazio, instrucaoVazio, alturaMaxCm);
  }
  const proporcao = dim.h / dim.w;
  let larguraCm = 16;
  let alturaCm = larguraCm * proporcao;
  if (alturaCm > alturaMaxCm) {
    alturaCm = alturaMaxCm;
    larguraCm = alturaCm / proporcao;
  }
  return new Paragraph({
    alignment: AlignmentType.CENTER,
    spacing: { line: 240 },
    children: [new ImageRun({
      type: arquivo.toLowerCase().endsWith(".png") ? "png" : "jpg",
      data: fs.readFileSync(caminho),
      transformation: { width: Math.round(larguraCm * PX), height: Math.round(alturaCm * PX) },
    })],
  });
};

// quadro ABNT com cabecalho e linhas
const quadro = (colWidths, header, linhas) =>
  new Table({
    columnWidths: colWidths,
    rows: [
      new TableRow({
        tableHeader: true,
        cantSplit: true,
        children: header.map((t, i) => new TableCell({
          width: { size: colWidths[i], type: WidthType.DXA },
          shading: { type: ShadingType.CLEAR, fill: "D9D9D9", color: "auto" },
          margins: { top: 80, bottom: 80, left: 120, right: 120 },
          children: [new Paragraph({
            spacing: { line: 240 },
            children: [new TextRun({ text: t, font: FONT, size: 20, bold: true })],
          })],
        })),
      }),
      ...linhas.map((linha) => new TableRow({
        cantSplit: true,
        children: linha.map((t, i) => new TableCell({
          width: { size: colWidths[i], type: WidthType.DXA },
          margins: { top: 80, bottom: 80, left: 120, right: 120 },
          children: [new Paragraph({
            spacing: { line: 240 },
            alignment: AlignmentType.JUSTIFIED,
            children: [new TextRun({ text: t, font: FONT, size: 20 })],
          })],
        })),
      })),
    ],
  });

// linha de sumario ou de lista, com pontilhado ate o numero da pagina
const sum = (texto, pagina, opts = {}) =>
  new Paragraph({
    spacing: { line: 360 },
    children: [
      new TextRun({ text: texto, font: FONT, size: 24, bold: !!opts.bold }),
      new TextRun({
        font: FONT, size: 24,
        children: [
          new PositionalTab({
            alignment: PositionalTabAlignment.RIGHT,
            relativeTo: "margin",
            leader: PositionalTabLeader.DOT,
          }),
          pagina,
        ],
      }),
    ],
  });

// citacao longa ABNT: recuo 4cm, Arial 10, simples
const citacao = (text) =>
  new Paragraph({
    alignment: AlignmentType.JUSTIFIED,
    spacing: { line: 240, before: 240, after: 240 },
    indent: { left: Math.round(4 * CM) },
    children: [new TextRun({ text, font: FONT, size: 20 })],
  });

// referencia ABNT: esquerda, entrelinha simples
const ref = (runs) =>
  new Paragraph({
    alignment: AlignmentType.LEFT,
    spacing: { line: 240, after: 240 },
    children: runs.map((r) => new TextRun({ text: r.t, font: FONT, size: 24, bold: !!r.b })),
  });

const alinea = (text) => p(text, { noIndent: true, after: 120 });

const INTEGRANTES = [
  "ENZO AVANZE",
  "FELIPE BERTACO CRUZ",
  "JOÃO PEDRO TOMAZINI RODRIGUES",
  "JULIA POSSEBON LEME",
  "NICOLAS FERNANDES SANTOS",
  "RAFAEL BRECCI DE SOUZA",
  "VITOR MATHEUS CANALI PEREIRA",
];

const TITULO = "GESTÃO DE LABORATÓRIOS E ATIVOS: APLICAÇÃO DO DESIGN THINKING DA EMPATIA À IDEAÇÃO";

// ---------- elementos pre-textuais ----------

const preTextual = [
  // CAPA
  center("SERVIÇO NACIONAL DE APRENDIZAGEM INDUSTRIAL", { bold: true }),
  center("ESCOLA SENAI “ENGº OCTÁVIO MARCONDES FERRAZ” (SP 6.02)", { bold: true }),
  center("CURSO TÉCNICO EM DESENVOLVIMENTO DE SISTEMAS", { bold: true, after: 240 }),
  ...blank(4),
  ...INTEGRANTES.map((n) => center(n)),
  ...blank(4),
  center(TITULO, { bold: true }),
  ...blank(8),
  center("SÃO PAULO"),
  center("2026"),

  // FOLHA DE ROSTO
  ...INTEGRANTES.map((n, i) => center(n, { pb: i === 0 })),
  ...blank(5),
  center(TITULO, { bold: true }),
  ...blank(4),
  notaRosto("Documentação parcial apresentada ao Curso Técnico em Desenvolvimento de Sistemas da Escola SENAI “Engº Octávio Marcondes Ferraz” como requisito do Projeto Integrador, no âmbito do Desafio de Ideias / SAGA SENAI de Inovação."),
  ...blank(1),
  notaRosto("Orientadores: Prof. Paulo Camargo e Prof. Raul Porto Lopes"),
  notaRosto("Product Owner: Rafael Brecci de Souza"),
  notaRosto("Turma: 2IEDS"),
  ...blank(4),
  center("SÃO PAULO"),
  center("2026"),

  // RESUMO
  center("RESUMO", { bold: true, after: 360, pb: true }),
  p("Este documento registra a aplicação do Design Thinking à demanda nº 12569, Gestão de Laboratórios e Ativos, proposta pela empresa W K Soluções Industriais no âmbito do Desafio de Ideias do SAGA SENAI de Inovação, percorrendo as etapas de Empatia, Definição e Ideação. A empresa controla equipamentos e ordens de serviço por meio de anotações físicas e registros manuais, do que decorrem atrasos na localização de ativos, divergência entre documentos e impossibilidade de prestação de contas. O objetivo foi compreender o problema sob a perspectiva do usuário final e converter esse entendimento em prioridades de desenvolvimento. Foram aplicadas cinco ferramentas: persona, mapa de empatia, matriz CSD, diagrama de afinidade e matriz de prioridade. Elegeu-se como persona primária o técnico de manutenção industrial, por ser o perfil que sustenta a premissa central da solução, a consulta realizada junto ao próprio equipamento. O mapa de empatia identificou como dor central o tempo de consulta, e não a ausência de cadastro. A matriz CSD separou o que a empresa afirmou daquilo que o grupo presumiu, expondo cinco dúvidas a levar à entrevista. O diagrama de afinidade agrupou as doze ideias geradas e revelou que cinco delas respondem à mesma necessidade, a de identificar o equipamento e alcançar a sua informação no local em que ele se encontra. A matriz de prioridade ordenou as doze ideias por valor e complexidade, e as três de maior valor e menor complexidade correspondem ao que já foi implementado no protótipo. Ao final, as suposições e as dúvidas acumuladas foram consolidadas em registro de dez riscos, três dos quais atingem o fundamento da solução e dependem de resposta da empresa.",
    { noIndent: true, line: 240, after: 360 }),
  p("Palavras-chave: Design Thinking. Persona. Mapa de empatia. Matriz CSD. Gestão de riscos. Gestão de ativos.",
    { noIndent: true, line: 240 }),

  // ABSTRACT
  center("ABSTRACT", { bold: true, after: 360, pb: true }),
  p("This document records the application of Design Thinking to demand no. 12569, Laboratory and Asset Management, proposed by the company W K Soluções Industriais within the SENAI Innovation Challenge, covering the Empathy, Definition and Ideation stages. The company controls equipment and service orders through physical notes and manual records, which results in delays in locating assets, divergence between documents and the impossibility of accountability. The objective was to understand the problem from the end user perspective and to convert that understanding into development priorities. Five tools were applied: persona, empathy map, CSD matrix, affinity diagram and priority matrix. The industrial maintenance technician was chosen as the primary persona, since this is the profile that sustains the central premise of the solution, namely the query performed next to the equipment itself. The empathy map identified query time, rather than the absence of registration, as the core pain. The CSD matrix separated what the company stated from what the group assumed, exposing five questions to be taken to the interview. The affinity diagram grouped the twelve generated ideas and showed that five of them answer the same need, that of identifying the equipment and reaching its information at the place where it stands. The priority matrix ranked the twelve ideas by value and complexity, and the three with highest value and lowest complexity match what has already been implemented in the prototype. Finally, the assumptions and open questions were consolidated into a register of ten risks, three of which affect the foundation of the solution and depend on the company's answer.",
    { noIndent: true, line: 240, after: 360 }),
  p("Keywords: Design Thinking. Persona. Empathy map. CSD matrix. Risk management. Asset management.",
    { noIndent: true, line: 240 }),

  // LISTA DE ILUSTRACOES
  center("LISTA DE ILUSTRAÇÕES", { bold: true, after: 360, pb: true }),
  sum("Quadro 1 – Caracterização da persona primária", "12"),
  sum("Figura 1 – Template “Crie a Persona” preenchido pelo grupo", "14"),
  sum("Quadro 2 – Síntese do mapa de empatia da persona primária", "15"),
  sum("Figura 2 – Template “Mapa de Empatia” preenchido pelo grupo", "16"),
  sum("Quadro 3 – Transcrição da matriz CSD", "16"),
  sum("Figura 3 – Matriz CSD preenchida pelo grupo", "17"),
  sum("Quadro 4 – Agrupamento das ideias geradas por afinidade temática", "18"),
  sum("Quadro 5 – Priorização das ideias por valor e complexidade", "18"),
  sum("Figura 4 – Matriz de Prioridade preenchida pelo grupo", "19"),
  sum("Quadro 6 – Decisão sobre as ideias e respectiva justificativa", "20"),
  sum("Quadro 7 – Registro de riscos do projeto", "21"),
  sum("Figura 5 – Equipe em atividade durante o workshop", "26"),

  // SUMARIO
  center("SUMÁRIO", { bold: true, after: 360, pb: true }),
  sum("1 INTRODUÇÃO", "6", { bold: true }),
  sum("2 OBJETIVOS", "7", { bold: true }),
  sum("2.1 Objetivo geral", "7"),
  sum("2.2 Objetivos específicos", "7"),
  sum("3 JUSTIFICATIVA", "8", { bold: true }),
  sum("4 FUNDAMENTAÇÃO TEÓRICA", "9", { bold: true }),
  sum("4.1 Design Thinking e o Duplo Diamante", "9"),
  sum("4.2 Persona", "9"),
  sum("4.3 Mapa de empatia", "9"),
  sum("4.4 Matriz CSD", "9"),
  sum("4.5 Matriz de prioridade", "10"),
  sum("4.6 Brainstorming e diagrama de afinidade", "10"),
  sum("4.7 Identificação e tratamento de riscos", "10"),
  sum("5 METODOLOGIA", "11", { bold: true }),
  sum("6 DESENVOLVIMENTO", "12", { bold: true }),
  sum("6.1 Caracterização do desafio", "12"),
  sum("6.2 Construção da persona", "12"),
  sum("6.3 Construção do mapa de empatia", "14"),
  sum("6.4 Organização dos dados na matriz CSD", "16"),
  sum("6.5 Geração e agrupamento das ideias", "17"),
  sum("6.6 Priorização das ideias", "18"),
  sum("6.7 Decisão sobre as ideias geradas", "19"),
  sum("6.8 Registro de riscos do projeto", "20"),
  sum("7 RESULTADOS PARCIAIS", "23", { bold: true }),
  sum("8 CONSIDERAÇÕES FINAIS", "24", { bold: true }),
  sum("REFERÊNCIAS", "25", { bold: true }),
  sum("APÊNDICE A – REGISTRO DA EQUIPE EM ATIVIDADE", "26", { bold: true }),
];

// ---------- elementos textuais ----------

const textual = [
  h1("1 Introdução"),
  p("O Desafio de Ideias é a etapa escolar do SAGA SENAI de Inovação, na qual empresas parceiras submetem problemas reais e as equipes de estudantes os enfrentam com o método do Design Thinking, apresentando ao final uma solução em formato de pitch para uma banca avaliadora."),
  p("Este documento registra a aplicação do método à demanda nº 12569, Gestão de Laboratórios e Ativos, proposta pela empresa W K Soluções Industriais. São percorridas três das quatro etapas do Duplo Diamante: a Empatia, com a construção da persona e do mapa de empatia; a Definição, com a organização dos dados na matriz CSD; e a Ideação, com a geração, o agrupamento por afinidade e a priorização das ideias. Registra-se ainda o conjunto de riscos que decorre das suposições e das dúvidas levantadas no percurso."),
  p("A escolha de documentar essas etapas em conjunto tem uma razão prática: é nelas que o problema deixa de ser um enunciado, ganha um rosto e se converte em uma ordem de trabalho. Todas as decisões técnicas tomadas em seguida, incluindo a arquitetura do protótipo, remetem ao que foi identificado aqui como a dor central do usuário."),

  h1("2 Objetivos"),
  h2("2.1 Objetivo geral"),
  p("Compreender, sob a perspectiva do usuário final, o problema de controle de equipamentos vivido pela W K Soluções Industriais, e converter esse entendimento em prioridades de desenvolvimento para a solução."),
  h2("2.2 Objetivos específicos"),
  alinea("a) construir uma persona representativa do usuário diretamente afetado pelo problema;"),
  alinea("b) elaborar o mapa de empatia correspondente a essa persona, identificando dores e necessidades;"),
  alinea("c) separar, por meio da matriz CSD, o que constitui certeza verificada, suposição do grupo e dúvida a ser levada à entrevista exploratória;"),
  alinea("d) agrupar as ideias geradas por afinidade temática, de modo a identificar a que necessidade cada uma responde;"),
  alinea("e) priorizar as ideias segundo os critérios de valor entregue e complexidade de execução, e registrar a decisão sobre cada uma;"),
  alinea("f) consolidar em registro de riscos as suposições e as dúvidas que condicionam a solução, indicando a resposta planejada para cada uma;"),
  p("g) relacionar as prioridades resultantes às decisões de arquitetura já tomadas no protótipo.", { noIndent: true, after: 240 }),

  h1("3 Justificativa"),
  p("O grupo analisou as 34 demandas da área de Tecnologia da Informação abertas no estado de São Paulo, avaliando cada uma quanto à viabilidade de entrega no semestre e ao valor técnico envolvido. A demanda nº 12569 foi selecionada por três características objetivas: escopo delimitado, composto por inventário, ordem de serviço e controle de estados; requisitos já detalhados pela própria empresa, o que elimina a fase mais cara de um projeto, que é a descoberta; e ausência de exigência de hardware específico ou infraestrutura onerosa."),
  p("A empresa controla seus equipamentos e ordens de serviço por meio de anotações físicas e registros manuais. Desse processo decorrem atrasos na localização de ativos, divergência entre documentos de setores distintos, retrabalho, compra duplicada e impossibilidade de prestação de contas. A própria empresa apontou como benefícios esperados a redução de inconsistências, a agilidade no acesso à informação e a redução de custos operacionais."),
  p("O curso de Desenvolvimento de Sistemas aplica-se diretamente ao caso, por envolver modelagem de dados, banco de dados centralizado, desenvolvimento web com acesso simultâneo, controle de acesso por perfil e engenharia de requisitos, esta última necessária para converter um processo informal em regras explícitas."),

  h1("4 Fundamentação teórica"),
  h2("4.1 Design Thinking e o Duplo Diamante"),
  p("O Design Thinking é uma abordagem de projeto que estabelece um único princípio orientador, o da centralidade no usuário. Segundo o material de capacitação do SENAI (2025), toda a construção da solução deve ser realizada por meio desse conceito."),
  p("O processo organiza-se no modelo do Duplo Diamante, formulado pelo Design Council (2007), composto por quatro etapas alternadas entre divergência e convergência: Empatia, na qual se amplia a compreensão do usuário; Definição, na qual se delimita o problema; Ideação, na qual se geram alternativas; e Prototipação, na qual se materializa e testa a solução escolhida."),
  h2("4.2 Persona"),
  p("Persona e público-alvo não são sinônimos. O material de capacitação demonstra essa distinção por meio de um exercício no qual dois indivíduos com dados demográficos idênticos revelam-se pessoas radicalmente diferentes, o que evidencia a insuficiência do recorte estatístico."),
  citacao("Personas são modelos que foram criados por designers para os aproximar da realidade dos consumidores, criando uma conexão empática com eles. (SENAI, 2025)"),
  p("A persona é, portanto, um instrumento de tangibilização: substitui o usuário abstrato por uma pessoa concreta, dotada de contexto, hábitos e limitações, sobre a qual a equipe pode raciocinar."),
  h2("4.3 Mapa de empatia"),
  p("O mapa de empatia organiza o entendimento sobre a persona em quatro quadrantes, referentes ao que ela vê, ao que pensa e sente, ao que ouve e ao que fala e faz, acrescidos de duas faixas destinadas às suas dores e necessidades. O objetivo é compreender o usuário a partir de seus sentimentos, e não apenas de seus dados."),
  h2("4.4 Matriz CSD"),
  p("A matriz CSD organiza as informações levantadas em três colunas, Certezas, Suposições e Dúvidas, e permite identificar o que ainda precisa ser explorado. Sua função no processo é impedir que uma suposição do grupo seja tratada como fato verificado, erro que compromete todas as decisões seguintes."),
  h2("4.5 Matriz de prioridade"),
  p("A matriz de prioridade é uma ferramenta visual de decisão, na qual as ideias geradas são posicionadas em um plano definido por dois eixos, o do valor entregue e o da complexidade de execução. A leitura do resultado é imediata: as ideias de maior valor e menor complexidade constituem o ponto de partida natural do desenvolvimento."),

  h2("4.6 Brainstorming e diagrama de afinidade"),
  p("O brainstorming, formulado por Alex Osborn, é técnica de geração de ideias em grupo cuja regra central é a suspensão do julgamento durante a fase criativa: acumula-se o maior número possível de ideias, e somente depois procede-se à seleção e ao descarte. O diagrama de afinidade é a ferramenta de organização aplicada ao resultado dessa geração. Consiste em agrupar as ideias por proximidade de propósito, e não por qualidade ou viabilidade, de modo a revelar quais necessidades concentram o maior número de respostas. O agrupamento antecede a priorização e a informa."),

  h2("4.7 Identificação e tratamento de riscos"),
  p("Risco, no planejamento de projeto, é a condição incerta que, se ocorrer, afeta os objetivos definidos. Seu tratamento compreende três operações: identificar, avaliar por impacto e probabilidade, e definir a resposta cabível. No percurso do Design Thinking, a matriz CSD constitui fonte direta de riscos, porque toda suposição declarada é uma condição admitida sem verificação e toda dúvida registrada é uma informação ausente. Ambas descrevem exposição do projeto. O registro de riscos formaliza essa leitura e vincula cada item à providência que o resolve."),

  h1("5 Metodologia"),
  p("O trabalho seguiu as ferramentas apresentadas no workshop do Desafio de Ideias, aplicadas em atividade de grupo sobre os templates impressos fornecidos pelo SENAI. O insumo inicial foi o detalhamento escrito da demanda nº 12569, disponibilizado pela empresa na plataforma do SAGA SENAI de Inovação."),
  p("A sequência adotada foi a seguinte: construção da persona e do mapa de empatia, na etapa de Empatia; organização das informações na matriz CSD, na etapa de Definição; e geração, agrupamento por afinidade, priorização e decisão sobre as ideias, na etapa de Ideação. Ao final das três etapas, as suposições e as dúvidas acumuladas na matriz CSD foram consolidadas em registro de riscos do projeto. Cada template preenchido foi fotografado e integra este documento como registro da atividade."),
  p("Cabe registrar uma limitação metodológica relevante: a persona e o mapa de empatia foram construídos a partir de documento escrito, e não de entrevista com um trabalhador real da empresa. O grupo optou por não ocultar essa limitação, mas por torná-la explícita na própria matriz CSD, na qual ela figura como suposição declarada. A validação está prevista para a entrevista exploratória com a empresa."),

  h1("6 Desenvolvimento"),
  h2("6.1 Caracterização do desafio"),
  p("A dor central identificada no detalhamento da demanda é o tempo de consulta. A empresa não consegue responder com rapidez onde está determinado equipamento, qual o seu histórico de manutenção e qual o setor responsável por ele. Os colaboradores relatam ainda divergências entre documentos."),
  p("Essa formulação é importante porque delimita o problema: não se trata de ausência de cadastro, mas de indisponibilidade da informação no momento e no local em que ela é necessária."),

  h2("6.2 Construção da persona"),
  p("Entre os três perfis de usuário identificados, o grupo elegeu como persona primária o técnico de manutenção. O critério não foi o volume de usuários, mas o de criticidade: é esse perfil que sustenta a aposta central da solução, a consulta realizada pelo telefone celular junto ao próprio equipamento. Caso a solução não funcione para ele, a premissa do projeto se desfaz."),
  legenda("Quadro 1 – Caracterização da persona primária"),
  quadro([Math.round(TEXT_W * 0.30), Math.round(TEXT_W * 0.70)],
    ["Campo", "Descrição"],
    [
      ["Nome", "Márcio Tavares"],
      ["Idade", "34 anos"],
      ["Formação", "Técnico em Eletromecânica (SENAI)"],
      ["Profissão", "Técnico de manutenção industrial"],
      ["Habilidades pessoais", "Resolve no improviso; conhece cada máquina pelo barulho; anota tudo numa caderneta de bolso; tem pouca paciência com sistema lento; usa o telefone celular o dia inteiro, porém de luva"],
      ["Onde vive", "Zona leste de São Paulo, a 1h10 de trajeto da empresa"],
      ["Onde estuda", "Cursos de atualização no SENAI aos sábados; complementa a formação por meio de vídeos"],
    ]),
  fonte("Fonte: elaborado pelos autores (2026)."),
  p("A frase-síntese adotada para representar a persona é: “Eu sei consertar a máquina; o que me atrasa é descobrir onde ela está e quem mexeu nela por último”."),
  p("Registra-se que o campo “onde estuda”, previsto no template, foi interpretado como “onde se capacita”, uma vez que o template original pressupõe personas em idade escolar. A informação obtida não é acessória: o fato de a persona aprender por meio de vídeos sustenta a decisão de projetar uma interface que dispense manual de uso."),
  p("Foram ainda caracterizadas duas personas secundárias, correspondentes aos demais perfis de acesso previstos no sistema: Simone, coordenadora de laboratório, responsável pela prestação de contas; e Débora, auxiliar de patrimônio, responsável pela conferência anual do inventário."),
  legenda("Figura 1 – Template “Crie a Persona” preenchido pelo grupo"),
  figura("01-persona.jpeg", "[ INSERIR FOTOGRAFIA DO TEMPLATE DA PERSONA ]",
    "Substituir esta caixa pela imagem.", 21),
  fonte("Fonte: acervo dos autores (2026)."),

  h2("6.3 Construção do mapa de empatia"),
  p("O quadro a seguir sintetiza o mapa de empatia elaborado para a persona primária."),
  legenda("Quadro 2 – Síntese do mapa de empatia da persona primária"),
  quadro([Math.round(TEXT_W * 0.26), Math.round(TEXT_W * 0.74)],
    ["Quadrante", "Conteúdo"],
    [
      ["Vê", "Ambiente de trabalho, papéis e planilhas"],
      ["Pensa e sente", "Medo de ser cobrado; a percepção de gastar mais tempo procurando do que consertando"],
      ["Ouve", "Perguntas de colegas sobre a localização de instrumentos e a afirmação de que a planilha registra localização diversa da real"],
      ["Fala e faz", "Percorre o local à procura do equipamento, consulta o colega e fotografa o patrimônio com o telefone celular"],
      ["Dores", "Tempo perdido na localização do ativo, inexistência de histórico e contradição entre documentos"],
      ["Necessidades", "Saber onde o equipamento está, dispor do histórico junto à máquina e registrar a movimentação de imediato"],
    ]),
  fonte("Fonte: elaborado pelos autores (2026)."),
  p("Observa-se que as necessidades foram deliberadamente formuladas sem referência à solução. A necessidade registrada é dispor do histórico junto à máquina, e não dispor de um aplicativo com código QR. A distinção preserva o encadeamento lógico do projeto, no qual a necessidade precede e justifica a solução técnica."),
  legenda("Figura 2 – Template “Mapa de Empatia” preenchido pelo grupo"),
  figura("02-mapa-empatia.jpeg", "[ INSERIR FOTOGRAFIA DO MAPA DE EMPATIA ]",
    "Substituir esta caixa pela imagem.", 12),
  fonte("Fonte: acervo dos autores (2026)."),

  h2("6.4 Organização dos dados na matriz CSD"),
  p("Concluída a etapa de Empatia, o grupo organizou as informações disponíveis na matriz CSD, de modo a distinguir o que a empresa efetivamente afirmou daquilo que o grupo presumiu. O resultado está transcrito no quadro a seguir."),
  legenda("Quadro 3 – Transcrição da matriz CSD"),
  quadro([Math.round(TEXT_W * 0.20), Math.round(TEXT_W * 0.80)],
    ["Coluna", "Itens registrados"],
    [
      ["Certezas", "Controle feito em papel e anotação manual; ninguém sabe rapidamente onde está o equipamento; não existe histórico de manutenção; documentos se contradizem entre setores; a empresa quer menos erro, mais agilidade e menos custo"],
      ["Suposições", "O técnico consulta no pé da máquina, pelo telefone celular; todo técnico tem smartphone e pode usá-lo na área; a etiqueta sobrevive a óleo, calor e atrito; há wi-fi ou sinal no chão de fábrica; a persona Márcio veio do texto da demanda, e não de entrevista"],
      ["Dúvidas", "Quantos ativos existem hoje? Há wi-fi na área e o telefone celular é liberado? Quem pode movimentar um ativo e é preciso aprovação? A ordem de serviço entra agora ou na fase 2? Existe planilha ou ERP do qual importar a base?"],
    ]),
  fonte("Fonte: elaborado pelos autores (2026)."),
  p("A matriz cumpriu a função de expor o risco do projeto. Três das cinco suposições registradas são de natureza técnica e ambiental, e não de comportamento: a resistência da etiqueta, a disponibilidade de sinal e a permissão de uso do telefone celular na área de produção. Todas as três condicionam a viabilidade da solução proposta, e nenhuma delas depende do grupo. Por esse motivo, foram convertidas em perguntas objetivas para a entrevista exploratória com a empresa."),
  legenda("Figura 3 – Matriz CSD preenchida pelo grupo"),
  figura("03-matriz-csd.jpeg", "[ INSERIR FOTOGRAFIA DA MATRIZ CSD ]",
    "Substituir esta caixa pela imagem.", 21),
  fonte("Fonte: acervo dos autores (2026)."),

  h2("6.5 Geração e agrupamento das ideias"),
  p("A etapa de Ideação teve início por sessão de brainstorming conduzida sobre a dor central definida na etapa anterior. O grupo reuniu doze ideias, registradas sem julgamento prévio, conforme a regra do método, segundo a qual a seleção e o descarte constituem etapa posterior à geração."),
  p("As doze ideias foram em seguida agrupadas por afinidade temática, procedimento que precede a priorização e cuja função é revelar a que necessidade cada ideia responde. O agrupamento não considera valor nem complexidade, critérios reservados à matriz de prioridade, mas apenas a proximidade de propósito entre as ideias."),
  legenda("Quadro 4 – Agrupamento das ideias geradas por afinidade temática"),
  quadro([Math.round(TEXT_W * 0.32), Math.round(TEXT_W * 0.68)],
    ["Grupo de afinidade", "Ideias reunidas"],
    [
      ["Identificação e acesso à informação no equipamento",
       "1. Ficha do ativo pelo código QR; 3. Folha de etiquetas em formato A4; 5. Leitor de código QR pela câmera; 10. Aplicativo nativo; 11. RFID e NFC"],
      ["Cadastro e consulta centralizados",
       "2. Cadastro com busca e filtro; 7. Foto do ativo; 9. Paginação e ordenação"],
      ["Rastreabilidade do ciclo de vida do ativo",
       "4. Ordem de serviço; 6. Movimentação com histórico"],
      ["Prestação de contas e interoperabilidade",
       "8. Exportação em CSV; 12. Integração com ERP"],
    ]),
  fonte("Fonte: elaborado pelos autores (2026)."),
  p("O agrupamento produziu um achado que orientou as decisões seguintes. O grupo mais numeroso, com cinco das doze ideias, reúne formas de identificar o equipamento e de alcançar a sua informação no próprio local em que ele se encontra. A convergência não é casual: ela reproduz, no plano das ideias, a necessidade registrada no mapa de empatia como consulta junto à máquina. Confirma-se, por esse caminho, que o eixo da solução está no meio de identificação do ativo, e não na tela de cadastro, e que as cinco ideias desse grupo constituem variantes técnicas de uma mesma resposta, entre as quais cabe escolher pela relação entre valor e complexidade."),

  h2("6.6 Priorização das ideias"),
  p("Na etapa de Ideação, as ideias levantadas pelo grupo foram posicionadas na matriz de prioridade segundo os eixos de valor entregue e complexidade de execução. O quadro a seguir reproduz a distribuição obtida."),
  legenda("Quadro 5 – Priorização das ideias por valor e complexidade"),
  quadro([Math.round(TEXT_W * 0.30), Math.round(TEXT_W * 0.70)],
    ["Quadrante", "Ideias posicionadas"],
    [
      ["Maior valor e menor complexidade", "1. Ficha do ativo pelo código QR; 2. Cadastro com busca e filtro; 3. Folha de etiquetas em formato A4"],
      ["Maior valor e maior complexidade", "4. Ordem de serviço; 5. Leitor de código QR pela câmera; 6. Movimentação com histórico"],
      ["Menor valor e menor complexidade", "7. Foto do ativo; 8. Exportação em CSV; 9. Paginação e ordenação"],
      ["Menor valor e maior complexidade", "10. Aplicativo nativo; 11. RFID e NFC; 12. Integração com ERP"],
    ]),
  fonte("Fonte: elaborado pelos autores (2026)."),
  p("A leitura da matriz orientou a ordem de execução. O quadrante de maior valor e menor complexidade define o ponto de partida, e o de maior valor e maior complexidade define a sequência seguinte, na qual se concentra o esforço técnico do projeto. O quadrante de menor valor e maior complexidade, que reúne o aplicativo nativo, o RFID e a integração com ERP, foi descartado do escopo atual, ainda que represente evolução possível no futuro."),
  legenda("Figura 4 – Matriz de Prioridade preenchida pelo grupo"),
  figura("04-matriz-prioridade.jpeg", "[ INSERIR FOTOGRAFIA DA MATRIZ DE PRIORIDADE ]",
    "Substituir esta caixa pela imagem.", 21),
  fonte("Fonte: acervo dos autores (2026)."),

  h2("6.7 Decisão sobre as ideias geradas"),
  p("Concluída a priorização, procedeu-se à decisão sobre quais ideias seriam desenvolvidas. O material de capacitação sugere, para essa escolha, votação individual na qual cada integrante assinala até três ideias com marcador de cor própria. O grupo não aplicou esse formato: a decisão deu-se por consenso, em discussão conduzida sobre a própria matriz de prioridade. Registra-se a opção para que não se atribua à seleção um procedimento que não foi executado."),
  p("A convergência sem necessidade de contagem de votos tem explicação no insumo de partida. Como os requisitos da demanda nº 12569 já se encontravam detalhados pela empresa, as ideias do grupo não disputavam entre si a definição do produto, mas apenas a ordem de execução, que os eixos da matriz já estabeleciam. O critério aplicado na decisão foi, portanto, o próprio posicionamento obtido no quadro anterior."),
  legenda("Quadro 6 – Decisão sobre as ideias e respectiva justificativa"),
  quadro([Math.round(TEXT_W * 0.22), Math.round(TEXT_W * 0.16), Math.round(TEXT_W * 0.62)],
    ["Decisão", "Ideias", "Justificativa"],
    [
      ["Desenvolver de imediato", "1, 2, 3, 5 e 6",
       "Maior valor para a dor de tempo de consulta, com complexidade compatível com o prazo da unidade curricular. As ideias 5 e 6, de maior complexidade, foram antecipadas porque a leitura pela câmera é condição de uso das ideias 1 e 3, e o histórico de movimentação é a própria rastreabilidade solicitada pela empresa."],
      ["Desenvolver após validação", "4. Ordem de serviço",
       "Única ideia de maior valor ainda pendente. Os estados, os prazos e as regras de atribuição não constam do detalhamento da demanda e precisam ser levantados na entrevista exploratória."],
      ["Manter como melhoria", "7, 8 e 9",
       "Menor valor e menor complexidade. Incorporam-se conforme a disponibilidade de tempo, sem disputar prioridade com os itens anteriores."],
      ["Descartar do escopo atual", "10, 11 e 12",
       "Menor valor e maior complexidade. O aplicativo nativo, o RFID e a integração com ERP exigem investimento ou infraestrutura incompatíveis com o escopo da unidade curricular, e permanecem como evolução possível."],
    ]),
  fonte("Fonte: elaborado pelos autores (2026)."),

  h2("6.8 Registro de riscos do projeto"),
  p("A matriz CSD cumpre, além da organização dos dados, a função de expor os pontos em que o projeto depende de informação que o grupo não possui. Cada suposição declarada e cada dúvida registrada constitui, do ponto de vista do planejamento, um risco, ou seja, uma condição que, se contrariada, obriga à revisão da solução. Por essa razão, consolidou-se o registro apresentado a seguir."),
  p("A avaliação combina impacto, entendido como a extensão da revisão exigida caso o risco se concretize, e probabilidade, estimada pelo grupo a partir do detalhamento da demanda. A coluna de resposta planejada indica a providência cabível e identifica, quando aplicável, a pergunta do roteiro da entrevista exploratória destinada a eliminar a incerteza."),
  legenda("Quadro 7 – Registro de riscos do projeto"),
  quadro([Math.round(TEXT_W * 0.07), Math.round(TEXT_W * 0.35), Math.round(TEXT_W * 0.16), Math.round(TEXT_W * 0.42)],
    ["Nº", "Risco e origem", "Avaliação", "Resposta planejada"],
    [
      ["R1", "A etiqueta não resistir a óleo, calor e atrito, e o ativo perder a identificação. Origem: suposição da matriz CSD.",
       "Impacto alto; probabilidade média",
       "Especificar etiqueta de material industrial e imprimir o número de patrimônio em texto legível ao lado do código, como redundância. Pergunta 8 do roteiro."],
      ["R2", "Ausência de sinal de rede no chão de fábrica, que impede a consulta junto ao equipamento. Origem: suposição e dúvida da matriz CSD.",
       "Impacto alto; probabilidade média",
       "Prever dados mínimos de identificação na própria etiqueta e sincronização posterior do registro. Pergunta 9 do roteiro."],
      ["R3", "Restrição ao uso de telefone celular na área de produção. Origem: suposição e dúvida da matriz CSD.",
       "Impacto alto; probabilidade média",
       "Prever terminal fixo de consulta nos setores, ou aparelho corporativo compartilhado, como alternativa ao aparelho pessoal. Pergunta 10 do roteiro."],
      ["R4", "Base de ativos muito superior à estimada, inviabilizando o cadastro inicial e o desempenho da consulta. Origem: dúvida da matriz CSD.",
       "Impacto médio; probabilidade média",
       "Importação da base por arquivo CSV, paginação e índices no banco de dados. Pergunta 4 do roteiro."],
      ["R5", "Persona e mapa de empatia construídos sobre documento escrito, e não sobre entrevista, com risco de a solução responder ao problema errado. Origem: limitação metodológica declarada na seção 5.",
       "Impacto alto; probabilidade média",
       "Realização da entrevista exploratória e revisão integral da matriz CSD à luz das respostas obtidas. Roteiro completo."],
      ["R6", "Desconhecimento de quem autoriza a movimentação de um ativo, com risco de modelo de permissões incorreto. Origem: dúvida da matriz CSD.",
       "Impacto médio; probabilidade alta",
       "Manter os perfis de acesso parametrizáveis, de modo que a regra possa ser ajustada sem reescrita do sistema. Pergunta 12 do roteiro."],
      ["R7", "Escopo do módulo de ordens de serviço indefinido quanto a estados, prazos e responsáveis. Origem: dúvida da matriz CSD.",
       "Impacto alto; probabilidade alta",
       "Não iniciar o desenvolvimento antes da validação com a empresa, conforme previsto no plano de ensino da unidade curricular."],
      ["R8", "Inexistência de base digital da qual importar o cadastro atual, implicando digitação integral. Origem: dúvida da matriz CSD.",
       "Impacto médio; probabilidade média",
       "Prever cadastro assistido pela leitura da etiqueta, distribuído entre os integrantes, e importação por CSV quando houver planilha. Pergunta 24 do roteiro."],
      ["R9", "Indisponibilidade da empresa para a entrevista no prazo da etapa, o que bloqueia a revisão da matriz CSD e a definição das ordens de serviço. Origem: dependência externa ao grupo.",
       "Impacto alto; probabilidade média",
       "Oferecer à empresa os formatos presencial, remoto e questionário escrito, com roteiro já elaborado e duração delimitada em sessenta minutos."],
      ["R10", "Escopo superior ao tempo disponível, considerando a carga de quarenta e cinco horas da unidade curricular. Origem: planejamento do projeto.",
       "Impacto médio; probabilidade média",
       "Desenvolvimento incremental por sprints, assegurada a entrega do quadrante de maior valor antes de qualquer item de melhoria."],
    ]),
  fonte("Fonte: elaborado pelos autores (2026)."),
  p("Três riscos merecem destaque por não dependerem de decisão do grupo e por atingirem o fundamento da solução. Os riscos R1, R2 e R3 incidem, respectivamente, sobre a durabilidade da etiqueta, sobre a disponibilidade de rede e sobre a permissão de uso do telefone celular na área. A proposta apoia-se na consulta realizada junto ao equipamento, de modo que a confirmação negativa de qualquer um dos três obriga à revisão do meio de acesso, ainda que não do diagnóstico da dor. Por essa razão, as três condições foram convertidas em perguntas específicas do roteiro da entrevista exploratória, e nenhuma delas pode permanecer sem resposta."),

  h1("7 Resultados parciais"),
  p("A comparação entre a matriz de prioridade e o estado atual do protótipo, denominado AtivoLab, confirma a coerência do percurso. As três ideias do quadrante de maior valor e menor complexidade estão implementadas, assim como duas das três do quadrante de maior valor e maior complexidade. A única pendência entre as ideias de maior valor é a ordem de serviço, que constitui o próximo item de desenvolvimento."),
  p("Duas decisões de arquitetura decorrem diretamente das necessidades levantadas na etapa de Empatia. A primeira consiste em posicionar a etiqueta com código QR como elemento central do sistema, e não a tela de cadastro. A necessidade de consultar o histórico junto à máquina implica que a informação deve ser alcançável a partir do próprio equipamento: ao apontar a câmera do telefone celular para a etiqueta, abre-se a ficha do ativo; a movimentação entre setores realiza-se pela leitura da etiqueta e pela indicação do destino."),
  p("A segunda decisão estabelece que o histórico seja incremental, ou seja, que as tabelas de movimentação e auditoria admitam exclusivamente operações de inserção. A correção de um erro implica o registro de novo evento, jamais a supressão do anterior. Essa escolha responde à necessidade de que o registro produzido pelo técnico não seja alterado por terceiros, e converte a rastreabilidade em propriedade da estrutura de dados."),
  p("O protótipo encontra-se em estágio funcional, contemplando autenticação com três perfis de acesso, cadastro e consulta de ativos, geração de folha de etiquetas em formato A4, leitura de código QR pela câmera, movimentação entre setores com histórico e trilha de auditoria."),

  h1("8 Considerações finais"),
  p("As três etapas percorridas cumpriram a função de converter um enunciado de problema em uma ordem de trabalho. A definição da dor central como tempo de consulta, e não como ausência de cadastro, mostrou-se determinante para as decisões técnicas, e a matriz de prioridade confirmou que o esforço de desenvolvimento foi aplicado nos itens de maior valor."),
  p("Reitera-se a limitação apontada na seção 5, e registrada pelo próprio grupo na coluna de suposições da matriz CSD: a persona e o mapa de empatia foram construídos sobre documento escrito. As cinco dúvidas levantadas, em especial as referentes à resistência da etiqueta, à disponibilidade de sinal na área e à liberação do telefone celular, precisam ser respondidas pela empresa antes que a solução possa ser considerada validada."),
  p("As próximas etapas previstas são a realização da entrevista exploratória com a W K Soluções Industriais, a revisão da matriz CSD à luz das respostas obtidas, a implementação do módulo de ordens de serviço e a preparação do pitch."),

  h1("Referências", { align: AlignmentType.CENTER }),
  ref([{ t: "ASSOCIAÇÃO BRASILEIRA DE NORMAS TÉCNICAS. " }, { t: "NBR 14724", b: true },
       { t: ": informação e documentação: trabalhos acadêmicos: apresentação. Rio de Janeiro: ABNT, 2011." }]),
  ref([{ t: "ASSOCIAÇÃO BRASILEIRA DE NORMAS TÉCNICAS. " }, { t: "NBR 6023", b: true },
       { t: ": informação e documentação: referências: elaboração. Rio de Janeiro: ABNT, 2018." }]),
  ref([{ t: "BROWN, Tim. " }, { t: "Design thinking", b: true },
       { t: ": uma metodologia poderosa para decretar o fim das velhas ideias. Rio de Janeiro: Elsevier, 2010." }]),
  ref([{ t: "DESIGN COUNCIL. " }, { t: "Eleven lessons", b: true },
       { t: ": managing design in eleven global brands. Londres: Design Council, 2007." }]),
  ref([{ t: "OSBORN, Alex F. " }, { t: "Applied imagination", b: true },
       { t: ": principles and procedures of creative thinking. Nova York: Charles Scribner’s Sons, 1953." }]),
  ref([{ t: "SERVIÇO NACIONAL DE APRENDIZAGEM INDUSTRIAL. " }, { t: "Capacitação Desafio de Ideias 2025", b: true },
       { t: ": versão treinamento. Material de apoio do workshop. São Paulo: SENAI, 2025." }]),
  ref([{ t: "W K SOLUÇÕES INDUSTRIAIS. " }, { t: "Demanda nº 12569", b: true },
       { t: ": gestão de laboratórios e ativos. São Paulo: SAGA SENAI de Inovação, 2026. Detalhamento da demanda." }]),

  h1("Apêndice A – Registro da equipe em atividade"),
  p("Apresenta-se a seguir o registro fotográfico da equipe durante a realização das atividades do workshop."),
  legenda("Figura 5 – Equipe em atividade durante o workshop"),
  figura("05-equipe.jpeg", "[ INSERIR FOTOGRAFIA DA EQUIPE ]",
    "Substituir esta caixa pela imagem.", 12),
  fonte("Fonte: acervo dos autores (2026)."),
];

// ---------- documento ----------

const margens = {
  top: Math.round(3 * CM), right: Math.round(2 * CM),
  bottom: Math.round(2 * CM), left: Math.round(3 * CM),
};

const doc = new Document({
  styles: { default: { document: { run: { font: FONT, size: 24 }, paragraph: { spacing: { line: 360 } } } } },
  sections: [
    { properties: { page: { margin: margens } }, children: preTextual },
    {
      properties: { page: { margin: margens, pageNumbers: { start: 6 } } },
      headers: {
        default: new Header({
          children: [new Paragraph({
            alignment: AlignmentType.RIGHT,
            spacing: { line: 240 },
            children: [new TextRun({ children: [PageNumber.CURRENT], font: FONT, size: 20 })],
          })],
        }),
      },
      children: textual,
    },
  ],
});

Packer.toBuffer(doc).then((buf) => {
  fs.writeFileSync(process.argv[2], buf);
  console.log("ok:", process.argv[2], buf.length, "bytes");
  const esperadas = ["01-persona.jpeg", "02-mapa-empatia.jpeg", "03-matriz-csd.jpeg",
                     "04-matriz-prioridade.jpeg", "05-equipe.jpeg"];
  const faltando = esperadas.filter((f) => !fs.existsSync(path.join(DIR_IMG, f)));
  console.log(faltando.length ? "imagens ausentes: " + faltando.join(", ") : "todas as imagens presentes");
});
