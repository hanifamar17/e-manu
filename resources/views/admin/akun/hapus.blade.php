@extends('layouts.app')
@section('title', 'Hapus Akun')

@section('content')
<div class="max-w-xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('admin.akun.index') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Batal
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-6">Hapus akun?</h1>

  <div class="card-outline p-4 border border-[var(--line)] mb-6">
    <p class="font-bold text-[15px] m-0">{{ $item->nama }}</p>
    <p class="mono text-[11px] text-[var(--ink-soft)] m-0 mt-1">{{ $item->username }} &middot; {{ $roles[$item->role] ?? $item->role }}</p>
    @if ($kelas->isNotEmpty())
      <p class="text-sm m-0 mt-3">Terhubung sebagai wali kelas: <strong>{{ $kelas->implode(', ') }}</strong>. Kelas tetap ada, tapi akun wali kelasnya kosong sampai dihubungkan ulang.</p>
    @endif
    <p class="text-sm m-0 mt-3">Tindakan ini tidak bisa dibatalkan.</p>
  </div>

  <form data-lock method="POST" action="{{ route('admin.akun.destroy', $item) }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="inline-block px-6 py-2.5 text-sm font-semibold bg-red-600 text-white cursor-pointer touch-manipulation select-none transition hover:bg-red-700 active:scale-[0.97]">Ya, hapus akun</button>
  </form>
</div>
@include('partials.lock')
@endsection
