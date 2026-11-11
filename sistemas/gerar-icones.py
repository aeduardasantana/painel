#!/usr/bin/env python3
"""Ícones PNG determinísticos para PWA, sem bibliotecas externas."""
from pathlib import Path
import struct
import zlib

ROOT = Path(__file__).resolve().parent
DARK = (21, 28, 23)
LIME = (204, 237, 81)

def paint(size: int):
    def rect(x, y, a, b):
        return a <= x < b and y >= 0
    raw = bytearray()
    for y in range(size):
        raw.append(0)
        for x in range(size):
            px = (x * 512) // size
            py = (y * 512) // size
            # Quadrado com cantos recortados.
            corner = (
                (px < 52 and py < 52 and (px - 52) ** 2 + (py - 52) ** 2 > 52 ** 2)
                or (px > 460 and py < 52 and (px - 460) ** 2 + (py - 52) ** 2 > 52 ** 2)
                or (px < 52 and py > 460 and (px - 52) ** 2 + (py - 460) ** 2 > 52 ** 2)
                or (px > 460 and py > 460 and (px - 460) ** 2 + (py - 460) ** 2 > 52 ** 2)
            )
            line = (100 <= px < 412 and 108 <= py < 154) or (
                192 <= px < 412 and ((222 <= py < 268) or (337 <= py < 381))
            )
            box = 100 <= px < 150 and ((222 <= py < 272) or (335 <= py < 385))
            check = 110 <= px < 141 and 237 <= py < 262 and abs(py-(255-(px-110)//2)) < 6
            color = (0,0,0) if corner else (DARK if check else LIME if (line or box) else DARK)
            raw.extend(color)
    def chunk(kind, data):
        return (struct.pack(">I", len(data)) + kind + data
                + struct.pack(">I", zlib.crc32(kind + data) & 0xffffffff))
    png = b"\x89PNG\r\n\x1a\n"
    png += chunk(b"IHDR", struct.pack(">IIBBBBB", size, size, 8, 2, 0, 0, 0))
    png += chunk(b"IDAT", zlib.compress(bytes(raw), 9))
    png += chunk(b"IEND", b"")
    (ROOT / ("rotinas-icon-%d.png" % size)).write_bytes(png)

for size in (192, 512):
    paint(size)
