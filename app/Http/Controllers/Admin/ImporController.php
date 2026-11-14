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
            'formats' => CsvImporter::DATE_FORMATS,
        ]);
    }

    public function store(Request $request, CsvImporter $importer): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(CsvImporter::TYPES))],
            'file' => ['required', 'file', 'max:2048', 'mimes:csv,txt,xlsx'],
            'mode' => ['required', Rule::in(['replace', 'append'])],
            'format' => ['required', Rule::in(array_keys(CsvImporter::DATE_FORMATS))],
        ]);

        $res = $importer->import($data['type'], $request->file('file')->getRealPath(), $data['mode'] === 'replace', $data['format']);

        if ($res['errors']) {
            return back()->withInput()->with('import_errors', array_slice($res['errors'], 0, 50));
        }

        return back()->with('status', "Berhasil: {$res['inserted']} baris diimpor.");
    }

    public function template(Request $request, string $type, ExcelTemplate $tpl): BinaryFileResponse
    {
        abort_unless(isset(CsvImporter::TYPES[$type]), 404);
        $withData = $request->boolean('data');
        $path = $tpl->build($type, $withData);

        return response()->download($path, ($withData ? 'data-' : 'template-').$type.'.xlsx')->deleteFileAfterSend(true);
    }
}
