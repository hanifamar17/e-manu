@extends('layouts.app')
@section('title', 'Admin: Kalender Akademik')

@section('content')
<div class="max-w-4xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('admin.booking.index') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <div class="flex flex-wrap items-end justify-between gap-3 mt-8 mb-6">
    <h1 class="page-title text-2xl font-bold m-0">Kalender Akademik</h1>
    <div class="flex gap-3 text-sm">
      <a class="underline" href="{{ route('admin.impor.form') }}">Impor CSV</a>
      <a class="px-3 py-1.5 bg-[var(--ink)] text-[var(--paper)] font-semibold" href="{{ route('admin.kalender.create') }}">+ Tambah</a>
    </div>
  </div>

  @include('partials.flash')

  <table class="w-full text-sm">
    <thead>
      <tr>
        <th class="text-left py-2 eyebrow pr-4">Tanggal</th>
        <th class="text-left py-2 eyebrow pr-4">Kategori</th>
        <th class="text-left py-2 eyebrow">Keterangan</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $k)
        <tr style="border-top:1px solid var(--line)">
          <td class="mono text-xs py-3 pr-4 whitespace-nowrap">
            {{ $k->tanggal_mulai->format('d/m/Y') }}@if (! $k->tanggal_selesai->eq($k->tanggal_mulai)) &ndash; {{ $k->tanggal_selesai->format('d/m/Y') }}@endif
          </td>
          <td class="mono text-xs py-3 pr-4 uppercase">{{ $k->kategori }}</td>
          <td class="py-3">{{ $k->keterangan }}</td>
          <td class="py-3 text-right whitespace-nowrap">
            <a class="underline text-xs mr-3" href="{{ route('admin.kalender.edit', $k) }}">Edit</a>
            <form method="POST" action="{{ route('admin.kalender.destroy', $k) }}" class="inline" onsubmit="return confirm('Hapus?')">
              @csrf @method('DELETE')
              <button class="text-xs text-red-600">Hapus</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="py-10 text-center text-[var(--ink-faint)]">Belum ada data.</td></tr>
      @endforelse
    </tbody>
  </table>
  <div class="mt-6">{{ $items->links() }}</div>
</div>
@endsection
