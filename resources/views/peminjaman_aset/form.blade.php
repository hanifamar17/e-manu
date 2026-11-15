@extends('layouts.app')
@section('title', 'Form Peminjaman Aset')

@section('content')
@php
  $lbl = 'block text-[11px] font-bold uppercase tracking-wider text-[#a49b8c] mb-1.5';
  $inp = 'w-full py-2.5 bg-transparent border-0 border-b border-[#e6e0d6] text-sm focus:outline-none focus:border-[#1f1c19]';
@endphp
<div class="max-w-3xl mx-auto px-5 pt-12 pb-20 bg-[#faf8f5] text-[#1f1c19]">
  <a href="{{ route('peminjaman.landing') }}" class="text-sm font-semibold text-[#a49b8c] hover:text-[#1f1c19]">&larr; Kembali</a>
  <h1 class="text-2xl font-bold tracking-tight mt-8">Formulir Peminjaman Aset</h1>
  <p class="text-sm text-[#6b6459] mt-2 mb-10 max-w-[52ch]">Pilih inventaris, lengkapi data peminjam, lalu kirim. Ringkasan bisa dicek setelah dikirim.</p>

  <form id="borrowForm" class="space-y-14" novalidate>
    @csrf

    <section>
      <h2 class="text-[13px] font-bold uppercase tracking-wide border-b border-[#e6e0d6] pb-2 mb-6">01 · Pilih Inventaris</h2>
      <div class="relative mb-6">
        <input type="text" id="searchItem" placeholder="Cari barang, merk, atau lokasi..." class="{{ $inp }}" autocomplete="off">
        <div id="searchResult" class="hidden absolute z-30 w-full mt-2 bg-white border border-[#e6e0d6] shadow-sm p-1"></div>
      </div>
      <div class="flex justify-between mb-2">
        <span class="text-[11px] font-bold uppercase tracking-wider text-[#a49b8c]">Aset dipilih</span>
        <span id="selectedCount" class="text-[11px] font-bold text-[#a8501f]">0 item</span>
      </div>
      <table class="w-full text-left text-sm border-t border-[#e6e0d6]"><tbody id="selectedBody" class="divide-y divide-[#e6e0d6]"></tbody></table>
      <p id="selectedEmpty" class="py-6 text-center text-[13px] text-[#a49b8c] border-b border-[#e6e0d6]">Belum ada barang dipilih.</p>
    </section>

    <section>
      <h2 class="text-[13px] font-bold uppercase tracking-wide border-b border-[#e6e0d6] pb-2 mb-6">02 · Data Peminjam</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
        <div><label class="{{ $lbl }}">Nama Lengkap *</label><input name="nama" class="{{ $inp }}" placeholder="Sesuai identitas" required></div>
        <div><label class="{{ $lbl }}">Jenis Kelamin</label>
          <select name="jenis_kelamin" class="{{ $inp }}"><option>Laki-laki</option><option>Perempuan</option></select></div>
        <div><label class="{{ $lbl }}">Pekerjaan / Instansi *</label><input name="pekerjaan" class="{{ $inp }}" placeholder="Contoh: Guru, Mahasiswa" required></div>
        <div><label class="{{ $lbl }}">No. Telepon / WA *</label><input name="telepon" class="{{ $inp }}" placeholder="08xx-xxxx-xxxx" required></div>
        <div class="sm:col-span-2"><label class="{{ $lbl }}">Alamat *</label><input name="alamat" class="{{ $inp }}" placeholder="Alamat domisili" required></div>
        <div class="sm:col-span-2"><label class="{{ $lbl }}">No. Identitas (KTP/SIM) *</label><input name="no_identitas" class="{{ $inp }}" placeholder="Nomor sesuai identitas" required></div>
      </div>
    </section>

    <section>
      <h2 class="text-[13px] font-bold uppercase tracking-wide border-b border-[#e6e0d6] pb-2 mb-6">03 · Detail Peminjaman</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
        <div><label class="{{ $lbl }}">Tujuan *</label><input name="tujuan" class="{{ $inp }}" placeholder="Contoh: Praktikum, dokumentasi" required></div>
        <div><label class="{{ $lbl }}">Lokasi Penggunaan *</label><input name="lokasi_penggunaan" class="{{ $inp }}" placeholder="Contoh: Lab Komputer" required></div>
        <div><label class="{{ $lbl }}">Tanggal Pinjam *</label><input type="date" name="tgl_pinjam" class="{{ $inp }}" required></div>
        <div><label class="{{ $lbl }}">Tanggal Kembali *</label><input type="date" name="tgl_kembali" class="{{ $inp }}" required></div>
        <div><label class="{{ $lbl }}">Kota *</label><input name="kota" value="Yogyakarta" class="{{ $inp }}" required></div>
        <div><label class="{{ $lbl }}">Tanggal Surat *</label><input type="date" name="tgl_surat" value="{{ now()->toDateString() }}" class="{{ $inp }}" required></div>
        <div class="sm:col-span-2"><label class="{{ $lbl }}">Nama Petugas *</label><input name="nama_petugas" class="{{ $inp }}" placeholder="Petugas yang menyerahkan barang" required></div>
      </div>
    </section>

    <section>
      <h2 class="text-[13px] font-bold uppercase tracking-wide border-b border-[#e6e0d6] pb-2 mb-6">04 · Jaminan</h2>
      <div class="flex flex-wrap items-center gap-x-8 gap-y-4">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="jaminan_identitas" id="jaminanIdentitas"> Identitas</label>
        <select name="jenis_identitas_jaminan" class="{{ $inp }} w-auto min-w-[100px]"><option>KTP</option><option>SIM</option></select>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="jaminan_surat" id="jaminanSurat"> Surat Resmi</label>
      </div>
    </section>

    <div>
      <div id="status" class="hidden text-sm font-medium p-3 mb-4 border"></div>
      <button type="submit" id="btnSubmit" class="w-full py-3.5 bg-[#1f1c19] text-[#faf8f5] text-xs font-bold uppercase tracking-widest hover:bg-[#a8501f] disabled:opacity-60 disabled:cursor-not-allowed">Kirim Peminjaman</button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const $ = id => document.getElementById(id);
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const form = $('borrowForm'), box = $('searchResult'), input = $('searchItem');
  let selected = [], timer;

  const notify = (msg, ok = false) => {
    const s = $('status');
    s.textContent = msg;
    s.className = 'text-sm font-medium p-3 mb-4 border ' + (ok
      ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
      : 'bg-rose-50 text-rose-800 border-rose-200');
  };

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
        box.innerHTML = '<div class="p-3 text-sm text-[#a49b8c] text-center">Aset tidak ditemukan</div>';
      }
      items.forEach(it => {
        const info = [it.merk && 'Merk: ' + it.merk, it.kondisi && 'Kondisi: ' + it.kondisi, it.lokasi && 'Lokasi: ' + it.lokasi, it.sub_bidang && 'Sub bidang: ' + it.sub_bidang].filter(Boolean).join(' · ');
        const habis = it.jumlah_tersedia <= 0;
        const el = document.createElement('div');
        el.className = 'p-3 text-sm ' + (habis ? 'opacity-60 cursor-not-allowed bg-gray-50' : 'cursor-pointer hover:bg-[#f3f0ea]');
        el.innerHTML = `<div class="font-semibold">${esc(it.nama_barang)}</div>
          ${info ? `<div class="text-xs text-[#a49b8c] mt-0.5">${esc(info)}</div>` : ''}
          <div class="text-[11px] font-medium mt-0.5 ${habis ? 'text-red-500' : 'text-emerald-600'}">${habis ? 'Stok habis' : 'Tersedia: ' + it.jumlah_tersedia + ' unit'}</div>`;
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
          <span class="block text-[11px] text-[#a49b8c]">${esc([s.merk && 'Merk: ' + s.merk, s.kondisi && 'Kondisi: ' + s.kondisi].filter(Boolean).join(' · '))}</span></td>
        <td class="py-3 w-20 text-center"><input type="number" min="1" max="${s.tersedia}" value="${s.jumlah}" data-i="${i}" class="qty w-16 text-center border border-[#e6e0d6] text-xs p-1"></td>
        <td class="py-3 w-10 text-center"><button type="button" data-rm="${i}" class="text-[#a49b8c] hover:text-red-500">&times;</button></td>
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
    if (e.target.dataset.rm === undefined) return;
    selected.splice(+e.target.dataset.rm, 1);
    render();
  });

  form.addEventListener('submit', async e => {
    e.preventDefault();
    if (!form.reportValidity()) return;
    if (!selected.length) return notify('Pilih minimal 1 barang inventaris.');
    if (!$('jaminanIdentitas').checked && !$('jaminanSurat').checked) return notify('Pilih minimal 1 jaminan.');

    const fd = new FormData(form);
    selected.forEach(s => { fd.append('barang_id[]', s.id); fd.append('barang_jumlah[]', s.jumlah); });

    const btn = $('btnSubmit');
    btn.disabled = true; btn.textContent = 'Memproses...';
    try {
      const res = await fetch(@json(route('peminjaman.submit')), { method: 'POST', headers: { Accept: 'application/json' }, body: fd });
      const out = await res.json();
      if (res.ok && out.success) {
        notify(out.message, true);
        window.location.href = out.ringkasan_url;
        return;
      }
      const first = out.errors ? Object.values(out.errors)[0][0] : null;
      notify(first || out.message || 'Gagal memproses data.');
    } catch (err) {
      console.error(err);
      notify('Gagal terhubung dengan server.');
    }
    btn.disabled = false; btn.textContent = 'Kirim Peminjaman';
  });

  render();
});
</script>
@endsection
