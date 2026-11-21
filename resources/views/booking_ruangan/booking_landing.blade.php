@extends('layouts.app')
@section('title', 'Peminjaman Ruangan')

@section('content')
<style>
  #ruangan-page .item { transition: all .15s ease-out; -webkit-tap-highlight-color: transparent; }
  #ruangan-page .item:hover { padding-left: 6px; background: rgba(31,28,25,.03); }
  #ruangan-page .item:active { background: #f3e6dc; transform: scale(.99); }
  #ruangan-page .item .idx,
  #ruangan-page .item h3,
  #ruangan-page .item .arrow { transition: color .15s ease-out, transform .15s ease-out; }
  #ruangan-page .item .idx,
  #ruangan-page .item .arrow { color: #a49b8c; }
  #ruangan-page .item.on .idx,
  #ruangan-page .item.on .arrow { color: #a8501f; }
  #ruangan-page .item:hover .idx,
  #ruangan-page .item:hover h3,
  #ruangan-page .item:hover .arrow { color: #a8501f; }
  #ruangan-page .item:hover .arrow { transform: translateX(3px); }

  #ruangan-page .back { transition: all .15s ease-out; -webkit-tap-highlight-color: transparent; }
  #ruangan-page .back:hover { color: #1f1c19; transform: translateX(-2px); }
  #ruangan-page .back:active { transform: scale(.97); }
</style>

@php
  $menu = [
    ['01', 'Prosedur Peminjaman', 'Alur & regulasi izin penggunaan ruangan', route('booking_ruangan.booking_prosedur'), false],
    ['02', 'Jadwal Penggunaan', 'Cek ketersediaan tanggal dan waktu lab', route('booking_ruangan.jadwal_penggunaan'), false],
    ['03', 'Form Peminjaman', 'Isi formulir permohonan booking lab', route('booking_ruangan.booking_form_page'), true],
  ];
@endphp

<div id="ruangan-page" class="max-w-[460px] mx-auto px-5 pt-14 pb-20 bg-[#faf8f5] text-[#1f1c19] font-['Inter',ui-sans-serif,system-ui,sans-serif] overflow-x-clip">

  <div class="text-[11px] font-semibold tracking-[0.08em] uppercase text-[#a8501f] mb-3">
    Layanan Ruangan
  </div>

  <h1 class="text-[26px] leading-[1.15] font-semibold font-['Source_Serif_4',ui-serif,Georgia,serif] tracking-[-0.01em] mb-3 break-words min-w-0">
    Peminjaman Ruangan
  </h1>

  <p class="text-[14px] leading-relaxed max-w-[36ch] mb-10 text-[#6b6459]">
    Cek ketersediaan, pelajari prosedur administrasi, dan ajukan izin penggunaan laboratorium komputer.
  </p>

  @include('partials.flash')

  <nav class="border-t border-[#e6e0d6]">
    @foreach ($menu as [$no, $judul, $desc, $url, $on])
      <a href="{{ $url }}"
        class="item {{ $on ? 'on' : '' }} flex items-center gap-4 py-5 px-0.5 border-b border-[#e6e0d6] no-underline cursor-pointer select-none touch-manipulation">
        <span class="idx font-['Source_Serif_4',ui-serif,Georgia,serif] tabular-nums text-[15px] w-6 shrink-0">{{ $no }}</span>
        <div class="flex-1 min-w-0">
          <h3 class="text-[15px] font-semibold m-0 text-[#1f1c19]">{{ $judul }}</h3>
          <p class="text-[13px] mt-0.5 m-0 text-[#a49b8c]">{{ $desc }}</p>
        </div>
        <svg class="arrow w-4 h-4 shrink-0" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3.333 8h9.334"/><path d="M8 3.333 12.667 8 8 12.667"/>
        </svg>
      </a>
    @endforeach
  </nav>

  @if (auth()->check() && auth()->user()->role === 'admin')
  <a href="{{ route('admin.booking.index') }}" class="block mt-6 text-[13px] font-semibold text-[#a8501f] no-underline">Admin: tinjau pengajuan &rarr;</a>
  @endif

  <a href="{{ route('repository') }}"
    class="back inline-flex items-center gap-2 mt-8 text-[13px] font-medium text-[#6b6459] no-underline cursor-pointer select-none touch-manipulation">
    <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
      <path d="M12.667 8H3.333"/><path d="M8 12.667 3.333 8 8 3.333"/>
    </svg>
    Kembali ke Dashboard Utama
  </a>

</div>
@endsection