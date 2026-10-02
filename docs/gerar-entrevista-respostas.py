# -*- coding: utf-8 -*-
"""
Gera a versao do roteiro da entrevista do GR4 preenchida com as respostas que o
grupo espera ouvir da W K Solucoes Industriais (demanda 12569).
Uso: python gerar-entrevista-respostas.py [saida.pdf]

As respostas sao hipoteses do grupo, escritas antes da conversa, para ensaio do
roteiro e para comparacao posterior com o que a empresa realmente responder.
Nenhuma resposta aqui foi dita pela empresa.
"""
import sys
from reportlab.lib.pagesizes import A4
from reportlab.lib.units import cm
from reportlab.lib import colors
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.enums import TA_JUSTIFY, TA_CENTER
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (BaseDocTemplate, PageTemplate, Frame, Paragraph,
                                Spacer, Table, TableStyle, KeepTogether, PageBreak)

SAIDA = sys.argv[1] if len(sys.argv) > 1 else "07 - Roteiro_Entrevista_GR4_Respostas_Esperadas.pdf"

# ----------------------------------------------------------------- fontes
try:
    pdfmetrics.registerFont(TTFont("Arial", "C:/Windows/Fonts/arial.ttf"))
    pdfmetrics.registerFont(TTFont("Arial-Bold", "C:/Windows/Fonts/arialbd.ttf"))
    pdfmetrics.registerFont(TTFont("Arial-Italic", "C:/Windows/Fonts/ariali.ttf"))
    pdfmetrics.registerFontFamily("Arial", normal="Arial", bold="Arial-Bold",
                                  italic="Arial-Italic", boldItalic="Arial-Bold")
    F, FB, FI = "Arial", "Arial-Bold", "Arial-Italic"
except Exception:
    F, FB, FI = "Helvetica", "Helvetica-Bold", "Helvetica-Oblique"

VERMELHO  = colors.HexColor("#C00000")
AZUL      = colors.HexColor("#1F497D")
AZULCLARO = colors.HexColor("#DCE6F1")
FUNDORESP = colors.HexColor("#F2F5FA")
CINZA     = colors.HexColor("#808080")
LINHA     = colors.HexColor("#9C9C9C")

MARG_E, MARG_D, MARG_S, MARG_I = 2.5 * cm, 2.0 * cm, 2.3 * cm, 2.0 * cm
LARG = A4[0] - MARG_E - MARG_D


# ----------------------------------------------------------------- estilos
def est(nome, **kw):
    base = dict(name=nome, fontName=F, fontSize=9.5, leading=13, spaceAfter=0)
    base.update(kw)
    return ParagraphStyle(**base)


S_TITULO   = est("titulo", fontName=FB, fontSize=18, leading=22, alignment=TA_CENTER,
                 textColor=AZUL, spaceAfter=6)
S_SUB      = est("sub", fontSize=10.5, leading=15, alignment=TA_CENTER, spaceAfter=3)
S_FINAL    = est("fin", fontSize=9, leading=12.5, alignment=TA_CENTER,
                 textColor=colors.HexColor("#404040"), spaceAfter=12)
S_SECAO    = est("secao", fontName=FB, fontSize=11.5, leading=15, textColor=VERMELHO,
                 spaceBefore=13, spaceAfter=5)
S_SUBSECAO = est("subsecao", fontName=FB, fontSize=10, leading=13, textColor=AZUL,
                 spaceBefore=8, spaceAfter=4)
S_PERG     = est("perg", fontName=FB, fontSize=9.5, leading=12.5, spaceAfter=1)
S_OBJ      = est("obj", fontSize=8, leading=10.5, textColor=CINZA,
                 leftIndent=0.5 * cm, spaceAfter=3)
S_RESP     = est("resp", fontSize=9, leading=12.3, alignment=TA_JUSTIFY,
                 textColor=colors.HexColor("#202020"))
S_TXT      = est("txt", alignment=TA_JUSTIFY, spaceAfter=5)
S_NOTA     = est("nota", fontSize=8.5, leading=11.5, alignment=TA_JUSTIFY,
                 textColor=colors.HexColor("#404040"), spaceAfter=4)
S_ITEM     = est("item", fontSize=9, leading=12, leftIndent=0.45 * cm,
                 bulletIndent=0.1 * cm, spaceAfter=2)
S_CEL      = est("cel", fontSize=8.5, leading=11)
S_CELB     = est("celb", fontName=FB, fontSize=8.5, leading=11, textColor=AZUL)
S_CELA     = est("cela", fontName=FI, fontSize=8.5, leading=11,
                 textColor=colors.HexColor("#1F3864"))


# --------------------------------------------------------------- moldura
def moldura(canvas, doc):
    canvas.saveState()
    canvas.setFont(FB, 9)
    canvas.setFillColor(VERMELHO)
    canvas.drawString(MARG_E, A4[1] - 1.5 * cm, "GR4 | 2IEDS")
    canvas.setFont(F, 7.5)
    canvas.setFillColor(CINZA)
    canvas.drawRightString(A4[0] - MARG_D, A4[1] - 1.5 * cm,
                           "SENAI | Desafio de Ideias \u00b7 Respostas esperadas (hip\u00f3tese do grupo)")
    canvas.setStrokeColor(LINHA)
    canvas.setLineWidth(0.5)
    canvas.line(MARG_E, A4[1] - 1.75 * cm, A4[0] - MARG_D, A4[1] - 1.75 * cm)
    canvas.line(MARG_E, 1.5 * cm, A4[0] - MARG_D, 1.5 * cm)
    canvas.setFont(F, 7.5)
    canvas.setFillColor(CINZA)
    canvas.drawString(MARG_E, 1.2 * cm,
                      "Demanda n\u00ba 12569 \u00b7 W K Solu\u00e7\u00f5es Industriais")
    canvas.drawRightString(A4[0] - MARG_D, 1.2 * cm, "p. %d" % doc.page)
    canvas.restoreState()


# ----------------------------------------------------------------- helpers
contador = [0]


def caixa_resposta(texto):
    t = Table([[Paragraph("<b>Resposta esperada.</b> " + texto, S_RESP)]],
              colWidths=[LARG - 0.5 * cm])
    t.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, -1), FUNDORESP),
        ("LINEBEFORE", (0, 0), (0, -1), 1.6, AZUL),
        ("LEFTPADDING", (0, 0), (-1, -1), 6),
        ("RIGHTPADDING", (0, 0), (-1, -1), 5),
        ("TOPPADDING", (0, 0), (-1, -1), 4),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 4),
    ]))
    t.hAlign = "RIGHT"
    return t


def pergunta(texto, objetivo, resposta=None):
    contador[0] += 1
    partes = [Paragraph('<font color="%s">%d.</font> %s'
                        % (AZUL.hexval(), contador[0], texto), S_PERG),
              Paragraph("Objetivo: %s" % objetivo, S_OBJ)]
    if resposta:
        partes.append(caixa_resposta(resposta))
    partes.append(Spacer(1, 6))
    return KeepTogether(partes)


def quadro(cabecalho, linhas_dados, larguras, folga=6, resp_de=99):
    """resp_de: indice da primeira coluna cujo conteudo e resposta prevista."""
    dados = [[Paragraph(c, S_CELB) for c in cabecalho]]
    for lin in linhas_dados:
        dados.append([Paragraph(c, S_CELA if i >= resp_de else S_CEL) if c else ""
                      for i, c in enumerate(lin)])
    t = Table(dados, colWidths=larguras, repeatRows=1)
    t.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), AZULCLARO),
        ("GRID", (0, 0), (-1, -1), 0.5, colors.HexColor("#7F9DB9")),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 4),
        ("RIGHTPADDING", (0, 0), (-1, -1), 4),
        ("TOPPADDING", (0, 0), (-1, -1), 4),
        ("BOTTOMPADDING", (0, 0), (-1, 0), 4),
        ("BOTTOMPADDING", (0, 1), (-1, -1), folga),
    ]))
    return t


def secao(titulo, minutos):
    return Paragraph('%s <font size="8.5" color="%s">(cerca de %d minutos)</font>'
                     % (titulo, CINZA.hexval(), minutos), S_SECAO)


def item(texto):
    return Paragraph(texto, S_ITEM, bulletText="\u2013")


E = []

# ============================================================== abertura
E += [
    Spacer(1, 0.2 * cm),
    Paragraph("ROTEIRO DE ENTREVISTA EXPLORAT\u00d3RIA<br/>RESPOSTAS ESPERADAS", S_TITULO),
    Paragraph("Grupo GR4 \u00b7 Demanda n\u00ba 12569, Gest\u00e3o de Laborat\u00f3rios e Ativos",
              S_SUB),
    Paragraph("Empresa W K Solu\u00e7\u00f5es Industriais \u00b7 Etapa de Empatia do Design Thinking",
              S_SUB),
    Spacer(1, 0.25 * cm),
    Paragraph("Turma 2IEDS | 2026 \u00b7 Docente respons\u00e1vel: Paulo Cesar de Camargo \u00b7 "
              "Coordena\u00e7\u00e3o do curso: Marcos Alves \u00b7 Coordena\u00e7\u00e3o "
              "pedag\u00f3gica: Ant\u00f4nio Marcos", S_FINAL),
]

E.append(Paragraph(
    "<b>Aviso.</b> Este documento \u00e9 a vers\u00e3o do roteiro preenchida com a resposta que o "
    "GR4 <b>espera</b> ouvir em cada pergunta. Nenhuma das respostas foi dita pela empresa: todas "
    "s\u00e3o hip\u00f3teses do grupo, deduzidas do detalhamento escrito da demanda, do que se "
    "observa em empresas de porte semelhante e da persona constru\u00edda na etapa de Empatia. Tudo "
    "o que est\u00e1 aqui pertence \u00e0 coluna de <i>suposi\u00e7\u00f5es</i> da matriz CSD, e "
    "n\u00e3o \u00e0 de certezas.", S_TXT))

E.append(Paragraph("Para que serve", S_SUBSECAO))
for t in [
    "<b>Ensaiar o roteiro.</b> Pergunta cuja resposta prevista \u00e9 vaga ou \u00f3bvia est\u00e1 "
    "mal formulada e precisa ser reescrita antes da conversa.",
    "<b>Medir o pr\u00f3prio erro.</b> Depois da entrevista, comparar resposta esperada com resposta "
    "real mostra onde o grupo projetou a pr\u00f3pria ideia sobre o cliente.",
    "<b>Preparar a escuta.</b> Quem sabe a resposta que espera percebe na hora quando ouve outra "
    "coisa, e aprofunda ali em vez de seguir o roteiro no autom\u00e1tico.",
    "<b>Risco a evitar.</b> Entrar na entrevista tentando confirmar o que est\u00e1 escrito aqui. A "
    "hip\u00f3tese serve para ser derrubada, e a resposta divergente vale mais que a coincidente.",
]:
    E.append(item(t))

E.append(Spacer(1, 0.2 * cm))
E.append(Paragraph(
    "Os n\u00fameros citados (quantidade de equipamentos, tempos, percentuais) s\u00e3o estimativas "
    "de ordem de grandeza, colocadas para que o grupo saiba quais perguntas precisam terminar com um "
    "n\u00famero na m\u00e3o. Todos devem ser substitu\u00eddos pelos valores reais na vers\u00e3o "
    "preenchida em campo.", S_NOTA))

E.append(PageBreak())

# ============================================================== A
E.append(secao("A. O processo hoje", 8))
E.append(pergunta(
    "Descreva o caminho de um equipamento na empresa, de quando ele chega at\u00e9 quando sai de uso, "
    "dizendo quem registra o qu\u00ea em cada etapa.",
    "Mapear o processo atual por inteiro, e n\u00e3o apenas a etapa problem\u00e1tica.",
    "Chega pela portaria com a nota fiscal, o almoxarifado confere e avisa o patrim\u00f4nio, que "
    "lan\u00e7a o item na planilha e cola a etiqueta com o n\u00famero. Dali em diante o equipamento "
    "\u00e9 entregue ao setor que pediu e sai do controle: s\u00f3 reaparece quando quebra ou na "
    "confer\u00eancia anual. Quando para de servir, fica encostado no dep\u00f3sito, e a baixa "
    "cont\u00e1bil sai meses depois."))
E.append(pergunta(
    "Quando algu\u00e9m precisa encontrar um equipamento hoje, o que faz na pr\u00e1tica? Quanto tempo "
    "costuma levar?",
    "Medir o tempo de consulta atual, que \u00e9 a dor central apontada na demanda.",
    "Pergunta para o colega, e se ningu\u00e9m souber sai andando de setor em setor. Item port\u00e1til "
    "leva de quinze a quarenta minutos. J\u00e1 houve caso de perder meio per\u00edodo procurando um "
    "instrumento que estava na sala ao lado."))
E.append(pergunta(
    "Em que pontos surgem as diverg\u00eancias entre documentos e setores? Como se descobre que um "
    "documento est\u00e1 errado?",
    "Chegar \u00e0 causa raiz, e n\u00e3o ao sintoma relatado.",
    "A diverg\u00eancia nasce na movimenta\u00e7\u00e3o entre setores, que acontece sem aviso ao "
    "patrim\u00f4nio. O erro s\u00f3 aparece na confer\u00eancia anual ou quando o auditor pede o "
    "item, e a\u00ed se descobre equipamento que mudou de setor h\u00e1 dois anos."))

# ============================================================== B
E.append(secao("B. Os equipamentos", 8))
E.append(pergunta(
    "Quantos equipamentos a empresa controla hoje, aproximadamente, e quantos entram e saem por ano?",
    "Dimensionar o problema e o crescimento.",
    "Algo em torno de 380 itens controlados, dos quais uns 120 s\u00e3o de laborat\u00f3rio. Entram "
    "de 30 a 40 por ano e saem de 10 a 15."))
E.append(pergunta(
    "Quais tipos de equipamento d\u00e3o mais trabalho para controlar, e quais quase n\u00e3o d\u00e3o "
    "problema?",
    "Deixar a empresa apontar a prioridade, em vez de o grupo escolher por conta pr\u00f3pria.",
    "Os port\u00e1teis d\u00e3o todo o trabalho: mult\u00edmetro, paqu\u00edmetro, torqu\u00edmetro, "
    "furadeira, instrumento de calibra\u00e7\u00e3o. M\u00e1quina grande fica fixa no lugar, e dessa "
    "ningu\u00e9m tem d\u00favida de onde est\u00e1."))
E.append(pergunta(
    "Como um equipamento \u00e9 identificado fisicamente hoje? O que est\u00e1 escrito nessa "
    "identifica\u00e7\u00e3o, e com que frequ\u00eancia ela se apaga, solta ou desaparece?",
    "Conhecer a identifica\u00e7\u00e3o existente e quanto ela resiste ao uso.",
    "Etiqueta adesiva de patrim\u00f4nio com o n\u00famero e a sigla da empresa. No laborat\u00f3rio "
    "ela dura anos. Na f\u00e1brica, com \u00f3leo, calor e lavagem semanal, descola ou apaga: uns 15 "
    "a 20 por cento dos itens est\u00e3o hoje sem etiqueta leg\u00edvel."))
E.append(pergunta(
    '<font color="%s">[Quadro 1]</font> Que informa\u00e7\u00f5es a empresa precisa saber sobre um '
    'equipamento, e como voc\u00eas chamam cada uma delas?' % VERMELHO.hexval(),
    "Levantar a informa\u00e7\u00e3o que o neg\u00f3cio exige, com o vocabul\u00e1rio da casa."))
E.append(quadro(
    ["Informa\u00e7\u00e3o", "Essencial", "\u00datil", "N\u00e3o usamos",
     "Como voc\u00eas chamam"],
    [["Nome do equipamento", "X", "", "", "descri\u00e7\u00e3o"],
     ["N\u00famero de identifica\u00e7\u00e3o", "X", "", "", "patrim\u00f4nio, ou n\u00famero do bem"],
     ["Tipo ou categoria", "", "X", "", "fam\u00edlia"],
     ["Setor respons\u00e1vel", "X", "", "", "centro de custo"],
     ["Local exato dentro do setor", "X", "", "", "posi\u00e7\u00e3o, ou bancada"],
     ["N\u00famero de s\u00e9rie", "X", "", "", "s\u00e9rie do fabricante"],
     ["Fabricante e modelo", "", "X", "", "marca e modelo"],
     ["Data e valor de aquisi\u00e7\u00e3o", "X", "", "",
      "valor de aquisi\u00e7\u00e3o; s\u00f3 a contabilidade enxerga"],
     ["Pessoa respons\u00e1vel pelo bem", "X", "", "", "detentor"],
     ["Hist\u00f3rico de manuten\u00e7\u00e3o", "X", "", "", "ficha do equipamento"],
     ["Pr\u00f3xima calibra\u00e7\u00e3o ou inspe\u00e7\u00e3o", "X", "", "",
      "vencimento da calibra\u00e7\u00e3o"]],
    [LARG * 0.31, LARG * 0.12, LARG * 0.10, LARG * 0.12, LARG * 0.35], resp_de=1))
E.append(Spacer(1, 0.3 * cm))

# ============================================================== C
E.append(secao("C. O ambiente de trabalho", 8))
E.append(pergunta(
    "A que os equipamentos e as \u00e1reas ficam expostos no dia a dia: \u00f3leo, calor, vapor, "
    "produto qu\u00edmico, lavagem, poeira?",
    "Conhecer as condi\u00e7\u00f5es f\u00edsicas que qualquer registro em campo ter\u00e1 de "
    "suportar.",
    "No laborat\u00f3rio o ambiente \u00e9 limpo e climatizado. Na f\u00e1brica h\u00e1 \u00f3leo, "
    "cavaco, calor perto da solda e lavagem com desengraxante uma vez por semana. Poeira o ano "
    "inteiro."))
E.append(pergunta(
    "Quando algu\u00e9m precisa anotar ou consultar alguma coisa estando na \u00e1rea, o que usa hoje? "
    "H\u00e1 restri\u00e7\u00e3o ao uso de telefone celular em alguma \u00e1rea?",
    "Entender como a informa\u00e7\u00e3o circula no ch\u00e3o de f\u00e1brica e que regras existem.",
    "Caderneta de bolso e o quadro branco do setor. Celular \u00e9 permitido na maior parte da planta, "
    "menos na cabine de pintura e na \u00e1rea de solda, onde s\u00f3 entra r\u00e1dio. O aparelho "
    "\u00e9 pessoal, a empresa n\u00e3o fornece, e boa parte do pessoal trabalha de luva."))
E.append(pergunta(
    "H\u00e1 lugares da empresa onde n\u00e3o chega rede sem fio ou sinal de telefone?",
    "Mapear as limita\u00e7\u00f5es de comunica\u00e7\u00e3o da planta.",
    "Escrit\u00f3rio e laborat\u00f3rio t\u00eam wi-fi est\u00e1vel. No fundo do galp\u00e3o o sinal "
    "cai, e no dep\u00f3sito externo n\u00e3o chega nem wi-fi nem telefone."))
E.append(pergunta(
    "Quem, de fora do setor ou de fora da empresa, precisa consultar informa\u00e7\u00e3o sobre um "
    "equipamento? Auditor, cliente, fornecedor, contabilidade?",
    "Identificar todos os interessados na informa\u00e7\u00e3o, e n\u00e3o s\u00f3 quem a produz.",
    "O auditor da ISO pede o certificado de calibra\u00e7\u00e3o, a contabilidade pede a "
    "rela\u00e7\u00e3o de bens uma vez por ano para o balan\u00e7o, e h\u00e1 cliente que pede o "
    "certificado do instrumento usado no ensaio dele."))

# ============================================================== D
E.append(secao("D. Movimenta\u00e7\u00e3o, baixa e corre\u00e7\u00e3o", 9))
E.append(pergunta(
    "Quando um equipamento muda de setor, o que acontece hoje? Quem decide, quem precisa autorizar "
    "antes e quem fica sabendo depois?",
    "Descobrir a regra de circula\u00e7\u00e3o e quem responde por ela.",
    "Na pr\u00e1tica a pessoa pega e leva. A regra \u00e9 avisar o supervisor da \u00e1rea, e para o "
    "item sair do pr\u00e9dio precisa de autoriza\u00e7\u00e3o do gerente de opera\u00e7\u00f5es. O "
    "aviso costuma ser por WhatsApp, e o patrim\u00f4nio quase nunca fica sabendo. \u00c9 exatamente "
    "a\u00ed que o controle se perde."))
E.append(pergunta(
    "Depois que a mudan\u00e7a aconteceu, o que a empresa precisa saber sobre ela: de onde saiu, para "
    "onde foi, quem levou, quando, por qu\u00ea, em que estado?",
    "Levantar o que o neg\u00f3cio exige que fique registrado de cada movimenta\u00e7\u00e3o.",
    "De onde saiu, para onde foi, quem levou e quando. O motivo \u00e9 \u00fatil, mas o que falta "
    "mesmo \u00e9 o estado: h\u00e1 equipamento que volta danificado e ningu\u00e9m assume, porque "
    "n\u00e3o existe registro de como ele saiu."))
E.append(pergunta(
    '<font color="%s">[Quadro 2]</font> Em que situa\u00e7\u00f5es um equipamento pode estar no dia a '
    'dia? Alguma delas impede que ele seja levado para outro setor?' % VERMELHO.hexval(),
    "Obter o vocabul\u00e1rio e as regras da empresa, sem sugerir uma lista pronta."))
E.append(quadro(
    ["Situa\u00e7\u00e3o, na palavra de voc\u00eas", "Pode ir para outro setor? (S/N)",
     "Quem registra essa situa\u00e7\u00e3o"],
    [["Em uso", "Sim, avisando o supervisor", "Supervisor da \u00e1rea, quando \u00e9 avisado"],
     ["Parado, ou encostado", "Sim", "Ningu\u00e9m registra hoje"],
     ["Em manuten\u00e7\u00e3o", "N\u00e3o", "Supervisor da manuten\u00e7\u00e3o"],
     ["Em calibra\u00e7\u00e3o, fora da empresa",
      "N\u00e3o, est\u00e1 com o laborat\u00f3rio externo", "Coordena\u00e7\u00e3o da qualidade"],
     ["Em obra, na planta do cliente", "Sim, com autoriza\u00e7\u00e3o do gerente",
      "Gerente de opera\u00e7\u00f5es"],
     ["Baixado, ou sucateado", "N\u00e3o", "Patrim\u00f4nio, junto com a contabilidade"]],
    [LARG * 0.34, LARG * 0.30, LARG * 0.36], resp_de=0))
E.append(Spacer(1, 0.3 * cm))
E.append(pergunta(
    "Como um equipamento sai de uso definitivamente: quais motivos existem, quem autoriza e a empresa "
    "ainda precisa consultar esse equipamento depois?",
    "Conhecer o encerramento do ciclo de vida e a necessidade de consulta posterior.",
    "Sai por quebra sem conserto vi\u00e1vel, por obsolesc\u00eancia ou por venda. Autoriza o gerente "
    "de opera\u00e7\u00f5es, e a contabilidade d\u00e1 a baixa. E sim, o hist\u00f3rico do bem baixado "
    "continua sendo consultado: a auditoria pede, e a contabilidade guarda por cinco anos."))
E.append(pergunta(
    "Quando algu\u00e9m registra uma informa\u00e7\u00e3o errada, o que se faz hoje? O registro antigo "
    "\u00e9 corrigido, apagado, ou fica marcado de alguma forma?",
    "Descobrir a pr\u00e1tica e a exig\u00eancia da empresa quanto \u00e0 corre\u00e7\u00e3o de "
    "registros.",
    "Na planilha corrige por cima, e o valor antigo se perde. Em documento da qualidade n\u00e3o pode: "
    "risca, escreve ao lado, assina e data. O procedimento da ISO exige enxergar quem mudou o qu\u00ea "
    "e quando."))

# ============================================================== E
E.append(secao("E. Responsabilidades", 5))
E.append(pergunta(
    "Quem responde por um equipamento: o setor, uma pessoa, o gestor da \u00e1rea? E quando ele some, "
    "quem \u00e9 cobrado?",
    "Identificar a responsabilidade real, que costuma diferir da formal.",
    "No papel responde o setor, pelo centro de custo. Quando some, quem \u00e9 cobrado \u00e9 o "
    "supervisor da \u00e1rea, e ele cobra o \u00faltimo que pegou, se algu\u00e9m lembrar quem foi. "
    "Como n\u00e3o h\u00e1 registro, sobra para quem estava por perto."))
E.append(pergunta(
    '<font color="%s">[Quadro 3]</font> Nas atividades abaixo, quem executa, quem autoriza e quem '
    'precisa ficar sabendo?' % VERMELHO.hexval(),
    "Mapear pap\u00e9is a partir do processo atual, e n\u00e3o de perfis propostos pelo grupo."))
E.append(quadro(
    ["Atividade", "Quem executa", "Quem autoriza", "Quem fica sabendo"],
    [["Registrar a chegada de um equipamento novo", "Almoxarifado, depois patrim\u00f4nio",
      "Compras", "Setor solicitante"],
     ["Levar um equipamento para outro setor", "O pr\u00f3prio t\u00e9cnico",
      "Supervisor da \u00e1rea, quando \u00e9 avisado", "Ningu\u00e9m, na pr\u00e1tica"],
     ["Emprestar um equipamento a outra pessoa", "Quem est\u00e1 com o item",
      "Ningu\u00e9m, \u00e9 no combinado", "Ningu\u00e9m"],
     ["Tirar um equipamento de uso", "Manuten\u00e7\u00e3o",
      "Gerente de opera\u00e7\u00f5es", "Patrim\u00f4nio e contabilidade"],
     ["Chamar a manuten\u00e7\u00e3o", "Operador, ou o pr\u00f3prio t\u00e9cnico",
      "Supervisor da \u00e1rea", "Supervisor da manuten\u00e7\u00e3o"],
     ["Encerrar uma manuten\u00e7\u00e3o", "T\u00e9cnico que atendeu",
      "Supervisor da manuten\u00e7\u00e3o", "Setor solicitante"],
     ["Conferir o patrim\u00f4nio", "Auxiliar de patrim\u00f4nio",
      "Contabilidade", "Diretoria"],
     ["Descobrir onde um equipamento est\u00e1", "Quem precisa dele",
      "N\u00e3o se aplica", "Ningu\u00e9m"]],
    [LARG * 0.32, LARG * 0.24, LARG * 0.22, LARG * 0.22], resp_de=1))
E.append(Spacer(1, 0.3 * cm))
E.append(pergunta(
    "Existe informa\u00e7\u00e3o sobre os equipamentos que nem todo mundo pode ver?",
    "Levantar restri\u00e7\u00f5es de acesso que a empresa j\u00e1 pratica.",
    "Valor de aquisi\u00e7\u00e3o e deprecia\u00e7\u00e3o ficam com a contabilidade e n\u00e3o "
    "circulam. Contrato de comodato de cliente tamb\u00e9m \u00e9 restrito. Localiza\u00e7\u00e3o, "
    "estado e hist\u00f3rico de manuten\u00e7\u00e3o podem ser vistos por qualquer funcion\u00e1rio."))

# ============================================================== F
E.append(secao("F. Manuten\u00e7\u00e3o", 8))
E.append(pergunta(
    "Descreva o que acontece desde a hora em que um equipamento apresenta problema at\u00e9 voltar a "
    "funcionar: quem participa e por quais etapas passa.",
    "Mapear o fluxo de manuten\u00e7\u00e3o com as etapas que a empresa reconhece.",
    "O operador avisa o supervisor, que chama a manuten\u00e7\u00e3o pessoalmente ou por WhatsApp. O "
    "t\u00e9cnico avalia: se resolve na hora, resolve; se precisa de pe\u00e7a, abre "
    "requisi\u00e7\u00e3o e o equipamento fica parado esperando. A ordem de servi\u00e7o em papel "
    "\u00e9 preenchida quando d\u00e1 tempo, e boa parte n\u00e3o volta preenchida."))
E.append(pergunta(
    "O que a empresa precisa ter registrado sobre cada manuten\u00e7\u00e3o realizada? E como se sabe "
    "hoje o que j\u00e1 foi feito em um equipamento?",
    "Levantar a exig\u00eancia de hist\u00f3rico e a pr\u00e1tica atual de consulta.",
    "O que foi feito, a pe\u00e7a trocada, quem executou, a data e quanto tempo a m\u00e1quina ficou "
    "parada. Hoje s\u00f3 se sabe o que j\u00e1 foi feito perguntando ao t\u00e9cnico que atendeu, ou "
    "olhando a caderneta dele. Se ele est\u00e1 de f\u00e9rias, ningu\u00e9m sabe."))
E.append(pergunta(
    "Existe manuten\u00e7\u00e3o, calibra\u00e7\u00e3o ou inspe\u00e7\u00e3o com prazo "
    "obrigat\u00f3rio? O que acontece se o prazo vencer?",
    "Descobrir obriga\u00e7\u00f5es de prazo, comuns em laborat\u00f3rio e frequentemente "
    "esquecidas.",
    "Sim. Instrumento de medi\u00e7\u00e3o tem calibra\u00e7\u00e3o anual, exig\u00eancia da ISO 9001, "
    "e h\u00e1 inspe\u00e7\u00e3o peri\u00f3dica em equipamento el\u00e9trico. Vencido, o instrumento "
    "n\u00e3o pode ser usado em ensaio, e se o auditor encontra vira n\u00e3o conformidade. Na "
    "\u00faltima auditoria apareceram quatro vencidos."))
E.append(pergunta(
    "Entre saber sempre onde cada equipamento est\u00e1 e ter o hist\u00f3rico de manuten\u00e7\u00e3o "
    "organizado, o que resolveria mais dor primeiro?",
    "Deixar a empresa ordenar as prioridades, em vez de o grupo supor a ordem.",
    "Localiza\u00e7\u00e3o primeiro. O hist\u00f3rico incomoda, mas o que para a produ\u00e7\u00e3o "
    "\u00e9 n\u00e3o achar o equipamento. O hist\u00f3rico pode vir na sequ\u00eancia, desde que o "
    "controle de calibra\u00e7\u00e3o entre junto."))

# ============================================================== G
E.append(secao("G. Registros e regras da empresa", 5))
E.append(pergunta(
    "Existe hoje alguma planilha, caderno ou sistema em que essas informa\u00e7\u00f5es j\u00e1 "
    "estejam registradas? Quem cuida, e o quanto se pode confiar no que est\u00e1 l\u00e1?",
    "Conhecer o registro existente e sua confiabilidade.",
    "Uma planilha do Excel na rede, com cerca de 380 linhas, mantida pelo auxiliar de "
    "patrim\u00f4nio. Confi\u00e1vel para n\u00famero, descri\u00e7\u00e3o e valor. Nada "
    "confi\u00e1vel para setor e localiza\u00e7\u00e3o: pelo menos um ter\u00e7o est\u00e1 "
    "desatualizado. Existem tamb\u00e9m as cadernetas dos t\u00e9cnicos, que ningu\u00e9m mais "
    "l\u00ea."))
E.append(pergunta(
    "Que informa\u00e7\u00e3o sobre os equipamentos a dire\u00e7\u00e3o cobra, com que frequ\u00eancia, "
    "e como ela \u00e9 produzida hoje?",
    "Descobrir a presta\u00e7\u00e3o de contas que j\u00e1 existe e quanto custa produzi-la.",
    "A rela\u00e7\u00e3o de bens por centro de custo, uma vez por ano para o balan\u00e7o, e o mapa de "
    "calibra\u00e7\u00f5es a vencer antes de cada auditoria. Tudo montado \u00e0 m\u00e3o, o que leva "
    "de dois a tr\u00eas dias de trabalho."))
E.append(pergunta(
    "Existe regra da empresa sobre onde as informa\u00e7\u00f5es podem ficar guardadas, ou sobre "
    "informa\u00e7\u00e3o da empresa sair da rede interna?",
    "Levantar a restri\u00e7\u00e3o de confidencialidade antes de qualquer proposta.",
    "Pol\u00edtica escrita n\u00e3o existe. O que existe \u00e9 a orienta\u00e7\u00e3o da diretoria de "
    "que dado da empresa n\u00e3o vai para servi\u00e7o de nuvem sem aprova\u00e7\u00e3o, e "
    "informa\u00e7\u00e3o de cliente n\u00e3o sai daqui de jeito nenhum. Qualquer hospedagem externa "
    "passa pelo respons\u00e1vel de TI."))

# ============================================================== H
E.append(secao("H. Sucesso e pr\u00f3ximos passos", 7))
E.append(pergunta(
    "O que precisaria mudar no dia a dia para a empresa considerar esse problema resolvido? Qual "
    "seria o n\u00famero aceit\u00e1vel, comparado ao de hoje?",
    "Definir o crit\u00e9rio de sucesso e a meta com as palavras do cliente.",
    "Localizar qualquer equipamento em menos de cinco minutos, sem sair perguntando, contra os trinta "
    "de hoje. E chegar na auditoria com zero calibra\u00e7\u00e3o vencida, contra quatro na "
    "\u00faltima. Se a confer\u00eancia anual deixar de tomar uma semana, melhor ainda."))
E.append(pergunta(
    "Quem acompanha esse assunto do lado de voc\u00eas, com que frequ\u00eancia podemos conversar, e "
    "seria poss\u00edvel visitar a \u00e1rea e conversar alguns minutos com um t\u00e9cnico de "
    "manuten\u00e7\u00e3o?",
    "Estabelecer o ponto focal e viabilizar a observa\u00e7\u00e3o em campo, que sustenta a persona.",
    "A coordena\u00e7\u00e3o da qualidade acompanha e responde por e-mail. Conversa a cada quinze dias "
    "\u00e9 vi\u00e1vel. Visita \u00e0 \u00e1rea pode ser agendada em uma manh\u00e3, com "
    "integra\u00e7\u00e3o de seguran\u00e7a e EPI emprestado, e d\u00e1 para acompanhar um "
    "t\u00e9cnico durante o atendimento."))
E.append(pergunta(
    "Existe algo importante sobre esse problema que n\u00e3o perguntamos?",
    "Abrir espa\u00e7o para o que o roteiro n\u00e3o previu. Costuma ser a resposta mais \u00fatil da "
    "entrevista.",
    "Duas coisas. A primeira \u00e9 a ferramenta que \u00e9 do pr\u00f3prio funcion\u00e1rio e n\u00e3o "
    "da empresa, que \u00e9 boa parte do que circula na bancada e n\u00e3o entra em controle nenhum. "
    "A segunda \u00e9 o equipamento que fica meses na obra do cliente: \u00e9 o que mais se perde, e "
    "ningu\u00e9m acompanha."))

E.append(Spacer(1, 0.15 * cm))
E.append(Paragraph(
    "<b>Observa\u00e7\u00e3o sobre a \u00faltima resposta.</b> A pergunta 29 \u00e9 a que tem maior "
    "chance de trazer algo que o grupo n\u00e3o previu, e por isso a hip\u00f3tese escrita acima "
    "\u00e9 justamente a menos confi\u00e1vel de todo o documento. Se a resposta real for diferente, "
    "ela provavelmente muda o escopo, e precisa ir para o Quadro C da s\u00edntese.", S_NOTA))

# ============================================================== sintese
E.append(PageBreak())
E.append(Paragraph("S\u00edntese p\u00f3s-entrevista, preenchida com as hip\u00f3teses", S_SECAO))
E.append(Paragraph(
    "Os quadros abaixo est\u00e3o preenchidos com o desfecho que o grupo espera, considerando as "
    "respostas previstas nas p\u00e1ginas anteriores. Na s\u00edntese real, feita em at\u00e9 vinte e "
    "quatro horas ap\u00f3s a conversa, cada c\u00e9lula deve ser reescrita com o que a empresa "
    "efetivamente disse.", S_TXT))

E.append(Paragraph("Quadro A \u2013 Revis\u00e3o da matriz CSD", S_SUBSECAO))
E.append(quadro(
    ["Suposi\u00e7\u00e3o registrada pelo grupo", "Confirmada ou rejeitada",
     "O que a empresa disse"],
    [["O t\u00e9cnico consulta junto \u00e0 m\u00e1quina, pelo celular",
      "Confirmada em parte",
      "J\u00e1 fotografa a etiqueta com o pr\u00f3prio celular, mas trabalha em p\u00e9 e de luva"],
     ["Todo t\u00e9cnico tem celular e pode us\u00e1-lo na \u00e1rea",
      "Rejeitada em parte",
      "Aparelho \u00e9 pessoal, a empresa n\u00e3o fornece, e pintura e solda proibem"],
     ["Uma etiqueta sobrevive a \u00f3leo, calor e atrito",
      "Rejeitada",
      "De 15 a 20 por cento das etiquetas est\u00e3o ileg\u00edveis ou ausentes"],
     ["H\u00e1 rede sem fio ou sinal no ch\u00e3o de f\u00e1brica",
      "Confirmada em parte",
      "Laborat\u00f3rio e escrit\u00f3rio sim; fundo do galp\u00e3o e dep\u00f3sito externo n\u00e3o"],
     ["A persona M\u00e1rcio corresponde ao t\u00e9cnico real",
      "Confirmada",
      "Caderneta de bolso, pergunta ao colega e foto do patrim\u00f4nio se confirmam"],
     ["Nova suposi\u00e7\u00e3o surgida na entrevista: o maior extravio est\u00e1 no equipamento em "
      "obra de cliente e na ferramenta particular",
      "A investigar",
      "Surgiu na pergunta 29 e n\u00e3o estava no detalhamento escrito da demanda"]],
    [LARG * 0.38, LARG * 0.20, LARG * 0.42], resp_de=1))

E.append(Spacer(1, 0.3 * cm))
E.append(Paragraph("Quadro B \u2013 Do que foi dito ao que ser\u00e1 constru\u00eddo", S_SUBSECAO))
E.append(quadro(
    ["Decis\u00e3o de projeto", "O que a resposta determinou", "Pergunta de origem"],
    [["Ordem de trabalho: localiza\u00e7\u00e3o ou manuten\u00e7\u00e3o primeiro",
      "Localiza\u00e7\u00e3o na primeira entrega; hist\u00f3rico e controle de calibra\u00e7\u00e3o "
      "na segunda",
      "23, 22"],
     ["Equipamentos contemplados na primeira etapa",
      "Os port\u00e1teis e os instrumentos de laborat\u00f3rio, cerca de 120 dos 380 itens",
      "4, 5"],
     ["Regra de circula\u00e7\u00e3o entre setores",
      "Movimenta\u00e7\u00e3o registrada pelo pr\u00f3prio t\u00e9cnico; sa\u00edda do pr\u00e9dio "
      "exige autoriza\u00e7\u00e3o do gerente; item em manuten\u00e7\u00e3o ou calibra\u00e7\u00e3o "
      "n\u00e3o circula",
      "12, 14"],
     ["Consulta por quem n\u00e3o tem acesso ao sistema",
      "Consulta somente leitura pela etiqueta, sem login, e sem o valor de aquisi\u00e7\u00e3o "
      "\u00e0 vista",
      "11, 19"],
     ["Aproveitamento do registro que j\u00e1 existe",
      "Importar a planilha de 380 linhas: n\u00famero e valor entram como est\u00e3o, setor e local "
      "entram marcados como a conferir",
      "24"],
     ["Onde a informa\u00e7\u00e3o pode ficar guardada",
      "Hospedagem externa depende de aprova\u00e7\u00e3o do TI, e o registro precisa funcionar sem "
      "rede no dep\u00f3sito externo",
      "10, 26"]],
    [LARG * 0.30, LARG * 0.55, LARG * 0.15], resp_de=1))

E.append(Spacer(1, 0.3 * cm))
E.append(Paragraph("Quadro C \u2013 Pend\u00eancias", S_SUBSECAO))
E.append(quadro(
    ["O que ficou em aberto ou precisa mudar no projeto", "Respons\u00e1vel", "Prazo"],
    [["Confirmar com o respons\u00e1vel de TI se hospedagem fora da rede interna \u00e9 permitida",
      "Rafael Brecci", "Pr\u00f3xima reuni\u00e3o"],
     ["Levantar quantos itens est\u00e3o sem etiqueta leg\u00edvel e testar material resistente a "
      "\u00f3leo e calor",
      "Enzo Avanze e Nicolas Fernandes", "Pr\u00f3xima reuni\u00e3o"],
     ["Definir como o t\u00e9cnico registra movimenta\u00e7\u00e3o nas \u00e1reas em que o celular "
      "\u00e9 proibido",
      "Julia Possebon e Vitor Canali", "Antes da Idea\u00e7\u00e3o"],
     ["Decidir se equipamento em obra de cliente entra no escopo da primeira entrega",
      "Grupo, com a coordena\u00e7\u00e3o da qualidade", "Antes de fechar o escopo"],
     ["Obter c\u00f3pia da planilha atual para avaliar a importa\u00e7\u00e3o",
      "Felipe Bertaco", "At\u00e9 a visita \u00e0 \u00e1rea"],
     ["Agendar a visita \u00e0 \u00e1rea e o acompanhamento de um t\u00e9cnico",
      "Jo\u00e3o Pedro Tomazini", "At\u00e9 a pr\u00f3xima aula"]],
    [LARG * 0.52, LARG * 0.28, LARG * 0.20], resp_de=1))

E.append(Spacer(1, 0.4 * cm))
E.append(Paragraph(
    "Elaborado pelo GR4, turma 2IEDS, 2026: Enzo Avanze, Felipe Bertaco Cruz, Jo\u00e3o Pedro "
    "Tomazini Rodrigues, Julia Possebon Leme, Nicolas Fernandes Santos, Rafael Brecci de Souza e "
    "Vitor Matheus Canali Pereira. Vers\u00e3o de ensaio do roteiro de entrevista "
    "explorat\u00f3ria: as respostas s\u00e3o hip\u00f3teses do grupo, e n\u00e3o "
    "declara\u00e7\u00f5es da empresa.", S_NOTA))

# ----------------------------------------------------------------- monta
doc = BaseDocTemplate(
    SAIDA, pagesize=A4,
    leftMargin=MARG_E, rightMargin=MARG_D, topMargin=MARG_S, bottomMargin=MARG_I,
    title="Roteiro de Entrevista Exploratoria, respostas esperadas - GR4 - Demanda 12569",
    author="GR4 - Turma 2IEDS")
frame = Frame(MARG_E, MARG_I, LARG, A4[1] - MARG_S - MARG_I, id="corpo",
              leftPadding=0, rightPadding=0, topPadding=0, bottomPadding=0)
doc.addPageTemplates([PageTemplate(id="padrao", frames=[frame], onPage=moldura)])
doc.build(E)
print("gerado: %s | perguntas: %d" % (SAIDA, contador[0]))
