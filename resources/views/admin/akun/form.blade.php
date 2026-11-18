@extends('layouts.app')
@section('title', $item->exists ? 'Ubah Akun' : 'Tambah Akun')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-base sm:text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
@endphp
<div class="max-w-xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('admin.akun.index') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-6">{{ $item->exists ? 'Ubah' : 'Tambah' }} Akun</h1>

  @include('partials.flash')

  <form data-lock method="POST" action="{{ $item->exists ? route('admin.akun.update', $item) : route('admin.akun.store') }}" class="space-y-6" autocomplete="off">
    @csrf
    @if ($item->exists) @method('PUT') @endif
    <div>
      <label class="{{ $lbl }}" for="username">Username</label>
      @if ($item->exists)
        <p class="mono text-sm py-2.5 m-0">{{ $item->username }}</p>
      @else
        <input class="{{ $inp }}" id="username" name="username" maxlength="50" required autocapitalize="none" value="{{ old('username') }}">
      @endif
      @error('username')<p class="text-xs text-red-600 mt-1 m-0">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="{{ $lbl }}" for="nama">Nama lengkap</label>
      <input class="{{ $inp }}" id="nama" name="nama" maxlength="100" required value="{{ old('nama', $item->nama) }}">
      @error('nama')<p class="text-xs text-red-600 mt-1 m-0">{{ $message }}</p>@enderror
    </div>
    <div>
      <label class="{{ $lbl }}" for="role">Role</label>
      <select class="{{ $inp }} cursor-pointer" id="role" name="role" required>
        @foreach ($roles as $val => $label)
          <option value="{{ $val }}" @selected(old('role', $item->role) === $val)>{{ $label }}</option>
        @endforeach
      </select>
      @error('role')<p class="text-xs text-red-600 mt-1 m-0">{{ $message }}</p>@enderror
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div>
        <label class="{{ $lbl }}" for="password">{{ $item->exists ? 'Password baru (kosong = tetap)' : 'Password' }}</label>
        <input class="{{ $inp }}" type="password" id="password" name="password" minlength="8" autocomplete="new-password" {{ $item->exists ? '' : 'required' }}>
        @error('password')<p class="text-xs text-red-600 mt-1 m-0">{{ $message }}</p>@enderror
      </div>
      <div>
        <label class="{{ $lbl }}" for="password_confirmation">Ulangi password</label>
        <input class="{{ $inp }}" type="password" id="password_confirmation" name="password_confirmation" minlength="8" autocomplete="new-password" {{ $item->exists ? '' : 'required' }}>
      </div>
    </div>
    <button type="submit" class="inline-block px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)]">Simpan</button>
  </form>
</div>
@include('partials.lock')
@endsection
