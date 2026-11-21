@extends('layouts.app')
@section('title', 'Lab Komp: Peminjaman Barang')

@section('content')
<style>
  #lk-pinjam nav > a {
    padding-left: 2px; padding-right: 2px;
    cursor: pointer; user-select: none; touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
    transition: all .15s ease-out;
  }
  #lk-pinjam nav > a:hover { padding-left: 6px; background: rgba(31,28,25,.03); }
  #lk-pinjam nav > a:active { background: #f3e6dc; transform: scale(.99); }

  #lk-pinjam nav > a > span,
  #lk-pinjam nav > a h3,
  #lk-pinjam nav > a > i { transition: color .15s ease-out, transform .15s ease-out; }
  #lk-pinjam nav > a:hover > span,
  #lk-pinjam nav > a:hover h3 { color: #a8501f; }
  #lk-pinjam nav > a:hover > i { color: #a8501f; transform: translateX(3px); }

  #lk-pinjam .back-lk { transition: all .15s ease-out; -webkit-tap-highlight-color: transparent; }
  #lk-pinjam .back-lk:hover { color: var(--ink); transform: translateX(-2px); }
  #lk-pinjam .back-lk:active { transform: scale(.97); }
  #lk-pinjam { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; overflow-x: clip; }
#lk-pinjam nav > a > span { font-family: 'Source Serif 4', ui-serif, Georgia, serif; }
</style>

<div id="lk-pinjam" class="max-w-[460px] mx-auto px-5 pt-14 pb-20 text-[var(--ink)]">
  <p class="text-[11px] font-semibold tracking-[0.08em] uppercase text-[#a8501f] mb-3">Lab Komputer</p>
  <h1 class="text-[26px] leading-[1.15] font-semibold font-['Source_Serif_4',ui-serif,Georgia,serif] tracking-[-0.01em] mb-3 break-words min-w-0">Pinjam Barang</h1>
  <p class="text-[14px] leading-relaxed max-w-[36ch] mb-10 text-[var(--ink-soft)]">
    Kelola peminjaman dan pengembalian inventaris lab dengan mudah dan cepat.
  </p>

  @include('partials.flash')

  <nav class="border-t border-[var(--line)]">
    @include('labkomp.partials.navrow', ['no' => '01', 'judul' => 'Peminjaman Barang', 'desc' => 'Pinjam peralatan dan fasilitas laboratorium', 'href' => route('peminjaman_invlabkomp.borrow')])
    @include('labkomp.partials.navrow', ['no' => '02', 'judul' => 'Pengembalian Barang', 'desc' => 'Kembalikan peralatan yang telah selesai digunakan', 'href' => route('peminjaman_invlabkomp.return_page')])
    @include('labkomp.partials.navrow', ['no' => '03', 'judul' => 'Riwayat Peminjaman', 'desc' => 'Lihat histori log dan status peminjaman', 'href' => route('peminjaman_invlabkomp.history')])
  </nav>

  <a href="{{ route('repository') }}" class="back-lk inline-flex items-center gap-2 mt-8 text-[13px] font-medium text-[var(--ink-soft)] no-underline cursor-pointer select-none touch-manipulation">
    <i class="ph ph-arrow-left"></i> Kembali ke Dashboard Utama
  </a>
</div>
@endsection