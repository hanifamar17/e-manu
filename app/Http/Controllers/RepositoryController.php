<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class RepositoryController extends Controller
{
    public function index(): View
    {
        return view('repository', [
            'menu' => $this->menu(),
            'pengumuman' => Pengumuman::orderByDesc('updated_at')->limit(10)->get(),
        ]);
    }

    // Link ber-route yang belum dibuat otomatis tampil terkunci (disabled).
    private function link(string $title, string $subtext, ?string $route = null, ?string $url = null, bool $disabled = false): array
    {
        if ($route !== null) {
            if (Route::has($route)) {
                $url = route($route);
            } else {
                $url = '#';
                $disabled = true;
            }
        }

        return ['title' => $title, 'subtext' => $subtext, 'url' => $url ?? '#', 'disabled' => $disabled];
    }

    private function menu(): array
    {
        return [
            [
                'category' => 'Home',
                'color' => 'slate',
                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
                'links' => [
                ],
            ],
            [
                'category' => 'Akademik',
                'color' => 'blue',
                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>',
                'links' => [
                    $this->link('Kalender akademik', 'Dokumen kalender akademik', url: route('booking_ruangan.jadwal_penggunaan', ['dari' => 'repository'])),
                    $this->link('Jadwal KBM', 'Jadwal kegiatan belajar mengajar (KBM)', route: 'jadwal_pelajaran.jadwal_pelajaran_landing'),
                    $this->link('Poin Disiplin', 'Formulir pencatatan poin disiplin', url: 'https://script.google.com/macros/s/AKfycbzQtB0i7iIKvb6tEuvHzTVUqt1K5JXRRIRphtKZOAGLHRjmI5Cr9kMTN2ylM5PVmOWZ/exec'),
                    $this->link('RDM', 'Rapor Digital Madrasah', route: 'rdm'),
                ],
            ],
            [
                'category' => 'Sekolah',
                'color' => 'emerald',
                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>',
                'links' => [
                    $this->link('Presensi Sekolah', 'Formulir presensi kegiatan sekolah', url: 'https://presensi-sekolah.manu.sch.id/', disabled: false),
                    $this->link('Presensi Diniyah', 'Formulir presensi kegiatan kelas diniyyah', url: 'https://script.google.com/macros/s/AKfycbydodNzn3SzN3ekC7jv17PZgoV-7GktfQn3cFyxyEXcFcz1Zv-U5ab3sGBwHxnYojOg/exec'),
                    $this->link('Jurnal Mapel', 'Formulir jurnal kegiatan belajar mengajar mata pelajaran', url: 'https://docs.google.com/forms/d/e/1FAIpQLSdGZ7jXITewg7GQM7zoSLmPEUglqbMj3TwGFGWZ46HuGSCotg/viewform'),
                    $this->link('Supervisi', 'Coming Soon', url: '#', disabled: true),
                ],
            ],
            [
                'category' => 'Ekstrakurikuler',
                'color' => 'amber',
                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22a3.5 3.5 0 01-3.5-3.5c0-2 1.5-3.5 3.5-5.5 2 2 3.5 3.5 3.5 5.5A3.5 3.5 0 0112 22zM12 13V2M12 2c-2.5 2.5-6 3.5-8 3.5M12 2c2.5 2.5 6 3.5 8 3.5M12 6.5c-2 2-4 2.5-6 2.5M12 6.5c2 2 4 2.5 6 2.5"/></svg>',
                'links' => [
                    $this->link('Presensi Ekstrakurikuler', 'Formulir presensi kegiatan ekstrakurikuler', url: 'https://script.google.com/macros/s/AKfycbxiuxLz2L7ob5jAG9KUKwT2-uXDtzhGG9VCuBfky6-rLp8cHsv9tG3OW3SVck8ny8dh/exec'),
                    $this->link('Jurnal Ekstrakurikuler', 'Formulir jurnal kegiatan ekstrakurikuler', url: 'https://docs.google.com/forms/d/e/1FAIpQLSfu0TYIpe67BVyhjXyaSdkPj5ZdvnynCEB2TXrNnMjuSdqxJQ/viewform'),
                ],
            ],
            [
                'category' => 'Asrama',
                'color' => 'violet',
                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>',
                'links' => [
                    $this->link('Presensi kegiatan Asrama', 'Formulir presensi kegiatan asrama', url: 'https://presensimanu.my.id/login'),
                    $this->link('Berita Acara Persidangan Santri', 'Formulir berita acara persidangan santri', url: 'https://docs.google.com/forms/d/e/1FAIpQLSc5OG8Kf879_mOgmQNxIRuTfH3beYR_DZ54waDaDjWudECLtQ/viewform'),
                    $this->link('Formulir & Jurnal Perizinan', 'Formulir dan jurnal perizinan siswa', route: 'perizinan.index'),
                ],
            ],
            [
                'category' => 'Sarpras',
                'color' => 'rose',
                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>',
                'links' => [
                    $this->link('Peminjaman Aset', 'Pengelolaan peminjaman aset madrasah', route: 'peminjaman.landing'),
                    $this->link('Database sarpras', 'Pengelolaan database sarana dan prasarana', route: 'inventaris.index'),
                ],
            ],
            [
                'category' => 'Lab Komp',
                'color' => 'indigo',
                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>',
                'links' => [
                    $this->link('Inventaris Lab Komp', 'Master data inventaris lab komputer', route: 'inventory_labkomp.inventory_landing'),
                    $this->link('Peminjaman Barang', 'Pengelolaan peminjaman barang lab komputer', route: 'peminjaman_invlabkomp.peminjaman_landing'),
                    $this->link('Peminjaman Ruangan', 'Pengelolaan peminjaman ruangan lab komputer', route: 'booking_ruangan.booking_landing'),
                ],
            ],
                        [
                'category' => 'Layanan',
                'color' => 'teal',
                'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>',
                'links' => [
                    $this->link('Layanan Hotline', 'Layanan hotline internal madrasah', url: 'https://hotline-eight.vercel.app/'),
                ],
            ],
        ];
    }
}
