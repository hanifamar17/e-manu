@php
    $label = ['menunggu_acc' => 'Menunggu ACC', 'aktif' => 'Aktif', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'dibatalkan' => 'Dibatalkan'][$s] ?? $s;
    $cls = in_array($s, ['aktif', 'disetujui'], true)
        ? 'bg-[var(--ink)] text-[var(--paper)] border-[var(--ink)]'
        : ($s === 'menunggu_acc' ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-[var(--line)] text-[var(--ink-faint)] line-through');
@endphp
<span class="mono text-[10px] font-bold uppercase tracking-widest px-2 py-1 border {{ $cls }}">{{ $label }}</span>
