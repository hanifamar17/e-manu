@extends('layouts.app')
@section('title', 'Lab Komp: Pengembalian Barang')

@section('content')
@php $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block'; @endphp
<div class="max-w-2xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('peminjaman_invlabkomp.peminjaman_landing') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-2">Pengembalian Barang</h1>
  <p class="text-sm text-[var(--ink-soft)] mt-0 mb-6">Masukkan kode peminjaman / referensi.</p>

  @include('partials.flash')

  <div class="mb-8">
    <label class="{{ $lbl }}" for="loanCode">Kode Peminjaman</label>
    <input id="loanCode" type="text" autocomplete="off" placeholder="LK-261118-ABCD"
      class="block py-2.5 w-full text-[15px] font-semibold mono uppercase bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]">
    <p id="loanMsg" class="text-xs text-[var(--ink-faint)] mt-2 mb-0 hidden"></p>
  </div>

  <div id="loanSummary" class="hidden space-y-6 mb-8 border-t border-[var(--line)] pt-6">
    <div class="grid grid-cols-2 gap-4">
      <div><span class="{{ $lbl }}">Kode Referensi</span><span id="sKode" class="mono text-sm font-semibold"></span></div>
      <div><span class="{{ $lbl }}">Status</span><span id="sStatus" class="text-xs font-bold uppercase"></span></div>
    </div>
    <div><span class="{{ $lbl }}">Peminjam</span><p id="sBorrower" class="text-base font-semibold m-0"></p></div>
    <div class="grid grid-cols-2 gap-4">
      <div><span class="{{ $lbl }}">Tgl Pinjam</span><p id="sLoanDate" class="text-sm font-semibold m-0"></p></div>
      <div><span class="{{ $lbl }}">Tgl Kembali</span><p id="sReturnDate" class="text-sm font-semibold m-0"></p></div>
    </div>
    <div><span class="{{ $lbl }}">Catatan</span><p id="sNote" class="text-sm text-[var(--ink-soft)] m-0"></p></div>
    <div>
      <span class="{{ $lbl }}">Daftar Barang</span>
      <table class="w-full text-left text-sm mt-2"><tbody id="sItems" class="divide-y divide-[var(--line)]"></tbody></table>
    </div>
  </div>

  <form id="returnForm" data-lock method="POST" action="{{ route('peminjaman_invlabkomp.return_submit') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    <input type="hidden" name="code" id="codeHidden">
    <div>
      <label class="{{ $lbl }}" for="proof">Foto bukti (opsional)</label>
      <input type="file" id="proof" name="proof" accept="image/*" class="block w-full text-sm">
    </div>
    <button type="submit" id="btnReturn" disabled
      class="w-full inline-block px-6 py-3.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)] disabled:opacity-40 disabled:cursor-not-allowed disabled:pointer-events-none">
      Konfirmasi Pengembalian
    </button>
  </form>
</div>

<dialog id="confirmDlg" class="m-auto w-[calc(100%-2.5rem)] max-w-sm p-0 bg-[#faf8f5] text-[#1f1c19] border border-[#e6e0d6] shadow-xl">
  <form method="dialog" class="p-6">
    <p class="text-[11px] font-bold uppercase tracking-widest text-[#a8501f] mb-2">Konfirmasi</p>
    <h2 class="text-lg font-bold tracking-tight m-0">Proses pengembalian?</h2>
    <p id="cmText" class="text-sm text-[#6b6459] mt-2 mb-6">Pastikan fisik barang sesuai.</p>
    <div class="flex gap-3">
      <button value="cancel"
        class="flex-1 py-3 text-[13px] font-semibold border border-[#e6e0d6] cursor-pointer touch-manipulation select-none transition hover:bg-[#f3f0ea] active:scale-[0.97]">
        Batal
      </button>
      <button value="ok"
        class="flex-1 py-3 text-[13px] font-semibold bg-[#1f1c19] text-[#faf8f5] cursor-pointer touch-manipulation select-none transition hover:bg-[#a8501f] active:scale-[0.97]">
        Ya, Selesaikan
      </button>
    </div>
  </form>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const API = @json(route('peminjaman_invlabkomp.api_loan'));
  const $ = (id) => document.getElementById(id);
  const codeInput = $('loanCode'), summary = $('loanSummary'), msg = $('loanMsg'),
        btn = $('btnReturn'), hidden = $('codeHidden'), form = $('returnForm');
  let timer, current = null;

  const setMsg = (t) => { msg.textContent = t || ''; msg.classList.toggle('hidden', !t); };

  codeInput.addEventListener('input', () => {
    codeInput.value = codeInput.value.toUpperCase();
    clearTimeout(timer);
    timer = setTimeout(load, 400);
  });

  async function load() {
    const code = codeInput.value.trim();
    current = null; hidden.value = ''; btn.disabled = true; summary.classList.add('hidden'); setMsg('');
    if (!code) return;
    setMsg('Mencari...');
    try {
      const res = await fetch(API + '?code=' + encodeURIComponent(code), { headers: { Accept: 'application/json' } });
      const data = await res.json();
      if (!data.ok) { setMsg(data.error || 'Kode tidak ditemukan'); return; }
      setMsg('');
      current = data.loan;
      $('sKode').textContent = current.kode;
      $('sStatus').textContent = current.status;
      $('sBorrower').textContent = current.nama_peminjam;
      $('sLoanDate').textContent = current.tgl_pinjam;
      $('sReturnDate').textContent = current.tgl_kembali;
      $('sNote').textContent = current.catatan || '-';
      const body = $('sItems'); body.innerHTML = '';
      current.items.forEach(i => {
        const tr = document.createElement('tr');
        const a = document.createElement('td'); a.className = 'py-1.5'; a.textContent = i.nama_barang;
        const b = document.createElement('td'); b.className = 'py-1.5 text-right'; b.textContent = '\u00d7' + i.qty;
        tr.append(a, b); body.appendChild(tr);
      });
      summary.classList.remove('hidden');
      if (current.status === 'dikembalikan') { setMsg('Peminjaman ini sudah dikembalikan.'); return; }
      hidden.value = current.kode;
      btn.disabled = false;
    } catch (e) { setMsg('Gagal memuat data.'); }
  }

  const dlg = $('confirmDlg');
  dlg.addEventListener('click', e => { if (e.target === dlg) dlg.close('cancel'); });   // klik luar = batal
  const confirmReturn = () => new Promise(resolve => {
    const done = () => { dlg.removeEventListener('close', done); resolve(dlg.returnValue === 'ok'); };
    dlg.addEventListener('close', done);
    dlg.returnValue = '';
    dlg.showModal();
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!current || !hidden.value) return;
    $('cmText').textContent = `${current.nama_peminjam} · ${current.items.length} barang. Pastikan fisik barang sesuai.`;
    if (!(await confirmReturn())) return;
    btn.disabled = true;
    btn.textContent = 'Memproses...';
    btn.classList.add('pointer-events-none', 'opacity-60');
    form.submit();   // submit() tidak memicu event submit lagi
  });

  // Anti-klik-ganda tambahan (data-lock)
  const LOCK = ['pointer-events-none', 'opacity-60'];
  const lock = el => { el.setAttribute('aria-busy', 'true'); el.classList.add(...LOCK); };
  const unlockAll = () => document.querySelectorAll('[data-lock] [aria-busy], a[data-lock][aria-busy], button[aria-busy]').forEach(el => {
    el.removeAttribute('aria-busy'); el.classList.remove(...LOCK);
  });

  document.querySelectorAll('a[data-lock]').forEach(a => a.addEventListener('click', e => {
    if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank') return;
    if (a.hasAttribute('aria-busy')) { e.preventDefault(); return; }
    lock(a);
  }));

  window.addEventListener('pageshow', unlockAll);
});
</script>
@endsection