@extends('layouts.app')
@section('title', 'Izin '.$item->id_perizinan)

@section('content')
@php
    $inp = 'block w-full py-2.5 px-0 text-base sm:text-sm bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0 focus:border-[var(--accent)] rounded-none text-[var(--ink)]';
    $lbl = 'eyebrow mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-soft)] mb-1 block';
    $err = 'text-xs text-red-600 mt-1 m-0';
    $role = auth()->user()->role;
    $bisaAcc = $item->status === \App\Models\Perizinan::MENUNGGU && in_array($role, ['wali_kelas', 'admin'], true);
    $bisaBatal = $item->bisaDibatalkanOleh($role);
    $row = fn ($k, $v, $wide = false) => '<div class="py-2.5 border-b border-[var(--line)]'.($wide ? ' sm:col-span-2 lg:col-span-3' : '').'"><p class="'.$lbl.' m-0">'.e($k).'</p><p class="text-sm m-0 mt-0.5">'.e($v ?: '—').'</p></div>';
@endphp
<div class="max-w-4xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a data-lock href="{{ route('perizinan.riwayat') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)] cursor-pointer touch-manipulation select-none transition active:scale-95">
    <i class="ph ph-arrow-left"></i> Riwayat
  </a>
  <div class="flex flex-wrap items-center justify-between gap-3 mt-8 mb-6">
    <h1 class="page-title text-2xl font-bold m-0">{{ $item->ringkas() }}</h1>
    @include('partials.pz-badge', ['s' => $item->status])
  </div>

  @include('partials.flash')

  @if ($item->status === \App\Models\Perizinan::MENUNGGU && in_array($role, ['guru', 'admin'], true))
    <div class="border-2 border-[var(--ink)] p-5 mb-8">
      <p class="{{ $lbl }}">Langkah berikutnya</p>
      <p class="text-base font-bold m-0 mt-1">Kirim tautan ACC ke wali kelas</p>
      <p class="text-sm text-[var(--ink-soft)] m-0 mt-2">
        Izin ini belum aktif. Wali kelas {{ $item->wali_kelas_tujuan ?: '(belum ada di data kelas)' }} perlu membuka tautan ACC dari WhatsApp, lalu menyetujui atau menolak.
      </p>
      @if ($hpWali ?? null)
        <p class="mono text-[11px] text-[var(--ink-soft)] m-0 mt-2">Tujuan: +{{ $hpWali }}</p>
      @else
        <p class="text-sm text-red-600 m-0 mt-2">No HP wali kelas belum terisi di menu Kelas &amp; Wali Kelas (admin). Pesan tetap bisa dikirim: pilih kontak wali kelas sendiri di WhatsApp. Tapi ACC lewat tautan baru bisa dipakai setelah no HP terisi.</p>
      @endif
      <div class="flex flex-wrap gap-2 mt-4">
        <a href="{{ $waUrl }}" target="_blank" rel="noopener"
           class="px-5 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] no-underline">Kirim lewat WhatsApp</a>
        <button type="button" id="salinAcc" data-url="{{ $accUrl }}"
           class="px-5 py-2.5 text-sm font-semibold bg-white border border-[var(--line)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.97]">Salin tautan ACC</button>
      </div>
      <p class="text-[11px] text-[var(--ink-soft)] m-0 mt-3">Wali kelas akan diminta 4 digit terakhir no HP-nya sebelum bisa ACC.</p>
    </div>
  @endif

  <div class="mb-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8">
    {!! $row('ID izin', $item->id_perizinan) !!}
    {!! $row('Jumlah siswa', $item->siswa->count().' siswa · '.$item->daftarKelas()) !!}
    {!! $row('Wali kelas', $item->wali_kelas_tujuan) !!}
    {!! $row('Mulai', $item->tanggal_mulai->translatedFormat('l, j F Y').' · '.substr($item->jam_mulai, 0, 5)) !!}
    {!! $row('Selesai', $item->tanggal_selesai->translatedFormat('l, j F Y').' · '.substr($item->jam_selesai, 0, 5)) !!}
    {!! $row('Durasi', $item->durasi) !!}
    {!! $row('Alasan', $item->alasan, true) !!}
    {!! $row('Petugas', $item->petugas.' ('.str_replace('_', ' ', $item->status_petugas).')') !!}
    {!! $row('Diinput', $item->need_acc ? 'Dengan ACC wali kelas' : 'Langsung aktif (tanpa ACC)') !!}
    @if ($item->diproses_oleh)
      {!! $row('Diproses oleh', $item->diproses_oleh.' · '.$item->tanggal_acc?->translatedFormat('j M Y H:i')) !!}
      {!! $row('Catatan wali kelas', $item->catatan_wali_kelas, true) !!}
    @endif
    @if ($item->status === \App\Models\Perizinan::DIBATALKAN)
      {!! $row('Dibatalkan oleh', $item->dibatalkan_oleh.' · '.$item->dibatalkan_at?->translatedFormat('j M Y H:i')) !!}
      {!! $row('Alasan batal', $item->alasan_batal, true) !!}
    @endif
  </div>

  <div class="mb-8">
    <p class="{{ $lbl }}">Daftar siswa ({{ $item->siswa->count() }})</p>
    <div class="border border-[var(--line)] max-h-96 overflow-auto">
      <table class="w-full text-sm">
        @foreach ($item->siswaUrut() as $i => $s)
          <tr class="border-b border-[var(--line)]">
            <td class="py-2 px-3 w-10 mono text-[11px] text-[var(--ink-soft)]">{{ $i + 1 }}</td>
            <td class="py-2 font-semibold">{{ $s->nama_siswa }}</td>
            <td class="py-2 px-3 mono text-[11px] text-[var(--ink-soft)] text-right">{{ $s->nisn }} · {{ $s->kelas_umum }}</td>
          </tr>
        @endforeach
      </table>
    </div>
  </div>

  @if (in_array($item->status, ['aktif', 'disetujui'], true))
    <div class="flex flex-wrap gap-2 mb-8">
      <a href="{{ route('perizinan.surat', $item->uuid) }}" target="_blank" rel="noopener"
         class="px-4 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97] no-underline">Buka surat izin</a>
      <button type="button" id="salin" data-url="{{ route('perizinan.surat', $item->uuid) }}"
         class="px-4 py-2.5 text-sm font-semibold bg-white border border-[var(--line)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] hover:border-[var(--ink)] active:scale-[0.97]">Salin link surat</button>
    </div>
  @endif

  @if ($bisaAcc)
    <div class="border border-[var(--line)] p-4 mb-6">
      <p class="{{ $lbl }}">Proses ACC</p>
      <form data-lock method="POST" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5" id="formAcc">
        @csrf
        @if ($item->wali_kelas_tujuan)
        <div>
          <p class="{{ $lbl }}">Diproses oleh (wali kelas)</p>
          <p class="text-sm font-semibold m-0 py-2.5 border-b border-[var(--line)]">{{ $item->wali_kelas_tujuan }}</p>
        </div>
        @else
        <div>
          <label class="{{ $lbl }}" for="diproses_oleh">Nama pemroses (wali kelas)</label>
          <select class="{{ $inp }} cursor-pointer" id="diproses_oleh" name="diproses_oleh" required>
            <option value="">— pilih nama —</option>
            @foreach ($walis as $w)
              <option value="{{ $w }}" @selected(old('diproses_oleh', $item->wali_kelas_tujuan) === $w)>{{ $w }}</option>
            @endforeach
          </select>
          @error('diproses_oleh')<p class="{{ $err }}">{{ $message }}</p>@enderror
        </div>
        @endif
        <div>
          <label class="{{ $lbl }}" for="catatan_wali_kelas">Catatan (wajib jika ditolak)</label>
          <input class="{{ $inp }}" id="catatan_wali_kelas" name="catatan_wali_kelas" maxlength="200" value="{{ old('catatan_wali_kelas') }}">
          @error('catatan_wali_kelas')<p class="{{ $err }}">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-wrap gap-2 sm:col-span-2">
          <button type="submit" formaction="{{ route('perizinan.setujui', $item) }}" class="px-6 py-2.5 text-sm font-semibold bg-[var(--ink)] text-[var(--paper)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--accent)] active:scale-[0.97]">Setujui</button>
          <button type="submit" formaction="{{ route('perizinan.tolak', $item) }}" class="px-6 py-2.5 text-sm font-semibold bg-white border border-[var(--line)] text-red-600 cursor-pointer touch-manipulation select-none transition hover:bg-red-600 hover:text-white hover:border-red-600 active:scale-[0.97]">Tolak</button>
        </div>
      </form>
    </div>
  @endif

  @if ($bisaBatal)
    <button type="button" id="bukaBatal" class="px-4 py-2.5 text-sm font-semibold bg-white border border-[var(--line)] text-red-600 cursor-pointer touch-manipulation select-none transition hover:bg-red-600 hover:text-white hover:border-red-600 active:scale-[0.97]">Batalkan izin</button>

    <dialog id="dlgBatal" class="p-0 border border-[var(--line)] bg-[var(--paper)] text-[var(--ink)] w-[92vw] max-w-md">
      <form data-lock method="POST" action="{{ route('perizinan.batal', $item) }}" class="p-5 space-y-5">
        @csrf
        <h2 class="text-lg font-bold m-0">Batalkan izin {{ $item->ringkas() }}?</h2>
        @if ($item->siswa->count() > 1)
          <p class="text-xs text-[var(--ink-soft)] m-0">Seluruh {{ $item->siswa->count() }} siswa di izin ini ikut dibatalkan.</p>
        @endif
        @if ($item->status !== \App\Models\Perizinan::MENUNGGU)
          <p class="text-xs text-[var(--ink-soft)] m-0">Izin sudah {{ $item->status }}; hanya admin yang bisa membatalkan.</p>
        @endif
        <div>
          <label class="{{ $lbl }}" for="dibatalkan_oleh">Nama pembatal</label>
          <input class="{{ $inp }}" id="dibatalkan_oleh" name="dibatalkan_oleh" maxlength="100" required value="{{ old('dibatalkan_oleh') }}">
          @error('dibatalkan_oleh')<p class="{{ $err }}">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="{{ $lbl }}" for="alasan_batal">Alasan pembatalan</label>
          <input class="{{ $inp }}" id="alasan_batal" name="alasan_batal" maxlength="200" required value="{{ old('alasan_batal') }}">
          @error('alasan_batal')<p class="{{ $err }}">{{ $message }}</p>@enderror
        </div>
        <div class="flex gap-2 justify-end">
          <button type="button" id="tutupBatal" class="px-4 py-2.5 text-sm font-semibold bg-white border border-[var(--line)] cursor-pointer touch-manipulation select-none transition hover:bg-[var(--ink)] hover:text-[var(--paper)] active:scale-[0.97]">Tidak</button>
          <button type="submit" class="px-4 py-2.5 text-sm font-semibold bg-red-600 text-white cursor-pointer touch-manipulation select-none transition hover:bg-red-700 active:scale-[0.97]">Ya, batalkan</button>
        </div>
      </form>
    </dialog>
  @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const dlg = document.getElementById('dlgBatal');
  if (dlg) {
    document.getElementById('bukaBatal').addEventListener('click', () => dlg.showModal());
    document.getElementById('tutupBatal').addEventListener('click', () => dlg.close());
    dlg.addEventListener('click', e => { if (e.target === dlg) dlg.close(); });
    @if ($errors->has('dibatalkan_oleh') || $errors->has('alasan_batal')) dlg.showModal(); @endif
  }
  const salinAcc = document.getElementById('salinAcc');
  if (salinAcc) salinAcc.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(salinAcc.dataset.url); salinAcc.textContent = 'Tautan tersalin'; }
    catch (e) { window.prompt('Salin tautan:', salinAcc.dataset.url); }
    setTimeout(() => salinAcc.textContent = 'Salin tautan ACC', 2000);
  });
  const salin = document.getElementById('salin');
  if (salin) salin.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(salin.dataset.url); salin.textContent = 'Link tersalin'; }
    catch (e) { window.prompt('Salin link:', salin.dataset.url); }
    setTimeout(() => salin.textContent = 'Salin link surat', 2000);
  });
});
</script>
@include('partials.lock')
@endsection
