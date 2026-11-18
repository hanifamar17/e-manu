@extends('layouts.app')
@section('title', $item ? 'Lab Komp: Edit Inventaris' : 'Lab Komp: Tambah Inventaris')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
    $satuanSekarang = old('satuan', $item->satuan ?? '');
    $daftarSatuan = \App\Models\LabkompItem::SATUAN;
    if ($satuanSekarang !== '' && ! in_array($satuanSekarang, $daftarSatuan, true)) {
        $daftarSatuan[] = $satuanSekarang; // data lama dengan satuan di luar daftar tetap bisa tampil
    }
@endphp
<div class="max-w-xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('inventory_labkomp.inventory_list') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-2">{{ $item ? 'Edit Inventaris: '.$item->kode : 'Tambah Inventaris' }}</h1>
  <p class="text-sm text-[var(--ink-soft)] mt-0 mb-6">{{ $item ? 'Perbarui detail informasi barang.' : 'Lengkapi formulir. Kode barang dibuat otomatis.' }}</p>

  @include('partials.flash')
  @if ($errors->any())
    <ul class="mb-6 text-sm text-red-700 list-disc pl-5">
      @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  @endif

  <form data-lock method="POST" action="{{ $item ? route('inventory_labkomp.inventory_update', $item) : route('inventory_labkomp.inventory_store') }}" class="space-y-6">
    @csrf
    @if ($item) @method('PUT') @endif

    <div>
      <label class="{{ $lbl }}" for="nama_barang">Nama Barang</label>
      <input class="{{ $inp }}" type="text" id="nama_barang" name="nama_barang" required maxlength="255"
        value="{{ old('nama_barang', $item->nama_barang ?? '') }}" placeholder="Contoh: Monitor LED 24 inch">
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="{{ $lbl }}" for="stok">Stok tersedia</label>
        <input class="{{ $inp }}" type="number" id="stok" name="stok" required min="0" value="{{ old('stok', $item->stok ?? '') }}" placeholder="0">
      </div>
      <div>
        <label class="{{ $lbl }}" for="satuan">Satuan</label>
        <select class="{{ $inp }}" id="satuan" name="satuan" required>
          <option value="" disabled @selected($satuanSekarang === '')>Pilih satuan</option>
          @foreach ($daftarSatuan as $s)
            <option value="{{ $s }}" @selected($satuanSekarang === $s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="{{ $lbl }}" for="kondisi">Kondisi</label>
        <select class="{{ $inp }}" id="kondisi" name="kondisi" required>
          @foreach (\App\Models\LabkompItem::KONDISI as $k)
            <option value="{{ $k }}" @selected(old('kondisi', $item->kondisi ?? 'Baik') === $k)>{{ $k }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="{{ $lbl }}" for="lokasi">Lokasi</label>
        <input class="{{ $inp }}" type="text" id="lokasi" name="lokasi" maxlength="255" value="{{ old('lokasi', $item->lokasi ?? '') }}" placeholder="Contoh: Rak A1">
      </div>
    </div>

    <div>
      <label class="{{ $lbl }}" for="catatan">Catatan</label>
      <textarea class="{{ $inp }} resize-none" id="catatan" name="catatan" rows="2" maxlength="1000" placeholder="Keterangan tambahan...">{{ old('catatan', $item->catatan ?? '') }}</textarea>
    </div>

    <div class="flex items-center justify-end gap-6 pt-2">
      <a data-lock href="{{ route('inventory_labkomp.inventory_list') }}"
         class="inline-block py-2 px-1 mono text-[12px] font-bold text-[var(--ink-faint)] uppercase cursor-pointer touch-manipulation select-none transition hover:text-[var(--ink)] active:scale-95">
        Batal
      </a>
      <button type="submit"
        class="inline-block px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)]">
        {{ $item ? 'Perbarui Data' : 'Simpan Data' }}
      </button>
    </div>
  </form>
</div>

<script>
// Tombol/tautan data-lock: sekali klik -> terkunci (cegah klik ganda), dibuka lagi saat halaman ditampilkan ulang (tombol Back).
document.addEventListener('DOMContentLoaded', () => {
  const LOCK = ['pointer-events-none', 'opacity-60'];
  const lock = el => { el.setAttribute('aria-busy', 'true'); el.classList.add(...LOCK); };
  const unlockAll = () => document.querySelectorAll('[data-lock] [aria-busy], a[data-lock][aria-busy], button[aria-busy]').forEach(el => {
    el.removeAttribute('aria-busy'); el.classList.remove(...LOCK);
  });

  document.querySelectorAll('a[data-lock]').forEach(a => a.addEventListener('click', e => {
    if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank') return;
    if (a.hasAttribute('aria-busy')) { e.preventDefault(); return; }
    lock(a);
  }));

  document.querySelectorAll('form[data-lock]').forEach(f => f.addEventListener('submit', e => {
    const b = f.querySelector('button[type="submit"], button:not([type])');
    if (!b || b.disabled || b.hasAttribute('aria-busy')) { e.preventDefault(); return; }
    lock(b);
  }));

  window.addEventListener('pageshow', unlockAll);
});
</script>
@endsection