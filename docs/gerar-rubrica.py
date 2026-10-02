# -*- coding: utf-8 -*-
"""
Gera a rubrica da Etapa 1 e as fichas de autoavaliacao do GR4 (demanda 12569).
Uso: python gerar-rubrica.py [saida.pdf]

O plano de ensino da UC Projetos de Software nomeia como instrumentos de
avaliacao da primeira etapa a rubrica e a autoavaliacao e avaliacao da equipe.
Este documento e esse instrumento, com quatro partes:

  1. Escala de desenvolvimento, transcrita do plano de ensino.
  2. Rubrica dos quatro criterios da etapa, com descritor para cada nivel, de
     modo que a nota atribuida tenha referencia e nao impressao.
  3. Evidencias por criterio, com o lugar exato onde cada uma esta no
     repositorio. Esta parte vem preenchida: ela e o inventario do que o grupo
     produziu, e o que falta aparece como pendencia declarada.
  4. Autoavaliacao individual das capacidades socioemocionais e avaliacao
     cruzada da equipe, uma via por integrante.

Os niveis nao vem assinalados. Quem avalia e o grupo e o docente, nao o gerador.
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
                                Spacer, Table, TableStyle, Flowable, KeepTogether,
                                PageBreak)

SAIDA = sys.argv[1] if len(sys.argv) > 1 else "09 - Rubrica_e_Autoavaliacao_GR4.pdf"

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
CINZA     = colors.HexColor("#808080")
LINHA     = colors.HexColor("#9C9C9C")

MARG_E, MARG_D, MARG_S, MARG_I = 2.5 * cm, 2.0 * cm, 2.3 * cm, 2.0 * cm
LARG = A4[0] - MARG_E - MARG_D

INTEGRANTES = [
    "Enzo Avanze",
    "Felipe Bertaco Cruz",
    "Jo\u00e3o Pedro Tomazini Rodrigues",
    "Julia Possebon Leme",
    "Nicolas Fernandes Santos",
    "Rafael Brecci de Souza",
    "Vitor Matheus Canali Pereira",
]

CAPACIDADES = [
    "Autogest\u00e3o",
    "Pensamento anal\u00edtico",
    "Intelig\u00eancia emocional",
    "Autonomia",
    "Resili\u00eancia emocional",
    "Trabalho em equipe",
    "Criatividade e inova\u00e7\u00e3o",
]


# ----------------------------------------------------------------- estilos
def est(nome, **kw):
    base = dict(name=nome, fontName=F, fontSize=9.5, leading=13, spaceAfter=0)
    base.update(kw)
    return ParagraphStyle(**base)


S_TITULO   = est("titulo", fontName=FB, fontSize=19, leading=23, alignment=TA_CENTER,
                 textColor=AZUL, spaceAfter=8)
S_SUB      = est("sub", fontSize=10.5, leading=15, alignment=TA_CENTER, spaceAfter=3)
S_SECAO    = est("secao", fontName=FB, fontSize=11.5, leading=15, textColor=VERMELHO,
                 spaceBefore=13, spaceAfter=5)
S_SUBSECAO = est("subsecao", fontName=FB, fontSize=10, leading=13, textColor=AZUL,
                 spaceBefore=8, spaceAfter=4)
S_TXT      = est("txt", alignment=TA_JUSTIFY, spaceAfter=5)
S_NOTA     = est("nota", fontSize=8.5, leading=11.5, alignment=TA_JUSTIFY,
                 textColor=colors.HexColor("#404040"), spaceAfter=4)
S_CEL      = est("cel", fontSize=8.5, leading=11)
S_CELP     = est("celp", fontSize=8, leading=10.5)
S_CELB     = est("celb", fontName=FB, fontSize=8.5, leading=11, textColor=AZUL)
S_PESO     = est("peso", fontName=FB, fontSize=8.5, leading=11, alignment=TA_CENTER,
                 textColor=VERMELHO)


class Linhas(Flowable):
    def __init__(self, n=2, largura=LARG, esp=0.52 * cm, recuo=0.0 * cm):
        Flowable.__init__(self)
        self.n, self.largura, self.esp, self.recuo = n, largura, esp, recuo
        self.width = largura
        self.height = esp * n + 0.10 * cm

    def draw(self):
        c = self.canv
        c.setStrokeColor(LINHA)
        c.setLineWidth(0.4)
        y = self.height - self.esp + 0.12 * cm
        for _ in range(self.n):
            c.line(self.recuo, y, self.largura, y)
            y -= self.esp


class Assinatura(Flowable):
    def __init__(self, rotulo, largura=LARG * 0.46):
        Flowable.__init__(self)
        self.rotulo = rotulo
        self.width = largura
        self.height = 1.15 * cm

    def draw(self):
        c = self.canv
        c.setStrokeColor(colors.black)
        c.setLineWidth(0.6)
        c.line(0, 0.52 * cm, self.width, 0.52 * cm)
        c.setFont(F, 8)
        c.setFillColor(colors.HexColor("#404040"))
        c.drawCentredString(self.width / 2.0, 0.22 * cm, self.rotulo)


def moldura(canvas, doc):
    canvas.saveState()
    canvas.setFont(FB, 9)
    canvas.setFillColor(VERMELHO)
    canvas.drawString(MARG_E, A4[1] - 1.5 * cm, "GR4 | 2IEDS")
    canvas.setFont(F, 7.5)
    canvas.setFillColor(CINZA)
    canvas.drawRightString(A4[0] - MARG_D, A4[1] - 1.5 * cm,
                           "SENAI | Projetos de Software \u00b7 Rubrica da Etapa 1")
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


def quadro(cabecalho, linhas_dados, larguras, folga=12, estilo_cel=S_CEL):
    dados = [[Paragraph(c, S_CELB) for c in cabecalho]]
    for lin in linhas_dados:
        dados.append([Paragraph(c, estilo_cel) if c else "" for c in lin])
    t = Table(dados, colWidths=larguras, repeatRows=1)
    t.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), AZULCLARO),
        ("GRID", (0, 0), (-1, -1), 0.5, colors.HexColor("#7F9DB9")),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("LEFTPADDING", (0, 0), (-1, -1), 4),
        ("RIGHTPADDING", (0, 0), (-1, -1), 4),
        ("TOPPADDING", (0, 0), (-1, 0), 4),
        ("BOTTOMPADDING", (0, 0), (-1, 0), 4),
        ("TOPPADDING", (0, 1), (-1, -1), 3),
        ("BOTTOMPADDING", (0, 1), (-1, -1), folga),
    ]))
    return t


NIVEIS = ["N\u00e3o desenvolvido<br/>0% a 25%",
          "Em desenvolvimento<br/>26% a 50%",
          "Parcialmente desenvolvido<br/>51% a 75%",
          "Desenvolvido<br/>76% a 90%",
          "Plenamente desenvolvido<br/>91% a 100%"]


def criterio(titulo, peso, descritores):
    """Um criterio da rubrica: cinco niveis, um descritor cada, coluna para marcar."""
    cab = Paragraph('%s <font color="%s">\u00b7 peso %s</font>'
                    % (titulo, VERMELHO.hexval(), peso), S_SUBSECAO)
    linhas = [[NIVEIS[i], descritores[i], ""] for i in range(5)]
    t = quadro(["N\u00edvel", "O que caracteriza este n\u00edvel", "Marque"],
               linhas, [LARG * 0.20, LARG * 0.68, LARG * 0.12],
               folga=4, estilo_cel=S_CELP)
    return KeepTogether([cab, t, Spacer(1, 6)])


E = []

# ============================================================== abertura
E += [
    Spacer(1, 0.2 * cm),
    Paragraph("RUBRICA E AUTOAVALIA\u00c7\u00c3O DA ETAPA 1", S_TITULO),
    Paragraph("Grupo GR4 \u00b7 Demanda n\u00ba 12569, Gest\u00e3o de Laborat\u00f3rios e Ativos",
              S_SUB),
    Paragraph("UC Projetos de Software \u00b7 Situa\u00e7\u00e3o de Aprendizagem 01 \u00b7 "
              "Turma 2IEDS", S_SUB),
    Spacer(1, 0.3 * cm),
]

E.append(Paragraph(
    "A primeira etapa do plano de ensino compreende quinze aulas e abrange a an\u00e1lise da demanda "
    "n\u00ba 12569, as etapas de Empatia, Defini\u00e7\u00e3o e Idea\u00e7\u00e3o do Design Thinking "
    "e a entrevista explorat\u00f3ria com a empresa. Esta ficha re\u00fane os dois instrumentos de "
    "avalia\u00e7\u00e3o previstos para a etapa, que s\u00e3o a rubrica e a autoavalia\u00e7\u00e3o, "
    "e inventaria as evid\u00eancias produzidas at\u00e9 aqui.", S_TXT))

E.append(Paragraph("1 Escala de desenvolvimento", S_SECAO))
E.append(Paragraph(
    "Transcrita do plano de ensino. O percentual da etapa corresponde \u00e0 soma dos "
    "crit\u00e9rios demonstrados pelo grupo.", S_NOTA))
E.append(Spacer(1, 3))
E.append(quadro(
    ["Faixa", "N\u00edvel"],
    [["0% a 25%", "N\u00e3o desenvolvido"],
     ["26% a 50%", "Em desenvolvimento"],
     ["51% a 75%", "Parcialmente desenvolvido"],
     ["76% a 90%", "Desenvolvido"],
     ["91% a 100%", "Plenamente desenvolvido"]],
    [LARG * 0.22, LARG * 0.78], folga=4))

E.append(Paragraph("2 Crit\u00e9rios da etapa", S_SECAO))
E.append(Paragraph(
    "Os quatro crit\u00e9rios e os respectivos pesos s\u00e3o os do plano de ensino. Os descritores "
    "de n\u00edvel foram elaborados pelo grupo, para que a nota atribu\u00edda tenha refer\u00eancia "
    "escrita e n\u00e3o impress\u00e3o.", S_NOTA))

E.append(criterio(
    "2.1 Compreens\u00e3o da dor e do contexto da demanda n\u00ba 12569", "30%",
    ["O problema \u00e9 repetido com as palavras do enunciado da demanda, sem que se identifique "
     "qual \u00e9 a dor central.",
     "A dor \u00e9 mencionada, mas confunde-se com a aus\u00eancia de cadastro ou com a falta de "
     "um sistema, que s\u00e3o sintomas.",
     "A dor central \u00e9 nomeada como tempo de consulta, sem que se relacione ao contexto de "
     "trabalho do usu\u00e1rio.",
     "A dor central \u00e9 nomeada e sustentada pelo contexto, com as consequ\u00eancias que a "
     "pr\u00f3pria empresa relatou.",
     "A dor central orienta as decis\u00f5es de projeto, e cada decis\u00e3o tomada pode ser "
     "remetida a ela de forma expl\u00edcita."]))

E.append(criterio(
    "2.2 Coer\u00eancia entre demanda, persona, mapa de empatia e prioridades", "25%",
    ["A persona n\u00e3o corresponde a um usu\u00e1rio efetivamente afetado pelo problema da "
     "demanda.",
     "A persona existe, mas o mapa de empatia n\u00e3o deriva dela, e sim de impress\u00f5es "
     "gerais sobre o setor.",
     "Persona e mapa de empatia s\u00e3o coerentes entre si, e as prioridades de desenvolvimento "
     "n\u00e3o decorrem deles.",
     "As prioridades de desenvolvimento decorrem das necessidades registradas no mapa de empatia.",
     "A cadeia \u00e9 expl\u00edcita em todos os elos, e a necessidade antecede a solu\u00e7\u00e3o "
     "tanto no documento quanto no pitch."]))

E.append(criterio(
    "2.3 Identifica\u00e7\u00e3o de certezas, suposi\u00e7\u00f5es, d\u00favidas e riscos, com "
    "valida\u00e7\u00e3o na entrevista", "25%",
    ["A matriz CSD n\u00e3o distingue o que a empresa afirmou do que o grupo presumiu.",
     "A distin\u00e7\u00e3o existe, mas suposi\u00e7\u00f5es do grupo aparecem registradas como "
     "certezas.",
     "A matriz distingue corretamente as tr\u00eas colunas, e n\u00e3o h\u00e1 registro de riscos "
     "nem plano de valida\u00e7\u00e3o.",
     "H\u00e1 registro de riscos derivado da matriz, com impacto, probabilidade e resposta "
     "planejada para cada item.",
     "Cada d\u00favida corresponde a uma pergunta do roteiro, e a matriz \u00e9 revisada com as "
     "respostas obtidas na entrevista."]))

E.append(criterio(
    "2.4 Participa\u00e7\u00e3o, colabora\u00e7\u00e3o e pensamento anal\u00edtico", "20%",
    ["A contribui\u00e7\u00e3o concentra-se em um ou dois integrantes.",
     "H\u00e1 divis\u00e3o de tarefas entre os integrantes, sem articula\u00e7\u00e3o entre as "
     "partes produzidas.",
     "Todos contribuem, e as decis\u00f5es tomadas n\u00e3o ficam registradas.",
     "A contribui\u00e7\u00e3o \u00e9 distribu\u00edda e as decis\u00f5es ficam registradas com "
     "a respectiva justificativa.",
     "O grupo revisa as pr\u00f3prias conclus\u00f5es e declara as limita\u00e7\u00f5es do que "
     "produziu, em lugar de omiti-las."]))

# ============================================================== evidencias
E.append(PageBreak())
E.append(Paragraph("3 Evid\u00eancias por crit\u00e9rio", S_SECAO))
E.append(Paragraph(
    "Inventario do que o grupo produziu e do lugar exato onde cada evid\u00eancia est\u00e1. As "
    "se\u00e7\u00f5es e os quadros referem-se ao documento em norma ABNT; os n\u00fameros 06 a 09 "
    "referem-se aos arquivos da pasta de documenta\u00e7\u00e3o.", S_NOTA))
E.append(Spacer(1, 3))
E.append(quadro(
    ["Crit\u00e9rio", "Evid\u00eancia", "Onde est\u00e1"],
    [["2.1", "Caracteriza\u00e7\u00e3o do desafio e defini\u00e7\u00e3o da dor central como tempo "
             "de consulta", "ABNT, se\u00e7\u00e3o 6.1"],
     ["2.1", "Justificativa da demanda escolhida", "JUSTIFICATIVA.md e ABNT, se\u00e7\u00e3o 3"],
     ["2.2", "Persona prim\u00e1ria e personas secund\u00e1rias",
      "ABNT, se\u00e7\u00e3o 6.2, Quadro 1 e Figura 1"],
     ["2.2", "Mapa de empatia, com dores e necessidades",
      "ABNT, se\u00e7\u00e3o 6.3, Quadro 2 e Figura 2"],
     ["2.2", "Agrupamento das ideias por afinidade, que liga necessidade e ideia",
      "ABNT, se\u00e7\u00e3o 6.5, Quadro 4"],
     ["2.2", "Prioriza\u00e7\u00e3o por valor e complexidade, e decis\u00e3o sobre cada ideia",
      "ABNT, se\u00e7\u00f5es 6.6 e 6.7, Quadros 5 e 6"],
     ["2.3", "Matriz CSD, com cinco certezas, cinco suposi\u00e7\u00f5es e cinco d\u00favidas",
      "ABNT, se\u00e7\u00e3o 6.4, Quadro 3 e Figura 3"],
     ["2.3", "Registro de dez riscos, com resposta planejada e pergunta que resolve cada um",
      "ABNT, se\u00e7\u00e3o 6.8, Quadro 7"],
     ["2.3", "Roteiro da entrevista, com as cinco d\u00favidas mapeadas em perguntas",
      "Documento 06"],
     ["2.3", "Termo de autoriza\u00e7\u00e3o e ata da entrevista",
      "Documento 08, <b>pendente de realiza\u00e7\u00e3o</b>"],
     ["2.3", "Matriz CSD revisada com as respostas da empresa",
      "<b>Pendente da entrevista</b>"],
     ["2.4", "Registro fotogr\u00e1fico da equipe em atividade",
      "ABNT, Ap\u00eandice A, Figura 5"],
     ["2.4", "Hist\u00f3rico de contribui\u00e7\u00f5es ao reposit\u00f3rio",
      "Hist\u00f3rico de commits do reposit\u00f3rio"],
     ["2.4", "Autoavalia\u00e7\u00e3o e avalia\u00e7\u00e3o da equipe",
      "Se\u00e7\u00f5es 4 e 5 deste documento"],
     ["2.4", "Limita\u00e7\u00e3o metodol\u00f3gica declarada pelo pr\u00f3prio grupo",
      "ABNT, se\u00e7\u00f5es 5 e 8"]],
    [LARG * 0.09, LARG * 0.55, LARG * 0.36], folga=4))

E.append(Paragraph(
    "Duas evid\u00eancias do crit\u00e9rio 2.3 dependem da entrevista com a W K Solu\u00e7\u00f5es "
    "Industriais e por isso constam como pendentes. Enquanto n\u00e3o forem produzidas, o "
    "crit\u00e9rio n\u00e3o alcan\u00e7a o n\u00edvel plenamente desenvolvido, cujo descritor exige "
    "a matriz revisada com as respostas obtidas. O grupo registra a pend\u00eancia em lugar de "
    "apresentar como validado o que ainda \u00e9 suposi\u00e7\u00e3o.", S_TXT))

E.append(Paragraph("Resultado da etapa", S_SUBSECAO))
E.append(quadro(
    ["Crit\u00e9rio", "Peso", "N\u00edvel atingido", "Percentual demonstrado"],
    [["2.1 Compreens\u00e3o da dor e do contexto", "30%", "", ""],
     ["2.2 Coer\u00eancia entre demanda, persona, mapa e prioridades", "25%", "", ""],
     ["2.3 Certezas, suposi\u00e7\u00f5es, d\u00favidas e riscos", "25%", "", ""],
     ["2.4 Participa\u00e7\u00e3o, colabora\u00e7\u00e3o e pensamento anal\u00edtico", "20%", "", ""],
     ["<b>Total da etapa</b>", "<b>100%</b>", "", ""]],
    [LARG * 0.44, LARG * 0.10, LARG * 0.23, LARG * 0.23], folga=11))

# ============================================================ autoavaliacao
E.append(PageBreak())
E.append(Paragraph("4 Autoavalia\u00e7\u00e3o individual", S_SECAO))
E.append(Paragraph(
    "Uma via por integrante. As sete capacidades socioemocionais s\u00e3o as previstas na UC "
    "Projetos de Software. Avalie-se pela escala da se\u00e7\u00e3o 1 e justifique com um fato "
    "concreto da etapa, e n\u00e3o com uma inten\u00e7\u00e3o.", S_NOTA))
E.append(Spacer(1, 4))
E.append(quadro(
    ["Campo", "Preenchimento"],
    [["Nome do integrante", ""],
     ["Papel no grupo", ""],
     ["Data", ""]],
    [LARG * 0.28, LARG * 0.72], folga=9))
E.append(Spacer(1, 6))
E.append(quadro(
    ["Capacidade socioemocional", "N\u00edvel", "Fato concreto que sustenta o n\u00edvel"],
    [[c, "", ""] for c in CAPACIDADES],
    [LARG * 0.26, LARG * 0.16, LARG * 0.58], folga=13))

E.append(Paragraph("O que contribu\u00ed para a etapa", S_SUBSECAO))
E.append(Linhas(3))
E.append(Paragraph("O que faria diferente na pr\u00f3xima etapa", S_SUBSECAO))
E.append(Linhas(3))

E.append(Spacer(1, 0.7 * cm))
t = Table([[Assinatura("Assinatura do integrante")]], colWidths=[LARG * 0.5])
t.setStyle(TableStyle([("LEFTPADDING", (0, 0), (-1, -1), 0)]))
E.append(t)

# ========================================================= avaliacao da equipe
E.append(PageBreak())
E.append(Paragraph("5 Avalia\u00e7\u00e3o da equipe", S_SECAO))
E.append(Paragraph(
    "Preenchida em grupo, em uma \u00fanica via, ao fechar a etapa. Registre a contribui\u00e7\u00e3o "
    "efetiva de cada integrante, com o que foi produzido, e n\u00e3o com adjetivos.", S_NOTA))
E.append(Spacer(1, 4))
E.append(quadro(
    ["Integrante", "O que produziu nesta etapa", "N\u00edvel"],
    [[nome, "", ""] for nome in INTEGRANTES],
    [LARG * 0.28, LARG * 0.56, LARG * 0.16], folga=14))

E.append(Paragraph("O que funcionou no trabalho do grupo", S_SUBSECAO))
E.append(Linhas(3))
E.append(Paragraph("O que precisa mudar na pr\u00f3xima etapa", S_SUBSECAO))
E.append(Linhas(3))
E.append(Paragraph("Pend\u00eancias que o grupo assume ao fechar a etapa", S_SUBSECAO))
E.append(Linhas(3))

E.append(Spacer(1, 0.6 * cm))
t = Table([[Assinatura("Scrum Master"), "", Assinatura("Product Owner")]],
          colWidths=[LARG * 0.45, LARG * 0.10, LARG * 0.45])
t.setStyle(TableStyle([
    ("VALIGN", (0, 0), (-1, -1), "BOTTOM"),
    ("LEFTPADDING", (0, 0), (-1, -1), 0),
    ("RIGHTPADDING", (0, 0), (-1, -1), 0),
]))
E.append(t)

E.append(Spacer(1, 0.4 * cm))
E.append(Paragraph(
    "Grupo GR4, turma 2IEDS, 2026. Enzo Avanze, Scrum Master. Rafael Brecci de Souza, Product "
    "Owner. Felipe Bertaco Cruz, Jo\u00e3o Pedro Tomazini Rodrigues, Julia Possebon Leme, Nicolas "
    "Fernandes Santos e Vitor Matheus Canali Pereira, desenvolvedores. Crit\u00e9rios, pesos, "
    "escala e capacidades transcritos do plano de ensino da UC Projetos de Software. Docente "
    "respons\u00e1vel: Paulo Cesar de Camargo.", S_NOTA))

# ----------------------------------------------------------------- monta
doc = BaseDocTemplate(
    SAIDA, pagesize=A4,
    leftMargin=MARG_E, rightMargin=MARG_D, topMargin=MARG_S, bottomMargin=MARG_I,
    title="Rubrica e Autoavaliacao da Etapa 1 - GR4 - Demanda 12569",
    author="GR4 - Turma 2IEDS")
frame = Frame(MARG_E, MARG_I, LARG, A4[1] - MARG_S - MARG_I, id="corpo",
              leftPadding=0, rightPadding=0, topPadding=0, bottomPadding=0)
doc.addPageTemplates([PageTemplate(id="padrao", frames=[frame], onPage=moldura)])
doc.build(E)
print("gerado: %s" % SAIDA)
