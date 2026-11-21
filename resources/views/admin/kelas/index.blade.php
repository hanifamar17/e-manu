@extends('layouts.app')
@section('title', 'Admin: Kelas & Wali Kelas')

@section('content')
<div class="max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('admin.home') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Dashboard Admin
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-2">Kelas &amp; Wali Kelas</h1>
  <p class="text-sm text-[var(--ink-soft)] mb-6">Kelas ditambah / dihapus lewat menu Impor Data. Di sini: ubah wali kelas, nomor HP, dan akun login (role wali_kelas) tiap kelas.</p>

  @include('partials.flash')

    <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr>
          <th class="text-left py-2 eyebrow pr-4">Kelas</th>
          <th class="text-left py-2 eyebrow pr-4">Wali Kelas</th>
          <th class="text-left py-2 eyebrow pr-4">No. HP</th>
          <th class="text-left py-2 eyebrow">Akun</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($items as $k)
          <tr style="border-top:1px solid var(--line)">
            <td class="py-3 pr-4 font-semibold whitespace-nowrap">{{ $k->nama_kelas }}</td>
            <td class="py-3 pr-4">{{ $k->wali_kelas ?: '—' }}</td>
            <td class="mono text-xs py-3 pr-4 whitespace-nowrap">{{ $k->no_hp ?: '—' }}</td>
            <td class="mono text-xs py-3 pr-4 whitespace-nowrap">{{ $k->user?->username ?: 'belum dihubungkan' }}</td>
            <td class="py-3 text-right whitespace-nowrap">
              <a data-lock class="inline-block py-2 px-1 underline text-xs cursor-pointer touch-manipulation select-none transition hover:opacity-70 active:scale-95" href="{{ route('admin.kelas.edit', $k) }}">Ubah</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="py-10 text-center text-[var(--ink-faint)]">Belum ada data. Impor kelas dulu.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@include('partials.lock')
@endsection
