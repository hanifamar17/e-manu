<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PengumumanController extends Controller
{
    // CRUD (daftar/tambah/edit/hapus) menyusul di langkah 6.
    public function detail(int $id): View|RedirectResponse
    {
        $item = Pengumuman::find($id);

        return $item ? view('pengumuman.detail', ['item' => $item]) : redirect()->route('repository');
    }
}
