@php
    $tab = 'shrink-0 inline-flex items-center mono text-[11px] font-bold uppercase tracking-widest px-4 py-2 border border-[var(--line)]';
    $on = 'bg-[var(--ink)] text-[var(--paper)]';
    $off = 'text-[var(--ink-soft)]';
    $active = $active ?? 'semua';   // 'semua' | slug bidang | '__pinjam'
    $q = $q ?? '';
@endphp
<div class="flex gap-2 mb-6 overflow-x-auto pb-1">
  <a href="{{ route('inventaris.index', array_filter(['q' => $q])) }}" class="{{ $tab }} {{ $active === 'semua' ? $on : $off }}">Semua</a>
  @foreach (\App\Models\Inventaris::BIDANG_LABEL as $slug => $label)
    <a href="{{ route('inventaris.index', array_filter(['bidang' => $slug, 'q' => $q])) }}" class="{{ $tab }} {{ $active === $slug ? $on : $off }}">{{ $label }}</a>
  @endforeach
  <a href="{{ route('barang_pinjaman.index', array_filter(['q' => $q])) }}" class="{{ $tab }} {{ $active === '__pinjam' ? $on : $off }}">Barang Pinjaman</a>
</div>
