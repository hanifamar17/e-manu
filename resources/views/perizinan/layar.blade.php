<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Izin Siswa Hari Ini · e-manu</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
<link href="{{ asset('css/emanu.css') }}" rel="stylesheet">
<style>
/* Papan keberangkatan TV — kontras tinggi, minimalis */
:root {
  --tv-paper: var(--paper, #faf8f3);
  --tv-ink: var(--ink, #141413);
  --tv-soft: var(--ink-soft, #3d3b36);
  --tv-faint: var(--ink-faint, #6b675f);
  --tv-line: var(--line, #c8c2b4);
  --tv-accent: var(--accent, #2d5a3d);
  --tv-font: 'Manrope', system-ui, sans-serif;
  --tv-mono: 'JetBrains Mono', ui-monospace, monospace;
  --tv-gap: clamp(18px, 2.8vw, 52px);
  --tv-row: clamp(16px, 1.6vw, 30px);
  --tv-cols: calc(var(--tv-row) * 6.6) calc(var(--tv-row) * 6.6) minmax(0, 1.5fr) calc(var(--tv-row) * 5.2) minmax(0, 1.7fr) calc(var(--tv-row) * 8);
}

* { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; overflow-x: clip; }
body {
  display: flex; flex-direction: column; overflow-y: hidden;
  background: var(--tv-paper); color: var(--tv-ink); font-family: var(--tv-font);
}

/* Header */
header {
  display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end;
  gap: 10px var(--tv-gap);
  padding: calc(var(--tv-gap) * .7) var(--tv-gap) calc(var(--tv-gap) * .38);
  position: relative;
}
header::after {
  content: "";
  position: absolute;
  left: var(--tv-gap);
  right: var(--tv-gap);
  bottom: 0;
  height: 2px;
  background: var(--tv-ink);
}
.inst {
  font-size: clamp(13px, 1.15vw, 20px);
  font-weight: 700;
  color: var(--tv-soft);
  letter-spacing: .03em;
}
h1 {
  margin-top: .12em; min-width: 0;
  font-size: clamp(24px, 3.2vw, 54px);
  font-weight: 800; letter-spacing: -.03em; line-height: 1.05;
  overflow-wrap: anywhere;
}
.tgl {
  margin-top: .3em;
  font-size: clamp(15px, 1.4vw, 24px);
  color: var(--tv-soft);
  font-weight: 500;
}
.jam {
  font-family: var(--tv-mono);
  font-size: clamp(40px, 5.2vw, 92px);
  font-weight: 700; line-height: .9; letter-spacing: -.04em;
}

/* Papan */
main { flex: 1; min-height: 0; padding: 0 var(--tv-gap); }
#scroller { height: 100%; overflow: hidden; }
.kolom, .row {
  display: grid; grid-template-columns: var(--tv-cols);
  gap: 0 clamp(12px, 1.6vw, 28px); align-items: center;
}

/* Header tabel */
.kolom {
  padding: clamp(16px, 1.4vw, 24px) 0 14px;
  font-size: clamp(15px, 1.25vw, 22px);
  font-weight: 800;
  color: var(--tv-ink);
  letter-spacing: .04em;
  text-transform: uppercase;
  border-bottom: 2px solid var(--tv-ink);
}
.row {
  padding: clamp(10px, 1.15vw, 20px) 0;
  border-bottom: 1px solid var(--tv-line);
  font-size: var(--tv-row); line-height: 1.25;
}
.row > * { min-width: 0; overflow-wrap: anywhere; }

/* Ubin jam */
.waktu { display: flex; flex-direction: column; align-items: flex-start; gap: .22em; }
.tiles {
  display: inline-flex; align-items: center; gap: .08em;
  font-family: var(--tv-mono); font-size: 1.3em; font-weight: 700; line-height: 1;
}
.t {
  position: relative; display: inline-flex; align-items: center; justify-content: center;
  width: .78em; height: 1.28em; border-radius: .12em;
  background: var(--tv-ink); color: var(--tv-paper);
}
.t::after {
  content: ""; position: absolute; left: 0; right: 0; top: 50%;
  height: 1px; background: var(--tv-paper); opacity: .25;
}
.sep { width: .3em; text-align: center; }
.tsub { font-size: .58em; font-weight: 600; color: var(--tv-soft); }

/* Isi baris */
.c-nama { font-weight: 800; letter-spacing: -.01em; }
.c-kelas { font-family: var(--tv-mono); font-size: .88em; font-weight: 700; }
.c-alasan { color: var(--tv-ink); font-weight: 500; }
.c-alasan small {
  display: block; margin-top: .18em;
  font-size: .65em; color: var(--tv-soft); font-weight: 500;
}

/* Status */
.status {
  justify-self: start;
  padding: .26em .85em;
  border-radius: 999px;
  font-size: .68em;
  font-weight: 700;
  white-space: nowrap;
  letter-spacing: .02em;
  border: 1px solid transparent;
  background: transparent;
  color: var(--tv-ink);
}

.row.berlangsung .status {
  background: var(--tv-accent);
  border-color: var(--tv-accent);
  color: var(--tv-paper);
}

.row.akan .status {
  background: color-mix(in srgb, var(--tv-accent) 12%, transparent);
  border-color: color-mix(in srgb, var(--tv-accent) 30%, transparent);
  color: var(--tv-accent);
}

.row.selesai {
  color: var(--tv-faint);
}
.row.selesai .c-alasan,
.row.selesai .c-alasan small,
.row.selesai .tsub {
  color: inherit;
}
.row.selesai .t {
  background: #8a857a;
  color: var(--tv-paper);
}
.row.selesai .t::after {
  background: var(--tv-paper);
  opacity: .35;
}
.row.selesai .status {
  background: transparent;
  border-color: var(--tv-line);
  color: var(--tv-faint);
}

/* ========== EMPTY STATE (diperbaiki) ========== */
.kosong {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 0 20px;
}

.kosong .judul-kosong {
  font-size: clamp(18px, 2.4vw, 32px);
  font-weight: 600;
  color: var(--tv-soft);
  letter-spacing: -0.01em;
  line-height: 1.3;
}

.kosong .ket-kosong {
  margin-top: 10px;
  font-size: clamp(13px, 1.3vw, 18px);
  font-weight: 500;
  color: var(--tv-faint);
  max-width: 36ch;
  line-height: 1.45;
}

/* Footer */
footer {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 16px var(--tv-gap) 18px;
  border-top: 2px solid var(--tv-line);
  font-size: clamp(16px, 1.5vw, 26px);
  font-weight: 600;
  color: var(--tv-soft);
  letter-spacing: .01em;
}
#upd { color: var(--tv-ink); font-weight: 700; }
#net.off {
  color: #b91c1c;
  font-weight: 800;
}

/* Layar sempit */
@media (max-width: 899px) {
  .kolom { display: none; }
  .row { grid-template-columns: minmax(0, 1fr) auto; gap: .35em 12px; }
  .row .w-keluar, .row .w-kembali { grid-row: 1; }
  .w-keluar { grid-column: 1 / 2; }
  .w-kembali { grid-column: 2 / 3; justify-self: end; align-items: flex-end; }
  .c-nama { grid-column: 1 / 2; }
  .c-kelas { grid-column: 2 / 3; text-align: right; }
  .c-alasan { grid-column: 1 / -1; }
  .status { grid-column: 1 / -1; }
}
</style>
</head>
<body>
<header>
  <div>
    <p class="inst">MA Nurul Ummah Kotagede</p>
    <h1>Izin siswa hari ini</h1>
    <p class="tgl" id="tgl">&nbsp;</p>
  </div>
  <p class="jam" id="jam">--:--</p>
</header>

<main>
  <div id="scroller"><p class="kosong">Memuat…</p></div>
</main>

<footer>
  <span>Diperbarui <span id="upd">--:--:--</span> · <span id="net">terhubung</span></span>
</footer>

<script>
(() => {
  const URL_DATA = @json($dataUrl);
  const JEDA = 30000;
  const JEDA_MAKS = 120000;
  const TIMEOUT = 8000;

  const STATUS = { berlangsung: 'Sedang izin', akan: 'Akan izin', selesai: 'Selesai' };
  const $ = id => document.getElementById(id);
  const scroller = $('scroller');
  const el = (tag, cls, txt) => {
    const e = document.createElement(tag);
    if (cls) e.className = cls;
    if (txt != null) e.textContent = txt;
    return e;
  };

  function ubin(jam) {
    const w = el('span', 'tiles');
    w.setAttribute('aria-hidden', 'true');
    [...jam].forEach(ch => w.append(ch === ':' ? el('span', 'sep', ':') : el('span', 't', ch)));
    return w;
  }

  function urai(waktu) {
    const bagian = waktu.includes('→') ? waktu.split('→') : waktu.split(/\s[–-]\s/);
    const multi = waktu.includes('→');
    return bagian.slice(0, 2).map(s => {
      const m = s.match(/(\d{1,2}:\d{2})\s*$/);
      return { jam: m ? m[1].padStart(5, '0') : s.trim(), hari: multi ? s.replace(/\s*\d{1,2}:\d{2}\s*$/, '').trim() : '' };
    });
  }

  function sel(kls, w) {
    const c = el('div', 'waktu ' + kls);
    c.setAttribute('aria-label', w.jam);
    c.append(ubin(w.jam));
    if (w.hari) c.append(el('span', 'tsub', w.hari));
    return c;
  }

  function render(d) {
    $('upd').textContent = d.diperbarui;

    const frag = document.createDocumentFragment();
    if (!d.items.length) {
      const p = el('div', 'kosong');
      p.append(
        el('p', 'judul-kosong', 'Tidak ada siswa izin hari ini.'),
        el('p', 'ket-kosong', 'Daftar terisi otomatis begitu ada izin aktif atau disetujui.')
      );
      frag.append(p);
    } else {
      const kol = el('div', 'kolom');
      ['Keluar', 'Kembali', 'Siswa', 'Kelas', 'Alasan', 'Status'].forEach(t => kol.append(el('span', null, t)));
      frag.append(kol);

      d.items.forEach(r => {
        const [a, b] = urai(r.waktu);
        const row = el('div', 'row ' + r.status);
        const alasan = el('div', 'c-alasan', r.alasan);
        if (r.durasi) alasan.append(el('small', null, r.durasi));
        row.append(
          sel('w-keluar', a),
          sel('w-kembali', b || a),
          el('div', 'c-nama', r.nama),
          el('div', 'c-kelas', r.kelas),
          alasan,
          el('span', 'status', STATUS[r.status] || r.status)
        );
        frag.append(row);
      });
    }

    const pos = scroller.scrollTop;
    scroller.replaceChildren(frag);
    scroller.scrollTop = pos;
  }

  let timer, gagal = 0;
  const jadwal = ms => { clearTimeout(timer); timer = setTimeout(muat, ms); };

  async function muat() {
    clearTimeout(timer);
    if (document.hidden) { jadwal(JEDA); return; }

    const ac = new AbortController();
    const to = setTimeout(() => ac.abort(), TIMEOUT);
    try {
      const r = await fetch(URL_DATA, { headers: { Accept: 'application/json' }, cache: 'no-store', signal: ac.signal });
      if (!r.ok) throw new Error(r.status);
      render(await r.json());
      gagal = 0;
      $('net').textContent = 'terhubung';
      $('net').className = '';
    } catch (e) {
      gagal++;
      $('net').textContent = 'TERPUTUS, mencoba lagi';
      $('net').className = 'off';
    } finally {
      clearTimeout(to);
      jadwal(Math.min(JEDA * 2 ** gagal, JEDA_MAKS));
    }
  }

  let menit = '';
  const jam = () => {
    const d = new Date();
    const j = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    if (j === menit) return;
    menit = j;
    $('jam').textContent = j;
    $('tgl').textContent = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  };

  if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
    let dir = 1, pause = 100;
    setInterval(() => {
      if (document.hidden || scroller.scrollHeight <= scroller.clientHeight + 4) return;
      if (pause > 0) { pause--; return; }
      scroller.scrollTop += dir;
      const end = scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 1;
      if (end || scroller.scrollTop <= 0) { dir = end ? -1 : 1; pause = 100; }
    }, 40);
  }

  document.addEventListener('visibilitychange', () => { if (!document.hidden) muat(); });

  jam();
  setInterval(jam, 1000);
  muat();
  setTimeout(() => location.reload(), 3600000 + Math.random() * 120000);
})();
</script>
</body>
</html>