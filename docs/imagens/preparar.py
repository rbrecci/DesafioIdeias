# -*- coding: utf-8 -*-
# Prepara as fotografias dos templates para entrar no documento ABNT.
# Recorta a area do papel (a foto costuma trazer mesa, chao e maos em volta),
# gira o que foi fotografado deitado e regrava com qualidade adequada a impressao.
# Uso: python preparar.py
import io
import json
import os
import sys

from PIL import Image, ImageOps

# arquivo de origem -> (nome final, graus de rotacao, recortar?)
ORIGENS = [
    ("bruto/01-persona.jpeg",           "01-persona.jpeg",           0,   True),
    ("bruto/02-mapa-empatia.jpeg",      "02-mapa-empatia.jpeg",      -90, True),
    ("bruto/03-matriz-csd.jpeg",        "03-matriz-csd.jpeg",        90,  True),
    ("bruto/04-matriz-prioridade.jpeg", "04-matriz-prioridade.jpeg", 90,  True),
    ("bruto/05-equipe.jpeg",            "05-equipe.jpeg",            0,   False),
]


def caixa_do_papel(im, limiar=165, ocupacao=0.25):
    """Recorta a folha.

    Um bounding box simples da regiao clara nao serve: mesa e parede tambem sao
    claras e o box acaba pegando a foto inteira. A folha se distingue por ocupar
    a maior parte da linha ou da coluna, entao a deteccao e feita por proporcao
    de pixels claros em cada linha e em cada coluna.
    """
    cinza = ImageOps.grayscale(im)
    claros = cinza.point(lambda v: 1 if v > limiar else 0)
    px = claros.load()
    larg, alt = im.size

    linhas = [sum(px[x, y] for x in range(0, larg, 4)) / (larg / 4.0) for y in range(alt)]
    colunas = [sum(px[x, y] for y in range(0, alt, 4)) / (alt / 4.0) for x in range(larg)]

    def faixa(valores):
        dentro = [i for i, v in enumerate(valores) if v >= ocupacao]
        return (dentro[0], dentro[-1]) if dentro else None

    fy = faixa(linhas)
    fx = faixa(colunas)
    if not fy or not fx:
        return None

    folga_x = int(larg * 0.01)
    folga_y = int(alt * 0.01)
    return (
        max(0, fx[0] - folga_x),
        max(0, fy[0] - folga_y),
        min(larg, fx[1] + folga_x),
        min(alt, fy[1] + folga_y),
    )


def main():
    dims = {}
    for origem, destino, graus, recortar in ORIGENS:
        if not os.path.exists(origem):
            print("ausente, mantido como esta: %s" % origem)
            if os.path.exists(destino):
                im = Image.open(destino)
                dims[destino] = {"w": im.size[0], "h": im.size[1]}
            continue
        im = Image.open(origem)
        im = ImageOps.exif_transpose(im)
        if graus:
            im = im.rotate(graus, expand=True)
        if recortar:
            caixa = caixa_do_papel(im)
            if caixa:
                im = im.crop(caixa)
        im.save(destino, quality=90, optimize=True)
        dims[destino] = {"w": im.size[0], "h": im.size[1]}
        print("%s -> %s %s" % (origem, destino, im.size))

    json.dump(dims, io.open("dimensoes.json", "w", encoding="utf-8"), indent=2)
    print("dimensoes.json atualizado com %d imagens" % len(dims))


if __name__ == "__main__":
    main()
