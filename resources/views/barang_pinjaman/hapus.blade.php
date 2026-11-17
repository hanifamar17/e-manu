@extends('layouts.app')
@section('title', 'Hapus Barang Pinjaman')

@section('content')
@php
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
    $dipinjam = $item->jumlah_dipinjam > 0;
@endphp
<div class="max-w-3xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('barang_pinjaman.index') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>

  <div class="mt-8 mb-8">
    <h1 class="page-title text-2xl font-bold m-0">Hapus Barang Pinjaman</h1>
    <p class="text-sm text-[var(--ink-soft)] mt-1 mb-0">Konfirmasi terakhir sebelum data dihapus permanen.</p>
  </div>

  @error('barang_pinjaman')
    <p class="text-sm text-red-600 border-l-2 border-red-600 pl-4 py-2 mb-6">{{ $message }}</p>
  @enderror

  @if ($dipinjam)
    <div class="border-l-2 border-[var(--accent)] bg-[var(--accent-soft)] px-4 py-3 mb-6">
      <p class="eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--accent)] m-0">Tidak bisa dihapus</p>
      <p class="text-sm text-[var(--ink-soft)] mt-1 mb-0">
        Barang masih dipinjam <strong>{{ $item->jumlah_dipinjam }} unit</strong>.
        Selesaikan pengembalian dulu, baru hapus.
      </p>
    </div>
  @endif

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 border-t border-[var(--line)] pt-6 mb-8">
    <div>
      <span class="{{ $lbl }}">Kode</span>
      <p class="mono text-sm font-semibold m-0">{{ $item->kode }}</p>
    </div>
    <div>
      <span class="{{ $lbl }}">Nama Barang</span>
      <p class="text-sm font-semibold m-0">{{ $item->nama_barang }}</p>
    </div>
    <div>
      <span class="{{ $lbl }}">Merk</span>
      <p class="text-sm m-0">{{ $item->merk ?: '-' }}</p>
    </div>
    <div>
      <span class="{{ $lbl }}">Kondisi</span>
      <p class="text-sm m-0">{{ $item->kondisi }}</p>
    </div>
    <div>
      <span class="{{ $lbl }}">Jumlah Total</span>
      <p class="text-sm font-semibold m-0">{{ $item->jumlah_total }} unit</p>
    </div>
    <div>
      <span class="{{ $lbl }}">Sedang Dipinjam</span>
      <p class="text-sm font-semibold m-0">{{ $item->jumlah_dipinjam }} unit</p>
    </div>
    <div>
      <span class="{{ $lbl }}">Sub Bidang</span>
      <p class="text-sm m-0">{{ $item->sub_bidang ?: '-' }}</p>
    </div>
    <div>
      <span class="{{ $lbl }}">Lokasi</span>
      <p class="text-sm m-0">{{ $item->lokasi ?: '-' }}</p>
    </div>
    <div>
      <span class="{{ $lbl }}">Tahun Pengadaan</span>
      <p class="text-sm m-0">{{ $item->tahun_pengadaan ?: '-' }}</p>
    </div>
    <div>
      <span class="{{ $lbl }}">Sumber Dana</span>
      <p class="text-sm m-0">{{ $item->sumber_dana ?: '-' }}</p>
    </div>
  </div>

  <div class="border-l-2 border-[var(--ink)] pl-4 py-1 mb-6">
    <p class="font-semibold text-[15px] m-0">Apakah Anda yakin?</p>
    <p class="text-sm text-[var(--ink-soft)] mt-1 mb-0">Data hilang permanen dan tidak bisa dibatalkan.</p>
  </div>

  <div class="flex flex-wrap items-center gap-5">
    <form method="POST" action="{{ route('barang_pinjaman.destroy', $item) }}">
      @csrf @method('DELETE')
      <button type="submit" @disabled($dipinjam)
        class="px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] disabled:opacity-30 disabled:cursor-not-allowed">
        Ya, Hapus Sekarang
      </button>
    </form>
    <a href="{{ route('barang_pinjaman.index') }}" class="text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">Batalkan</a>
  </div>
</div>
@endsection