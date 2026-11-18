@extends('layouts.app')
@section('title', 'Buat Izin Siswa')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-base sm:text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
    $err = 'text-xs text-red-600 mt-1 m-0';
@endphp
<div class="max-w-xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('perizinan.index') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-6">Buat Izin Siswa</h1>

  @include('partials.flash')

  <form id="formIzin" data-lock method="POST" action="{{ route('perizinan.store') }}" class="space-y-6" autocomplete="off">
    @csrf

    <div>
      <label class="{{ $lbl }}" for="cari">Siswa (cari nama / NISN)</label>
      <input type="hidden" name="nisn" id="nisn" value="{{ old('nisn') }}">
      <div id="chip" class="{{ $pilih ? '' : 'hidden' }} flex items-center justify-between gap-3 border border-[var(--line)] p-3">
        <div class="min-w-0 text-sm">
          <p class="font-bold m-0" id="chipNama">{{ $pilih?->nama_siswa }}</p>
          <p class="mono text-[11px] text-[var(--ink-soft)] m-0 mt-0.5" id="chipInfo">{{ $pilih ? $pilih->nisn.' · '.$pilih->kelas_umum : '' }}</p>
        </div>
        <button type="button" id="ganti" class="px-3 py-1.5 text-xs font-semibold bg-white border border-[var(--line)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] active:scale-[0.97]">Ganti</button>
      </div>
      <div id="cariBox" class="{{ $pilih ? 'hidden' : '' }} relative">
        <input class="{{ $inp }}" id="cari" placeholder="mis. Ahmad / 8721" enterkeyhint="search">
        <div id="hasil" class="hidden absolute z-20 left-0 right-0 mt-1 border border-[var(--line)] bg-[var(--paper)] max-h-64 overflow-auto"></div>
      </div>
      <p id="errSiswa" class="{{ $err }} {{ $errors->has('nisn') ? '' : 'hidden' }}">{{ $errors->first('nisn') }}</p>
    </div>

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
      <label class="{{ $lbl }}" for="alasan">Alasan</label>
      <input class="{{ $inp }}" id="alasan" name="alasan" maxlength="200" required value="{{ old('alasan') }}">
      @error('alasan')<p class="{{ $err }}">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div>
        <label class="{{ $lbl }}" for="petugas">Nama petugas (yang menginput)</label>
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
        <span class="text-[var(--ink-soft)]">Tanpa centang, izin langsung aktif. Dengan centang, izin menunggu dan kamu diminta mengirim tautan ACC ke WhatsApp wali kelas.</span></span>
    </label>

    <button type="submit" class="inline-block px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)]">Simpan izin</button>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const $ = id => document.getElementById(id);
  const cari = $('cari'), hasil = $('hasil'), nisn = $('nisn'), chip = $('chip'), box = $('cariBox'), errEl = $('errSiswa');
  const URL_API = @json(route('perizinan.siswa'));
  let timer = null, seq = 0;

  const pilih = s => {
    nisn.value = s.nisn;
    $('chipNama').textContent = s.nama;
    $('chipInfo').textContent = s.nisn + ' · ' + s.kelas + (s.wali ? ' · wali: ' + s.wali : '');
    chip.classList.remove('hidden'); box.classList.add('hidden'); hasil.classList.add('hidden');
    errEl.classList.add('hidden');
  };

  $('ganti').addEventListener('click', () => {
    nisn.value = ''; chip.classList.add('hidden'); box.classList.remove('hidden'); cari.value = ''; cari.focus();
  });

  cari.addEventListener('input', () => {
    clearTimeout(timer);
    const q = cari.value.trim();
    if (q.length < 2) { hasil.classList.add('hidden'); return; }
    timer = setTimeout(async () => {
      const my = ++seq;
      try {
        const r = await fetch(URL_API + '?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } });
        if (!r.ok || my !== seq) return;
        const rows = await r.json();
        hasil.replaceChildren();
        if (!rows.length) {
          const p = document.createElement('p');
          p.className = 'p-3 text-sm text-[var(--ink-soft)] m-0';
          p.textContent = 'Tidak ditemukan.';
          hasil.append(p);
        }
        rows.forEach(s => {
          const b = document.createElement('button');
          b.type = 'button';
          b.className = 'block w-full text-left px-3 py-2.5 border-b border-[var(--line)] cursor-pointer touch-manipulation hover:bg-black/[.04] active:bg-black/[.08]';
          const n = document.createElement('span'); n.className = 'block text-sm font-semibold'; n.textContent = s.nama;
          const i = document.createElement('span'); i.className = 'block mono text-[11px] text-[var(--ink-soft)]'; i.textContent = s.nisn + ' · ' + s.kelas;
          b.append(n, i);
          b.addEventListener('click', () => pilih(s));
          hasil.append(b);
        });
        hasil.classList.remove('hidden');
      } catch (e) { /* abaikan: ketik ulang */ }
    }, 250);
  });

  document.addEventListener('click', e => { if (!box.contains(e.target)) hasil.classList.add('hidden'); });

  // Siswa wajib dipilih dari hasil pencarian (nisn terisi).
  $('formIzin').addEventListener('submit', e => {
    if (!nisn.value) {
      e.preventDefault(); e.stopImmediatePropagation();
      errEl.textContent = 'Pilih siswa dari hasil pencarian.'; errEl.classList.remove('hidden'); cari.focus();
    }
  }, true);
});
</script>
@include('partials.lock')
@endsection
