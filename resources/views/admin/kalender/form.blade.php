@extends('layouts.app')
@section('title', $item->exists ? 'Edit Kalender' : 'Tambah Kalender')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
@endphp
<div class="max-w-xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('admin.kalender.index') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-6">{{ $item->exists ? 'Edit' : 'Tambah' }} Kalender</h1>

  @include('partials.flash')

  <form method="POST" action="{{ $item->exists ? route('admin.kalender.update', $item) : route('admin.kalender.store') }}" class="space-y-6">
    @csrf
    @if ($item->exists) @method('PUT') @endif
    <div class="grid grid-cols-2 gap-6">
      <div>
        <label class="{{ $lbl }}" for="tanggal_mulai">Mulai</label>
        <input class="{{ $inp }}" type="date" id="tanggal_mulai" name="tanggal_mulai" required
          value="{{ old('tanggal_mulai', $item->tanggal_mulai?->format('Y-m-d')) }}">
      </div>
      <div>
        <label class="{{ $lbl }}" for="tanggal_selesai">Selesai (kosong = 1 hari)</label>
        <input class="{{ $inp }}" type="date" id="tanggal_selesai" name="tanggal_selesai"
          value="{{ old('tanggal_selesai', $item->tanggal_selesai?->format('Y-m-d')) }}">
      </div>
    </div>
    <div>
      <label class="{{ $lbl }}" for="kategori">Kategori</label>
      <select class="{{ $inp }}" id="kategori" name="kategori" required>
        @foreach (\App\Models\KalenderAkademik::KATEGORI as $k)
          <option value="{{ $k }}" @selected(old('kategori', $item->kategori) === $k)>{{ ucfirst($k) }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="{{ $lbl }}" for="keterangan">Keterangan</label>
      <input class="{{ $inp }}" id="keterangan" name="keterangan" maxlength="200" required value="{{ old('keterangan', $item->keterangan) }}">
    </div>
    <button class="px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)]">Simpan</button>
  </form>
</div>
@endsection
