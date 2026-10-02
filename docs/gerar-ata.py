# -*- coding: utf-8 -*-
"""
Gera o termo de autorizacao e a ata da entrevista exploratoria do GR4 (demanda 12569).
Uso: python gerar-ata.py [saida.pdf]

Dois documentos em um arquivo, para imprimir e levar a reuniao:

  1. Termo de autorizacao de registro e uso das informacoes, assinado antes de
     iniciar a conversa. O material de capacitacao condiciona a gravacao a
     autorizacao do entrevistado, e sem o termo a gravacao nao pode ser usada
     como evidencia.
  2. Ata da entrevista, preenchida durante e imediatamente apos a conversa. E o
     instrumento de avaliacao que o plano de ensino chama de evidencia da
     entrevista, e a fonte da revisao da matriz CSD.

A ata nao repete as 29 perguntas, que estao no roteiro. Ela registra quem
participou, o que foi apurado em cada bloco, as citacoes literais, a conferencia
final do entendimento e as pendencias.
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

SAIDA = sys.argv[1] if len(sys.argv) > 1 else "08 - Ata_e_Autorizacao_Entrevista_GR4.pdf"

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

GRUPO = ("Enzo Avanze, Felipe Bertaco Cruz, Jo\u00e3o Pedro Tomazini Rodrigues, "
         "Julia Possebon Leme, Nicolas Fernandes Santos, Rafael Brecci de Souza e "
         "Vitor Matheus Canali Pereira")


# ----------------------------------------------------------------- estilos
def est(nome, **kw):
    base = dict(name=nome, fontName=F, fontSize=9.5, leading=13, spaceAfter=0)
    base.update(kw)
    return ParagraphStyle(**base)


S_TITULO   = est("titulo", fontName=FB, fontSize=19, leading=23, alignment=TA_CENTER,
                 textColor=AZUL, spaceAfter=8)
S_SUB      = est("sub", fontSize=10.5, leading=15, alignment=TA_CENTER, spaceAfter=3)
S_FINAL    = est("fin", fontSize=9, leading=12.5, alignment=TA_CENTER,
                 textColor=colors.HexColor("#404040"), spaceAfter=12)
S_SECAO    = est("secao", fontName=FB, fontSize=11.5, leading=15, textColor=VERMELHO,
                 spaceBefore=13, spaceAfter=5)
S_SUBSECAO = est("subsecao", fontName=FB, fontSize=10, leading=13, textColor=AZUL,
                 spaceBefore=8, spaceAfter=4)
S_TXT      = est("txt", alignment=TA_JUSTIFY, spaceAfter=5)
S_NOTA     = est("nota", fontSize=8.5, leading=11.5, alignment=TA_JUSTIFY,
                 textColor=colors.HexColor("#404040"), spaceAfter=4)
S_ITEM     = est("item", fontSize=9, leading=12, leftIndent=0.45 * cm,
                 bulletIndent=0.1 * cm, spaceAfter=2)
S_CEL      = est("cel", fontSize=8.5, leading=11)
S_CELB     = est("celb", fontName=FB, fontSize=8.5, leading=11, textColor=AZUL)
S_ASSIN    = est("assin", fontSize=8.5, leading=11, alignment=TA_CENTER,
                 textColor=colors.HexColor("#404040"))


# ------------------------------------------------------- linhas de resposta
class Linhas(Flowable):
    """Pauta para registro manual."""

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
    """Linha de assinatura com rotulo embaixo."""

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


# --------------------------------------------------------------- moldura
TITULO_CORRENTE = ["Termo de Autoriza\u00e7\u00e3o"]


def moldura(canvas, doc):
    canvas.saveState()
    canvas.setFont(FB, 9)
    canvas.setFillColor(VERMELHO)
    canvas.drawString(MARG_E, A4[1] - 1.5 * cm, "GR4 | 2IEDS")
    canvas.setFont(F, 7.5)
    canvas.setFillColor(CINZA)
    canvas.drawRightString(A4[0] - MARG_D, A4[1] - 1.5 * cm,
                           "SENAI | Desafio de Ideias \u00b7 Entrevista Explorat\u00f3ria")
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
def quadro(cabecalho, linhas_dados, larguras, folga=12):
    dados = [[Paragraph(c, S_CELB) for c in cabecalho]]
    for lin in linhas_dados:
        dados.append([Paragraph(c, S_CEL) if c else "" for c in lin])
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


def item(texto):
    return Paragraph(texto, S_ITEM, bulletText="\u2013")


def par_assinaturas(rotulo_esq, rotulo_dir):
    t = Table([[Assinatura(rotulo_esq), "", Assinatura(rotulo_dir)]],
              colWidths=[LARG * 0.45, LARG * 0.10, LARG * 0.45])
    t.setStyle(TableStyle([
        ("VALIGN", (0, 0), (-1, -1), "BOTTOM"),
        ("LEFTPADDING", (0, 0), (-1, -1), 0),
        ("RIGHTPADDING", (0, 0), (-1, -1), 0),
    ]))
    return t


def bloco_sintese(letra_titulo, perguntas, linhas=3):
    """Um bloco do roteiro: o que foi apurado nas perguntas indicadas."""
    return KeepTogether([
        Paragraph('%s <font size="8.5" color="%s">(perguntas %s)</font>'
                  % (letra_titulo, CINZA.hexval(), perguntas), S_SUBSECAO),
        Linhas(linhas),
        Spacer(1, 4),
    ])


E = []

# ==================================================== 1. TERMO DE AUTORIZACAO
E += [
    Spacer(1, 0.2 * cm),
    Paragraph("TERMO DE AUTORIZA\u00c7\u00c3O DE REGISTRO", S_TITULO),
    Paragraph("Entrevista explorat\u00f3ria \u00b7 Demanda n\u00ba 12569, "
              "Gest\u00e3o de Laborat\u00f3rios e Ativos", S_SUB),
    Paragraph("Grupo GR4 \u00b7 Turma 2IEDS \u00b7 Curso T\u00e9cnico em Desenvolvimento "
              "de Sistemas", S_SUB),
    Spacer(1, 0.3 * cm),
]

E.append(Paragraph(
    "O grupo GR4, da turma 2IEDS do curso T\u00e9cnico em Desenvolvimento de Sistemas da Escola "
    "SENAI Eng\u00ba Oct\u00e1vio Marcondes Ferraz, realiza entrevista explorat\u00f3ria com a "
    "empresa W K Solu\u00e7\u00f5es Industriais no \u00e2mbito do Desafio de Ideias do SAGA SENAI "
    "de Inova\u00e7\u00e3o, referente \u00e0 demanda n\u00ba 12569, Gest\u00e3o de "
    "Laborat\u00f3rios e Ativos. A conversa integra a etapa de Empatia do m\u00e9todo Design "
    "Thinking e tem por objetivo compreender como o controle de equipamentos acontece hoje na "
    "empresa.", S_TXT))

E.append(Paragraph("Finalidade e uso das informa\u00e7\u00f5es", S_SECAO))
E.append(item("As informa\u00e7\u00f5es prestadas ser\u00e3o usadas exclusivamente para fins "
              "acad\u00eamicos, na documenta\u00e7\u00e3o do projeto e na apresenta\u00e7\u00e3o "
              "\u00e0 banca avaliadora do Desafio de Ideias."))
E.append(item("A grava\u00e7\u00e3o de \u00e1udio, se autorizada, serve apenas para que o grupo "
              "confira as anota\u00e7\u00f5es, e n\u00e3o ser\u00e1 publicada nem divulgada fora "
              "da escola."))
E.append(item("A empresa pode, a qualquer momento, indicar informa\u00e7\u00e3o que n\u00e3o deva "
              "constar do documento, e o grupo a retirar\u00e1 do registro."))
E.append(item("O grupo compromete-se a apresentar \u00e0 empresa o registro resultante da "
              "conversa, para confer\u00eancia antes do uso."))

E.append(Paragraph("Autoriza\u00e7\u00f5es", S_SECAO))
E.append(Paragraph(
    "Assinale as autoriza\u00e7\u00f5es concedidas. A recusa a qualquer dos itens n\u00e3o impede "
    "a realiza\u00e7\u00e3o da entrevista.", S_NOTA))
E.append(Spacer(1, 2))
E.append(quadro(
    ["Item", "Autoriza", "N\u00e3o autoriza"],
    [["Grava\u00e7\u00e3o do \u00e1udio da conversa", "", ""],
     ["Anota\u00e7\u00e3o de cita\u00e7\u00f5es literais, com identifica\u00e7\u00e3o do cargo", "", ""],
     ["Men\u00e7\u00e3o ao nome da empresa na documenta\u00e7\u00e3o e no pitch", "", ""],
     ["Fotografia do ambiente ou dos equipamentos", "", ""],
     ["Uso de dados quantitativos informados, como quantidade de ativos", "", ""]],
    [LARG * 0.62, LARG * 0.19, LARG * 0.19], folga=8))

E.append(Paragraph("Identifica\u00e7\u00e3o e assinaturas", S_SECAO))
E.append(quadro(
    ["Campo", "Preenchimento"],
    [["Nome do respons\u00e1vel pela empresa", ""],
     ["Cargo e \u00e1rea", ""],
     ["Contato para d\u00favidas posteriores", ""],
     ["Local e data", ""]],
    [LARG * 0.38, LARG * 0.62], folga=8))

E.append(Spacer(1, 0.65 * cm))
E.append(par_assinaturas("W K Solu\u00e7\u00f5es Industriais",
                         "Pelo grupo GR4, turma 2IEDS"))

E.append(Spacer(1, 0.4 * cm))
E.append(Paragraph(
    "Integrantes do GR4, turma 2IEDS, 2026: %s. Docente responsável: Paulo Cesar de "
    "Camargo. Coordenação do curso: Marcos Alves. Coordenação "
    "pedagógica: Antônio Marcos." % GRUPO, S_NOTA))

# ================================================================== 2. ATA
E.append(PageBreak())

E += [
    Spacer(1, 0.2 * cm),
    Paragraph("ATA DA ENTREVISTA EXPLORAT\u00d3RIA", S_TITULO),
    Paragraph("Grupo GR4 \u00b7 Demanda n\u00ba 12569, Gest\u00e3o de Laborat\u00f3rios e Ativos",
              S_SUB),
    Paragraph("Empresa W K Solu\u00e7\u00f5es Industriais \u00b7 Etapa de Empatia do "
              "Design Thinking", S_SUB),
    Spacer(1, 0.2 * cm),
]

E.append(Paragraph(
    "Esta ata registra a entrevista conduzida conforme o roteiro do grupo, composto por 29 "
    "perguntas distribu\u00eddas em oito blocos. Preencha durante a conversa e feche logo "
    "depois, enquanto a mem\u00f3ria est\u00e1 fresca. O que interessa aqui n\u00e3o \u00e9 a "
    "transcri\u00e7\u00e3o da fala, mas o que foi apurado, com n\u00famero sempre que houver "
    "n\u00famero.", S_TXT))

E.append(Paragraph("1 Identifica\u00e7\u00e3o", S_SECAO))
E.append(quadro(
    ["Campo", "Preenchimento"],
    [["Data", ""],
     ["Hor\u00e1rio de in\u00edcio e de t\u00e9rmino", ""],
     ["Local ou canal", ""],
     ["Autoriza\u00e7\u00e3o de grava\u00e7\u00e3o assinada", ""],
     ["Roteiro aplicado", ""]],
    [LARG * 0.38, LARG * 0.62], folga=13))

E.append(Paragraph("2 Participantes", S_SECAO))
E.append(quadro(
    ["Nome", "Cargo e \u00e1rea", "Organiza\u00e7\u00e3o"],
    [["", "", ""] for _ in range(5)],
    [LARG * 0.38, LARG * 0.37, LARG * 0.25], folga=14))
E.append(Paragraph(
    "Registre tamb\u00e9m qual integrante conduziu as perguntas e qual registrou as respostas, "
    "conforme a orienta\u00e7\u00e3o do roteiro de que apenas dois conduzam a conversa.", S_NOTA))

E.append(PageBreak())
E.append(Paragraph("3 S\u00edntese por bloco do roteiro", S_SECAO))
E.append(Paragraph(
    "Uma a tr\u00eas linhas por bloco, com o essencial. Problema relatado pede frequ\u00eancia e "
    "tempo gasto: sem n\u00famero n\u00e3o existe m\u00e9trica de sucesso.", S_NOTA))
E.append(Spacer(1, 3))
E.append(bloco_sintese("A. O processo hoje", "1 a 3"))
E.append(bloco_sintese("B. Os equipamentos", "4 a 7"))
E.append(bloco_sintese("C. O ambiente de trabalho", "8 a 11"))
E.append(bloco_sintese("D. Movimenta\u00e7\u00e3o, baixa e corre\u00e7\u00e3o", "12 a 16"))
E.append(bloco_sintese("E. Responsabilidades", "17 a 19"))
E.append(bloco_sintese("F. Manuten\u00e7\u00e3o", "20 a 23"))
E.append(bloco_sintese("G. Registros e regras da empresa", "24 a 26"))
E.append(bloco_sintese("H. Sucesso e pr\u00f3ximos passos", "27 a 29"))

E.append(PageBreak())

E.append(Paragraph("4 Resposta \u00e0s cinco d\u00favidas da matriz CSD", S_SECAO))
E.append(Paragraph(
    "Nenhuma das cinco pode voltar em branco. S\u00e3o elas que convertem suposi\u00e7\u00e3o em "
    "certeza e liberam a revis\u00e3o da matriz.", S_NOTA))
E.append(Spacer(1, 3))
E.append(quadro(
    ["D\u00favida", "Pergunta", "Resposta obtida"],
    [["Quantos ativos existem hoje", "4", ""],
     ["H\u00e1 sinal de rede na \u00e1rea e o telefone celular \u00e9 liberado", "8, 9 e 10", ""],
     ["Quem pode movimentar um ativo e se h\u00e1 aprova\u00e7\u00e3o", "12", ""],
     ["Prioridade entre localiza\u00e7\u00e3o e hist\u00f3rico de manuten\u00e7\u00e3o", "23", ""],
     ["Que registro j\u00e1 existe e de onde importar a base", "24", ""]],
    [LARG * 0.40, LARG * 0.13, LARG * 0.47], folga=16))

E.append(Paragraph("5 Cita\u00e7\u00f5es literais", S_SECAO))
E.append(Paragraph(
    "Anote as palavras exatas, com o cargo de quem falou. A frase da empresa vale mais que a "
    "par\u00e1frase do grupo, e \u00e9 ela que sustenta o problema no pitch.", S_NOTA))
E.append(Spacer(1, 3))
E.append(quadro(
    ["Cita\u00e7\u00e3o", "Quem disse (cargo)"],
    [["", ""] for _ in range(4)],
    [LARG * 0.72, LARG * 0.28], folga=16))

E.append(Paragraph("6 Achados que contrariam o que o grupo presumia", S_SECAO))
E.append(Paragraph(
    "Aqui entra o que surpreendeu. Cada item desta lista \u00e9 candidato a alterar a matriz CSD, "
    "o registro de riscos ou a pr\u00f3pria solu\u00e7\u00e3o.", S_NOTA))
E.append(Spacer(1, 3))
E.append(quadro(
    ["O que o grupo presumia", "O que a empresa informou", "Risco afetado"],
    [["", "", ""] for _ in range(4)],
    [LARG * 0.36, LARG * 0.44, LARG * 0.20], folga=15))

E.append(Paragraph("7 Confer\u00eancia final do entendimento", S_SECAO))
E.append(Paragraph(
    "Ao final da conversa, repita em voz alta o que foi entendido do processo e pe\u00e7a "
    "corre\u00e7\u00e3o. Registre abaixo o que a empresa corrigiu nessa confer\u00eancia, ainda "
    "que nada tenha sido corrigido, caso em que se registra a confirma\u00e7\u00e3o.", S_NOTA))
E.append(Spacer(1, 3))
E.append(Linhas(3))

E.append(Paragraph("8 Pend\u00eancias e pr\u00f3ximos passos", S_SECAO))
E.append(quadro(
    ["O que ficou em aberto", "Respons\u00e1vel", "Prazo"],
    [["", "", ""] for _ in range(4)],
    [LARG * 0.56, LARG * 0.26, LARG * 0.18], folga=15))

E.append(Spacer(1, 0.9 * cm))
E.append(par_assinaturas("Quem conduziu a entrevista",
                         "Quem registrou a ata"))

E.append(Spacer(1, 0.5 * cm))
E.append(Paragraph(
    "Elaborado pelo GR4, turma 2IEDS, 2026: %s. Documento complementar ao roteiro de entrevista "
    "explorat\u00f3ria do grupo." % GRUPO, S_NOTA))

# ----------------------------------------------------------------- monta
doc = BaseDocTemplate(
    SAIDA, pagesize=A4,
    leftMargin=MARG_E, rightMargin=MARG_D, topMargin=MARG_S, bottomMargin=MARG_I,
    title="Ata e Autorizacao da Entrevista Exploratoria - GR4 - Demanda 12569",
    author="GR4 - Turma 2IEDS")
frame = Frame(MARG_E, MARG_I, LARG, A4[1] - MARG_S - MARG_I, id="corpo",
              leftPadding=0, rightPadding=0, topPadding=0, bottomPadding=0)
doc.addPageTemplates([PageTemplate(id="padrao", frames=[frame], onPage=moldura)])
doc.build(E)
print("gerado: %s" % SAIDA)
