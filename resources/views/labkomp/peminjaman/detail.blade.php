@extends('layouts.app')
@section('title', 'Lab Komp: Detail Peminjaman '.$loan->kode)

@section('content')
@php
    $wa = "*DETAIL PEMINJAMAN INVENTARIS*\n*Kode:* {$loan->kode}\n*Nama:* {$loan->nama_peminjam}\n*Tgl Pinjam:* ".$loan->tgl_pinjam->format('d-m-Y')."\n*Status:* {$loan->status}\n\nLihat bukti lengkap:\n{$receiptUrl}";
    $lbl = 'block mono text-[11px] font-bold text-[var(--ink-faint)] uppercase tracking-wider mb-1';
@endphp
<div class="max-w-2xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('peminjaman_invlabkomp.history') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>

  <div class="flex items-center justify-between mt-8 mb-8">
    <h1 class="page-title text-2xl font-bold m-0">Detail Peminjaman</h1>
    <span class="px-2 py-0.5 mono text-[10px] font-bold uppercase border {{ $loan->status === 'dipinjam' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'border-[var(--line)] text-[var(--ink-soft)]' }}">{{ $loan->status }}</span>
  </div>

  @include('partials.flash')

  <div class="border-t border-[var(--line)] pt-6 grid grid-cols-2 gap-6">
    <div><span class="{{ $lbl }}">Kode Referensi</span><span class="mono text-[14px] font-semibold">{{ $loan->kode }}</span></div>
    <div><span class="{{ $lbl }}">Peminjam</span><p class="text-[14px] font-semibold m-0">{{ $loan->nama_peminjam }}</p></div>
    <div><span class="{{ $lbl }}">Tgl Pinjam</span><p class="text-[14px] font-semibold m-0">{{ $loan->tgl_pinjam->format('d-m-Y') }}</p></div>
    <div><span class="{{ $lbl }}">Batas Kembali</span><p class="text-[14px] font-semibold m-0">{{ $loan->tgl_kembali->format('d-m-Y') }}</p></div>
    <div class="col-span-2"><span class="{{ $lbl }}">Email</span><p class="text-[14px] m-0">{{ $loan->email_peminjam }}</p></div>
  </div>

  @if ($loan->catatan)
    <div class="mt-6 pt-4 border-t border-[var(--line)]">
      <span class="{{ $lbl }}">Catatan</span>
      <p class="text-[14px] text-[var(--ink-soft)] italic m-0">&ldquo;{{ $loan->catatan }}&rdquo;</p>
    </div>
  @endif

  <div class="mt-6 pt-4 border-t border-[var(--line)]">
    <span class="{{ $lbl }}">Daftar Barang</span>
    <table class="w-full text-left text-[14px] mt-2">
      <tbody class="divide-y divide-[var(--line)]">
        @foreach ($loan->items as $i)
          <tr><td class="py-1.5 font-medium">{{ $i->nama_barang }}</td><td class="py-1.5 text-right mono text-[var(--ink-soft)]">{{ $i->qty }}</td></tr>
        @endforeach
      </tbody>
    </table>
  </div>

  @if ($loan->status === 'dikembalikan')
    <div class="mt-6 pt-4 border-t border-[var(--line)]">
      <span class="{{ $lbl }}">Dikembalikan</span>
      <p class="text-[14px] m-0">{{ $loan->dikembalikan_at?->format('d-m-Y H:i') ?? '-' }}</p>
      @if ($loan->bukti_kembali)
        <a href="{{ asset($loan->bukti_kembali) }}" target="_blank" class="block mt-3"><img src="{{ asset($loan->bukti_kembali) }}" alt="Bukti pengembalian" class="max-h-56 border border-[var(--line)]"></a>
      @endif
    </div>
  @endif

  <div class="mt-8 pt-6 border-t border-[var(--line)] grid grid-cols-1 sm:grid-cols-3 gap-3">
    {{-- Ringkasan (solid) --}}
    <a href="{{ $receiptUrl }}" target="_blank"
       class="inline-block text-center bg-[var(--ink)] text-[var(--paper)] text-[13px] font-semibold py-3.5 no-underline cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)]">
      Ringkasan &rarr;
    </a>

    {{-- Salin Link (border / transparan) --}}
    <button type="button" id="btnShare" data-link="{{ $receiptUrl }}"
      class="inline-block border border-[var(--line)] text-[13px] font-semibold py-3.5 cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.97]">
      Salin Link
    </button>

    {{-- WhatsApp (solid) --}}
    <a href="https://wa.me/?text={{ urlencode($wa) }}" target="_blank"
       class="inline-block text-center bg-[var(--ink)] text-[var(--paper)] text-[13px] font-semibold py-3.5 no-underline cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)]">
      WhatsApp
    </a>
  </div>
</div>

<script>
document.getElementById('btnShare').addEventListener('click', async (e) => {
  const btn = e.currentTarget, link = btn.dataset.link, old = btn.textContent;
  if (btn.hasAttribute('aria-busy')) return;
  btn.setAttribute('aria-busy', 'true');
  btn.classList.add('pointer-events-none', 'opacity-60');
  try {
    await navigator.clipboard.writeText(link);
    btn.textContent = 'Tersalin \u2713';
    setTimeout(() => {
      btn.textContent = old;
      btn.removeAttribute('aria-busy');
      btn.classList.remove('pointer-events-none', 'opacity-60');
    }, 2000);
  } catch (err) {
    btn.removeAttribute('aria-busy');
    btn.classList.remove('pointer-events-none', 'opacity-60');
    window.prompt('Salin link:', link); // fallback (mis. bukan HTTPS)
  }
});
</script>
@endsection