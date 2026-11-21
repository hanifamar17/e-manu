<a href="{{ $href }}" class="group flex items-center gap-4 py-5 border-b border-[var(--line)] no-underline text-inherit transition-all duration-150 hover:pl-1.5 active:scale-[0.99]">
  <span class="mono tabular-nums text-[15px] w-6 shrink-0 text-[var(--ink-faint)] group-hover:text-[var(--accent)]">{{ $no }}</span>
  <div class="flex-1 min-w-0">
    <h3 class="text-[15px] font-semibold m-0 group-hover:text-[var(--accent)]">{{ $judul }}</h3>
    <p class="text-[13px] mt-0.5 m-0 text-[var(--ink-faint)]">{{ $desc }}</p>
  </div>
  <i class="ph ph-arrow-right text-[var(--ink-faint)] group-hover:text-[var(--accent)]"></i>
</a>