@extends('layouts.app')
@section('title', 'Riwayat Peminjaman Aset')

@section('content')
<div class="max-w-5xl mx-auto px-5 pt-12 pb-20 bg-[#faf8f5] text-[#1f1c19]">
  <a href="{{ route('peminjaman.landing') }}" class="text-sm font-semibold text-[#6b6459] hover:text-[#1f1c19]">&larr; Kembali</a>
  <h1 class="text-2xl font-bold tracking-tight mt-8">Riwayat Peminjaman Aset</h1>
  <p class="text-sm text-[#6b6459] mt-2 mb-10">Daftar log aktivitas dan dokumen peminjaman aset.</p>

  <form method="GET" class="w-full max-w-sm mb-8">
    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama atau nomor surat..."
           class="block py-2.5 w-full text-sm bg-transparent border-0 border-b border-[#e6e0d6] focus:outline-none focus:border-[#a8501f]">
    @if ($q !== '')<p class="text-xs text-zinc-500 mt-2">Ditemukan {{ $rows->total() }} data</p>@endif
  </form>

  @if ($rows->isEmpty())
    <div class="py-10 text-center text-[13px] font-semibold text-[#a49b8c]">Tidak ada data ditemukan</div>
  @else
  <div class="overflow-x-auto border-t border-[#e6e0d6]">
    <table class="w-full text-left text-sm whitespace-nowrap">
      <thead>
        <tr class="border-b border-[#e6e0d6] text-[11px] font-semibold text-[#a49b8c] uppercase tracking-wider">
          <th class="py-2 pr-6">Peminjam & Surat</th>
          <th class="py-2 pr-6 hidden sm:table-cell">Periode Pinjam</th>
          <th class="py-2 pr-6">Pengembalian</th>
          <th class="py-2 pr-6">Status Surat</th>
          <th class="py-2 text-right">Dokumen</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($rows as $r)
          <tr class="border-b border-[#e6e0d6] hover:bg-[#f3f0ea]">
            <td class="py-2 pr-6">
              <div class="text-[14px] font-semibold">{{ $r->nama }}</div>
              <div class="text-[12px] text-[#a49b8c] mt-0.5">Ref: {{ $r->nomor_surat }}</div>
            </td>
            <td class="py-2 pr-6 text-[13px] text-[#6b6459] hidden sm:table-cell">
              <div>{{ $r->tgl_pinjam->format('d-m-Y') }}</div>
              <div class="text-[12px] text-[#a49b8c]">s/d {{ $r->tgl_kembali->format('d-m-Y') }}</div>
            </td>
            <td class="py-2 pr-6">
              @if ($r->sudah_kembali)
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-bold uppercase tracking-wider">Kembali</span>
              @else
                <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold uppercase tracking-wider">Dipinjam</span>
              @endif
            </td>
            <td class="py-2 pr-6">
              @if ($r->pdf_path)
                <span class="px-2 py-0.5 bg-[#e6e0d6] text-[10px] font-bold uppercase tracking-wider">Sudah Generate</span>
              @else
                <span class="px-2 py-0.5 bg-[#f3f0ea] text-[#a49b8c] border border-[#e6e0d6] text-[10px] font-bold uppercase tracking-wider">Belum Generate</span>
              @endif
            </td>
            <td class="py-2 text-right">
              <div class="flex items-center justify-end gap-4 text-[11px] font-bold uppercase tracking-wide">
                <a href="{{ route('peminjaman.ringkasan', $r) }}" class="text-[#a8501f] hover:underline">Ringkasan</a>
                @if ($r->pdf_path)
                  <a href="{{ route('peminjaman.preview', $r) }}" target="_blank" class="text-[#a8501f] hover:underline">PDF</a>
                @else
                  <span class="text-[#a49b8c] cursor-not-allowed">PDF</span>
                @endif
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="py-6">{{ $rows->links() }}</div>
  @endif
</div>
@endsection
