<?php

use App\Http\Controllers\Admin\BookingAdminController;
use App\Http\Controllers\Admin\AkunController;
use App\Http\Controllers\Admin\KelasAdminController;
use App\Http\Controllers\Admin\EksporController;
use App\Http\Controllers\Admin\ImporController;
use App\Http\Controllers\Admin\KalenderAkademikController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PengumumanAdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingRuanganController;
use App\Http\Controllers\JadwalPelajaranController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PerizinanController as PZ;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\BarangPinjamanController;
use App\Http\Controllers\PeminjamanAsetController as PA;
use App\Http\Controllers\LabkompInventarisController as LKI;
use App\Http\Controllers\LabkompPeminjamanController as LKP;

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

// Nama route sengaja sama dengan endpoint Flask (blueprint.fungsi)
// supaya konversi url_for('x.y') -> route('x.y') 1:1.

Route::redirect('/', '/repository');
Route::get('/repository', [RepositoryController::class, 'index'])->name('repository');
Route::view('/e-labkomp', 'landing')->name('labkomp_landing');

Route::view('/rdm', 'rdm')->name('rdm');

Route::get('/pengumuman/{id}/detail', [PengumumanController::class, 'detail'])
    ->whereNumber('id')->name('pengumuman.pengumuman_detail');

// --- Langkah 4: booking ruangan (publik) ---
Route::prefix('booking-ruangan')->name('booking_ruangan.')->group(function () {
    Route::get('/', [BookingRuanganController::class, 'landing'])->name('booking_landing');
    Route::get('/prosedur', [BookingRuanganController::class, 'prosedur'])->name('booking_prosedur');
    Route::get('/jadwal', [BookingRuanganController::class, 'jadwal'])->name('jadwal_penggunaan');
    Route::get('/form', [BookingRuanganController::class, 'form'])->name('booking_form_page');
    Route::post('/form', [BookingRuanganController::class, 'store'])->middleware('throttle:5,1')->name('booking_store');
    Route::get('/ringkasan/{uuid}', [BookingRuanganController::class, 'ringkasan'])->whereUuid('uuid')->name('ringkasan');
});

// --- Langkah 5: jadwal pelajaran (publik). /kelas/<nama> & /hari/<hari> dihapus: landing sudah memuat semua. ---
Route::get('/jadwal-pelajaran', [JadwalPelajaranController::class, 'index'])->name('jadwal_pelajaran.jadwal_pelajaran_landing');

// Sarpras
Route::middleware(['auth', RoleMiddleware::class.':admin'])->group(function () {
    Route::get('inventaris/{inventaris}/hapus', [InventarisController::class, 'konfirmasiHapus'])
        ->name('inventaris.hapus');
    Route::resource('inventaris', InventarisController::class)
        ->parameters(['inventaris' => 'inventaris'])->except('show');

    Route::get('barang-pinjaman/{barang_pinjaman}/hapus', [BarangPinjamanController::class, 'konfirmasiHapus'])
        ->name('barang_pinjaman.hapus');
    Route::resource('barang-pinjaman', BarangPinjamanController::class)
        ->parameters(['barang-pinjaman' => 'barang_pinjaman'])
        ->names('barang_pinjaman')->except('show');
});

Route::prefix('peminjaman-aset')->name('peminjaman.')->group(function () {
    // publik (peminjam)
    Route::get('/', [PA::class, 'landing'])->name('landing');
    Route::get('/form', [PA::class, 'form'])->name('form');
    Route::get('/search-barang', [PA::class, 'searchBarang'])->middleware('throttle:60,1')->name('search');
    Route::post('/submit', [PA::class, 'submit'])->middleware('throttle:10,1')->name('submit');
    Route::get('/ringkasan/{peminjaman}', [PA::class, 'ringkasan'])->whereUuid('peminjaman')->name('ringkasan');
    Route::post('/ringkasan/{peminjaman}/generate', [PA::class, 'generate'])->whereUuid('peminjaman')->middleware('throttle:10,1')->name('generate');
    Route::get('/ringkasan/{peminjaman}/preview', [PA::class, 'preview'])->whereUuid('peminjaman')->name('preview');

    // login-only (petugas)
    Route::middleware(['auth', RoleMiddleware::class.':admin'])->group(function () {
        Route::get('/riwayat', [PA::class, 'riwayat'])->name('riwayat');
        Route::get('/return', [PA::class, 'returnPage'])->name('return');
        Route::get('/api/loan', [PA::class, 'apiLoan'])->name('api.loan');
        Route::post('/return/submit', [PA::class, 'returnSubmit'])->name('return.submit');
    });
});

// --- Lab Komp: inventaris (landing + search publik; sisanya login) ---
Route::prefix('labkomp/inventaris')->name('inventory_labkomp.')->group(function () {
    Route::get('/', [LKI::class, 'landing'])->name('inventory_landing');
    Route::get('/api/search', [LKI::class, 'search'])->middleware('throttle:60,1')->name('api_search');

    Route::middleware(['auth', RoleMiddleware::class.':admin'])->group(function () {
        Route::get('/daftar', [LKI::class, 'index'])->name('inventory_list');
        Route::get('/tambah', [LKI::class, 'create'])->name('inventory_add');
        Route::post('/tambah', [LKI::class, 'store'])->name('inventory_store');
        Route::get('/{item}/edit', [LKI::class, 'edit'])->whereNumber('item')->name('inventory_edit');
        Route::put('/{item}', [LKI::class, 'update'])->whereNumber('item')->name('inventory_update');
        Route::get('/{item}/hapus', [LKI::class, 'konfirmasiHapus'])->whereNumber('item')->name('inventory_delete');
        Route::delete('/{item}', [LKI::class, 'destroy'])->whereNumber('item')->name('inventory_destroy');
    });
});

// --- Lab Komp: peminjaman barang ---
Route::prefix('labkomp/peminjaman')->name('peminjaman_invlabkomp.')->group(function () {
    // publik (peminjam)
    Route::get('/', [LKP::class, 'landing'])->name('peminjaman_landing');
    Route::get('/pinjam', [LKP::class, 'borrow'])->name('borrow');
    Route::post('/pinjam', [LKP::class, 'submit'])->middleware('throttle:10,1')->name('borrow_submit');
    Route::get('/bukti/{uuid}', [LKP::class, 'receipt'])->whereUuid('uuid')->name('receipt');

    // login-only (petugas)
    Route::middleware(['auth', RoleMiddleware::class.':admin'])->group(function () {
        Route::get('/riwayat', [LKP::class, 'history'])->name('history');
        Route::get('/detail/{kode}', [LKP::class, 'detail'])->name('loan_detail');
        Route::get('/kembali', [LKP::class, 'returnPage'])->name('return_page');
        Route::get('/api/loan', [LKP::class, 'apiLoan'])->name('api_loan');
        Route::post('/kembali', [LKP::class, 'returnSubmit'])->name('return_submit');
    });
});

// --- Perizinan siswa: login (guru / wali_kelas / admin); surat publik via uuid ---
Route::prefix('perizinan')->name('perizinan.')->group(function () {
    Route::get('/surat/{uuid}', [PZ::class, 'surat'])->whereUuid('uuid')->name('surat');

    // ACC wali kelas lewat tautan (dikirim guru via WhatsApp), tanpa login; dijaga uuid + PIN 4 digit
    Route::get('/acc-link/{uuid}', [PZ::class, 'accLink'])->whereUuid('uuid')->name('acc_link');
    Route::post('/acc-link/{uuid}/setujui', [PZ::class, 'accLinkSetujui'])->whereUuid('uuid')->middleware('throttle:10,1')->name('acc_link.setujui');
    Route::post('/acc-link/{uuid}/tolak', [PZ::class, 'accLinkTolak'])->whereUuid('uuid')->middleware('throttle:10,1')->name('acc_link.tolak');

    // layar TV: tanpa login, dikunci token rahasia (config emanu.layar_token)
    Route::get('/layar/{token}', [PZ::class, 'layar'])->where('token', '[A-Za-z0-9_-]+')->middleware('throttle:30,1')->name('layar');
    Route::get('/layar/{token}/data', [PZ::class, 'layarData'])->where('token', '[A-Za-z0-9_-]+')->middleware('throttle:30,1')->name('layar.data');

    Route::middleware(['auth', RoleMiddleware::class.':guru,wali_kelas,admin'])->group(function () {
        Route::get('/', [PZ::class, 'index'])->name('index');
        Route::get('/form', [PZ::class, 'form'])->name('form');
        Route::post('/form', [PZ::class, 'store'])->middleware('throttle:20,1')->name('store');
        Route::get('/api/siswa', [PZ::class, 'siswa'])->middleware('throttle:60,1')->name('siswa');
        Route::get('/api/kelas', [PZ::class, 'siswaKelas'])->middleware('throttle:60,1')->name('kelas');
        Route::get('/riwayat', [PZ::class, 'riwayat'])->name('riwayat');
        Route::get('/riwayat/{perizinan}', [PZ::class, 'show'])->name('show');
        Route::post('/riwayat/{perizinan}/batal', [PZ::class, 'batal'])->name('batal');

        // hanya wali kelas (+ admin); menu ACC disembunyikan dari guru
        Route::middleware(RoleMiddleware::class.':wali_kelas,admin')->group(function () {
            Route::get('/acc', [PZ::class, 'acc'])->name('acc');
            Route::post('/riwayat/{perizinan}/setujui', [PZ::class, 'setujui'])->name('setujui');
            Route::post('/riwayat/{perizinan}/tolak', [PZ::class, 'tolak'])->name('tolak');
        });
    });
});

// --- Admin ---
Route::middleware(['auth', RoleMiddleware::class.':admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');

    Route::get('/booking', [BookingAdminController::class, 'index'])->name('booking.index');
    Route::post('/booking/{booking}/setujui', [BookingAdminController::class, 'setujui'])->name('booking.setujui');
    Route::post('/booking/{booking}/tolak', [BookingAdminController::class, 'tolak'])->name('booking.tolak');
    Route::delete('/booking/{booking}', [BookingAdminController::class, 'destroy'])->name('booking.destroy');

    Route::resource('kalender', KalenderAkademikController::class)->except('show');
    Route::resource('pengumuman', PengumumanAdminController::class)->parameters(['pengumuman' => 'pengumuman'])->except('show');

    Route::get('/impor', [ImporController::class, 'form'])->name('impor.form');
    Route::post('/impor', [ImporController::class, 'store'])->name('impor.store');
    Route::get('/impor/template/{type}', [ImporController::class, 'template'])->name('impor.template');

    Route::resource('kelas', KelasAdminController::class)->only(['index', 'edit', 'update'])->parameters(['kelas' => 'kelas']);

    Route::get('akun/{akun}/hapus', [AkunController::class, 'konfirmasiHapus'])->name('akun.hapus');
    Route::resource('akun', AkunController::class)->parameters(['akun' => 'akun'])->except('show');

    Route::get('/ekspor', [EksporController::class, 'index'])->name('ekspor.index');
    Route::get('/ekspor/{type}', [EksporController::class, 'download'])->name('ekspor.download');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:60,1')->name('login.store');
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');
