<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessProductImportJob;
use App\Models\ProductImport;

class ProductImportController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $path = $request->file('file')->store('imports');
        $import = ProductImport::create(['filename' => $path, 'status' => 'queued']);
        // dispatch job
        ProcessProductImportJob::dispatch($path, $import->id)->onQueue('imports');

        return response()->json(['message' => 'Import queued', 'import_id' => $import->id]);
    }

    public function status($id)
    {
        $imp = ProductImport::findOrFail($id);
        return response()->json($imp);
    }
}
