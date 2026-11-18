@extends('layouts.app')
@section('title', 'Admin: Ekspor Data')

@section('content')
@php
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
@endphp
<div class="max-w-xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('admin.home') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-2">Ekspor Data</h1>
  <p class="text-sm text-[var(--ink-soft)] mt-0 mb-6">Unduh data asli (.xlsx). Format sama dengan template impor. Template kosong: menu <a class="underline" href="{{ route('admin.impor.form') }}">Impor</a>.</p>

  @include('partials.flash')

  <p class="{{ $lbl }}">Data</p>
  @foreach ($types as $k => $label)
    <div class="flex items-baseline justify-between gap-2 py-2.5 border-b border-[var(--line)] text-sm">
      <span class="font-semibold">{{ $label }}</span>
      <a class="underline whitespace-nowrap" href="{{ route('admin.ekspor.download', $k) }}">Unduh</a>
    </div>
  @endforeach
</div>
@endsection
