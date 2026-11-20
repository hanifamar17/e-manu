@extends('layouts.app')
@section('title', 'Buat Izin Siswa')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-base sm:text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
    $err = 'text-xs text-red-600 mt-1 m-0';
    $btn = 'px-3 py-1.5 text-xs font-semibold border border-[var(--line)] cursor-pointer touch-manipulation select-none transition active:scale-[0.97]';
    $errSiswa = $errors->first('nisn') ?: $errors->first('nisn.*');
@endphp
<div class="max-w-xl lg:max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('perizinan.index') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-6">Buat Izin Siswa</h1>

  @include('partials.flash')

  <form id="formIzin" data-lock method="POST" action="{{ route('perizinan.store') }}" class="grid grid-cols-1 lg:grid-cols-2 gap-x-12 gap-y-6 items-start" autocomplete="off">
    @csrf
    <div class="space-y-6">
    <div id="hidden"></div>

    <div>
      <p class="{{ $lbl }}">Siswa</p>
      <div class="flex gap-2 mb-3">
        <button type="button" data-tab="cari" class="tab {{ $btn }}">Cari siswa</button>
        <button type="button" data-tab="kelas" class="tab {{ $btn }}">Per kelas</button>
      </div>

      <div id="tab-cari" class="relative">
        <input class="{{ $inp }}" id="cari" placeholder="Ketik nama / NISN, klik untuk menambah" enterkeyhint="search">
        <div id="hasil" class="hidden absolute z-20 left-0 right-0 mt-1 border border-[var(--line)] bg-[var(--paper)] max-h-64 overflow-auto"></div>
      </div>

      <div id="tab-kelas" class="hidden">
        <select class="{{ $inp }} cursor-pointer" id="kelas">
          <option value="">— pilih kelas —</option>
          @foreach ($kelasList as $k)
            <option value="{{ $k }}">{{ $k }}</option>
          @endforeach
        </select>
        <div id="kelasBox" class="hidden mt-3 border border-[var(--line)]">
          <label class="flex items-center gap-3 p-3 border-b border-[var(--line)] cursor-pointer select-none">
            <input type="checkbox" id="semua" class="cursor-pointer">
            <span class="text-sm font-bold">Pilih semua kelas ini</span>
            <span id="kelasJumlah" class="mono text-[11px] text-[var(--ink-soft)] ml-auto"></span>
          </label>
          <div id="kelasList" style="max-height:15rem" class="overflow-auto"></div>
        </div>
      </div>

      <p id="errSiswa" class="{{ $err }} {{ $errSiswa ? '' : 'hidden' }}">{{ $errSiswa }}</p>
    </div>

    <div class="border border-[var(--line)]">
      <div class="flex items-center justify-between gap-3 p-3 border-b border-[var(--line)]">
        <p id="jumlah" class="{{ $lbl }} m-0">0 siswa dipilih</p>
        <button type="button" id="kosongkan" class="{{ $btn }} bg-white hover:bg-[var(--ink)] hover:text-[var(--paper)]">Kosongkan</button>
      </div>
      <div id="terpilih" style="max-height:20rem" class="overflow-auto"></div>
    </div>
    </div>

    <div class="space-y-6">

    <div class="grid grid-cols-2 gap-6">
      <div>
        <label class="{{ $lbl }}" for="tanggal_mulai">Tanggal mulai</label>
        <input class="{{ $inp }} cursor-pointer" type="date" id="tanggal_mulai" name="tanggal_mulai" required value="{{ old('tanggal_mulai', now()->toDateString()) }}">
        @error('tanggal_mulai')<p class="{{ $err }}">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="{{ $lbl }}" for="jam_mulai">Jam mulai</label>
        <input class="{{ $inp }} cursor-pointer" type="time" id="jam_mulai" name="jam_mulai" required value="{{ old('jam_mulai') }}">
        @error('jam_mulai')<p class="{{ $err }}">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="{{ $lbl }}" for="tanggal_selesai">Tanggal selesai</label>
        <input class="{{ $inp }} cursor-pointer" type="date" id="tanggal_selesai" name="tanggal_selesai" required value="{{ old('tanggal_selesai', now()->toDateString()) }}">
        @error('tanggal_selesai')<p class="{{ $err }}">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="{{ $lbl }}" for="jam_selesai">Jam selesai</label>
        <input class="{{ $inp }} cursor-pointer" type="time" id="jam_selesai" name="jam_selesai" required value="{{ old('jam_selesai') }}">
        @error('jam_selesai')<p class="{{ $err }}">{{ $message }}</p>@enderror
      </div>
    </div>

    <div>
      <label class="{{ $lbl }}" for="durasi">Durasi</label>
      <select class="{{ $inp }} cursor-pointer" id="durasi" name="durasi" required>
        <option value="">— pilih —</option>
        @foreach ($durasi as $k => $label)
          <option value="{{ $k }}" @selected(old('durasi') === $k)>{{ $label }}</option>
        @endforeach
      </select>
      @error('durasi')<p class="{{ $err }}">{{ $message }}</p>@enderror
    </div>

    <div>
      <label class="{{ $lbl }}" for="alasan">Alasan / kegiatan</label>
      <input class="{{ $inp }}" id="alasan" name="alasan" maxlength="200" required value="{{ old('alasan') }}">
      @error('alasan')<p class="{{ $err }}">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div>
        <label class="{{ $lbl }}" for="petugas">Nama petugas</label>
        <input class="{{ $inp }}" id="petugas" name="petugas" maxlength="100" required value="{{ old('petugas') }}">
        @error('petugas')<p class="{{ $err }}">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="{{ $lbl }}" for="status_petugas">Status petugas</label>
        <select class="{{ $inp }} cursor-pointer" id="status_petugas" name="status_petugas" required>
          @foreach ($statusPetugas as $s)
            <option value="{{ $s }}" @selected(old('status_petugas', $defaultStatusPetugas) === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
          @endforeach
        </select>
        @error('status_petugas')<p class="{{ $err }}">{{ $message }}</p>@enderror
      </div>
    </div>

    <label class="flex items-start gap-3 cursor-pointer select-none">
      <input type="checkbox" name="need_acc" value="1" class="mt-1 cursor-pointer" @checked(old('need_acc'))>
      <span class="text-sm"><strong>Kirim ke wali kelas untuk ACC.</strong>
        <span class="text-[var(--ink-soft)]">Tanpa centang, izin langsung aktif. Dengan centang, izin menunggu dan kamu diminta mengirim tautan ACC ke WhatsApp wali kelas. Tautan ACC dikirim ke wali kelas dari kelas siswa (otomatis). Satu ACC untuk seluruh siswa di izin ini.</span></span>
    </label>


    <button type="submit" class="inline-block px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)]">Simpan izin</button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const $ = id => document.getElementById(id);
  const URL_CARI = @json(route('perizinan.siswa'));
  const URL_KELAS = @json(route('perizinan.kelas'));
  const sel = new Map();               // nisn -> {nisn, nama, kelas, wali}
  @json($terpilih).forEach(s => sel.set(s.nisn, s));
  let kelasRows = [];
  let timer = null, seq = 0;

  const kelasAktif = () => { const f = sel.values().next().value; return f ? f.kelas : null; };
  const pesan = t => { const er = $('errSiswa'); er.textContent = t; er.classList.remove('hidden'); };
  const bolehTambah = s => {
    const k = kelasAktif();
    if (k && s.kelas !== k) { pesan('Satu izin hanya untuk satu kelas (sekarang: ' + k + '). Kosongkan daftar dulu untuk pilih kelas lain, atau buat izin terpisah per kelas.'); return false; }
    return true;
  };
  const el = (tag, cls, text) => { const e = document.createElement(tag); if (cls) e.className = cls; if (text != null) e.textContent = text; return e; };

  // ---- tab
  const ON = ['bg-[var(--ink)]', 'text-[var(--paper)]'], OFF = ['bg-white'];
  const tab = nama => {
    ['cari', 'kelas'].forEach(t => $('tab-' + t).classList.toggle('hidden', t !== nama));
    document.querySelectorAll('.tab').forEach(b => {
      const on = b.dataset.tab === nama;
      b.classList.remove(...ON, ...OFF); b.classList.add(...(on ? ON : OFF));
    });
  };
  document.querySelectorAll('.tab').forEach(b => b.addEventListener('click', () => tab(b.dataset.tab)));
  tab('cari');

  // ---- render
  const render = () => {
    $('hidden').replaceChildren(...[...sel.keys()].map(n => {
      const i = el('input'); i.type = 'hidden'; i.name = 'nisn[]'; i.value = n; return i;
    }));
    $('jumlah').textContent = sel.size + ' siswa dipilih';
    const box = $('terpilih'); box.replaceChildren();
    if (!sel.size) box.append(el('p', 'p-3 text-sm text-[var(--ink-soft)] m-0', 'Belum ada siswa.'));
    sel.forEach(s => {
      const row = el('div', 'flex items-center justify-between gap-3 px-3 py-2 border-b border-[var(--line)]');
      const info = el('div', 'min-w-0');
      info.append(el('p', 'text-sm font-semibold m-0', s.nama), el('p', 'mono text-[11px] text-[var(--ink-soft)] m-0', s.nisn + ' · ' + s.kelas));
      const x = el('button', 'px-2 py-1 text-xs font-semibold border border-[var(--line)] bg-white cursor-pointer touch-manipulation hover:bg-red-600 hover:text-white hover:border-red-600 active:scale-95', 'Hapus');
      x.type = 'button'; x.addEventListener('click', () => { sel.delete(s.nisn); render(); });
      row.append(info, x); box.append(row);
    });
    syncKelas();
    $('errSiswa').classList.add('hidden');
  };
  const tambah = s => { if (!sel.has(s.nisn)) { if (!bolehTambah(s)) return; sel.set(s.nisn, s); } render(); };

  $('kosongkan').addEventListener('click', () => { sel.clear(); render(); });

  // ---- tab cari
  const cari = $('cari'), hasil = $('hasil');
  cari.addEventListener('input', () => {
    clearTimeout(timer);
    const q = cari.value.trim();
    if (q.length < 2) { hasil.classList.add('hidden'); return; }
    timer = setTimeout(async () => {
      const my = ++seq;
      try {
        const r = await fetch(URL_CARI + '?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } });
        if (!r.ok || my !== seq) return;
        const rows = await r.json();
        hasil.replaceChildren();
        if (!rows.length) hasil.append(el('p', 'p-3 text-sm text-[var(--ink-soft)] m-0', 'Tidak ditemukan.'));
        rows.forEach(s => {
          const b = el('button', 'block w-full text-left px-3 py-2.5 border-b border-[var(--line)] cursor-pointer touch-manipulation hover:bg-black/[.04] active:bg-black/[.08]');
          b.type = 'button';
          b.append(el('span', 'block text-sm font-semibold', s.nama + (sel.has(s.nisn) ? ' ✓' : '')), el('span', 'block mono text-[11px] text-[var(--ink-soft)]', s.nisn + ' · ' + s.kelas));
          b.addEventListener('click', () => { tambah(s); cari.value = ''; hasil.classList.add('hidden'); cari.focus(); });
          hasil.append(b);
        });
        hasil.classList.remove('hidden');
      } catch (e) { /* ketik ulang */ }
    }, 250);
  });
  document.addEventListener('click', e => { if (!$('tab-cari').contains(e.target)) hasil.classList.add('hidden'); });

  // ---- tab per kelas
  const syncKelas = () => {
    document.querySelectorAll('#kelasList input[type=checkbox]').forEach(c => { c.checked = sel.has(c.dataset.nisn); });
    $('semua').checked = kelasRows.length > 0 && kelasRows.every(s => sel.has(s.nisn));
    $('kelasJumlah').textContent = kelasRows.filter(s => sel.has(s.nisn)).length + '/' + kelasRows.length;
  };
  $('kelas').addEventListener('change', async () => {
    const k = $('kelas').value;
    kelasRows = []; $('kelasList').replaceChildren();
    if (!k) { $('kelasBox').classList.add('hidden'); return; }
    try {
      const r = await fetch(URL_KELAS + '?kelas=' + encodeURIComponent(k), { headers: { Accept: 'application/json' } });
      if (!r.ok) return;
      kelasRows = await r.json();
    } catch (e) { return; }
    kelasRows.forEach(s => {
      const lb = el('label', 'flex items-center gap-3 px-3 py-2 border-b border-[var(--line)] cursor-pointer select-none');
      const c = el('input', 'cursor-pointer'); c.type = 'checkbox'; c.dataset.nisn = s.nisn;
      c.addEventListener('change', () => {
        if (c.checked) { if (!bolehTambah(s)) { c.checked = false; return; } sel.set(s.nisn, s); } else sel.delete(s.nisn);
        render();
      });
      lb.append(c, el('span', 'text-sm', s.nama), el('span', 'mono text-[11px] text-[var(--ink-soft)] ml-auto', s.nisn));
      $('kelasList').append(lb);
    });
    $('kelasBox').classList.remove('hidden');
    syncKelas();
  });
  $('semua').addEventListener('change', () => {
    if ($('semua').checked) {
      if (kelasRows.length && !bolehTambah(kelasRows[0])) { $('semua').checked = false; return; }
      kelasRows.forEach(s => sel.set(s.nisn, s));
    } else kelasRows.forEach(s => sel.delete(s.nisn));
    render();
  });

  // siswa wajib minimal 1
  $('formIzin').addEventListener('submit', e => {
    if (!sel.size) {
      e.preventDefault(); e.stopImmediatePropagation();
      const er = $('errSiswa'); er.textContent = 'Pilih minimal 1 siswa.'; er.classList.remove('hidden'); cari.focus();
    }
  }, true);

  render();
});
</script>
@include('partials.lock')
@endsection
