@extends('layouts.app')

@section('title', 'Izin Siswa Hari Ini')

@section('head')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
<div class="mx-auto w-full max-w-5xl px-5 py-5 md:px-6 md:py-8 2xl:max-w-7xl">

    <div class="flex items-end justify-between gap-3 border-b border-[var(--ink)] pb-4">
        <div class="min-w-0">
            <h1 class="text-xl font-semibold leading-tight tracking-tight md:text-3xl 2xl:text-5xl">Izin siswa hari ini</h1>
            <p id="tgl" class="text-xs text-[var(--ink-soft)] md:text-base 2xl:text-2xl">&nbsp;</p>
        </div>
        <p id="jam" class="mono text-3xl leading-none tracking-tighter md:text-5xl 2xl:text-7xl">--:--</p>
    </div>

    <div class="hidden grid-cols-[4.5rem_1.2fr_9rem_1.5fr_6.5rem] gap-x-5 border-b border-[var(--line)] py-2 text-xs font-extrabold uppercase tracking-wider text-[var(--ink-faint)] md:grid 2xl:text-base">
        <span>Kelas</span><span>Siswa</span><span>Pukul</span><span>Alasan</span><span>Status</span>
    </div>

    <div id="list"></div>

    <p class="mt-3 text-center text-xs text-[var(--ink-faint)] md:text-sm">
        Diperbarui <b id="upd" class="text-[var(--ink)]">--:--:--</b> · <span id="net">terhubung</span>
    </p>
</div>
@endsection

@section('scripts')
<script>
(() => {
  const URL_DATA = @json($dataUrl);
  const JEDA = 30000, JEDA_MAKS = 120000, TIMEOUT = 8000;
  const $ = id => document.getElementById(id);
  const el = (tag, cls, txt = '') => Object.assign(document.createElement(tag), { className: cls, textContent: txt });

  const ROW = 'grid grid-cols-2 items-center gap-x-3 gap-y-0.5 border-b border-[var(--line)] py-2.5 text-sm md:grid-cols-[4.5rem_1.2fr_9rem_1.5fr_6.5rem] md:gap-x-5 md:py-2 md:text-base 2xl:text-xl';
  const STATUS = {
    berlangsung: ['Sedang izin', 'bg-[var(--accent)] text-white'],
    akan: ['Akan izin', 'text-[var(--accent)] ring-1 ring-[var(--accent)]'],
    selesai: ['Selesai', 'text-[var(--ink-faint)] ring-1 ring-[var(--line)]'],
  };

  // "10:00 → 16:59" | "Kam 10:00 → Jum 07:00" | "10:00 - 16:59"
  const waktu = w => w.split(w.includes('→') ? '→' : /\s[–-]\s/).slice(0, 2)
    .map(s => s.trim().replace(/(^|\s)(\d:\d{2})$/, '$10$2')).join(' → ');

  function render(d) {
    $('upd').textContent = d.diperbarui;
    const rows = d.items.map(r => {
      const [label, cls] = STATUS[r.status] || [r.status, ''];
      const row = el('div', ROW + (r.status === 'selesai' ? ' opacity-50' : ''));
      row.append(
        el('span', 'mono text-[var(--ink-faint)] md:bg-transparent md:p-0 md:text-[var(--ink)]', r.kelas),
        el('b', 'col-span-2 text-[1em] leading-tight md:col-auto', r.nama),
        el('span', 'mono col-span-2 text-[.95em] md:col-auto', waktu(r.waktu)),
        el('span', 'col-span-2 text-[.92em] text-[var(--ink-faint)] md:col-auto', [r.alasan, r.durasi].filter(Boolean).join(' · ')),
        el('span', `col-start-2 row-start-1 justify-self-end whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs md:col-auto md:row-auto md:justify-self-start ${cls}`, label)
      );
      return row;
    });
    $('list').replaceChildren(...(rows.length ? rows : [
      el('p', 'py-16 text-center text-[var(--ink-faint)]', 'Tidak ada siswa izin hari ini.'),
    ]));
  }

  let timer, gagal = 0;
  const jadwal = ms => { clearTimeout(timer); timer = setTimeout(muat, ms); };

  async function muat() {
    clearTimeout(timer);
    if (document.hidden) return jadwal(JEDA);
    const ac = new AbortController();
    const to = setTimeout(() => ac.abort(), TIMEOUT);
    try {
      const r = await fetch(URL_DATA, { headers: { Accept: 'application/json' }, cache: 'no-store', signal: ac.signal });
      if (!r.ok) throw new Error(r.status);
      render(await r.json());
      gagal = 0;
    } catch {
      gagal++;
    } finally {
      clearTimeout(to);
      $('net').textContent = gagal ? 'TERPUTUS, mencoba lagi' : 'terhubung';
      $('net').className = gagal ? 'font-extrabold text-red-700' : '';
      jadwal(Math.min(JEDA * 2 ** gagal, JEDA_MAKS));
    }
  }

  const jam = () => {
    const d = new Date();
    $('jam').textContent = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':');
    $('tgl').textContent = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  };

  document.addEventListener('visibilitychange', () => { if (!document.hidden) muat(); });
  jam(); setInterval(jam, 1000); muat();
  setTimeout(() => location.reload(), 3600000 + Math.random() * 120000);
})();
</script>
@endsection