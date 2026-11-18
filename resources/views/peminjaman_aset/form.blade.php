@extends('layouts.app')
@section('title', 'Form Peminjaman Aset')

@section('content')
@php
  $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
  $inp = 'block w-full py-2.5 px-0 text-base sm:text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
  $h2  = 'text-[13px] font-bold uppercase tracking-wide border-b border-[var(--line)] pb-2 mb-6';
@endphp
<div class="max-w-2xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('peminjaman.landing') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>
  <h1 class="page-title text-2xl font-bold mt-8 mb-2">Formulir Peminjaman Aset</h1>
  <p class="text-sm text-[var(--ink-soft)] mt-0 mb-10 max-w-[52ch]">Pilih barang, lengkapi data peminjam, lalu kirim. Ringkasan bisa dicek setelah dikirim.</p>

  @include('partials.flash')

  <form id="borrowForm" class="space-y-14" novalidate>
    @csrf

    <section>
      <h2 class="{{ $h2 }}">01 · Pilih Barang</h2>
      <div class="relative mb-6">
        <input type="text" id="searchItem" placeholder="Cari barang, merk, atau lokasi..." class="{{ $inp }}" autocomplete="off">
        <div id="searchResult" class="hidden absolute z-30 w-full mt-2 bg-[var(--paper)] border border-[var(--line)] shadow-lg p-1 max-h-72 overflow-y-auto"></div>
      </div>
      <div class="flex justify-between mb-2">
        <span class="{{ $lbl }} m-0">Aset dipilih</span>
        <span id="selectedCount" class="mono text-[11px] font-bold text-[var(--accent)]">0 item</span>
      </div>
      <table class="w-full text-left text-sm border-t border-[var(--line)]"><tbody id="selectedBody" class="divide-y divide-[var(--line)]"></tbody></table>
      <p id="selectedEmpty" class="py-6 text-center text-[13px] text-[var(--ink-faint)] border-b border-[var(--line)] m-0">Belum ada barang dipilih.</p>
    </section>

    <section>
      <h2 class="{{ $h2 }}">02 · Data Peminjam</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
        <div><label class="{{ $lbl }}">Nama Lengkap *</label><input name="nama" class="{{ $inp }}" placeholder="Sesuai identitas" required></div>
        <div><label class="{{ $lbl }}">Jenis Kelamin</label>
          <select name="jenis_kelamin" class="{{ $inp }} cursor-pointer"><option>Laki-laki</option><option>Perempuan</option></select></div>
        <div><label class="{{ $lbl }}">Pekerjaan / Instansi *</label><input name="pekerjaan" class="{{ $inp }}" placeholder="Contoh: Guru, Mahasiswa" required></div>
        <div><label class="{{ $lbl }}">No. Telepon / WA *</label><input name="telepon" type="tel" inputmode="tel" class="{{ $inp }}" placeholder="08xx-xxxx-xxxx" required></div>
        <div class="sm:col-span-2"><label class="{{ $lbl }}">Alamat *</label><input name="alamat" class="{{ $inp }}" placeholder="Alamat domisili" required></div>
        <div class="sm:col-span-2"><label class="{{ $lbl }}">No. Identitas (KTP/SIM) *</label><input name="no_identitas" inputmode="numeric" class="{{ $inp }}" placeholder="Nomor sesuai identitas" required></div>
      </div>
    </section>

    <section>
      <h2 class="{{ $h2 }}">03 · Detail Peminjaman</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
        <div><label class="{{ $lbl }}">Tujuan *</label><input name="tujuan" class="{{ $inp }}" placeholder="Contoh: Praktikum, dokumentasi" required></div>
        <div><label class="{{ $lbl }}">Lokasi Penggunaan *</label><input name="lokasi_penggunaan" class="{{ $inp }}" placeholder="Contoh: Lab Komputer" required></div>
        <div><label class="{{ $lbl }}">Tanggal Pinjam *</label><input type="date" name="tgl_pinjam" class="{{ $inp }} cursor-pointer" required></div>
        <div><label class="{{ $lbl }}">Tanggal Kembali *</label><input type="date" name="tgl_kembali" class="{{ $inp }} cursor-pointer" required></div>
        <div><label class="{{ $lbl }}">Kota *</label><input name="kota" value="Yogyakarta" class="{{ $inp }}" required></div>
        <div><label class="{{ $lbl }}">Tanggal Surat *</label><input type="date" name="tgl_surat" value="{{ now()->toDateString() }}" class="{{ $inp }} cursor-pointer" required></div>
        <div class="sm:col-span-2"><label class="{{ $lbl }}">Nama Petugas *</label><input name="nama_petugas" class="{{ $inp }}" placeholder="Petugas yang menyerahkan barang" required></div>
      </div>
    </section>

    <section>
      <h2 class="{{ $h2 }}">04 · Jaminan</h2>
      <div class="flex flex-wrap items-center gap-x-8 gap-y-2">
        <label class="flex items-center gap-2 text-sm py-2 cursor-pointer touch-manipulation"><input type="checkbox" name="jaminan_identitas" id="jaminanIdentitas" class="w-4 h-4 cursor-pointer accent-[var(--ink)]"> Identitas</label>
        <select name="jenis_identitas_jaminan" class="{{ $inp }} w-auto min-w-[100px] cursor-pointer"><option>KTP</option><option>SIM</option></select>
        <label class="flex items-center gap-2 text-sm py-2 cursor-pointer touch-manipulation"><input type="checkbox" name="jaminan_surat" id="jaminanSurat" class="w-4 h-4 cursor-pointer accent-[var(--ink)]"> Surat Resmi</label>
      </div>
    </section>

    <div>
      <button type="submit" id="btnSubmit"
        class="w-full py-4 px-6 bg-[var(--ink)] text-[var(--paper)] text-xs font-bold uppercase tracking-widest cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.98] active:bg-[var(--accent)] disabled:opacity-60 disabled:cursor-not-allowed disabled:active:scale-100">Kirim Peminjaman</button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const $ = id => document.getElementById(id);
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const form = $('borrowForm'), box = $('searchResult'), input = $('searchItem'), btn = $('btnSubmit');
  const BTN_LABEL = btn.textContent;
  let selected = [], timer, busy = false;

  const notify = (msg, ok = false) => emanuNotify(msg, ok);

  // Kunci tombol: flag `busy` memblokir tap kedua walau DOM belum sempat ter-disable.
  const lock = () => { busy = true; btn.disabled = true; btn.setAttribute('aria-busy', 'true'); btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Memproses...'; };
  const unlock = () => { busy = false; btn.disabled = false; btn.removeAttribute('aria-busy'); btn.textContent = BTN_LABEL; };

  input.addEventListener('input', e => {
    clearTimeout(timer);
    timer = setTimeout(() => search(e.target.value.trim()), 400);
  });
  document.addEventListener('click', e => { if (!box.contains(e.target) && e.target !== input) box.classList.add('hidden'); });

  async function search(q) {
    if (q.length < 2) { box.classList.add('hidden'); return; }
    try {
      const res = await fetch(@json(route('peminjaman.search')) + '?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' } });
      const { items } = await res.json();
      box.innerHTML = '';
      if (!items.length) {
        box.innerHTML = '<div class="p-3 text-sm text-[var(--ink-faint)] text-center">Aset tidak ditemukan</div>';
      }
      items.forEach(it => {
        const info = [it.merk && 'Merk: ' + it.merk, it.kondisi && 'Kondisi: ' + it.kondisi, it.lokasi && 'Lokasi: ' + it.lokasi, it.sub_bidang && 'Sub bidang: ' + it.sub_bidang].filter(Boolean).join(' · ');
        const habis = it.jumlah_tersedia <= 0;
        const el = document.createElement('div');
        el.className = 'p-3 text-sm touch-manipulation ' + (habis ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer hover:bg-[var(--line)]/40 active:bg-[var(--line)]/70');
        el.innerHTML = `<div class="font-semibold">${esc(it.nama_barang)}</div>
          ${info ? `<div class="text-xs text-[var(--ink-faint)] mt-0.5">${esc(info)}</div>` : ''}
          <div class="text-[11px] font-medium mt-0.5 ${habis ? 'text-red-600' : 'text-[var(--accent)]'}">${habis ? 'Stok habis' : 'Tersedia: ' + it.jumlah_tersedia + ' unit'}</div>`;
        if (!habis) el.onclick = () => { addItem(it); box.classList.add('hidden'); input.value = ''; };
        box.appendChild(el);
      });
      box.classList.remove('hidden');
    } catch (e) { console.error(e); }
  }

  function addItem(it) {
    const ex = selected.find(s => s.id === it.id);
    if (ex) { if (ex.jumlah < it.jumlah_tersedia) ex.jumlah++; }
    else selected.push({ id: it.id, nama: it.nama_barang, merk: it.merk, kondisi: it.kondisi, tersedia: it.jumlah_tersedia, jumlah: 1 });
    render();
  }

  function render() {
    $('selectedBody').innerHTML = selected.map((s, i) => `
      <tr>
        <td class="py-3 text-sm"><span class="font-semibold">${esc(s.nama)}</span>
          <span class="block text-[11px] text-[var(--ink-faint)]">${esc([s.merk && 'Merk: ' + s.merk, s.kondisi && 'Kondisi: ' + s.kondisi].filter(Boolean).join(' · '))}</span></td>
        <td class="py-3 w-20 text-center"><input type="number" min="1" max="${s.tersedia}" value="${s.jumlah}" data-i="${i}" inputmode="numeric" class="qty w-16 text-center bg-transparent border border-[var(--line)] text-base sm:text-sm p-1.5 focus:outline-none focus:border-[var(--accent)]"></td>
        <td class="py-3 w-12 text-center"><button type="button" data-rm="${i}" aria-label="Hapus" class="w-10 h-10 inline-flex items-center justify-center text-lg text-[var(--ink-faint)] hover:text-red-600 active:scale-90 transition cursor-pointer touch-manipulation">&times;</button></td>
      </tr>`).join('');
    $('selectedCount').textContent = selected.length + ' item';
    $('selectedEmpty').style.display = selected.length ? 'none' : 'block';
  }
  $('selectedBody').addEventListener('change', e => {
    if (!e.target.classList.contains('qty')) return;
    const s = selected[+e.target.dataset.i];
    s.jumlah = Math.min(Math.max(parseInt(e.target.value) || 1, 1), s.tersedia);
    render();
  });
  $('selectedBody').addEventListener('click', e => {
    const rm = e.target.closest('[data-rm]');
    if (!rm) return;
    selected.splice(+rm.dataset.rm, 1);
    render();
  });

  form.addEventListener('submit', async e => {
    e.preventDefault();
    if (busy) return;
    if (!form.reportValidity()) return;
    if (!selected.length) return notify('Pilih minimal 1 barang inventaris.');
    if (!$('jaminanIdentitas').checked && !$('jaminanSurat').checked) return notify('Pilih minimal 1 jaminan.');

    const fd = new FormData(form);
    selected.forEach(s => { fd.append('barang_id[]', s.id); fd.append('barang_jumlah[]', s.jumlah); });

    lock();
    try {
      const res = await fetch(@json(route('peminjaman.submit')), { method: 'POST', headers: { Accept: 'application/json' }, body: fd });
      const out = await res.json();
      if (res.ok && out.success) {
        notify(out.message, true);
        setTimeout(() => { window.location.href = out.ringkasan_url; }, 700);
        return;   // tombol tetap terkunci sampai pindah halaman
      }
      const first = out.errors ? Object.values(out.errors)[0][0] : null;
      notify(first || out.message || 'Gagal memproses data.');
    } catch (err) {
      console.error(err);
      notify('Gagal terhubung dengan server.');
    }
    unlock();
  });

  render();
});
</script>
@endsection