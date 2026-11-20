@extends('layouts.app')
@section('title', 'Surat Izin '.$item->id_perizinan)
@section('head')
<meta name="robots" content="noindex, nofollow">
<style>@media print { nav, footer, .no-print { display: none !important; } }</style>
@endsection

@section('content')
@php
    $aktif = in_array($item->status, ['aktif', 'disetujui'], true);
    $daftar = $item->siswaUrut();
@endphp
<div class="max-w-xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <div class="border border-[var(--ink)] p-6">
    <p class="eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] m-0">MA Nurul Ummah Kotagede</p>
    <h1 class="page-title text-2xl font-bold mt-2 mb-1">Surat Izin Siswa</h1>
    <p class="mono text-[12px] text-[var(--ink-soft)] m-0 mb-5">{{ $item->id_perizinan }}</p>

    <table class="w-full text-sm">
      @foreach ([
        'Mulai' => $item->tanggal_mulai->translatedFormat('l, j F Y').' pukul '.substr($item->jam_mulai, 0, 5),
        'Selesai' => $item->tanggal_selesai->translatedFormat('l, j F Y').' pukul '.substr($item->jam_selesai, 0, 5),
        'Durasi' => $item->durasi,
        'Alasan' => $item->alasan,
        'Petugas' => $item->petugas,
        'Wali kelas' => $item->diproses_oleh ?: $item->wali_kelas_tujuan,
      ] as $k => $v)
        <tr class="align-top border-b border-[var(--line)]">
          <td class="py-2 pr-4 w-28 mono text-[11px] uppercase tracking-widest text-[var(--ink-soft)]">{{ $k }}</td>
          <td class="py-2 font-semibold">{{ $v ?: '—' }}</td>
        </tr>
      @endforeach
    </table>

    <p class="eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mt-6 mb-2">Daftar siswa ({{ $daftar->count() }})</p>
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-[var(--ink)] text-left mono text-[11px] uppercase tracking-widest text-[var(--ink-soft)]">
          <th class="py-1.5 pr-2 w-8">No</th>
          <th class="py-1.5 pr-2">Nama</th>
          <th class="py-1.5 pr-2">NISN</th>
          <th class="py-1.5">Kelas</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($daftar as $i => $s)
          <tr class="align-top border-b border-[var(--line)]">
            <td class="py-1.5 pr-2 mono text-[11px] text-[var(--ink-soft)]">{{ $i + 1 }}</td>
            <td class="py-1.5 pr-2 font-semibold">{{ $s->nama_siswa }}</td>
            <td class="py-1.5 pr-2 mono text-[11px]">{{ $s->nisn }}</td>
            <td class="py-1.5">{{ $s->kelas_umum }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="mt-6 flex items-center justify-between gap-3">
      @include('partials.pz-badge', ['s' => $item->status])
      @if ($item->status === 'disetujui')
        <span class="text-xs text-[var(--ink-soft)]">Disetujui {{ $item->tanggal_acc?->translatedFormat('j M Y H:i') }}</span>
      @elseif ($item->status === 'aktif')
        <span class="text-xs text-[var(--ink-soft)]">Tanpa ACC wali kelas</span>
      @endif
    </div>
    @unless ($aktif)
      <p class="text-xs text-red-600 mt-3 m-0">Surat ini tidak berlaku ({{ str_replace('_', ' ', $item->status) }}).</p>
    @endunless
  </div>
  <button type="button" onclick="window.print()" class="no-print mt-6 px-4 py-2.5 text-sm font-semibold bg-white border border-[var(--line)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.97]">Cetak</button>
</div>
@endsection
