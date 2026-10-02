# -*- coding: utf-8 -*-
"""
Gera o roteiro da entrevista exploratoria do GR4 (demanda 12569).
Uso: python gerar-entrevista.py [saida.pdf]

Roteiro de etapa de Empatia: investiga o problema da empresa, o processo atual e
as regras que ja existem. Nao apresenta nem valida a solucao do grupo, e nao
pergunta se a empresa gostaria de tal recurso. Os requisitos sao deduzidos das
respostas, depois, na sintese. Sao 29 perguntas em cerca de 60 minutos, com tres
quadros que a empresa preenche no lugar de listas ditadas em voz alta.
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

SAIDA = sys.argv[1] if len(sys.argv) > 1 else "06 - Roteiro_Entrevista_GR4_Essencial.pdf"

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
S_PERG     = est("perg", fontName=FB, fontSize=9.5, leading=12.5, spaceAfter=1)
S_OBJ      = est("obj", fontSize=8, leading=10.5, textColor=CINZA,
                 leftIndent=0.5 * cm, spaceAfter=2)
S_TXT      = est("txt", alignment=TA_JUSTIFY, spaceAfter=5)
S_NOTA     = est("nota", fontSize=8.5, leading=11.5, alignment=TA_JUSTIFY,
                 textColor=colors.HexColor("#404040"), spaceAfter=4)
S_ITEM     = est("item", fontSize=9, leading=12, leftIndent=0.45 * cm,
                 bulletIndent=0.1 * cm, spaceAfter=2)
S_CEL      = est("cel", fontSize=8.5, leading=11)
S_CELB     = est("celb", fontName=FB, fontSize=8.5, leading=11, textColor=AZUL)


# ------------------------------------------------------- linhas de resposta
class Linhas(Flowable):
    """Pauta para registro manual da resposta."""

    def __init__(self, n=2, largura=LARG, esp=0.52 * cm, recuo=0.5 * cm):
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


# --------------------------------------------------------------- moldura
def moldura(canvas, doc):
    canvas.saveState()
    canvas.setFont(FB, 9)
    canvas.setFillColor(VERMELHO)
    canvas.drawString(MARG_E, A4[1] - 1.5 * cm, "GR4 | 2IEDS")
    canvas.setFont(F, 7.5)
    canvas.setFillColor(CINZA)
    canvas.drawRightString(A4[0] - MARG_D, A4[1] - 1.5 * cm,
                           "SENAI | Desafio de Ideias \u00b7 Roteiro de Entrevista Explorat\u00f3ria")
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


def pergunta(texto, objetivo, linhas=2):
    contador[0] += 1
    cab = Paragraph('<font color="%s">%d.</font> %s'
                    % (AZUL.hexval(), contador[0], texto), S_PERG)
    partes = [cab, Paragraph("Objetivo: %s" % objetivo, S_OBJ)]
    if linhas:
        partes.append(Linhas(linhas))
    partes.append(Spacer(1, 5))
    return KeepTogether(partes)


def quadro(cabecalho, linhas_dados, larguras, folga=12):
    """Altura automatica: o rotulo define a altura minima e a folga abre o
    espaco de escrita, de modo que rotulo longo nunca seja cortado."""
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


def secao(titulo, minutos):
    return Paragraph('%s <font size="8.5" color="%s">(cerca de %d minutos)</font>'
                     % (titulo, CINZA.hexval(), minutos), S_SECAO)


def item(texto):
    return Paragraph(texto, S_ITEM, bulletText="\u2013")


E = []

# ============================================================== abertura
E += [
    Spacer(1, 0.2 * cm),
    Paragraph("ROTEIRO DE ENTREVISTA EXPLORAT\u00d3RIA", S_TITULO),
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
    "Esta entrevista investiga o problema da empresa, e nada al\u00e9m disso. Nenhuma pergunta "
    "descreve, prop\u00f5e ou pede opini\u00e3o sobre a solu\u00e7\u00e3o em constru\u00e7\u00e3o: o "
    "que se busca \u00e9 como o trabalho acontece hoje, que regras j\u00e1 existem, o que o ambiente "
    "imp\u00f5e e o que d\u00f3i. Os requisitos s\u00e3o deduzidos depois, pelo grupo, na s\u00edntese "
    "da \u00faltima p\u00e1gina. A valida\u00e7\u00e3o do prot\u00f3tipo \u00e9 uma conversa \u00e0 "
    "parte, agendada para outro dia.", S_TXT))

E.append(Paragraph(
    "S\u00e3o 29 perguntas em cerca de 60 minutos, com o tempo sugerido ao lado de cada bloco. Tr\u00eas "
    "quadros substituem o levantamento de listas, que consumiria boa parte da conversa.", S_TXT))

E.append(Paragraph("Como conduzir", S_SUBSECAO))
for t in [
    "Dois integrantes conduzem: um pergunta, o outro registra. N\u00e3o se dividam em mais do que isso.",
    "N\u00e3o descreva o que o grupo est\u00e1 construindo e n\u00e3o pergunte se a empresa gostaria de "
    "tal recurso. Pergunte como \u00e9 hoje. Se perguntarem o que estamos fazendo, responda ao final.",
    "Pergunte e espere. A resposta \u00fatil costuma vir depois do sil\u00eancio, e n\u00e3o depois da "
    "segunda pergunta.",
    "Resposta gen\u00e9rica pede exemplo concreto: <i>qual foi a \u00faltima vez que isso aconteceu, e "
    "o que foi feito?</i>",
    "Diante de todo problema relatado, pergunte com que frequ\u00eancia ocorre e quanto tempo custa. "
    "Sem n\u00famero n\u00e3o existe m\u00e9trica de sucesso.",
    "Use o vocabul\u00e1rio da empresa e anote as palavras exatas. Elas valem mais que a "
    "par\u00e1frase do grupo.",
    "Os tr\u00eas quadros podem ser preenchidos pelo entrevistado enquanto a conversa segue, ou "
    "deixados com ele para devolver depois. N\u00e3o gaste a reuni\u00e3o lendo item por item.",
    "Ao final, repita em voz alta o que foi entendido do processo e pe\u00e7a corre\u00e7\u00e3o. "
    "\u00c9 o que evita construir em cima de uma leitura errada.",
]:
    E.append(item(t))

E.append(Paragraph("Registro da entrevista", S_SUBSECAO))
E.append(quadro(
    ["Campo", "Preenchimento"],
    [["Data e hor\u00e1rio (in\u00edcio e t\u00e9rmino)", ""],
     ["Local ou canal (presencial, chamada de v\u00eddeo, telefone)", ""],
     ["Entrevistados: nome, cargo e \u00e1rea", ""],
     ["Contato para d\u00favidas posteriores", ""],
     ["Integrantes do GR4 (quem conduz e quem registra)", ""],
     ["Autoriza\u00e7\u00e3o para gravar o \u00e1udio (sim ou n\u00e3o)", ""]],
    [LARG * 0.46, LARG * 0.54], folga=15))

E.append(Spacer(1, 0.25 * cm))
E.append(Paragraph(
    "<b>Onde as cinco d\u00favidas da matriz CSD s\u00e3o respondidas.</b> O tamanho da base: pergunta "
    "4. As condi\u00e7\u00f5es do ambiente e o uso de telefone celular na \u00e1rea: perguntas 8, 9 e "
    "10. Quem decide e quem autoriza a mudan\u00e7a de setor: pergunta 12. A ordem de prioridade entre "
    "localiza\u00e7\u00e3o e hist\u00f3rico de manuten\u00e7\u00e3o: pergunta 23. O registro que "
    "j\u00e1 existe hoje: pergunta 24. Nenhuma das cinco pode voltar em branco.", S_NOTA))

E.append(PageBreak())

# ============================================================== A
E.append(secao("A. O processo hoje", 8))
E.append(pergunta(
    "Descreva o caminho de um equipamento na empresa, de quando ele chega at\u00e9 quando sai de uso, "
    "dizendo quem registra o qu\u00ea em cada etapa.",
    "Mapear o processo atual por inteiro, e n\u00e3o apenas a etapa problem\u00e1tica.", 3))
E.append(pergunta(
    "Quando algu\u00e9m precisa encontrar um equipamento hoje, o que faz na pr\u00e1tica? Quanto tempo "
    "costuma levar?",
    "Medir o tempo de consulta atual, que \u00e9 a dor central apontada na demanda."))
E.append(pergunta(
    "Em que pontos surgem as diverg\u00eancias entre documentos e setores? Como se descobre que um "
    "documento est\u00e1 errado?",
    "Chegar \u00e0 causa raiz, e n\u00e3o ao sintoma relatado."))

# ============================================================== B
E.append(secao("B. Os equipamentos", 8))
E.append(pergunta(
    "Quantos equipamentos a empresa controla hoje, aproximadamente, e quantos entram e saem por ano?",
    "Dimensionar o problema e o crescimento."))
E.append(pergunta(
    "Quais tipos de equipamento d\u00e3o mais trabalho para controlar, e quais quase n\u00e3o d\u00e3o "
    "problema?",
    "Deixar a empresa apontar a prioridade, em vez de o grupo escolher por conta pr\u00f3pria."))
E.append(pergunta(
    "Como um equipamento \u00e9 identificado fisicamente hoje? O que est\u00e1 escrito nessa "
    "identifica\u00e7\u00e3o, e com que frequ\u00eancia ela se apaga, solta ou desaparece?",
    "Conhecer a identifica\u00e7\u00e3o existente e quanto ela resiste ao uso.", 3))
E.append(pergunta(
    '<font color="%s">[Quadro 1]</font> Que informa\u00e7\u00f5es a empresa precisa saber sobre um '
    'equipamento, e como voc\u00eas chamam cada uma delas?' % VERMELHO.hexval(),
    "Levantar a informa\u00e7\u00e3o que o neg\u00f3cio exige, com o vocabul\u00e1rio da casa.", 0))
E.append(quadro(
    ["Informa\u00e7\u00e3o", "Essencial", "\u00datil", "N\u00e3o usamos",
     "Como voc\u00eas chamam"],
    [[c, "", "", "", ""] for c in [
        "Nome do equipamento", "N\u00famero de identifica\u00e7\u00e3o", "Tipo ou categoria",
        "Setor respons\u00e1vel", "Local exato dentro do setor", "N\u00famero de s\u00e9rie",
        "Fabricante e modelo", "Data e valor de aquisi\u00e7\u00e3o",
        "Pessoa respons\u00e1vel pelo bem", "Hist\u00f3rico de manuten\u00e7\u00e3o",
        "Pr\u00f3xima calibra\u00e7\u00e3o ou inspe\u00e7\u00e3o"]],
    [LARG * 0.31, LARG * 0.12, LARG * 0.10, LARG * 0.12, LARG * 0.35], folga=11))
E.append(Spacer(1, 0.3 * cm))

# ============================================================== C
E.append(secao("C. O ambiente de trabalho", 8))
E.append(pergunta(
    "A que os equipamentos e as \u00e1reas ficam expostos no dia a dia: \u00f3leo, calor, vapor, "
    "produto qu\u00edmico, lavagem, poeira?",
    "Conhecer as condi\u00e7\u00f5es f\u00edsicas que qualquer registro em campo ter\u00e1 de "
    "suportar."))
E.append(pergunta(
    "Quando algu\u00e9m precisa anotar ou consultar alguma coisa estando na \u00e1rea, o que usa hoje? "
    "H\u00e1 restri\u00e7\u00e3o ao uso de telefone celular em alguma \u00e1rea?",
    "Entender como a informa\u00e7\u00e3o circula no ch\u00e3o de f\u00e1brica e que regras existem."))
E.append(pergunta(
    "H\u00e1 lugares da empresa onde n\u00e3o chega rede sem fio ou sinal de telefone?",
    "Mapear as limita\u00e7\u00f5es de comunica\u00e7\u00e3o da planta."))
E.append(pergunta(
    "Quem, de fora do setor ou de fora da empresa, precisa consultar informa\u00e7\u00e3o sobre um "
    "equipamento? Auditor, cliente, fornecedor, contabilidade?",
    "Identificar todos os interessados na informa\u00e7\u00e3o, e n\u00e3o s\u00f3 quem a produz."))

# ============================================================== D
E.append(secao("D. Movimenta\u00e7\u00e3o, baixa e corre\u00e7\u00e3o", 9))
E.append(pergunta(
    "Quando um equipamento muda de setor, o que acontece hoje? Quem decide, quem precisa autorizar "
    "antes e quem fica sabendo depois?",
    "Descobrir a regra de circula\u00e7\u00e3o e quem responde por ela.", 3))
E.append(pergunta(
    "Depois que a mudan\u00e7a aconteceu, o que a empresa precisa saber sobre ela: de onde saiu, para "
    "onde foi, quem levou, quando, por qu\u00ea, em que estado?",
    "Levantar o que o neg\u00f3cio exige que fique registrado de cada movimenta\u00e7\u00e3o."))
E.append(pergunta(
    '<font color="%s">[Quadro 2]</font> Em que situa\u00e7\u00f5es um equipamento pode estar no dia a '
    'dia? Alguma delas impede que ele seja levado para outro setor?' % VERMELHO.hexval(),
    "Obter o vocabul\u00e1rio e as regras da empresa, sem sugerir uma lista pronta.", 0))
E.append(quadro(
    ["Situa\u00e7\u00e3o, na palavra de voc\u00eas", "Pode ir para outro setor? (S/N)",
     "Quem registra essa situa\u00e7\u00e3o"],
    [["", "", ""] for _ in range(6)],
    [LARG * 0.40, LARG * 0.26, LARG * 0.34], folga=14))
E.append(Spacer(1, 0.3 * cm))
E.append(pergunta(
    "Como um equipamento sai de uso definitivamente: quais motivos existem, quem autoriza e a empresa "
    "ainda precisa consultar esse equipamento depois?",
    "Conhecer o encerramento do ciclo de vida e a necessidade de consulta posterior."))
E.append(pergunta(
    "Quando algu\u00e9m registra uma informa\u00e7\u00e3o errada, o que se faz hoje? O registro antigo "
    "\u00e9 corrigido, apagado, ou fica marcado de alguma forma?",
    "Descobrir a pr\u00e1tica e a exig\u00eancia da empresa quanto \u00e0 corre\u00e7\u00e3o de "
    "registros."))

# ============================================================== E
E.append(secao("E. Responsabilidades", 5))
E.append(pergunta(
    "Quem responde por um equipamento: o setor, uma pessoa, o gestor da \u00e1rea? E quando ele some, "
    "quem \u00e9 cobrado?",
    "Identificar a responsabilidade real, que costuma diferir da formal."))
E.append(pergunta(
    '<font color="%s">[Quadro 3]</font> Nas atividades abaixo, quem executa, quem autoriza e quem '
    'precisa ficar sabendo?' % VERMELHO.hexval(),
    "Mapear pap\u00e9is a partir do processo atual, e n\u00e3o de perfis propostos pelo grupo.", 0))
E.append(quadro(
    ["Atividade", "Quem executa", "Quem autoriza", "Quem fica sabendo"],
    [[c, "", "", ""] for c in [
        "Registrar a chegada de um equipamento novo", "Levar um equipamento para outro setor",
        "Emprestar um equipamento a outra pessoa", "Tirar um equipamento de uso",
        "Chamar a manuten\u00e7\u00e3o", "Encerrar uma manuten\u00e7\u00e3o",
        "Conferir o patrim\u00f4nio", "Descobrir onde um equipamento est\u00e1"]],
    [LARG * 0.40, LARG * 0.20, LARG * 0.20, LARG * 0.20], folga=11))
E.append(Spacer(1, 0.3 * cm))
E.append(pergunta(
    "Existe informa\u00e7\u00e3o sobre os equipamentos que nem todo mundo pode ver?",
    "Levantar restri\u00e7\u00f5es de acesso que a empresa j\u00e1 pratica."))

# ============================================================== F
E.append(secao("F. Manuten\u00e7\u00e3o", 8))
E.append(pergunta(
    "Descreva o que acontece desde a hora em que um equipamento apresenta problema at\u00e9 voltar a "
    "funcionar: quem participa e por quais etapas passa.",
    "Mapear o fluxo de manuten\u00e7\u00e3o com as etapas que a empresa reconhece.", 3))
E.append(pergunta(
    "O que a empresa precisa ter registrado sobre cada manuten\u00e7\u00e3o realizada? E como se sabe "
    "hoje o que j\u00e1 foi feito em um equipamento?",
    "Levantar a exig\u00eancia de hist\u00f3rico e a pr\u00e1tica atual de consulta."))
E.append(pergunta(
    "Existe manuten\u00e7\u00e3o, calibra\u00e7\u00e3o ou inspe\u00e7\u00e3o com prazo "
    "obrigat\u00f3rio? O que acontece se o prazo vencer?",
    "Descobrir obriga\u00e7\u00f5es de prazo, comuns em laborat\u00f3rio e frequentemente "
    "esquecidas."))
E.append(pergunta(
    "Entre saber sempre onde cada equipamento est\u00e1 e ter o hist\u00f3rico de manuten\u00e7\u00e3o "
    "organizado, o que resolveria mais dor primeiro?",
    "Deixar a empresa ordenar as prioridades, em vez de o grupo supor a ordem."))

# ============================================================== G
E.append(secao("G. Registros e regras da empresa", 5))
E.append(pergunta(
    "Existe hoje alguma planilha, caderno ou sistema em que essas informa\u00e7\u00f5es j\u00e1 "
    "estejam registradas? Quem cuida, e o quanto se pode confiar no que est\u00e1 l\u00e1?",
    "Conhecer o registro existente e sua confiabilidade."))
E.append(pergunta(
    "Que informa\u00e7\u00e3o sobre os equipamentos a dire\u00e7\u00e3o cobra, com que frequ\u00eancia, "
    "e como ela \u00e9 produzida hoje?",
    "Descobrir a presta\u00e7\u00e3o de contas que j\u00e1 existe e quanto custa produzi-la."))
E.append(pergunta(
    "Existe regra da empresa sobre onde as informa\u00e7\u00f5es podem ficar guardadas, ou sobre "
    "informa\u00e7\u00e3o da empresa sair da rede interna?",
    "Levantar a restri\u00e7\u00e3o de confidencialidade antes de qualquer proposta."))

# ============================================================== H
E.append(secao("H. Sucesso e pr\u00f3ximos passos", 7))
E.append(pergunta(
    "O que precisaria mudar no dia a dia para a empresa considerar esse problema resolvido? Qual "
    "seria o n\u00famero aceit\u00e1vel, comparado ao de hoje?",
    "Definir o crit\u00e9rio de sucesso e a meta com as palavras do cliente."))
E.append(pergunta(
    "Quem acompanha esse assunto do lado de voc\u00eas, com que frequ\u00eancia podemos conversar, e "
    "seria poss\u00edvel visitar a \u00e1rea e conversar alguns minutos com um t\u00e9cnico de "
    "manuten\u00e7\u00e3o?",
    "Estabelecer o ponto focal e viabilizar a observa\u00e7\u00e3o em campo, que sustenta a persona.",
    3))
E.append(pergunta(
    "Existe algo importante sobre esse problema que n\u00e3o perguntamos?",
    "Abrir espa\u00e7o para o que o roteiro n\u00e3o previu. Costuma ser a resposta mais \u00fatil da "
    "entrevista.", 3))

E.append(Spacer(1, 0.15 * cm))
E.append(Paragraph(
    "<b>N\u00e3o apresente o prot\u00f3tipo nesta conversa.</b> Se houver interesse em ver o que o "
    "grupo vem construindo, agende uma segunda reuni\u00e3o para isso. Misturar as duas coisas "
    "compromete a entrevista: a partir do momento em que a solu\u00e7\u00e3o aparece, as respostas "
    "passam a falar dela, e n\u00e3o do problema.", S_NOTA))

# ============================================================== sintese
E.append(PageBreak())
E.append(Paragraph("S\u00edntese p\u00f3s-entrevista", S_SECAO))
E.append(Paragraph(
    "Trabalho do grupo, sem a empresa, em at\u00e9 vinte e quatro horas ap\u00f3s a conversa. \u00c9 "
    "aqui que as respostas sobre o problema viram decis\u00e3o de projeto: a entrevista levanta o "
    "que acontece na empresa, e esta s\u00edntese traduz isso em requisito.", S_TXT))

E.append(Paragraph("Quadro A \u2013 Revis\u00e3o da matriz CSD", S_SUBSECAO))
E.append(quadro(
    ["Suposi\u00e7\u00e3o registrada pelo grupo", "Confirmada ou rejeitada",
     "O que a empresa disse"],
    [["O t\u00e9cnico consulta junto \u00e0 m\u00e1quina, pelo celular", "", ""],
     ["Todo t\u00e9cnico tem celular e pode us\u00e1-lo na \u00e1rea", "", ""],
     ["Uma etiqueta sobrevive a \u00f3leo, calor e atrito", "", ""],
     ["H\u00e1 rede sem fio ou sinal no ch\u00e3o de f\u00e1brica", "", ""],
     ["A persona M\u00e1rcio corresponde ao t\u00e9cnico real", "", ""],
     ["Nova suposi\u00e7\u00e3o surgida na entrevista:", "", ""]],
    [LARG * 0.38, LARG * 0.20, LARG * 0.42], folga=17))

E.append(Spacer(1, 0.3 * cm))
E.append(Paragraph("Quadro B \u2013 Do que foi dito ao que ser\u00e1 constru\u00eddo", S_SUBSECAO))
E.append(quadro(
    ["Decis\u00e3o de projeto", "O que a resposta determinou", "Pergunta de origem"],
    [["Ordem de trabalho: localiza\u00e7\u00e3o ou manuten\u00e7\u00e3o primeiro", "", ""],
     ["Equipamentos contemplados na primeira etapa", "", ""],
     ["Regra de circula\u00e7\u00e3o entre setores", "", ""],
     ["Consulta por quem n\u00e3o tem acesso ao sistema", "", ""],
     ["Aproveitamento do registro que j\u00e1 existe", "", ""],
     ["Onde a informa\u00e7\u00e3o pode ficar guardada", "", ""]],
    [LARG * 0.42, LARG * 0.40, LARG * 0.18], folga=15))

E.append(Spacer(1, 0.3 * cm))
E.append(Paragraph("Quadro C \u2013 Pend\u00eancias", S_SUBSECAO))
E.append(quadro(
    ["O que ficou em aberto ou precisa mudar no projeto", "Respons\u00e1vel", "Prazo"],
    [["", "", ""] for _ in range(6)],
    [LARG * 0.56, LARG * 0.26, LARG * 0.18], folga=15))

E.append(Spacer(1, 0.4 * cm))
E.append(Paragraph(
    "Elaborado pelo GR4, turma 2IEDS, 2026: Enzo Avanze, Felipe Bertaco Cruz, Jo\u00e3o Pedro Tomazini "
    "Rodrigues, Julia Possebon Leme, Nicolas Fernandes Santos, Rafael Brecci de Souza e Vitor Matheus "
    "Canali Pereira. Documento derivado do roteiro de apoio fornecido pelo docente respons\u00e1vel.",
    S_NOTA))

# ----------------------------------------------------------------- monta
doc = BaseDocTemplate(
    SAIDA, pagesize=A4,
    leftMargin=MARG_E, rightMargin=MARG_D, topMargin=MARG_S, bottomMargin=MARG_I,
    title="Roteiro de Entrevista Exploratoria - GR4 - Demanda 12569",
    author="GR4 - Turma 2IEDS")
frame = Frame(MARG_E, MARG_I, LARG, A4[1] - MARG_S - MARG_I, id="corpo",
              leftPadding=0, rightPadding=0, topPadding=0, bottomPadding=0)
doc.addPageTemplates([PageTemplate(id="padrao", frames=[frame], onPage=moldura)])
doc.build(E)
print("gerado: %s | perguntas: %d" % (SAIDA, contador[0]))
