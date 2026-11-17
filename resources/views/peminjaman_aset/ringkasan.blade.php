@extends('layouts.app')
@section('title', 'Ringkasan Peminjaman Aset')

@section('content')
@php
  $lbl = 'block text-[11px] font-bold text-[#a49b8c] uppercase tracking-wider mb-1';
@endphp
<div class="max-w-2xl mx-auto px-5 py-9 sm:py-14 text-[#1f1c19]">
  @auth
    <a href="{{ route('peminjaman.riwayat') }}" class="text-sm font-semibold text-[#6b6459] hover:text-[#1f1c19]">&larr; Kembali</a>
  @endauth

  <div class="mb-10 mt-8">
    <h1 class="text-2xl font-bold tracking-tight mb-2">Ringkasan Peminjaman</h1>
    <p class="text-sm text-[#6b6459]">No. Surat: <span class="font-mono font-bold bg-[#e6e0d6]/50 px-1.5 py-0.5">{{ $p->nomor_surat }}</span></p>
  </div>

  <div class="space-y-8">
    <div class="grid grid-cols-2 gap-x-6 gap-y-6">
      <div><span class="{{ $lbl }}">Nama Peminjam</span><p class="text-[14px] font-semibold">{{ $p->nama }}</p></div>
      <div><span class="{{ $lbl }}">Pekerjaan</span><p class="text-[14px]">{{ $p->pekerjaan }}</p></div>
      <div><span class="{{ $lbl }}">No. Telepon</span><p class="text-[14px] font-mono">{{ $p->telepon }}</p></div>
      <div><span class="{{ $lbl }}">No. Identitas</span><p class="text-[14px] font-mono">{{ $noIdentitas }}</p></div>
      <div><span class="{{ $lbl }}">Jaminan</span>
        <p class="text-[14px]">
          {{ collect([$p->jaminan_identitas ? 'Identitas asli (' . $p->jenis_identitas_jaminan . ')' : null, $p->jaminan_surat ? 'Surat jaminan' : null])->filter()->implode(' + ') }}
        </p></div>
      <div><span class="{{ $lbl }}">Periode</span><p class="text-[14px] font-semibold">{{ $p->tgl_pinjam->format('d-m-Y') }} s/d {{ $p->tgl_kembali->format('d-m-Y') }}</p></div>
    </div>

    <div class="border-t border-[#e6e0d6] pt-6 space-y-6">
      <div><span class="{{ $lbl }}">Lokasi Penggunaan</span><p class="text-[14px]">{{ $p->lokasi_penggunaan }}</p></div>
      <div><span class="{{ $lbl }}">Alamat Lengkap</span><p class="text-[14px] leading-relaxed">{{ $p->alamat }}</p></div>
      <div><span class="{{ $lbl }}">Tujuan</span><p class="text-[14px] italic text-[#6b6459]">"{{ $p->tujuan }}"</p></div>
    </div>

    <div class="pt-6 border-t border-[#e6e0d6]">
      <h3 class="{{ $lbl }} mb-4">Daftar Barang</h3>
      <table class="w-full text-left text-[14px]">
        <tbody class="divide-y divide-[#e6e0d6]">
          @foreach ($p->items as $i)
            <tr>
              <td class="py-2.5 pr-3 text-[#a49b8c] font-mono">{{ $i->no }}</td>
              <td class="py-2.5 pr-3 font-semibold">{{ $i->nama_barang }}</td>
              <td class="py-2.5 pr-3 text-[#6b6459]">{{ $i->merk ?: '-' }}</td>
              <td class="py-2.5 pr-3 font-medium">{{ $i->jumlah }}</td>
              <td class="py-2.5"><span class="text-xs px-2 py-0.5 bg-[#f3f0ea] border border-[#e6e0d6]">{{ $i->kondisi }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-12 pt-8 border-t border-[#e6e0d6]">
    @if ($p->pdf_path)
      <div class="bg-[#f3f0ea] p-5 border border-[#e6e0d6]">
        <p class="text-sm font-medium mb-4">Dokumen peminjaman sudah tersedia.</p>
        <a href="{{ route('peminjaman.preview', $p) }}" target="_blank"
           class="block w-full text-center bg-[#1f1c19] text-white text-[13px] font-semibold py-3.5 hover:bg-[#333]">Lihat PDF</a>
      </div>
    @else
      <form id="generateForm" class="space-y-4">
        @csrf
        <label class="flex items-start gap-3 cursor-pointer">
          <input type="checkbox" id="setuju" name="setuju" value="1" class="mt-1">
          <span class="text-sm text-[#6b6459]">Saya memastikan seluruh data di atas valid dan benar.</span>
        </label>
        <button type="submit" id="btnGenerate" disabled
                class="w-full px-5 py-3.5 bg-[#1f1c19] text-white text-[13px] font-semibold disabled:bg-[#e6e0d6] disabled:text-[#a49b8c] disabled:cursor-not-allowed">
          Generate Dokumen PDF
        </button>
        <div id="status" class="hidden text-xs font-medium p-3 border"></div>
      </form>
    @endif
  </div>
</div>

@unless ($p->pdf_path)

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('generateForm'), cb = document.getElementById('setuju'),
        btn = document.getElementById('btnGenerate');
  
  const show = (m, ok) => emanuNotify(m, ok);
  
  cb.addEventListener('change', () => { btn.disabled = !cb.checked; });
  form.addEventListener('submit', async e => {
    e.preventDefault();
    if (!cb.checked) return;
    btn.disabled = true; btn.textContent = 'Memproses berkas PDF...';
    try {
      const res = await fetch(@json(route('peminjaman.generate', $p)), { method: 'POST', headers: { Accept: 'application/json' }, body: new FormData(form) });
      const out = await res.json();
      if (res.ok && out.success) { show(out.message, true); setTimeout(() => location.reload(), 800); return; }
      show(out.message || 'Gagal membuat PDF.', false);
    } catch (err) { show('Gangguan jaringan, coba lagi.', false); }
    btn.disabled = false; btn.textContent = 'Generate Dokumen PDF';
  });
});
</script>
@endunless
@endsection
