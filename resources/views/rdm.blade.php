@extends('layouts.app')
@section('title', 'Rapor Digital Madrasah (RDM)')

@section('content')
@php $rdmUrl = 'https://rapor.manu.sch.id/'; @endphp
<div class="max-w-xl mx-auto px-5 pt-12 pb-20 bg-[#faf8f5] text-[#1f1c19]">
  <a href="/repository" class="text-sm font-semibold text-[#6b6459] hover:text-[#1f1c19]">&larr; Kembali</a>

  <h1 class="text-[26px] leading-[1.15] font-semibold font-['Source_Serif_4',ui-serif,Georgia,serif] tracking-[-0.01em] mb-3 mt-8 break-words min-w-0">Rapor Digital Madrasah (RDM)</h1>
  <p class="text-sm text-[#6b6459] mt-2 mb-8 max-w-[52ch] leading-relaxed">
    RDM belum bisa dibuka langsung dari e-manu. Salin alamat di bawah, lalu tempel di tab baru
    atau simpan sebagai bookmark.
  </p>

  <label for="rdmUrl" class="block text-[11px] font-bold uppercase tracking-wider text-[#a49b8c] mb-1.5">Alamat RDM</label>
  <div class="flex items-stretch border border-[#e6e0d6] bg-white/60">
    <input id="rdmUrl" type="text" readonly value="{{ $rdmUrl }}"
           class="flex-1 min-w-0 px-3 py-3 text-sm font-mono bg-transparent border-0 focus:outline-none">
    <button type="button" id="btnCopy"
            class="px-5 text-xs font-bold uppercase tracking-widest bg-[#1f1c19] text-[#faf8f5] hover:bg-[#a8501f] transition-colors">
      Salin
    </button>
  </div>
  <p id="copyStatus" class="hidden text-xs font-medium mt-3 text-emerald-700"></p>

  <ol class="mt-10 space-y-2 text-sm text-[#6b6459] list-decimal list-inside">
    <li>Klik <strong>Salin</strong></li>
    <li>Buka tab baru (<kbd>Ctrl</kbd>+<kbd>T</kbd>)</li>
    <li>Tempel (<kbd>Ctrl</kbd>+<kbd>V</kbd>) di address bar, lalu Enter</li>
  </ol>

  <p class="mt-10 text-xs text-[#a49b8c]">
    <a href="{{ $rdmUrl }}" target="_blank" rel="noopener" class="underline hover:text-[#1f1c19]">Coba buka langsung</a>
    (mungkin ditolak oleh RDM).
  </p>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('rdmUrl');
  const status = document.getElementById('copyStatus');
  const done = ok => {
    status.textContent = ok ? 'Alamat tersalin. Tempel di tab baru.' : 'Gagal menyalin. Blok alamat lalu tekan Ctrl+C.';
    status.className = 'text-xs font-medium mt-3 ' + (ok ? 'text-emerald-700' : 'text-rose-700');
  };

  document.getElementById('btnCopy').addEventListener('click', async () => {
    // navigator.clipboard hanya jalan di HTTPS/localhost; domain lokal http (mis. .test) perlu fallback
    if (navigator.clipboard && window.isSecureContext) {
      try { await navigator.clipboard.writeText(input.value); return done(true); } catch (e) {}
    }
    input.focus();
    input.select();
    input.setSelectionRange(0, input.value.length);
    let ok = false;
    try { ok = document.execCommand('copy'); } catch (e) {}
    done(ok);
  });

  input.addEventListener('focus', () => input.select());
});
</script>
@endsection