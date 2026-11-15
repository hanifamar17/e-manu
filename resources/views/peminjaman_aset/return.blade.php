@extends('layouts.app')
@section('title', 'Pengembalian Aset')

@section('content')
@php $lbl = 'block text-[11px] font-bold text-zinc-400 uppercase'; @endphp
<div class="max-w-2xl mx-auto px-5 py-9 sm:py-14 text-[#1f1c19]">
  @csrf
  <a href="{{ route('peminjaman.landing') }}" class="text-sm font-semibold text-[#6b6459] hover:text-[#1f1c19]">&larr; Kembali</a>
  <h1 class="text-2xl font-bold tracking-tight mt-8">Pengembalian Barang</h1>
  <p class="text-sm text-[#6b6459] mt-2 mb-10">Masukkan no. surat peminjaman pada form di bawah.</p>

  <div class="mb-8">
    <label class="{{ $lbl }} mb-1">No. Surat Peminjaman</label>
    <input id="loanCode" type="text" placeholder="001/PA/MANU/IX/2026" autocomplete="off"
           class="block py-2.5 w-full text-[15px] font-semibold border-b border-zinc-200 focus:outline-none focus:border-zinc-900 uppercase font-mono bg-transparent">
    <p id="notFound" class="hidden text-xs text-rose-600 mt-2">Peminjaman tidak ditemukan.</p>
  </div>

  <div id="loanSummary" class="hidden space-y-6 mb-8 border-t border-zinc-200 pt-6">
    <div class="grid grid-cols-2 gap-4">
      <div><span class="{{ $lbl }}">No. Surat</span><span id="sCode" class="text-sm font-mono font-semibold"></span></div>
      <div><span class="{{ $lbl }}">Status</span><span id="sStatus" class="text-xs font-bold uppercase"></span></div>
    </div>
    <div><span class="{{ $lbl }}">Peminjam</span><p id="sBorrower" class="text-base font-semibold"></p></div>
    <div class="grid grid-cols-2 gap-4">
      <div><span class="{{ $lbl }}">Tgl Pinjam</span><p id="sLoan" class="text-sm font-semibold"></p></div>
      <div><span class="{{ $lbl }}">Tgl Kembali</span><p id="sReturn" class="text-sm font-semibold"></p></div>
    </div>
    <div><span class="{{ $lbl }}">Tujuan</span><p id="sNote" class="text-sm text-zinc-500"></p></div>
    <div>
      <h3 class="text-[13px] font-bold uppercase tracking-widest">Daftar Barang</h3>
      <table class="w-full text-left text-sm mt-4"><tbody id="sItems" class="divide-y divide-zinc-100"></tbody></table>
    </div>
  </div>

  <div class="space-y-4">
    <div>
      <span class="{{ $lbl }}">Catatan Pengembalian</span>
      <textarea id="catatan" rows="2" maxlength="255" placeholder="Catatan kondisi barang (opsional)..."
                class="w-full text-sm border-b border-zinc-200 focus:outline-none focus:border-zinc-900 py-2 resize-none bg-transparent"></textarea>
    </div>
    <div id="status" class="hidden text-xs font-medium p-3 border"></div>
    <button id="btnReturn" disabled class="w-full bg-zinc-900 text-white text-[13px] font-semibold py-4 hover:bg-zinc-800 disabled:bg-zinc-200 disabled:cursor-not-allowed">Konfirmasi Pengembalian</button>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const $ = id => document.getElementById(id);
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const token = document.querySelector('input[name=_token]').value;
  let loan = null, timer;

  const show = (m, ok) => { const s = $('status'); s.textContent = m; s.className = 'text-xs font-medium p-3 border ' + (ok ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200'); };

  $('loanCode').addEventListener('input', e => {
    e.target.value = e.target.value.toUpperCase();
    clearTimeout(timer);
    timer = setTimeout(fetchLoan, 500);
  });

  async function fetchLoan() {
    const code = $('loanCode').value.trim();
    loan = null; $('btnReturn').disabled = true;
    $('loanSummary').classList.add('hidden'); $('notFound').classList.add('hidden');
    if (!code) return;
    try {
      const res = await fetch(@json(route('peminjaman.api.loan')) + '?code=' + encodeURIComponent(code), { headers: { Accept: 'application/json' } });
      const out = await res.json();
      if (!out.ok) { $('notFound').classList.remove('hidden'); return; }
      loan = out.loan;
      $('sCode').textContent = loan.code;
      $('sStatus').textContent = loan.status;
      $('sBorrower').textContent = loan.borrower_name;
      $('sLoan').textContent = loan.loan_date;
      $('sReturn').textContent = loan.return_date;
      $('sNote').textContent = loan.note || '-';
      $('sItems').innerHTML = loan.items.map(i => `
        <tr><td class="py-1.5">${esc(i.item_name)}</td><td class="py-1.5">${esc(i.merk)}</td>
        <td class="text-right">${i.qty} unit</td>
        <td class="text-right"><span class="text-[10px] font-bold uppercase ${i.dikembalikan ? 'text-emerald-600' : 'text-amber-600'}">${i.dikembalikan ? 'Kembali' : 'Dipinjam'}</span></td></tr>`).join('');
      const done = loan.status === 'dikembalikan';
      $('btnReturn').disabled = done;
      $('btnReturn').textContent = done ? 'Sudah Dikembalikan' : 'Konfirmasi Pengembalian';
      $('loanSummary').classList.remove('hidden');
    } catch (e) { console.error(e); show('Gagal terhubung ke server.', false); }
  }

  $('btnReturn').addEventListener('click', async () => {
    if (!loan || !confirm('Proses pengembalian? Pastikan fisik barang sesuai.')) return;
    const btn = $('btnReturn');
    btn.disabled = true; btn.textContent = 'Memproses...';
    const fd = new FormData();
    fd.append('code', loan.code);
    fd.append('catatan', $('catatan').value);
    try {
      const res = await fetch(@json(route('peminjaman.return.submit')), { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token }, body: fd });
      const out = await res.json();
      if (out.ok) { show('Berhasil dikembalikan.', true); $('catatan').value = ''; await fetchLoan(); return; }
      show(out.error || out.message || 'Gagal.', false);
    } catch (e) { show('Server error.', false); }
    btn.disabled = false; btn.textContent = 'Konfirmasi Pengembalian';
  });
});
</script>
@endsection
