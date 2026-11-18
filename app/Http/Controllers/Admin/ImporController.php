<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CsvImporter;
use App\Services\ExcelTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImporController extends Controller
{
    public function form(): View
    {
        return view('admin.impor.form', [
            'types' => CsvImporter::TYPES,
            'columns' => CsvImporter::COLUMNS,
        ]);
    }

    public function store(Request $request, CsvImporter $importer): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(CsvImporter::TYPES))],
            'file' => ['required', 'file', 'max:2048', 'mimes:csv,txt,xlsx'],
            'mode' => ['required', Rule::in(['replace', 'append'])],
        ]);

        // Format tanggal tunggal: DD-MM-YYYY (CsvImporter::DEFAULT_DATE_FORMAT).
        $res = $importer->import($data['type'], $request->file('file')->getRealPath(), $data['mode'] === 'replace');

        if ($res['errors']) {
            return back()->withInput()->with('import_errors', array_slice($res['errors'], 0, 50));
        }

        $msg = "Berhasil: {$res['inserted']} baris diimpor.";
        if (! empty($res['generated'])) {
            $g = $res['generated'];
            $msg .= ' Kode baru: '.(count($g) === 1 ? $g[0] : $g[0].' - '.end($g)).'.';
        }

        return back()->with('status', $msg);
    }

    /** Template kosong (header saja). Data asli lewat menu Ekspor. */
    public function template(string $type, ExcelTemplate $tpl): BinaryFileResponse
    {
        abort_unless(isset(CsvImporter::TYPES[$type]), 404);
        $path = $tpl->build($type, false);

        return response()->download($path, 'template-'.$type.'.xlsx')->deleteFileAfterSend(true);
    }
}
