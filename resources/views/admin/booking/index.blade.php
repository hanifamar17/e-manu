@extends('layouts.app')
@section('title', 'Admin: Pengajuan Booking')

@section('content')
<div class="max-w-5xl mx-auto px-5 py-9 sm:py-14 text-[var(--ink)]">
  <a href="{{ route('booking_ruangan.booking_landing') }}" class="back-link inline-flex items-center gap-2 text-sm font-semibold text-[var(--ink-soft)] hover:text-[var(--ink)]">
    <i class="ph ph-arrow-left"></i> Kembali
  </a>

  <div class="flex flex-wrap items-end justify-between gap-3 mt-8 mb-6">
    <h1 class="page-title text-2xl font-bold m-0">Pengajuan Booking Lab</h1>
    <div class="flex gap-3 text-sm">
      <a class="underline" href="{{ route('admin.kalender.index') }}">Kalender akademik</a>
      <a class="underline" href="{{ route('admin.impor.form') }}">Impor CSV</a>
    </div>
  </div>

  @include('partials.flash')

  <div class="flex gap-2 mb-6 mono text-xs font-bold uppercase tracking-widest">
    @foreach (['pending' => 'Menunggu ('.$jumlahPending.')', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'] as $k => $label)
      <a href="{{ route('admin.booking.index', ['status' => $k]) }}"
        class="px-3 py-1.5 border border-[var(--line)] {{ $status === $k ? 'bg-[var(--ink)] text-[var(--paper)]' : 'text-[var(--ink-soft)]' }}">{{ $label }}</a>
    @endforeach
  </div>

  <div class="space-y-3">
    @forelse ($items as $b)
      <div class="card-outline p-4 border border-[var(--line)]">
        <div class="flex flex-wrap justify-between gap-2">
          <div>
            <p class="font-bold text-[15px] m-0">{{ $b->nama_kegiatan }}</p>
            <p class="mono text-[11px] uppercase tracking-wider text-[var(--ink-soft)] mt-1 m-0">
              {{ $b->nama_pemesan }} &middot; {{ $b->email }} &middot; {{ $b->eksternal ? 'EKSTERNAL' : 'INTERNAL' }}
            </p>
          </div>
          <div class="mono text-[11px] font-bold border border-[var(--line)] px-2.5 py-1.5 h-fit">
            {{ $b->tanggal->translatedFormat('D, j M Y') }} &middot; {{ $b->jam }}
          </div>
        </div>
        @if ($b->catatan_tambahan)
          <p class="text-[12px] text-[var(--ink-soft)] mt-2 m-0">{{ $b->catatan_tambahan }}</p>
        @endif
        @if ($b->status === 'ditolak' && $b->alasan_penolakan)
          <p class="text-[12px] mt-2 m-0">Alasan: {{ $b->alasan_penolakan }}</p>
        @endif
        @if ($b->ada_bentrok ?? false)
          <p class="text-[12px] font-bold text-red-600 mt-2 m-0">Bentrok dengan booking yang sudah disetujui.</p>
        @endif

        <div class="flex flex-wrap items-start gap-3 mt-3">
          @if ($b->status === 'pending')
            <form method="POST" action="{{ route('admin.booking.setujui', $b) }}">
              @csrf
              <button class="px-3 py-1.5 text-xs font-semibold bg-[var(--ink)] text-[var(--paper)]" @disabled($b->ada_bentrok ?? false)>Setujui</button>
            </form>
            <form method="POST" action="{{ route('admin.booking.tolak', $b) }}" class="flex gap-2 flex-1 min-w-[220px]">
              @csrf
              <input name="alasan_penolakan" required maxlength="500" placeholder="Alasan penolakan"
                class="flex-1 text-xs bg-transparent border-0 border-b border-[var(--line)] focus:outline-none focus:ring-0">
              <button class="px-3 py-1.5 text-xs font-semibold border border-[var(--line)]">Tolak</button>
            </form>
          @endif
          <form method="POST" action="{{ route('admin.booking.destroy', $b) }}" onsubmit="return confirm('Hapus pengajuan ini?')">
            @csrf @method('DELETE')
            <button class="px-3 py-1.5 text-xs text-red-600">Hapus</button>
          </form>
        </div>
      </div>
    @empty
      <div class="card-outline py-12 text-center mono text-[11px] font-bold uppercase tracking-widest text-[var(--ink-faint)]">— KOSONG —</div>
    @endforelse
  </div>

  <div class="mt-6">{{ $items->links() }}</div>
</div>
@endsection
