<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CsvImporter;
use App\Services\ExcelTemplate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EksporController extends Controller
{
    public function index(): View
    {
        return view('admin.ekspor.index', ['types' => CsvImporter::TYPES]);
    }

    /** Data asli dari DB, format sama dengan template impor (bisa diimpor ulang). */
    public function download(string $type, ExcelTemplate $tpl): BinaryFileResponse
    {
        abort_unless(isset(CsvImporter::TYPES[$type]), 404);
        $path = $tpl->build($type, true);

        return response()->download($path, 'ekspor-'.$type.'-'.now()->format('Ymd').'.xlsx')->deleteFileAfterSend(true);
    }
}
