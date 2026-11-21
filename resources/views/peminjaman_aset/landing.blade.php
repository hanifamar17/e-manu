@extends('layouts.app')
@section('title', 'Peminjaman Aset')

@section('content')
<style>
  #aset-page .item { transition: all .15s ease-out; -webkit-tap-highlight-color: transparent; }
  #aset-page .item:hover { padding-left: 6px; background: rgba(31,28,25,.03); }
  #aset-page .item:active { background: #f3e6dc; transform: scale(.99); }
  #aset-page .item .idx,
  #aset-page .item h3,
  #aset-page .item .arrow { transition: color .15s ease-out, transform .15s ease-out; }
  #aset-page .item:hover .idx,
  #aset-page .item:hover h3 { color: #a8501f; }
  #aset-page .item:hover .arrow { color: #a8501f; transform: translateX(3px); }

  #aset-page .back { transition: all .15s ease-out; -webkit-tap-highlight-color: transparent; }
  #aset-page .back:hover { color: #1f1c19; transform: translateX(-2px); }
  #aset-page .back:active { transform: scale(.97); }
  #aset-page { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; overflow-x: clip; }
#aset-page .item .idx { font-family: 'Source Serif 4', ui-serif, Georgia, serif; }
</style>

<div id="aset-page" class="max-w-[460px] mx-auto px-5 pt-14 pb-20 bg-[#faf8f5] text-[#1f1c19]">
  <div class="text-[11px] font-semibold tracking-[0.08em] uppercase text-[#a8501f] mb-3">Layanan SARPRAS</div>
  <h1 class="text-[26px] leading-[1.15] font-semibold font-['Source_Serif_4',ui-serif,Georgia,serif] tracking-[-0.01em] mb-3 break-words min-w-0">Inventaris SARPRAS</h1>
  <p class="text-[14px] leading-relaxed max-w-[36ch] mb-10 text-[#6b6459]">
    Kelola siklus peminjaman aset, pantau daftar inventaris, dan lihat aktivitas peminjaman.
  </p>

  <nav class="border-t border-[#e6e0d6]">
    @php
      $menu = [
        ['01', 'Peminjaman Aset', 'Ajukan permohonan aset baru', route('peminjaman.form')],
        ['02', 'Pengembalian Aset', 'Kembalikan aset yang dipinjam', route('peminjaman.return')],
        ['03', 'Daftar Peminjaman', 'Pantau status barang dipinjam', route('peminjaman.riwayat')],
      ];
    @endphp
    @foreach ($menu as [$no, $judul, $sub, $url])
      <a href="{{ $url }}" class="item flex items-center gap-4 py-5 px-0.5 border-b border-[#e6e0d6] no-underline cursor-pointer select-none touch-manipulation">
        <span class="idx tabular-nums text-[15px] w-6 shrink-0 text-[#a49b8c]">{{ $no }}</span>
        <div class="flex-1 min-w-0">
          <h3 class="text-[15px] font-semibold m-0">{{ $judul }}</h3>
          <p class="text-[13px] mt-0.5 m-0 text-[#a49b8c]">{{ $sub }}</p>
        </div>
        <span class="arrow shrink-0 text-[#a49b8c]">&rarr;</span>
      </a>
    @endforeach
  </nav>

  <a href="/repository" class="back inline-flex items-center gap-2 mt-8 text-[13px] font-medium text-[#6b6459] no-underline cursor-pointer select-none touch-manipulation">
    &larr; Kembali ke Dashboard Utama
  </a>
</div>
@endsection