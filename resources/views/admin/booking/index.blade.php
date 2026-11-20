@extends('layouts.app')
@section('title', 'Admin: Pengajuan Booking')

@section('head')
<style>
  /* HP (<640px): kartu 2 kolom. Desktop: tabel kolom dibagi rata. */
  .bk-desk { display: none; }
  .bk-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
  .bk-c1 { width: 26%; } .bk-c2 { width: 24%; } .bk-c3 { width: 20%; } .bk-c5 { width: 15%; } .bk-c6 { width: 15%; }
  .bk-c5 { white-space: nowrap; }
  @media (min-width: 640px) { .bk-desk { display: block; } .bk-cards { display: none; } }
  .bk-card { padding: 14px 0; border-top: 1px solid var(--line); }
  .bk-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
  .bk-card-top > div:first-child { min-width: 0; }
  .bk-card-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; margin-top: 12px; }
  .bk-card-grid .v { font-size: 13px; line-height: 1.45; overflow-wrap: anywhere; }
  .bk-card-grid .k { font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--ink-faint); margin-bottom: 2px; }
  .bk-card-grid .nowrap { white-space: nowrap; }
  .bk-card-act { display: flex; justify-content: flex-end; margin-top: 12px; }

  /* Tombol kecil di baris */
  .bk-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; font-size: 12px; font-weight: 600; cursor: pointer; touch-action: manipulation; user-select: none; transition: background .15s, color .15s, transform .1s; }
  .bk-btn:active { transform: scale(.97); }
  .bk-btn-line { border: 1px solid var(--line); background: #fff; color: var(--ink); }
  .bk-btn-line:hover { background: var(--ink); color: var(--paper); border-color: var(--ink); }
  .bk-btn-danger { color: #b3261e; border: 1px solid var(--line); background: #fff; }
  .bk-btn-danger:hover { background: #b3261e; color: #fff; border-color: #b3261e; }

  /* Dropdown aksi (satu menu bersama, posisi fixed supaya tidak terpotong tabel) */
  .bk-menu { position: fixed; z-index: 60; min-width: 168px; padding: 4px; background: var(--paper); border: 1px solid var(--line); box-shadow: 0 10px 28px rgba(0,0,0,.10); }
  .bk-menu[hidden] { display: none; }
  .bk-menu form { margin: 0; }
  .bk-mi { display: block; width: 100%; text-align: left; padding: 10px 12px; font-size: 13px; font-weight: 600; background: none; border: 0; color: var(--ink); cursor: pointer; touch-action: manipulation; }
  .bk-mi:hover { background: var(--ink); color: var(--paper); }
  .bk-mi[disabled] { opacity: .4; cursor: not-allowed; pointer-events: none; }
  .bk-mi-danger { color: #b3261e; }
  .bk-mi-danger:hover { background: #b3261e; color: #fff; }
  .bk-mi-sep { height: 1px; margin: 4px 0; background: var(--line); }

  /* Dialog tolak / hapus */
  .bk-dlg { margin: auto; padding: 0; width: 92vw; max-width: 440px; border: 1px solid var(--line); background: var(--paper); color: var(--ink); }
  .bk-dlg form { display: flex; flex-direction: column; gap: 14px; padding: 24px; }
  .bk-dlg h2 { margin: 0; font-size: 18px; font-weight: 800; }
  .bk-dlg p { margin: 0; font-size: 14px; line-height: 1.5; }
  .bk-dlg .nama { font-weight: 700; }
  .bk-dlg .muted { color: var(--ink-soft); }
  .bk-dlg label { display: block; margin-bottom: 4px; }
  .bk-dlg input { display: block; width: 100%; padding: 10px 0; font-size: 14px; background: transparent; border: 0; border-bottom: 1px solid var(--line); border-radius: 0; color: var(--ink); }
  .bk-dlg input:focus { outline: none; box-shadow: none; border-color: var(--accent); }
  .bk-dlg .acts { display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px; }
  .bk-b { padding: 10px 18px; font-size: 13px; font-weight: 600; cursor: pointer; touch-action: manipulation; transition: background .15s, color .15s, transform .1s; }
  .bk-b:active { transform: scale(.97); }
  .bk-b-line { border: 1px solid var(--line); background: #fff; color: var(--ink); }
  .bk-b-line:hover { background: var(--ink); color: var(--paper); border-color: var(--ink); }
  .bk-b-red { border: 1px solid #b3261e; background: #b3261e; color: #fff; }
  .bk-b-red:hover { background: #8f1d17; border-color: #8f1d17; }
</style>
@endsection

@section('content')
@php
    $fld = 'py-2.5 px-0 text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
@endphp
<div class="max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('admin.home') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>

  <div class="flex flex-wrap items-end justify-between gap-3 mt-8 mb-6">
    <div>
      <h1 class="page-title text-2xl font-bold m-0">Pengajuan Booking Lab</h1>
      <p class="text-sm text-[var(--ink-soft)] mt-1 mb-0">
        Menunggu ACC: <span class="font-bold {{ $jumlahPending ? 'text-red-600' : 'text-[var(--ink)]' }}">{{ $jumlahPending }}</span>
        &middot; Ditampilkan: <span class="font-semibold text-[var(--ink)]">{{ $items->total() }}</span>
      </p>
    </div>
    <a data-lock href="{{ route('admin.kalender.index') }}" class="underline text-sm cursor-pointer touch-manipulation select-none transition hover:text-[var(--ink)] active:scale-95">Kalender akademik</a>
  </div>

  @include('partials.flash')
  @if ($errors->has('booking'))
    <p class="text-sm text-red-600 mb-4 m-0">{{ $errors->first('booking') }}</p>
  @endif

  <div class="flex flex-wrap gap-2 mb-5 mono text-xs font-bold uppercase tracking-widest">
    @foreach (['pending' => 'Menunggu ('.$jumlahPending.')', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'] as $k => $label)
      <a data-lock href="{{ route('admin.booking.index', ['status' => $k]) }}"
        class="inline-block px-3 py-1.5 border border-[var(--line)] cursor-pointer touch-manipulation select-none transition active:scale-[0.97]
          {{ $status === $k
            ? 'bg-[var(--ink)] text-[var(--paper)] border-[var(--ink)]'
            : 'text-[var(--ink-soft)] hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)]' }}">
        {{ $label }}
      </a>
    @endforeach
  </div>

  <form data-lock method="GET" class="flex items-end gap-3 mb-6">
    <input type="hidden" name="status" value="{{ $status }}">
    <input name="q" value="{{ $cari }}" placeholder="Cari kegiatan, pemesan, atau email..." enterkeyhint="search" class="flex-1 min-w-0 {{ $fld }}">
    <button type="submit" class="bk-btn bk-btn-line">Cari</button>
  </form>

  {{-- DESKTOP: tabel --}}
  <div class="bk-desk">
    <table class="bk-table text-sm">
      <thead>
        <tr>
          <th class="text-left py-2 eyebrow pr-4 bk-c1">Kegiatan</th>
          <th class="text-left py-2 eyebrow pr-4 bk-c2">Pemesan</th>
          <th class="text-left py-2 eyebrow pr-4 bk-c3">Tanggal</th>
          <th class="text-left py-2 eyebrow pr-4 bk-c5">Status</th>
          <th class="bk-c6"></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($items as $b)
        <tr style="border-top:1px solid var(--line)">
          <td class="py-3 pr-4 align-top bk-c1">
            <div class="font-semibold">{{ $b->nama_kegiatan }}</div>
            <div class="mono text-[11px] text-[var(--ink-soft)]">{{ $b->kode }}</div>
            @if ($b->catatan_tambahan)<div class="text-[12px] text-[var(--ink-soft)] mt-1">Catatan: {{ $b->catatan_tambahan }}</div>@endif
            @if ($b->status === 'ditolak' && $b->alasan_penolakan)<div class="text-[12px] mt-1">Alasan ditolak: {{ $b->alasan_penolakan }}</div>@endif
            @if ($b->ada_bentrok ?? false)<div class="text-[12px] font-bold text-red-600 mt-1">Bentrok dengan booking yang sudah disetujui.</div>@endif
          </td>
          <td class="py-3 pr-4 align-top bk-c2">
            <div class="font-semibold">{{ $b->nama_pemesan }}</div>
            <div class="mono text-[11px] text-[var(--ink-soft)]">{{ $b->eksternal ? 'EKSTERNAL' : 'INTERNAL' }}</div>
            <div class="text-[12px] text-[var(--ink-soft)] break-all">{{ $b->email }}</div>
          </td>
          <td class="py-3 pr-4 align-top bk-c3">
            <div class="mono text-[12px]">{{ $b->tanggal->translatedFormat('D, j M Y') }}</div>
            <div class="mono text-[12px] text-[var(--ink-soft)] mt-0.5">{{ $b->jam }}</div>
          </td>
          <td class="py-3 pr-4 align-top bk-c5">@include('partials.pz-badge', ['s' => $b->status === 'pending' ? 'menunggu_acc' : $b->status])</td>
          <td class="py-3 align-top bk-c6 text-right">
            @if ($b->status === 'pending')
                <button type="button" class="bk-btn bk-btn-line" data-aksi
                  data-nama="{{ $b->nama_kegiatan }}"
                  data-setujui="{{ route('admin.booking.setujui', $b) }}"
                  data-tolak="{{ route('admin.booking.tolak', $b) }}"
                  data-hapus="{{ route('admin.booking.destroy', $b) }}"
                  data-bentrok="{{ ($b->ada_bentrok ?? false) ? 1 : 0 }}"
                  aria-haspopup="menu" aria-expanded="false">
                  Aksi <i class="ph ph-caret-down"></i>
                </button>
              @else
                <button type="button" class="bk-btn bk-btn-danger" data-hapus-langsung
                  data-nama="{{ $b->nama_kegiatan }}" data-hapus="{{ route('admin.booking.destroy', $b) }}">Hapus</button>
              @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  {{-- HP: kartu 2 kolom --}}
  <div class="bk-cards">
    @foreach ($items as $b)
    <div class="bk-card">
      <div class="bk-card-top">
        <div>
          <div class="font-semibold">{{ $b->nama_kegiatan }}</div>
          <div class="mono text-[11px] text-[var(--ink-soft)]">{{ $b->kode }}</div>
        </div>
        <div class="shrink-0">@include('partials.pz-badge', ['s' => $b->status === 'pending' ? 'menunggu_acc' : $b->status])</div>
      </div>
      <div class="bk-card-grid">
        <div>
          <div class="k">Pemesan</div>
          <div class="v"><span class="font-semibold">{{ $b->nama_pemesan }}</span><br><span class="text-[var(--ink-soft)]">{{ $b->eksternal ? 'EKSTERNAL' : 'INTERNAL' }}</span><br><span class="text-[var(--ink-soft)]">{{ $b->email }}</span></div>
        </div>
        <div>
          <div class="k">Jadwal</div>
          <div class="v mono"><span class="nowrap">{{ $b->tanggal->translatedFormat('D, j M Y') }}</span><br><span class="nowrap text-[var(--ink-soft)]">{{ $b->jam }}</span></div>
        </div>
      </div>
      @if ($b->catatan_tambahan)<div class="text-[12px] text-[var(--ink-soft)] mt-1">Catatan: {{ $b->catatan_tambahan }}</div>@endif
            @if ($b->status === 'ditolak' && $b->alasan_penolakan)<div class="text-[12px] mt-1">Alasan ditolak: {{ $b->alasan_penolakan }}</div>@endif
            @if ($b->ada_bentrok ?? false)<div class="text-[12px] font-bold text-red-600 mt-1">Bentrok dengan booking yang sudah disetujui.</div>@endif
      <div class="bk-card-act">
        @if ($b->status === 'pending')
                <button type="button" class="bk-btn bk-btn-line" data-aksi
                  data-nama="{{ $b->nama_kegiatan }}"
                  data-setujui="{{ route('admin.booking.setujui', $b) }}"
                  data-tolak="{{ route('admin.booking.tolak', $b) }}"
                  data-hapus="{{ route('admin.booking.destroy', $b) }}"
                  data-bentrok="{{ ($b->ada_bentrok ?? false) ? 1 : 0 }}"
                  aria-haspopup="menu" aria-expanded="false">
                  Aksi <i class="ph ph-caret-down"></i>
                </button>
              @else
                <button type="button" class="bk-btn bk-btn-danger" data-hapus-langsung
                  data-nama="{{ $b->nama_kegiatan }}" data-hapus="{{ route('admin.booking.destroy', $b) }}">Hapus</button>
              @endif
      </div>
    </div>
    @endforeach
  </div>

  @if ($items->isEmpty())
    <div class="py-10 text-center text-[var(--ink-faint)]">Tidak ada data ditemukan.</div>
  @endif

  <div class="mt-6 flex items-center justify-between text-xs text-[var(--ink-soft)]">
    <div>Hal <span class="text-[var(--ink)]">{{ $items->currentPage() }}</span>/<span class="text-[var(--ink)]">{{ max(1, $items->lastPage()) }}</span></div>
    <div class="flex gap-4">
      @if ($items->onFirstPage())
        <span class="uppercase opacity-30 select-none">&larr; Prev</span>
      @else
        <a data-lock href="{{ $items->previousPageUrl() }}" class="uppercase hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none">&larr; Prev</a>
      @endif
      @if ($items->hasMorePages())
        <a data-lock href="{{ $items->nextPageUrl() }}" class="uppercase hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none">Next &rarr;</a>
      @else
        <span class="uppercase opacity-30 select-none">Next &rarr;</span>
      @endif
    </div>
  </div>
</div>

{{-- Dropdown aksi (dipakai bersama semua baris menunggu) --}}
<div id="aksiMenu" class="bk-menu" role="menu" hidden>
  <form id="aksiSetujui" data-lock method="POST">
    @csrf
    <button type="submit" id="miSetujui" class="bk-mi" role="menuitem">Setujui</button>
  </form>
  <button type="button" id="miTolak" class="bk-mi" role="menuitem">Tolak&hellip;</button>
  <div class="bk-mi-sep"></div>
  <button type="button" id="miHapus" class="bk-mi bk-mi-danger" role="menuitem">Hapus&hellip;</button>
</div>

{{-- Dialog Tolak --}}
<dialog id="dlgTolak" class="bk-dlg">
  <form data-lock method="POST">
    @csrf
    <h2>Tolak pengajuan?</h2>
    <p class="nama" data-nama></p>
    <div>
      <label class="eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)]" for="alasan_penolakan">Alasan penolakan (dikirim ke pemesan)</label>
      <input id="alasan_penolakan" name="alasan_penolakan" required maxlength="500" autocomplete="off">
    </div>
    <div class="acts">
      <button type="button" data-tutup class="bk-b bk-b-line">Batal</button>
      <button type="submit" class="bk-b bk-b-red">Ya, tolak</button>
    </div>
  </form>
</dialog>

{{-- Dialog Hapus --}}
<dialog id="dlgHapus" class="bk-dlg">
  <form data-lock method="POST">
    @csrf @method('DELETE')
    <h2>Hapus pengajuan?</h2>
    <p class="nama" data-nama></p>
    <p class="muted">Tindakan ini tidak bisa dibatalkan. Jika pengajuan sudah disetujui, jadwalnya ikut hilang dari halaman Jadwal Penggunaan.</p>
    <div class="acts">
      <button type="button" data-tutup class="bk-b bk-b-line">Tidak</button>
      <button type="submit" class="bk-b bk-b-red">Ya, hapus</button>
    </div>
  </form>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const menu = document.getElementById('aksiMenu');
  const dlgTolak = document.getElementById('dlgTolak');
  const dlgHapus = document.getElementById('dlgHapus');
  let aktif = null; // tombol pemicu yang sedang membuka menu

  const tutupMenu = () => {
    menu.hidden = true;
    if (aktif) { aktif.setAttribute('aria-expanded', 'false'); aktif = null; }
  };

  const bukaDialog = (dlg, url, nama) => {
    dlg.querySelector('form').action = url;
    dlg.querySelector('[data-nama]').textContent = nama;
    dlg.showModal();
    const f = dlg.querySelector('input[name]:not([type=hidden])');
    if (f) f.focus();
  };

  // Posisi menu: di bawah tombol, rata kanan; kalau tidak muat, di atas.
  const taruhMenu = btn => {
    menu.hidden = false;
    const r = btn.getBoundingClientRect();
    const mw = menu.offsetWidth, mh = menu.offsetHeight;
    let left = Math.min(Math.max(8, r.right - mw), window.innerWidth - mw - 8);
    let top = r.bottom + 4;
    if (top + mh > window.innerHeight - 8) top = Math.max(8, r.top - mh - 4);
    menu.style.left = left + 'px';
    menu.style.top = top + 'px';
  };

  document.querySelectorAll('[data-aksi]').forEach(btn => btn.addEventListener('click', e => {
    e.stopPropagation();
    if (aktif === btn) { tutupMenu(); return; }
    tutupMenu();
    aktif = btn;
    btn.setAttribute('aria-expanded', 'true');
    document.getElementById('aksiSetujui').action = btn.dataset.setujui;
    document.getElementById('miSetujui').disabled = btn.dataset.bentrok === '1';
    document.getElementById('miSetujui').title = btn.dataset.bentrok === '1' ? 'Bentrok dengan booking yang sudah disetujui' : '';
    taruhMenu(btn);
  }));

  document.getElementById('miTolak').addEventListener('click', () => {
    const b = aktif; tutupMenu();
    if (b) bukaDialog(dlgTolak, b.dataset.tolak, b.dataset.nama);
  });
  document.getElementById('miHapus').addEventListener('click', () => {
    const b = aktif; tutupMenu();
    if (b) bukaDialog(dlgHapus, b.dataset.hapus, b.dataset.nama);
  });
  document.querySelectorAll('[data-hapus-langsung]').forEach(b => b.addEventListener('click', () => bukaDialog(dlgHapus, b.dataset.hapus, b.dataset.nama)));

  document.addEventListener('click', e => { if (!menu.hidden && !menu.contains(e.target)) tutupMenu(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') tutupMenu(); });
  window.addEventListener('scroll', tutupMenu, { passive: true });
  window.addEventListener('resize', tutupMenu);

  document.querySelectorAll('dialog').forEach(d => {
    d.querySelector('[data-tutup]').addEventListener('click', () => d.close());
    d.addEventListener('click', e => { if (e.target === d) d.close(); });
  });
});
</script>
@include('partials.lock')
@endsection