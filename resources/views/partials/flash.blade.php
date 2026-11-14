@if (session('status'))
    <div class="mb-6 border-l-2 border-[var(--accent)] bg-[var(--accent-soft)] px-4 py-3 text-sm text-[var(--ink)]">{{ session('status') }}</div>
@endif
@if (session('warning'))
    <div class="mb-6 border-l-2 border-[#D4A373] px-4 py-3 text-sm text-[var(--ink)]">{{ session('warning') }}</div>
@endif
@if ($errors->any())
    <div class="mb-6 border-l-2 border-red-600 px-4 py-3 text-sm text-[var(--ink)]">
        <ul class="m-0 pl-4 list-disc">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if (session('import_errors'))
    <div class="mb-6 border-l-2 border-red-600 px-4 py-3 text-sm text-[var(--ink)]">
        <p class="font-bold m-0 mb-1">Impor dibatalkan, tidak ada data ditulis:</p>
        <ul class="m-0 pl-4 list-disc mono text-xs">
            @foreach (session('import_errors') as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif
