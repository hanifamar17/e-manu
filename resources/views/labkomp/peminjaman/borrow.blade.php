@extends('layouts.app')
@section('title', 'Lab Komp: Peminjaman Barang')

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
@endphp
<div class="max-w-2xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('peminjaman_invlabkomp.peminjaman_landing') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-2">Peminjaman Inventaris</h1>
  <p class="text-sm text-[var(--ink-soft)] mt-0 mb-6">Cari dan pilih barang dari inventaris lab.</p>

  @include('partials.flash')
  @if ($errors->any())
    <ul class="mb-6 text-sm text-red-700 list-disc pl-5">
      @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  @endif

  <div class="relative mb-8">
    <label class="{{ $lbl }}" for="searchItem">Cari Barang</label>
    <input id="searchItem" type="text" autocomplete="off" placeholder="Cari nama barang..."
      class="block w-full py-3 px-4 text-sm bg-[var(--line)]/30 border-0 focus:outline-none focus:ring-1 focus:ring-[var(--ink)] text-[var(--ink)]">
    <div id="searchResult" class="absolute left-0 right-0 mt-1 bg-[var(--paper)] border border-[var(--line)] shadow-lg z-40 hidden"></div>
  </div>

  <form id="borrowForm" data-lock method="POST" action="{{ route('peminjaman_invlabkomp.borrow_submit') }}" class="space-y-8 hidden">
    @csrf
    <div>
      <p class="{{ $lbl }}">Daftar Barang</p>
      <table class="w-full text-left text-[14px]">
        <thead class="mono text-[11px] font-bold text-[var(--ink-faint)] uppercase border-b border-[var(--line)]">
          <tr><th class="py-2">Barang</th><th class="py-2 text-center w-24">Qty</th><th class="py-2 text-center w-10">&times;</th></tr>
        </thead>
        <tbody id="selectedItems" class="divide-y divide-[var(--line)]"></tbody>
      </table>
    </div>

    <div class="pt-6 border-t border-[var(--line)] space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
          <label class="{{ $lbl }}" for="nama_peminjam">Nama Lengkap</label>
          <input class="{{ $inp }}" type="text" id="nama_peminjam" name="nama_peminjam" required maxlength="100" value="{{ old('nama_peminjam') }}">
        </div>
        <div>
          <label class="{{ $lbl }}" for="email_peminjam">Email</label>
          <input class="{{ $inp }}" type="email" id="email_peminjam" name="email_peminjam" required maxlength="150" value="{{ old('email_peminjam') }}">
        </div>
        <div>
          <label class="{{ $lbl }}" for="tgl_pinjam">Tgl Pinjam</label>
          <input class="{{ $inp }}" type="date" id="tgl_pinjam" name="tgl_pinjam" required value="{{ old('tgl_pinjam', now()->format('Y-m-d')) }}">
        </div>
        <div>
          <label class="{{ $lbl }}" for="tgl_kembali">Tgl Kembali</label>
          <input class="{{ $inp }}" type="date" id="tgl_kembali" name="tgl_kembali" required value="{{ old('tgl_kembali') }}">
        </div>
      </div>
      <div>
        <label class="{{ $lbl }}" for="catatan">Catatan</label>
        <textarea class="{{ $inp }} resize-none" id="catatan" name="catatan" rows="2" maxlength="500">{{ old('catatan') }}</textarea>
      </div>
    </div>

    <button type="submit" id="submitBtn"
      class="w-full inline-block px-6 py-3.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] active:bg-[var(--accent)] disabled:opacity-60 disabled:pointer-events-none">
      Konfirmasi Peminjaman &rarr;
    </button>
  </form>
</div>

<dialog id="confirmDlg" class="m-auto w-[calc(100%-2.5rem)] max-w-sm p-0 bg-[#faf8f5] text-[#1f1c19] border border-[#e6e0d6] shadow-xl">
  <form method="dialog" class="p-6">
    <p class="text-[11px] font-bold uppercase tracking-widest text-[#a8501f] mb-2">Konfirmasi</p>
    <h2 class="text-lg font-bold tracking-tight m-0">Ajukan peminjaman?</h2>
    <p id="cmText" class="text-sm text-[#6b6459] mt-2 mb-6">Pastikan data sudah benar.</p>
    <div class="flex gap-3">
      <button value="cancel"
        class="flex-1 py-3 text-[13px] font-semibold border border-[#e6e0d6] cursor-pointer touch-manipulation select-none transition hover:bg-[#f3f0ea] active:scale-[0.97]">
        Batal
      </button>
      <button value="ok"
        class="flex-1 py-3 text-[13px] font-semibold bg-[#1f1c19] text-[#faf8f5] cursor-pointer touch-manipulation select-none transition hover:bg-[#a8501f] active:scale-[0.97]">
        Ya, Ajukan
      </button>
    </div>
  </form>
</dialog>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const SEARCH_URL = @json(route('inventory_labkomp.api_search'));
  let selected = @json($preselected);
  const form = document.getElementById('borrowForm'), tbody = document.getElementById('selectedItems'),
        input = document.getElementById('searchItem'), result = document.getElementById('searchResult'),
        btn = document.getElementById('submitBtn');
  let timer;

  const el = (tag, cls, text) => { const e = document.createElement(tag); if (cls) e.className = cls; if (text !== undefined) e.textContent = text; return e; };

  function render() {
    tbody.innerHTML = '';
    selected.forEach((it, idx) => {
      const tr = el('tr');
      const tdName = el('td', 'py-3 font-medium', it.nama_barang);
      const hid = el('input'); hid.type = 'hidden'; hid.name = `items[${idx}][item_id]`; hid.value = it.id;
      tdName.appendChild(hid);

      const tdQty = el('td', 'py-3');
      const q = el('input', 'w-16 bg-transparent border-b border-[var(--line)] text-center focus:border-[var(--ink)] outline-none');
      q.type = 'number'; q.name = `items[${idx}][qty]`; q.min = 1; q.max = it.stok; q.value = it.qty; q.required = true;
      q.addEventListener('change', () => { it.qty = Math.min(Math.max(parseInt(q.value) || 1, 1), it.stok); q.value = it.qty; });
      tdQty.appendChild(q);

      const tdDel = el('td', 'py-3 text-center');
      const del = el('button', 'text-[var(--ink-faint)] hover:text-red-600 cursor-pointer touch-manipulation select-none transition active:scale-95', '\u2715');
      del.type = 'button';
      del.addEventListener('click', () => { selected.splice(idx, 1); render(); });
      tdDel.appendChild(del);

      tr.append(tdName, tdQty, tdDel);
      tbody.appendChild(tr);
    });
    form.classList.toggle('hidden', selected.length === 0);
  }

  function addItem(item) {
    const ex = selected.find(i => i.id === item.id);
    if (ex) { if (ex.qty < ex.stok) ex.qty++; }
    else selected.push({ id: item.id, nama_barang: item.nama_barang, stok: item.stok, qty: 1 });
    render();
  }

  input.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(async () => {
      const q = input.value.trim();
      if (!q) { result.classList.add('hidden'); return; }
      try {
        const res = await fetch(SEARCH_URL + '?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } });
        const data = await res.json();
        result.innerHTML = '';
        if (!data.length) result.appendChild(el('div', 'p-3 text-[13px] text-[var(--ink-faint)]', 'Tidak ada barang tersedia'));
        data.forEach(item => {
          const row = el('div', 'p-3 hover:bg-[var(--line)]/40 cursor-pointer text-[13px]');
          row.append(el('div', 'font-semibold', item.nama_barang), el('div', 'text-[var(--ink-faint)]', `Stok: ${item.stok} ${item.satuan}`));
          row.addEventListener('click', () => { addItem(item); result.classList.add('hidden'); input.value = ''; });
          result.appendChild(row);
        });
        result.classList.remove('hidden');
      } catch (e) { console.error(e); }
    }, 400);
  });

  const dlg = document.getElementById('confirmDlg');
  dlg.addEventListener('click', e => { if (e.target === dlg) dlg.close('cancel'); });   // klik luar = batal
  const confirmBorrow = () => new Promise(resolve => {
    const done = () => { dlg.removeEventListener('close', done); resolve(dlg.returnValue === 'ok'); };
    dlg.addEventListener('close', done);
    dlg.returnValue = '';
    dlg.showModal();
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!selected.length) { emanuNotify('Pilih barang dulu.', false); return; }
    const nama = document.getElementById('nama_peminjam').value.trim();
    document.getElementById('cmText').textContent =
      `${nama ? nama + ' · ' : ''}${selected.length} barang. Pastikan data sudah benar.`;
    if (!(await confirmBorrow())) return;
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

  render();
});
</script>
@endsection