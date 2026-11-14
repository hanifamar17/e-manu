@extends('layouts.app')
@section('title', 'Admin: Pengumuman')

@section('content')
<div class="max-w-4xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('admin.home') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <div class="flex flex-wrap items-end justify-between gap-3 mt-8 mb-6">
    <h1 class="page-title text-2xl font-bold m-0">Pengumuman</h1>
    <a class="px-3 py-1.5 bg-[var(--ink)] text-[var(--paper)] text-sm font-semibold" href="{{ route('admin.pengumuman.create') }}">+ Tambah</a>
  </div>

  @include('partials.flash')

  <table class="w-full text-sm">
    <thead>
      <tr>
        <th class="text-left py-2 eyebrow pr-4">Judul</th>
        <th class="text-left py-2 eyebrow pr-4">Diperbarui</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @forelse ($items as $p)
        <tr style="border-top:1px solid var(--line)">
          <td class="py-3 pr-4">
            <a class="font-semibold hover:underline" href="{{ route('pengumuman.pengumuman_detail', $p->id) }}" target="_blank">{{ $p->title }}</a>
          </td>
          <td class="mono text-xs py-3 pr-4 whitespace-nowrap">{{ $p->updated_at->translatedFormat('j M Y, H:i') }}</td>
          <td class="py-3 text-right whitespace-nowrap">
            <a class="underline text-xs mr-3" href="{{ route('admin.pengumuman.edit', $p) }}">Edit</a>
            <form method="POST" action="{{ route('admin.pengumuman.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus pengumuman ini?')">
              @csrf @method('DELETE')
              <button class="text-xs text-red-600">Hapus</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="3" class="py-10 text-center text-[var(--ink-faint)]">Belum ada pengumuman.</td></tr>
      @endforelse
    </tbody>
  </table>
  <div class="mt-6">{{ $items->links() }}</div>
</div>
@endsection