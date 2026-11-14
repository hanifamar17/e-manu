<?php

use App\Http\Controllers\Admin\BookingAdminController;
use App\Http\Controllers\Admin\ImporController;
use App\Http\Controllers\Admin\KalenderAkademikController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingRuanganController;
use App\Http\Controllers\JadwalPelajaranController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\RepositoryController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

// Nama route sengaja sama dengan endpoint Flask (blueprint.fungsi)
// supaya konversi url_for('x.y') -> route('x.y') 1:1.

Route::redirect('/', '/repository');
Route::get('/repository', [RepositoryController::class, 'index'])->name('repository');
Route::view('/e-labkomp', 'landing')->name('labkomp_landing');

Route::get('/pengumuman/{id}/detail', [PengumumanController::class, 'detail'])
    ->whereNumber('id')->name('pengumuman.pengumuman_detail');

// --- Langkah 4: booking ruangan (publik) ---
Route::prefix('booking-ruangan')->name('booking_ruangan.')->group(function () {
    Route::get('/', [BookingRuanganController::class, 'landing'])->name('booking_landing');
    Route::get('/prosedur', [BookingRuanganController::class, 'prosedur'])->name('booking_prosedur');
    Route::get('/jadwal', [BookingRuanganController::class, 'jadwal'])->name('jadwal_penggunaan');
    Route::get('/form', [BookingRuanganController::class, 'form'])->name('booking_form_page');
    Route::post('/form', [BookingRuanganController::class, 'store'])->middleware('throttle:5,1')->name('booking_store');
});

// --- Langkah 5: jadwal pelajaran (publik). /kelas/<nama> & /hari/<hari> dihapus: landing sudah memuat semua. ---
Route::get('/jadwal-pelajaran', [JadwalPelajaranController::class, 'index'])->name('jadwal_pelajaran.jadwal_pelajaran_landing');

// --- Admin ---
Route::middleware(['auth', RoleMiddleware::class.':admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/', '/admin/booking')->name('home');

    Route::get('/booking', [BookingAdminController::class, 'index'])->name('booking.index');
    Route::post('/booking/{booking}/setujui', [BookingAdminController::class, 'setujui'])->name('booking.setujui');
    Route::post('/booking/{booking}/tolak', [BookingAdminController::class, 'tolak'])->name('booking.tolak');
    Route::delete('/booking/{booking}', [BookingAdminController::class, 'destroy'])->name('booking.destroy');

    Route::resource('kalender', KalenderAkademikController::class)->except('show');

    Route::get('/impor', [ImporController::class, 'form'])->name('impor.form');
    Route::post('/impor', [ImporController::class, 'store'])->name('impor.store');
    Route::get('/impor/template/{type}', [ImporController::class, 'template'])->name('impor.template');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');
