<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Siswa Izin Hari Ini</title>
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
<link href="{{ asset('css/emanu.css') }}" rel="stylesheet">
<style>
/* Hallmark · e-manu design system (editorial, light) · page: layar TV · tokens = project tokens
 * pre-emit critique: P4 H5 E4 S4 R5 V3 */
:root {
  --tv-paper: var(--paper, #faf8f3);
  --tv-ink: var(--ink, #141413);
  --tv-soft: var(--ink-soft, #5b5953);
  --tv-faint: var(--ink-faint, #9b978d);
  --tv-line: var(--line, #d8d3c6);
  --tv-accent: var(--accent, #2d5a3d);
  --tv-font: 'Manrope', system-ui, sans-serif;
  --tv-mono: 'JetBrains Mono', ui-monospace, monospace;
  --tv-gap: clamp(14px, 2.4vw, 44px);
  --tv-row: clamp(14px, 1.45vw, 26px);
}
* { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; overflow-x: clip; }
body { background: var(--tv-paper); color: var(--tv-ink); font-family: var(--tv-font); display: flex; flex-direction: column; overflow-y: hidden; }
.mono { font-family: var(--tv-mono); }

/* header */
header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 12px var(--tv-gap); padding: var(--tv-gap) var(--tv-gap) calc(var(--tv-gap) * .6); border-bottom: 2px solid var(--tv-ink); }
.eyebrow { font-family: var(--tv-mono); font-size: clamp(10px, .9vw, 15px); font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--tv-soft); }
h1 { font-size: clamp(28px, 4.2vw, 76px); font-weight: 800; letter-spacing: -.025em; line-height: 1; margin-top: .25em; font-style: normal; overflow-wrap: anywhere; min-width: 0; }
.tgl { font-size: clamp(14px, 1.5vw, 26px); color: var(--tv-soft); margin-top: .5em; }
.kanan { display: flex; align-items: flex-end; gap: var(--tv-gap); }
.angka { display: flex; gap: clamp(14px, 1.8vw, 32px); }
.angka div { display: flex; flex-direction: column; }
.angka b { font-family: var(--tv-mono); font-size: clamp(22px, 2.8vw, 48px); font-weight: 700; line-height: 1; }
.angka span { font-size: clamp(10px, .85vw, 14px); color: var(--tv-soft); margin-top: 4px; white-space: nowrap; }
.jam { font-family: var(--tv-mono); font-size: clamp(36px, 5.4vw, 96px); font-weight: 700; line-height: .9; letter-spacing: -.03em; }

/* ledger */
main { flex: 1; min-height: 0; padding: 0 var(--tv-gap); }
#scroller { height: 100%; overflow: hidden; }
.kolom, .row { display: grid; grid-template-columns: minmax(0, 13rem) minmax(0, 1.6fr) minmax(0, 7.5rem) minmax(0, 8rem) minmax(0, 2fr); gap: 0 clamp(12px, 1.6vw, 28px); align-items: baseline; }
.kolom { font-family: var(--tv-mono); font-size: clamp(9px, .8vw, 13px); font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--tv-faint); padding: 14px 12px 8px; border-bottom: 1px solid var(--tv-line); }
.grup { margin-top: clamp(12px, 1.6vw, 28px); }
.grup-h { display: flex; align-items: baseline; gap: 14px; padding: 0 12px 8px; font-size: clamp(16px, 1.8vw, 32px); font-weight: 800; letter-spacing: -.01em; }
.grup-h .n { font-family: var(--tv-mono); font-size: .6em; font-weight: 700; color: var(--tv-soft); }
.row { padding: clamp(9px, 1vw, 18px) 12px; border-top: 1px solid var(--tv-line); font-size: var(--tv-row); line-height: 1.25; }
.row > * { min-width: 0; overflow-wrap: anywhere; }
.c-waktu { font-family: var(--tv-mono); font-weight: 700; font-size: .92em; }
.c-nama { font-weight: 800; }
.c-kelas, .c-durasi { font-family: var(--tv-mono); font-size: .85em; color: var(--tv-soft); }
.c-alasan { color: var(--tv-ink); }
.row.berlangsung { background: var(--tv-ink); color: var(--tv-paper); border-top-color: var(--tv-ink); }
.row.berlangsung .c-kelas, .row.berlangsung .c-durasi, .row.berlangsung .c-alasan { color: inherit; }
.row.selesai { color: var(--tv-faint); }
.row.selesai .c-nama { font-weight: 500; text-decoration: line-through; text-decoration-thickness: 1px; }
.row.selesai .c-kelas, .row.selesai .c-durasi, .row.selesai .c-alasan { color: inherit; }

.kosong { padding: clamp(40px, 12vh, 160px) 12px; font-size: clamp(22px, 3.2vw, 56px); font-weight: 800; letter-spacing: -.02em; }
.kosong small { display: block; margin-top: .6em; font-size: .38em; font-weight: 500; color: var(--tv-soft); letter-spacing: 0; }

footer { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 6px 20px; padding: 10px var(--tv-gap); border-top: 1px solid var(--tv-line); font-family: var(--tv-mono); font-size: clamp(10px, .85vw, 14px); color: var(--tv-soft); }
#net.off { color: #b3261e; font-weight: 700; }

/* layar sempit (HP): ledger jadi tumpukan */
@media (max-width: 899px) {
  .kolom { display: none; }
  .row { grid-template-columns: minmax(0, 1fr) auto; gap: 2px 12px; }
  .c-waktu { grid-column: 1 / -1; }
  .c-nama { grid-column: 1 / 2; }
  .c-kelas { grid-column: 2 / 3; text-align: right; }
  .c-durasi { grid-column: 1 / -1; }
  .c-alasan { grid-column: 1 / -1; }
  header { align-items: flex-start; }
  .kanan { width: 100%; justify-content: space-between; }
}
</style>
</head>
<body>
<header>
  <div>
    <p class="eyebrow">MA Nurul Ummah Kotagede</p>
    <h1>Siswa izin hari ini</h1>
    <p class="tgl" id="tgl">&nbsp;</p>
  </div>
  <div class="kanan">
    <div class="angka" id="angka"></div>
    <p class="jam" id="jam">--:--</p>
  </div>
</header>

<main>
  <div id="scroller"><p class="kosong">Memuat…</p></div>
</main>

<footer>
  <span>Diperbarui <span id="upd">--:--:--</span> · otomatis tiap 15 detik</span>
  <span id="net">terhubung</span>
</footer>

<script>
(() => {
  const URL_DATA = @json($dataUrl);
  const $ = id => document.getElementById(id);
  const scroller = $('scroller');
  const GRUP = [['berlangsung', 'Sedang izin'], ['akan', 'Akan izin hari ini'], ['selesai', 'Sudah selesai']];
  const el = (tag, cls, txt) => { const e = document.createElement(tag); if (cls) e.className = cls; if (txt != null) e.textContent = txt; return e; };

  function render(d) {
    $('tgl').textContent = d.tanggal;
    $('upd').textContent = d.diperbarui;

    const hitung = k => d.items.filter(i => i.status === k).length;
    const angka = $('angka'); angka.replaceChildren();
    GRUP.forEach(([k, judul]) => {
      const box = el('div');
      box.append(el('b', null, String(hitung(k))), el('span', null, judul.replace(' hari ini', '').toLowerCase()));
      angka.append(box);
    });

    const frag = document.createDocumentFragment();
    if (!d.items.length) {
      const p = el('p', 'kosong', 'Tidak ada siswa izin hari ini.');
      p.append(el('small', null, 'Daftar terisi otomatis begitu ada izin aktif atau disetujui.'));
      frag.append(p);
    } else {
      const kol = el('div', 'kolom');
      ['Waktu', 'Siswa', 'Kelas', 'Durasi', 'Alasan'].forEach(t => kol.append(el('span', null, t)));
      frag.append(kol);
      GRUP.forEach(([k, judul]) => {
        const rows = d.items.filter(i => i.status === k);
        if (!rows.length) return;
        const g = el('section', 'grup');
        const h = el('h2', 'grup-h'); h.append(el('span', null, judul), el('span', 'n', String(rows.length)));
        g.append(h);
        rows.forEach(r => {
          const row = el('div', 'row ' + r.status);
          row.append(el('span', 'c-waktu', r.waktu), el('span', 'c-nama', r.nama), el('span', 'c-kelas', r.kelas), el('span', 'c-durasi', r.durasi), el('span', 'c-alasan', r.alasan));
          g.append(row);
        });
        frag.append(g);
      });
    }
    const pos = scroller.scrollTop;
    scroller.replaceChildren(frag);
    scroller.scrollTop = pos;
  }

  async function muat() {
    try {
      const r = await fetch(URL_DATA, { headers: { Accept: 'application/json' }, cache: 'no-store' });
      if (!r.ok) throw new Error(r.status);
      render(await r.json());
      $('net').textContent = 'terhubung'; $('net').className = '';
    } catch (e) {
      $('net').textContent = 'TERPUTUS — mencoba lagi'; $('net').className = 'off';
    }
  }

  const jam = () => { const d = new Date(); $('jam').textContent = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0'); };

  // Auto-scroll pelan kalau daftar lebih tinggi dari layar; jeda di ujung. Mati jika OS minta reduced motion.
  if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
    let dir = 1, pause = 100;
    setInterval(() => {
      if (scroller.scrollHeight <= scroller.clientHeight + 4) return;
      if (pause > 0) { pause--; return; }
      scroller.scrollTop += dir;
      const end = scroller.scrollTop + scroller.clientHeight >= scroller.scrollHeight - 1;
      if (end || scroller.scrollTop <= 0) { dir = end ? -1 : 1; pause = 100; }
    }, 40);
  }

  jam(); setInterval(jam, 1000);
  muat(); setInterval(muat, 15000);
  setTimeout(() => location.reload(), 60 * 60 * 1000);
})();
</script>
</body>
</html>
