@extends('layouts.app')
@section('title', 'Perizinan Siswa')

@section('content')
<style>
  #perizinan-page .item { transition: all .15s ease-out; -webkit-tap-highlight-color: transparent; }
  #perizinan-page .item:hover { padding-left: 6px; background: rgba(31,28,25,.03); }
  #perizinan-page .item:active { background: #f3e6dc; transform: scale(.99); }
  #perizinan-page .item .idx,
  #perizinan-page .item h3,
  #perizinan-page .item .arrow { transition: color .15s ease-out, transform .15s ease-out; }
  #perizinan-page .item:hover .idx,
  #perizinan-page .item:hover h3 { color: #a8501f; }
  #perizinan-page .item:hover .arrow { color: #a8501f; transform: translateX(3px); }
  #perizinan-page { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; overflow-x: clip; }
#perizinan-page .item .idx { font-family: 'Source Serif 4', ui-serif, Georgia, serif; }
#perizinan-page .item .idx,
#perizinan-page .item .arrow { color: #a49b8c; }
</style>

@php
    $wali = auth()->user()->hasRole('wali_kelas', 'admin');
    $menu = [
        ['route' => 'perizinan.form', 'judul' => 'Buat Izin', 'desc' => 'Formulir izin siswa baru'],
        ['route' => 'perizinan.riwayat', 'judul' => 'Riwayat', 'desc' => 'Cari & lihat semua izin'],
    ];
    if ($wali) {
        array_splice($menu, 1, 0, [['route' => 'perizinan.acc', 'judul' => 'ACC Izin', 'desc' => $menunggu.' izin menunggu persetujuan']]);
    }
    $tv = config('emanu.layar_token');
    if ($tv) {
        $menu[] = ['url' => route('perizinan.layar', $tv), 'blank' => true, 'judul' => 'Layar TV', 'desc' => 'Daftar izin hari ini, tampil otomatis (tab baru)'];
    }
@endphp
<div id="perizinan-page" class="max-w-[460px] mx-auto px-5 pt-14 pb-20 bg-[#faf8f5] text-[#1f1c19] font-['Inter',ui-sans-serif,system-ui,sans-serif] overflow-x-clip">

  <div class="text-[11px] font-semibold tracking-[0.08em] uppercase text-[#a8501f] mb-3">
    Layanan Siswa
  </div>

  <h1 class="text-[26px] leading-[1.15] font-semibold font-['Source_Serif_4',ui-serif,Georgia,serif] tracking-[-0.01em] mb-3 break-words min-w-0">
    Perizinan Siswa
  </h1>

  <p class="text-[14px] leading-relaxed max-w-[36ch] mb-8 text-[#6b6459]">
    Buat izin siswa, proses persetujuan wali kelas, dan cari riwayat perizinan.
  </p>

  @include('partials.flash')

  <div class="grid grid-cols-2 border-t border-b border-[#e6e0d6] mb-8">
    <div class="py-4 pr-4">
      <p class="text-[11px] font-semibold tracking-[0.08em] uppercase text-[#a49b8c] m-0">Siswa izin hari ini</p>
      <p class="text-[26px] leading-[1.15] font-semibold font-['Source_Serif_4',ui-serif,Georgia,serif] tabular-nums m-0 mt-1">{{ $hariIni }}</p>
    </div>
    <div class="py-4 pl-4 border-l border-[#e6e0d6]">
      <p class="text-[11px] font-semibold tracking-[0.08em] uppercase text-[#a49b8c] m-0">Menunggu ACC</p>
      <p class="text-[26px] leading-[1.15] font-semibold font-['Source_Serif_4',ui-serif,Georgia,serif] tabular-nums m-0 mt-1 {{ $menunggu > 0 ? 'text-[#a8501f]' : '' }}">{{ $menunggu }}</p>
    </div>
  </div>

  <nav class="border-t border-[#e6e0d6]">
  @foreach ($menu as $i => $m)
    <a href="{{ $m['url'] ?? route($m['route']) }}"{!! empty($m['blank']) ? '' : ' target="_blank" rel="noopener"' !!}
      class="item flex items-center gap-4 py-5 px-0.5 border-b border-[#e6e0d6] no-underline cursor-pointer select-none touch-manipulation">
      <span class="idx tabular-nums text-[15px] w-6 shrink-0">
  {{ sprintf('%02d', $i + 1) }}
</span>
      <div class="flex-1 min-w-0">
        <h3 class="text-[15px] font-semibold m-0 text-[#1f1c19]">{{ $m['judul'] }}</h3>
        <p class="text-[13px] mt-0.5 m-0 text-[#a49b8c]">{{ $m['desc'] }}</p>
      </div>
      <svg class="arrow w-4 h-4 shrink-0" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3.333 8h9.334"/><path d="M8 3.333 12.667 8 8 12.667"/>
      </svg>
    </a>
  @endforeach
</nav>

  <a href="{{ route('repository') }}"
    class="inline-flex items-center gap-2 mt-8 text-[13px] font-medium text-[#6b6459] no-underline transition-all duration-150 touch-manipulation cursor-pointer select-none hover:text-[#1f1c19] hover:-translate-x-[2px] active:scale-[0.97]">
    <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12.667 8H3.333"/><path d="M8 12.667 3.333 8 8 3.333"/>
    </svg>
    Kembali ke Dashboard Utama
  </a>

</div>
@endsection
