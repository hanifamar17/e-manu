@extends('layouts.app')
@section('title', $item->exists ? 'Edit Barang Pinjaman' : 'Tambah Barang Pinjaman')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
@endphp
<div class="max-w-2xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('barang_pinjaman.index') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-6">{{ $item->exists ? 'Edit Barang Pinjaman: '.$item->kode : 'Tambah Barang Pinjaman' }}</h1>

  @include('partials.flash')

  <form method="POST" action="{{ $item->exists ? route('barang_pinjaman.update', $item) : route('barang_pinjaman.store') }}" class="space-y-6">
    @csrf
    @if ($item->exists) @method('PUT') @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div>
        <label class="{{ $lbl }}" for="nama_barang">Nama Barang</label>
        <input class="{{ $inp }}" id="nama_barang" name="nama_barang" maxlength="150" required value="{{ old('nama_barang', $item->nama_barang) }}">
      </div>
      <div>
        <label class="{{ $lbl }}" for="merk">Merk</label>
        <input class="{{ $inp }}" id="merk" name="merk" maxlength="100" required value="{{ old('merk', $item->merk) }}">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div>
        <label class="{{ $lbl }}" for="kondisi">Kondisi</label>
        <select class="{{ $inp }}" id="kondisi" name="kondisi" required>
          @foreach (\App\Models\Inventaris::KONDISI as $k)
            <option value="{{ $k }}" @selected(old('kondisi', $item->kondisi) === $k)>{{ $k }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="{{ $lbl }}" for="sumber_dana">Sumber Dana</label>
        <select class="{{ $inp }}" id="sumber_dana" name="sumber_dana">
          <option value="">- Pilih -</option>
          @foreach (\App\Models\Inventaris::SUMBER_DANA as $s)
            <option value="{{ $s }}" @selected(old('sumber_dana', $item->sumber_dana) === $s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="{{ $lbl }}" for="jumlah_total">Jumlah Total</label>
        <input class="{{ $inp }}" type="number" id="jumlah_total" name="jumlah_total" min="0" required value="{{ old('jumlah_total', $item->jumlah_total) }}">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
      <div>
        <label class="{{ $lbl }}" for="lokasi">Lokasi</label>
        <input class="{{ $inp }}" id="lokasi" name="lokasi" maxlength="100" required value="{{ old('lokasi', $item->lokasi) }}">
      </div>
      <div>
        <label class="{{ $lbl }}" for="tahun_pengadaan">Tahun Pengadaan</label>
        <input class="{{ $inp }}" type="number" id="tahun_pengadaan" name="tahun_pengadaan" min="1990" max="2100" required value="{{ old('tahun_pengadaan', $item->tahun_pengadaan) }}">
      </div>
      <div>
        <label class="{{ $lbl }}" for="sub_bidang">Sub Bidang</label>
        <select class="{{ $inp }}" id="sub_bidang" name="sub_bidang" required>
          <option value="">- Pilih -</option>
          @foreach (\App\Models\Inventaris::SUB_BIDANG as $s)
            <option value="{{ $s }}" @selected(old('sub_bidang', $item->sub_bidang) === $s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>
    </div>

    @if ($item->exists)
      <p class="text-[12px] border-l-2 border-[var(--accent)] bg-[var(--accent-soft)] px-4 py-3 m-0">
        Sedang dipinjam: <strong>{{ $item->jumlah_dipinjam }} dari {{ $item->jumlah_total }}</strong> unit.
        Jumlah total tidak boleh kurang dari jumlah yang dipinjam.
      </p>
    @endif

    <button class="px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)]">Simpan</button>
  </form>
</div>
@endsection