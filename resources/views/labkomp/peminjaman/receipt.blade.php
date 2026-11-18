@extends('layouts.app')
@section('title', 'Bukti Peminjaman '.$loan->kode)

@section('content')
@php $aktif = $loan->status !== 'dikembalikan'; @endphp
<div class="min-h-[70vh] flex items-center justify-center px-4 py-10 text-[var(--ink)]">
  <div class="max-w-[400px] w-full border border-[var(--line)] bg-[var(--paper)]">
    <div class="p-6 text-center border-b border-[var(--line)]">
      @include('partials.flash')
      <p class="eyebrow mono text-[10px] font-bold tracking-widest text-[var(--ink-faint)] uppercase mb-2">Bukti Peminjaman Barang</p>
      <h1 class="text-lg font-bold mb-0.5">Laboratorium Komputer</h1>
      <p class="text-[11px] text-[var(--ink-soft)] m-0">Madrasah Aliyah Nurul Ummah</p>
      <span class="inline-block mt-4 px-2 py-0.5 mono text-[10px] font-bold uppercase border {{ $aktif ? 'bg-amber-50 text-amber-700 border-amber-200' : 'border-[var(--line)] text-[var(--ink-soft)]' }}">
        {{ $aktif ? 'Aktif / Dipinjam' : 'Selesai / Dikembalikan' }}
      </span>
    </div>

    <div class="p-6 space-y-6">
      <div class="text-center border-b border-[var(--line)] pb-6">
        <span class="block mono text-[10px] font-bold text-[var(--ink-faint)] uppercase tracking-widest mb-1">Kode Ref</span>
        <span class="mono text-2xl">{{ $loan->kode }}</span>
      </div>

      <div class="grid grid-cols-2 gap-4 text-[13px]">
        <div>
          <span class="block mono text-[10px] font-bold text-[var(--ink-faint)] uppercase mb-1">Peminjam</span>
          <p class="font-semibold m-0">{{ $loan->nama_peminjam }}</p>
        </div>
        <div class="text-right">
          <span class="block mono text-[10px] font-bold text-[var(--ink-faint)] uppercase mb-1">Tgl Pinjam</span>
          <p class="font-semibold m-0">{{ $loan->tgl_pinjam->format('d-m-Y') }}</p>
        </div>
        <div class="col-span-2">
          <span class="block mono text-[10px] font-bold text-[var(--ink-faint)] uppercase mb-1">Batas Kembali</span>
          <p class="font-semibold m-0">{{ $loan->tgl_kembali->format('d-m-Y') }}</p>
        </div>
      </div>

      @if ($loan->catatan)
        <div class="p-3 border-l-2 border-[var(--ink)] bg-[var(--line)]/30">
          <span class="block mono text-[10px] font-bold text-[var(--ink-faint)] uppercase mb-1">Catatan</span>
          <p class="text-[12px] text-[var(--ink-soft)] italic m-0">&ldquo;{{ $loan->catatan }}&rdquo;</p>
        </div>
      @endif

      <div class="border-t border-[var(--line)] pt-4">
        <span class="block mono text-[10px] font-bold text-[var(--ink-faint)] uppercase mb-3">Item Dipinjam</span>
        <div class="space-y-1">
          @foreach ($loan->items as $i)
            <div class="flex justify-between items-center text-[13px]">
              <span class="font-medium">{{ $i->nama_barang }}</span>
              <span class="mono text-[var(--ink-soft)]">&times;{{ $i->qty }}</span>
            </div>
          @endforeach
        </div>
      </div>

      <div class="text-center border-t border-[var(--line)] pt-4">
        <p class="text-[9px] text-[var(--ink-faint)] m-0">Simpan kode ref untuk pengembalian &bull; <span id="ts"></span></p>
      </div>
    </div>
  </div>
</div>
<script>document.getElementById('ts').textContent = new Date().toLocaleString('id-ID');</script>
@endsection
