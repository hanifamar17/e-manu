@extends('layouts.app')
@section('title', 'Lab Komp: Inventaris')

@section('content')
<style>
  #lk-inv nav > a {
    padding-left: 2px; padding-right: 2px;
    cursor: pointer; user-select: none; touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
    transition: all .15s ease-out;
  }
  #lk-inv nav > a:hover { padding-left: 6px; background: rgba(31,28,25,.03); }
  #lk-inv nav > a:active { background: #f3e6dc; transform: scale(.99); }

  #lk-inv nav > a > span,
  #lk-inv nav > a h3,
  #lk-inv nav > a > i { transition: color .15s ease-out, transform .15s ease-out; }
  #lk-inv nav > a:hover > span,
  #lk-inv nav > a:hover h3 { color: #a8501f; }
  #lk-inv nav > a:hover > i { color: #a8501f; transform: translateX(3px); }

  #lk-inv .back-lk { transition: all .15s ease-out; -webkit-tap-highlight-color: transparent; }
  #lk-inv .back-lk:hover { color: var(--ink); transform: translateX(-2px); }
  #lk-inv .back-lk:active { transform: scale(.97); }
  #lk-inv { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; overflow-x: clip; }
#lk-inv nav > a > span { font-family: 'Source Serif 4', ui-serif, Georgia, serif; }
</style>

<div id="lk-inv" class="max-w-[460px] mx-auto px-5 pt-14 pb-20 text-[var(--ink)]">
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

  <a href="{{ route('repository') }}" class="back-lk inline-flex items-center gap-2 mt-8 text-[13px] font-medium text-[var(--ink-soft)] no-underline cursor-pointer select-none touch-manipulation">
    <i class="ph ph-arrow-left"></i> Kembali ke Dashboard Utama
  </a>
</div>
@endsection