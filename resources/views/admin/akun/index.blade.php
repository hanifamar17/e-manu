@extends('layouts.app')
@section('title', 'Admin: Akun Pengguna')

@section('content')
<div class="max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('admin.home') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Dashboard Admin
  </a>
  <div class="flex flex-wrap items-center justify-between gap-3 mt-8 mb-6">
    <h1 class="page-title text-2xl font-bold m-0">Akun Pengguna</h1>
    <a data-lock href="{{ route('admin.akun.create') }}"
       class="px-4 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] no-underline">
      + Tambah Akun
    </a>
  </div>

  @include('partials.flash')

  <div class="space-y-2">
    @foreach ($items as $u)
      <div class="card-outline p-4 border border-[var(--line)] flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
          <p class="font-bold text-[15px] m-0">{{ $u->nama }}</p>
          <p class="mono text-[11px] text-[var(--ink-soft)] m-0 mt-1">
            {{ $u->username }} &middot; {{ $roles[$u->role] ?? $u->role }}@if ($kelasBy->has($u->id)) &middot; wali {{ $kelasBy[$u->id] }}@endif
          </p>
        </div>
        <div class="flex gap-2">
          <a data-lock href="{{ route('admin.akun.edit', $u) }}"
             class="px-4 py-2.5 text-sm font-semibold bg-white border border-[var(--line)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.97] no-underline text-inherit">Ubah</a>
          @unless ($u->is(auth()->user()))
            <a data-lock href="{{ route('admin.akun.hapus', $u) }}"
               class="px-4 py-2.5 text-sm font-semibold bg-white border border-[var(--line)] text-red-600 cursor-pointer touch-manipulation select-none transition hover:bg-red-600 hover:text-white hover:border-red-600 active:scale-[0.97] no-underline">Hapus</a>
          @endunless
        </div>
      </div>
    @endforeach
  </div>
</div>
@include('partials.lock')
@endsection
