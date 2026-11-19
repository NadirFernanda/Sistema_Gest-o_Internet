import sharp from 'sharp';
import { mkdir } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';

// Arte institucional sem texto: o título e a oferta permanecem no HTML do anúncio.
const output = new URL('../public/img/campaign-demos/', import.meta.url);
await mkdir(output, { recursive: true });

const designs = [
  {
    name: 'internet-para-todos.webp',
    accent: '#FFC21B',
    drawing: `<g fill="none" stroke="#fff" stroke-linecap="round" stroke-width="21">
      <path d="M278 248c112-104 292-104 404 0"/><path d="M344 312c76-70 196-70 272 0"/>
      <path d="M409 378c38-35 104-35 142 0"/></g><circle cx="480" cy="426" r="22" fill="#FFC21B"/>
      <circle cx="189" cy="320" r="12" fill="#FFC21B"/><circle cx="768" cy="166" r="9" fill="#fff" opacity=".72"/>`,
  },
  {
    name: 'wifi-em-casa.webp',
    accent: '#4BD0F8',
    drawing: `<path d="M260 266 480 102l220 164v178a30 30 0 0 1-30 30H290a30 30 0 0 1-30-30Z" fill="#fff" opacity=".94"/>
      <path d="M388 475V348a18 18 0 0 1 18-18h148a18 18 0 0 1 18 18v127" fill="#0A59B7"/>
      <g fill="none" stroke="#FFBD13" stroke-linecap="round" stroke-width="16"><path d="M408 255c40-34 104-34 144 0"/><path d="M441 289c22-18 56-18 78 0"/></g><circle cx="480" cy="315" r="10" fill="#FFBD13"/>`,
  },
  {
    name: 'ligacao-24-horas.webp',
    accent: '#FFC21B',
    drawing: `<circle cx="480" cy="270" r="175" fill="#fff" opacity=".94"/>
      <circle cx="480" cy="270" r="136" fill="none" stroke="#1777D7" stroke-width="12"/>
      <g fill="none" stroke="#1470D1" stroke-width="12" stroke-linecap="round"><path d="M480 160v110l77 47"/><path d="M480 134v17M480 389v17M344 270h17M599 270h17"/></g>
      <path d="M282 368c48 78 157 112 252 83" fill="none" stroke="#FFC21B" stroke-width="18" stroke-linecap="round"/>
      <path d="m518 427 23 22-33 8" fill="none" stroke="#FFC21B" stroke-width="14" stroke-linecap="round" stroke-linejoin="round"/>`,
  },
];

for (const design of designs) {
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="960" height="540" viewBox="0 0 960 540">
    <defs><linearGradient id="bg" x2="1" y2="1"><stop stop-color="#06367C"/><stop offset=".56" stop-color="#0867C8"/><stop offset="1" stop-color="#0B8EE5"/></linearGradient></defs>
    <rect width="960" height="540" fill="url(#bg)"/>
    <circle cx="885" cy="-54" r="252" fill="${design.accent}" opacity=".13"/>
    <circle cx="63" cy="552" r="242" fill="#fff" opacity=".06"/>
    <path d="M80 92h224M80 116h136M741 459h129" stroke="#fff" stroke-width="5" opacity=".15" stroke-linecap="round"/>
    ${design.drawing}
  </svg>`;
  await sharp(Buffer.from(svg)).webp({ quality: 88 }).toFile(fileURLToPath(new URL(design.name, output)));
}
