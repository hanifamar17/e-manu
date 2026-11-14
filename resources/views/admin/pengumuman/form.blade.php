@extends('layouts.app')
@section('title', $item->exists ? 'Edit Pengumuman' : 'Tambah Pengumuman')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
@endphp
<style>#editor .ql-editor { min-height: 260px; }</style>
<div class="max-w-3xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('admin.pengumuman.index') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-6">{{ $item->exists ? 'Edit' : 'Tambah' }} Pengumuman</h1>

  @include('partials.flash')

  <form id="formPengumuman" method="POST" action="{{ $item->exists ? route('admin.pengumuman.update', $item) : route('admin.pengumuman.store') }}" class="space-y-6">
    @csrf
    @if ($item->exists) @method('PUT') @endif
    <div>
      <label class="{{ $lbl }}" for="title">Judul</label>
      <input class="{{ $inp }}" id="title" name="title" maxlength="255" required value="{{ old('title', $item->title) }}">
    </div>
    <div>
      <label class="{{ $lbl }}">Isi</label>
      <div id="editor" class="bg-white"></div>
      <input type="hidden" name="deskripsi" id="deskripsi">
    </div>
    <button class="px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)]">Simpan</button>
  </form>
</div>
@endsection

@section('scripts')
<script>
  const quill = new Quill('#editor', {
    theme: 'snow',
    modules: {
      toolbar: [
        [{ header: [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ list: 'ordered' }, { list: 'bullet' }],
        [{ align: [] }, { direction: 'rtl' }],
        ['link', 'blockquote'],
        ['clean']
      ]
    }
  });
  // Isi awal: disanitasi server dulu (data lama dari Flask bisa tak bersih)
  quill.clipboard.dangerouslyPasteHTML(@json(clean(old('deskripsi', $item->deskripsi ?? ''), 'quill')));

  document.getElementById('formPengumuman').addEventListener('submit', () => {
    document.getElementById('deskripsi').value = quill.root.innerHTML;
  });
</script>
@endsection