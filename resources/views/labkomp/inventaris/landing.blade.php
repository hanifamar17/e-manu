@extends('layouts.app')
@section('title', 'Lab Komp: Inventaris')

@section('content')
<div class="max-w-[460px] mx-auto px-5 pt-14 pb-20 text-[var(--ink)]">
  <p class="text-[11px] font-semibold tracking-[0.08em] uppercase text-[#a8501f] mb-3">Lab Komputer</p>
  <h1 class="text-[26px] leading-[1.15] font-semibold font-['Source_Serif_4',ui-serif,Georgia,serif] tracking-[-0.01em] mb-3 break-words min-w-0">Inventaris Barang</h1>
  <p class="text-[14px] leading-relaxed max-w-[36ch] mb-10 text-[var(--ink-soft)]">
    Kelola ketersediaan, kondisi, dan lokasi barang laboratorium secara terpusat.
  </p>

  @include('partials.flash')

  <nav class="border-t border-[var(--line)]">
    @include('labkomp.partials.navrow', ['no' => '01', 'judul' => 'Daftar Inventaris', 'desc' => 'Lihat, cari, dan periksa semua data barang', 'href' => route('inventory_labkomp.inventory_list')])
    @include('labkomp.partials.navrow', ['no' => '02', 'judul' => 'Tambah Inventaris', 'desc' => 'Input barang baru atau peralatan laboratorium', 'href' => route('inventory_labkomp.inventory_add')])
  </nav>

  <a href="{{ route('repository') }}" class="inline-flex items-center gap-2 mt-8 text-[13px] font-medium text-[var(--ink-soft)] hover:text-[var(--ink)] no-underline">
    <i class="ph ph-arrow-left"></i> Kembali ke Dashboard Utama
  </a>
</div>
@endsection
