<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'e-manu')</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Tailwind CSS (dihasilkan oleh Tailwind CLI) -->
    <link href="{{ asset('css/output.css') }}" rel="stylesheet">

    <!-- Font: satu keluarga, dipakai secara sengaja (bukan Inter default) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">

    <!--Font Arab-->
    <link
        href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Naskh+Arabic:wght@400;600&display=swap"
        rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Phosphor Icons (outline, satu keluarga ikon konsisten) -->
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">

    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!--Quill js untuk text editor-->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <link href="{{ asset('css/emanu.css') }}" rel="stylesheet">
    @yield('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="min-h-screen flex flex-col antialiased">

    <nav class="border-b border-[var(--line)] bg-[var(--paper)]">
        <div class="max-w-5xl mx-auto px-5 md:px-6 h-16 flex items-center justify-between">

            <a href="/" class="flex items-center gap-2.5 group select-none touch-manipulation">
                <span
                    class="font-bold tracking-tight text-[15px] text-[var(--ink)] transition-colors group-hover:text-[var(--accent)]">e-manu</span>
                <span class="text-[var(--ink-faint)] hidden sm:inline">/</span>
                <span class="mono text-[11px] uppercase tracking-widest text-[var(--ink-soft)] hidden sm:inline">Digital
                    Repository</span>
            </a>

            @if (empty($noAuth) && auth()->check())
            <div class="relative" id="userDropdownContainer">
                <button id="dropdownBtn" type="button"
                    class="flex items-center gap-1.5 pl-2.5 pr-3 py-2 rounded-full border text-sm font-medium hover:bg-black/[.03] transition-colors focus:outline-none cursor-pointer"
                    style="border-color:var(--line);">
                    <div id="avatarIcon" class="flex items-center justify-center shrink-0">
                        <i class="ph ph-user text-sm"></i>
                    </div>
                    <span class="max-w-[90px] md:max-w-[120px] truncate">{{ auth()->user()->username }}</span>
                    <i id="chevronIcon"
                        class="ph ph-caret-down text-xs opacity-50 transition-transform duration-150 ml-0.5"></i>
                </button>

                <div id="dropdownMenu" data-state="closed"
                    class="menu-panel hidden absolute right-0 mt-2 w-56 rounded-xl border bg-white shadow-lg z-50 overflow-hidden"
                    style="border-color:var(--line);">

                    <div class="px-4 py-3 border-b" style="border-color:var(--line);">
                        <p class="mono text-[10px] uppercase tracking-wider opacity-50">Akun terhubung</p>
                        <p class="text-sm font-semibold truncate mt-0.5">{{ auth()->user()->username }}</p>
                    </div>

                    <div class="py-1">
                        <a href="{{ config('emanu.base_url') }}" target="_blank" rel="noopener noreferrer"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-black/[.03] transition-colors">
                            <i class="ph ph-squares-four text-base opacity-60"></i>
                            <span>e-manu</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors cursor-pointer">
                                <i class="ph ph-sign-out text-base"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </nav>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t mt-auto" style="border-color:var(--line); background:var(--ink);">
        <div
            class="max-w-5xl mx-auto px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-white">
            <span class="text-xs opacity-70 mono">made with 🫰</span>
            <div class="flex items-center gap-4">
                <a href="https://github.com/hanifamar17/peminjaman-barang" target="_blank"
                    class="flex items-center gap-1.5 text-xs opacity-80 hover:opacity-100 transition-opacity">
                    <i class="ph ph-github-logo text-sm"></i>
                    GitHub
                </a>
                <span class="text-xs opacity-50 mono">&copy; <span id="current-year"></span></span>
                <span class="text-xs opacity-70 mono">v2.0.0</span>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById("current-year").textContent = new Date().getFullYear();

        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('dropdownBtn');
            const menu = document.getElementById('dropdownMenu');
            const container = document.getElementById('userDropdownContainer');
            const chevron = document.getElementById('chevronIcon');

            if (!btn || !menu) return;

            const setOpen = (open) => {
                menu.classList.toggle('hidden', !open);
                menu.dataset.state = open ? 'open' : 'closed';
                chevron.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
            };

            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                setOpen(menu.classList.contains('hidden'));
            });

            document.addEventListener('click', (e) => {
                if (!menu.classList.contains('hidden') && !container.contains(e.target)) setOpen(false);
            });

            menu.addEventListener('click', (e) => e.stopPropagation());
        });
    </script>
    @yield('scripts')
</body>

</html>