<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessProductImportJob;
use App\Models\ProductImport;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Product Import", description: "CSV product import endpoints")]
class ProductImportController extends Controller
{
    #[OA\Post(
        path: "/products/import",
        tags: ["Product Import"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["file"],
                    properties: [
                        new OA\Property(
                            property: "file",
                            type: "string",
                            format: "binary",
                            description: "CSV file with products"
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: "200", description: "Import queued"),
            new OA\Response(response: "401", description: "Unauthorized")
        ]
    )]
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $path = $request->file('file')->store('imports');
        $import = ProductImport::create(['filename' => $path, 'status' => 'queued']);
        ProcessProductImportJob::dispatch($path, $import->id)->onQueue('imports');

        return response()->json(['message' => 'Import queued', 'import_id' => $import->id]);
    }

    #[OA\Get(
        path: "/products/import/{id}",
        tags: ["Product Import"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: "200", description: "Import status"),
            new OA\Response(response: "404", description: "Not found")
        ]
    )]
    public function status($id)
    {
        $imp = ProductImport::findOrFail($id);
        return response()->json($imp);
    }
}