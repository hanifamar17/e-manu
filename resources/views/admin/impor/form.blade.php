@extends('layouts.app')
@section('title', 'Admin: Impor Data')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
@endphp
<div class="max-w-xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('admin.home') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-2">Impor Data</h1>
  <p class="text-sm text-[var(--ink-soft)] mt-0 mb-6">Format: Excel (.xlsx) atau CSV. Satu baris salah = seluruh impor dibatalkan.</p>

  @include('partials.flash')

  <div class="mb-8">
    <p class="{{ $lbl }}">1. Unduh template (format pasti sama dengan sistem)</p>
    @foreach ($types as $k => $label)
      <div class="flex flex-wrap items-baseline justify-between gap-2 py-2.5 border-b border-[var(--line)] text-sm">
        <div>
          <span class="font-semibold">{{ $label }}</span>
          <span class="mono text-[11px] text-[var(--ink-faint)] block">{{ implode(', ', $columns[$k]) }}</span>
        </div>
        <div class="flex gap-3 whitespace-nowrap">
          <a class="underline" href="{{ route('admin.impor.template', $k) }}">Template</a>
          <a class="underline" href="{{ route('admin.impor.template', [$k, 'data' => 1]) }}">Data saat ini</a>
        </div>
      </div>
    @endforeach
  </div>

  <p class="{{ $lbl }}">2. Unggah</p>
  <form method="POST" action="{{ route('admin.impor.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    <div>
      <label class="{{ $lbl }}" for="type">Data</label>
      <select class="{{ $inp }}" id="type" name="type" required>
        @foreach ($types as $k => $label)
          <option value="{{ $k }}" @selected(old('type') === $k)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="{{ $lbl }}" for="file">File .xlsx / .csv (maks 2 MB)</label>
      <input class="{{ $inp }}" type="file" id="file" name="file" accept=".xlsx,.csv,.txt" required>
    </div>
    <div>
      <label class="{{ $lbl }}" for="mode">Mode</label>
      <select class="{{ $inp }}" id="mode" name="mode" required>
        <option value="replace" @selected(old('mode', 'replace') === 'replace')>Ganti semua data lama</option>
        <option value="append" @selected(old('mode') === 'append')>Tambah ke data lama</option>
      </select>
    </div>
    <div>
      <label class="{{ $lbl }}" for="format">Format tanggal teks (Kalender). Template = YYYY-MM-DD</label>
      <select class="{{ $inp }}" id="format" name="format" required>
        @foreach ($formats as $k => $label)
          <option value="{{ $k }}" @selected(old('format', 'Y-m-d') === $k)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <button class="px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)]">Impor</button>
  </form>
</div>
@endsection
