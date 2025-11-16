<?php

namespace App\Jobs;

use App\Imports\ProductsCsvImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductImport;

class ProcessProductImportJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public string $path;
    public int $importId;

    public function __construct(string $path, int $importId)
    {
        $this->path = $path;
        $this->importId = $importId;
    }

    public function handle()
    {
        $fullPath = storage_path('app/' . $this->path);
        ProductImport::where('id', $this->importId)->update(['status' => 'processing']);
        Excel::import(new ProductsCsvImport(), $fullPath);
        ProductImport::where('id', $this->importId)->update(['status' => 'completed']);
    }

    public function failed(\Throwable $exception)
    {
        ProductImport::where('id', $this->importId)->update([
            'status' => 'failed',
            'result' => ['error' => $exception->getMessage()]
        ]);
    }
}
