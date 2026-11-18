@extends('layouts.app')
@section('title', 'Ubah Kelas')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-base sm:text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
@endphp
<div class="max-w-xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('admin.kelas.index') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-6">Ubah Kelas {{ $item->nama_kelas }}</h1>

  @include('partials.flash')

  <form data-lock method="POST" action="{{ route('admin.kelas.update', $item) }}" class="space-y-6">
    @csrf
    @method('PUT')
    <div>
      <label class="{{ $lbl }}" for="wali_kelas">Nama wali kelas</label>
      <input class="{{ $inp }}" id="wali_kelas" name="wali_kelas" maxlength="100" value="{{ old('wali_kelas', $item->wali_kelas) }}">
      @error('wali_kelas')<p class="text-xs text-red-600 mt-1 m-0">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="{{ $lbl }}" for="no_hp">No HP / WhatsApp</label>
      <input class="{{ $inp }}" id="no_hp" name="no_hp" inputmode="tel" maxlength="25" placeholder="081234567890" value="{{ old('no_hp', $item->no_hp) }}">
      <p class="text-[11px] text-[var(--ink-soft)] m-0 mt-1">08xx / +628xx / 628xx; disimpan sebagai 628xx.</p>
      @error('no_hp')<p class="text-xs text-red-600 mt-1 m-0">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="{{ $lbl }}" for="user_id">Akun login wali kelas</label>
      <select class="{{ $inp }} cursor-pointer" id="user_id" name="user_id">
        <option value="">— belum dihubungkan —</option>
        @foreach ($akun as $u)
          <option value="{{ $u->id }}" @selected((string) old('user_id', $item->user_id) === (string) $u->id)>{{ $u->nama }} ({{ $u->username }})</option>
        @endforeach
      </select>
      <p class="text-[11px] text-[var(--ink-soft)] m-0 mt-1">Hanya akun role wali_kelas. Buat akun di menu Akun Pengguna.</p>
      @error('user_id')<p class="text-xs text-red-600 mt-1 m-0">{{ $message }}</p>@enderror
    </div>
    <button type="submit" class="inline-block px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)]">Simpan</button>
  </form>
</div>
@include('partials.lock')
@endsection
