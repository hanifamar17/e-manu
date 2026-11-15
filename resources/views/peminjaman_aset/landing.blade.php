@extends('layouts.app')
@section('title', 'Peminjaman Aset')

@section('content')
<div class="max-w-[460px] mx-auto px-5 pt-14 pb-20 bg-[#faf8f5] text-[#1f1c19]">
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
      <a href="{{ $url }}" class="group flex items-center gap-4 py-5 border-b border-[#e6e0d6] hover:pl-[6px] transition-all">
        <span class="tabular-nums text-[15px] w-6 text-[#a49b8c] group-hover:text-[#a8501f]">{{ $no }}</span>
        <div class="flex-1 min-w-0">
          <h3 class="text-[15px] font-semibold m-0 group-hover:text-[#a8501f]">{{ $judul }}</h3>
          <p class="text-[13px] mt-0.5 m-0 text-[#a49b8c]">{{ $sub }}</p>
        </div>
        <span class="text-[#a49b8c] group-hover:text-[#a8501f]">&rarr;</span>
      </a>
    @endforeach
  </nav>

  <a href="/repository" class="inline-flex items-center gap-2 mt-8 text-[13px] font-medium text-[#6b6459] hover:text-[#1f1c19]">
    &larr; Kembali ke Dashboard Utama
  </a>
</div>
@endsection
